@extends('layouts.main')

@section('content')

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('surat-masuk.index') }}"
       class="btn {{ request()->routeIs('surat-masuk.*') ? 'btn-primary' : 'btn-light' }} btn-sm">
        Surat Masuk
    </a>

    <a href="{{ route('surat-keluar.index') }}"
       class="btn {{ request()->routeIs('surat-keluar.*') ? 'btn-primary' : 'btn-light' }} btn-sm">
        Surat Keluar
    </a>

    <a href="{{ route('template-surat.index') }}"
       class="btn {{ request()->routeIs('template-surat.*') ? 'btn-primary' : 'btn-light' }} btn-sm">
        Template Surat
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $title }}</h4>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
    </div>

    <button type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modal-create">
        <i class="fa fa-plus"></i> Tambah Template
    </button>
</div>

<div class="card-panel">
    <div class="d-flex mb-3">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text bg-white border-end-0">
                <i class="fa fa-search text-muted"></i>
            </span>

            <input type="text"
                   id="search-box"
                   class="form-control border-start-0"
                   placeholder="Cari template...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="table" width="100%">
            <thead>
                <tr class="text-muted text-uppercase" style="font-size:11px;">
                    <th>No</th>
                    <th>Nama Template</th>
                    <th>Jenis Surat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


{{-- ================= MODAL TAMBAH ================= --}}

<div class="modal fade" id="modal-create" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form id="form-create">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tambah Template Surat</h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nama Template
                            </label>

                            <input type="text"
                                   name="nama_template"
                                   class="form-control"
                                   placeholder="Contoh: SK Tugas Tambahan Guru">

                            <small class="text-danger error-msg"
                                   id="create-error-nama_template">
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Jenis Surat
                            </label>

                            <input type="text"
                                   name="jenis_surat"
                                   class="form-control"
                                   placeholder="Contoh: Surat Keputusan">

                            <small class="text-danger error-msg"
                                   id="create-error-jenis_surat">
                            </small>
                        </div>

                    </div>

                    <div class="mb-2">
                        <label class="form-label">
                            Placeholder Cepat
                        </label>

                        <div class="d-flex flex-wrap gap-2 placeholder-buttons"
                             data-target="#create-isi_template">

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="nomor_surat">
                                Nomor Surat
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="tanggal_surat">
                                Tanggal Surat
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="perihal">
                                Perihal
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="tujuan">
                                Tujuan
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="alamat_tujuan">
                                Alamat Tujuan
                            </button>

                        </div>

                        <small class="text-muted">
                            Tombol di atas akan memasukkan placeholder seperti
                            @{{ nomor_surat }} ke isi template.
                        </small>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">
                            Isi Template
                        </label>

                        <textarea
                            id="create-isi_template"
                            name="isi_template"
                            class="form-control font-monospace"
                            rows="18"
                            placeholder="Tulis isi surat di sini..."></textarea>

                        <small class="text-danger error-msg"
                               id="create-error-isi_template">
                        </small>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="is_active"
                               value="1"
                               id="create-is-active"
                               checked>

                        <label class="form-check-label"
                               for="create-is-active">
                            Template aktif
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit"
                            class="btn btn-primary">
                        Simpan Template
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- ================= MODAL UBAH ================= --}}

<div class="modal fade" id="modal-update" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form id="form-update">
                @csrf

                <input type="hidden"
                       id="update-id">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Ubah Template Surat
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nama Template
                            </label>

                            <input type="text"
                                   id="update-nama_template"
                                   name="nama_template"
                                   class="form-control">

                            <small class="text-danger error-msg"
                                   id="update-error-nama_template">
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Jenis Surat
                            </label>

                            <input type="text"
                                   id="update-jenis_surat"
                                   name="jenis_surat"
                                   class="form-control">

                            <small class="text-danger error-msg"
                                   id="update-error-jenis_surat">
                            </small>
                        </div>

                    </div>

                    <div class="mb-2">
                        <label class="form-label">
                            Placeholder Cepat
                        </label>

                        <div class="d-flex flex-wrap gap-2 placeholder-buttons"
                             data-target="#update-isi_template">

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="nomor_surat">
                                Nomor Surat
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="tanggal_surat">
                                Tanggal Surat
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="perihal">
                                Perihal
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="tujuan">
                                Tujuan
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-variable="alamat_tujuan">
                                Alamat Tujuan
                            </button>

                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">
                            Isi Template
                        </label>

                        <textarea
                            id="update-isi_template"
                            name="isi_template"
                            class="form-control font-monospace"
                            rows="18"></textarea>

                        <small class="text-danger error-msg"
                               id="update-error-isi_template">
                        </small>
                    </div>

                    <div class="form-check form-switch">

                        <input class="form-check-input"
                               type="checkbox"
                               name="is_active"
                               value="1"
                               id="update-is_active">

                        <label class="form-check-label"
                               for="update-is_active">
                            Template aktif
                        </label>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit"
                            class="btn btn-primary">
                        Update Template
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- ================= MODAL DETAIL ================= --}}

<div class="modal fade" id="modal-detail" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Detail Template Surat
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <table class="table table-borderless"
                       style="font-size:14px;">

                    <tr>
                        <td width="170" class="text-muted">
                            Nama Template
                        </td>

                        <td id="detail-nama_template"></td>
                    </tr>

                    <tr>
                        <td class="text-muted">
                            Jenis Surat
                        </td>

                        <td id="detail-jenis_surat"></td>
                    </tr>

                    <tr>
                        <td class="text-muted">
                            Status
                        </td>

                        <td id="detail-status"></td>
                    </tr>

                    <tr>
                        <td class="text-muted">
                            Variabel
                        </td>

                        <td id="detail-variabel"></td>
                    </tr>

                </table>

                <hr>

                <label class="form-label fw-bold">
                    Isi Template
                </label>

                <pre id="detail-isi_template"
                     class="border rounded p-3 bg-light"
                     style="white-space:pre-wrap; min-height:300px;"></pre>

            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')

<script>

$(document).ready(function () {

    "use strict";

    let formMode = 'create';

    const baseUrl =
        "{{ route('template-surat.index') }}";

    const table = $('#table').DataTable({

        serverSide: true,
        processing: true,

        dom: 'rtip',

        ajax: {
            url: baseUrl,
            method: 'GET'
        },

        columns: [

            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },

            {
                data: 'nama_template',
                name: 'nama_template'
            },

            {
                data: 'jenis_surat',
                name: 'jenis_surat',
                render: function (data) {
                    return data || '-';
                }
            },

            {
                data: 'status_badge',
                name: 'status_badge',
                orderable: false,
                searchable: false
            },

            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }

        ]

    });


    $('#search-box').on('keyup', function () {

        table
            .search(this.value)
            .draw();

    });


    $('[data-bs-target="#modal-create"]').on(
        'click',
        function () {

            formMode = 'create';

            $('#form-create')
                .trigger('reset');

            $('#create-is-active')
                .prop('checked', true);

            $('.error-msg')
                .text('');

        }
    );


    $('.placeholder-buttons button').on(
        'click',
        function () {

            const variable =
                $(this).data('variable');

            const target =
                $(this)
                    .closest('.placeholder-buttons')
                    .data('target');

            const textarea =
                document.querySelector(target);

            if (!textarea) {
                return;
            }

            const placeholder =
                '{{' + variable + '}}';

            const start =
                textarea.selectionStart;

            const end =
                textarea.selectionEnd;

            const current =
                textarea.value;

            textarea.value =
                current.substring(0, start) +
                placeholder +
                current.substring(end);

            textarea.focus();

            textarea.selectionStart =
                textarea.selectionEnd =
                start + placeholder.length;

        }
    );


    $('#form-create').on(
        'submit',
        function (e) {

            e.preventDefault();

            formMode = 'create';

            const formData =
                new FormData(this);

            if (!$('#create-is-active').is(':checked')) {
                formData.set('is_active', '0');
            }

            formData.append(
                '_method',
                'post'
            );

            callApi(formData);

        }
    );


    $('#form-update').on(
        'submit',
        function (e) {

            e.preventDefault();

            formMode = 'update';

            const id =
                $('#update-id').val();

            const formData =
                new FormData(this);

            if (!$('#update-is_active').is(':checked')) {
                formData.set('is_active', '0');
            }

            formData.append(
                '_method',
                'put'
            );

            callApi(
                formData,
                `${baseUrl}/${id}`
            );

        }
    );


    $('#table tbody').on(
        'click',
        '.btn-update',
        function () {

            const detail =
                $(this).data('detail');

            formMode = 'update';

            $('.error-msg')
                .text('');

            $('#update-id')
                .val(detail.id);

            $('#update-nama_template')
                .val(detail.nama_template);

            $('#update-jenis_surat')
                .val(detail.jenis_surat);

            $('#update-isi_template')
                .val(detail.isi_template);

            $('#update-is_active')
                .prop(
                    'checked',
                    Boolean(detail.is_active)
                );

            $('#modal-update')
                .modal('show');

        }
    );


    $('#table tbody').on(
        'click',
        '.btn-detail',
        function () {

            const detail =
                $(this).data('detail');

            $('#detail-nama_template')
                .text(
                    detail.nama_template || '-'
                );

            $('#detail-jenis_surat')
                .text(
                    detail.jenis_surat || '-'
                );

            $('#detail-status')
                .text(
                    detail.is_active
                        ? 'Aktif'
                        : 'Nonaktif'
                );

            const variables =
                Array.isArray(detail.variabel)
                    ? detail.variabel
                    : [];

            $('#detail-variabel')
                .text(
                    variables.length
                        ? variables
                            .map(v => '{{' + v + '}}')
                            .join(', ')
                        : '-'
                );

            $('#detail-isi_template')
                .text(
                    detail.isi_template || ''
                );

            $('#modal-detail')
                .modal('show');

        }
    );


    $('#table tbody').on(
        'click',
        '.btn-delete',
        async function () {

            const id =
                $(this).data('id');

            if (!await konfirmasi(
                'Yakin mau hapus template surat ini?'
            )) {
                return;
            }

            formMode = 'delete';

            const formData =
                new FormData();

            formData.append(
                '_method',
                'delete'
            );

            callApi(
                formData,
                `${baseUrl}/${id}`
            );

        }
    );


    function callApi(
        formData,
        url = null
    ) {

        $.ajax({

            url: url || baseUrl,

            method: 'POST',

            data: formData,

            dataType: 'JSON',

            cache: false,

            contentType: false,

            processData: false,

            beforeSend: function () {

                $('.error-msg')
                    .text('');

            },

            success: function (response) {

                if (
                    formMode === 'create' ||
                    formMode === 'update'
                ) {

                    $(`#form-${formMode}`)
                        .trigger('reset');

                    $(`#modal-${formMode}`)
                        .modal('hide');

                }

                table.ajax.reload(
                    null,
                    false
                );

                alert(
                    response.message
                );

            },

            error: function (response) {

                if (
                    response.status === 422
                ) {

                    const errors =
                        response.responseJSON.errors;

                    $.each(
                        errors,
                        function (
                            field,
                            messages
                        ) {

                            $(
                                `#${formMode}-error-${field.replace(/\./g, '-')}`
                            ).text(
                                messages[0]
                            );

                        }
                    );

                    return;

                }

                alert(
                    response.responseJSON?.message ??
                    'Terjadi kesalahan.'
                );

            }

        });

    }

});

</script>

@endpush
