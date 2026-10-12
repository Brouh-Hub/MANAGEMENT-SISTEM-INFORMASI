<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kerjasama;
use App\Models\Mitra;
use App\Models\Pkl;
use App\Models\Siswa;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MitraController extends Controller
{
    private function rules(): array
    {
        return [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat'          => 'nullable|string',
            'contact_person'  => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|url|max:255',
            'bidang'          => 'required|string|max:255',
            'foto'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * =========================================================
     * DAFTAR MITRA
     * =========================================================
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

        $data = Mitra::withCount('kerjasama')
            ->with([
                'kerjasama' => function ($query) {
                    $query->withCount('pkl');
                }
            ])
            ->orderByDesc('id')
            ->get();

            return DataTables::of($data)

                ->addIndexColumn()

                ->addColumn('foto_preview', function ($row) {
                    return $row->foto_url
                        ? '<img src="' . e($row->foto_url) . '"
                                alt="foto"
                                style="width:48px;height:48px;object-fit:cover;border-radius:8px;">'
                        : '<span class="text-muted small">-</span>';
                })

                ->addColumn('jumlah_kerjasama', function ($row) {
                    return '<span class="badge bg-primary">'
                        . $row->kerjasama_count
                        . ' kerja sama</span>';
                })

                ->addColumn('pkl', function ($row) {
                $jumlah = $row->kerjasama->sum('pkl_count');

                return '<span class="badge bg-success">'
                    . $jumlah
                    . ' siswa PKL</span>';
                })

                ->addColumn('actions', function ($row) {

    $id = $row->id;

    return '
        <div class="btn-group">

            <button
                type="button"
                class="btn btn-sm btn-light btn-detail-pkl"
                data-id="' . $id . '"
                title="Peserta PKL">
                <i class="fa fa-users"></i>
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
                    'foto_preview',
                    'jumlah_kerjasama',
                    'pkl',
                    'actions',
                ])

                ->make(true);
        }

        return view('admin.mitra.index', [
            'title'    => 'Mitra Industri',
            'subtitle' => 'Kelola data kerjasama sekolah dengan dunia industri & dunia kerja (DUDI).',
        ]);
    }

    /**
     * =========================================================
     * SIMPAN MITRA
     * =========================================================
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->rules()
        );

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {

            $data = $validator->validated();

            $data['humas_id'] = auth()->id();

            if ($request->hasFile('foto')) {
                $data['foto'] = $request
                    ->file('foto')
                    ->store('mitra', 'public');
            }

            Mitra::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil simpan data mitra',
                'data'    => null,
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal simpan data mitra, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * =========================================================
     * UPDATE MITRA
     * =========================================================
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            $this->rules()
        );

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {

            $mitra = Mitra::findOrFail($id);

            $data = $validator->validated();

            if ($request->hasFile('foto')) {

                if ($mitra->foto) {
                    Storage::disk('public')
                        ->delete($mitra->foto);
                }

                $data['foto'] = $request
                    ->file('foto')
                    ->store('mitra', 'public');

            } else {

                unset($data['foto']);
            }

            $mitra->update($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil ubah data mitra',
                'data'    => null,
            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data mitra tidak ditemukan',
                'data'    => null,
            ], 404);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal ubah data mitra, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * =========================================================
     * HAPUS MITRA
     * =========================================================
     */
    public function destroy(string $id)
    {
        try {

            $mitra = Mitra::findOrFail($id);

            if ($mitra->foto) {
                Storage::disk('public')
                    ->delete($mitra->foto);
            }

            $mitra->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil hapus data mitra',
                'data'    => null,
            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data mitra tidak ditemukan',
                'data'    => null,
            ], 404);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal hapus data mitra, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR PESERTA PKL
    |--------------------------------------------------------------------------
    */

    /**
     * =========================================================
     * AMBIL DATA PKL MILIK MITRA
     *
     * GET /admin/mitra/{id}/pkl
     * =========================================================
     */
    public function pesertaPkl(string $id)
    {
        try {

            $mitra = Mitra::with([
                'kerjasama' => function ($query) {
                    $query->with([
                        'pkl.siswa'
                    ])->orderByDesc('id');
                }
            ])->findOrFail($id);

            $data = [];

            foreach ($mitra->kerjasama as $kerjasama) {

                foreach ($kerjasama->pkl as $pkl) {

                    $data[] = [
                        'pkl_id' => $pkl->id,

                        'kerjasama_id' => $kerjasama->id,

                        'judul_kerjasama' =>
                            $kerjasama->judul_kerjasama,

                        'nis' =>
                            $pkl->siswa->nis ?? '-',

                        'nama' =>
                            $pkl->siswa->nama ?? '-',

                        'kelas' =>
                            $pkl->siswa->kelas ?? '-',

                        'jurusan' =>
                            $pkl->siswa->jurusan ?? '-',

                        'tanggal_mulai' =>
                            $pkl->tanggal_mulai
                                ? $pkl->tanggal_mulai->format('Y-m-d')
                                : null,

                        'tanggal_selesai' =>
                            $pkl->tanggal_selesai
                                ? $pkl->tanggal_selesai->format('Y-m-d')
                                : null,

                        'tanggal_mulai_format' =>
                            $pkl->tanggal_mulai
                                ? $pkl->tanggal_mulai->format('d/m/Y')
                                : '-',

                        'tanggal_selesai_format' =>
                            $pkl->tanggal_selesai
                                ? $pkl->tanggal_selesai->format('d/m/Y')
                                : '-',

                        'status' =>
                            $pkl->status,

                        'status_badge' =>
                            $pkl->status_badge,

                        'keterangan' =>
                            $pkl->keterangan,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'mitra' => [
                    'id' => $mitra->id,
                    'nama_perusahaan' => $mitra->nama_perusahaan,
                    'bidang' => $mitra->bidang,
                ],
                'kerjasama' => $mitra->kerjasama->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'judul_kerjasama' => $item->judul_kerjasama,
                        'tanggal_mulai' => $item->tanggal_mulai
                            ? $item->tanggal_mulai->format('Y-m-d')
                            : null,
                        'tanggal_selesai' => $item->tanggal_selesai
                            ? $item->tanggal_selesai->format('Y-m-d')
                            : null,
                    ];
                })->values(),

                'data' => $data,
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Data mitra tidak ditemukan.',
            ], 404);
        }
    }



    /**
     * =========================================================
     * DAFTAR SISWA YANG BISA DIPILIH
     *
     * GET /admin/mitra/{mitraId}/pkl/siswa
     *
     * ?kerjasama_id=1
     * =========================================================
     */
    public function daftarSiswaPkl(
        Request $request,
        string $mitraId
    ) {
        try {

            Mitra::findOrFail($mitraId);

            $request->validate([
                'kerjasama_id' =>
                    'required|exists:kerjasama,id',
            ]);

            $kerjasama = Kerjasama::where('id', $request->kerjasama_id)
                ->where('industri_id', $mitraId)
                ->firstOrFail();

            $siswaIds = Pkl::where(
                'kerjasama_id',
                $kerjasama->id
            )
                ->pluck('siswa_id');

            $siswa = Siswa::query()
                ->when(
                    $request->filled('search'),
                    function ($query) use ($request) {

                        $search = $request->search;

                        $query->where(function ($q) use ($search) {

                            $q->where('nis', 'like', "%{$search}%")
                                ->orWhere('nama', 'like', "%{$search}%")
                                ->orWhere('kelas', 'like', "%{$search}%")
                                ->orWhere('jurusan', 'like', "%{$search}%");

                        });
                    }
                )
                ->whereNotIn('id', $siswaIds)
                ->orderBy('nama')
                ->get([
                    'id',
                    'nis',
                    'nama',
                    'kelas',
                    'jurusan',
                ]);

            return response()->json([
                'status' => 'success',
                'data' => $siswa,
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Data mitra atau kerja sama tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * =========================================================
     * TAMBAH PESERTA PKL
     *
     * POST /admin/mitra/{mitraId}/pkl
     * =========================================================
     */
    public function tambahPesertaPkl(
        Request $request,
        string $mitraId
    ) {
        $validator = Validator::make(
            $request->all(),
            [
                'kerjasama_id' =>
                    'required|exists:kerjasama,id',

                'siswa_id' =>
                    'required|exists:siswa,id',

                'tanggal_mulai' =>
                    'required|date',

                'tanggal_selesai' =>
                    'required|date|after_or_equal:tanggal_mulai',

                'keterangan' =>
                    'nullable|string',
            ],
            [
                'kerjasama_id.required' =>
                    'Kerja sama wajib dipilih.',

                'kerjasama_id.exists' =>
                    'Kerja sama tidak ditemukan.',

                'siswa_id.required' =>
                    'Siswa wajib dipilih.',

                'siswa_id.exists' =>
                    'Siswa tidak ditemukan.',

                'tanggal_mulai.required' =>
                    'Tanggal mulai wajib diisi.',

                'tanggal_selesai.required' =>
                    'Tanggal selesai wajib diisi.',

                'tanggal_selesai.after_or_equal' =>
                    'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => 'Data belum lengkap.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $kerjasama = Kerjasama::where('id', $request->kerjasama_id)
                ->where('industri_id', $mitraId)
                ->firstOrFail();

            $siswa = Siswa::findOrFail(
                $request->siswa_id
            );

            /*
             * Cek apakah siswa sudah terdaftar
             * pada kerja sama ini.
             */
            $sudahAda = Pkl::where(
                'kerjasama_id',
                $kerjasama->id
            )
                ->where(
                    'siswa_id',
                    $siswa->id
                )
                ->exists();

            if ($sudahAda) {

                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'Siswa tersebut sudah terdaftar sebagai peserta PKL pada kerja sama ini.',
                ], 422);
            }

            $pkl = Pkl::create([
                'kerjasama_id' =>
                    $kerjasama->id,

                'siswa_id' =>
                    $siswa->id,

                'tanggal_mulai' =>
                    $request->tanggal_mulai,

                'tanggal_selesai' =>
                    $request->tanggal_selesai,

                'keterangan' =>
                    $request->keterangan,
            ]);

            $pkl->load([
                'siswa',
                'kerjasama',
            ]);

            return response()->json([
                'status' => 'success',
                'message' =>
                    'Peserta PKL berhasil ditambahkan.',
                'data' => [
                    'id' => $pkl->id,
                    'nis' => $pkl->siswa->nis,
                    'nama' => $pkl->siswa->nama,
                    'kerjasama' =>
                        $pkl->kerjasama->judul_kerjasama,
                ],
            ], 201);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status' => 'error',
                'message' =>
                    'Mitra atau kerja sama tidak ditemukan.',
            ], 404);

        } catch (Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' =>
                    'Gagal menambahkan peserta PKL, '
                    . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================================================
     * HAPUS PESERTA PKL
     *
     * DELETE /admin/mitra/{mitraId}/pkl/{pklId}
     * =========================================================
     */
    public function hapusPesertaPkl(
        string $mitraId,
        string $pklId
    ) {
        try {

            Mitra::findOrFail($mitraId);

            $pkl = Pkl::with('kerjasama')
                ->findOrFail($pklId);

            /*
             * Pastikan PKL tersebut memang milik
             * kerja sama dari mitra yang sedang dibuka.
             */
            if (
                !$pkl->kerjasama ||
                (string) $pkl->kerjasama->industri_id !==
                (string) $mitraId
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'Peserta PKL tidak terkait dengan mitra ini.',
                ], 422);
            }

            $pkl->delete();

            return response()->json([
                'status' => 'success',
                'message' =>
                    'Peserta PKL berhasil dihapus.',
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status' => 'error',
                'message' =>
                    'Data peserta PKL tidak ditemukan.',
            ], 404);

        } catch (Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' =>
                    'Gagal menghapus peserta PKL, '
                    . $e->getMessage(),
            ], 500);
        }
    }


}
