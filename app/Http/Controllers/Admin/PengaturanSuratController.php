<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSurat;
use App\Models\PenomoranSurat;
use App\Models\TemplateSurat;
use App\Services\RenderSurat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/*
| Menu "Pengaturan Surat": kop surat (teks + logo) dan data Kepala Sekolah.
| Semua template surat keluar memakai pengaturan ini lewat {{kop_surat}}.
*/
class PengaturanSuratController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan-surat.index', [
            'title'      => 'Pengaturan Surat',
            'subtitle'   => 'Kop surat dan data penanda tangan untuk semua surat keluar.',
            'pengaturan' => PengaturanSurat::ambil(),
            'penomoran'  => $this->daftarPenomoran(),
        ]);
    }

    /*
     * Aturan nomor untuk semua template aktif + "Umum" (surat tanpa template).
     * Yang belum ada dibuat otomatis dengan format bawaan.
     */
    private function daftarPenomoran()
    {
        PenomoranSurat::untukTemplate(null);

        TemplateSurat::where('is_active', true)->pluck('id')
            ->each(fn ($id) => PenomoranSurat::untukTemplate($id));

        return PenomoranSurat::with('templateSurat:id,nama_template,is_active')
            ->get()
            ->filter(fn ($p) => $p->template_surat_id === null || $p->templateSurat?->is_active)
            ->sortBy(fn ($p) => $p->templateSurat->nama_template ?? '')
            ->values();
    }

    public function simpanPenomoran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'penomoran'                  => 'required|array',
            'penomoran.*.kode'           => 'nullable|string|max:30',
            'penomoran.*.format'         => ['required', 'string', 'max:100', 'regex:/\{nomor\}/'],
            'penomoran.*.panjang'        => 'required|integer|min:1|max:6',
            'penomoran.*.reset'          => 'required|in:tahunan,bulanan,tidak',
            'penomoran.*.nomor_terakhir' => 'required|integer|min:0|max:999999',
        ], [
            'penomoran.*.format.required' => 'Format nomor wajib diisi.',
            'penomoran.*.format.regex'    => 'Format nomor wajib memuat {nomor}.',
            'penomoran.*.nomor_terakhir.*'=> 'Nomor terakhir harus angka 0 atau lebih.',
            'penomoran.*.panjang.*'       => 'Jumlah digit 1 sampai 6.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        foreach ($validator->validated()['penomoran'] as $id => $isi) {
            $aturan = PenomoranSurat::find($id);

            if (! $aturan) {
                continue;
            }

            $aturan->fill($isi);
            // "Nomor terakhir" yang diisi TU dianggap milik periode sekarang
            $aturan->periode = $aturan->periodeUntuk(now());
            $aturan->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan nomor surat disimpan.',
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kop_baris_1'     => 'nullable|string|max:150',
            'kop_baris_2'     => 'nullable|string|max:150',
            'kop_baris_3'     => 'required|string|max:150',
            'kop_alamat'      => 'nullable|string|max:255',
            'kop_telepon'     => 'nullable|string|max:100',
            'kop_laman'       => 'nullable|string|max:150',
            'kop_email'       => 'nullable|string|max:150',
            'tinggi_logo'     => 'required|integer|min:40|max:130',
            'logo_kiri'       => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'logo_kanan'      => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'hapus_logo_kiri' => 'nullable|boolean',
            'hapus_logo_kanan'=> 'nullable|boolean',
            'kota'            => 'required|string|max:100',
            'nama_sekolah'    => 'required|string|max:150',
            'kepsek_nama'     => 'required|string|max:150',
            'kepsek_pangkat'  => 'nullable|string|max:100',
            'kepsek_golongan' => 'nullable|string|max:20',
            'kepsek_nip'      => 'nullable|string|max:50',
        ], [
            'kop_baris_3.required' => 'Nama sekolah di kop wajib diisi.',
            'kepsek_nama.required' => 'Nama Kepala Sekolah wajib diisi.',
            'logo_kiri.image'      => 'Logo harus berupa gambar PNG/JPG.',
            'logo_kanan.image'     => 'Logo harus berupa gambar PNG/JPG.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pengaturan = PengaturanSurat::ambil();
        $data = collect($validator->validated())
            ->except(['logo_kiri', 'logo_kanan', 'hapus_logo_kiri', 'hapus_logo_kanan'])
            ->all();

        foreach (['kiri', 'kanan'] as $sisi) {
            $kolom = 'logo_' . $sisi;

            if ($request->hasFile($kolom) || $request->boolean('hapus_' . $kolom)) {
                // Hapus file logo lama (logo bawaan di public/images tidak disentuh)
                if ($pengaturan->$kolom) {
                    Storage::disk('public')->delete($pengaturan->$kolom);
                }
                $data[$kolom] = null;
            }

            if ($request->hasFile($kolom)) {
                $data[$kolom] = $request->file($kolom)->store('kop-surat', 'public');
            }
        }

        $pengaturan->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan surat disimpan. Semua surat yang dibuat setelah ini memakai kop baru.',
        ]);
    }

    /*
     * Contoh PDF dengan pengaturan yang tersimpan (pakai template "Surat Dinas Standar").
     */
    public function contohPdf(RenderSurat $render)
    {
        $template = TemplateSurat::where('nama_template', 'Surat Dinas Standar')->value('isi_template')
            ?? '{{kop_surat}}<p>{{kota}}, {{tanggal_surat}}</p><p>{{isi_surat}}</p>'
             . '<p>Kepala Sekolah,<br><br><br><br>{{nama_kepala_sekolah}}<br>{{pangkat_kepala_sekolah}}<br>NIP {{nip_kepala_sekolah}}</p>';

        $html = $render->isiTemplate($template, [
            'nomor_surat'   => '000/CONTOH/' . now()->year,
            'tanggal_surat' => now()->toDateString(),
            'sifat'         => 'Biasa',
            'lampiran'      => '-',
            'perihal'       => 'Contoh Tampilan Kop Surat',
            'tujuan'        => 'Bapak/Ibu Penerima Surat',
            'alamat_tujuan' => 'Tempat',
            'isi_surat'     => "Dengan hormat,\nIni adalah contoh surat untuk melihat tampilan kop dan tanda tangan sesuai Pengaturan Surat.\nDemikian, atas perhatiannya diucapkan terima kasih.",
        ]);

        return Pdf::loadView('admin.surat-keluar.pdf', ['isiSurat' => $render->sematkanGambar($html)])
            ->setPaper('a4', 'portrait')
            ->stream('contoh-kop-surat.pdf');
    }
}
