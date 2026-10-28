<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('role_badge', function ($row) {
                    $color = $row->role === 'super_admin' ? 'primary' : 'secondary';
                    $label = $row->role === 'super_admin' ? 'Super Admin' : 'Admin';
                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $detail = htmlspecialchars(json_encode($row));

                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="javascript:void(0)" class="btn-update btn btn-sm btn-light" data-detail="' . $detail . '" data-id="' . $row->id . '"><i class="fa fa-pen"></i></a>';
                    if ($row->id !== auth()->id()) {
                        $btn .= '<a href="javascript:void(0)" class="btn-delete btn btn-sm btn-light text-danger" data-id="' . $row->id . '"><i class="fa fa-trash"></i></a>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['role_badge', 'actions'])
                ->make(true);
        }

        return view('admin.user.index', [
            'title'    => 'Manajemen User',
            'subtitle' => 'Kelola akun admin dan super admin sistem.',
            'daftarJabatan' => config('jabatan.daftar', []),
        ]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,super_admin',
            // Jabatan dipilih dari master jabatan (config/jabatan.php)
            'jabatan'  => ['required', Rule::in(config('jabatan.daftar', []))],
        ], [
            'jabatan.required' => 'Pilih jabatan.',
            'jabatan.in'       => 'Jabatan tidak ada di daftar master jabatan.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['password'] = Hash::make($data['password']);

            User::create($data);

            return response()->json(['status' => 'success', 'message' => 'Berhasil tambah user', 'data' => null], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal tambah user, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function show(string $id) {}
    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,super_admin',
            // Jabatan dari master; jabatan lama user yang belum ada di daftar tetap boleh dipertahankan
            'jabatan'  => ['required', Rule::in(array_merge(
                config('jabatan.daftar', []),
                [(string) User::whereKey($id)->value('jabatan')]
            ))],
        ], [
            'jabatan.required' => 'Pilih jabatan.',
            'jabatan.in'       => 'Jabatan tidak ada di daftar master jabatan.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = User::findOrFail($id);
            $data = $validator->validated();

            // Jangan sampai sistem tidak punya Tata Usaha sama sekali
            if ($user->jabatan === 'Tata Usaha' && $data['jabatan'] !== 'Tata Usaha'
                && User::where('jabatan', 'Tata Usaha')->count() <= 1) {
                return response()->json(['status' => 'error', 'message' => 'Ini satu-satunya akun Tata Usaha, jabatannya tidak bisa diubah.', 'data' => null], 422);
            }

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return response()->json(['status' => 'success', 'message' => 'Berhasil ubah user', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal ubah user, ' . $e->getMessage(), 'data' => null], 500);
        }
    }

    public function destroy(string $id)
    {
        if ((int) $id === auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa menghapus akun sendiri', 'data' => null], 403);
        }

        try {
            $user = User::findOrFail($id);

            if ($user->jabatan === 'Tata Usaha' && User::where('jabatan', 'Tata Usaha')->count() <= 1) {
                return response()->json(['status' => 'error', 'message' => 'Ini satu-satunya akun Tata Usaha, tidak bisa dihapus.', 'data' => null], 422);
            }

            // Hapus user menghapus data miliknya lewat cascade: surat, disposisi, mitra, sertifikat, dst.
            $punyaData = collect([
                'disposisi'            => ['kepala_sekolah_id', 'tujuan_user_id'],
                'industri'             => ['humas_id'],
                'kerjasama'            => ['humas_id'],
                'sertifikat'           => ['user_id_penerbit', 'user_id_penerima'],
                'surat_keluar'         => ['user_id_pembuat'],
                'template_sertifikat'  => ['created_by'],
            ])->contains(fn ($kolom, $tabel) => DB::table($tabel)
                ->where(fn ($q) => collect($kolom)->each(fn ($k) => $q->orWhere($k, $user->id)))
                ->exists());

            if ($punyaData) {
                return response()->json(['status' => 'error', 'message' => 'User ini masih punya data (surat, disposisi, mitra, atau sertifikat) yang ikut terhapus bila akunnya dihapus. Ubah password atau jabatannya saja.', 'data' => null], 422);
            }

            $user->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus user', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus user, ' . $e->getMessage(), 'data' => null], 500);
        }
    }
}
