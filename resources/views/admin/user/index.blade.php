@extends('layouts.main')
@section('content')

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $title }}</h4>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
        <i class="fa fa-plus"></i> Tambah User
    </button>
</div>

<div class="card-panel">
    <div class="table-responsive">
        <table class="table align-middle" id="table" width="100%">
            <thead>
                <tr class="text-muted text-uppercase" style="font-size:11px;">
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jabatan</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@include('admin.user.modal')

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    "use strict";

    let formMode = 'create';

    const table = $('#table').DataTable({
        serverSide: true,
        processing: true,
        ajax: { url: "{{ route('users.index') }}", method: 'GET' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'jabatan', name: 'jabatan' },
            { data: 'role_badge', name: 'role_badge', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ]
    });

    $('[data-bs-target="#modal-create"]').on('click', function () {
        formMode = 'create';
        $('#form-create').trigger('reset');
    });

    $('#form-create').on('submit', function (e) {
        e.preventDefault();
        formMode = 'create';

        const formData = new FormData(this);
        formData.append('_method', 'post');

        callApi(formData);
    });

    $('#form-update').on('submit', function (e) {
        e.preventDefault();
        formMode = 'update';

        let id = $('#update-id').val();
        const formData = new FormData(this);
        formData.append('_method', 'put');

        callApi(formData, `{{ route('users.index') }}/${id}`);
    });

    $('#table tbody').on('click', '.btn-update', function () {
        let detail = $(this).data('detail');
        formMode = 'update';

        for (const key in detail) {
            if (detail.hasOwnProperty(key) && key !== 'password') {
                $(`#update-${key}`).val(detail[key]);
            }
        }

        $('#modal-update').modal('show');
    });

    $('#table tbody').on('click', '.btn-delete', function () {
        let id = $(this).data('id');

        if (!confirm('Yakin mau hapus user ini?')) return;

        let formData = new FormData();
        formData.append('_method', 'delete');

        callApi(formData, `{{ route('users.index') }}/${id}`);
    });

    function callApi(formData, url = null) {
        $.ajax({
            url: url ? url : "{{ route('users.index') }}",
            method: 'POST',
            data: formData,
            dataType: 'JSON',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('.text-danger.error-msg').text('');
            },
            success: function (response) {
                $(`#form-${formMode}`).trigger('reset');
                $(`#modal-${formMode}`).modal('hide');
                table.ajax.reload(null, false);
                alert(response.message);
            },
            error: function (response) {
                if (response.status === 422) {
                    const errors = response.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        $(`#${formMode}-error-${field}`).text(messages[0]);
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
