@extends('layouts.main')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">{{ $title ?? 'Template Sertifikat' }}</h4>
        <small class="text-muted">{{ $subtitle ?? 'Kelola desain latar sertifikat' }}</small>
    </div>
    <button type="button" class="btn text-white" style="background: var(--brand);" data-bs-toggle="modal" data-bs-target="#modalTemplate">
        <i class="fa fa-plus me-1"></i> Tambah Template
    </button>
</div>

<div class="card-panel">
    <div class="table-responsive">
        <table id="table-template" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Preview</th>
                    <th>Nama Template</th>
                    <th>Kategori</th>
                    <th>Posisi Field</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- diisi oleh DataTables via AJAX --}}
            </tbody>
        </table>
    </div>
</div>

{{-- ===================== MODAL: TAMBAH / EDIT TEMPLATE ===================== --}}
<div class="modal fade" id="modalTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: none;">
            <form id="formTemplate" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="template_id" id="template_id">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTemplateLabel">Tambah Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Template</label>
                        <input type="text" name="nama_template" id="nama_template" class="form-control" placeholder="Contoh: Sertifikat Kerja Sama - Biru">
                        <small class="text-danger d-none" id="error_nama_template"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" name="kategori" id="kategori" class="form-control" placeholder="Contoh: Kerja Sama Industri, Penghargaan, dll">
                        <small class="text-danger d-none" id="error_kategori"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Gambar Latar (JPG/PNG, maks 5MB)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1" id="fileHint">Kosongkan kalau tidak ingin ganti gambar saat edit.</small>
                        <small class="text-danger d-none" id="error_file"></small>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background: var(--brand);">
                        <i class="fa fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {

    const tableTemplate = $('#table-template').DataTable({
        processing: true,
        serverSide: true,

        // Tampilkan semua data sekaligus
        paging: false,
        lengthChange: false,
        pageLength: -1,

        ajax: '{{ route("template-sertifikat.index") }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'preview', orderable: false, searchable: false },
            { data: 'nama_template' },
            { data: 'kategori' },
            { data: 'jumlah_field', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    function clearErrors() {
        $('#formTemplate small.text-danger').addClass('d-none').text('');
        $('#formTemplate .form-control').removeClass('is-invalid');
    }

    $('#modalTemplate').on('hidden.bs.modal', function () {
        $('#formTemplate')[0].reset();
        $('#template_id').val('');
        $('#modalTemplateLabel').text('Tambah Template');
        $('#file').prop('required', true);
        $('#fileHint').addClass('d-none');
        clearErrors();
    });

    $('#formTemplate').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#template_id').val();
        const url = id ? `/admin/template-sertifikat/${id}` : '{{ route("template-sertifikat.store") }}';
        const formData = new FormData(this);
        if (id) formData.append('_method', 'PUT');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                $('#modalTemplate').modal('hide');
                tableTemplate.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function (field) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#error_${field}`).removeClass('d-none').text(errors[field][0]);
                    });
                } else {
                    alert(xhr.responseJSON?.message || 'Terjadi kesalahan, coba lagi.');
                }
            }
        });
    });

    $('#table-template').on('click', '.btn-update', function () {
        const detail = $(this).data('detail');

        $('#template_id').val(detail.id);
        $('#nama_template').val(detail.nama_template);
        $('#kategori').val(detail.kategori);
        $('#file').prop('required', false);
        $('#fileHint').removeClass('d-none');
        $('#modalTemplateLabel').text('Edit Template');
        $('#modalTemplate').modal('show');
    });

    $('#table-template').on('click', '.btn-delete', async function () {
        const id = $(this).data('id');
        if (!await konfirmasi('Hapus template ini? Sertifikat yang sudah pakai template ini tidak akan ikut terhapus.')) return;

        $.ajax({
            url: `/admin/template-sertifikat/${id}`,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function () {
                tableTemplate.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Gagal hapus template');
            }
        });
    });

});
</script>
@endpush
