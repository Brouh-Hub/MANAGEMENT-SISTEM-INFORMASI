<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIM Sekolah</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="antialiased">

<div class="relative min-h-screen w-full overflow-hidden bg-gradient-to-br from-[#1624C4] via-[#2433D3] to-[#3B4CE0] flex items-center justify-center px-4 py-10">

    {{-- Shape dekoratif latar --}}
    <div class="pointer-events-none absolute -top-32 -left-32 w-96 h-96 bg-white/10 rounded-full"></div>
    <div class="pointer-events-none absolute top-1/4 -right-24 w-72 h-72 bg-white/10 rounded-full"></div>
    <div class="pointer-events-none absolute -bottom-40 left-1/3 w-[28rem] h-[28rem] bg-white/5 rounded-full"></div>
    <svg class="pointer-events-none absolute bottom-0 left-0 w-full h-40" viewBox="0 0 500 80" preserveAspectRatio="none">
        <path d="M0,40 C100,80 200,0 300,40 C400,80 450,20 500,40 L500,100 L0,100 Z" fill="white" fill-opacity="0.08"/>
    </svg>

    {{-- Konten tengah --}}
    <div class="relative z-10 w-full max-w-[420px]">

        {{-- Logo + nama sekolah --}}
        <div class="mb-6 flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-white shadow-lg flex items-center justify-center mb-3">
                {{-- Pakai logo dari Setting sekolah; kalau belum ada, pakai public/images/logo-smkn4-tik.png --}}
                <img src="{{ $sekolah?->logo_path ? asset('storage/'.$sekolah->logo_path) : asset('images/logo-smkn4-tik.png') }}"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    class="w-10 h-10 object-contain"
                    alt="Logo">
                <i class="fa fa-shield-halved text-[#1624C4] text-2xl" style="display:none;"></i>
            </div>
            <span class="font-bold text-white text-lg leading-tight">{{ $sekolah->nama ?? 'SIM Sekolah' }}</span>
            <span class="text-[13px] text-[#dde1fa]">Satu ruang digital untuk seluruh sekolah</span>
        </div>

        {{-- Kartu form (modal) --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            <div class="text-center">
                <h1 class="font-bold text-[23px] text-[#12141a]">Masuk akun</h1>
                <p class="mt-1 text-[13.5px] text-[#8a8f98]">Silakan masuk untuk melanjutkan</p>
            </div>

            <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6">
                @csrf

                {{-- Email/Username --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-xs font-semibold text-[#4a4f55]">Email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa fa-user"></i>
                        </span>
                        <input
                            type="text"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-lg border bg-gray-50 pl-11 pr-4 py-3 outline-none text-sm transition focus:bg-white focus:border-[#1624C4] focus:ring-4 focus:ring-[#1624C4]/10 @error('email') border-red-400 @else border-gray-200 @enderror"
                            placeholder="nama@sekolah.com"
                            autocomplete="username"
                            autofocus
                            required
                        >
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-xs font-semibold text-[#4a4f55]">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input
                            x-bind:type="show ? 'text' : 'password'"
                            type="password"
                            name="password"
                            class="w-full rounded-lg border bg-gray-50 pl-11 pr-11 py-3 outline-none text-sm transition focus:bg-white focus:border-[#1624C4] focus:ring-4 focus:ring-[#1624C4]/10 @error('password') border-red-400 @else border-gray-200 @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" x-on:click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="Tampilkan password">
                            <i class="fa" x-bind:class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 accent-[#1624C4]">
                    <label for="remember" class="text-xs text-[#4a4f55]">Ingat saya</label>
                </div>

                {{-- Button --}}
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-[#1624C4] py-3 font-bold text-white shadow-lg shadow-[#1624C4]/30 transition hover:bg-[#101ca0]"
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

        {{-- Footer --}}
        <p class="mt-6 text-center text-xs text-white/70">
            &copy; {{ date('Y') }} {{ $sekolah->nama ?? 'SIM Sekolah' }}
        </p>
    </div>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

</body>
</html>