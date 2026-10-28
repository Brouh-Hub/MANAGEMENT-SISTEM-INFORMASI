{{--
| Satu tombol "Lihat" untuk surat keluar yang menunggu persetujuan Kepala Sekolah.
| Membuka modal detail (preview PDF + data) yang juga berisi tombol Setujui.
| Pakai: @include('admin.disposisi.partials.tombol-surat-keluar', ['surat' => $surat])
--}}
@php
    $dataSurat = [
        'judul'       => $surat->perihal,
        'pdf'         => $surat->file_path ? route('surat-keluar.preview', $surat->id) . '?v=' . optional($surat->updated_at)->timestamp : null,
        'url_setujui' => route('surat-keluar.approve', $surat->id),
        'baris'       => [
            'Nomor Surat'   => $surat->nomor_surat ?: '-',
            'Perihal'       => $surat->perihal ?: '-',
            'Tujuan'        => $surat->tujuan ?: '-',
            'Alamat Tujuan' => $surat->alamat_tujuan ?: '-',
            'Tanggal Surat' => $surat->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->format('d-m-Y') : '-',
            'Keterangan'    => $surat->keterangan ?: '-',
            'Dibuat oleh'   => $surat->pembuat->name ?? '-',
            'Diperiksa'     => $surat->dikonfirmasi_at
                ? 'Disetujui ' . ($surat->pengonfirmasi->name ?? 'Kepala TU') . ' (' . $surat->dikonfirmasi_at->format('d-m-Y H:i') . ')'
                : '-',
        ],
    ];
@endphp
<button type="button" class="btn btn-sm btn-primary btn-detail-surat-keluar" data-surat='@json($dataSurat)' title="Lihat & Setujui">
    <i class="fa fa-magnifying-glass"></i> Periksa
</button>
