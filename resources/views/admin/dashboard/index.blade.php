@extends('layouts.main')
@section('content')

@php
    $jabatan = auth()->user()->jabatan;
    $isKepalaSekolah = $jabatan === 'Kepala Sekolah';
    $isTataUsaha     = $jabatan === 'Tata Usaha';
@endphp

<style>
    /* ===== Responsive helpers ===== */
    .page-title { font-size: 1.25rem; }
    .stat-card, .card-panel { padding: 14px; }
    .stat-card h3 { font-size: 1.5rem; }
    .icon-box { flex-shrink: 0; }
    .avatar-circle { flex-shrink: 0; }
    .min-w-0 { min-width: 0; }

    .chart-wrap { position: relative; height: 240px; }

    .rekap-box { background:#F5F6FC; border-radius:12px; padding:12px 14px; height:100%; }
    .rekap-angka { font-size:1.5rem; font-weight:700; display:block; line-height:1.2; }

    @media (min-width: 768px) {
        .page-title { font-size: 1.75rem; }
        .stat-card, .card-panel { padding: 20px; }
        .stat-card h3 { font-size: 1.75rem; }
        .chart-wrap { height: 320px; }
    }
</style>

<h3 class="fw-bold mb-1 page-title">{{ $title }}</h3>
<p class="text-muted mb-3 mb-md-4 small">{{ $subtitle }}</p>

{{-- Peringatan MoU/PKS yang hampir habis masa berlakunya --}}
@include('admin.dashboard.partials.peringatan-mou')

<div class="row g-2 g-md-3 mb-3 mb-md-4">

    <div class="{{ $isKepalaSekolah ? 'col-12 col-md-4' : 'col-12 col-md-6' }}">
        <div class="stat-card d-flex justify-content-between align-items-start gap-2 h-100">
            <div class="min-w-0">
                <p class="text-muted mb-1" style="font-size:13px;">Surat Hari Ini</p>
                <h3 class="fw-bold mb-0">{{ $suratHariIni }}</h3>
                <small class="text-muted">{{ $masukHariIni }} masuk · {{ $keluarHariIni }} keluar</small>
            </div>
            <div class="icon-box"><i class="fa fa-file-lines"></i></div>
        </div>
    </div>

    @if($isKepalaSekolah)

        <div class="col-6 col-md-4">
            <div class="stat-card d-flex justify-content-between align-items-start gap-2 h-100">
                <div class="min-w-0">
                    <p class="text-muted mb-1" style="font-size:13px;">Belum Disposisi</p>
                    <h3 class="fw-bold mb-0">{{ $belumDisposisi }}</h3>
                    <small class="text-danger">
                        <i class="fa fa-triangle-exclamation"></i> perlu tindak lanjut
                    </small>
                </div>
                <div class="icon-box"><i class="fa fa-share-from-square"></i></div>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="stat-card d-flex justify-content-between align-items-start gap-2 h-100">
                <div class="min-w-0">
                    <p class="text-muted mb-1" style="font-size:13px;">Prioritas Tinggi</p>
                    <h3 class="fw-bold mb-0">{{ $prioritasTinggi }}</h3>
                    <small class="text-muted">sangat segera / rahasia</small>
                </div>
                <div class="icon-box" style="background:#FDE8E8; color:#C53030;">
                    <i class="fa fa-heart-pulse"></i>
                </div>
            </div>
        </div>

    @elseif(!$isTataUsaha)

        {{-- Jabatan lain (Waka, Pembina OSIS, dst.). Tata Usaha tidak melihat kartu ini --}}
        <div class="col-12 col-md-6">
            <div class="stat-card d-flex justify-content-between align-items-start gap-2 h-100">
                <div class="min-w-0">
                    <p class="text-muted mb-1" style="font-size:13px;">Disposisi Saya</p>
                    <h3 class="fw-bold mb-0">{{ $disposisiSaya }}</h3>
                    <small class="text-muted">ditujukan kepada saya</small>
                </div>
                <div class="icon-box"><i class="fa fa-share-from-square"></i></div>
            </div>
        </div>

    @endif

</div>

<div class="row g-2 g-md-3 mb-3 mb-md-4">

    {{-- Tata Usaha tidak punya panel disposisi, jadi grafik dibuat selebar penuh --}}
    <div class="{{ $isTataUsaha ? 'col-12' : 'col-12 col-lg-8' }}">
        <div class="card-panel h-100">
            <div class="mb-3">
                <h6 class="fw-bold mb-0">Surat Masuk & Surat Keluar</h6>
                <small class="text-muted">Perbandingan volume surat 6 bulan terakhir</small>
            </div>
            <div class="chart-wrap">
                <canvas id="chartSurat"></canvas>
            </div>
        </div>
    </div>

    @if($isKepalaSekolah)

        <div class="col-12 col-lg-4">
            <div class="card-panel h-100">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Disposisi Terbaru</h6>
                        <small class="text-muted">Aktivitas disposisi terakhir</small>
                    </div>
                   <a href="{{ url('/admin/disposisi') }}" class="small">Lihat semua</a>
                </div>

                @forelse($disposisiTerbaru as $d)
                    <div class="d-flex gap-2 mb-3">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($d->tujuan->name ?? '-', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="mb-0 fw-semibold text-break" style="font-size:13px;">
                                {{ $d->suratMasuk->perihal ?? '-' }}
                            </p>
                            <small class="text-muted d-block text-break">
                                Didisposisikan ke {{ $d->tujuan->jabatan ?? '-' }}
                                ({{ $d->tujuan->name ?? '-' }})
                            </small>
                            <small class="text-muted">{{ $d->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small">Belum ada disposisi.</p>
                @endforelse
            </div>
        </div>

    @elseif(!$isTataUsaha)

        {{-- Jabatan lain. Tata Usaha tidak melihat panel ini --}}
        <div class="col-12 col-lg-4">
            <div class="card-panel h-100">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Disposisi Saya</h6>
                        <small class="text-muted">Disposisi terbaru yang ditujukan kepada Anda</small>
                    </div>
                    <a href="{{ route('disposisi.saya') }}" class="small">Lihat semua</a>
                </div>

                @forelse($disposisiTerbaru as $d)
                    <div class="d-flex gap-2 mb-3">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($d->suratMasuk->perihal ?? '-', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="mb-0 fw-semibold text-break" style="font-size:13px;">
                                {{ $d->suratMasuk->perihal ?? '-' }}
                            </p>
                            <small class="text-muted d-block text-break">{{ $d->instruksi ?? '-' }}</small>
                            <small class="text-muted">{{ $d->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small">Belum ada disposisi.</p>
                @endforelse
            </div>
        </div>

    @endif

</div>

{{-- =========================================================
     LAPORAN REKAP SURAT (Kepala Sekolah & Tata Usaha)
     ========================================================= --}}
@if($laporan)
<div class="card-panel mb-3 mb-md-4" id="laporan">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-0">Laporan Rekap Surat</h6>
            <small class="text-muted">
                Periode {{ $laporanDari->translatedFormat('d M Y') }} – {{ $laporanSampai->translatedFormat('d M Y') }}
            </small>
        </div>
        <a href="{{ route('laporan.pdf', ['dari' => $laporanDari->toDateString(), 'sampai' => $laporanSampai->toDateString()]) }}"
           target="_blank" class="btn btn-sm btn-primary">
            <i class="fa fa-print"></i> Cetak PDF
        </a>
    </div>

    {{-- Filter periode --}}
    <form method="GET" action="{{ route('admin.dashboard') }}#laporan" class="row g-2 align-items-end mb-3">
        <div class="col-6 col-md-3">
            <label class="form-label small text-muted mb-1">Dari</label>
            <input type="date" name="dari" value="{{ $laporanDari->toDateString() }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-muted mb-1">Sampai</label>
            <input type="date" name="sampai" value="{{ $laporanSampai->toDateString() }}" class="form-control form-control-sm">
        </div>
        <div class="col-12 col-md-6 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-sm btn-dark">
                <i class="fa fa-filter"></i> Terapkan
            </button>
            @php
                $preset = [
                    'Bulan ini' => [now()->startOfMonth(), now()],
                    'Bulan lalu' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                    'Tahun ini' => [now()->startOfYear(), now()],
                ];
            @endphp
            @foreach($preset as $label => [$pDari, $pSampai])
                <a href="{{ route('admin.dashboard', ['dari' => $pDari->toDateString(), 'sampai' => $pSampai->toDateString()]) }}#laporan"
                   class="btn btn-sm btn-light">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    {{-- Angka ringkas --}}
    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="rekap-box">
                <small class="text-muted d-block">Surat Masuk</small>
                <span class="rekap-angka text-primary">{{ $laporan['total_masuk'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rekap-box">
                <small class="text-muted d-block">Surat Keluar</small>
                <span class="rekap-angka text-success">{{ $laporan['total_keluar'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rekap-box">
                <small class="text-muted d-block">Disposisi Selesai</small>
                <span class="rekap-angka">{{ $laporan['persen_selesai'] }}%</span>
                <small class="text-muted">dari surat masuk</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rekap-box">
                <small class="text-muted d-block">Prioritas Tinggi</small>
                <span class="rekap-angka text-danger">{{ $laporan['prioritas_tinggi'] }}</span>
                <small class="text-muted">sangat segera / rahasia</small>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3">

        {{-- Status surat masuk & keluar --}}
        <div class="col-12 col-lg-6">
            <p class="fw-semibold small mb-2">Status Surat Masuk</p>
            @foreach($laporan['masuk_per_status'] as $label => $jumlah)
                @php $persen = $laporan['total_masuk'] ? round($jumlah / $laporan['total_masuk'] * 100) : 0; @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span>{{ $label }}</span>
                        <span class="text-muted">{{ $jumlah }} surat</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" style="width: {{ $persen }}%; background:#1625C4;"></div>
                    </div>
                </div>
            @endforeach

            <p class="fw-semibold small mb-2 mt-3">Status Surat Keluar</p>
            @foreach($laporan['keluar_per_status'] as $label => $jumlah)
                @php $persen = $laporan['total_keluar'] ? round($jumlah / $laporan['total_keluar'] * 100) : 0; @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span>{{ $label }}</span>
                        <span class="text-muted">{{ $jumlah }} surat</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-success" style="width: {{ $persen }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Rekap per bulan + pengirim terbanyak --}}
        <div class="col-12 col-lg-6">
            <p class="fw-semibold small mb-2">Rekap per Bulan</p>
            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle mb-0" style="font-size:13px;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:11px;">
                            <th>Bulan</th>
                            <th class="text-end">Masuk</th>
                            <th class="text-end">Keluar</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan['per_bulan'] as $b)
                            <tr>
                                <td>{{ $b['label'] }}</td>
                                <td class="text-end">{{ $b['masuk'] }}</td>
                                <td class="text-end">{{ $b['keluar'] }}</td>
                                <td class="text-end fw-semibold">{{ $b['masuk'] + $b['keluar'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td class="text-end">{{ $laporan['total_masuk'] }}</td>
                            <td class="text-end">{{ $laporan['total_keluar'] }}</td>
                            <td class="text-end">{{ $laporan['total_masuk'] + $laporan['total_keluar'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <p class="fw-semibold small mb-2">Pengirim Surat Masuk Terbanyak</p>
            @forelse($laporan['top_pengirim'] as $pengirim => $jumlah)
                <div class="d-flex justify-content-between small border-bottom py-1">
                    <span class="text-break me-2">{{ $pengirim }}</span>
                    <span class="text-muted text-nowrap">{{ $jumlah }} surat</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Tidak ada surat masuk pada periode ini.</p>
            @endforelse
        </div>

    </div>
</div>
@endif

<div class="card-panel">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-0">Surat Terbaru</h6>
            <small class="text-muted">8 surat masuk & keluar paling baru</small>
        </div>
        <a href="#" class="small">Lihat semua</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr class="text-muted text-uppercase" style="font-size:11px;">
                    <th>Tipe</th>
                    {{-- Kolom ini disembunyikan di mobile, infonya dipindah ke bawah Perihal --}}
                    <th class="d-none d-md-table-cell">Nomor Surat</th>
                    <th class="d-none d-md-table-cell">Pengirim / Tujuan</th>
                    <th>Perihal</th>
                    <th class="d-none d-sm-table-cell text-nowrap">Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratTerbaru as $s)
                    <tr style="font-size:13px;">
                        <td class="text-nowrap">
                            @if($s['tipe'] === 'Masuk')
                                <span class="text-primary"><i class="fa fa-arrow-down-to-bracket"></i> Masuk</span>
                            @else
                                <span class="text-success"><i class="fa fa-arrow-up-from-bracket"></i> Keluar</span>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell">{{ $s['nomor_surat'] }}</td>
                        <td class="d-none d-md-table-cell">{{ $s['pihak'] }}</td>
                        <td class="text-break">
                            {{ $s['perihal'] }}
                            {{-- Info tambahan khusus tampilan mobile --}}
                            <div class="d-md-none text-muted" style="font-size:11px;">
                                {{ $s['nomor_surat'] }} · {{ $s['pihak'] }}
                            </div>
                            <div class="d-sm-none text-muted" style="font-size:11px;">
                                {{ \Carbon\Carbon::parse($s['tanggal'])->format('d M Y') }}
                            </div>
                        </td>
                        <td class="d-none d-sm-table-cell text-nowrap">
                            {{ \Carbon\Carbon::parse($s['tanggal'])->format('d M Y') }}
                        </td>
                        <td>
                            @php
                                $badgeClass = match(true) {
                                    str_contains(strtolower($s['status']), 'selesai') => 'badge-selesai',
                                    str_contains(strtolower($s['status']), 'proses') => 'badge-diproses',
                                    default => 'badge-belum',
                                };
                            @endphp
                            <span class="badge-status {{ $badgeClass }}">{{ $s['status'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada surat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('chartSurat');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabel),
            datasets: [
                {
                    label: 'Masuk',
                    data: @json($chartMasuk),
                    backgroundColor: '#1625C4',
                    borderRadius: 4,
                },
                {
                    label: 'Keluar',
                    data: @json($chartKeluar),
                    backgroundColor: '#B9C2F2',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // tinggi mengikuti .chart-wrap
            plugins: {
                legend: {
                    position: 'top',
                    align: window.innerWidth < 768 ? 'start' : 'end',
                    labels: { boxWidth: 12, font: { size: 12 } }
                }
            },
            scales: {
                x: { ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } } }
            }
        }
    });
</script>
@endpush
