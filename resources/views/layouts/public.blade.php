<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Beranda' }} - {{ $sekolah->nama ?? 'SIM Sekolah' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root { --brand: #1625C4; }
        body { font-family: 'Segoe UI', sans-serif; background: #fff; }

        .navbar-public {
            background: #fff;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            padding: 14px 0;
        }
        .navbar-public .brand {
            font-weight: 700;
            color: var(--brand);
            display: flex; align-items: center; gap: 8px;
        }
        .navbar-public .nav-link {
            color: #333;
            font-size: 14px;
            font-weight: 500;
            margin: 0 8px;
        }
        .navbar-public .nav-link.active,
        .navbar-public .nav-link:hover {
            color: var(--brand);
        }
        .navbar-public .brand { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .menu-hp .nav-link { display: block; margin: 0; padding: 10px 0; border-top: 1px solid #f0f1f5; }

        @media (max-width: 767.98px) {
            .hero { padding: 40px 0; }
            .hero h1 { font-size: 1.6rem; }
            footer { margin-top: 40px; padding: 28px 0; }
        }

        .btn-login-widget {
            background: var(--brand);
            color: #fff;
            border-radius: 20px;
            padding: 6px 18px;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-login-widget:hover { color: #fff; opacity: 0.9; }

        .hero {
            background: linear-gradient(135deg, var(--brand), #3B4CE0);
            color: #fff;
            padding: 70px 0;
        }

        .card-berita img { height: 160px; object-fit: cover; width: 100%; }
        .card-mitra { border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); height: 100%; }

        footer {
            background: #0F1740;
            color: #cfd3f0;
            padding: 40px 0;
            margin-top: 60px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <nav class="navbar-public">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="brand text-decoration-none">
                <i class="fa fa-shield-halved"></i> {{ $sekolah->nama ?? 'SIM Sekolah' }}
            </a>

            {{-- Menu desktop --}}
            <div class="d-none d-md-flex align-items-center">
                @include('layouts.partials.menu-publik')
            </div>

            {{-- Tombol menu HP --}}
            <button class="btn btn-light d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#menu-publik-hp" aria-label="Buka menu">
                <i class="fa fa-bars"></i>
            </button>
        </div>

        {{-- Menu HP (buka-tutup) --}}
        <div class="collapse d-md-none" id="menu-publik-hp">
            <div class="container menu-hp pt-2">
                @include('layouts.partials.menu-publik')
            </div>
        </div>
    </nav>

    @yield('content')

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-white fw-bold">{{ $sekolah->nama ?? 'SIM Sekolah' }}</h6>
                    <p class="mb-1">{{ $sekolah->alamat ?? '-' }}</p>
                    <p class="mb-1">{{ $sekolah->telepon ?? '-' }} · {{ $sekolah->email ?? '-' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>&copy; {{ date('Y') }} {{ $sekolah->nama ?? 'SIM Sekolah' }}. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
