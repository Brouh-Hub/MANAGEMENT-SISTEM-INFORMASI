<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluarField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuratKeluarFieldController extends Controller
{
    // Tambah field custom baru
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'type'  => 'required|in:text,textarea,date,number',
        ]);

        // key otomatis dari label, dijamin unik
        $base = Str::slug($request->label, '_') ?: 'field';
        $key  = $base;
        $i    = 2;
        while (SuratKeluarField::where('key', $key)->exists()) {
            $key = $base . '_' . $i++;
        }

        $field = SuratKeluarField::create([
            'key'        => $key,
            'label'      => $request->label,
            'type'       => $request->type,
            'is_default' => false,
            'is_active'  => true,
            'urutan'     => (SuratKeluarField::max('urutan') ?? 0) + 1,
        ]);

        return response()->json(['message' => 'Field berhasil ditambahkan', 'data' => $field]);
    }

    // Edit label (semua field) + aktif/nonaktif (khusus field custom)
    public function update(Request $request, SuratKeluarField $field)
    {
        $request->validate([
            'label'     => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $field->label = $request->label;

        if (!$field->is_default && $request->has('is_active')) {
            $field->is_active = (bool) $request->is_active;
        }

        $field->save();

        return response()->json(['message' => 'Field berhasil diperbarui']);
    }

    // Hapus field custom (data lama di JSON tetap tersimpan, hanya tidak ditampilkan)
    public function destroy(SuratKeluarField $field)
    {
        if ($field->is_default) {
            return response()->json(['message' => 'Field bawaan tidak bisa dihapus'], 403);
        }

        $field->delete();

        return response()->json(['message' => 'Field berhasil dihapus']);
    }
}
