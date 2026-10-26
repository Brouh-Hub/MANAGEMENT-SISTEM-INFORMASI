{{--
| Input nomor surat keluar yang HANYA bisa diisi lewat tombol "Generate".
| TU memasukkan password akun sendiri, lalu nomor dibuat sistem (unik).
|
| Pakai:
|   @include('admin.surat-keluar.partials.input-nomor', ['inputId' => 'create-nomor_surat'])
| Jenis surat diambil dari select [name=template_surat_id] di form yang sama
| (tanpa select itu = format Umum). Opsional: 'name' (nama input), 'url' (endpoint generate).
--}}
<div class="generate-nomor-wrap" data-url="{{ $url ?? route('surat-keluar.generate-nomor') }}">
    <div class="input-group">
        <input
            type="text"
            name="{{ $name ?? 'nomor_surat' }}"
            id="{{ $inputId }}"
            class="form-control bg-light input-nomor-generate"
            placeholder="Klik Generate"
            readonly
            required
        >
        <button type="button" class="btn btn-outline-primary btn-generate-nomor" title="Generate nomor surat">
            <i class="fa fa-wand-magic-sparkles"></i> Generate
        </button>
    </div>

    <div class="box-generate-nomor border rounded-3 p-2 mt-2 bg-white" style="display:none;">
        <label class="form-label small fw-semibold mb-1">
            <i class="fa fa-lock text-muted"></i> Password akun Anda
        </label>
        <div class="input-group input-group-sm">
            {{-- sengaja tanpa name: tidak ikut terkirim saat surat disimpan --}}
            <input type="password" class="form-control inp-pass-nomor" autocomplete="new-password" placeholder="Password login TU">
            <button type="button" class="btn btn-primary btn-kirim-nomor">Buat Nomor</button>
            <button type="button" class="btn btn-light btn-batal-nomor" title="Batal"><i class="fa fa-xmark"></i></button>
        </div>
        <small class="text-danger d-block err-pass-nomor"></small>
        <small class="text-muted d-block" style="font-size:11px;">Format nomor diatur di Pengaturan Surat &rsaquo; Penomoran.</small>
    </div>
</div>

@once
@push('scripts')
<script>
$(function () {

    function kotak(el) { return $(el).closest('.generate-nomor-wrap'); }

    $(document).on('click', '.btn-generate-nomor', function () {
        const $w = kotak(this);
        $w.find('.box-generate-nomor').slideDown(120);
        $w.find('.err-pass-nomor').text('');
        setTimeout(() => $w.find('.inp-pass-nomor').trigger('focus'), 130);
    });

    $(document).on('click', '.btn-batal-nomor', function () {
        const $w = kotak(this);
        $w.find('.inp-pass-nomor').val('');
        $w.find('.box-generate-nomor').slideUp(120);
    });

    // Enter di kolom password = Buat Nomor (bukan submit form surat)
    $(document).on('keydown', '.inp-pass-nomor', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            kotak(this).find('.btn-kirim-nomor').trigger('click');
        }
    });

    $(document).on('click', '.btn-kirim-nomor', function () {
        const $w = kotak(this);
        const $btn = $(this);
        const $form = $w.closest('form');
        const templateId = $form.find('[name=template_surat_id]').val() || '';

        $w.find('.err-pass-nomor').text('');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: $w.data('url'),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                template_surat_id: templateId,
                password: $w.find('.inp-pass-nomor').val()
            },
            success: function (res) {
                $w.find('.input-nomor-generate').val(res.data.nomor_surat).trigger('input').trigger('change');
                $w.find('.inp-pass-nomor').val('');
                $w.find('.box-generate-nomor').slideUp(120);
                $form.find('[id$="error-nomor_surat"]').text('');
            },
            error: function (xhr) {
                const r = xhr.responseJSON ?? {};
                $w.find('.err-pass-nomor').text(r.errors?.password?.[0] ?? r.message ?? 'Gagal membuat nomor.');
                $w.find('.inp-pass-nomor').val('').trigger('focus');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Buat Nomor');
            }
        });
    });

    // Ganti jenis surat -> nomor harus di-generate ulang (format tiap jenis berbeda)
    $(document).on('change', 'form [name=template_surat_id]', function () {
        const $input = $(this).closest('form').find('.input-nomor-generate');
        if ($input.val()) {
            $input.val('').trigger('input');
        }
    });
});
</script>
@endpush
@endonce
