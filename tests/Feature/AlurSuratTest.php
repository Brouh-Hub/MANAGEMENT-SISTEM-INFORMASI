<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Mitra;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlurSuratTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $jabatan): User
    {
        return User::create([
            'name'     => $jabatan,
            'email'    => strtolower(str_replace(' ', '', $jabatan)) . '@test.id',
            'password' => Hash::make('rahasia123'),
            'role'     => 'admin',
            'jabatan'  => $jabatan,
        ]);
    }

    private function suratMasuk(string $status = 'menunggu'): SuratMasuk
    {
        return SuratMasuk::create([
            'nomor_surat'       => 'SM-' . uniqid(),
            'perihal'           => 'Undangan',
            'pengirim'          => 'Dinas',
            'tanggal_surat'     => '2026-10-01',
            'tanggal_terima'    => '2026-10-02',
            'no_agenda'         => '001/SM/X/2026',
            'status_konfirmasi' => $status,
        ]);
    }

    private function suratKeluar(User $tu, string $status = 'draft'): SuratKeluar
    {
        return SuratKeluar::create([
            'nomor_surat'     => 'SK-' . uniqid(),
            'perihal'         => 'Pemberitahuan',
            'tujuan'          => 'Orang tua',
            'tanggal_surat'   => '2026-10-03',
            'user_id_pembuat' => $tu->id,
            'status'          => $status,
        ]);
    }

    public function test_menu_disposisi_saya_tampil_untuk_kepala_tu_bukan_untuk_tu(): void
    {
        $this->actingAs($this->user('Kepala Tata Usaha'))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Disposisi Saya');

        $this->actingAs($this->user('Tata Usaha'))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertDontSee('Disposisi Saya');
    }

    public function test_hak_akses_halaman_per_jabatan(): void
    {
        $matriks = [
            // jabatan => [url => status]
            'Tata Usaha'        => ['/admin/surat-masuk' => 200, '/admin/surat-keluar' => 200, '/admin/disposisi' => 403, '/admin/konfirmasi-surat' => 403, '/admin/users' => 200],
            'Kepala Tata Usaha' => ['/admin/surat-masuk' => 403, '/admin/surat-keluar' => 403, '/admin/disposisi' => 403, '/admin/konfirmasi-surat' => 200, '/admin/disposisi-saya' => 200, '/admin/users' => 403],
            'Kepala Sekolah'    => ['/admin/surat-masuk' => 403, '/admin/surat-keluar' => 200, '/admin/disposisi' => 200, '/admin/konfirmasi-surat' => 403, '/admin/users' => 403],
            'Waka Humas'        => ['/admin/surat-masuk' => 403, '/admin/disposisi' => 403, '/admin/disposisi-saya' => 200, '/admin/mitra' => 200, '/admin/mou-pks' => 200, '/admin/users' => 403],
            'Waka Kurikulum'    => ['/admin/disposisi-saya' => 200, '/admin/surat-keluar' => 403, '/admin/mitra' => 403, '/admin/users' => 403],
        ];

        foreach ($matriks as $jabatan => $halaman) {
            $user = $this->user($jabatan);

            foreach ($halaman as $url => $status) {
                $this->actingAs($user)->get($url)->assertStatus($status, "$jabatan -> $url");
            }
        }
    }

    public function test_alur_surat_masuk_sampai_disposisi_selesai(): void
    {
        $tu     = $this->user('Tata Usaha');
        $ktu    = $this->user('Kepala Tata Usaha');
        $kepsek = $this->user('Kepala Sekolah');
        $waka   = $this->user('Waka Kurikulum');
        $surat  = $this->suratMasuk();

        // Kepala TU setuju -> disposisi otomatis ke Kepala Sekolah
        $this->actingAs($ktu)
            ->post("/admin/konfirmasi-surat/masuk/{$surat->id}", ['keputusan' => 'setujui'])
            ->assertOk();

        $awal = Disposisi::where('surat_masuk_id', $surat->id)->firstOrFail();
        $this->assertSame($kepsek->id, $awal->tujuan_user_id);

        // Dua kali setuju tidak boleh jalan
        $this->actingAs($ktu)
            ->post("/admin/konfirmasi-surat/masuk/{$surat->id}", ['keputusan' => 'setujui'])
            ->assertStatus(422);
        $this->assertSame(1, Disposisi::where('surat_masuk_id', $surat->id)->count());

        // TU tidak boleh ubah/hapus surat yang sudah disetujui
        $this->actingAs($tu)->delete("/admin/surat-masuk/{$surat->id}")->assertStatus(422);

        // Kepsek tidak boleh meneruskan ke Tata Usaha
        $this->actingAs($kepsek)
            ->post("/admin/disposisi/{$awal->id}/teruskan", ['tujuan_user_id' => $tu->id, 'sifat' => 'biasa', 'instruksi' => 'Tolong'])
            ->assertStatus(422);

        // Kepsek teruskan ke Kepala TU dan ke Waka
        foreach ([$ktu, $waka] as $penerima) {
            $this->actingAs($kepsek)
                ->post("/admin/disposisi/{$awal->id}/teruskan", ['tujuan_user_id' => $penerima->id, 'sifat' => 'segera', 'instruksi' => 'Tindak lanjuti'])
                ->assertOk();
        }

        // Kepala TU melihat disposisinya di Disposisi Saya
        $this->actingAs($ktu)->get('/admin/disposisi-saya')->assertOk()->assertSee('Undangan');

        // Penerima menjalankan alur status sampai selesai
        $d = Disposisi::where('tujuan_user_id', $waka->id)->firstOrFail();

        $this->actingAs($waka)->put("/admin/disposisi-saya/{$d->id}", ['status' => 'diproses'])->assertStatus(422); // lompat status
        $this->actingAs($waka)->put("/admin/disposisi-saya/{$d->id}", ['status' => 'diterima'])->assertOk();
        $this->actingAs($waka)->put("/admin/disposisi-saya/{$d->id}", ['status' => 'diproses'])->assertOk();
        $this->actingAs($waka)->put("/admin/disposisi-saya/{$d->id}", ['status' => 'selesai'])->assertStatus(422); // tanpa laporan
        $this->actingAs($waka)->put("/admin/disposisi-saya/{$d->id}", ['status' => 'selesai', 'laporan_hasil' => 'Sudah dilaksanakan'])->assertOk();

        $this->assertSame('selesai', $d->fresh()->status);

        // Orang lain tidak boleh mengubah disposisi milik Waka
        $this->actingAs($ktu)->put("/admin/disposisi-saya/{$d->id}", ['status' => 'diterima'])->assertForbidden();
    }

    public function test_detail_surat_hanya_untuk_kepsek_atau_penerima(): void
    {
        $surat  = $this->suratMasuk('disetujui');
        $waka   = $this->user('Waka Kurikulum');
        $guru   = $this->user('Guru');
        $kepsek = $this->user('Kepala Sekolah');

        Disposisi::create([
            'surat_masuk_id' => $surat->id, 'tujuan_user_id' => $waka->id, 'kepala_sekolah_id' => $kepsek->id,
            'instruksi' => 'x', 'status' => 'pending', 'sifat' => 'biasa', 'tanggal_disposisi' => now(),
        ]);

        $this->actingAs($guru)->get("/admin/disposisi/{$surat->id}/detail")->assertForbidden();
        $this->actingAs($waka)->get("/admin/disposisi/{$surat->id}/detail")->assertOk();
        $this->actingAs($kepsek)->get("/admin/disposisi/{$surat->id}/detail")->assertOk();
    }

    public function test_surat_keluar_hanya_bisa_diubah_atau_dihapus_saat_draft(): void
    {
        $tu = $this->user('Tata Usaha');

        $terbit = $this->suratKeluar($tu, 'terbit');
        $draft  = $this->suratKeluar($tu, 'draft');

        $this->actingAs($tu)
            ->put("/admin/surat-keluar/{$terbit->id}", ['perihal' => 'Ganti', 'tanggal_surat' => '2026-10-04'])
            ->assertStatus(422);
        $this->actingAs($tu)->delete("/admin/surat-keluar/{$terbit->id}")->assertStatus(422);
        $this->assertDatabaseHas('surat_keluar', ['id' => $terbit->id, 'perihal' => 'Pemberitahuan']);

        $this->actingAs($tu)
            ->put("/admin/surat-keluar/{$draft->id}", ['perihal' => 'Ganti', 'tanggal_surat' => '2026-10-04'])
            ->assertOk();
        $this->actingAs($tu)->delete("/admin/surat-keluar/{$draft->id}")->assertOk();
    }

    public function test_alur_surat_keluar_kepala_tu_lalu_kepala_sekolah(): void
    {
        $tu     = $this->user('Tata Usaha');
        $ktu    = $this->user('Kepala Tata Usaha');
        $kepsek = $this->user('Kepala Sekolah');
        $surat  = $this->suratKeluar($tu, 'menunggu_kepala_tu');

        // Kepsek belum boleh approve sebelum Kepala TU setuju
        $this->actingAs($kepsek)->post("/admin/surat-keluar/{$surat->id}/approve")->assertStatus(422);

        // Kepala TU mengembalikan
        $this->actingAs($ktu)
            ->post("/admin/konfirmasi-surat/keluar/{$surat->id}", ['keputusan' => 'kembalikan', 'alasan' => 'Perbaiki tujuan'])
            ->assertOk();
        $this->assertSame('dikembalikan', $surat->fresh()->status);

        // Setelah dikembalikan TU boleh mengubah, jejak konfirmasi dibersihkan saat diajukan ulang
        $this->actingAs($tu)
            ->put("/admin/surat-keluar/{$surat->id}", ['perihal' => 'Revisi', 'tanggal_surat' => '2026-10-04'])
            ->assertOk();

        $surat->refresh()->update(['status' => 'menunggu_kepala_tu']); // seolah sudah diajukan ulang
        $this->actingAs($ktu)
            ->post("/admin/konfirmasi-surat/keluar/{$surat->id}", ['keputusan' => 'setujui'])
            ->assertOk();
        $this->assertSame('menunggu_approve', $surat->fresh()->status);

        $this->actingAs($kepsek)->post("/admin/surat-keluar/{$surat->id}/approve")->assertOk();
        $this->assertSame('terbit', $surat->fresh()->status);

        // Surat terbit tidak bisa diubah lagi
        $this->actingAs($tu)
            ->put("/admin/surat-keluar/{$surat->id}", ['perihal' => 'Curang', 'tanggal_surat' => '2026-10-04'])
            ->assertStatus(422);
    }

    public function test_hapus_user_yang_punya_data_ditolak_dan_tu_terakhir_aman(): void
    {
        $tu     = $this->user('Tata Usaha');
        $kosong = $this->user('Guru');
        $this->suratKeluar($tu); // tu punya data

        $pembuat = $this->user('Waka Humas');
        $this->suratKeluar($pembuat);

        $this->actingAs($tu)->delete("/admin/users/{$pembuat->id}")->assertStatus(422);
        $this->actingAs($tu)->delete("/admin/users/{$kosong->id}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $kosong->id]);
    }

    private function generate(User $tu, ?int $templateId = null)
    {
        return $this->actingAs($tu)->postJson('/admin/surat-keluar/generate-nomor', [
            'password'          => 'rahasia123',
            'template_surat_id' => $templateId,
        ]);
    }

    public function test_generate_nomor_tidak_pernah_menghasilkan_nomor_yang_sudah_ada(): void
    {
        $tu  = $this->user('Tata Usaha');
        $tu2 = User::create(['name' => 'TU2', 'email' => 'tu2@test.id', 'password' => Hash::make('rahasia123'), 'role' => 'admin', 'jabatan' => 'Tata Usaha']);

        $n1 = $this->generate($tu)->assertOk()->json('data.nomor_surat');

        // Belum disimpan: TU yang sama dapat nomor itu lagi (tidak ada nomor terbuang)
        $this->assertSame($n1, $this->generate($tu)->json('data.nomor_surat'));

        // TU lain tidak boleh dapat nomor yang sama
        $n2 = $this->generate($tu2)->json('data.nomor_surat');
        $this->assertNotSame($n1, $n2);

        // Surat disimpan dengan n1 -> generate berikutnya tidak boleh n1 lagi
        $this->actingAs($tu)->postJson('/admin/surat-keluar', [
            'nomor_surat' => $n1, 'perihal' => 'A', 'tanggal_surat' => '2026-10-05',
        ])->assertCreated();
        $n3 = $this->generate($tu)->json('data.nomor_surat');
        $this->assertNotContains($n3, [$n1, $n2]);

        // Nomor sudah dipakai dokumen MoU/PKS -> tidak boleh keluar lagi, walau reservasinya masih kosong
        $mitra = Mitra::create(['nama_perusahaan' => 'PT Uji', 'bidang' => 'IT', 'humas_id' => $tu->id]);
        DB::table('dokumen_kerjasama')->insert([
            'industri_id' => $mitra->id, 'jenis' => 'MoU', 'nomor_dokumen' => $n3, 'judul' => 'x', 'tanggal_mulai' => '2026-10-01', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $n4 = $this->generate($tu)->json('data.nomor_surat');
        $this->assertNotContains($n4, [$n1, $n2, $n3]);

        // Nomor yang sudah ada di surat keluar (mis. surat lama diketik manual) dilewati
        $this->suratKeluar($tu)->update(['nomor_surat' => $n4]);
        $n5 = $this->generate($tu)->json('data.nomor_surat');
        $this->assertNotContains($n5, [$n1, $n2, $n3, $n4]);
    }

    public function test_simpan_surat_keluar_dengan_file_pdf(): void
    {
        Storage::fake('public');
        $tu    = $this->user('Tata Usaha');
        $nomor = $this->generate($tu)->json('data.nomor_surat');

        $this->actingAs($tu)->post('/admin/surat-keluar', [
            'nomor_surat' => $nomor, 'perihal' => 'Dengan file', 'tanggal_surat' => '2026-10-05',
            'file_surat'  => UploadedFile::fake()->create('s.pdf', 50, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated();
    }
}
