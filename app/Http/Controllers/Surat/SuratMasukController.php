<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Disposisi;
use App\Models\User;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SuratMasuk::with(['disposisi', 'pengonfirmasi:id,name'])->latest('id')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('prioritas', function ($row) {
                    $disposisi = $row->disposisi->last();
                    if (!$disposisi) return '-';

                    $map = [
                        'sangat_segera' => ['Tinggi', 'danger'],
                        'segera'        => ['Sedang', 'warning'],
                        'rahasia'       => ['Tinggi', 'danger'],
                        'biasa'         => ['Rendah', 'secondary'],
                    ];
                    [$label, $color] = $map[$disposisi->sifat] ?? ['-', 'secondary'];

                    return '<span class="text-' . $color . '"><i class="fa fa-flag"></i> ' . $label . '</span>';
                })
                ->addColumn('status_disposisi', function ($row) {
                    // Belum dikonfirmasi Kepala Tata Usaha
                    if ($row->status_konfirmasi === 'menunggu') {
                        return '<span class="badge-status badge-belum"><i class="fa fa-hourglass-half"></i> Menunggu Kepala TU</span>';
                    }
                    if ($row->status_konfirmasi === 'dikembalikan') {
                        return '<span class="badge-status badge-ditolak" title="' . e($row->alasan_konfirmasi) . '"><i class="fa fa-rotate-left"></i> Dikembalikan</span>'
                            . '<div class="small text-danger mt-1" style="max-width:220px; white-space:normal;">' . e($row->alasan_konfirmasi) . '</div>';
                    }

                    $disposisi = $row->disposisi->sortByDesc('id')->first();

                    if (!$disposisi) {
                        return '<span class="badge-status badge-belum">Belum Disposisi</span>';
                    }

                    $map = [
                        'pending'    => ['Belum Disposisi', 'badge-belum'],
                        'diterima'   => ['Diproses', 'badge-diproses'],
                        'diproses'   => ['Diproses', 'badge-diproses'],
                        'diteruskan' => ['Diproses', 'badge-diproses'],
                        'selesai'    => ['Selesai', 'badge-selesai'],
                        'diarsipkan' => ['Diarsipkan', 'badge-arsip'],
                        'ditolak'    => ['Ditolak', 'badge-ditolak'],
                    ];
                    [$label, $class] = $map[$disposisi->status] ?? ['-', 'badge-belum'];

                    return '<span class="badge-status ' . $class . '">' . $label . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    // Keputusan Kepala Sekolah ikut tampil di modal detail TU
                    $keputusan = $row->disposisi
                        ->whereIn('status', ['diarsipkan', 'ditolak'])
                        ->sortByDesc('id')
                        ->first();

                    $data = $row->toArray();
                    $data['konfirmasi_kepala_tu'] = match ($row->status_konfirmasi) {
                        'disetujui'    => 'Disetujui' . ($row->pengonfirmasi ? ' oleh ' . $row->pengonfirmasi->name : '')
                            . ($row->dikonfirmasi_at ? ' (' . $row->dikonfirmasi_at->format('d-m-Y H:i') . ')' : ''),
                        'dikembalikan' => 'Dikembalikan — ' . $row->alasan_konfirmasi,
                        default        => 'Menunggu konfirmasi Kepala TU',
                    };
                    unset($data['pengonfirmasi']);
                    $data['keputusan_kepsek'] = $keputusan
                        ? $keputusan->status_label . ($keputusan->alasan ? ' — ' . $keputusan->alasan : '')
                        : '-';
                    unset($data['disposisi']);

                    $detail = htmlspecialchars(json_encode($data));

                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="javascript:void(0)" class="btn-detail btn btn-sm btn-light" data-detail="' . $detail . '" title="Lihat"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="javascript:void(0)" class="btn-update btn btn-sm btn-light" data-detail="' . $detail . '" data-id="' . $row->id . '" title="Ubah"><i class="fa fa-pen"></i></a>';
                    $btn .= '<a href="javascript:void(0)" class="btn-delete btn btn-sm btn-light text-danger" data-id="' . $row->id . '" title="Hapus"><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['prioritas', 'status_disposisi', 'actions'])
                ->make(true);
        }

        return view('admin.surat-masuk.index', [
            'title'    => 'Surat Masuk',
            'subtitle' => 'Kelola dan pantau surat yang diterima oleh sekolah.',
        ]);
    }

    public function create() {}

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nomor_surat'          => 'required|string|max:255',
        'perihal'              => 'required|string|max:255',
        'pengirim'             => 'required|string|max:255',
        'alamat_asal_pengirim' => 'nullable|string',
        'tanggal_surat'        => 'required|date',
        'tanggal_terima'       => 'required|date',
        // No. Agenda tidak diisi TU: dibuat otomatis saat disimpan
        'file_surat'           => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
    }

    try {
        $data = $validator->validated();

        if ($request->hasFile('file_surat')) {
            $data['file_path'] = $request->file('file_surat')->store('surat-masuk', 'local');
        }
        unset($data['file_surat']);

        // Surat masuk menunggu konfirmasi Kepala Tata Usaha dulu.
        // Disposisi ke Kepala Sekolah dibuat setelah Kepala TU menyetujui
        // (lihat KonfirmasiSuratController).
        $data['status_konfirmasi'] = 'menunggu';

        // No. Agenda otomatis. Kolom no_agenda UNIK di database: kalau dua TU menyimpan
        // bersamaan dan dapat nomor sama, yang kalah dicoba lagi dengan nomor berikutnya.
        $surat = null;

        for ($coba = 1; $coba <= 5 && ! $surat; $coba++) {
            $data['no_agenda'] = SuratMasuk::nomorAgendaBerikutnya($data['tanggal_terima']);

            try {
                $surat = SuratMasuk::create($data);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($coba === 5) {
                    throw $e;
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Surat masuk disimpan dengan No. Agenda ' . $surat->no_agenda . ' dan menunggu konfirmasi Kepala Tata Usaha.',
            'data'    => ['no_agenda' => $surat->no_agenda],
        ], 201);
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Gagal simpan surat masuk, ' . $e->getMessage(), 'data' => null], 500);
    }
}

    /*
     * GET /surat-masuk-agenda-berikutnya?tanggal_terima=2026-10-21
     * Perkiraan No. Agenda untuk ditampilkan di form Tambah.
     * Nomor final tetap ditentukan saat surat disimpan.
     */
    public function agendaBerikutnya(Request $request)
    {
        $tanggal = $request->query('tanggal_terima');

        try {
            $nomor = SuratMasuk::nomorAgendaBerikutnya($tanggal ?: null);
        } catch (\Throwable $e) {
            $nomor = SuratMasuk::nomorAgendaBerikutnya();
        }

        return response()->json(['no_agenda' => $nomor]);
    }

    public function show(string $id) {}
    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nomor_surat'          => 'required|string|max:255',
            'perihal'              => 'required|string|max:255',
            'pengirim'             => 'required|string|max:255',
            'alamat_asal_pengirim' => 'nullable|string',
            'tanggal_surat'        => 'required|date',
            'tanggal_terima'       => 'required|date',
            // No. Agenda tidak bisa diubah setelah tercatat
            'file_surat'           => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $surat = SuratMasuk::findOrFail($id);
            $data = $validator->validated();

            if (! in_array($surat->status_konfirmasi, ['menunggu', 'dikembalikan'], true)) {
                return response()->json(['status' => 'error', 'message' => 'Surat yang sudah disetujui Kepala Tata Usaha tidak bisa diubah.', 'data' => null], 422);
            }

            if ($request->hasFile('file_surat')) {
                if ($surat->file_path) {
                    Storage::disk('local')->delete($surat->file_path);
                }
                $data['file_path'] = $request->file('file_surat')->store('surat-masuk', 'local');
            }
            unset($data['file_surat']);

            // Surat yang dikembalikan Kepala TU: setelah diperbaiki, otomatis diajukan lagi
            $diajukanLagi = $surat->status_konfirmasi === 'dikembalikan';

            if ($diajukanLagi) {
                $data['status_konfirmasi'] = 'menunggu';
            }

            $surat->update($data);

            $pesan = $diajukanLagi
                ? 'Surat masuk diperbaiki dan dikirim ulang ke Kepala Tata Usaha.'
                : 'Berhasil ubah surat masuk';

            return response()->json(['status' => 'success', 'message' => $pesan, 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat masuk tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal ubah surat masuk, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $surat = SuratMasuk::findOrFail($id);

            if (! in_array($surat->status_konfirmasi, ['menunggu', 'dikembalikan'], true)) {
                return response()->json(['status' => 'error', 'message' => 'Surat yang sudah disetujui Kepala Tata Usaha tidak bisa dihapus.', 'data' => null], 422);
            }

            if ($surat->file_path) {
                Storage::disk('local')->delete($surat->file_path);
            }
            $surat->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus surat masuk', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat masuk tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus surat masuk, ' . $e->getMessage(), 'data' => null], 500);
        }
    }
}
