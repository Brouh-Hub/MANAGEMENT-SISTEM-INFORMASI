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

    // Menu MoU/PKS: TU & Waka Humas (kelola), Kepala Sekolah (lihat)
    $bisaMou = in_array($jabatan, ['tata usaha', 'waka humas', 'kepala sekolah']);
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
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-status.dot::before {
            content: '';
            display: inline-block;
            width: 6px; height: 6px;
            margin-right: 6px;
            border-radius: 50%;
            background: currentColor;
            vertical-align: middle;
        }
        .badge-info { background: #E3F0FD; color: #1D5FA8; }
        .badge-selesai { background: #E3F9E5; color: #1B8A3E; }
        .badge-diproses { background: #FFF4DE; color: #B7791F; }
        .badge-belum { background: #FDE8E8; color: #C53030; }
        .badge-arsip { background: #EDEFF5; color: #4A5068; }
        .badge-ditolak { background: #FDE8E8; color: #9B1C1C; }

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

        /* ================= NOTIFIKASI (pengganti alert) ================= */
        #wadah-notif {
            position: fixed; top: 76px; right: 20px; z-index: 2100;
            display: flex; flex-direction: column; gap: 10px;
            width: 360px; max-width: calc(100vw - 32px);
            pointer-events: none;
        }
        .notif {
            pointer-events: auto;
            display: flex; gap: 12px; align-items: flex-start;
            background: #fff; border-radius: 12px; padding: 14px 14px 14px 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 64, .15);
            border-left: 4px solid var(--warna);
            position: relative; overflow: hidden;
            animation: notif-masuk .25s ease-out;
            font-size: 14px; color: #222;
        }
        .notif.keluar { animation: notif-keluar .2s ease-in forwards; }
        .notif .ikon {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--latar); color: var(--warna); font-size: 14px;
        }
        .notif .judul { font-weight: 700; margin-bottom: 2px; }
        .notif .isi { flex: 1; min-width: 0; word-wrap: break-word; line-height: 1.4; }
        .notif .tutup { border: 0; background: none; color: #999; padding: 0 2px; line-height: 1; font-size: 16px; }
        .notif .tutup:hover { color: #333; }
        .notif .waktu { position: absolute; left: 0; bottom: 0; height: 3px; background: var(--warna); opacity: .35; animation: notif-waktu linear forwards; }
        .notif-sukses { --warna: #16a34a; --latar: #e7f7ec; }
        .notif-gagal  { --warna: #dc2626; --latar: #fdecec; }
        .notif-info   { --warna: #1625C4; --latar: #eceefd; }
        @keyframes notif-masuk  { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: none; } }
        @keyframes notif-keluar { to { opacity: 0; transform: translateX(30px); } }
        @keyframes notif-waktu  { from { width: 100%; } to { width: 0; } }

        /* ================= KOTAK KONFIRMASI (pengganti confirm) ================= */
        .konfirmasi-latar {
            position: fixed; inset: 0; z-index: 2000;
            background: rgba(10, 14, 40, .45);
            display: flex; align-items: center; justify-content: center;
            padding: 16px;
            animation: konf-latar .15s ease-out;
        }
        .konfirmasi-kotak {
            background: #fff; border-radius: 16px; width: 100%; max-width: 400px;
            padding: 26px 24px 20px; text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
            animation: konf-kotak .2s ease-out;
        }
        .konfirmasi-kotak .ikon {
            width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 14px;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        .konfirmasi-kotak .judul { font-size: 18px; font-weight: 700; margin-bottom: 6px; color: #12141a; }
        .konfirmasi-kotak .pesan { font-size: 14px; color: #5b6070; margin-bottom: 22px; line-height: 1.5; white-space: pre-line; max-height: 50vh; overflow-y: auto; }
        .konfirmasi-kotak .tombol { display: flex; gap: 10px; }
        .konfirmasi-kotak .tombol .btn { flex: 1; padding: 10px; border-radius: 10px; font-weight: 600; }
        @keyframes konf-latar { from { opacity: 0; } }
        @keyframes konf-kotak { from { opacity: 0; transform: scale(.92); } }

        @media (max-width: 767.98px) {
            #wadah-notif { top: 72px; right: 16px; left: 16px; width: auto; }
        }

        /* ================= KONTROL DATATABLES (Tampilkan & Cari) ================= */
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            border-color: #dee2e6;
            margin: 0 6px;
            min-width: 72px;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 8px 12px 8px 36px;
            min-width: 260px;
            margin-left: 0 !important;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' fill='%23999'%3E%3Cpath d='M416 208c0 45.9-14.9 88.3-40 122.7l126.6 126.7c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z'/%3E%3C/svg%3E") no-repeat 12px center / 15px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 .2rem rgba(22,37,196,.12);
            outline: 0;
        }
        .dataTables_wrapper .dataTables_info { font-size: 13px; color: #6c757d; }

        /* ================= RESPONSIF (HP & TABLET) ================= */
        .btn-menu {
            display: none;
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1px solid #E3E6EF;
            background: #fff;
            color: #333;
            align-items: center; justify-content: center;
            margin-right: auto;
            font-size: 18px;
        }
        .judul-topbar { display: none; font-weight: 600; font-size: 15px; color: #222; margin-right: auto; }
        .sidebar-backdrop {
            position: fixed; inset: 0;
            background: rgba(10, 14, 40, .45);
            z-index: 999;
            opacity: 0; pointer-events: none;
            transition: opacity .2s;
        }

        /* Tablet & HP: sidebar jadi menu geser (tombol ☰ di kiri atas) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .25s ease;
                box-shadow: 0 0 30px rgba(0,0,0,.25);
            }
            body.sidebar-terbuka .sidebar { transform: none; }
            body.sidebar-terbuka .sidebar-backdrop { opacity: 1; pointer-events: auto; }
            body.sidebar-terbuka { overflow: hidden; }

            .topbar { left: 0; padding: 0 16px; gap: 10px; }
            .btn-menu { display: flex; margin-right: 0; }
            .judul-topbar { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

            .main-content { margin-left: 0; padding: calc(var(--bar-height) + 16px) 16px 24px; }
        }

        /* HP */
        @media (max-width: 767.98px) {
            .main-content h3 { font-size: 1.35rem; }
            .main-content h4 { font-size: 1.2rem; }
            .main-content h5 { font-size: 1.05rem; }
            .card-panel, .stat-card { padding: 14px; }

            /* Judul halaman + tombol: turun ke bawah kalau tidak muat */
            .main-content .d-flex.justify-content-between { flex-wrap: wrap; gap: .5rem; }

            /* Kolom pencarian yang lebarnya dikunci -> penuh */
            .main-content [style*="max-width: 3"],
            .main-content [style*="max-width:3"] { max-width: 100% !important; }

            /* ===== Tabel jadi KARTU di HP =====
               Tiap baris = 1 kartu, tiap kolom = "Label : isi".
               Label diambil otomatis dari judul kolom (lihat script labelTabel di bawah).
               Tabel yang tetap ingin digeser ke samping: beri class "tabel-geser". */
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) thead { display: none; }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser),
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) tbody { display: block; width: 100% !important; }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) tr {
                display: block;
                background: #fff;
                border: 1px solid #E6E8F0;
                border-radius: 12px;
                padding: 8px 12px;
                margin-bottom: 10px;
                box-shadow: 0 1px 2px rgba(0,0,0,.03);
            }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
                border: 0;
                padding: 5px 0 !important;
                text-align: right;
                white-space: normal;
                word-break: break-word;
                font-size: 14px;
                background: transparent !important;
                box-shadow: none !important;
            }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td::before {
                content: attr(data-label);
                flex: 0 0 38%;
                text-align: left;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .03em;
                color: #8a8f98;
                padding-top: 2px;
            }
            /* Kolom tanpa judul (mis. Aksi) & baris "Belum ada data" */
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td[data-label=""]::before,
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td[colspan]::before { display: none; }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td[colspan] { justify-content: center; text-align: center; }
            /* Kolom kosong (mis. email/website belum diisi) tidak perlu tampil sebagai label saja */
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td:empty { display: none; }
            /* Kolom aksi (berisi tombol) di baris paling bawah kartu */
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td.kolom-aksi {
                border-top: 1px dashed #E6E8F0;
                margin-top: 4px;
                padding-top: 8px !important;
                justify-content: flex-end;
            }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td.kolom-aksi::before { display: none; }
            .main-content .card-panel table.table:not(.table-borderless):not(.tabel-geser) td.kolom-aksi .btn { padding: 6px 12px; }

            /* Tabel yang memang mau digeser (class tabel-geser) */
            .main-content .card-panel table.table.tabel-geser { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .main-content .card-panel table.table.tabel-geser th,
            .main-content .card-panel table.table.tabel-geser td { white-space: nowrap; }

            /* Kontrol DataTables: "Tampilkan" kecil di kiri, kolom Cari selebar layar */
            .dataTables_wrapper > .row:first-child { row-gap: 8px; margin-bottom: 12px; }
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter { text-align: left !important; float: none !important; }
            .dataTables_wrapper .dataTables_filter label { display: block; width: 100%; }
            .dataTables_wrapper .dataTables_filter input { display: block; width: 100% !important; min-width: 0; }
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate { float: none !important; text-align: center !important; margin-top: 8px; }
            .dataTables_wrapper .pagination { justify-content: center; flex-wrap: wrap; }

            /* Kotak preview PDF di modal */
            #detail-preview, #periksa-preview, #sk-kepsek-preview { height: 60vh !important; }
            .modal-footer { flex-wrap: wrap; }
            .modal-footer .btn { flex: 1 1 auto; }

            .notif-dropdown { width: calc(100vw - 32px); }
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Latar gelap saat menu dibuka di HP (klik untuk menutup) --}}
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    {{-- ================= SIDEBAR ================= --}}
    <div class="sidebar" id="sidebar">
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
                <a href="{{ route('mou-pks.index') }}" class="nav-link {{ request()->routeIs('mou-pks.*') ? 'active' : '' }}">
                    <i class="fa fa-file-signature"></i> MoU / PKS
                </a>
            @elseif($jabatan === 'kepala tata usaha')
                {{-- Kepala Tata Usaha: periksa surat masuk & keluar sebelum ke Kepala Sekolah --}}
                @php
                    $jumlahKonfirmasi = \App\Models\SuratMasuk::where('status_konfirmasi', 'menunggu')->count()
                        + \App\Models\SuratKeluar::where('status', 'menunggu_kepala_tu')->count();
                @endphp
                <a href="{{ route('konfirmasi-surat.index') }}" class="nav-link {{ request()->routeIs('konfirmasi-surat.*') ? 'active' : '' }}">
                    <i class="fa fa-clipboard-check"></i> Konfirmasi Surat
                    @if($jumlahKonfirmasi)
                        <span class="badge rounded-pill bg-danger ms-auto">{{ $jumlahKonfirmasi }}</span>
                    @endif
                </a>
                <a href="{{ route('disposisi.saya') }}" class="nav-link {{ request()->routeIs('disposisi.saya*') ? 'active' : '' }}">
                    <i class="fa fa-share"></i> Disposisi Saya
                </a>
            @elseif($jabatan === 'tata usaha')

                {{-- Tata Usaha: tanpa menu Disposisi & Disposisi Saya --}}
                <a href="{{ route('surat-masuk.index') }}" class="nav-link {{ request()->routeIs('surat-masuk.*') ? 'active' : '' }}">
                    <i class="fa fa-inbox"></i> Surat Masuk
                </a>
                <a href="{{ route('surat-keluar.index') }}" class="nav-link {{ request()->routeIs('surat-keluar.*') ? 'active' : '' }}">
                    <i class="fa fa-paper-plane"></i> Surat Keluar
                </a>
                <div class="menu-label">Kerja Sama & Dokumen</div>
                <a href="{{ route('mitra.index') }}" class="nav-link {{ request()->routeIs('mitra.*') ? 'active' : '' }}">
                    <i class="fa fa-handshake"></i> Kerja Sama Industri
                </a>
                <a href="{{ route('mou-pks.index') }}" class="nav-link {{ request()->routeIs('mou-pks.*') ? 'active' : '' }}">
                    <i class="fa fa-file-signature"></i> MoU / PKS
                </a>
                <a href="{{ route('sertifikat.index') }}" class="nav-link {{ request()->routeIs('sertifikat.*') ? 'active' : '' }}">
                    <i class="fa fa-certificate"></i> Generate Sertifikat
                </a>

                <div class="menu-label">Administrasi</div>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Manajemen User
                </a>
                <a href="{{ route('log-login.index') }}" class="nav-link {{ request()->routeIs('log-login.*') ? 'active' : '' }}">
                    <i class="fa fa-clock-rotate-left"></i> Log Aktivitas
                </a>
                <a href="{{ route('pengaturan-surat.index') }}" class="nav-link {{ request()->routeIs('pengaturan-surat.*') ? 'active' : '' }}">
                    <i class="fa fa-stamp"></i> Pengaturan Surat
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
                    <a href="{{ route('mitra.index') }}" class="nav-link {{ request()->routeIs('mitra.*') ? 'active' : '' }}">
                        <i class="fa fa-handshake"></i> Kerja Sama Industri
                    </a>
                    <a href="{{ route('mou-pks.index') }}" class="nav-link {{ request()->routeIs('mou-pks.*') ? 'active' : '' }}">
                        <i class="fa fa-file-signature"></i> MoU / PKS
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
                    <a class="dropdown-item" href="{{ route('profil.show') }}">
                        <i class="fa fa-user me-2"></i> Profil Saya
                    </a>
                </li>
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
        {{-- Tombol menu (hanya muncul di HP/tablet) --}}
        <button type="button" class="btn-menu" id="btn-menu" aria-label="Buka menu">
            <i class="fa fa-bars"></i>
        </button>
        <div class="judul-topbar">{{ $title ?? $namaSekolah }}</div>

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

        // Modal berisi form: tutup hanya lewat tombol, bukan klik luar / Esc
        document.addEventListener('show.bs.modal', function (e) {
            if (!e.target.querySelector('form')) return;
            const m = bootstrap.Modal.getInstance(e.target);
            m._config.backdrop = 'static';
            m._config.keyboard = false;
        });

        /* =================================================================
           NOTIFIKASI & KONFIRMASI CUSTOM
           - notif('pesan', 'sukses' | 'gagal' | 'info')   -> toast pojok kanan atas
           - alert('pesan')  -> otomatis jadi toast (jenis ditebak dari isi pesan)
           - await konfirmasi('pesan')  -> kotak konfirmasi, hasil true / false
           ================================================================= */
        (function () {
            const JUDUL = { sukses: 'Berhasil', gagal: 'Gagal', info: 'Informasi' };
            const IKON  = { sukses: 'fa-check', gagal: 'fa-xmark', info: 'fa-info' };
            const KUNCI = 'notif-tertunda';

            function wadah() {
                let w = document.getElementById('wadah-notif');
                if (!w) {
                    w = document.createElement('div');
                    w.id = 'wadah-notif';
                    document.body.appendChild(w);
                }
                return w;
            }

            // Tebak jenis pesan dari isinya (untuk alert lama)
            function tebakJenis(pesan) {
                const t = String(pesan).toLowerCase();
                if (/(sedang|memuat|mohon tunggu)/.test(t)) return 'info';
                if (/(gagal|salah|error|tidak bisa|tidak dapat|tidak ditemukan|wajib|terlalu banyak|ooops|ditolak|kedaluwarsa)/.test(t)) return 'gagal';
                if (/(berhasil|disimpan|tersimpan|dihapus|disetujui|diajukan|dikirim|diperbarui|dibuat|diarsipkan|dikembalikan|diteruskan|selesai)/.test(t)) return 'sukses';
                return 'info';
            }

            window.notif = function (pesan, jenis, durasi) {
                jenis = JUDUL[jenis] ? jenis : tebakJenis(pesan);
                durasi = durasi || (jenis === 'gagal' ? 6000 : 4000);

                const el = document.createElement('div');
                el.className = 'notif notif-' + jenis;
                el.dataset.jenis = jenis;
                el.dataset.sampai = Date.now() + durasi;
                el.innerHTML =
                    '<div class="ikon"><i class="fa ' + IKON[jenis] + '"></i></div>' +
                    '<div class="isi"><div class="judul"></div><div class="teks"></div></div>' +
                    '<button type="button" class="tutup" aria-label="Tutup">&times;</button>' +
                    '<div class="waktu" style="animation-duration:' + durasi + 'ms"></div>';
                el.querySelector('.judul').textContent = JUDUL[jenis];
                el.querySelector('.teks').textContent = String(pesan ?? '');

                const tutup = () => {
                    if (el.classList.contains('keluar')) return;
                    el.classList.add('keluar');
                    setTimeout(() => el.remove(), 200);
                };
                el.querySelector('.tutup').addEventListener('click', tutup);
                setTimeout(tutup, durasi);

                wadah().appendChild(el);
            };

            // Ganti alert bawaan browser
            window.alert = function (pesan) { window.notif(pesan); };

            // Notifikasi yang masih tampil ikut dibawa kalau halaman dimuat ulang
            window.addEventListener('pagehide', function () {
                const sisa = [...document.querySelectorAll('#wadah-notif .notif:not(.keluar)')]
                    .map(n => ({ pesan: n.querySelector('.teks').textContent, jenis: n.dataset.jenis, sisa: n.dataset.sampai - Date.now() }))
                    .filter(n => n.sisa > 800);
                try { sessionStorage.setItem(KUNCI, JSON.stringify(sisa)); } catch (e) {}
            });
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    const daftar = JSON.parse(sessionStorage.getItem(KUNCI) || '[]');
                    sessionStorage.removeItem(KUNCI);
                    daftar.forEach(n => window.notif(n.pesan, n.jenis, Math.max(n.sisa, 2500)));
                } catch (e) {}
            });

            // Kotak konfirmasi (pengganti confirm). Pakai: if (!await konfirmasi('Yakin?')) return;
            window.konfirmasi = function (pesan, opsi) {
                opsi = opsi || {};
                const t = String(pesan).toLowerCase();
                const bahaya = opsi.bahaya ?? /(hapus|arsip|bersihkan|clear|batalkan|kosongkan)/.test(t);
                const setuju = !bahaya && /(setujui|ajukan|teruskan|kirim)/.test(t);

                const judul = opsi.judul
                    || (bahaya ? (/hapus|bersihkan|clear|kosongkan/.test(t) ? 'Hapus data?' : 'Yakin lanjutkan?')
                               : (setuju ? 'Konfirmasi' : 'Yakin?'));
                const tombolYa = opsi.tombol
                    || (/hapus|bersihkan|clear|kosongkan/.test(t) ? 'Ya, hapus'
                        : /arsip/.test(t) ? 'Ya, arsipkan'
                        : /setujui/.test(t) ? 'Ya, setujui'
                        : /ajukan/.test(t) ? 'Ya, ajukan'
                        : 'Ya, lanjutkan');

                const warna = bahaya ? ['#fdecec', '#dc2626', 'fa-trash-can', 'btn-danger']
                    : setuju ? ['#e7f7ec', '#16a34a', 'fa-circle-check', 'btn-success']
                    : ['#eceefd', '#1625C4', 'fa-circle-question', 'btn-primary'];
                if (bahaya && /arsip|batalkan/.test(t)) warna[2] = 'fa-triangle-exclamation';

                return new Promise(function (selesai) {
                    const latar = document.createElement('div');
                    latar.className = 'konfirmasi-latar';
                    latar.innerHTML =
                        '<div class="konfirmasi-kotak" role="dialog" aria-modal="true">' +
                        '<div class="ikon" style="background:' + warna[0] + ';color:' + warna[1] + '"><i class="fa ' + warna[2] + '"></i></div>' +
                        '<div class="judul"></div><div class="pesan"></div>' +
                        '<div class="tombol"><button type="button" class="btn btn-light btn-batal">Batal</button>' +
                        '<button type="button" class="btn ' + warna[3] + ' btn-ya"></button></div></div>';
                    latar.querySelector('.judul').textContent = judul;
                    latar.querySelector('.pesan').textContent = String(pesan ?? '');
                    latar.querySelector('.btn-ya').textContent = tombolYa;

                    const tutup = hasil => {
                        document.removeEventListener('keydown', tombol);
                        latar.remove();
                        selesai(hasil);
                    };
                    const tombol = e => {
                        if (e.key === 'Escape') tutup(false);
                        if (e.key === 'Enter') tutup(true);
                    };

                    latar.querySelector('.btn-batal').addEventListener('click', () => tutup(false));
                    latar.querySelector('.btn-ya').addEventListener('click', () => tutup(true));
                    latar.addEventListener('click', e => { if (e.target === latar) tutup(false); });
                    document.addEventListener('keydown', tombol);

                    document.body.appendChild(latar);
                    latar.querySelector('.btn-ya').focus();
                });
            };
        })();

        // ===== Bahasa Indonesia untuk semua tabel DataTables =====
        if ($.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    search: '',
                    searchPlaceholder: 'Cari data...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(disaring dari _MAX_ data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data',
                    processing: 'Memuat...',
                    loadingRecords: 'Memuat...',
                    paginate: { first: '«', last: '»', previous: '‹', next: '›' }
                }
            });
        }

        // ===== Label kolom untuk tampilan kartu di HP =====
        // Setiap <td> diberi data-label dari judul kolomnya (<th>).
        // Kolom yang isinya tombol/aksi diberi class "kolom-aksi".
        function labelTabel(tabel) {
            const judul = $(tabel).find('thead th').map((i, th) => $(th).text().trim()).get();
            if (!judul.length) return;

            $(tabel).find('tbody tr').each(function () {
                $(this).children('td').each(function (i) {
                    const label = judul[i] ?? '';
                    $(this).attr('data-label', /^aksi$/i.test(label) ? '' : label);

                    const isinyaTombol = $(this).find('.btn, button').length > 0
                        && $(this).text().trim().length < 40;
                    $(this).toggleClass('kolom-aksi', /^aksi$/i.test(label) || (label === '' && isinyaTombol));
                });
            });
        }

        $(function () {
            $('.main-content table.table').each((i, t) => labelTabel(t));

            // Tabel DataTables: isi dimuat lewat AJAX -> beri label tiap selesai digambar
            $(document).on('draw.dt', e => labelTabel(e.target));

            // Tabel yang isinya diganti lewat JS (mis. riwayat) -> beri label saat berubah
            const amati = new MutationObserver(daftar => {
                daftar.forEach(m => {
                    const t = $(m.target).closest('table.table')[0];
                    if (t) labelTabel(t);
                });
            });
            document.querySelectorAll('.main-content table.table tbody').forEach(tb => amati.observe(tb, { childList: true }));
        });

        // ===== Cegah halaman lama tampil lagi lewat tombol Back =====
        // Kalau halaman diambil dari cache "Back" browser, muat ulang dari server.
        // Setelah logout, server akan mengarahkan ke halaman login.
        window.addEventListener('pageshow', function (e) {
            const dariBack = e.persisted
                || (performance.getEntriesByType('navigation')[0]?.type === 'back_forward');
            if (dariBack) {
                location.reload();
            }
        });

        // ===== Menu geser di HP/tablet =====
        $(function () {
            const tutupMenu = () => $('body').removeClass('sidebar-terbuka');

            $('#btn-menu').on('click', () => $('body').toggleClass('sidebar-terbuka'));
            $('#sidebar-backdrop').on('click', tutupMenu);
            $('#sidebar .nav-link').on('click', tutupMenu);
            $(document).on('keydown', e => { if (e.key === 'Escape') tutupMenu(); });
            $(window).on('resize', () => { if (window.innerWidth >= 992) tutupMenu(); });
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
