@php
    // ===== FIELD LOGO & NAMA SEKOLAH =====
    // Logo ada di: public/images/logoskanpat.jpg
    $logoSekolah = $logoSekolah ?? asset('images/logo-smkn4-tik.png');
    $namaSekolah = $namaSekolah ?? 'SMK Negeri 4 Tanjungpinang';
    $user = auth()->user();

    // Samakan format supaya "Tata Usaha", "tata usaha", atau spasi berlebih tetap terbaca
    $jabatan = strtolower(trim($user->jabatan ?? ''));

    // Lonceng notifikasi hanya untuk jabatan yang menerima disposisi (bukan Kepala Sekolah & Tata Usaha)
    $tampilNotif = !in_array($jabatan, ['kepala sekolah', 'tata usaha']);

    // Generate Sertifikat hanya untuk Tata Usaha & Waka Humas
    $bisaSertifikat = in_array($jabatan, ['tata usaha', 'waka humas']);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} - SIM Sekolah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --brand: #1625C4;
            --bar-height: 64px;
        }
        body { background: #F4F6FB; font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--brand);
            position: fixed;
            top: 0; left: 0;
            color: #fff;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        .sidebar .brand {
            height: var(--bar-height);
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.2;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            flex-shrink: 0;
        }
        .sidebar .brand img {
            width: 38px; height: 38px;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
            padding: 3px;
            flex-shrink: 0;
        }
        .sidebar .sidebar-menu {
            flex: 1;
            overflow-y: auto;
        }
        .sidebar .menu-label {
            padding: 16px 20px 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
        }
        .sidebar a.nav-link {
            color: rgba(255,255,255,0.85);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            border-left: 3px solid transparent;
        }
        .sidebar a.nav-link:hover {
            background: rgba(255,255,255,0.08);
        }
        .sidebar a.nav-link.active {
            background: rgba(255,255,255,0.12);
            border-left-color: #fff;
            font-weight: 600;
        }

        /* Profil di kiri bawah */
        .sidebar .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.15);
            padding: 12px 16px;
            flex-shrink: 0;
        }
        .sidebar .profile-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            background: transparent;
            border: 0;
            color: #fff;
            text-align: left;
            padding: 6px;
            border-radius: 10px;
            cursor: pointer;
        }
        .sidebar .profile-trigger:hover { background: rgba(255,255,255,0.1); }
        .sidebar .profile-trigger::after { display: none; }
        .sidebar .profile-trigger .avatar-circle { background: #fff; flex-shrink: 0; }
        .sidebar .profile-trigger .p-name { font-size: 13px; font-weight: 600; }
        .sidebar .profile-trigger .p-role { font-size: 11px; color: rgba(255,255,255,0.65); }

        .profile-dropdown {
            min-width: 218px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            padding: 6px;
        }
        .profile-dropdown .dropdown-item {
            border-radius: 7px;
            padding: 9px 12px;
            font-size: 14px;
        }
        .profile-dropdown .dropdown-item:hover { background: #f5f5f5; }
        .profile-dropdown .dropdown-item.text-danger:hover { background: #fde8e8; }

        /* ================= TOP NAVBAR (tanpa tulisan, hanya garis) ================= */
        .topbar {
            position: fixed;
            top: 0; left: 250px; right: 0;
            height: var(--bar-height);
            background: #fff;
            border-bottom: 1px solid #E3E6EF;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
            padding: 0 32px;
            z-index: 900;
        }

        /* ================= KONTEN ================= */
        .main-content {
            margin-left: 250px;
            padding: calc(var(--bar-height) + 24px) 32px 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .stat-card .icon-box {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(22,37,196,0.1);
            color: var(--brand);
        }
        .card-panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .avatar-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #E7EAFB;
            color: var(--brand);
            display: flex; align-items: center; justify-content: center;
            font-weight: 600;
            font-size: 12px;
        }
        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-selesai { background: #E3F9E5; color: #1B8A3E; }
        .badge-diproses { background: #FFF4DE; color: #B7791F; }
        .badge-belum { background: #FDE8E8; color: #C53030; }

        /* ================= NOTIFIKASI ================= */
        .notif-bell {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #fff;
            border: 1px solid #E3E6EF;
            color: #555;
            position: relative;
        }
        .notif-bell:hover { background: #f5f5f5; }
        .notif-dropdown {
            width: 340px;
            padding: 0;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            margin-top: 10px !important;
        }
        .notif-item {
            display: block;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
            color: #333;
        }
        .notif-item:hover { background: #f8f9fb; }
        .notif-item.unread { background: #F4F6FE; }
    </style>

    @stack('styles')
</head>
<body>

    {{-- ================= SIDEBAR ================= --}}
    <div class="sidebar">
        <div class="brand">
            <img src="{{ $logoSekolah }}" alt="Logo {{ $namaSekolah }}" onerror="this.style.display='none'">
            <span>{{ $namaSekolah }}</span>
        </div>

        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa fa-grip"></i> Dashboard
            </a>

            @if($jabatan === 'kepala sekolah')
                {{-- Kepala Sekolah: Dashboard + Disposisi (buat/kelola disposisi) --}}
                <a href="{{ route('disposisi.index') }}" class="nav-link {{ request()->routeIs('disposisi.index') || request()->routeIs('disposisi.store') ? 'active' : '' }}">
                    <i class="fa fa-share"></i> Disposisi
                </a>
            @elseif($jabatan === 'tata usaha')
                {{-- Tata Usaha: tanpa menu Disposisi & Disposisi Saya --}}
                <a href="{{ route('surat-masuk.index') }}" class="nav-link {{ request()->routeIs('surat-masuk.*') ? 'active' : '' }}">
                    <i class="fa fa-inbox"></i> Surat Masuk
                </a>
                <a href="{{ route('surat-keluar.index', ['create' => 1]) }}" class="nav-link {{ request()->routeIs('surat-keluar.*') ? 'active' : '' }}">
                    <i class="fa fa-plus-circle"></i> Tambah Surat Keluar
                </a>
                <a href="{{ route('mitra.index') }}" class="nav-link {{ request()->routeIs('mitra.*') ? 'active' : '' }}">
                    <i class="fa fa-handshake"></i> Kerja Sama Industri
                </a>
                <a href="{{ route('sertifikat.index') }}" class="nav-link {{ request()->routeIs('sertifikat.*') ? 'active' : '' }}">
                    <i class="fa fa-certificate"></i> Generate Sertifikat
                </a>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Manajemen User
                </a>
            @else
                {{-- Jabatan lain (Waka, Pembina OSIS, dst): lihat disposisi yang ditujukan ke mereka --}}
                <a href="{{ route('disposisi.saya') }}" class="nav-link {{ request()->routeIs('disposisi.saya*') ? 'active' : '' }}">
                    <i class="fa fa-share"></i> Disposisi Saya
                </a>

                {{-- Generate Sertifikat: hanya Waka Humas (Tata Usaha sudah punya menunya di blok atas) --}}
                @if($bisaSertifikat)
                    <a href="{{ route('sertifikat.index') }}" class="nav-link {{ request()->routeIs('sertifikat.*') ? 'active' : '' }}">
                        <i class="fa fa-certificate"></i> Generate Sertifikat
                    </a>
                @endif
            @endif
        </div>

        {{-- Profil di kiri bawah --}}
        <div class="sidebar-footer dropup">
            <button type="button" class="profile-trigger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle">
                    {{ strtoupper(substr($user->name ?? 'AD', 0, 2)) }}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="p-name text-truncate">{{ $user->name ?? 'Admin' }}</div>
                    <div class="p-role text-truncate">{{ $user->jabatan ?? '' }}</div>
                </div>
                <i class="fa fa-chevron-up" style="font-size:11px;"></i>
            </button>

            <ul class="dropdown-menu profile-dropdown">
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fa fa-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    {{-- ================= TOP NAVBAR (tanpa tulisan) ================= --}}
    <div class="topbar">
        @if($tampilNotif)
        <div class="dropdown">
            <a href="javascript:void(0)" class="notif-bell" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell"></i>
                <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size:10px;">
                    0
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-end notif-dropdown" aria-labelledby="notifBell">
                <div class="px-3 py-2 border-bottom fw-bold" style="font-size:14px;">Disposisi Masuk</div>
                <div id="notifList" style="max-height: 320px; overflow-y: auto;">
                    <div class="text-center text-muted py-4 small">Memuat...</div>
                </div>
                <a href="{{ route('disposisi.saya') }}" class="d-block text-center py-2 border-top small text-decoration-none">
                    Lihat Semua
                </a>
            </div>
        </div>
        @endif
    </div>

    {{-- ================= KONTEN ================= --}}
    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        @if($tampilNotif)
        // Escape teks supaya aman dari XSS
        function esc(text) {
            return $('<div>').text(text ?? '').html();
        }

        function loadNotifikasiDisposisi() {
            $.get('{{ route("disposisi.notifikasi") }}', function (res) {
                const badge = $('#notifBadge');
                const list = $('#notifList');

                if (res.unread_count > 0) {
                    badge.text(res.unread_count).removeClass('d-none');
                } else {
                    badge.addClass('d-none');
                }

                if (res.items.length === 0) {
                    list.html('<div class="text-center text-muted py-4 small">Belum ada disposisi.</div>');
                    return;
                }

                let html = '';
                res.items.forEach(function (item) {
                    const urgentBadge = ['sangat_segera', 'rahasia'].includes(item.sifat)
                        ? '<span class="badge bg-danger ms-1" style="font-size:9px;">Urgent</span>' : '';

                    html += `
                        <a href="{{ route('disposisi.saya') }}?open=${encodeURIComponent(item.id)}" class="notif-item ${!item.sudah_baca ? 'unread' : ''}">
                            <div class="d-flex justify-content-between align-items-start">
                                <span style="font-size:13px; ${!item.sudah_baca ? 'font-weight:600;' : ''}">
                                    ${esc(item.perihal)} ${urgentBadge}
                                </span>
                            </div>
                            <small class="text-muted">${esc(item.waktu)}</small>
                        </a>
                    `;
                });

                list.html(html);
            }).fail(function () {
                $('#notifList').html('<div class="text-center text-danger py-4 small">Gagal memuat notifikasi.</div>');
            });
        }

        $(function () {
            loadNotifikasiDisposisi();
            setInterval(loadNotifikasiDisposisi, 30000);
        });
        @endif
    </script>

    @stack('scripts')
</body>
</html>
