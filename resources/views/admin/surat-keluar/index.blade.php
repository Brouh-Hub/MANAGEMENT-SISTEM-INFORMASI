    @extends('layouts.main')
    @section('content')

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('surat-masuk.index') }}" class="btn {{ request()->routeIs('surat-masuk.*') ? 'btn-primary' : 'btn-light' }} btn-sm">Surat Masuk</a>
        <a href="{{ route('surat-keluar.index') }}" class="btn {{ request()->routeIs('surat-keluar.*') ? 'btn-primary' : 'btn-light' }} btn-sm">Surat Keluar</a>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $title }}</h4>
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-fields">
                <i class="fa fa-gear"></i> Atur Field
            </button>

            <button type="button"
                    class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-create">
                <i class="fa fa-plus"></i> Tambah Surat Keluar
            </button>
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
        const fieldsUrl = "{{ url('admin/surat-keluar-fields') }}";
        const csrf      = $('meta[name="csrf-token"]').attr('content');

        const STATUS = { draft: 'Draft', menunggu_approve: 'Menunggu Persetujuan', terbit: 'Disetujui' };

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
// AJUKAN SURAT KEPADA KEPALA SEKOLAH
// =========================================================

$('#table tbody').on('click', '.btn-ajukan', function () {

    const id = $(this).data('id');

    if (!confirm('Ajukan surat ini kepada Kepala Sekolah?')) {
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

                        alert(
                            xhr.responseJSON?.message ??
                            'Surat tersimpan, tetapi gagal membuat PDF.'
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

setInterval(function () {
    if (!document.hidden) {
        table.ajax.reload(null, false);
    }
}, 5000);


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

            $('#modal-update').modal('show');
        });

        $('#table tbody').on('click', '.btn-detail', function () {
            const detail = $(this).data('detail');

            for (const key in detail) {
                if (detail.hasOwnProperty(key)) {
                    $(`#detail-${key}`).text(detail[key] ?? '-');
                }
            }
            $('#detail-status').text(STATUS[detail.status] ?? detail.status ?? '-');

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
        $('#table tbody').on('click', '.btn-approve', function () {
            const id = $(this).data('id');

            if (!confirm('Setujui surat ini untuk diterbitkan?')) return;

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
        $('#table tbody').on('click', '.btn-delete', function () {
            const id = $(this).data('id');

            if (!confirm('Yakin mau hapus surat ini?')) return;

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

        // ================= ATUR FIELD =================

        // Tombol "Atur Field" di dalam modal Tambah/Ubah: tutup modal itu, lalu buka modal Atur Field
        $(document).on('click', '.btn-open-fields', function () {
            const current = $(this).closest('.modal');
            current.one('hidden.bs.modal', function () {
                $('#modal-fields').modal('show');
            });
            current.modal('hide');
        });

        function fieldAjax(url, method, data) {
            return $.ajax({
                url: url,
                method: 'POST',
                data: Object.assign({ _method: method, _token: csrf }, data || {})
            });
        }

        function fieldFail(xhr) {
            let msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors)[0][0];
            }
            alert(msg);
        }

        // Tambah field baru
        $('#btn-field-add').on('click', function () {
            const label = $('#new-field-label').val().trim();
            if (!label) { alert('Label wajib diisi.'); return; }

            fieldAjax(fieldsUrl, 'POST', { label: label, type: $('#new-field-type').val() })
                .done(function () { location.reload(); })
                .fail(fieldFail);
        });

        // Simpan perubahan label / aktif
        $(document).on('click', '.btn-field-save', function () {
            const tr = $(this).closest('tr');
            const active = tr.find('.f-active');
            const data = { label: tr.find('.f-label').val() };
            if (active.length) data.is_active = active.is(':checked') ? 1 : 0;

            fieldAjax(`${fieldsUrl}/${tr.data('id')}`, 'PUT', data)
                .done(function () { location.reload(); })
                .fail(fieldFail);
        });

        // Hapus field custom
        $(document).on('click', '.btn-field-delete', function () {
            if (!confirm('Hapus field ini? Data lama yang sudah tersimpan tidak akan tampil lagi.')) return;
            const tr = $(this).closest('tr');

            fieldAjax(`${fieldsUrl}/${tr.data('id')}`, 'DELETE')
                .done(function () { location.reload(); })
                .fail(fieldFail);
        });
    });
    </script>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);

        if (params.get('create') === '1') {
            const tombolTambah = document.querySelector('[data-bs-target="#modal-create"]');

            if (tombolTambah) {
                tombolTambah.click();
            }
        }
    });
    </script>
    @endpush
