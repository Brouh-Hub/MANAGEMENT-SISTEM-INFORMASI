<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenKerjasama;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

/*
| Modul MoU / PKS: dokumen kerja sama sekolah dengan mitra industri (DUDI).
| - Tata Usaha & Waka Humas : tambah, ubah, hapus
| - Kepala Sekolah          : hanya melihat
*/
class DokumenKerjasamaController extends Controller
{
    private function bolehUbah(): bool
    {
        return in_array(strtolower(trim(auth()->user()->jabatan ?? '')), ['tata usaha', 'waka humas'], true);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DokumenKerjasama::with('mitra:id,nama_perusahaan')
                ->when($request->filled('status'), fn ($q) => $q->status($request->input('status')))
                ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->input('jenis')))
                ->when($request->filled('mitra'), fn ($q) => $q->where('industri_id', $request->input('mitra')))
                ->orderByRaw('tanggal_berakhir IS NULL')   // yang punya batas waktu di atas
                ->orderBy('tanggal_berakhir');

            $bolehUbah = $this->bolehUbah();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('mitra', fn ($row) => e($row->mitra->nama_perusahaan ?? '-'))
                ->filterColumn('mitra', fn ($q, $kata) => $q->whereHas('mitra', fn ($m) => $m->where('nama_perusahaan', 'like', "%{$kata}%")))
                ->editColumn('jenis', function ($row) {
                    $warna = ['MoU' => 'primary', 'PKS' => 'info', 'Lainnya' => 'secondary'][$row->jenis] ?? 'secondary';

                    return '<span class="badge bg-' . $warna . '-subtle text-' . $warna . '-emphasis">' . e($row->jenis) . '</span>';
                })
                ->addColumn('masa_berlaku', function ($row) {
                    $mulai = $row->tanggal_mulai?->format('d-m-Y');
                    $akhir = $row->tanggal_berakhir?->format('d-m-Y') ?? 'Tanpa batas';

                    return $mulai . ' s.d. ' . $akhir;
                })
                ->addColumn('status_badge', function ($row) {
                    [$label, $warna] = DokumenKerjasama::LABEL_STATUS[$row->status];
                    $html = '<span class="badge bg-' . $warna . '">' . $label . '</span>';

                    if ($row->sisa_hari !== null && in_array($row->status, ['aktif', 'akan_berakhir'], true)) {
                        $html .= '<div class="small text-muted mt-1">sisa ' . $row->sisa_hari . ' hari</div>';
                    }
                    if ($row->status === 'berakhir') {
                        $html .= '<div class="small text-danger mt-1">lewat ' . abs($row->sisa_hari) . ' hari</div>';
                    }

                    return $html;
                })
                ->addColumn('actions', function ($row) use ($bolehUbah) {
                    $data = $row->only([
                        'id', 'industri_id', 'jenis', 'nomor_dokumen', 'nomor_dokumen_mitra', 'judul',
                        'ruang_lingkup', 'penandatangan_sekolah', 'penandatangan_mitra', 'keterangan',
                    ]) + [
                        'mitra'            => $row->mitra->nama_perusahaan ?? '-',
                        'tanggal_mulai'    => $row->tanggal_mulai?->format('Y-m-d'),
                        'tanggal_berakhir' => $row->tanggal_berakhir?->format('Y-m-d'),
                        'masa_berlaku'     => $row->tanggal_mulai?->locale('id')->translatedFormat('j F Y')
                            . ' s.d. ' . ($row->tanggal_berakhir?->locale('id')->translatedFormat('j F Y') ?? 'tanpa batas waktu'),
                        'status_label'     => DokumenKerjasama::LABEL_STATUS[$row->status][0],
                        'status_warna'     => DokumenKerjasama::LABEL_STATUS[$row->status][1],
                        'file_url'         => $row->file_url,
                    ];
                    $json = e(json_encode($data));

                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="javascript:void(0)" class="btn btn-sm btn-light btn-detail" data-detail="' . $json . '" title="Lihat"><i class="fa fa-eye"></i></a>';

                    if ($bolehUbah) {
                        $btn .= '<a href="javascript:void(0)" class="btn btn-sm btn-light btn-update" data-detail="' . $json . '" title="Ubah"><i class="fa fa-pen"></i></a>';
                        $btn .= '<a href="javascript:void(0)" class="btn btn-sm btn-light text-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fa fa-trash"></i></a>';
                    }

                    return $btn . '</div>';
                })
                ->rawColumns(['jenis', 'status_badge', 'actions'])
                ->make(true);
        }

        $ringkasan = [
            'total'         => DokumenKerjasama::count(),
            'aktif'         => DokumenKerjasama::status('aktif')->count(),
            'akan_berakhir' => DokumenKerjasama::status('akan_berakhir')->count(),
            'berakhir'      => DokumenKerjasama::status('berakhir')->count(),
        ];

        return view('admin.mou-pks.index', [
            'title'          => 'MoU / PKS',
            'subtitle'       => 'Dokumen kerja sama sekolah dengan mitra industri (DUDI) beserta masa berlakunya.',
            'mitra'          => Mitra::orderBy('nama_perusahaan')->get(['id', 'nama_perusahaan']),
            'ringkasan'      => $ringkasan,
            'bolehUbah'      => $this->bolehUbah(),
            'hariPeringatan' => DokumenKerjasama::hariPeringatan(),
        ]);
    }

    private function aturan(?int $id = null): array
    {
        return [
            'industri_id'           => 'required|exists:industri,id',
            'jenis'                 => 'required|in:MoU,PKS,Lainnya',
            'nomor_dokumen'         => ['required', 'string', 'max:150', Rule::unique('dokumen_kerjasama', 'nomor_dokumen')->ignore($id)],
            'nomor_dokumen_mitra'   => 'nullable|string|max:150',
            'judul'                 => 'required|string|max:255',
            'ruang_lingkup'         => 'nullable|string|max:5000',
            'tanggal_mulai'         => 'required|date',
            'tanggal_berakhir'      => 'nullable|date|after_or_equal:tanggal_mulai',
            'penandatangan_sekolah' => 'nullable|string|max:150',
            'penandatangan_mitra'   => 'nullable|string|max:150',
            'keterangan'            => 'nullable|string|max:2000',
            'file'                  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:' . config('mou.maks_berkas', 10240),
            'hapus_file'            => 'nullable|boolean',
        ];
    }

    private function pesan(): array
    {
        return [
            'industri_id.required'            => 'Pilih mitra industri.',
            'jenis.required'                  => 'Pilih jenis dokumen.',
            'nomor_dokumen.required'          => 'Nomor dokumen wajib diisi.',
            'nomor_dokumen.unique'            => 'Nomor dokumen ini sudah dipakai dokumen lain.',
            'judul.required'                  => 'Judul/perihal kerja sama wajib diisi.',
            'tanggal_mulai.required'          => 'Tanggal mulai berlaku wajib diisi.',
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
            'file.mimes'                      => 'Berkas harus PDF atau gambar (JPG/PNG).',
            'file.max'                        => 'Ukuran berkas maksimal ' . round(config('mou.maks_berkas', 10240) / 1024) . ' MB.',
        ];
    }

    public function store(Request $request)
    {
        abort_unless($this->bolehUbah(), 403);

        $validator = Validator::make($request->all(), $this->aturan(), $this->pesan());

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $data = collect($validator->validated())->except(['file', 'hapus_file'])->all();
        $data['dibuat_oleh'] = auth()->id();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('mou-pks', 'public');
        }

        DokumenKerjasama::create($data);

        return response()->json(['status' => 'success', 'message' => 'Dokumen MoU/PKS berhasil disimpan.'], 201);
    }

    public function update(Request $request, DokumenKerjasama $mou_pk)
    {
        abort_unless($this->bolehUbah(), 403);
        $dokumen = $mou_pk;

        $validator = Validator::make($request->all(), $this->aturan($dokumen->id), $this->pesan());

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $data = collect($validator->validated())->except(['file', 'hapus_file'])->all();

        if ($request->hasFile('file') || $request->boolean('hapus_file')) {
            if ($dokumen->file_path) {
                Storage::disk('public')->delete($dokumen->file_path);
            }
            $data['file_path'] = $request->hasFile('file')
                ? $request->file('file')->store('mou-pks', 'public')
                : null;
        }

        $dokumen->update($data);

        return response()->json(['status' => 'success', 'message' => 'Dokumen MoU/PKS berhasil diperbarui.']);
    }

    public function destroy(DokumenKerjasama $mou_pk)
    {
        abort_unless($this->bolehUbah(), 403);

        if ($mou_pk->file_path) {
            Storage::disk('public')->delete($mou_pk->file_path);
        }
        $mou_pk->delete();

        return response()->json(['status' => 'success', 'message' => 'Dokumen MoU/PKS berhasil dihapus.']);
    }
}
