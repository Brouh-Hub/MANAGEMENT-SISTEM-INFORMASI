@extends('layouts.main')

@push('styles')
<style>
    .surat-create-page {
        background: #f4f5f7;
        padding: 20px;
        border-radius: 12px;
    }

    .input-panel {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 18px;
    }

    .paper-wrapper {
        overflow-x: auto;
        padding: 10px;
    }

    .paper-a4 {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .12);
        padding: 20mm;
        color: #000;
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.5;
    }

    .paper-a4 img {
        max-width: 100%;
    }

    .preview-empty {
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        text-align: center;
    }

    .result-panel {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 15px;
        margin-top: 15px;
    }

    @media (max-width: 1200px) {
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
        <h4 class="fw-bold mb-1">
            {{ $title }}
        </h4>

        <p class="text-muted mb-0">
            {{ $subtitle }}
        </p>
    </div>

    <a href="{{ route('surat-keluar.index') }}"
       class="btn btn-light btn-sm">
        <i class="fa fa-arrow-left me-1"></i>
        Kembali
    </a>
</div>


<div class="surat-create-page">

    <div class="row g-3">

        {{-- =====================================================
             FORM INPUT
        ====================================================== --}}
        <div class="col-xl-4">

            <div class="input-panel">

                <form id="form-surat-keluar">
                    @csrf

                    {{-- Hasil HTML surat --}}
                    <input
                        type="hidden"
                        name="isi_surat"
                        id="isi_surat"
                    >

                    <h6 class="fw-bold mb-3">
                        Data Surat Undangan
                    </h6>


                    {{-- TEMPLATE --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Template
                        </label>

                        <select
                            name="template_surat_id"
                            id="template_surat_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Pilih Template Surat --
                            </option>

                            @foreach($templates as $template)

                                <option value="{{ $template->id }}">
                                    {{ $template->nama_template }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- NOMOR --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Nomor
                        </label>

                        {{-- Nomor dibuat sistem: klik Generate + password TU --}}
                        @include('admin.surat-keluar.partials.input-nomor', ['inputId' => 'nomor_surat'])

                    </div>


                    {{-- TANGGAL --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Tanggal
                        </label>

                        <input
                            type="date"
                            name="tanggal_surat"
                            id="tanggal_surat"
                            class="form-control"
                            required
                        >

                    </div>


                    {{-- SIFAT --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Sifat
                        </label>

                        <input
                            type="text"
                            name="template_data[sifat]"
                            id="sifat"
                            class="form-control"
                            placeholder="Contoh: Biasa"
                        >

                    </div>


                    {{-- LAMPIRAN --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Lampiran
                        </label>

                        <input
                            type="text"
                            name="template_data[lampiran]"
                            id="lampiran"
                            class="form-control"
                            placeholder="Contoh: 1 berkas"
                        >

                    </div>


                    {{-- HAL --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Hal
                        </label>

                        <input
                            type="text"
                            name="perihal"
                            id="perihal"
                            class="form-control"
                            placeholder="Contoh: Undangan Rapat"
                            required
                        >

                    </div>


                    {{-- TUJUAN --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Tujuan
                        </label>

                        <input
                            type="text"
                            name="tujuan"
                            id="tujuan"
                            class="form-control"
                            placeholder="Nama / Instansi Tujuan"
                            required
                        >

                    </div>


                    {{-- ALAMAT TUJUAN --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Alamat Tujuan
                        </label>

                        <textarea
                            name="alamat_tujuan"
                            id="alamat_tujuan"
                            class="form-control"
                            rows="2"
                            placeholder="Alamat tujuan"
                        ></textarea>

                    </div>


                    {{-- ISI SURAT --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Isi Surat
                        </label>

                        <textarea
                            name="template_data[isi_surat]"
                            id="isi_surat_input"
                            class="form-control"
                            rows="7"
                            placeholder="Tuliskan isi surat..."
                        ></textarea>

                    </div>


                    {{-- HARI / TANGGAL ACARA --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Hari/Tanggal Acara
                        </label>

                        <input
                            type="text"
                            name="template_data[hari_tanggal_acara]"
                            id="hari_tanggal_acara"
                            class="form-control"
                            placeholder="Contoh: Senin, 12 Oktober 2026"
                        >

                    </div>


                    {{-- PUKUL --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Pukul
                        </label>

                        <input
                            type="text"
                            name="template_data[pukul]"
                            id="pukul"
                            class="form-control"
                            placeholder="Contoh: 09.00 WIB"
                        >

                    </div>


                    {{-- TEMPAT --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Tempat
                        </label>

                        <input
                            type="text"
                            name="template_data[tempat]"
                            id="tempat"
                            class="form-control"
                            placeholder="Tempat pelaksanaan"
                        >

                    </div>


                    {{-- ISI PENUTUP --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Isi Penutup
                        </label>

                        <textarea
                            name="template_data[isi_penutup]"
                            id="isi_penutup"
                            class="form-control"
                            rows="5"
                            placeholder="Tuliskan isi penutup surat..."
                        ></textarea>

                    </div>


                    {{-- FIELD TAMBAHAN TEMPLATE --}}
                    <div
                        id="template-fields-wrapper"
                        class="d-none"
                    >

                        <hr>

                        <h6 class="fw-bold mb-3">
                            Data Tambahan Template
                        </h6>

                        <div id="template-fields"></div>

                    </div>


                    {{-- BUTTON --}}
                    <div class="d-flex gap-2 mt-4">

                        <a
                            href="{{ route('surat-keluar.index') }}"
                            class="btn btn-light flex-fill"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            id="btn-save"
                            class="btn btn-primary flex-fill"
                        >
                            <i class="fa fa-save me-1"></i>
                            Simpan & Generate PDF
                        </button>

                    </div>

                </form>

            </div>


            {{-- HASIL PDF --}}
            <div
                id="result-panel"
                class="result-panel d-none"
            >

                <div class="fw-bold mb-2">
                    PDF Berhasil Dibuat
                </div>

                <div class="d-flex gap-2 flex-wrap">

                    <a
                        href="#"
                        target="_blank"
                        id="btn-preview-pdf"
                        class="btn btn-outline-primary btn-sm"
                    >
                        <i class="fa fa-eye me-1"></i>
                        Preview
                    </a>

                    <a
                        href="#"
                        id="btn-download-pdf"
                        class="btn btn-outline-success btn-sm"
                    >
                        <i class="fa fa-download me-1"></i>
                        Unduh PDF
                    </a>

                    <button
                        type="button"
                        id="btn-print-pdf"
                        class="btn btn-outline-dark btn-sm"
                    >
                        <i class="fa fa-print me-1"></i>
                        Cetak
                    </button>

                </div>

            </div>

        </div>


        {{-- =====================================================
             PREVIEW A4
        ====================================================== --}}
        <div class="col-xl-8">

            <div class="d-flex justify-content-between align-items-center mb-2">

                <div>

                    <div class="fw-bold">
                        Preview Surat
                    </div>

                    <small class="text-muted">
                        Preview berubah otomatis sesuai data yang diisi.
                    </small>

                </div>

                <span class="badge bg-light text-dark border">
                    A4
                </span>

            </div>


            <div class="paper-wrapper">

                <div
                    id="paper-preview"
                    class="paper-a4"
                >

                    <div class="preview-empty">
                        Pilih Template Surat terlebih dahulu.
                    </div>

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


    const templates = @json($templates);


    const baseKeys = [
        'nomor_surat',
        'tanggal_surat',
        'sifat',
        'lampiran',
        'perihal',
        'tujuan',
        'alamat_tujuan',
        'isi_surat',
        'hari_tanggal_acara',
        'pukul',
        'tempat',
        'isi_penutup'
    ];


    function escapeHtml(value) {

        return $('<div>')
            .text(value ?? '')
            .html();

    }


    function escapeRegex(value) {

        return value.replace(
            /[.*+?^${}()|[\]\\]/g,
            '\\$&'
        );

    }


    function selectedTemplate() {

        const id = $('#template_surat_id').val();

        return templates.find(function (template) {

            return String(template.id) === String(id);

        }) ?? null;

    }


    function buatInputField(field) {

        const key = field.key;

        const label =
            escapeHtml(field.label ?? key);

        const required =
            field.is_required
                ? 'required'
                : '';

        const tandaWajib =
            field.is_required
                ? '<span class="text-danger">*</span>'
                : '';

        const defaultValue =
            field.default_value ?? '';

        let input = '';


        if (field.type === 'textarea') {

            input = `
                <textarea
                    name="template_data[${key}]"
                    class="form-control template-input"
                    rows="3"
                    ${required}>${escapeHtml(defaultValue)}</textarea>
            `;

        } else {

            const type = [
                'text',
                'date',
                'time',
                'number'
            ].includes(field.type)
                ? field.type
                : 'text';

            input = `
                <input
                    type="${type}"
                    name="template_data[${key}]"
                    class="form-control template-input"
                    value="${escapeHtml(defaultValue)}"
                    ${required}>
            `;

        }


        return `
            <div class="mb-3">

                <label class="form-label">
                    ${label} ${tandaWajib}
                </label>

                ${input}

            </div>
        `;

    }


    function renderTemplateFields() {

        const template =
            selectedTemplate();

        const wrapper =
            $('#template-fields-wrapper');

        const container =
            $('#template-fields');


        container.empty();


        if (!template) {

            wrapper.addClass('d-none');

            return;

        }


        const fields =
            template.fields ?? [];


        const tambahan =
            fields.filter(function (field) {

                return !baseKeys.includes(field.key);

            });


        if (!tambahan.length) {

            wrapper.addClass('d-none');

            return;

        }


        tambahan.forEach(function (field) {

            container.append(
                buatInputField(field)
            );

        });


        wrapper.removeClass('d-none');

    }


    function ambilSemuaNilai() {

        const values = {

            nomor_surat:
                $('#nomor_surat').val() ?? '',

            tanggal_surat:
                formatTanggalIndonesia($('#tanggal_surat').val() ?? ''),

            // Kop & data Kepala Sekolah dari menu Pengaturan Surat
            ...@json(\App\Models\PengaturanSurat::ambil()->placeholder()),

            sifat:
                $('#sifat').val() || 'Biasa',

            lampiran:
                $('#lampiran').val() || '-',

            perihal:
                $('#perihal').val() ?? '',

            tujuan:
                $('#tujuan').val() ?? '',

            alamat_tujuan:
                $('#alamat_tujuan').val() ?? '',

            isi_surat:
                $('#isi_surat_input').val() ?? '',

            hari_tanggal_acara:
                $('#hari_tanggal_acara').val() ?? '',

            pukul:
                $('#pukul').val() ?? '',

            tempat:
                $('#tempat').val() ?? '',

            isi_penutup:
                $('#isi_penutup').val() ?? ''

        };


        $('.template-input').each(function () {

            const name =
                $(this).attr('name');

            const match =
                name.match(
                    /^template_data\[(.+)\]$/
                );


            if (!match) {
                return;
            }


            const nilai = $(this).val() ?? '';

            // Jangan timpa nilai bawaan (mis. Sifat "Biasa") dengan isian kosong
            if (nilai === '' && values[match[1]]) {
                return;
            }

            values[match[1]] = nilai;

        });


        return values;

    }


    // 2026-10-12 -> 12 Oktober 2026 (sama seperti di PDF)
    function formatTanggalIndonesia(value) {
        if (!value) return '';

        const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const p = value.split('-');

        if (p.length !== 3) return value;

        return parseInt(p[2], 10) + ' ' + bulan[parseInt(p[1], 10) - 1] + ' ' + p[0];
    }


    function formatTanggal(value) {

        if (!value) {
            return '';
        }

        const parts =
            value.split('-');

        if (parts.length !== 3) {
            return value;
        }

        return (
            parts[2] +
            '-' +
            parts[1] +
            '-' +
            parts[0]
        );

    }


    function renderPreview() {

    const template = selectedTemplate();
    const paper = $('#paper-preview');

    if (!template) {
        paper.html(`
            <div class="preview-empty">
                Pilih Template Surat terlebih dahulu.
            </div>
        `);
        return;
    }

    // Ambil HTML template dari database
    let html = template.isi_template ?? '';

    // Jika HTML masih berupa entity (&lt;div&gt;), decode kembali
    const decoder = document.createElement('textarea');
    decoder.innerHTML = html;
    html = decoder.value;

    // Ambil semua nilai dari form
    const values = ambilSemuaNilai();

    /*
     * Ganti semua placeholder:
     *
     * @{{nomor_surat}}
     * @{{ nomor_surat }}
     * @{{*nomor_surat*}}
     * @{{ *nomor_surat* }}
     */
    html = html.replace(
        /\{\{\s*\*?\s*([A-Za-z0-9_]+)\s*\*?\s*\}\}/g,
        function (match, key) {

            // Kop surat sudah berupa HTML
            if (key === 'kop_surat') {
                return values.kop_surat ?? '';
            }

            if (
                Object.prototype.hasOwnProperty.call(values, key)
            ) {
                return escapeHtml(values[key] ?? '')
                    .replace(/\n/g, '<br>');
            }

            // Placeholder yang tidak ada nilainya dihapus
            return '';
        }
    );

    /*
     * Masukkan sebagai HTML.
     * Jadi <div>, <table>, <img>, dll dibaca sebagai HTML,
     * bukan ditampilkan sebagai teks.
     */
$('#isi_surat').val(html);
paper[0].innerHTML = html;
}

    $('#template_surat_id').on(
        'change',
        function () {

            renderTemplateFields();

            renderPreview();

        }
    );


    $('#form-surat-keluar').on(
        'input change',
        'input, textarea, select',
        function () {

            renderPreview();

        }
    );


    /*
     * SIMPAN SURAT
     */
    $('#form-surat-keluar').on(
        'submit',
        function (e) {

            e.preventDefault();


            const form =
                this;

            const btn =
                $('#btn-save');


            /*
             * Pastikan isi_surat
             * sudah terbaru.
             */
            renderPreview();


            const data =
                new FormData(form);


            $.ajax({

                url:
                    @json(route('surat-keluar.store')),

                method:
                    'POST',

                data:
                    data,

                processData:
                    false,

                contentType:
                    false,


                beforeSend:
                    function () {

                        btn
                            .prop('disabled', true)
                            .html(
                                '<i class="fa fa-spinner fa-spin me-1"></i>' +
                                ' Menyimpan...'
                            );

                    },


                success:
                    function (response) {

                        const suratId =
                            response?.data?.id;


                        if (!suratId) {

                            alert(
                                'Surat berhasil disimpan, tetapi ID surat tidak ditemukan.'
                            );

                            return;

                        }


                        /*
                         * Setelah surat tersimpan,
                         * langsung generate PDF.
                         */
                        const generateUrl =
                            @json(route(
                                'surat-keluar.generate-pdf',
                                ['id' => 0]
                            ))
                            .replace(
                                '/0/generate-pdf',
                                '/' + suratId + '/generate-pdf'
                            );


                        btn.html(
                            '<i class="fa fa-spinner fa-spin me-1"></i>' +
                            ' Membuat PDF...'
                        );


                        $.ajax({

                            url:
                                generateUrl,

                            method:
                                'POST',

                            data: {
                                _token:
                                    @json(csrf_token())
                            },


                            success:
                                function (pdfResponse) {

                                    if (
                                        !pdfResponse.preview_url ||
                                        !pdfResponse.download_url
                                    ) {

                                        alert(
                                            'PDF berhasil dibuat, tetapi link PDF tidak ditemukan.'
                                        );

                                        return;

                                    }


                                    $('#btn-preview-pdf')
                                        .attr(
                                            'href',
                                            pdfResponse.preview_url
                                        );


                                    $('#btn-download-pdf')
                                        .attr(
                                            'href',
                                            pdfResponse.download_url
                                        );


                                    $('#btn-print-pdf')
                                        .data(
                                            'url',
                                            pdfResponse.preview_url
                                        );


                                    $('#result-panel')
                                        .removeClass('d-none');


                                    btn
                                        .prop('disabled', false)
                                        .html(
                                            '<i class="fa fa-check me-1"></i>' +
                                            ' PDF Berhasil Dibuat'
                                        );


                                    alert(
                                        pdfResponse.message ??
                                        'PDF surat berhasil dibuat.'
                                    );

                                },


                            error:
                                function (xhr) {

                                    alert(
                                        xhr.responseJSON?.message ??
                                        'Gagal membuat PDF.'
                                    );

                                    btn
                                        .prop('disabled', false)
                                        .html(
                                            '<i class="fa fa-save me-1"></i>' +
                                            ' Simpan & Generate PDF'
                                        );

                                }

                        });

                    },


                error:
                    function (xhr) {

                        if (
                            xhr.status === 422 &&
                            xhr.responseJSON?.errors
                        ) {

                            const errors =
                                xhr.responseJSON.errors;

                            const first =
                                Object.values(errors)[0];

                            alert(
                                first?.[0] ??
                                'Periksa kembali data surat.'
                            );

                            return;
                        }


                        alert(
                            xhr.responseJSON?.message ??
                            'Gagal menyimpan surat.'
                        );

                    },


                complete:
                    function () {

                        if (
                            btn.text().includes('Menyimpan')
                        ) {

                            btn
                                .prop('disabled', false)
                                .html(
                                    '<i class="fa fa-save me-1"></i>' +
                                    ' Simpan & Generate PDF'
                                );

                        }

                    }

            });

        }
    );


    /*
     * CETAK PDF
     */
    $('#btn-print-pdf').on(
        'click',
        function () {

            const url =
                $(this).data('url');

            if (!url) {
                return;
            }

            window.open(
                url,
                '_blank'
            );

        }
    );


    /*
     * TANGGAL DEFAULT HARI INI
     */
    const today =
        new Date()
            .toISOString()
            .split('T')[0];


    $('#tanggal_surat')
        .val(today);

});
</script>
@endpush