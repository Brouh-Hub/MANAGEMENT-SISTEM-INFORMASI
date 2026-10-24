```blade
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
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
        <i class="fa fa-plus"></i> Tambah Surat
    </button>
</div>

<div class="card-panel">
    <div class="mb-3" style="max-width: 320px;">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="fa fa-search text-muted"></i>
            </span>
            <input
                type="text"
                id="search-box"
                class="form-control border-start-0"
                placeholder="Cari nomor surat, pengirim, atau perihal..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="table" width="100%">
            <thead>
                <tr class="text-muted text-uppercase" style="font-size:11px;">
                    <th>No</th>
                    <th>Nomor Surat</th>
                    <th>Pengirim</th>
                    <th>Perihal</th>
                    <th>Tanggal Diterima</th>
                    <th>Prioritas</th>
                    <th>Status Disposisi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@include('admin.surat-masuk.modal')

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    "use strict";

    let formMode = 'create';

    const table = $('#table').DataTable({
        serverSide: true,
        processing: true,

        // Sembunyikan search bawaan DataTables
        // Search tetap menggunakan #search-box custom di atas tabel
        dom: 'rtip',

        ajax: {
            url: "{{ route('surat-masuk.index') }}",
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
                data: 'nomor_surat',
                name: 'nomor_surat'
            },
            {
                data: 'pengirim',
                name: 'pengirim'
            },
            {
                data: 'perihal',
                name: 'perihal'
            },
            {
                data: 'tanggal_terima',
                name: 'tanggal_terima'
            },
            {
                data: 'prioritas',
                name: 'prioritas',
                orderable: false,
                searchable: false
            },
            {
                data: 'status_disposisi',
                name: 'status_disposisi',
                orderable: false,
                searchable: false
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            },
        ]
    });

    // Custom search box, terhubung ke DataTables
    $('#search-box').on('keyup', function () {
        table.search(this.value).draw();
    });

    // ================= NO. AGENDA OTOMATIS =================
    // Tampilkan perkiraan nomor berikutnya (nomor final dibuat server saat disimpan)
    function tampilkanAgendaBerikutnya() {
        const tanggal = $('#form-create [name=tanggal_terima]').val();

        $.get(`{{ route('surat-masuk.agenda-berikutnya') }}`, { tanggal_terima: tanggal })
            .done(res => $('#create-no_agenda').val(res.no_agenda));
    }

    $('#form-create [name=tanggal_terima]').on('change', tampilkanAgendaBerikutnya);

    // Tombol Tambah -> reset form
    $('[data-bs-target="#modal-create"]').on('click', function () {
        formMode = 'create';
        $('#form-create').trigger('reset');
        $('#create-no_agenda').val('');
        tampilkanAgendaBerikutnya();
    });

    // Submit form Create
    $('#form-create').on('submit', function (e) {
        e.preventDefault();
        formMode = 'create';

        const formData = new FormData(this);
        formData.append('_method', 'post');

        callApi(formData);
    });

    // Submit form Update
    $('#form-update').on('submit', function (e) {
        e.preventDefault();
        formMode = 'update';

        let id = $('#update-id').val();
        const formData = new FormData(this);
        formData.append('_method', 'put');

        callApi(
            formData,
            `{{ route('surat-masuk.index') }}/${id}`
        );
    });

    // Tombol Ubah -> isi form otomatis
    $('#table tbody').on('click', '.btn-update', function () {
        let detail = $(this).data('detail');
        formMode = 'update';

        for (const key in detail) {
            if (detail.hasOwnProperty(key)) {
                $(`#update-${key}`).val(detail[key]);
            }
        }

        $('#modal-update').modal('show');
    });

    // Preview file surat di modal detail (PDF & gambar tampil langsung, Word cukup tombol unduh)
    function tampilkanPreview(filePath) {
        const $box = $('#detail-preview').empty();
        const $aksi = $('#detail-file-actions');

        if (!filePath) {
            $aksi.attr('style', 'display:none !important;');
            $box.html('<div class="text-center text-muted"><i class="fa fa-file-circle-xmark fa-2x mb-2"></i><div>Tidak ada file surat</div></div>');
            return;
        }

        const url = `{{ asset('storage') }}/${filePath}`;
        const ext = filePath.split('.').pop().toLowerCase();

        $('#detail-file-open').attr('href', url);
        $('#detail-file-download').attr('href', url);
        $aksi.attr('style', '');

        if (ext === 'pdf') {
            $box.html($('<iframe>', { src: url, style: 'width:100%;height:100%;border:0;' }));
        } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
            $box.html($('<img>', { src: url, alt: 'File surat', style: 'max-width:100%;max-height:100%;object-fit:contain;' }));
        } else {
            $box.html(`<div class="text-center text-muted px-3">
                <i class="fa fa-file-word fa-3x mb-2" style="color:#2B579A;"></i>
                <div>File ${ext.toUpperCase()} tidak bisa ditampilkan langsung.</div>
                <div class="small">Gunakan tombol <b>Unduh</b> di bawah.</div>
            </div>`);
        }
    }

    // Kosongkan preview saat modal ditutup (supaya PDF berhenti dimuat)
    $('#modal-detail').on('hidden.bs.modal', function () {
        $('#detail-preview').empty();
    });

    // Tombol Lihat -> isi field readonly di modal detail
    $('#table tbody').on('click', '.btn-detail', function () {
        let detail = $(this).data('detail');

        for (const key in detail) {
            if (detail.hasOwnProperty(key)) {
                $(`#detail-${key}`).text(detail[key] ?? '-');
            }
        }

        tampilkanPreview(detail.file_path);

        $('#modal-detail').modal('show');
    });

    // Tombol Hapus
    $('#table tbody').on('click', '.btn-delete', async function () {
        let id = $(this).data('id');

        if (!await konfirmasi('Yakin mau hapus surat ini?')) {
            return;
        }

        let formData = new FormData();
        formData.append('_method', 'delete');

        callApi(
            formData,
            `{{ route('surat-masuk.index') }}/${id}`
        );
    });

    // Fungsi AJAX universal (create/update/delete)
    function callApi(formData, url = null) {
        $.ajax({
            url: url
                ? url
                : "{{ route('surat-masuk.index') }}",

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

                alert(
                    response.responseJSON?.message
                    ?? 'Terjadi kesalahan.'
                );
            }
        });
    }
});
</script>
@endpush
```
