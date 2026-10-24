@extends('layouts.main')
@section('content')

<style>
    .kertas-wrap { background:#E9ECF3; border-radius:12px; padding:18px; overflow:auto; }
    .kertas {
        background:#fff; color:#000; margin:0 auto; max-width:760px; min-width:620px;
        padding:28px 34px 40px; box-shadow:0 4px 18px rgba(0,0,0,.08);
        font-family: Arial, Helvetica, sans-serif; font-size:11pt; line-height:1.25;
    }
    .logo-thumb { height:56px; width:auto; border:1px solid #E2E5EE; border-radius:8px; padding:4px; background:#fff; }
    .form-section-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#8a8f98; margin:18px 0 8px; }
    .sticky-preview { position:sticky; top:16px; }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h3 class="fw-bold mb-1">{{ $title }}</h3>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
    </div>
    <a href="{{ route('pengaturan-surat.contoh-pdf') }}" target="_blank" class="btn btn-light btn-sm">
        <i class="fa fa-file-pdf"></i> Lihat Contoh PDF
    </a>
</div>

<div class="row g-3">

    {{-- ===================== FORM ===================== --}}
    <div class="col-12 col-xl-5">
        <form id="form-pengaturan" class="card-panel" enctype="multipart/form-data">

            <div class="form-section-title mt-0">Kop Surat</div>

            @foreach([
                'kop_baris_1' => 'Baris 1 (instansi induk)',
                'kop_baris_2' => 'Baris 2 (dinas)',
                'kop_baris_3' => 'Baris 3 (nama sekolah) *',
                'kop_alamat'  => 'Alamat',
                'kop_telepon' => 'Telepon',
                'kop_laman'   => 'Laman (website)',
                'kop_email'   => 'Pos-el (email)',
            ] as $name => $label)
                <div class="mb-2">
                    <label class="form-label small mb-1">{{ $label }}</label>
                    <input type="text" name="{{ $name }}" value="{{ $pengaturan->$name }}"
                           class="form-control form-control-sm input-kop" data-target="{{ $name }}">
                    <small class="text-danger error-msg" id="error-{{ $name }}"></small>
                </div>
            @endforeach

            <div class="form-section-title">Logo</div>

            <div class="row g-2">
                @foreach(['kiri' => 'Logo kiri', 'kanan' => 'Logo kanan'] as $sisi => $label)
                    @php $url = $sisi === 'kiri' ? $pengaturan->logoKiriUrl() : $pengaturan->logoKananUrl(); @endphp
                    <div class="col-6">
                        <label class="form-label small mb-1">{{ $label }}</label>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <img src="{{ $url }}" class="logo-thumb" id="thumb-{{ $sisi }}" alt="{{ $label }}">
                        </div>
                        <input type="file" name="logo_{{ $sisi }}" accept=".png,.jpg,.jpeg"
                               class="form-control form-control-sm input-logo" data-sisi="{{ $sisi }}">
                        <div class="form-check mt-1">
                            <input class="form-check-input cek-hapus-logo" type="checkbox" value="1"
                                   name="hapus_logo_{{ $sisi }}" id="hapus-{{ $sisi }}" data-sisi="{{ $sisi }}">
                            <label class="form-check-label small" for="hapus-{{ $sisi }}">Pakai logo bawaan</label>
                        </div>
                        <small class="text-danger error-msg" id="error-logo_{{ $sisi }}"></small>
                    </div>
                @endforeach
            </div>

            <div class="mt-2">
                <label class="form-label small mb-1">
                    Tinggi logo: <span id="label-tinggi">{{ $pengaturan->tinggi_logo }}</span> px
                </label>
                <input type="range" name="tinggi_logo" id="tinggi_logo" class="form-range"
                       min="40" max="130" step="1" value="{{ $pengaturan->tinggi_logo }}">
            </div>

            <div class="form-section-title">Data Sekolah</div>

            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small mb-1">Kota (tempat surat)</label>
                    <input type="text" name="kota" value="{{ $pengaturan->kota }}" class="form-control form-control-sm input-ttd">
                    <small class="text-danger error-msg" id="error-kota"></small>
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Nama sekolah (di isi surat)</label>
                    <input type="text" name="nama_sekolah" value="{{ $pengaturan->nama_sekolah }}" class="form-control form-control-sm">
                    <small class="text-danger error-msg" id="error-nama_sekolah"></small>
                </div>
            </div>

            <div class="form-section-title">Kepala Sekolah (penanda tangan)</div>

            <div class="mb-2">
                <label class="form-label small mb-1">Nama lengkap & gelar *</label>
                <input type="text" name="kepsek_nama" value="{{ $pengaturan->kepsek_nama }}" class="form-control form-control-sm input-ttd">
                <small class="text-danger error-msg" id="error-kepsek_nama"></small>
            </div>
            <div class="row g-2">
                <div class="col-7">
                    <label class="form-label small mb-1">Pangkat</label>
                    <input type="text" name="kepsek_pangkat" value="{{ $pengaturan->kepsek_pangkat }}" class="form-control form-control-sm input-ttd">
                </div>
                <div class="col-5">
                    <label class="form-label small mb-1">Golongan</label>
                    <input type="text" name="kepsek_golongan" value="{{ $pengaturan->kepsek_golongan }}" class="form-control form-control-sm input-ttd">
                </div>
            </div>
            <div class="mt-2">
                <label class="form-label small mb-1">NIP</label>
                <input type="text" name="kepsek_nip" value="{{ $pengaturan->kepsek_nip }}" class="form-control form-control-sm input-ttd">
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3" id="btn-simpan">
                <i class="fa fa-floppy-disk"></i> Simpan Pengaturan
            </button>
            <small class="text-muted d-block mt-2">
                Perubahan berlaku untuk PDF surat yang dibuat setelah disimpan.
                Surat lama tetap memakai kop lama sampai PDF-nya dibuat ulang.
            </small>
        </form>
    </div>

    {{-- ===================== PREVIEW ===================== --}}
    <div class="col-12 col-xl-7">
        <div class="sticky-preview">
            <div class="small text-muted mb-2"><i class="fa fa-eye"></i> Preview (berubah langsung saat diketik)</div>
            <div class="kertas-wrap">
                <div class="kertas" id="preview-kertas">
                    <div id="preview-kop">{!! $pengaturan->kopHtml() !!}</div>

                    <div style="text-align:right; margin:6px 0 18px;">
                        <span data-ttd="kota">{{ $pengaturan->kota }}</span>, {{ now()->locale('id')->translatedFormat('j F Y') }}
                    </div>
                    <div style="color:#9aa0aa; margin-bottom:28px;">
                        Nomor : …<br>Hal &nbsp;&nbsp;&nbsp;&nbsp;: …<br><br>
                        (isi surat)
                    </div>

                    <table style="width:100%;">
                        <tr>
                            <td style="width:52%;"></td>
                            <td>
                                Kepala Sekolah,
                                <div style="height:52px;"></div>
                                <span data-ttd="kepsek_nama">{{ $pengaturan->kepsek_nama }}</span><br>
                                <span data-ttd="kepsek_pangkat">{{ $pengaturan->kepsek_pangkat }}</span><br>
                                NIP <span data-ttd="kepsek_nip">{{ $pengaturan->kepsek_nip }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== PENOMORAN OTOMATIS ===================== --}}
<form id="form-penomoran" class="card-panel mt-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
            <div class="form-section-title mt-0 mb-1">Penomoran Surat Otomatis</div>
            <p class="text-muted small mb-0">
                Nomor dibuat sistem saat Kepala Sekolah menandatangani surat (kalau TU mengosongkan kolom nomor).
                Kode format:
                <code>{nomor}</code> nomor urut,
                <code>{kode}</code> kode klasifikasi,
                <code>{tahun}</code>, <code>{bulan}</code>, <code>{romawi}</code> (bulan romawi).
            </p>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" id="btn-simpan-penomoran">
            <i class="fa fa-save"></i> Simpan Penomoran
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0" id="tabel-penomoran">
            <thead class="table-light small">
                <tr>
                    <th style="min-width:170px;">Jenis surat</th>
                    <th style="width:110px;">Kode</th>
                    <th style="min-width:280px;">Format</th>
                    <th style="width:80px;">Digit</th>
                    <th style="min-width:140px;">Reset</th>
                    <th style="width:110px;">Nomor terakhir</th>
                    <th style="min-width:190px;">Nomor berikutnya</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penomoran as $p)
                    <tr data-periode="{{ $p->periode }}" data-reset-awal="{{ $p->reset }}">
                        <td class="small fw-semibold">
                            {{ $p->templateSurat->nama_template ?? 'Umum (tanpa template)' }}
                        </td>
                        <td><input type="text" name="penomoran[{{ $p->id }}][kode]" value="{{ $p->kode }}" class="form-control form-control-sm inp-nomor"></td>
                        <td>
                            <input type="text" name="penomoran[{{ $p->id }}][format]" value="{{ $p->format }}" class="form-control form-control-sm inp-nomor font-monospace">
                            <small class="text-danger error-msg" id="error-penomoran.{{ $p->id }}.format"></small>
                        </td>
                        <td><input type="number" min="1" max="6" name="penomoran[{{ $p->id }}][panjang]" value="{{ $p->panjang }}" class="form-control form-control-sm inp-nomor"></td>
                        <td>
                            <select name="penomoran[{{ $p->id }}][reset]" class="form-select form-select-sm inp-nomor">
                                <option value="tahunan" @selected($p->reset === 'tahunan')>Tiap tahun</option>
                                <option value="bulanan" @selected($p->reset === 'bulanan')>Tiap bulan</option>
                                <option value="tidak" @selected($p->reset === 'tidak')>Tidak pernah</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" min="0" name="penomoran[{{ $p->id }}][nomor_terakhir]"
                                   value="{{ $p->urutBerikutnya() - 1 }}" class="form-control form-control-sm inp-nomor">
                        </td>
                        <td><span class="badge bg-light text-dark border font-monospace contoh-nomor">{{ $p->contoh() }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="small text-muted mt-2 mb-0">
        <i class="fa fa-circle-info"></i>
        Kalau sekolah sudah memakai nomor manual tahun ini, isi <b>Nomor terakhir</b> dengan nomor terakhir yang sudah dipakai
        supaya nomor otomatis melanjutkan. Nomor yang sudah dipakai surat lain otomatis dilewati.
    </p>
</form>

@endsection

@push('scripts')
<script>
$(function () {
    const $kop = $('#preview-kop');
    const logoBawaan = {
        kiri:  @json(\App\Models\PengaturanSurat::LOGO_KIRI_BAWAAN),
        kanan: @json(\App\Models\PengaturanSurat::LOGO_KANAN_BAWAAN),
    };
    const logoTersimpan = {
        kiri:  @json($pengaturan->logoKiriUrl()),
        kanan: @json($pengaturan->logoKananUrl()),
    };

    // Teks kop
    function tampilkan($el, ada) { $el.css('display', ada ? '' : 'none'); }

    $('.input-kop').on('input', function () {
        const key = $(this).data('target');
        const val = $(this).val().trim();
        const $target = $kop.find(`[data-kop="${key}"]`);
        $target.text(val);

        if (['kop_baris_1', 'kop_baris_2', 'kop_baris_3', 'kop_alamat'].includes(key)) {
            tampilkan($target, val !== '');
        }
        if (key === 'kop_telepon') {
            tampilkan($kop.find('[data-kop-baris="telepon"]'), val !== '');
        }
        if (key === 'kop_laman' || key === 'kop_email') {
            const laman = $('[name=kop_laman]').val().trim();
            const email = $('[name=kop_email]').val().trim();
            tampilkan($kop.find('[data-kop-bagian="laman"]'), laman !== '');
            tampilkan($kop.find('[data-kop-bagian="email"]'), email !== '');
            tampilkan($kop.find('[data-kop-baris="kontak"]'), laman !== '' || email !== '');
        }
    });

    // Tanda tangan & kota
    $('.input-ttd').on('input', function () {
        $(`[data-ttd="${this.name}"]`).text($(this).val());
    });

    // Logo baru dipilih -> langsung tampil di preview
    function setLogo(sisi, src) {
        $kop.find(`[data-kop-logo="${sisi}"]`).attr('src', src);
        $(`#thumb-${sisi}`).attr('src', src);
    }

    $('.input-logo').on('change', function () {
        const sisi = $(this).data('sisi');
        const file = this.files[0];
        if (!file) { setLogo(sisi, logoTersimpan[sisi]); return; }

        $(`#hapus-${sisi}`).prop('checked', false);
        const reader = new FileReader();
        reader.onload = e => setLogo(sisi, e.target.result);
        reader.readAsDataURL(file);
    });

    $('.cek-hapus-logo').on('change', function () {
        const sisi = $(this).data('sisi');
        if (this.checked) {
            $(`[name="logo_${sisi}"]`).val('');
            setLogo(sisi, logoBawaan[sisi]);
        } else {
            setLogo(sisi, logoTersimpan[sisi]);
        }
    });

    // Tinggi logo
    $('#tinggi_logo').on('input', function () {
        const t = parseInt(this.value, 10);
        $('#label-tinggi').text(t);
        $kop.find('[data-kop-logo]').css('height', t + 'px')
            .closest('td').css('width', (t + 17) + 'px');
    });

    // Simpan
    $('#form-pengaturan').on('submit', function (e) {
        e.preventDefault();
        $('.error-msg').text('');

        const $btn = $('#btn-simpan');
        const html = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        const fd = new FormData(this);
        fd.append('_method', 'PUT');

        $.ajax({
            url: '{{ route('pengaturan-surat.update') }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                alert(res.message);
                location.reload();
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors ?? {};
                $.each(errors, (field, msg) => $(`#error-${field}`).text(msg[0]));
                if (!Object.keys(errors).length) {
                    alert(xhr.responseJSON?.message ?? 'Gagal menyimpan pengaturan.');
                }
                $btn.prop('disabled', false).html(html);
            }
        });
    });
    // ===================== Penomoran =====================
    const romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    const kini = new Date();

    function contohNomor($tr) {
        const val = n => $tr.find(`[name$="[${n}]"]`).val() ?? '';
        const digit = Math.max(1, parseInt(val('panjang'), 10) || 1);
        const urut = (parseInt(val('nomor_terakhir'), 10) || 0) + 1;
        const bulan = kini.getMonth() + 1;

        return val('format')
            .replaceAll('{nomor}', String(urut).padStart(digit, '0'))
            .replaceAll('{kode}', val('kode'))
            .replaceAll('{tahun}', kini.getFullYear())
            .replaceAll('{bulan}', String(bulan).padStart(2, '0'))
            .replaceAll('{romawi}', romawi[bulan]);
    }

    $('#tabel-penomoran').on('input change', '.inp-nomor', function () {
        const $tr = $(this).closest('tr');
        $tr.find('.contoh-nomor').text(contohNomor($tr));
    });

    $('#form-penomoran').on('submit', function (e) {
        e.preventDefault();
        $(this).find('.error-msg').text('');

        const $btn = $('#btn-simpan-penomoran');
        const html = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: '{{ route('pengaturan-surat.penomoran') }}',
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                alert(res.message);
                $btn.prop('disabled', false).html(html);
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors ?? {};
                $.each(errors, (field, msg) => $(document.getElementById(`error-${field}`)).text(msg[0]));
                alert(xhr.responseJSON?.message ?? 'Gagal menyimpan penomoran.');
                $btn.prop('disabled', false).html(html);
            }
        });
    });
});
</script>
@endpush
