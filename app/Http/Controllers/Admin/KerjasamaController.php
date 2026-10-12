<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kerjasama;
use App\Models\Mitra;
use App\Models\Pkl;
use App\Models\Siswa;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KerjasamaController extends Controller
{
    /**
     * DAFTAR KERJA SAMA
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Kerjasama::with('mitra')
                ->withCount('pkl')
                ->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('mitra_nama', function ($row) {
                    return $row->mitra->nama_perusahaan ?? '-';
                })

                ->addColumn('periode', function ($row) {
                    $mulai = $row->tanggal_mulai
                        ? Carbon::parse($row->tanggal_mulai)->format('d/m/Y')
                        : '-';

                    $selesai = $row->tanggal_selesai
                        ? Carbon::parse($row->tanggal_selesai)->format('d/m/Y')
                        : '-';

                    return $mulai . ' - ' . $selesai;
                })

                ->addColumn('status_badge', function ($row) {

                    $status = $row->status ?? 'draft';

                    $badge = match ($status) {
                        'aktif' => 'success',
                        'selesai' => 'secondary',
                        'menunggu_approve',
                        'menunggu_persetujuan' => 'warning',
                        'tertunda' => 'danger',
                        default => 'dark',
                    };

                    return '<span class="badge bg-' . $badge . '">'
                        . e(ucwords(str_replace('_', ' ', $status)))
                        . '</span>';
                })

                ->addColumn('jumlah_pkl', function ($row) {
                    return '<span class="badge bg-primary">'
                        . $row->pkl_count
                        . ' siswa</span>';
                })

                ->addColumn('actions', function ($row) {

                    $id = $row->id;

                    return '
                        <div class="btn-group">

                            <button
                                type="button"
                                class="btn btn-sm btn-light btn-peserta"
                                data-id="' . $id . '"
                                title="Peserta PKL">
                                <i class="fa fa-users"></i>
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-light btn-edit"
                                data-id="' . $id . '"
                                title="Ubah">
                                <i class="fa fa-pen"></i>
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-light text-danger btn-delete"
                                data-id="' . $id . '"
                                title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>

                        </div>
                    ';
                })

                ->rawColumns([
                    'status_badge',
                    'jumlah_pkl',
                    'actions',
                ])

                ->make(true);
        }

        return view('admin.kerjasama.index', [
            'title' => 'Kerja Sama Industri',
            'subtitle' => 'Kelola kerja sama industri dan peserta PKL.',
            'mitras' => Mitra::orderBy('nama_perusahaan')->get(),
        ]);
    }

    /**
     * SIMPAN KERJA SAMA
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_kerjasama' => 'required|string|max:255',
            'industri_id' => 'required|exists:mitras,id',
            'jenis' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|string|max:50',
            'file_mou_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data belum lengkap.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $data = $validator->validated();

            $data['humas_id'] = auth()->id();

            if ($request->hasFile('file_mou_path')) {
                $data['file_mou_path'] =
                    $request->file('file_mou_path')
                        ->store('mou', 'public');
            }

            Kerjasama::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Data kerja sama berhasil disimpan.',
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan kerja sama: '
                    . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DETAIL KERJA SAMA
     */
    public function show(string $id)
    {
        $kerjasama = Kerjasama::with([
            'mitra',
            'pkl.siswa',
        ])->findOrFail($id);

        return response()->json($kerjasama);
    }

    /**
     * UPDATE KERJA SAMA
     */
    public function update(Request $request, string $id)
    {
        $kerjasama = Kerjasama::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'judul_kerjasama' => 'required|string|max:255',
            'industri_id' => 'required|exists:mitras,id',
            'jenis' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|string|max:50',
            'file_mou_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data belum lengkap.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $data = $validator->validated();

            if ($request->hasFile('file_mou_path')) {
                $data['file_mou_path'] =
                    $request->file('file_mou_path')
                        ->store('mou', 'public');
            } else {
                unset($data['file_mou_path']);
            }

            $kerjasama->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Data kerja sama berhasil diperbarui.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui kerja sama: '
                    . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HAPUS KERJA SAMA
     */
    public function destroy(string $id)
    {
        try {

            $kerjasama = Kerjasama::findOrFail($id);

            Pkl::where(
                'kerjasama_id',
                $kerjasama->id
            )->delete();

            $kerjasama->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kerja sama berhasil dihapus.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus kerja sama: '
                    . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DAFTAR PESERTA PKL
     */
    public function peserta(string $id)
    {
        $kerjasama = Kerjasama::with('mitra')
            ->findOrFail($id);

        $peserta = Pkl::with('siswa')
            ->where('kerjasama_id', $id)
            ->orderBy('tanggal_mulai')
            ->get();

        return response()->json([
            'kerjasama' => $kerjasama,
            'peserta' => $peserta,
        ]);
    }

    /**
     * TAMBAH SISWA PKL
     */
    public function tambahPeserta(Request $request, string $id)
    {
        $kerjasama = Kerjasama::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data peserta belum lengkap.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $sudahAda = Pkl::where('kerjasama_id', $id)
            ->where('siswa_id', $request->siswa_id)
            ->exists();

        if ($sudahAda) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tersebut sudah terdaftar pada kerja sama ini.',
            ], 422);
        }

        $pkl = Pkl::create([
            'kerjasama_id' => $kerjasama->id,
            'siswa_id' => $request->siswa_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Peserta PKL berhasil ditambahkan.',
            'data' => $pkl->load('siswa'),
        ], 201);
    }

    /**
     * HAPUS PESERTA PKL
     */
    public function hapusPeserta(
        string $kerjasamaId,
        string $pklId
    ) {
        $pkl = Pkl::where('id', $pklId)
            ->where('kerjasama_id', $kerjasamaId)
            ->firstOrFail();

        $pkl->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Peserta PKL berhasil dihapus.',
        ]);
    }

    /**
     * DATA SISWA UNTUK DROPDOWN
     */
    public function daftarSiswa()
    {
        return response()->json(
            Siswa::orderBy('nama')
                ->get([
                    'id',
                    'nis',
                    'nama',
                    'kelas',
                    'jurusan',
                ])
                ->map(function ($siswa) {

                    return [
                        'id' => $siswa->id,
                        'nis' => $siswa->nis,
                        'nama' => $siswa->nama,
                        'kelas' => $siswa->kelas,
                        'jurusan' => $siswa->jurusan,
                        'konsentrasi' =>
                            Siswa::deteksiJurusan(
                                $siswa->jurusan,
                                $siswa->kelas
                            ) ?? $siswa->jurusan,
                    ];
                })
        );
    }
}
