<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/*
| Halaman "Konfirmasi Surat" untuk Kepala Tata Usaha.
|
| Surat masuk : TU input -> [Kepala TU setujui] -> disposisi ke Kepala Sekolah
| Surat keluar: TU ajukan -> [Kepala TU setujui] -> menunggu persetujuan Kepala Sekolah
| Kalau dikembalikan, surat balik ke TU beserta alasannya.
*/
class KonfirmasiSuratController extends Controller
{
    public function index()
    {
        $suratMasuk = SuratMasuk::where('status_konfirmasi', 'menunggu')
            ->orderBy('tanggal_terima')
            ->orderBy('id')
            ->get();

        $suratKeluar = SuratKeluar::with('pembuat:id,name')
            ->where('status', 'menunggu_kepala_tu')
            ->orderBy('updated_at')
            ->get();

        // Riwayat keputusan terakhir (surat masuk & keluar digabung)
        $riwayat = SuratMasuk::with('pengonfirmasi:id,name')
            ->whereNotNull('dikonfirmasi_at')
            ->latest('dikonfirmasi_at')
            ->take(15)
            ->get()
            ->map(fn ($s) => [
                'jenis'   => 'Masuk',
                'nomor'   => $s->nomor_surat,
                'perihal' => $s->perihal,
                'pihak'   => $s->pengirim,
                // Sudah pernah diputuskan tapi "menunggu" lagi = dulu dikembalikan lalu dikirim ulang TU
                'hasil'   => $s->status_konfirmasi === 'disetujui' ? 'disetujui' : 'dikembalikan',
                'alasan'  => $s->status_konfirmasi === 'disetujui' ? null : $s->alasan_konfirmasi,
                'oleh'    => $s->pengonfirmasi->name ?? '-',
                'waktu'   => $s->dikonfirmasi_at,
            ])
            ->concat(
                SuratKeluar::with('pengonfirmasi:id,name')
                    ->whereNotNull('dikonfirmasi_at')
                    ->latest('dikonfirmasi_at')
                    ->take(15)
                    ->get()
                    ->map(fn ($s) => [
                        'jenis'   => 'Keluar',
                        'nomor'   => $s->nomor_surat,
                        'perihal' => $s->perihal,
                        'pihak'   => $s->tujuan,
                        'hasil'   => in_array($s->status, ['menunggu_approve', 'terbit'], true) ? 'disetujui' : 'dikembalikan',
                        'alasan'  => in_array($s->status, ['menunggu_approve', 'terbit'], true) ? null : $s->alasan_konfirmasi,
                        'oleh'    => $s->pengonfirmasi->name ?? '-',
                        'waktu'   => $s->dikonfirmasi_at,
                    ])
            )
            ->sortByDesc('waktu')
            ->take(15)
            ->values();

        return view('admin.konfirmasi-surat.index', [
            'title'         => 'Konfirmasi Surat',
            'subtitle'      => 'Periksa surat masuk dan surat keluar dari Tata Usaha sebelum diteruskan ke Kepala Sekolah.',
            'suratMasuk'    => $suratMasuk,
            'suratKeluar'   => $suratKeluar,
            'riwayat'       => $riwayat,
            'alasanKembali' => config('konfirmasi.alasan_kembali', []),
            'adaKepsek'     => User::where('jabatan', 'Kepala Sekolah')->exists(),
        ]);
    }

    /*
     * POST /konfirmasi-surat/{jenis}/{id}
     * keputusan = setujui | kembalikan, alasan wajib kalau dikembalikan
     */
    public function putuskan(Request $request, string $jenis, int $id)
    {
        abort_unless(in_array($jenis, ['masuk', 'keluar'], true), 404);

        $validator = Validator::make($request->all(), [
            'keputusan' => 'required|in:setujui,kembalikan',
            'alasan'    => 'required_if:keputusan,kembalikan|nullable|string|max:1000',
        ], [
            'keputusan.*'        => 'Pilih setujui atau kembalikan.',
            'alasan.required_if' => 'Tuliskan alasan surat dikembalikan.',
            'alasan.max'         => 'Alasan maksimal 1000 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $setujui = $request->input('keputusan') === 'setujui';
        $alasan = $setujui ? null : trim($request->input('alasan'));

        return $jenis === 'masuk'
            ? $this->putuskanMasuk($id, $setujui, $alasan)
            : $this->putuskanKeluar($id, $setujui, $alasan);
    }

    private function putuskanMasuk(int $id, bool $setujui, ?string $alasan)
    {
        $surat = SuratMasuk::find($id);

        if (! $surat) {
            return response()->json(['status' => 'error', 'message' => 'Surat masuk tidak ditemukan.'], 404);
        }

        if ($surat->status_konfirmasi !== 'menunggu') {
            return response()->json(['status' => 'error', 'message' => 'Surat ini sudah dikonfirmasi sebelumnya.'], 422);
        }

        DB::transaction(function () use ($surat, $setujui, $alasan) {
            $surat->update([
                'status_konfirmasi' => $setujui ? 'disetujui' : 'dikembalikan',
                'alasan_konfirmasi' => $alasan,
                'dikonfirmasi_oleh' => auth()->id(),
                'dikonfirmasi_at'   => now(),
            ]);

            // Disetujui -> masuk ke daftar disposisi Kepala Sekolah
            // (sebelumnya dibuat langsung saat TU menyimpan surat)
            if ($setujui && ! $surat->disposisi()->exists()) {
                $kepalaSekolah = User::where('jabatan', 'Kepala Sekolah')->first();

                if ($kepalaSekolah) {
                    Disposisi::create([
                        'surat_masuk_id'    => $surat->id,
                        'tujuan_user_id'    => $kepalaSekolah->id,
                        'kepala_sekolah_id' => $kepalaSekolah->id,
                        'sifat'             => 'biasa',
                        'instruksi'         => 'Surat masuk baru, mohon ditindaklanjuti.',
                        'catatan'           => null,
                        'status'            => 'pending',
                        'tanggal_disposisi' => now(),
                    ]);
                }
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => $setujui
                ? 'Surat masuk disetujui dan diteruskan ke Kepala Sekolah.'
                : 'Surat masuk dikembalikan ke Tata Usaha.',
        ]);
    }

    private function putuskanKeluar(int $id, bool $setujui, ?string $alasan)
    {
        $surat = SuratKeluar::find($id);

        if (! $surat) {
            return response()->json(['status' => 'error', 'message' => 'Surat keluar tidak ditemukan.'], 404);
        }

        if ($surat->status !== 'menunggu_kepala_tu') {
            return response()->json(['status' => 'error', 'message' => 'Surat ini tidak sedang menunggu konfirmasi Kepala TU.'], 422);
        }

        $surat->update([
            'status'            => $setujui ? 'menunggu_approve' : 'dikembalikan',
            'alasan_konfirmasi' => $alasan,
            'dikonfirmasi_oleh' => auth()->id(),
            'dikonfirmasi_at'   => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $setujui
                ? 'Surat keluar disetujui dan diteruskan ke Kepala Sekolah.'
                : 'Surat keluar dikembalikan ke Tata Usaha.',
        ]);
    }
}
