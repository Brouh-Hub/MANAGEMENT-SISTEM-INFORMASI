<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
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
            'jabatan'  => 'nullable|string|max:255',
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
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,super_admin',
            'jabatan'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Ooopps', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = User::findOrFail($id);
            $data = $validator->validated();

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
            $user->delete();

            return response()->json(['status' => 'success', 'message' => 'Berhasil hapus user', 'data' => null], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan', 'data' => null], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal hapus user, ' . $e->getMessage(), 'data' => null], 500);
        }
    }
}
