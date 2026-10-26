<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('sekolah');

        return view('admin.profil.index', [
            'title'    => 'Profil Saya',
            'subtitle' => 'Kelola data diri dan keamanan akun Anda',
            'user'     => $user,
            'statistik' => $this->statistik($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update($validator->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil menyimpan profil',
            'data'    => ['name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function password(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'password_lama' => 'required',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (! Hash::check($request->password_lama, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ooopps',
                'errors'  => ['password_lama' => ['Password lama tidak sesuai.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengubah password',
            'data'    => null,
        ]);
    }

    /**
     * Kartu statistik sesuai peran user. Setiap item: label, nilai, ikon FA.
     */
    private function statistik($user): array
    {
        $jabatan = strtolower($user->jabatan ?? '');
        $stat = [];

        $diterima = Disposisi::where('tujuan_user_id', $user->id);
        if ($jabatan !== 'kepala sekolah') {
            $stat[] = ['label' => 'Disposisi Diterima', 'nilai' => (clone $diterima)->count(), 'ikon' => 'fa-inbox'];
            $stat[] = ['label' => 'Sedang Diproses', 'nilai' => (clone $diterima)->whereIn('status', ['diterima', 'diproses'])->count(), 'ikon' => 'fa-spinner'];
            $stat[] = ['label' => 'Selesai', 'nilai' => (clone $diterima)->where('status', 'selesai')->count(), 'ikon' => 'fa-circle-check'];
        }

        if ($jabatan === 'kepala sekolah') {
            $stat[] = ['label' => 'Disposisi Diberikan', 'nilai' => Disposisi::where('kepala_sekolah_id', $user->id)->count(), 'ikon' => 'fa-paper-plane'];
            $stat[] = ['label' => 'Surat Keluar Disetujui', 'nilai' => SuratKeluar::where('kepala_sekolah_id', $user->id)->where('status', 'terbit')->count(), 'ikon' => 'fa-file-circle-check'];
            $stat[] = ['label' => 'Menunggu Persetujuan', 'nilai' => SuratKeluar::where('kepala_sekolah_id', $user->id)->where('status', 'menunggu_approve')->count(), 'ikon' => 'fa-hourglass-half'];
        }

        if (in_array($jabatan, ['tata usaha', 'kepala tata usaha'], true)) {
            $dibuat = SuratKeluar::where('user_id_pembuat', $user->id);
            $stat[] = ['label' => 'Surat Keluar Dibuat', 'nilai' => (clone $dibuat)->count(), 'ikon' => 'fa-file-pen'];
            $stat[] = ['label' => 'Surat Terbit', 'nilai' => (clone $dibuat)->where('status', 'terbit')->count(), 'ikon' => 'fa-file-circle-check'];
            $stat[] = ['label' => 'Masih Proses', 'nilai' => (clone $dibuat)->where('status', '!=', 'terbit')->count(), 'ikon' => 'fa-hourglass-half'];
        }

        return $stat;
    }
}
