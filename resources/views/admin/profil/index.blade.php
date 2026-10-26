@extends('layouts.main')

@push('styles')
<style>
    .profil-avatar {
        width: 96px; height: 96px;
        border-radius: 50%;
        background: #E7EAFB;
        color: var(--brand);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 32px;
        margin: 0 auto 12px;
    }
    .profil-cover {
        height: 72px;
        margin: -20px -20px 0;
        border-radius: 12px 12px 0 0;
        background: linear-gradient(135deg, var(--brand), #4a58e6);
    }
    .profil-head { margin-top: -48px; text-align: center; }
    .profil-head .profil-avatar { border: 4px solid #fff; }
    .profil-info { list-style: none; padding: 0; margin: 16px 0 0; }
    .profil-info li {
        display: flex; gap: 10px; align-items: flex-start;
        padding: 10px 0; border-top: 1px solid #EEF0F6; font-size: 13px;
    }
    .profil-info i { width: 16px; color: var(--brand); margin-top: 3px; }
    .profil-info .lbl { color: #8A90A6; font-size: 11px; display: block; }
    .profil-tabs .nav-link { color: #555; border-radius: 8px; font-size: 14px; }
    .profil-tabs .nav-link.active { background: var(--brand); color: #fff; }
    .badge-jabatan { background: #E7EAFB; color: var(--brand); font-weight: 600; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
    .badge-role { background: #FFF4DB; color: #8A6100; font-weight: 600; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
    .pw-wrap { position: relative; }
    .pw-wrap .form-control { padding-right: 42px; }
    .pw-wrap .pw-toggle {
        position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
        border: 0; background: transparent; color: #8A90A6; padding: 6px 10px;
    }
    .pw-meter { height: 6px; border-radius: 4px; background: #EEF0F6; overflow: hidden; }
    .pw-meter span { display: block; height: 100%; width: 0; transition: width .2s, background .2s; }
    .stat-card .nilai { font-size: 26px; font-weight: 700; line-height: 1.1; }
    .readonly-note { font-size: 12px; color: #8A90A6; }
</style>
@endpush

@section('content')
@php
    $inisial = strtoupper(substr($user->name ?? 'AD', 0, 2));
    $roleLabel = $user->role === 'super_admin' ? 'Super Admin' : 'Admin';
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $title }}</h4>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
    </div>
</div>

<div class="row g-3">
    {{-- ============ KARTU PROFIL ============ --}}
    <div class="col-lg-4 col-xl-3">
        <div class="card-panel">
            <div class="profil-cover"></div>
            <div class="profil-head">
                <div class="profil-avatar">{{ $inisial }}</div>
                <h5 class="fw-bold mb-1" id="profil-nama">{{ $user->name }}</h5>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                    @if($user->jabatan)
                        <span class="badge-jabatan">{{ $user->jabatan }}</span>
                    @endif
                    <span class="badge-role">{{ $roleLabel }}</span>
                </div>
            </div>

            <ul class="profil-info">
                <li>
                    <i class="fa fa-envelope"></i>
                    <div class="overflow-hidden">
                        <span class="lbl">Email</span>
                        <span id="profil-email" class="text-break">{{ $user->email }}</span>
                    </div>
                </li>
                <li>
                    <i class="fa fa-school"></i>
                    <div>
                        <span class="lbl">Sekolah</span>
                        {{ $user->sekolah->nama ?? '-' }}
                    </div>
                </li>
                <li>
                    <i class="fa fa-calendar-days"></i>
                    <div>
                        <span class="lbl">Bergabung</span>
                        {{ $user->created_at?->translatedFormat('d F Y') ?? '-' }}
                    </div>
                </li>
            </ul>
        </div>
    </div>

    {{-- ============ TAB ============ --}}
    <div class="col-lg-8 col-xl-9">
        <div class="card-panel">
            <ul class="nav nav-pills profil-tabs gap-1 mb-4" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-data" type="button">
                        <i class="fa fa-user me-1"></i> Data Diri
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-keamanan" type="button">
                        <i class="fa fa-lock me-1"></i> Keamanan
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-aktivitas" type="button">
                        <i class="fa fa-chart-simple me-1"></i> Aktivitas
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- ---------- DATA DIRI ---------- --}}
                <div class="tab-pane fade show active" id="tab-data">
                    <form id="form-profil">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                                <small class="text-danger error-msg" id="profil-error-name"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}">
                                <small class="text-danger error-msg" id="profil-error-email"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <input type="text" class="form-control" value="{{ $user->jabatan ?? '-' }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="{{ $roleLabel }}" disabled>
                            </div>
                        </div>
                        <p class="readonly-note mt-2 mb-3">
                            <i class="fa fa-circle-info me-1"></i>
                            Jabatan dan role hanya dapat diubah oleh Tata Usaha melalui Manajemen User.
                        </p>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                {{-- ---------- KEAMANAN ---------- --}}
                <div class="tab-pane fade" id="tab-keamanan">
                    <form id="form-password" style="max-width: 460px;">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <div class="pw-wrap">
                                <input type="password" name="password_lama" class="form-control" autocomplete="current-password">
                                <button type="button" class="pw-toggle" tabindex="-1"><i class="fa fa-eye"></i></button>
                            </div>
                            <small class="text-danger error-msg" id="password-error-password_lama"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <div class="pw-wrap">
                                <input type="password" name="password" id="pw-baru" class="form-control" autocomplete="new-password">
                                <button type="button" class="pw-toggle" tabindex="-1"><i class="fa fa-eye"></i></button>
                            </div>
                            <div class="pw-meter mt-2"><span id="pw-bar"></span></div>
                            <small class="readonly-note" id="pw-teks">Minimal 6 karakter.</small><br>
                            <small class="text-danger error-msg" id="password-error-password"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <div class="pw-wrap">
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                                <button type="button" class="pw-toggle" tabindex="-1"><i class="fa fa-eye"></i></button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-key me-1"></i> Ubah Password
                        </button>
                    </form>
                </div>

                {{-- ---------- AKTIVITAS ---------- --}}
                <div class="tab-pane fade" id="tab-aktivitas">
                    @if(count($statistik))
                        <div class="row g-3">
                            @foreach($statistik as $s)
                                <div class="col-sm-6 col-xl-4">
                                    <div class="stat-card border h-100">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="icon-box"><i class="fa {{ $s['ikon'] }}"></i></div>
                                            <div>
                                                <div class="nilai">{{ number_format($s['nilai']) }}</div>
                                                <div class="text-muted" style="font-size:12px;">{{ $s['label'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Belum ada aktivitas untuk ditampilkan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function kirimForm($form, prefix, onSuccess) {
    $form.find('.error-msg').text('');
    $.ajax({
        url: $form.data('url'),
        method: 'POST',
        data: new FormData($form[0]),
        contentType: false,
        processData: false,
        success: function (res) {
            alert(res.message);
            if (onSuccess) onSuccess(res);
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function (field, msgs) {
                    $('#' + prefix + '-error-' + field).text(msgs[0]);
                });
            } else {
                alert('Gagal menyimpan, coba lagi.');
            }
        }
    });
}

$('#form-profil').data('url', "{{ route('profil.update') }}").on('submit', function (e) {
    e.preventDefault();
    kirimForm($(this), 'profil', function (res) {
        $('#profil-nama').text(res.data.name);
        $('#profil-email').text(res.data.email);
        $('.sidebar .p-name').text(res.data.name);
        $('.sidebar .avatar-circle').text(res.data.name.substring(0, 2).toUpperCase());
        $('.profil-avatar').text(res.data.name.substring(0, 2).toUpperCase());
    });
});

$('#form-password').data('url', "{{ route('profil.password') }}").on('submit', function (e) {
    e.preventDefault();
    kirimForm($(this), 'password', function () {
        $('#form-password')[0].reset();
        $('#pw-bar').css('width', 0);
        $('#pw-teks').text('Minimal 6 karakter.');
    });
});

$('.pw-toggle').on('click', function () {
    var $in = $(this).siblings('input');
    var tampil = $in.attr('type') === 'password';
    $in.attr('type', tampil ? 'text' : 'password');
    $(this).find('i').toggleClass('fa-eye', !tampil).toggleClass('fa-eye-slash', tampil);
});

$('#pw-baru').on('input', function () {
    var v = $(this).val(), skor = 0;
    if (v.length >= 6) skor++;
    if (v.length >= 10) skor++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) skor++;
    if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) skor++;
    var level = [
        [0, '#EEF0F6', 'Minimal 6 karakter.'],
        [25, '#dc3545', 'Lemah'],
        [50, '#fd7e14', 'Cukup'],
        [75, '#ffc107', 'Baik'],
        [100, '#198754', 'Kuat']
    ][v.length ? Math.max(skor, 1) : 0];
    $('#pw-bar').css({ width: level[0] + '%', background: level[1] });
    $('#pw-teks').text(level[2]);
});
</script>
@endpush
