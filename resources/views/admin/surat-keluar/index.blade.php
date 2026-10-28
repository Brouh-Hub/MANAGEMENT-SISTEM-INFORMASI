    @extends('layouts.main')
    @section('content')

    @php
        // Tata Usaha & Kepala Tata Usaha sama-sama boleh kelola surat keluar
        $bisaKelolaSurat = in_array(auth()->user()?->jabatan, ['Tata Usaha', 'Kepala Tata Usaha'], true);
    @endphp

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('surat-masuk.index') }}" class="btn {{ request()->routeIs('surat-masuk.*') ? 'btn-primary' : 'btn-light' }} btn-sm">Surat Masuk</a>
        <a href="{{ route('surat-keluar.index') }}" class="btn {{ request()->routeIs('surat-keluar.*') ? 'btn-primary' : 'btn-light' }} btn-sm">Surat Keluar</a>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $title }}</h4>
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($bisaKelolaSurat)
                <button type="button"
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-create">
                    <i class="fa fa-plus"></i> Tambah Surat Keluar
                </button>
            @endif
        </div>
    </div>

    <div class="card-panel">
        <div class="d-flex mb-3">
            <div class="input-group" style="max-width: 320px;">
                <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="search-box" class="form-control border-start-0" placeholder="Cari nomor surat, tujuan, atau perihal...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="table" width="100%">
                <thead>
                    <tr class="text-muted text-uppercase" style="font-size:11px;">
                        <th>No</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Tujuan</th>
                        <th>Perihal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('admin.surat-keluar.modal')

    @endsection

    @push('scripts')
    <script>
    $(document).ready(function () {
        "use strict";

        let formMode = 'create';
        const baseUrl   = "{{ route('surat-keluar.index') }}";
        const csrf      = $('meta[name="csrf-token"]').attr('content');

        const STATUS = @json(\App\Models\SuratKeluar::STATUS);

        const table = $('#table').DataTable({
            serverSide: true,
            processing: true,
            dom: 'rtip', // sembunyikan search bawaan, pakai #search-box di kanan atas
            ajax: { url: baseUrl, method: 'GET' },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nomor_surat', name: 'nomor_surat' },
                { data: 'tanggal_surat', name: 'tanggal_surat' },
                { data: 'tujuan', name: 'tujuan' },
                { data: 'perihal', name: 'perihal' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });

        $('#search-box').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('[data-bs-target="#modal-create"]').on('click', function () {
            formMode = 'create';
            $('#form-create').trigger('reset');
            $('.error-msg').text('');

            $('#create-template-fields').empty();
            $('#create-template-fields-wrapper').addClass('d-none');
        });


        // ================= TEMPLATE SURAT =================

        $('#create-template-surat').on('change', function () {

            const option = $(this).find('option:selected');
            const rawFields = option.attr('data-fields');

            const container = $('#create-template-fields');
            const wrapper   = $('#create-template-fields-wrapper');

            container.empty();

            if (!$(this).val()) {
                wrapper.addClass('d-none');
                return;
            }

            let fields = [];

            try {
                fields = rawFields ? JSON.parse(rawFields) : [];
            } catch (e) {
                fields = [];
            }

            if (!fields.length) {
                wrapper.addClass('d-none');
                return;
            }

            fields.forEach(function (field) {

                const requiredMark = field.is_required
                    ? ' <span class="text-danger">*</span>'
                    : '';

                const requiredAttr = field.is_required
                    ? 'required'
                    : '';

                const value = field.default_value ?? '';

                let input = '';

                if (field.type === 'textarea') {

                    input = `
                        <textarea
                            name="template_data[${field.key}]"
                            class="form-control"
                            rows="3"
                            ${requiredAttr}>${escapeHtmlTemplate(value)}</textarea>
                    `;

                } else {

                    input = `
                        <input
                            type="${field.type}"
                            name="template_data[${field.key}]"
                            class="form-control"
                            value="${escapeHtmlTemplate(value)}"
                            ${requiredAttr}>
                    `;
                }

                container.append(`
                    <div class="mb-2">
                        <label class="form-label">
                            ${escapeHtmlTemplate(field.label)}
                            ${requiredMark}
                        </label>

                        ${input}

                        <small class="text-danger error-msg"
                            id="create-error-template_data-${field.key}">
                        </small>
                    </div>
                `);
            });

            wrapper.removeClass('d-none');
        });


        function escapeHtmlTemplate(value) {
            return $('<div>')
                .text(value ?? '')
                .html();
        }

        $('#form-create').on('submit', function (e) {
            e.preventDefault();
            formMode = 'create';

            const formData = new FormData(this);
            formData.append('_method', 'post');

            callApi(formData);
        });

        // =========================================================
// AJUKAN SURAT KEPADA KEPALA TATA USAHA
// =========================================================

$('#table tbody').on('click', '.btn-ajukan', async function () {

    const id = $(this).data('id');

    if (!await konfirmasi('Ajukan surat ini kepada Kepala Tata Usaha untuk diperiksa?')) {
        return;
    }

    const $button = $(this);

    $button.prop('disabled', true);

    $.ajax({
        url: `{{ url('admin/surat-keluar') }}/${id}/ajukan`,
        method: 'POST',
        data: {
            _token: csrf
        },

        success: function (response) {
            alert(response.message);
            table.ajax.reload(null, false);
        },

        error: function (xhr) {
            alert(
                xhr.responseJSON?.message ||
                'Gagal mengajukan surat.'
            );

            $button.prop('disabled', false);
        }
    });
});

        // ================= SIMPAN & UNDUH PDF =================

    $('#btn-save-download').on('click', function () {

        const form = document.getElementById('form-create');

        if (!form) {
            alert('Form Tambah Surat tidak ditemukan.');
            return;
        }

        // Jalankan validasi required HTML terlebih dahulu.
        if (!form.reportValidity()) {
            return;
        }

        // Pastikan isi A4 hasil edit terakhir ikut disimpan.
        $('#create-isi-surat').val(
            $('#create-paper-preview').html()
        );

        const button = $(this);
        const originalHtml = button.html();

        button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin me-1"></i> Membuat PDF...');

        $('.error-msg').text('');

        const formData = new FormData(form);
        formData.append('_method', 'post');

        // 1. Simpan surat terlebih dahulu.
        $.ajax({
            url: baseUrl,
            method: 'POST',
            data: formData,
            dataType: 'JSON',
            cache: false,
            contentType: false,
            processData: false,

            success: function (saveResponse) {

                const id = saveResponse.data?.id;

                if (!id) {
                    button
                        .prop('disabled', false)
                        .html(originalHtml);

                    alert('Surat tersimpan, tetapi ID surat tidak ditemukan.');
                    table.ajax.reload(null, false);
                    return;
                }

                // 2. Generate PDF dari isi_surat yang sudah dicustom.
                $.ajax({
                    url: `${baseUrl}/${id}/generate-pdf`,
                    method: 'POST',
                    data: {
                        _token: csrf
                    },

                    success: function (pdfResponse) {

                        table.ajax.reload(null, false);

                        $('#form-create').trigger('reset');
                        $('#modal-create').modal('hide');

                        if (pdfResponse.download_url) {

                            // 3. Langsung unduh PDF.
                            window.location.href =
                                pdfResponse.download_url;

                        } else {

                            alert(
                                'PDF berhasil dibuat, tetapi URL download tidak ditemukan.'
                            );
                        }
                    },

                    error: function (xhr) {

                        // Surat sudah tersimpan: tutup form supaya tidak disimpan dua kali
                        table.ajax.reload(null, false);
                        $('#form-create').trigger('reset');
                        $('#modal-create').modal('hide');

                        alert(
                            (xhr.responseJSON?.message ?? 'Gagal membuat PDF.') +
                            '\n\nSurat sudah tersimpan sebagai Draft. Buka detail surat lalu klik "Buat PDF sekarang".'
                        );
                    },

                    complete: function () {

                        button
                            .prop('disabled', false)
                            .html(originalHtml);
                    }
                });
            },

            error: function (response) {

                button
                    .prop('disabled', false)
                    .html(originalHtml);

                if (
                    response.status === 422 &&
                    response.responseJSON?.errors
                ) {

                    const errors =
                        response.responseJSON.errors;

                    $.each(
                        errors,
                        function (field, messages) {

                            const id =
                                '#create-error-' +
                                field.replace(/\./g, '-');

                            $(id).text(messages[0]);
                        }
                    );

                    return;
                }

                alert(
                    response.responseJSON?.message ??
                    'Gagal menyimpan surat.'
                );
            }
        });

    });

    // Refresh otomatis setiap 5 detik.
// Status berubah meskipun approval dilakukan dari halaman lain.

// setInterval(function () {
//     if (!document.hidden) {
//         table.ajax.reload(null, false);
//     }
// }, 5000);


    $('#form-update').on('submit', function (e) {
            e.preventDefault();
            formMode = 'update';

            const id = $('#update-id').val();
            const formData = new FormData(this);
            formData.append('_method', 'put');

            callApi(formData, `${baseUrl}/${id}`);
        });

        $('#table tbody').on('click', '.btn-update', function () {
            const detail = $(this).data('detail');
            formMode = 'update';
            $('.error-msg').text('');

            for (const key in detail) {
                if (!detail.hasOwnProperty(key)) continue;

                const input = $(`#update-${key}`);

                // input file tidak boleh diisi nilai lewat .val()
                if (input.length && !input.is(':file')) {
                    input.val(detail[key]);
                }
            }

            // isi field tambahan (custom)
            const extra = detail.data_tambahan || {};
            $('#form-update [data-custom-key]').each(function () {
                $(this).val(extra[$(this).attr('data-custom-key')] ?? '');
            });

            // isi surat dari template: input diambil dari daftar field template di form Tambah
            const boxTpl = $('#update-template-fields').empty();
            $('#update-template-fields-wrapper').addClass('d-none');

            if (detail.template_surat_id) {
                const raw = $(`#create-template-surat option[value="${detail.template_surat_id}"]`).attr('data-fields');
                let tplFields = [];
                try { tplFields = raw ? JSON.parse(raw) : []; } catch (e) { tplFields = []; }
                const tersimpan = extra._template || {};

                tplFields.forEach(function (field) {
                    const nilai = escapeHtmlTemplate(tersimpan[field.key] ?? field.default_value ?? '');
                    const req   = field.is_required ? 'required' : '';
                    const input = field.type === 'textarea'
                        ? `<textarea name="template_data[${field.key}]" class="form-control" rows="3" ${req}>${nilai}</textarea>`
                        : `<input type="${field.type}" name="template_data[${field.key}]" class="form-control" value="${nilai}" ${req}>`;

                    boxTpl.append(`
                        <div class="mb-2">
                            <label class="form-label">${escapeHtmlTemplate(field.label)}${field.is_required ? ' <span class="text-danger">*</span>' : ''}</label>
                            ${input}
                            <small class="text-danger error-msg" id="update-error-template_data-${field.key}"></small>
                        </div>`);
                });

                if (tplFields.length) $('#update-template-fields-wrapper').removeClass('d-none');
            }

            $('#modal-update').modal('show');
        });

        // ================= PREVIEW PDF DI MODAL DETAIL =================
        const bisaBuatPdf = @json($bisaKelolaSurat);

        function tampilkanPreviewSurat(detail) {
            const $box  = $('#detail-preview').empty();
            const $aksi = $('#detail-file-actions');

            if (!detail.file_path) {
                $aksi.attr('style', 'display:none !important;');
                $box.html(`
                    <div class="text-center text-muted px-3">
                        <i class="fa fa-file-circle-exclamation fa-2x mb-2"></i>
                        <div>PDF surat ini belum dibuat.</div>
                        ${bisaBuatPdf ? `<button type="button" class="btn btn-sm btn-primary mt-3" id="btn-buat-pdf-detail" data-id="${detail.id}">
                            <i class="fa fa-file-pdf"></i> Buat PDF sekarang
                        </button>` : ''}
                    </div>`);
                return;
            }

            const base = `{{ url('admin/surat-keluar') }}/${detail.id}`;
            const versi = encodeURIComponent(detail.updated_at ?? Date.now());

            $('#detail-file-open').attr('href', `${base}/preview?v=${versi}`);
            $('#detail-file-download').attr('href', `${base}/download`);
            $aksi.attr('style', '');

            $box.html($('<iframe>', {
                src: `${base}/preview?v=${versi}`,
                style: 'width:100%;height:100%;border:0;',
                title: 'Preview surat keluar'
            }));
        }

        // Buat PDF langsung dari modal detail (kalau belum ada)
        $(document).on('click', '#btn-buat-pdf-detail', function () {
            const $btn = $(this);
            const id = $btn.data('id');

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Membuat PDF...');

            $.post(`{{ url('admin/surat-keluar') }}/${id}/generate-pdf`)
                .done(function () {
                    tampilkanPreviewSurat({ id: id, file_path: 'ada', updated_at: Date.now() });
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    alert(xhr.responseJSON?.message ?? 'Gagal membuat PDF.');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-pdf"></i> Buat PDF sekarang');
                });
        });

        // Kosongkan preview saat modal ditutup
        $('#modal-detail').on('hidden.bs.modal', function () {
            $('#detail-preview').empty();
        });

        $('#table tbody').on('click', '.btn-detail', function () {
            const detail = $(this).data('detail');

            for (const key in detail) {
                if (detail.hasOwnProperty(key)) {
                    $(`#detail-${key}`).text(detail[key] ?? '-');
                }
            }
            $('#detail-status').text(STATUS[detail.status] ?? detail.status ?? '-');

            // Catatan Kepala Tata Usaha
            if (detail.status === 'dikembalikan' && detail.alasan_konfirmasi) {
                $('#detail-konfirmasi').html(`<span class="text-danger"><i class="fa fa-rotate-left"></i> Dikembalikan:</span> `)
                    .append(document.createTextNode(detail.alasan_konfirmasi));
            } else if (detail.dikonfirmasi_at) {
                const oleh = detail.pengonfirmasi?.name ? ` oleh ${detail.pengonfirmasi.name}` : '';
                $('#detail-konfirmasi').text(`Disetujui${oleh} (${new Date(detail.dikonfirmasi_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })})`);
            } else {
                $('#detail-konfirmasi').text(detail.status === 'menunggu_kepala_tu' ? 'Sedang diperiksa Kepala TU' : '-');
            }

            tampilkanPreviewSurat(detail);

            // tampilkan field tambahan (custom): label diambil dari form ubah
            $('#modal-detail tr.row-custom').remove();
            const extra = detail.data_tambahan || {};
            $('#form-update [data-custom-key]').each(function () {
                const key   = $(this).attr('data-custom-key');
                const label = $(this).closest('.mb-2').find('label').first().text();
                const value = extra[key];

                const row = $('<tr class="row-custom"><td class="text-muted"></td><td></td></tr>');
                row.find('td').eq(0).text(label);
                row.find('td').eq(1).text(value ? value : '-');
                $('#detail-status-row').before(row);
            });

            $('#modal-detail').modal('show');
        });

        // Tombol Approve
        $('#table tbody').on('click', '.btn-approve', async function () {
            const id = $(this).data('id');

            if (!await konfirmasi('Setujui surat ini untuk diterbitkan?')) return;

            $.ajax({
                url: `{{ url('admin/surat-keluar') }}/${id}/approve`,
                method: 'POST',
                data: { _token: csrf },
                success: function (response) {
                    table.ajax.reload(null, false);
                    alert(response.message);
                },
                error: function (response) {
                    alert(response.responseJSON?.message ?? 'Gagal approve surat.');
                }
            });
        });

        // Hapus
        $('#table tbody').on('click', '.btn-delete', async function () {
            const id = $(this).data('id');

            if (!await konfirmasi('Yakin mau hapus surat ini?')) return;

            formMode = 'delete';
            const formData = new FormData();
            formData.append('_method', 'delete');

            callApi(formData, `${baseUrl}/${id}`);
        });

        function callApi(formData, url = null) {
            $.ajax({
                url: url ? url : baseUrl,
                method: 'POST',
                data: formData,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('.error-msg').text('');
                },
                success: function (response) {
                    if (formMode === 'create' || formMode === 'update') {
                        $(`#form-${formMode}`).trigger('reset');
                        $(`#modal-${formMode}`).modal('hide');
                    }
                    table.ajax.reload(null, false);
                    alert(response.message);
                },
                error: function (response) {
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            // 'custom.lampiran' -> 'custom-lampiran' (id tidak boleh pakai titik)
                            $(`#${formMode}-error-${field.replace(/\./g, '-')}`).text(messages[0]);
                        });
                        return;
                    }
                    alert(response.responseJSON?.message ?? 'Terjadi kesalahan.');
                }
            });
        }
    });
    </script>
    @endpush
