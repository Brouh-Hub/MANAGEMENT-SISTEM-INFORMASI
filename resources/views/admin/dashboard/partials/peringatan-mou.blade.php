{{--
| Peringatan dokumen MoU/PKS yang akan / sudah berakhir.
| Tampil di dashboard untuk Tata Usaha, Waka Humas, dan Kepala Sekolah.
--}}
@php
    $jabatanMou = strtolower(trim(auth()->user()->jabatan ?? ''));
    $tampilMou = in_array($jabatanMou, ['tata usaha', 'waka humas', 'kepala sekolah'], true)
        && \Illuminate\Support\Facades\Schema::hasTable('dokumen_kerjasama');

    $mouSegera = $tampilMou
        ? \App\Models\DokumenKerjasama::with('mitra:id,nama_perusahaan')
            ->status('akan_berakhir')->orderBy('tanggal_berakhir')->take(5)->get()
        : collect();
    $jumlahBerakhir = $tampilMou ? \App\Models\DokumenKerjasama::status('berakhir')->count() : 0;
@endphp

@if($tampilMou && ($mouSegera->isNotEmpty() || $jumlahBerakhir))
    <div class="card-panel mb-3 mb-md-4" style="border-left:4px solid #f59e0b;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
                <div class="fw-bold"><i class="fa fa-triangle-exclamation text-warning me-1"></i> Masa berlaku MoU/PKS</div>
                <small class="text-muted">
                    {{ $mouSegera->count() }} dokumen akan berakhir dalam {{ \App\Models\DokumenKerjasama::hariPeringatan() }} hari
                    @if($jumlahBerakhir) · <span class="text-danger">{{ $jumlahBerakhir }} sudah berakhir</span>@endif
                </small>
            </div>
            <a href="{{ route('mou-pks.index') }}" class="btn btn-sm btn-light">Lihat semua <i class="fa fa-arrow-right"></i></a>
        </div>

        @foreach($mouSegera as $mou)
            <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-top" style="font-size:14px;">
                <div class="min-w-0">
                    <div class="fw-semibold text-truncate">{{ $mou->mitra->nama_perusahaan ?? '-' }}</div>
                    <div class="small text-muted text-truncate">{{ $mou->jenis }} · {{ $mou->nomor_dokumen }}</div>
                </div>
                <div class="text-end flex-shrink-0">
                    <span class="badge bg-warning text-dark">sisa {{ $mou->sisa_hari }} hari</span>
                    <div class="small text-muted">{{ $mou->tanggal_berakhir->format('d-m-Y') }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
