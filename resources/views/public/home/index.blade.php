@extends('layouts.public')

@section('content')

{{-- ================= HERO (foto gedung + overlay biru) ================= --}}
<div class="hero-modern" id="beranda"
     style="background-image:linear-gradient(160deg, rgba(28,38,180,.68), rgba(10,14,36,.8)), url('{{ $sekolah->foto_utama ? asset('storage/'.$sekolah->foto_utama) : asset('images/hero-default.jpeg') }}');">
    <div class="container position-relative" style="z-index:1;">
        <div class="hero-content">
            <p class="text-uppercase small fw-semibold mb-2" style="color:#C7D2FF;">
                Sekolah Menengah Kejuruan
            </p>
            <h1 class="fw-bold mb-3 text-white" style="max-width:16ch;">
                Belajar keterampilan yang langsung terpakai di dunia kerja
            </h1>
            <p class="mb-4" style="max-width:46ch;color:#E3E6FA;">
                {{ $sekolah->deskripsi_singkat ?? ($sekolah->nama ?? 'Sekolah Kami') . ' menyiapkan siswa lewat kurikulum berbasis praktik dan kemitraan langsung dengan industri, agar siap kerja sejak hari pertama lulus.' }}
            </p>
            <div class="d-flex gap-3 flex-wrap mb-4">
                <a href="#tentang" class="btn btn-hero-primary">Kenali Sekolah Kami</a>
                <a href="#kerjasama" class="btn btn-hero-ghost">Lihat Mitra Industri</a>
            </div>

            <div class="d-flex flex-wrap gap-4 pt-3 hero-stats-row">
                <div>
                    <strong class="d-block fs-4 fw-bold text-white">{{ number_format($statistik['siswa_aktif'] ?? 0, 0, ',', '.') }}</strong>
                    <span class="small" style="color:#D6DBF3;">Siswa aktif</span>
                </div>
                <div>
                    <strong class="d-block fs-4 fw-bold text-white">{{ $statistik['mitra_industri'] ?? 0 }}</strong>
                    <span class="small" style="color:#D6DBF3;">Mitra industri</span>
                </div>
                <div>
                    <strong class="d-block fs-4 fw-bold text-white">{{ $statistik['persentase_terserap'] ?? 0 }}%</strong>
                    <span class="small" style="color:#D6DBF3;">Lulusan terserap kerja</span>
                </div>
            </div>
        </div>
    </div>
    {{-- transisi gelombang ke konten putih --}}
    <svg class="hero-wave" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0,32 C360,70 1080,-10 1440,32 L1440,60 L0,60 Z" fill="#FFFFFF"></path>
    </svg>
</div>

<div class="container py-5">

    {{-- ================= TENTANG SEKOLAH ================= --}}
    <div id="tentang" class="py-4">
        <div class="section-kicker mb-2">Tentang Sekolah</div>
        <h4 class="fw-bold mb-4">Pendidikan kejuruan yang dekat dengan kebutuhan industri</h4>
        <div class="row g-4">
            <div class="col-lg-7">
                {!! $sekolah->tentang ?? '<p>Konten "Tentang Sekolah" belum diisi dari halaman admin.</p>' !!}
            </div>
            <div class="col-lg-5">
                <ul class="list-unstyled d-flex flex-column gap-3">
                    <li class="fact-card d-flex gap-3 align-items-start">
                        <span class="fact-num">{{ $jurusan->count() ?? 0 }}</span>
                        <span>
                            <b class="d-block fw-bold">Jurusan</b>
                            <span class="text-muted small">{{ $jurusan->pluck('singkatan')->implode(', ') ?? '-' }}</span>
                        </span>
                    </li>
                    <li class="fact-card d-flex gap-3 align-items-start">
                        <span class="fact-num">A</span>
                        <span>
                            <b class="d-block fw-bold">Akreditasi</b>
                            <span class="text-muted small">Terakreditasi Badan Akreditasi Nasional</span>
                        </span>
                    </li>
                    <li class="fact-card d-flex gap-3 align-items-start">
                        <span class="fact-num">{{ $sekolah->durasi_pkl ?? '-' }}</span>
                        <span>
                            <b class="d-block fw-bold">Bulan PKL</b>
                            <span class="text-muted small">Praktik kerja lapangan atau magang industri</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ================= KERJA SAMA INDUSTRI ================= --}}
    <div id="kerjasama" class="py-4 mt-4 border-top">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="section-kicker mb-2">Kerja Sama Industri</div>
                <h4 class="fw-bold mb-0">Mitra yang membuka jalan ke dunia kerja</h4>
            </div>
            @if(Route::has('mitra.public'))
                <a href="{{ route('mitra.public') }}" class="text-decoration-none">Lihat semua &rarr;</a>
            @endif
        </div>

        <div class="row g-3">
            @forelse($mitraUnggulan as $mitra)
                <div class="col-md-4 col-lg-2">
                    <div class="card-mitra text-center">
                        <i class="fa fa-building fa-2x mb-2" style="color:var(--brand-navy);"></i>
                        <p class="fw-semibold mb-0" style="font-size:13px;">{{ $mitra->nama_perusahaan }}</p>
                        <small class="text-muted">{{ $mitra->bidang }}</small>
                    </div>
                </div>
            @empty
                <p class="text-muted">Belum ada data mitra.</p>
            @endforelse
        </div>
    </div>

    {{-- ================= PROFIL (VISI & MISI) ================= --}}
    <div id="profil" class="py-4 mt-4 border-top">
        <div class="section-kicker mb-2">Profil</div>
        <h4 class="fw-bold mb-4">Visi dan arah sekolah</h4>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="profil-card h-100">
                    <h6 class="fw-bold mb-2">Visi</h6>
                    <p class="text-muted mb-0">{{ $sekolah->visi ?? '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="profil-card h-100">
                    <h6 class="fw-bold mb-2">Misi</h6>
                    <p class="text-muted mb-0">{{ $sekolah->misi ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= BERITA TERBARU ================= --}}
    <div id="berita" class="py-4 mt-4 border-top">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Berita Terbaru</h4>
            <a href="{{ route('berita.index') }}" class="text-decoration-none">Lihat semua &rarr;</a>
        </div>

        <div class="row g-3">
            @forelse($beritaTerbaru as $berita)
                <div class="col-md-4">
                    <div class="card-berita-modern h-100">
                        <img src="{{ $berita->thumbnail ? asset('storage/'.$berita->thumbnail) : 'https://via.placeholder.com/400x200?text=Berita' }}"
                             alt="{{ $berita->judul }}" class="w-100">
                        <div class="p-3">
                            <small class="fw-semibold" style="color:var(--brand-navy);">{{ $berita->tanggal_terbit?->format('Y') ?? ($berita->kategori->nama_kategori ?? '-') }}</small>
                            <h6 class="fw-bold mt-1">{{ $berita->judul }}</h6>
                            <a href="{{ route('berita.show', $berita->slug) }}" class="text-decoration-none small">Baca selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">Belum ada berita.</p>
            @endforelse
        </div>
    </div>

</div>

@push('styles')
<style>
    :root{ --brand-navy:#3B4CDB; --brand-navy-light:#5A69E3; --brand-navy-soft:#E7E9FA; }

    .hero-modern{
        position:relative;overflow:hidden;padding:130px 0 100px;
        background-size:cover;background-position:center;background-repeat:no-repeat;
    }
    .hero-content{ max-width:640px; }
    .hero-stats-row{ border-top:1px solid rgba(255,255,255,.25); }
    .hero-wave{ position:absolute;bottom:-1px;left:0;width:100%;height:44px;display:block; }

    .btn-hero-primary{ background:var(--brand-navy); color:#fff; padding:.7rem 1.6rem; border-radius:.4rem; }
    .btn-hero-primary:hover{ background:var(--brand-navy-light); color:#fff; }
    .btn-hero-ghost{ border:1px solid rgba(255,255,255,.55); color:#fff; padding:.7rem 1.6rem; border-radius:.4rem; }
    .btn-hero-ghost:hover{ border-color:#fff; background:rgba(255,255,255,.08); color:#fff; }

    .section-kicker{
        color:var(--brand-navy); font-weight:600; font-size:.9rem;
        display:flex; align-items:center; gap:8px;
    }
    .section-kicker::before{ content:""; width:22px; height:2px; background:var(--brand-navy); display:inline-block; }

    .fact-card{
        background:#fff; border:1px solid #E3E5F3; border-radius:8px; padding:16px 18px;
        box-shadow:0 2px 10px rgba(30,40,140,.05); transition:transform .18s, box-shadow .18s;
    }
    .fact-card:hover{ transform:translateY(-2px); box-shadow:0 8px 20px rgba(30,40,140,.09); }
    .fact-num{
        width:30px; height:30px; flex-shrink:0; border-radius:50%; background:var(--brand-navy-soft);
        color:var(--brand-navy); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem;
    }

    .card-mitra{
        border:1px solid #E3E5F3; border-radius:10px; padding:18px; background:#fff;
        box-shadow:0 2px 10px rgba(30,40,140,.05); transition:transform .18s, box-shadow .18s, border-color .18s;
    }
    .card-mitra:hover{ transform:translateY(-2px); border-color:var(--brand-navy); box-shadow:0 8px 20px rgba(30,40,140,.1); }

    .profil-card{
        background:#fff; border:1px solid #E3E5F3; border-radius:10px; padding:28px;
        box-shadow:0 4px 16px rgba(30,40,140,.06); position:relative; overflow:hidden;
    }
    .profil-card::before{ content:""; position:absolute; top:0; left:0; width:4px; height:100%; background:var(--brand-navy); }

    .card-berita-modern{
        border-top:3px solid var(--brand-navy); border-radius:0 0 10px 10px; background:#fff;
        box-shadow:0 4px 16px rgba(30,40,140,.06); overflow:hidden; transition:transform .18s, box-shadow .18s;
    }
    .card-berita-modern:hover{ transform:translateY(-3px); box-shadow:0 10px 24px rgba(30,40,140,.1); }

    @media (max-width:820px){
        .hero-modern{ padding:100px 0 70px; }
    }
</style>
@endpush

@endsection
