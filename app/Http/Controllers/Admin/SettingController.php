<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman setting (data sekolah baris pertama/satu-satunya)
     */
    public function index()
    {
        $sekolah = Sekolah::first();

        return view('admin.setting.index', [
            'title'    => 'Pengaturan Sekolah',
            'subtitle' => 'Kelola Profil & Identitas Sekolah',
            'sekolah'  => $sekolah,
        ]);
    }

    /**
     * Update data sekolah (selalu update baris yang ada, create kalau belum ada sama sekali)
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string|max:255',
            'alamat'   => 'required|string',
            'telepon'  => 'required|string|max:20',
            'email'    => 'required|email|max:255',
            'logo'     => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('logo')) {
                $data['logo_path'] = $request->file('logo')->store('sekolah-logo', 'public');
            }
            unset($data['logo']); // hapus key 'logo' krn kolom di DB namanya 'logo_path'

            $sekolah = Sekolah::first();

            if ($sekolah) {
                $sekolah->update($data);
            } else {
                Sekolah::create($data);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil update pengaturan sekolah',
                'data'    => null,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal update pengaturan sekolah, ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
