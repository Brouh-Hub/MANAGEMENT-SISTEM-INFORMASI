<?php

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use App\Models\TemplateSuratField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateSuratFieldController extends Controller
{
    public function store(Request $request, TemplateSurat $templateSurat)
    {
        $request->validate([
            'label'         => 'required|string|max:100',
            'type'          => 'required|in:text,textarea,date,time,number',
            'is_required'   => 'nullable|boolean',
            'default_value' => 'nullable|string',
        ]);

        $base = Str::slug($request->label, '_') ?: 'field';
        $key = $base;
        $i = 2;

        while (
            TemplateSuratField::where('template_surat_id', $templateSurat->id)
                ->where('key', $key)
                ->exists()
        ) {
            $key = $base . '_' . $i++;
        }

        $field = TemplateSuratField::create([
            'template_surat_id' => $templateSurat->id,
            'key'               => $key,
            'label'             => $request->label,
            'type'              => $request->type,
            'is_required'       => $request->boolean('is_required'),
            'default_value'     => $request->default_value,
            'urutan'            => (
                TemplateSuratField::where(
                    'template_surat_id',
                    $templateSurat->id
                )->max('urutan') ?? 0
            ) + 1,
            'is_active'         => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Field template berhasil ditambahkan',
            'data' => $field,
        ], 201);
    }

    public function update(
        Request $request,
        TemplateSuratField $field
    ) {
        $request->validate([
            'label'         => 'required|string|max:100',
            'type'          => 'required|in:text,textarea,date,time,number',
            'is_required'   => 'nullable|boolean',
            'default_value' => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);

        $field->update([
            'label'         => $request->label,
            'type'          => $request->type,
            'is_required'   => $request->boolean('is_required'),
            'default_value' => $request->default_value,
            'is_active'     => $request->boolean('is_active'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Field template berhasil diperbarui',
            'data' => $field,
        ]);
    }

    public function destroy(TemplateSuratField $field)
    {
        $field->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Field template berhasil dihapus',
            'data' => null,
        ]);
    }
}
