<?php

namespace App\Http\Controllers\Surat;

use App\Models\SuratKeluar;
use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class DisposisiController extends Controller
{
    // List surat masuk yang perlu didisposisikan (kepala sekolah)
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SuratMasuk::with('disposisi')->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_disposisi', function ($row) {
                    $terakhir = $row->disposisi->sortByDesc('id')->first();
                    $status   = $terakhir->status ?? null;

                    return match ($status) {
                        'selesai'                => '<span class="badge-status badge-selesai">Selesai</span>',
                        'diproses', 'diteruskan' => '<span class="badge-status badge-diproses">Diproses</span>',
                        'pending'                => '<span class="badge-status badge-belum">Menunggu</span>',
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

        $penerimaOptions = User::whereNotNull('jabatan')->orderBy('jabatan')->get(['id', 'name', 'jabatan']);

        // Surat keluar yang sudah diajukan TU dan menunggu persetujuan Kepala Sekolah
        $suratKeluarMenunggu = SuratKeluar::where('status', 'menunggu_approve')
            ->latest('id')
            ->get();

        return view('admin.disposisi.index', compact('penerimaOptions', 'suratKeluarMenunggu'));
    }

    // Detail surat masuk (nomor, perihal, url pdf, + id disposisi milik kepala sekolah) untuk modal
    public function detailSurat(SuratMasuk $suratMasuk)
    {
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
            'pdf_url'       => asset('storage/' . $suratMasuk->file_path),
        ]);
    }

    // Simpan disposisi baru langsung (dipakai kalau belum ada disposisi otomatis ke kepsek sama sekali)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surat_masuk_id'   => 'required|exists:surat_masuk,id',
            'tujuan_disposisi' => 'required|exists:users,id',
            'sifat'            => 'required|in:sangat_segera,segera,rahasia,biasa',
            'instruksi'        => 'required|string',
            'catatan'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Disposisi::create([
            'surat_masuk_id'    => $request->surat_masuk_id,
            'tujuan_user_id'    => $request->tujuan_disposisi,
            'kepala_sekolah_id' => auth()->id(),
            'sifat'             => $request->sifat,
            'instruksi'         => $request->instruksi,
            'catatan'           => $request->catatan,
            'status'            => 'pending',
            'tanggal_disposisi' => now(),
        ]);

        return response()->json(['message' => 'Disposisi berhasil dikirim.']);
    }

    // Update disposisi (misal kepala sekolah revisi instruksi/tujuan sebelum diproses staf)
    public function update(Request $request, Disposisi $disposisi)
    {
        $validator = Validator::make($request->all(), [
            'tujuan_disposisi' => 'required|exists:users,id',
            'sifat'            => 'required|in:sangat_segera,segera,rahasia,biasa',
            'instruksi'        => 'required|string',
            'catatan'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $disposisi->update([
            'tujuan_user_id' => $request->tujuan_disposisi,
            'sifat'          => $request->sifat,
            'instruksi'      => $request->instruksi,
            'catatan'        => $request->catatan,
            'status'         => 'diproses', // tandai sudah didisposisikan Kepsek
        ]);

        return response()->json(['message' => 'Disposisi berhasil diperbarui.']);
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

            DB::transaction(function () use ($disposisi) {
                // Kembalikan disposisi asal supaya surat bisa diteruskan lagi
                if ($disposisi->diteruskan_dari_id) {
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

    // Riwayat semua disposisi untuk satu surat masuk
    public function riwayat(string $suratMasuk)
    {
        $items = Disposisi::with('tujuanUser')
            ->where('surat_masuk_id', $suratMasuk)
            ->orderBy('id')
            ->get()
            ->map(fn ($d) => [
                'tujuan'    => ($d->tujuanUser->jabatan ?? '-') . ' — ' . ($d->tujuanUser->name ?? '-'),
                'sifat'     => $d->sifat,
                'status'    => $d->status,
                'instruksi' => $d->instruksi,
                'tanggal'   => $d->tanggal_disposisi,
            ]);

        return response()->json(['items' => $items]);
    }

    // Kepala Sekolah: teruskan disposisi yang sudah ada (miliknya) ke staf/jabatan lain
    public function teruskan(Request $request, Disposisi $disposisi)
    {
        abort_unless($disposisi->tujuan_user_id == auth()->id(), 403);

        $validator = Validator::make($request->all(), [
            'tujuan_user_id' => 'required|exists:users,id',
            'sifat'          => 'required|in:sangat_segera,segera,rahasia,biasa',
            'instruksi'      => 'required|string',
            'catatan'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $baru = DB::transaction(function () use ($request, $disposisi) {
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
        $suratKeluarMenunggu = SuratKeluar::where(
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
    // Staf: update status jadi diproses/selesai
    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        abort_unless($disposisi->tujuan_user_id == auth()->id(), 403);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:diproses,selesai',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $disposisi->update(['status' => $request->status]);

        return response()->json(['message' => 'Status diperbarui.']);
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