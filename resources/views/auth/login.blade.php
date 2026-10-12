<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIM Sekolah</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="relative min-h-screen w-full overflow-hidden bg-white flex">

    {{-- Panel kiri (biru) --}}
    <div class="relative w-full lg:w-[45%] min-h-screen bg-gradient-to-br from-[#1624C4] to-[#3B4CE0] overflow-hidden">

        {{-- Shape dekoratif --}}
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-white/10 rounded-full"></div>
        <div class="absolute top-1/3 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
        <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 500 80" preserveAspectRatio="none">
            <path d="M0,40 C100,80 200,0 300,40 C400,80 450,20 500,40 L500,100 L0,100 Z" fill="white" fill-opacity="0.08"/>
        </svg>

        <div class="relative z-10 p-10 flex flex-col justify-between min-h-screen">

            {{-- Logo + nama sekolah --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/15 flex items-center justify-center">
                    {{-- Ganti src ini kalau sudah ada logo asli sekolah --}}
                    <img src="{{ $sekolah?->logo_path ? asset('storage/'.$sekolah->logo_path) : '' }}"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        class="w-6 h-6 object-contain"
                        alt="Logo">
                    <i class="fa fa-shield-halved text-white" style="display:none;"></i>
                </div>
                <span class="font-bold text-white text-lg">{{ $sekolah->nama ?? 'SIM Sekolah' }}</span>
            </div>

            {{-- Headline --}}
            <div class="mb-24">
                <h1 class="font-bold text-white text-[34px] leading-tight mb-4">
                    Satu ruang digital<br>untuk seluruh sekolah
                </h1>
                <p class="text-[#dde1fa] text-[15px] mb-8">
                    Presensi, nilai, dan administrasi, terhubung<br>
                    dalam satu sistem yang mudah dipakai.
                </p>

                {{-- Badge statistik --}}
                <div class="inline-flex items-center gap-3 bg-white rounded-xl px-4 py-3 shadow-lg">
                    <div class="w-9 h-9 rounded-lg bg-[#EEF0FD] flex items-center justify-center">
                        <i class="fa fa-graduation-cap text-[#1624C4]"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-[#8a8f98] mb-0 leading-none">Siswa terdaftar</p>
                        <p class="font-bold text-[#12141a] text-[15px] mb-0 leading-tight">{{ $totalSiswa ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel kanan (form) --}}
    <div class="relative flex-1 flex items-center justify-center p-6">

        <div class="absolute top-10 right-10 w-40 h-40 bg-[#EEF0FD] rounded-full blur-2xl opacity-60"></div>
        <div class="absolute bottom-10 right-24 w-24 h-24 bg-[#EEF0FD] rounded-full blur-xl opacity-60"></div>

        <div class="relative z-10 w-full max-w-[400px] bg-white rounded-2xl shadow-xl p-8">

            <h1 class="font-bold text-[23px] text-[#12141a]">Masuk akun</h1>
            <p class="mt-1 text-[13.5px] text-[#8a8f98]">Pilih peranmu untuk melanjutkan</p>

            <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6" x-data="{ role: 'guru' }">
                @csrf
                <input type="hidden" name="role" x-bind:value="role">

                {{-- Role toggle --}}
                <div class="mb-5 inline-flex bg-gray-100 rounded-full p-1 w-full">
                    <button type="button"
                        x-on:click="role = 'guru'"
                        x-bind:class="role === 'guru' ? 'bg-[#1624C4] text-white' : 'text-[#8a8f98]'"
                        class="flex-1 rounded-full py-2 text-sm font-medium transition">
                        Guru
                    </button>
                    <button type="button"
                        x-on:click="role = 'admin'"
                        x-bind:class="role === 'admin' ? 'bg-[#1624C4] text-white' : 'text-[#8a8f98]'"
                        class="flex-1 rounded-full py-2 text-sm font-medium transition">
                        Admin
                    </button>
                </div>

                {{-- Email/Username --}}
                <div class="mb-4">
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa fa-user"></i>
                        </span>
                        <input
                            type="text"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-lg border border-gray-200 pl-11 pr-4 py-3 outline-none focus:border-[#1624C4] text-sm"
                            placeholder="NIS / Username"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <div class="relative" x-data="{ show: false }">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input
                            x-bind:type="show ? 'text' : 'password'"
                            name="password"
                            class="w-full rounded-lg border border-gray-200 pl-11 pr-11 py-3 outline-none focus:border-[#1624C4] text-sm"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" x-on:click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa" x-bind:class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
                    <label for="remember" class="text-xs text-[#4a4f55]">Ingat saya</label>
                </div>

                {{-- Button --}}
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-[#1624C4] py-3 font-bold text-white transition hover:bg-[#101ca0]"
                >
                    Masuk <i class="fa fa-arrow-right text-sm"></i>
                </button>
            </form>

            <div class="my-6 flex items-center gap-3">
                <div class="h-px flex-1 bg-gray-200"></div>
                <span class="text-xs text-[#b0b4c0]">atau</span>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <p class="text-center text-xs text-[#b0b4c0]">Hubungi admin sekolah jika lupa akun</p>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

</body>
</html>
