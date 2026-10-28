{{--
| Riwayat aktivitas Kepala Sekolah (timeline) di halaman Disposisi.
| Data dari DisposisiController::riwayatAktivitas()
--}}
@php
    $sifatLabel = [
        'sangat_segera' => ['Sangat Segera', 'danger'],
        'segera'        => ['Segera', 'warning'],
        'rahasia'       => ['Rahasia', 'dark'],
        'biasa'         => ['Biasa', 'secondary'],
    ];
    $jumlahBelum = $aktivitas->where('belum', true)->count();

    $judulTanggal = function ($t) {
        if ($t->isToday()) return 'Hari ini';
        if ($t->isYesterday()) return 'Kemarin';
        return $t->locale('id')->translatedFormat('l, j F Y');
    };
@endphp

<style>
    .aktv-ringkas { display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:10px; }
    @media (max-width: 991px) { .aktv-ringkas { grid-template-columns:repeat(2, minmax(0,1fr)); } }
    .aktv-ringkas .kotak { background:#F6F7FB; border-radius:12px; padding:12px 14px; }
    .aktv-ringkas .angka { font-size:20px; font-weight:700; line-height:1.1; }
    .aktv-ringkas .label { font-size:12px; color:#6c757d; }

    .aktv-filter .btn { border-radius:20px; font-size:13px; padding:4px 12px; }
    .aktv-filter .btn.active { background:#1625C4; border-color:#1625C4; color:#fff; }

    .aktv-tanggal { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#8a8f98; margin:18px 0 8px; }
    .aktv-list { position:relative; padding-left:34px; }
    .aktv-list::before { content:''; position:absolute; left:13px; top:4px; bottom:4px; width:2px; background:#E6E8F0; }
    .aktv-item { position:relative; padding:0 0 16px; }
    .aktv-item .titik { position:absolute; left:-34px; top:0; width:28px; height:28px; border-radius:50%;
        display:flex; align-items:center; justify-content:center; font-size:12px; color:#fff; box-shadow:0 0 0 4px #fff; }
    .aktv-item .isi { background:#fff; border:1px solid #ECEEF4; border-radius:12px; padding:10px 14px; }
    .aktv-item.terlambat .isi { border-color:#F5C2C7; background:#FFF8F8; }
    .aktv-item .jam { font-size:12px; color:#8a8f98; }
    .aktv-item .teks { font-size:14px; }
    .aktv-item .instruksi { font-size:13px; color:#555; background:#F6F7FB; border-radius:8px; padding:6px 10px; margin-top:6px; }
    .aktv-jejak { list-style:none; padding:0; margin:8px 0 0; font-size:13px; }
    .aktv-jejak li { padding:2px 0; }
    .aktv-jejak .laporan { color:#444; font-style:italic; }
</style>

<p class="text-muted small mb-3">Apa saja yang sudah Anda lakukan dan sejauh mana tindak lanjutnya (90 hari terakhir).</p>

<div>

    {{-- Ringkasan bulan ini --}}
    <div class="aktv-ringkas mb-3">
        <div class="kotak"><div class="angka">{{ $ringkasanAktivitas['disposisi'] }}</div><div class="label">Disposisi bulan ini</div></div>
        <div class="kotak"><div class="angka text-success">{{ $ringkasanAktivitas['selesai'] }}</div><div class="label">Sudah selesai</div></div>
        <div class="kotak"><div class="angka text-warning">{{ $ringkasanAktivitas['diproses'] }}</div><div class="label">Sedang diproses</div></div>
        <div class="kotak"><div class="angka text-danger">{{ $ringkasanAktivitas['belum_dibaca'] }}</div><div class="label">Belum dibaca</div></div>
        <div class="kotak"><div class="angka text-primary">{{ $ringkasanAktivitas['keluar'] }}</div><div class="label">Surat keluar disetujui</div></div>
    </div>

    {{-- Filter --}}
    <div class="aktv-filter d-flex flex-wrap gap-2 mb-1">
        <button type="button" class="btn btn-light active" data-filter="semua">Semua</button>
        <button type="button" class="btn btn-light" data-filter="disposisi">Disposisi</button>
        <button type="button" class="btn btn-light" data-filter="belum">
            Belum ditindaklanjuti @if($jumlahBelum)<span class="badge bg-danger ms-1">{{ $jumlahBelum }}</span>@endif
        </button>
        <button type="button" class="btn btn-light" data-filter="arsip">Diarsipkan</button>
        <button type="button" class="btn btn-light" data-filter="keluar">Surat Keluar</button>
    </div>

    <div id="aktv-wadah">
        @forelse($aktivitas->groupBy(fn ($i) => $i['waktu']->toDateString()) as $tanggal => $grup)
            <div class="aktv-grup">
                <div class="aktv-tanggal">{{ $judulTanggal(\Carbon\Carbon::parse($tanggal)) }}</div>
                <div class="aktv-list">
                    @foreach($grup as $a)
                        <div class="aktv-item {{ ($a['terlambat'] ?? false) ? 'terlambat' : '' }}"
                             data-tipe="{{ $a['tipe'] }}" data-belum="{{ $a['belum'] ? 1 : 0 }}">

                            @if($a['tipe'] === 'disposisi')
                                @php [$sLabel, $sWarna] = $sifatLabel[$a['sifat']] ?? ['-', 'secondary']; @endphp
                                <div class="titik {{ $a['status'] === 'selesai' ? 'bg-success' : (($a['terlambat'] ?? false) ? 'bg-danger' : 'bg-primary') }}">
                                    <i class="fa fa-share"></i>
                                </div>
                                <div class="isi">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                                        <div class="teks">
                                            Anda mendisposisikan <b>"{{ $a['judul'] }}"</b> ke <b>{{ $a['tujuan'] }}</b>
                                            <span class="badge bg-{{ $sWarna }}-subtle text-{{ $sWarna }}-emphasis ms-1">{{ $sLabel }}</span>
                                        </div>
                                        <span class="jam text-nowrap">{{ $a['waktu']->format('H.i') }}</span>
                                    </div>
                                    @if($a['nomor'])<div class="jam">{{ $a['nomor'] }}</div>@endif
                                    @if($a['instruksi'])<div class="instruksi"><i class="fa fa-quote-left text-muted me-1"></i>{{ $a['instruksi'] }}</div>@endif

                                    <ul class="aktv-jejak">
                                        @forelse($a['jejak'] as $j)
                                            <li>
                                                <i class="fa {{ $j['ikon'] }} {{ $j['warna'] }} me-1"></i>
                                                {{ $j['teks'] }}
                                                @if($j['waktu'])<span class="jam">· {{ \Carbon\Carbon::parse($j['waktu'])->locale('id')->translatedFormat('j M, H.i') }}</span>@endif
                                                @if(! empty($j['laporan']))
                                                    <div class="laporan ms-4">"{{ \Illuminate\Support\Str::limit($j['laporan'], 160) }}"</div>
                                                @endif
                                                @if(! empty($j['file']))
                                                    <a href="{{ $j['file'] }}" target="_blank" class="ms-4 small"><i class="fa fa-paperclip"></i> Lampiran hasil</a>
                                                @endif
                                            </li>
                                        @empty
                                            <li class="{{ ($a['terlambat'] ?? false) ? 'text-danger' : 'text-muted' }}">
                                                <i class="fa fa-clock me-1"></i>
                                                Belum dibaca penerima{{ ($a['terlambat'] ?? false) ? ' sejak ' . $a['waktu']->locale('id')->diffForHumans() : '' }}
                                            </li>
                                        @endforelse
                                    </ul>

                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-light btn-riwayat-disposisi" data-id="{{ $a['surat_id'] }}">
                                            <i class="fa fa-clock-rotate-left"></i> Riwayat lengkap
                                        </button>
                                    </div>
                                </div>

                            @elseif($a['tipe'] === 'arsip')
                                <div class="titik bg-secondary"><i class="fa fa-box-archive"></i></div>
                                <div class="isi">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                                        <div class="teks">Anda mengarsipkan <b>"{{ $a['judul'] }}"</b></div>
                                        <span class="jam text-nowrap">{{ $a['waktu']->format('H.i') }}</span>
                                    </div>
                                    @if($a['nomor'])<div class="jam">{{ $a['nomor'] }}</div>@endif
                                    @if($a['alasan'])<div class="instruksi">{{ $a['alasan'] }}</div>@endif
                                </div>

                            @else
                                <div class="titik bg-success"><i class="fa fa-paper-plane"></i></div>
                                <div class="isi">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                                        <div class="teks">Anda menyetujui surat keluar <b>{{ $a['nomor'] ?: '-' }}</b></div>
                                        <span class="jam text-nowrap">{{ $a['waktu']->format('H.i') }}</span>
                                    </div>
                                    <div class="small text-muted">{{ $a['judul'] }}@if($a['tujuan'] && $a['tujuan'] !== '-') — kepada {{ $a['tujuan'] }}@endif</div>
                                    @if($a['pdf'])
                                        <a href="{{ $a['pdf'] }}" target="_blank" class="btn btn-sm btn-light mt-2"><i class="fa fa-file-pdf"></i> Lihat PDF</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">Belum ada aktivitas dalam 90 hari terakhir.</div>
        @endforelse

        <div class="text-center text-muted py-4" id="aktv-kosong" style="display:none;">Tidak ada aktivitas untuk filter ini.</div>
    </div>

    <div class="text-center mt-2">
        <button type="button" class="btn btn-sm btn-outline-primary" id="aktv-lebih" style="display:none;">
            Tampilkan lebih banyak
        </button>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    const PER_HALAMAN = 8;
    let filter = 'semua';
    let batas = PER_HALAMAN;

    function cocok($el) {
        if (filter === 'semua') return true;
        if (filter === 'belum') return $el.data('belum') == 1;
        return $el.data('tipe') === filter;
    }

    function tampilkan() {
        let n = 0, total = 0;

        $('#aktv-wadah .aktv-item').each(function () {
            const $el = $(this);
            const ok = cocok($el);
            if (ok) total++;
            const muncul = ok && n < batas;
            if (muncul) n++;
            $el.toggle(muncul);
        });

        // Sembunyikan judul tanggal yang semua itemnya tersembunyi
        $('#aktv-wadah .aktv-grup').each(function () {
            $(this).toggle($(this).find('.aktv-item').filter(function () { return $(this).css('display') !== 'none'; }).length > 0);
        });

        $('#aktv-kosong').toggle(total === 0 && $('#aktv-wadah .aktv-item').length > 0);
        $('#aktv-lebih').toggle(total > batas);
    }

    $('.aktv-filter .btn').on('click', function () {
        $('.aktv-filter .btn').removeClass('active');
        $(this).addClass('active');
        filter = $(this).data('filter');
        batas = PER_HALAMAN;
        tampilkan();
    });

    $('#aktv-lebih').on('click', function () {
        batas += PER_HALAMAN;
        tampilkan();
    });

    tampilkan();
});
</script>
@endpush
