<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Disposisi;
use App\Models\User;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SuratMasuk::with('disposisi')->latest()->get();

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
                    $disposisi = $row->disposisi->last();

                    if (!$disposisi) {
                        return '<span class="badge-status badge-belum">Belum Disposisi</span>';
                    }

                    $map = [
                        'pending'  => ['Belum Disposisi', 'badge-belum'],
                        'diproses' => ['Diproses', 'badge-diproses'],
                        'selesai'  => ['Selesai', 'badge-selesai'],
                    ];
                    [$label, $class] = $map[$disposisi->status] ?? ['-', 'badge-belum'];

                    return '<span class="badge-status ' . $class . '">' . $label . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $detail = htmlspecialchars(json_encode($row));

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
        'no_agenda'            => 'required|string|max:255',
        'file_surat'           => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
    }

    try {
        $data = $validator->validated();

        if ($request->hasFile('file_surat')) {
            $data['file_path'] = $request->file('file_surat')->store('surat-masuk', 'public');
        }
        unset($data['file_surat']);

        $surat = SuratMasuk::create($data);

        // Otomatis disposisikan ke Kepala Sekolah begitu surat masuk dibuat
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

        return response()->json(['status' => 'success', 'message' => 'Berhasil simpan surat masuk', 'data' => null], 201);
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Gagal simpan surat masuk, ' . $e->getMessage(), 'data' => null], 500);
    }
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
            'no_agenda'            => 'required|string|max:255',
            'file_surat'           => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $surat = SuratMasuk::findOrFail($id);
            $data = $validator->validated();

            if ($request->hasFile('file_surat')) {
                $data['file_path'] = $request->file('file_surat')->store('surat-masuk', 'public');
            }
            unset($data['file_surat']);

            $surat->update($data);

            return response()->json(['status' => 'success', 'message' => 'Berhasil ubah surat masuk', 'data' => null], 200);
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
            $surat->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus surat masuk', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Surat masuk tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus surat masuk, ' . $e->getMessage(), 'data' => null], 500);
        }
    }
}
