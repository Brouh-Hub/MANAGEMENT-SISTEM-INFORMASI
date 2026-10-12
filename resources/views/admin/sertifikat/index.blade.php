@extends('layouts.main')

@section('content')

{{-- =========================================================
    HEADER
========================================================= --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">{{ $title ?? 'Sertifikat' }}</h4>
        <small class="text-muted">
            {{ $subtitle ?? 'Generate sertifikat berdasarkan data siswa' }}
        </small>
    </div>

    <div class="d-flex gap-2">

        {{-- IMPORT SISWA --}}
        <button type="button"
                class="btn btn-outline-secondary"
                data-bs-toggle="modal"
                data-bs-target="#modalImportSiswa">
            <i class="fa fa-file-import me-1"></i>
            Import Siswa
        </button>

        {{-- CLEAR SEMUA DATA --}}
        <button type="button"
                class="btn btn-outline-danger"
                id="btnClearSemuaSiswa">
            <i class="fa fa-trash me-1"></i>
            Clear Semua Data
        </button>

    </div>
</div>


{{-- =========================================================
    TABLE DATA SISWA
========================================================= --}}
<div class="card-panel">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div class="input-group" style="max-width: 360px;">
            <span class="input-group-text bg-white border-end-0">
                <i class="fa fa-search text-muted"></i>
            </span>

            <input type="text"
                   id="searchSiswa"
                   class="form-control border-start-0"
                   placeholder="Cari NIS / nama / kelas / jurusan...">
        </div>

    </div>

    <div class="table-responsive">

        <table id="table-siswa"
               class="table table-hover align-middle w-100">

            <thead>
                <tr class="text-muted text-uppercase"
                    style="font-size:11px;">

                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Konsentrasi Keahlian</th>
                    <th style="width:180px;">Aksi</th>

                </tr>
            </thead>

        </table>

    </div>

</div>


{{-- =========================================================
    MODAL BUAT / EDIT SERTIFIKAT
========================================================= --}}
<div class="modal fade"
     id="modalGenerateSertifikat"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content"
             style="border-radius:14px; border:none;">

            <form id="formGenerateSertifikat">

                @csrf

                <input type="hidden"
                       name="sertifikat_id"
                       id="sertifikat_id">

                <input type="hidden"
                       name="siswa_id"
                       id="siswa_id">

                {{-- HEADER MODAL --}}
                <div class="modal-header border-0 pb-0">

                    <div>
                        <h5 class="modal-title fw-bold"
                            id="modalGenerateSertifikatLabel">
                            Generate Sertifikat
                        </h5>

                        <small class="text-muted"
                               id="selectedSiswaInfo">
                        </small>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>


                {{-- BODY --}}
                <div class="modal-body pt-2"
                     style="max-height:70vh; overflow-y:auto;">

                    <div class="row g-3">

                        {{-- TEMPLATE --}}
                        <div class="col-md-6">

                            <label class="form-label small fw-semibold">
                                Template Sertifikat
                            </label>

                            <select name="template_key"
                                    id="template_key"
                                    class="form-select">

                                @foreach($templates as $key => $t)

                                    <option value="{{ $key }}"
                                            data-image="{{ asset($t['image']) }}">
                                        {{ $t['label'] }}
                                    </option>

                                @endforeach

                            </select>

                            <small class="text-danger d-none"
                                   id="error_template_key">
                            </small>

                            <img id="template_preview"
                                 class="mt-2 rounded border w-100"
                                 alt="Pratinjau template">

                        </div>


                        {{-- DATA UMUM --}}
                        <div class="col-md-6">

                            {{-- NAMA --}}
                            <div class="mb-3">

                                <label class="form-label small fw-semibold">
                                    Nama Penerima
                                </label>

                                <input type="text"
                                       name="nama_penerima"
                                       id="nama_penerima"
                                       class="form-control"
                                       placeholder="Nama lengkap penerima">

                                <small class="text-danger d-none"
                                       id="error_nama_penerima">
                                </small>

                            </div>


                            {{-- TANGGAL --}}
                            <div class="mb-3">

                                <label class="form-label small fw-semibold">
                                    Tanggal Sertifikat
                                </label>

                                <input type="date"
                                       name="tanggal_terbit"
                                       id="tanggal_terbit"
                                       class="form-control">

                                <small class="text-danger d-none"
                                       id="error_tanggal_terbit">
                                </small>

                            </div>


                            {{-- KERJA SAMA --}}
                            <div class="mb-3">

                                <label class="form-label small fw-semibold">
                                    Kerja Sama Industri (opsional)
                                </label>

                                <select name="kerjasama_id"
                                        id="kerjasama_id"
                                        class="form-select">

                                    <option value="">
                                        -- Tidak terkait kerja sama --
                                    </option>

                                    @foreach($kerjasamaList as $kerjasama)

                                        <option value="{{ $kerjasama->id }}">
                                            {{ $kerjasama->judul_kerjasama }}
                                        </option>

                                    @endforeach

                                </select>

                                <small class="text-danger d-none"
                                       id="error_kerjasama_id">
                                </small>

                            </div>


                            {{-- NOMOR --}}
                            <div>

                                <label class="form-label small fw-semibold">
                                    Nomor Sertifikat
                                </label>

                                <input type="text"
                                       name="nomor_sertifikat"
                                       id="nomor_sertifikat"
                                       class="form-control"
                                       placeholder="Kosongkan = nomor otomatis">

                                <small class="text-muted">
                                    Kosongkan agar sistem membuat nomor otomatis.
                                </small>

                                <small class="text-danger d-none"
                                       id="error_nomor_sertifikat">
                                </small>

                            </div>

                        </div>


                        {{-- =================================================
                            DATA PKL
                        ================================================= --}}
                        <div class="col-12 d-none"
                             id="blok-pkl">

                            <div class="row g-3">

                                <div class="col-12">

                                    <hr class="my-1">

                                    <div class="fw-semibold small text-uppercase text-muted">
                                        Data PKL
                                    </div>

                                </div>


                                {{-- SISWA YANG DIPILIH --}}
                                <div class="col-12">

                                    <div class="alert alert-light border mb-0">

                                        <div class="d-flex align-items-center">

                                            <div class="me-3">
                                                <i class="fa fa-user-graduate fa-lg text-muted"></i>
                                            </div>

                                            <div>

                                                <div class="small text-muted">
                                                    Siswa yang dipilih
                                                </div>

                                                <div class="fw-semibold"
                                                     id="info_nama_siswa">
                                                    -
                                                </div>

                                                <div class="small text-muted"
                                                     id="info_nis_siswa">
                                                    NIS: -
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                {{-- NIS --}}
                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">
                                        Nomor Induk Siswa (NIS)
                                    </label>

                                    <input type="text"
                                           name="nis"
                                           id="nis"
                                           class="form-control"
                                           readonly>

                                    <small class="text-danger d-none"
                                           id="error_nis">
                                    </small>

                                </div>


                                {{-- TEMPAT LAHIR --}}
                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">
                                        Tempat Lahir
                                    </label>

                                    <input type="text"
                                           name="tempat_lahir"
                                           id="tempat_lahir"
                                           class="form-control"
                                           placeholder="Contoh: Kijang">

                                    <small class="text-danger d-none"
                                           id="error_tempat_lahir">
                                    </small>

                                </div>


                                {{-- TANGGAL LAHIR --}}
                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">
                                        Tanggal Lahir
                                    </label>

                                    <input type="date"
                                           name="tanggal_lahir"
                                           id="tanggal_lahir"
                                           class="form-control">

                                    <small class="text-danger d-none"
                                           id="error_tanggal_lahir">
                                    </small>

                                </div>


                                {{-- KONSENTRASI --}}
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold">
                                        Konsentrasi Keahlian
                                    </label>

                                    <input type="text"
                                           name="konsentrasi"
                                           id="konsentrasi"
                                           class="form-control"
                                           readonly>

                                    <small class="text-danger d-none"
                                           id="error_konsentrasi">
                                    </small>

                                </div>


                                {{-- PREDIKAT --}}
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold">
                                        Predikat
                                    </label>

                                    <select name="predikat"
                                            id="predikat"
                                            class="form-select">

                                        <option value="Sangat Baik">
                                            Sangat Baik
                                        </option>

                                        <option value="Baik">
                                            Baik
                                        </option>

                                        <option value="Cukup">
                                            Cukup
                                        </option>

                                    </select>

                                    <small class="text-danger d-none"
                                           id="error_predikat">
                                    </small>

                                </div>


                                {{-- TEMPAT PKL --}}
                                <div class="col-12">

                                    <label class="form-label small fw-semibold">
                                        Tempat PKL
                                    </label>

                                    <input type="text"
                                           name="mitra"
                                           id="mitra"
                                           class="form-control"
                                           placeholder="Contoh: Nongsa Digital Park">

                                    <small class="text-danger d-none"
                                           id="error_mitra">
                                    </small>

                                </div>


                                {{-- PENANDA TANGAN --}}
                                <div class="col-12">

                                    <div class="fw-semibold small text-uppercase text-muted">
                                        Penanda Tangan dari Mitra
                                    </div>

                                </div>


                                {{-- JABATAN --}}
                                <div class="col-md-3">

                                    <label class="form-label small fw-semibold">
                                        Jabatan
                                    </label>

                                    <input type="text"
                                           name="pimpinan_jabatan"
                                           id="pimpinan_jabatan"
                                           class="form-control"
                                           value="Kepala">

                                    <small class="text-danger d-none"
                                           id="error_pimpinan_jabatan">
                                    </small>

                                </div>


                                {{-- NAMA --}}
                                <div class="col-md-5">

                                    <label class="form-label small fw-semibold">
                                        Nama (beserta gelar)
                                    </label>

                                    <input type="text"
                                           name="pimpinan_nama"
                                           id="pimpinan_nama"
                                           class="form-control"
                                           placeholder="Contoh: Drs. H. Edi Batara, M.A.P.">

                                    <small class="text-danger d-none"
                                           id="error_pimpinan_nama">
                                    </small>

                                </div>


                                {{-- NIP --}}
                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">
                                        NIP (opsional)
                                    </label>

                                    <input type="text"
                                           name="pimpinan_nip"
                                           id="pimpinan_nip"
                                           class="form-control"
                                           placeholder="Kosongkan jika tidak ada">

                                    <small class="text-danger d-none"
                                           id="error_pimpinan_nip">
                                    </small>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            DATA UMUM NON PKL
                        ================================================= --}}
                        <div class="col-12"
                             id="blok-umum">

                            <div class="row g-3">

                                <div class="col-12">

                                    <label class="form-label small fw-semibold">
                                        Judul / Kegiatan
                                    </label>

                                    <input type="text"
                                           name="judul"
                                           id="judul"
                                           class="form-control"
                                           placeholder="Contoh: Juara 1 Lomba Kompetensi Siswa 2026">

                                    <small class="text-danger d-none"
                                           id="error_judul">
                                    </small>

                                </div>


                                <div class="col-12">

                                    <label class="form-label small fw-semibold">
                                        Deskripsi (opsional)
                                    </label>

                                    <textarea name="deskripsi"
                                              id="deskripsi"
                                              class="form-control"
                                              rows="2"
                                              placeholder="Keterangan singkat..."></textarea>

                                    <small class="text-danger d-none"
                                           id="error_deskripsi">
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="modal-footer border-0 pt-0">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn text-white"
                            style="background:var(--brand);"
                            id="btnSimpanSertifikat">

                        <i class="fa fa-floppy-disk me-1"></i>
                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =========================================================
    MODAL DETAIL SISWA
========================================================= --}}
<div class="modal fade"
     id="modalDetailSiswa"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content"
             style="border-radius:14px; border:none;">

            <div class="modal-header border-0">

                <div>
                    <h5 class="modal-title fw-bold">
                        Detail Data Siswa
                    </h5>

                    <small class="text-muted">
                        Data siswa, PKL, dan sertifikat
                    </small>
                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div id="detail-siswa-loading"
                     class="text-center py-5">

                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>

                    <div class="small text-muted mt-2">
                        Memuat data...
                    </div>

                </div>


                <div id="detail-siswa-content"
                     class="d-none">

                    {{-- DATA SISWA --}}
                    <div class="mb-4">

                        <div class="fw-bold mb-3">
                            <i class="fa fa-user-graduate me-2"></i>
                            Data Siswa
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <small class="text-muted">NIS</small>
                                <div class="fw-semibold"
                                     id="detail_nis">
                                    -
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Nama</small>
                                <div class="fw-semibold"
                                     id="detail_nama">
                                    -
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Tempat Lahir</small>
                                <div id="detail_tempat_lahir">
                                    -
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Tanggal Lahir</small>
                                <div id="detail_tanggal_lahir">
                                    -
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Kelas</small>
                                <div id="detail_kelas">
                                    -
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted">Jurusan</small>
                                <div id="detail_konsentrasi">
                                    -
                                </div>
                            </div>

                        </div>

                    </div>

                    <hr>


                    {{-- DATA PKL --}}
                    <div class="mb-4">

                        <div class="fw-bold mb-3">
                            <i class="fa fa-building me-2"></i>
                            Data PKL
                        </div>

                        <div id="detail-pkl-ada">

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Tempat PKL
                                    </small>

                                    <div class="fw-semibold"
                                         id="detail_tempat_pkl">
                                        -
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Pembimbing PKL
                                    </small>

                                    <div id="detail_pembimbing">
                                        -
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Tanggal Mulai
                                    </small>

                                    <div id="detail_tanggal_mulai">
                                        -
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Tanggal Selesai
                                    </small>

                                    <div id="detail_tanggal_selesai">
                                        -
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Status PKL
                                    </small>

                                    <div id="detail_status_pkl">
                                        -
                                    </div>

                                </div>

                                <div class="col-12">

                                    <small class="text-muted">
                                        Keterangan
                                    </small>

                                    <div id="detail_keterangan">
                                        -
                                    </div>

                                </div>

                            </div>

                        </div>


                        <div id="detail-pkl-tidak-ada"
                             class="alert alert-light border d-none mb-0">

                            <i class="fa fa-info-circle me-1"></i>
                            Siswa ini belum memiliki data PKL.

                        </div>

                    </div>

                    <hr>


                    {{-- DATA SERTIFIKAT --}}
                    <div>

                        <div class="fw-bold mb-3">

                            <i class="fa fa-certificate me-2"></i>
                            Data Sertifikat

                        </div>


                        <div id="detail-sertifikat-ada">

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Status
                                    </small>

                                    <div id="detail_status_sertifikat">
                                        -
                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Nomor Sertifikat
                                    </small>

                                    <div id="detail_nomor_sertifikat">
                                        -
                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Tanggal Terbit
                                    </small>

                                    <div id="detail_tanggal_terbit">
                                        -
                                    </div>

                                </div>

                            </div>

                        </div>


                        <div id="detail-sertifikat-tidak-ada"
                             class="alert alert-light border d-none mb-0">

                            <i class="fa fa-info-circle me-1"></i>
                            Sertifikat belum dibuat.

                        </div>

                    </div>

                </div>

            </div>


            <div class="modal-footer border-0">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Tutup
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    MODAL IMPORT SISWA
========================================================= --}}
<div class="modal fade"
     id="modalImportSiswa"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content"
             style="border-radius:14px; border:none;">

            <form id="formImportSiswa"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-header border-0 pb-0">

                    <h5 class="modal-title fw-bold">
                        Import Data Siswa
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body pt-2">

                    <p class="small text-muted mb-2">

                        File Excel dapat menggunakan format Rekapan Pemetaan PKL
                        dengan kolom seperti:

                        <b>Nama Siswa</b>,
                        <b>Kelas</b>,
                        <b>Program Keahlian</b>,
                        <b>DUDI/Instansi</b>,
                        <b>Alamat DUDI/Instansi</b>,
                        <b>Pembimbing</b>.

                        Jika kolom NIS tersedia, sistem akan menggunakannya
                        untuk mencocokkan siswa.

                    </p>


                    <a href="{{ url('admin/sertifikat/siswa/template') }}"
                       class="small">

                        Unduh contoh format (CSV)

                    </a>


                    <div class="mt-3">

                        <label class="form-label small fw-semibold">
                            File (CSV / XLSX)
                        </label>

                        <input type="file"
                               name="file"
                               id="file_import_siswa"
                               class="form-control"
                               accept=".csv,.txt,.xlsx,.xls">

                        <small class="text-danger d-none"
                               id="error_file_import">
                        </small>

                    </div>


                    <div id="hasil-import-siswa"
                         class="small mt-3 d-none">
                    </div>

                </div>


                <div class="modal-footer border-0 pt-0">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Tutup
                    </button>

                    <button type="submit"
                            class="btn text-white"
                            style="background:var(--brand);"
                            id="btnImportSiswa">

                        <i class="fa fa-file-import me-1"></i>
                        Import

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

    const baseUrl = '{{ url("admin/sertifikat") }}';


    /* =========================================================
       CLEAR SEMUA DATA SISWA
    ========================================================= */

    $('#btnClearSemuaSiswa').on('click', function () {

        if (!confirm(
            'PERINGATAN!\n\n' +
            'Semua data siswa akan dihapus dari database.\n\n' +
            'Tindakan ini tidak bisa dibatalkan.\n\n' +
            'Lanjutkan?'
        )) {
            return;
        }

        if (!confirm(
            'Yakin ingin menghapus SEMUA DATA SISWA?\n\n' +
            'Klik OK untuk benar-benar menghapus.'
        )) {
            return;
        }

        const button = $(this);

        button.prop('disabled', true);

        button.html(
            '<i class="fa fa-spinner fa-spin me-1"></i> Menghapus...'
        );


        $.ajax({

            url: baseUrl + '/siswa/clear',

            method: 'POST',

            data: {
                _token: '{{ csrf_token() }}'
            },

            success: function (res) {

                tableSiswa.ajax.reload(
                    null,
                    false
                );

                alert(
                    res.message ||
                    'Semua data siswa berhasil dihapus.'
                );

            },

            error: function (xhr) {

                alert(
                    xhr.responseJSON?.message ||
                    'Gagal menghapus semua data siswa.'
                );

            },

            complete: function () {

                button.prop(
                    'disabled',
                    false
                );

                button.html(
                    '<i class="fa fa-trash me-1"></i> Clear Semua Data'
                );

            }

        });

    });


    /* =========================================================
       DATATABLE SISWA PKL
    ========================================================= */

    const tableSiswa = $('#table-siswa').DataTable({

        processing: true,

        serverSide: true,

        searching: false,

        paging: true,

        pageLength: 10,

        lengthChange: true,


        ajax: {

            url: "{{ route('sertifikat.siswa') }}",

            type: 'GET',

            dataType: 'json',

            data: function (d) {

                d.search.value =
                    $('#searchSiswa').val();

            },

            error: function (
                xhr,
                error,
                thrown
            ) {

                console.error(
                    'DATATABLE ERROR:',
                    {
                        status: xhr.status,
                        error: error,
                        thrown: thrown,
                        response: xhr.responseText
                    }
                );

            }

        },


        columns: [

            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },

            {
                data: 'nis',
                name: 'nis',
                defaultContent: '-'
            },

            {
                data: 'nama',
                name: 'nama',
                defaultContent: '-'
            },

            {
                data: 'kelas',
                name: 'kelas',
                defaultContent: '-'
            },

            {
                data: 'jurusan',
                name: 'jurusan',
                orderable: false,
                defaultContent: '-'
            },

            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }

        ]

    });


    /* =========================================================
       SEARCH
    ========================================================= */

    let searchTimer;

    $('#searchSiswa').on(
        'keyup',
        function () {

            clearTimeout(
                searchTimer
            );

            searchTimer = setTimeout(
                function () {

                    tableSiswa.draw();

                },
                300
            );

        }
    );


    /* =========================================================
       TANGGAL HARI INI
    ========================================================= */

    function hariIni() {

        const d = new Date();

        return d.getFullYear()
            + '-'
            + String(
                d.getMonth() + 1
            ).padStart(2, '0')
            + '-'
            + String(
                d.getDate()
            ).padStart(2, '0');

    }


    /* =========================================================
       ESC HTML
    ========================================================= */

    function escHtml(value) {

        return $('<div>')
            .text(value ?? '-')
            .html();

    }


    /* =========================================================
       DETAIL SISWA
    ========================================================= */

    $('#table-siswa tbody').on(
        'click',
        '.btn-detail-siswa',
        function () {

            const siswaId =
                $(this).data('id');


            /* RESET */

            $('#detail_nama')
                .text('Memuat...');

            $('#detail_nis')
                .text('-');

            $('#detail_kelas')
                .text('-');

            $('#detail_konsentrasi')
                .text('-');

            $('#detail_tempat_lahir')
                .text('-');

            $('#detail_tanggal_lahir')
                .text('-');


            $('#detail-pkl-ada')
                .addClass('d-none');

            $('#detail-pkl-tidak-ada')
                .addClass('d-none');


            $('#detail-sertifikat-ada')
                .addClass('d-none');

            $('#detail-sertifikat-tidak-ada')
                .addClass('d-none');


            $('#detail-siswa-loading')
                .removeClass('d-none');

            $('#detail-siswa-content')
                .addClass('d-none');


            $('#modalDetailSiswa')
                .modal('show');


            /* REQUEST DETAIL */

            $.ajax({

                url:
                    baseUrl +
                    '/siswa/' +
                    siswaId +
                    '/detail',

                method: 'GET',


                success: function (result) {

                    const siswa =
                        result.siswa || {};


                    /* =========================
                       DATA SISWA
                    ========================= */

                    $('#detail_nama')
                        .text(
                            siswa.nama || '-'
                        );

                    $('#detail_nis')
                        .text(
                            siswa.nis || '-'
                        );

                    $('#detail_kelas')
                        .text(
                            siswa.kelas || '-'
                        );

                    $('#detail_konsentrasi')
                        .text(
                            siswa.jurusan || '-'
                        );

                    $('#detail_tempat_lahir')
                        .text(
                            siswa.tempat_lahir || '-'
                        );

                    $('#detail_tanggal_lahir')
                        .text(
                            siswa.tanggal_lahir || '-'
                        );


                    /* =========================
                       DATA PKL
                    ========================= */

                    const pkl =
                        result.pkl || null;


                    if (pkl) {

                        $('#detail_tempat_pkl')
                            .text(
                                pkl.tempat_pkl || '-'
                            );

                        $('#detail_pembimbing')
                            .text(
                                pkl.pembimbing_pkl || '-'
                            );

                        $('#detail_tanggal_mulai')
                            .text(
                                pkl.tanggal_mulai || '-'
                            );

                        $('#detail_tanggal_selesai')
                            .text(
                                pkl.tanggal_selesai || '-'
                            );

                        $('#detail_status_pkl')
                            .text(
                                pkl.status || '-'
                            );

                        $('#detail_keterangan')
                            .text(
                                pkl.keterangan || '-'
                            );


                        $('#detail-pkl-ada')
                            .removeClass('d-none');

                        $('#detail-pkl-tidak-ada')
                            .addClass('d-none');

                    } else {

                        $('#detail-pkl-ada')
                            .addClass('d-none');

                        $('#detail-pkl-tidak-ada')
                            .removeClass('d-none');

                    }


                    /* =========================
                       DATA SERTIFIKAT
                    ========================= */

                    const sertifikat =
                        result.sertifikat || null;


                    if (sertifikat) {

                        $('#detail_status_sertifikat')
                            .text(
                                sertifikat.status || '-'
                            );

                        $('#detail_nomor_sertifikat')
                            .text(
                                sertifikat.nomor || '-'
                            );

                        $('#detail_tanggal_terbit')
                            .text(
                                sertifikat.tanggal_terbit || '-'
                            );


                        $('#detail-sertifikat-ada')
                            .removeClass('d-none');

                        $('#detail-sertifikat-tidak-ada')
                            .addClass('d-none');

                    } else {

                        $('#detail-sertifikat-ada')
                            .addClass('d-none');

                        $('#detail-sertifikat-tidak-ada')
                            .removeClass('d-none');

                    }


                    /* TAMPILKAN CONTENT */

                    $('#detail-siswa-loading')
                        .addClass('d-none');

                    $('#detail-siswa-content')
                        .removeClass('d-none');

                },


                error: function (xhr) {

                    console.error(
                        'DETAIL SISWA ERROR:',
                        xhr.responseText
                    );


                    $('#detail-siswa-loading')
                        .addClass('d-none');


                    $('#detail-siswa-content')
                        .removeClass('d-none');


                    alert(
                        'Gagal mengambil detail siswa.'
                    );

                }

            });

        }
    );


    /* =========================================================
       TEMPLATE PREVIEW
    ========================================================= */

    function updateTemplatePreview() {

        const selected =
            $('#template_key option:selected');

        const image =
            selected.data('image');


        if (image) {

            $('#template_preview')
                .attr('src', image)
                .show();

        } else {

            $('#template_preview')
                .hide();

        }

    }


    $('#template_key').on(
        'change',
        function () {

            updateTemplatePreview();

        }
    );


    updateTemplatePreview();


    /* =========================================================
       IMPORT SISWA / PKL
    ========================================================= */

    $('#formImportSiswa').on(
        'submit',
        function (e) {

            e.preventDefault();


            const form =
                $(this);

            const button =
                $('#btnImportSiswa');

            const hasil =
                $('#hasil-import-siswa');


            const file =
                $('#file_import_siswa')[0]
                    ?.files[0];


            if (!file) {

                $('#error_file_import')
                    .removeClass('d-none')
                    .text(
                        'Silakan pilih file terlebih dahulu.'
                    );

                return;

            }


            $('#error_file_import')
                .addClass('d-none')
                .text('');


            button.prop(
                'disabled',
                true
            );


            button.html(
                '<i class="fa fa-spinner fa-spin me-1"></i> Mengimport...'
            );


            hasil
                .removeClass('d-none')
                .removeClass('text-danger')
                .addClass('text-muted')
                .html(
                    '<i class="fa fa-spinner fa-spin me-1"></i> Memproses data...'
                );


            const formData =
                new FormData(
                    this
                );


            $.ajax({

                url:
                    baseUrl +
                    '/siswa/import',

                method: 'POST',

                data: formData,

                processData: false,

                contentType: false,


                success: function (res) {

                    let html = '';

                    html +=
                        '<div class="alert alert-success mb-2">' +
                        '<i class="fa fa-check-circle me-1"></i>' +
                        (
                            res.message ||
                            'Import berhasil.'
                        ) +
                        '</div>';


                    if (res.hasil) {

                        html += '<ul class="mb-2">';

                        html +=
                            '<li>Siswa baru: ' +
                            (
                                res.hasil.baru ?? 0
                            ) +
                            '</li>';

                        html +=
                            '<li>Siswa diperbarui: ' +
                            (
                                res.hasil.diperbarui ?? 0
                            ) +
                            '</li>';

                        html +=
                            '<li>PKL baru: ' +
                            (
                                res.hasil.pkl_baru ?? 0
                            ) +
                            '</li>';

                        html +=
                            '<li>PKL diperbarui: ' +
                            (
                                res.hasil.pkl_diperbarui ?? 0
                            ) +
                            '</li>';

                        html +=
                            '<li>Dilewati: ' +
                            (
                                res.hasil.dilewati ?? 0
                            ) +
                            '</li>';

                        html += '</ul>';


                        if (
                            res.hasil.catatan &&
                            res.hasil.catatan.length
                        ) {

                            html +=
                                '<div class="mt-2">' +
                                '<b>Catatan:</b>' +
                                '<ul class="mb-0">';

                            res.hasil.catatan
                                .forEach(function (catatan) {

                                    html +=
                                        '<li>' +
                                        escHtml(catatan) +
                                        '</li>';

                                });

                            html +=
                                '</ul>' +
                                '</div>';

                        }

                    }


                    hasil
                        .removeClass('text-muted')
                        .addClass('text-success')
                        .html(html);


                    tableSiswa.ajax.reload(
                        null,
                        false
                    );

                },


                error: function (xhr) {

                    console.error(
                        'IMPORT ERROR:',
                        xhr.responseText
                    );


                    const message =
                        xhr.responseJSON?.message ||
                        'Gagal melakukan import data.';


                    hasil
                        .removeClass('text-muted')
                        .addClass('text-danger')
                        .html(
                            '<div class="alert alert-danger mb-0">' +
                            '<i class="fa fa-circle-exclamation me-1"></i>' +
                            escHtml(message) +
                            '</div>'
                        );

                },


                complete: function () {

                    button.prop(
                        'disabled',
                        false
                    );

                    button.html(
                        '<i class="fa fa-file-import me-1"></i> Import'
                    );

                }

            });

        }
    );


    /* =========================================================
       MODAL IMPORT RESET
    ========================================================= */

    $('#modalImportSiswa').on(
        'hidden.bs.modal',
        function () {

            $('#formImportSiswa')[0]
                ?.reset();

            $('#hasil-import-siswa')
                .addClass('d-none')
                .html('');

            $('#error_file_import')
                .addClass('d-none')
                .text('');

        }
    );


    /* =========================================================
       GENERATE / SIMPAN SERTIFIKAT
    ========================================================= */

    $('#formGenerateSertifikat').on(
        'submit',
        function (e) {

            e.preventDefault();


            const form =
                $(this);

            const button =
                $('#btnSimpanSertifikat');


            button.prop(
                'disabled',
                true
            );


            button.html(
                '<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...'
            );


            $.ajax({

                url:
                    baseUrl,

                method: 'POST',

                data:
                    form.serialize(),


                success: function (res) {

                    alert(
                        res.message ||
                        'Sertifikat berhasil disimpan.'
                    );


                    $('#modalGenerateSertifikat')
                        .modal('hide');


                    tableSiswa.ajax.reload(
                        null,
                        false
                    );

                },


                error: function (xhr) {

                    console.error(
                        'SERTIFIKAT ERROR:',
                        xhr.responseText
                    );


                    if (
                        xhr.status === 422 &&
                        xhr.responseJSON?.errors
                    ) {

                        const errors =
                            xhr.responseJSON.errors;


                        Object.keys(errors)
                            .forEach(function (field) {

                                $('#error_' + field)
                                    .removeClass('d-none')
                                    .text(
                                        errors[field][0]
                                    );

                            });

                    } else {

                        alert(
                            xhr.responseJSON?.message ||
                            'Gagal menyimpan sertifikat.'
                        );

                    }

                },


                complete: function () {

                    button.prop(
                        'disabled',
                        false
                    );

                    button.html(
                        '<i class="fa fa-floppy-disk me-1"></i> Simpan'
                    );

                }

            });

        }
    );


    /* =========================================================
       BERSIHKAN ERROR FORM
    ========================================================= */

    $('#formGenerateSertifikat input, #formGenerateSertifikat select, #formGenerateSertifikat textarea')
        .on('input change', function () {

            const id =
                $(this).attr('id');

            if (!id) {
                return;
            }

            $('#error_' + id)
                .addClass('d-none')
                .text('');

        });


    /* =========================================================
       MODAL GENERATE RESET
    ========================================================= */

    $('#modalGenerateSertifikat').on(
        'hidden.bs.modal',
        function () {

            $('#formGenerateSertifikat')
                .find('.text-danger')
                .addClass('d-none')
                .text('');

        }
    );

});

</script>

@endpush
