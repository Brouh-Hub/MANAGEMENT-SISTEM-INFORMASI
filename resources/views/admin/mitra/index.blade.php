    @extends('layouts.main')
    @section('content')

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $title }}</h4>
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
            <i class="fa fa-plus"></i> Tambah Mitra
        </button>
    </div>

    <div class="card-panel">
        <div class="mb-3" style="max-width: 320px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="search-box" class="form-control border-start-0" placeholder="Cari nama mitra atau bidang...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="table" width="100%">
                <thead>
                    <tr class="text-muted text-uppercase" style="font-size:11px;">
                        <th>No</th>
                        <th>Nama Perusahaan</th>
                        <th>Bidang</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Website</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@include('admin.mitra.modal')

<div class="modal fade" id="modal-detail-pkl" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-users me-2"></i>
                    Siswa PKL
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <h5 id="detail-pkl-mitra" class="fw-bold mb-1">-</h5>

                    <span class="badge bg-primary">
                        <span id="detail-pkl-jumlah">0</span> Siswa PKL
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody id="detail-pkl-list">
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

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
        const baseUrl = "{{ route('mitra.index') }}";

        const table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'rtip', // sembunyikan search bawaan, pakai #search-box
            ajax: baseUrl,
columns: [
    {
        data: 'DT_RowIndex',
        name: 'DT_RowIndex',
        orderable: false,
        searchable: false
    },
    {
        data: 'nama_perusahaan',
        name: 'nama_perusahaan'
    },
    {
        data: 'bidang',
        name: 'bidang'
    },
    {
        data: 'contact_person',
        name: 'contact_person'
    },
    {
        data: 'email',
        name: 'email'
    },
    {
        data: 'website',
        name: 'website'
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
            table.search(this.value).draw();
        });

$('#table tbody').on('click', '.btn-detail-pkl', function () {

    const id = $(this).data('id');

    // Reset modal
    $('#detail-pkl-mitra').text('Memuat data...');
    $('#detail-pkl-jumlah').text('0 Siswa PKL');

    $('#detail-pkl-list').html(`
        <tr>
            <td colspan="6" class="text-center">
                <i class="fa fa-spinner fa-spin me-1"></i>
                Memuat data siswa PKL...
            </td>
        </tr>
    `);

    $('#modal-detail-pkl').modal('show');

    $.ajax({
        url: `${baseUrl}/${id}/pkl`,
        method: 'GET',

        success: function (res) {

            // Ambil nama mitra dengan aman
            let namaMitra = '-';

            if (typeof res.mitra === 'string') {
                namaMitra = res.mitra;
            } else if (res.mitra && typeof res.mitra === 'object') {
                namaMitra =
                    res.mitra.nama_perusahaan ||
                    res.mitra.nama ||
                    '-';
            }

            // Jumlah berdasarkan data yang benar-benar ditampilkan
            const jumlah =
                Array.isArray(res.data)
                    ? res.data.length
                    : 0;

            // Tampilkan informasi yang jelas
            $('#detail-pkl-mitra').text(
                `Mitra: ${namaMitra}`
            );

            $('#detail-pkl-jumlah').text(
                `${jumlah} Siswa PKL`
            );

            // Tidak ada siswa
            if (
                !Array.isArray(res.data) ||
                res.data.length === 0
            ) {

                $('#detail-pkl-list').html(`
                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-muted py-4"
                        >
                            <i class="fa fa-users mb-2"></i>
                            <br>
                            Belum ada siswa yang terdaftar
                            untuk PKL di mitra ini.
                        </td>
                    </tr>
                `);

                return;
            }

            // Nama siswa berasal dari file Excel: escape agar tidak jadi HTML
            const esc = v => $('<div>').text(v ?? '').html();

            // Tampilkan daftar siswa
            let html = '';

            res.data.forEach(function (item, index) {

                html += `
                    <tr>
                        <td>${index + 1}</td>

                        <td>
                            ${esc(item.nis || '-')}
                        </td>

                        <td>
                            <strong>
                                ${esc(item.nama || '-')}
                            </strong>
                        </td>

                        <td>
                            ${item.tanggal_mulai || '-'}
                        </td>

                        <td>
                            ${item.tanggal_selesai || '-'}
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                ${esc(item.status || 'Belum diketahui')}
                            </span>
                        </td>
                    </tr>
                `;
            });

            $('#detail-pkl-list').html(html);
        },

        error: function (xhr) {

            $('#detail-pkl-mitra').text(
                'Data PKL'
            );

            $('#detail-pkl-jumlah').text(
                '0 Siswa PKL'
            );

            $('#detail-pkl-list').html(`
                <tr>
                    <td
                        colspan="6"
                        class="text-center text-danger py-4"
                    >
                        <i class="fa fa-exclamation-circle me-1"></i>
                        Gagal mengambil data siswa PKL.
                    </td>
                </tr>
            `);

            console.error(xhr.responseText);
        }
    });

});
        // Preview foto saat memilih file
        function previewFile(input, imgSelector) {
            const file = input.files[0];
            const img = $(imgSelector);
            if (file) {
                img.attr('src', URL.createObjectURL(file)).removeClass('d-none');
            } else {
                img.addClass('d-none').attr('src', '');
            }
        }

        // Buka modal tambah
        $('[data-bs-target="#modal-create"]').on('click', function () {
            formMode = 'create';
            $('#form-create').trigger('reset');
            $('#create-foto-preview').addClass('d-none').attr('src', '');
            $('.error-msg').text('');
        });

        // Simpan tambah
        $('#form-create').on('submit', function (e) {
            e.preventDefault();
            formMode = 'create';
            callApi(new FormData(this));
        });

        // Simpan ubah (POST + _method=PUT karena PUT multipart tidak terbaca PHP)
        $('#form-update').on('submit', function (e) {
            e.preventDefault();
            formMode = 'update';

            const id = $('#update-id').val();
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            callApi(formData, `${baseUrl}/${id}`);
        });

        // Buka modal ubah
        $('#table tbody').on('click', '.btn-update', function () {
            const detail = $(this).data('detail');
            formMode = 'update';
            $('.error-msg').text('');

            for (const key in detail) {
                // input file tidak boleh diisi nilai, jadi foto dilewati
                if (detail.hasOwnProperty(key) && key !== 'foto') {
                    $(`#update-${key}`).val(detail[key]);
                }
            }

            $('#update-foto').val('');
            $('#update-foto-preview')
                .attr('src', detail.foto_url ?? '')
                .toggleClass('d-none', !detail.foto_url);

            $('#modal-update').modal('show');
        });

        // Buka modal detail
        $('#table tbody').on('click', '.btn-detail', function () {
            const detail = $(this).data('detail');

            for (const key in detail) {
                if (detail.hasOwnProperty(key) && key !== 'foto') {
                    $(`#detail-${key}`).text(detail[key] ?? '-');
                }
            }

            $('#detail-foto')
                .attr('src', detail.foto_url ?? '')
                .toggleClass('d-none', !detail.foto_url);

            $('#modal-detail').modal('show');
        });

        // Hapus
        $('#table tbody').on('click', '.btn-delete', async function () {
            const id = $(this).data('id');

            if (!await konfirmasi('Yakin mau hapus data mitra ini?')) return;

            formMode = 'delete';
            const formData = new FormData();
            formData.append('_method', 'DELETE');

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
                        $(`#${formMode}-foto-preview`).addClass('d-none').attr('src', '');
                        $(`#modal-${formMode}`).modal('hide');
                    }
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
