@extends('layouts.main')

@push('styles')
<style>
    .template-editor-page {
        background: #f4f5f7;
        padding: 20px;
        border-radius: 12px;
    }

    .editor-toolbar {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .editor-toolbar .btn {
        min-width: 36px;
    }

    .paper-wrapper {
        overflow-x: auto;
        padding: 30px 10px;
    }

    .paper-a4 {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,.12);
        padding: 20mm 20mm 20mm 20mm;
        color: #000;
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.4;
        outline: none;
    }

    .paper-a4:focus {
        box-shadow: 0 4px 25px rgba(13,110,253,.18);
    }

    .variable-box {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
    }

    .variable-btn {
        margin: 3px 2px;
    }

    @media (max-width: 1000px) {
        .paper-a4 {
            width: 100%;
            min-height: 900px;
            padding: 40px;
        }
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $title }}</h4>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
    </div>

    <a href="{{ route('surat-keluar.index') }}"
       class="btn btn-light btn-sm">
        <i class="fa fa-arrow-left"></i>
        Kembali
    </a>
</div>

<div class="template-editor-page">

    <div class="card-panel mb-3">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nama Template</label>

                <input type="text"
                       id="nama-template"
                       class="form-control"
                       value="{{ old('nama_template', $template?->nama_template) }}"
                       placeholder="Contoh: Surat Undangan Orang Tua">
            </div>

            <div class="col-md-6">
                <label class="form-label">Jenis Surat</label>

                <input type="text"
                       id="jenis-surat"
                       class="form-control"
                       value="{{ old('jenis_surat', $template?->jenis_surat) }}"
                       placeholder="Contoh: Surat Undangan">
            </div>

        </div>
    </div>


    <div class="variable-box mb-3">

        <div class="fw-semibold mb-2">
            Variabel Surat
        </div>

        <div class="mb-2">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary variable-btn"
                    data-variable="nomor_surat">
                Nomor Surat
            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-secondary variable-btn"
                    data-variable="tanggal_surat">
                Tanggal Surat
            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-secondary variable-btn"
                    data-variable="perihal">
                Perihal
            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-secondary variable-btn"
                    data-variable="tujuan">
                Tujuan
            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-secondary variable-btn"
                    data-variable="alamat_tujuan">
                Alamat Tujuan
            </button>
        </div>

        <div class="input-group"
             style="max-width: 450px;">

            <input type="text"
                   id="custom-variable"
                   class="form-control form-control-sm"
                   placeholder="Contoh: waktu_acara">

            <button type="button"
                    id="btn-custom-variable"
                    class="btn btn-outline-primary btn-sm">
                + Tambah Variabel
            </button>

        </div>

        <small class="text-muted">
            Contoh hasil:
            @{{ nomor_surat }},
            @{{ tanggal_surat }},
            @{{ waktu_acara }}.
        </small>

    </div>


    <div class="editor-toolbar">

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="bold"
                title="Bold">
            <b>B</b>
        </button>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="italic"
                title="Italic">
            <i>I</i>
        </button>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="underline"
                title="Underline">
            <u>U</u>
        </button>

        <span class="border-start mx-1"></span>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="justifyLeft"
                title="Rata kiri">
            <i class="fa fa-align-left"></i>
        </button>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="justifyCenter"
                title="Rata tengah">
            <i class="fa fa-align-center"></i>
        </button>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="justifyRight"
                title="Rata kanan">
            <i class="fa fa-align-right"></i>
        </button>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="justifyFull"
                title="Justify">
            <i class="fa fa-align-justify"></i>
        </button>

        <span class="border-start mx-1"></span>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="insertOrderedList"
                title="Numbering">
            <i class="fa fa-list-ol"></i>
        </button>

        <button type="button"
                class="btn btn-light btn-sm editor-command"
                data-command="insertUnorderedList"
                title="Bullet">
            <i class="fa fa-list-ul"></i>
        </button>

        <select id="font-size"
                class="form-select form-select-sm"
                style="width:110px;">

            <option value="">Ukuran</option>
            <option value="2">10 pt</option>
            <option value="3">12 pt</option>
            <option value="4">14 pt</option>
            <option value="5">18 pt</option>
            <option value="6">24 pt</option>
        </select>

    </div>


    <div class="paper-wrapper">

        <div id="paper-editor"
             class="paper-a4"
             contenteditable="true">

            @if($template)
                {!! $template->isi_template !!}
            @else

                <div style="text-align:center;">
                    <strong>PEMERINTAH PROVINSI KEPULAUAN RIAU</strong><br>
                    <strong>DINAS PENDIDIKAN</strong><br>
                    <strong style="font-size:16pt;">
                        SMK NEGERI 4 TANJUNGPINANG
                    </strong>
                </div>

                <hr style="border:0; border-top:3px solid #000; margin-top:10px;">

                <p style="text-align:right;">
                    Tanjungpinang, {{'{'}}{{'{'}}tanggal_surat{{'}'}}{{'}'}}
                </p>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:90px;">Nomor</td>
                        <td style="width:15px;">:</td>
                        <td>{{'{'}}{{'{'}}nomor_surat{{'}'}}{{'}'}}</td>
                    </tr>

                    <tr>
                        <td>Sifat</td>
                        <td>:</td>
                        <td>Biasa</td>
                    </tr>

                    <tr>
                        <td>Lampiran</td>
                        <td>:</td>
                        <td>-</td>
                    </tr>

                    <tr>
                        <td>Hal</td>
                        <td>:</td>
                        <td>{{'{'}}{{'{'}}perihal{{'}'}}{{'}'}}</td>
                    </tr>
                </table>

                <br>

                <p>
                    Yth. {{'{'}}{{'{'}}tujuan{{'}'}}{{'}'}}<br>
                    di<br>
                    {{'{'}}{{'{'}}alamat_tujuan{{'}'}}{{'}'}}
                </p>

                <br>

                <p style="text-align:justify;">
                    Tulis isi surat di bagian ini. Teks ini bebas diubah oleh
                    Tata Usaha sesuai kebutuhan surat.
                </p>

                <br><br>

                <div style="width:45%; margin-left:auto;">
                    <p>Kepala Sekolah,</p>

                    <br><br><br>

                    <p>
                        <strong>Nama Kepala Sekolah</strong><br>
                        NIP. ....................................
                    </p>
                </div>

            @endif

        </div>

    </div>


    <div class="d-flex justify-content-between align-items-center mt-3">

        <div class="form-check form-switch">
            <input class="form-check-input"
                   type="checkbox"
                   id="is-active"
                   @checked(!$template || $template->is_active)>

            <label class="form-check-label"
                   for="is-active">
                Template aktif
            </label>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('surat-keluar.index') }}"
               class="btn btn-light">
                Batal
            </a>

            <button type="button"
                    id="btn-save-template"
                    class="btn btn-primary">
                <i class="fa fa-save"></i>
                Simpan Template
            </button>
        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>
$(document).ready(function () {

    "use strict";

    const isEdit = @json((bool) $template);

    const saveUrl = isEdit
        ? @json($template ? route('surat-keluar.template.update', $template->id) : '')
        : @json(route('surat-keluar.template.store'));

    const editor = document.getElementById('paper-editor');


    $('.editor-command').on('click', function () {

        editor.focus();

        document.execCommand(
            $(this).data('command'),
            false,
            null
        );

    });


    $('#font-size').on('change', function () {

        if (!this.value) {
            return;
        }

        editor.focus();

        document.execCommand(
            'fontSize',
            false,
            this.value
        );

        this.value = '';

    });


    function insertVariable(variable) {

        variable = variable
            .trim()
            .replace(/\s+/g, '_')
            .replace(/[^A-Za-z0-9_]/g, '');

        if (!variable) {
            return;
        }

        editor.focus();

        const placeholder =
            '{' + '{' + variable + '}' + '}';

        document.execCommand(
            'insertText',
            false,
            placeholder
        );

    }


    $('.variable-btn').on('click', function () {

        insertVariable(
            $(this).data('variable')
        );

    });


    $('#btn-custom-variable').on('click', function () {

        const input =
            $('#custom-variable');

        insertVariable(
            input.val()
        );

        input.val('');

    });


    $('#btn-save-template').on('click', function () {

        const namaTemplate =
            $('#nama-template').val().trim();

        const jenisSurat =
            $('#jenis-surat').val().trim();

        const isiTemplate =
            $('#paper-editor').html().trim();

        if (!namaTemplate) {
            alert('Nama template wajib diisi.');
            $('#nama-template').focus();
            return;
        }

        if (!isiTemplate) {
            alert('Isi template tidak boleh kosong.');
            return;
        }

        const data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            nama_template: namaTemplate,
            jenis_surat: jenisSurat,
            isi_template: isiTemplate,
            is_active: $('#is-active').is(':checked') ? 1 : 0
        };

        if (isEdit) {
            data._method = 'PUT';
        }

        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: data,

            beforeSend: function () {
                $('#btn-save-template')
                    .prop('disabled', true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },

            success: function (response) {

                alert(response.message);

                window.location.href =
                    @json(route('surat-keluar.index'));
            },

            error: function (xhr) {

                if (
                    xhr.status === 422 &&
                    xhr.responseJSON?.errors
                ) {
                    const errors =
                        xhr.responseJSON.errors;

                    alert(
                        Object.values(errors)[0][0]
                    );

                    return;
                }

                alert(
                    xhr.responseJSON?.message ??
                    'Gagal menyimpan template.'
                );
            },

            complete: function () {

                $('#btn-save-template')
                    .prop('disabled', false)
                    .html('<i class="fa fa-save"></i> Simpan Template');
            }
        });

    });

});
</script>
@endpush
