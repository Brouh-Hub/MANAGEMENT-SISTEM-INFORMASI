{{-- Link menu halaman publik (dipakai menu desktop & menu HP) --}}
<a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
<a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">Tentang Sekolah</a>
@if(Route::has('mitra.public'))
    <a href="{{ route('mitra.public') }}" class="nav-link {{ request()->routeIs('mitra.public*') ? 'active' : '' }}">Kerjasama Industri</a>
@endif
<a href="{{ route('berita.index') }}" class="nav-link {{ request()->routeIs('berita.*') ? 'active' : '' }}">Berita</a>
