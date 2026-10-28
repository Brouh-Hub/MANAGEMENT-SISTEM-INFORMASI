<?php

namespace App\Http\Controllers\Surat;

use App\Models\SuratKeluar;
use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\Disposisi;
use App\Models\InstruksiTersembunyi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class DisposisiController extends Controller
{
    private const JABATAN_BUKAN_PENERIMA = ['Tata Usaha', 'Kepala Sekolah'];

    // List surat masuk yang perlu didisposisikan (kepala sekolah)
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Hanya surat yang sudah disetujui Kepala Tata Usaha
            $data = SuratMasuk::with('disposisi')
                ->where('status_konfirmasi', 'disetujui')
                ->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_disposisi', function ($row) {
                    $terakhir = $row->disposisi->sortByDesc('id')->first();
                    $status   = $terakhir->status ?? null;

                    return match ($status) {
                        'selesai'                => '<span class="badge-status badge-selesai">Selesai</span>',
                        'diterima', 'diproses', 'diteruskan' => '<span class="badge-status badge-diproses">Diproses</span>',
                        'pending'                => '<span class="badge-status badge-belum">Menunggu</span>',
                        'diarsipkan'             => '<span class="badge-status badge-arsip">Diarsipkan</span>',
                        'ditolak'                => '<span class="badge-status badge-ditolak">Ditolak</span>',
                        default                  => '<span class="badge-status badge-belum">Belum Disposisi</span>',
                    };
                })
                ->addColumn('aksi', function ($row) {
                    // Disposisi yang sudah diteruskan Kepala Sekolah dan belum diproses penerimanya
                    $bisaDibatalkan = $row->disposisi
                        ->whereNotNull('diteruskan_dari_id')
                        ->where('status', 'pending')
                        ->where('kepala_sekolah_id', auth()->id())
                        ->sortByDesc('id')
                        ->first();

                    $btn  = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-light btn-lihat-disposisi" data-id="' . $row->id . '" title="Lihat & Disposisikan"><i class="fa fa-eye"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-light btn-riwayat-disposisi" data-id="' . $row->id . '" title="Riwayat"><i class="fa fa-clock-rotate-left"></i></button>';

                    if ($bisaDibatalkan) {
                        $btn .= '<button type="button" class="btn btn-sm btn-light text-danger btn-hapus-disposisi" data-disposisi-id="' . $bisaDibatalkan->id . '" title="Batalkan Disposisi"><i class="fa fa-trash"></i></button>';
                    }

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['status_disposisi', 'aksi'])
                ->make(true);
        }

        // Tata Usaha & Kepala Sekolah tidak menerima disposisi (tidak ada menu "Disposisi Saya")
        $penerimaOptions = User::whereNotNull('jabatan')
            ->whereNotIn('jabatan', self::JABATAN_BUKAN_PENERIMA)
            ->orderBy('jabatan')
            ->get(['id', 'name', 'jabatan']);

        // Surat keluar yang sudah diajukan TU dan menunggu persetujuan Kepala Sekolah
        $suratKeluarMenunggu = SuratKeluar::with(['pembuat:id,name', 'pengonfirmasi:id,name'])
            ->where('status', 'menunggu_approve')
            ->latest('id')
            ->get();

        // Pilihan alasan cepat, diatur di config/disposisi.php
        $alasanArsip = config('disposisi.alasan_arsip', []);

        // Instruksi yang terakhir dipakai Kepsek ini (tanpa duplikat), untuk pilihan cepat.
        // Instruksi yang sudah dihapus Kepsek dari daftar tidak ikut ditampilkan.
        $tersembunyi = InstruksiTersembunyi::where('user_id', auth()->id())
            ->pluck('kunci')
            ->flip();

        $saring = fn ($daftar) => collect($daftar)
            ->map(fn ($i) => InstruksiTersembunyi::rapikan($i))
            ->filter()
            ->unique(fn ($i) => mb_strtolower($i))
            ->reject(fn ($i) => $tersembunyi->has(InstruksiTersembunyi::kunci($i)))
            ->take((int) config('disposisi.jumlah_instruksi_terakhir', 6))
            ->values();

        $riwayatInstruksi = Disposisi::where('kepala_sekolah_id', auth()->id())
            ->whereNotNull('diteruskan_dari_id')
            ->latest('id')
            ->limit(100)
            ->pluck('instruksi');

        $instruksiDariRiwayat = $riwayatInstruksi->isNotEmpty();

        $instruksiTerakhir = $instruksiDariRiwayat
            ? $saring($riwayatInstruksi)
            : $saring(config('disposisi.instruksi_default', []));

        // Riwayat aktivitas Kepala Sekolah (timeline di bawah halaman)
        [$aktivitas, $ringkasanAktivitas] = $this->riwayatAktivitas(auth()->id());

        return view('admin.disposisi.index', compact(
            'penerimaOptions',
            'suratKeluarMenunggu',
            'alasanArsip',
            'instruksiTerakhir',
            'instruksiDariRiwayat',
            'aktivitas',
            'ringkasanAktivitas'
        ));
    }

    /*
     * Gabungan aktivitas Kepala Sekolah 90 hari terakhir, terbaru di atas:
     *  - disposisi yang dikirim ke staf (+ jejak dibaca / selesai / laporan)
     *  - surat masuk yang diarsipkan
     *  - surat keluar yang disetujui
     */
    private function riwayatAktivitas(int $kepsekId): array
    {
        $sejak = now()->subDays(90)->startOfDay();

        $waktu = fn ($t) => $t ? \Carbon\Carbon::parse($t) : null;

        // 1. Disposisi yang dikirim Kepsek ke staf
        $dikirim = Disposisi::with(['suratMasuk:id,nomor_surat,perihal,pengirim', 'tujuanUser:id,name,jabatan'])
            ->where('kepala_sekolah_id', $kepsekId)
            ->where('tujuan_user_id', '!=', $kepsekId)
            ->where('tanggal_disposisi', '>=', $sejak)
            ->get();

        $items = $dikirim->map(function ($d) use ($waktu) {
            $mulai = $waktu($d->tanggal_disposisi);
            $jejak = [];

            if ($d->dibaca_at) {
                $jejak[] = ['ikon' => 'fa-eye', 'warna' => 'text-primary', 'waktu' => $d->dibaca_at, 'teks' => 'Dibaca'];
            }
            if ($d->status === 'selesai') {
                $jejak[] = [
                    'ikon'    => 'fa-circle-check',
                    'warna'   => 'text-success',
                    'waktu'   => $d->selesai_at,
                    'teks'    => 'Selesai',
                    'laporan' => $d->laporan_hasil,
                    'file'    => $d->file_hasil_url,
                ];
            } elseif (in_array($d->status, ['diterima', 'diproses', 'diteruskan'], true)) {
                $jejak[] = ['ikon' => 'fa-spinner', 'warna' => 'text-warning', 'waktu' => null, 'teks' => 'Sedang ' . strtolower($d->status_label)];
            }

            $belumSelesai = ! in_array($d->status, ['selesai', 'diarsipkan', 'ditolak'], true);
            $belumDibaca  = ! $d->dibaca_at && $d->status === 'pending';

            return [
                'tipe'      => 'disposisi',
                'waktu'     => $mulai,
                'surat_id'  => $d->surat_masuk_id,
                'judul'     => $d->suratMasuk->perihal ?? '(surat dihapus)',
                'nomor'     => $d->suratMasuk->nomor_surat ?? null,
                'tujuan'    => trim(($d->tujuanUser->jabatan ?? '') . ' — ' . ($d->tujuanUser->name ?? '-'), ' —'),
                'sifat'     => $d->sifat,
                'instruksi' => $d->instruksi,
                'status'    => $d->status,
                'jejak'     => $jejak,
                'belum'     => $belumSelesai,
                // Belum dibaca lebih dari 2 hari -> ditandai merah
                'terlambat' => $belumDibaca && $mulai && $mulai->lt(now()->subDays(2)),
                'belum_dibaca' => $belumDibaca,
            ];
        });

        // 2. Surat masuk yang diarsipkan Kepsek
        $diarsipkan = Disposisi::with('suratMasuk:id,nomor_surat,perihal,pengirim')
            ->where('tujuan_user_id', $kepsekId)
            ->where('status', 'diarsipkan')
            ->where('diputuskan_at', '>=', $sejak)
            ->get()
            ->map(fn ($d) => [
                'tipe'     => 'arsip',
                'waktu'    => $d->diputuskan_at,
                'surat_id' => $d->surat_masuk_id,
                'judul'    => $d->suratMasuk->perihal ?? '(surat dihapus)',
                'nomor'    => $d->suratMasuk->nomor_surat ?? null,
                'alasan'   => $d->alasan,
                'belum'    => false,
            ]);

        // 3. Surat keluar yang disetujui Kepsek (waktu = terakhir diperbarui)
        $disetujui = SuratKeluar::where('kepala_sekolah_id', $kepsekId)
            ->where('status', 'terbit')
            ->where('updated_at', '>=', $sejak)
            ->get()
            ->map(fn ($s) => [
                'tipe'   => 'keluar',
                'waktu'  => $s->updated_at,
                'judul'  => $s->perihal,
                'nomor'  => $s->nomor_surat,
                'tujuan' => $s->tujuan,
                'pdf'    => $s->file_path ? route('surat-keluar.preview', $s->id) : null,
                'belum'  => false,
            ]);

        $aktivitas = $items->concat($diarsipkan)->concat($disetujui)
            ->filter(fn ($i) => $i['waktu'])
            ->sortByDesc('waktu')
            ->take(60)
            ->values();

        // Ringkasan bulan ini
        $bulanIni = $dikirim->filter(fn ($d) => $waktu($d->tanggal_disposisi)?->isSameMonth(now()));

        $ringkasan = [
            'disposisi'    => $bulanIni->count(),
            'selesai'      => $bulanIni->where('status', 'selesai')->count(),
            'diproses'     => $bulanIni->whereIn('status', ['diterima', 'diproses', 'diteruskan'])->count(),
            'belum_dibaca' => $bulanIni->filter(fn ($d) => ! $d->dibaca_at && $d->status === 'pending')->count(),
            'keluar'       => SuratKeluar::where('kepala_sekolah_id', $kepsekId)
                ->where('status', 'terbit')
                ->whereYear('updated_at', now()->year)
                ->whereMonth('updated_at', now()->month)
                ->count(),
        ];

        return [$aktivitas, $ringkasan];
    }

    // Detail surat masuk (nomor, perihal, url pdf, + id disposisi milik kepala sekolah) untuk modal
    public function detailSurat(SuratMasuk $suratMasuk)
    {
        // Hanya Kepala Sekolah, atau user yang menerima disposisi surat ini
        $bolehLihat = auth()->user()?->jabatan === 'Kepala Sekolah'
            || $suratMasuk->disposisi()->where('tujuan_user_id', auth()->id())->exists();
        abort_unless($bolehLihat, 403);

        $disposisiKepsek = $suratMasuk->disposisi()
            ->where('tujuan_user_id', auth()->id())
            ->latest('id')
            ->first();

        return response()->json([
            'id'            => $suratMasuk->id,
            'disposisi_id'  => $disposisiKepsek->id ?? null,
            'nomor_surat'   => $suratMasuk->nomor_surat,
            'pengirim'      => $suratMasuk->pengirim,
            'perihal'       => $suratMasuk->perihal,
            'tanggal_surat' => $suratMasuk->tanggal_surat,
            'catatan'       => $disposisiKepsek->catatan ?? null,
            'pdf_url'       => $suratMasuk->file_path ? asset('storage/' . $suratMasuk->file_path) : null,
            'status'        => $disposisiKepsek->status ?? null,
            'status_label'  => $disposisiKepsek->status_label ?? null,
            'alasan'        => $disposisiKepsek->alasan ?? null,
            'diputuskan'    => $disposisiKepsek?->diputuskan_at?->format('d-m-Y H:i'),
        ]);
    }

    // Kepala Sekolah: arsipkan surat (cukup diketahui, tidak diteruskan)
    public function putuskan(Request $request, Disposisi $disposisi)
    {
        abort_unless($disposisi->tujuan_user_id == auth()->id(), 403);

        if (! in_array($disposisi->status, ['pending', 'diterima'], true)) {
            return response()->json([
                'message' => 'Surat ini sudah diputuskan sebelumnya (' . $disposisi->status_label . ').',
            ], 422);
        }

        // Saat ini hanya "diarsipkan" (cukup diketahui). Sekolah tidak memakai penolakan surat.
        $validator = Validator::make($request->all(), [
            'keputusan' => 'required|in:diarsipkan',
            'alasan'    => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $disposisi->update([
            'status'        => $request->keputusan,
            'alasan'        => filled($request->alasan) ? trim($request->alasan) : null,
            'diputuskan_at' => now(),
            'dibaca_at'     => $disposisi->dibaca_at ?? now(),
        ]);

        return response()->json([
            'message' => 'Surat diarsipkan (cukup diketahui).',
        ]);
    }

    // Batalkan disposisi yang sudah diteruskan (hanya selama penerima belum memprosesnya)
    public function destroy(string $id)
    {
        try {
            $disposisi = Disposisi::findOrFail($id);

            if ($disposisi->kepala_sekolah_id != auth()->id()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak berhak menghapus disposisi ini.',
                ], 403);
            }

            if ($disposisi->status !== 'pending') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Disposisi yang sudah diproses atau selesai tidak bisa dihapus.',
                ], 422);
            }

            // Disposisi awal (otomatis dari Kepala TU) tidak boleh dihapus, hanya yang diteruskan
            if (! $disposisi->diteruskan_dari_id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Disposisi awal tidak bisa dihapus.',
                ], 422);
            }

            DB::transaction(function () use ($disposisi) {
                // Kembalikan disposisi asal supaya surat bisa diteruskan lagi
                // (hanya jika tidak ada disposisi turunan lain yang masih aktif)
                $adaTurunanLain = Disposisi::where('diteruskan_dari_id', $disposisi->diteruskan_dari_id)
                    ->where('id', '!=', $disposisi->id)
                    ->exists();

                if (! $adaTurunanLain) {
                    Disposisi::where('id', $disposisi->diteruskan_dari_id)->update(['status' => 'pending']);
                }

                $disposisi->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil membatalkan disposisi',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Disposisi tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus disposisi, ' . $e->getMessage()], 500);
        }
    }

    // Kepala Sekolah: hapus satu instruksi dari daftar "Terakhir Anda pakai"
    public function hapusInstruksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instruksi' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Instruksi tidak valid.'], 422);
        }

        InstruksiTersembunyi::sembunyikan(auth()->id(), $request->instruksi);

        return response()->json(['message' => 'Instruksi dihapus dari daftar.']);
    }

    // Riwayat semua disposisi untuk satu surat masuk
    public function riwayat(string $suratMasuk)
    {
        $items = Disposisi::with('tujuanUser')
            ->where('surat_masuk_id', $suratMasuk)
            ->orderBy('id')
            ->get()
            ->map(fn ($d) => [
                'tujuan'    => ($d->tujuanUser->jabatan ?? '-') . ' — ' . ($d->tujuanUser->name ?? '-'),
                'sifat'     => ucwords(str_replace('_', ' ', $d->sifat)),
                'status'    => $d->status_label,
                'instruksi' => $d->instruksi,
                'tanggal'   => $d->tanggal_disposisi
                    ? \Carbon\Carbon::parse($d->tanggal_disposisi)->format('d-m-Y H:i')
                    : null,
                'dibaca'    => $d->dibaca_at?->format('d-m-Y H:i'),
                'laporan'   => $d->laporan_hasil,
                'file_url'  => $d->file_hasil_url,
                'selesai'   => $d->selesai_at?->format('d-m-Y H:i'),
                'alasan'    => $d->alasan,
                'diputuskan'=> $d->diputuskan_at?->format('d-m-Y H:i'),
            ]);

        return response()->json(['items' => $items]);
    }

    // Kepala Sekolah: teruskan disposisi yang sudah ada (miliknya) ke staf/jabatan lain
    public function teruskan(Request $request, Disposisi $disposisi)
    {
        abort_unless($disposisi->tujuan_user_id == auth()->id(), 403);

        if (in_array($disposisi->status, ['diarsipkan', 'ditolak', 'selesai'], true)) {
            return response()->json([
                'message' => 'Surat ini sudah ' . strtolower($disposisi->status_label) . ', tidak bisa diteruskan.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'tujuan_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->whereNotIn('jabatan', self::JABATAN_BUKAN_PENERIMA)
                ),
            ],
            'sifat'          => 'required|in:sangat_segera,segera,rahasia,biasa',
            'instruksi'      => 'required|string',
            'catatan'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Instruksi yang dipakai lagi muncul kembali di daftar "Terakhir Anda pakai"
        InstruksiTersembunyi::tampilkanLagi(auth()->id(), (string) $request->instruksi);

        $baru = DB::transaction(function () use ($request, $disposisi) {
            // Kunci baris supaya klik ganda tidak membuat disposisi ganda
            $disposisi = Disposisi::lockForUpdate()->findOrFail($disposisi->id);
            abort_if(in_array($disposisi->status, ['diarsipkan', 'ditolak', 'selesai'], true), 422, 'Status disposisi sudah berubah.');

            // Tandai disposisi asal (milik kepala sekolah) sebagai sudah diteruskan
            $disposisi->update(['status' => 'diteruskan']);

            // Buat record disposisi baru untuk staf tujuan, terhubung ke disposisi asal
            return Disposisi::create([
                'surat_masuk_id'     => $disposisi->surat_masuk_id,
                'tujuan_user_id'     => $request->tujuan_user_id,
                'kepala_sekolah_id'  => auth()->id(),
                'sifat'              => $request->sifat,
                'instruksi'          => $request->instruksi,
                'catatan'            => $request->catatan,
                'status'             => 'pending',
                'tanggal_disposisi'  => now(),
                'diteruskan_dari_id' => $disposisi->id,
            ]);
        });

        return response()->json(['message' => 'Disposisi berhasil diteruskan.', 'data' => $baru]);
    }

    // Staf: lihat disposisi yang ditujukan ke dirinya
// =========================================================
// DISPOSISI SAYA + SURAT KELUAR MENUNGGU PERSETUJUAN
// =========================================================

public function myDisposisi()
{
    $disposisi = Disposisi::with('suratMasuk')
        ->where('tujuan_user_id', auth()->id())
        ->latest('id')
        ->get();

    $suratKeluarMenunggu = collect();

    if (auth()->user()?->jabatan === 'Kepala Sekolah') {
        $suratKeluarMenunggu = SuratKeluar::with(['pembuat:id,name', 'pengonfirmasi:id,name'])->where(
            'status',
            'menunggu_approve'
        )
            ->latest('id')
            ->get();
    }

    return view('admin.disposisi.saya', compact(
        'disposisi',
        'suratKeluarMenunggu'
    ));
}
    // Staf: update status tindak lanjut
    // Alur: pending → diterima → diproses → selesai (wajib laporan hasil)
    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        abort_unless($disposisi->tujuan_user_id == auth()->id(), 403);

        $alurBerikutnya = [
            'pending'  => 'diterima',
            'diterima' => 'diproses',
            'diproses' => 'selesai',
        ];

        $validator = Validator::make($request->all(), [
            'status'        => 'required|in:diterima,diproses,selesai',
            'laporan_hasil' => 'required_if:status,selesai|nullable|string|min:5|max:5000',
            'file_hasil'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ], [
            'laporan_hasil.required_if' => 'Laporan hasil wajib diisi saat menandai selesai.',
            'laporan_hasil.min'         => 'Laporan hasil minimal 5 karakter.',
            'file_hasil.mimes'          => 'Lampiran harus berupa PDF, gambar, atau dokumen Word.',
            'file_hasil.max'            => 'Ukuran lampiran maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $statusBaru = $request->status;

        if (($alurBerikutnya[$disposisi->status] ?? null) !== $statusBaru) {
            return response()->json([
                'message' => 'Status tidak bisa diubah dari "' . $disposisi->status_label
                    . '" ke "' . (Disposisi::LABEL_STATUS[$statusBaru] ?? $statusBaru) . '".',
            ], 422);
        }

        $data = ['status' => $statusBaru];

        // Menerima/memproses berarti sudah dibaca
        if (! $disposisi->dibaca_at) {
            $data['dibaca_at'] = now();
        }

        if ($statusBaru === 'selesai') {
            $data['laporan_hasil'] = trim($request->laporan_hasil);
            $data['selesai_at']    = now();

            if ($request->hasFile('file_hasil')) {
                $data['file_hasil'] = $request->file('file_hasil')
                    ->store('disposisi-hasil', 'public');
            }
        }

        $disposisi->update($data);

        $pesan = [
            'diterima' => 'Disposisi diterima.',
            'diproses' => 'Disposisi mulai diproses.',
            'selesai'  => 'Disposisi ditandai selesai. Laporan terkirim ke Kepala Sekolah.',
        ];

        return response()->json(['message' => $pesan[$statusBaru]]);
    }

    // Staf: tandai disposisi sudah dibaca (dipanggil saat detail dibuka)
    public function tandaiDibaca(Disposisi $disposisi)
    {
        abort_unless($disposisi->tujuan_user_id == auth()->id(), 403);

        if (! $disposisi->dibaca_at) {
            $disposisi->update(['dibaca_at' => now()]);
        }

        return response()->json(['message' => 'ok']);
    }

    public function notifikasi()
    {
        $unreadCount = Disposisi::where('tujuan_user_id', auth()->id())
            ->whereNull('dibaca_at')
            ->count();

        $terbaru = Disposisi::with('suratMasuk')
            ->where('tujuan_user_id', auth()->id())
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($d) {
                return [
                    'id'         => $d->id,
                    'perihal'    => $d->suratMasuk->perihal ?? '-',
                    'sifat'      => $d->sifat,
                    'sudah_baca' => (bool) $d->dibaca_at,
                    'waktu'      => $d->created_at?->diffForHumans() ?? '-',
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'items'        => $terbaru,
        ]);
    }
}
