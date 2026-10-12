@push('styles')

<style>

/* =========================================================
   MODAL CREATE
   FORM KIRI + PREVIEW A4 KANAN
   ========================================================= */

#modal-create .modal-dialog,
#modal-create .modal-dialog.modal-xl {
    width: 96vw !important;
    max-width: 1600px !important;
    margin: 10px auto !important;
}

#modal-create .modal-content {
    width: 100%;
    height: calc(100vh - 20px);
    max-height: calc(100vh - 20px);
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

#modal-create #form-create {
    height: 100%;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

/* =========================================================
   HEADER
   ========================================================= */

#modal-create .modal-header {
    flex: 0 0 auto;
    padding: 18px 22px;
    background: #fff;
    border-bottom: 1px solid #dee2e6;
}

/* =========================================================
   BODY
   ========================================================= */

#modal-create .modal-body {
    flex: 1 1 auto;
    min-height: 0;

    display: grid !important;

    grid-template-columns:
        minmax(520px, 1fr)
        minmax(520px, 1fr);

    grid-template-rows:
        auto
        minmax(0, 1fr);

    gap: 14px;

    padding: 14px !important;

    background: #f4f5f7;

    overflow: hidden !important;
}

/* =========================================================
   FORM KIRI
   ========================================================= */

#modal-create .editor-card {
    grid-column: 1;
    grid-row: 1 / span 2;

    min-width: 0;
    min-height: 0;

    height: 100%;

    background: #fff;

    border: 1px solid #dee2e6;
    border-radius: 10px;

    padding: 18px;

    overflow-y: auto;
    overflow-x: hidden;
}

/* =========================================================
   TITLE
   ========================================================= */

#modal-create .section-title {
    font-size: 13px;
    font-weight: 700;

    text-transform: uppercase;

    color: #6c757d;

    margin-bottom: 14px;
}

/* =========================================================
   PREVIEW TOOLBAR
   ========================================================= */

#modal-create .preview-toolbar {
    grid-column: 2;
    grid-row: 1;

    min-width: 0;

    margin: 0 !important;

    background: #fff;

    border: 1px solid #dee2e6;
    border-radius: 8px;

    padding: 10px 14px;
}

/* =========================================================
   PREVIEW AREA
   ========================================================= */

#modal-create .paper-wrapper {
    grid-column: 2;
    grid-row: 2;

    min-width: 0;
    min-height: 0;

    width: 100%;
    height: 100%;

    overflow: auto !important;

    padding: 18px 20px 30px !important;

    display: flex;
    justify-content: center;
    align-items: flex-start;

    background: #e9ecef;

    border-radius: 8px;
}

/* =========================================================
   KERTAS A4
   ========================================================= */

#modal-create .paper-a4 {
    flex: 0 0 auto;

    width: 210mm !important;
    min-width: 210mm;

    min-height: 297mm;

    margin: 0 auto !important;

    padding: 20mm;

    box-sizing: border-box;

    background: #fff;

    color: #000;

    font-family:
        "Times New Roman",
        Times,
        serif;

    font-size: 12pt;

    line-height: 1.4;

    box-shadow:
        0 4px 20px rgba(0, 0, 0, .15);

    transform-origin: top center;
}

/* =========================================================
   PREVIEW KOSONG
   ========================================================= */

#modal-create .preview-empty {
    width: 100%;
    min-height: 500px;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #999;

    text-align: center;
}

/* =========================================================
   DEFAULT HTML TEMPLATE
   ========================================================= */

#modal-create .paper-a4 img {
    max-width: 100%;
}

#modal-create .paper-a4 table {
    border-collapse: collapse;
}

#modal-create .paper-a4 p {
    margin-top: 0;
}

/* =========================================================
   FOOTER
   ========================================================= */

#modal-create .modal-footer {
    flex: 0 0 auto;

    background: #fff;

    padding: 14px 22px;

    border-top: 1px solid #dee2e6;

    box-shadow:
        0 -4px 14px rgba(0, 0, 0, .08);

    z-index: 30;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1200px) {

    #modal-create .modal-body {
        grid-template-columns:
            minmax(430px, 1fr)
            minmax(430px, 1fr);
    }

}

@media (max-width: 1000px) {

    #modal-create .modal-dialog,
    #modal-create .modal-dialog.modal-xl {
        width: 98vw !important;
        max-width: 98vw !important;
    }

    #modal-create .modal-body {
        display: grid !important;

        grid-template-columns: 1fr;

        grid-template-rows:
            auto
            auto
            minmax(600px, 1fr);

        overflow-y: auto !important;
    }

    #modal-create .editor-card {
        grid-column: 1;
        grid-row: 1;

        height: auto;
        max-height: none;

        overflow: visible;
    }

    #modal-create .preview-toolbar {
        grid-column: 1;
        grid-row: 2;
    }

    #modal-create .paper-wrapper {
        grid-column: 1;
        grid-row: 3;

        min-height: 600px;
    }

}

</style>

@endpush


<div
    class="modal fade"
    id="modal-create"
    tabindex="-1"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-xl modal-dialog-centered"
    >

        <div class="modal-content">

            <form id="form-create">

                @csrf


                {{-- =====================================================
                     HIDDEN ISI SURAT
                     ===================================================== --}}

                <input
                    type="hidden"
                    name="isi_surat"
                    id="create-isi-surat"
                >


                {{-- =====================================================
                     HEADER
                     ===================================================== --}}

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title fw-bold mb-1">
                            Tambah Surat Keluar
                        </h5>

                        <small class="text-muted">
                            Pilih template, isi data surat, lalu lihat hasil surat pada lembar A4.
                        </small>

                    </div>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                {{-- =====================================================
                     BODY
                     ===================================================== --}}

                <div class="modal-body">


                    {{-- =================================================
                         FORM KIRI
                         ================================================= --}}

                    <div class="editor-card">

                        <div class="section-title">
                            Data Surat
                        </div>


                        @php

                            $standardFieldKeys = [

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

                                'isi_penutup',

                            ];

                        @endphp


                        <div class="row g-3">


                            {{-- =========================================
                                 TEMPLATE
                                 ========================================= --}}

                            <div class="col-md-6">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Template Surat
                                </label>


                                <select
                                    name="template_surat_id"
                                    id="create-template-surat"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Template Surat --
                                    </option>


                                    @foreach($templates as $template)

                                        @php

                                            $dynamicFields =
                                                $template->fields
                                                    ->reject(function ($field) use ($standardFieldKeys) {

                                                        return in_array(
                                                            $field->key,
                                                            $standardFieldKeys,
                                                            true
                                                        );

                                                    })
                                                    ->values();

                                        @endphp


                                        <option
                                            value="{{ $template->id }}"
                                            data-fields='@json($dynamicFields)'
                                            data-jenis="{{ $template->jenis_surat }}"
                                        >
                                            {{ $template->nama_template }}
                                        </option>

                                    @endforeach

                                </select>


                                <small
                                    class="text-danger error-msg"
                                    id="create-error-template_surat_id"
                                ></small>

                            </div>


                            {{-- =========================================
                                 JENIS SURAT
                                 ========================================= --}}

                            <div class="col-md-6">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Jenis Surat
                                </label>


                                <input
                                    type="text"
                                    id="create-jenis-surat"
                                    class="form-control"
                                    placeholder="Terisi otomatis setelah memilih template"
                                    readonly
                                >

                            </div>


                            {{-- =========================================
                                 NOMOR
                                 ========================================= --}}

                            <div class="col-md-4">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Nomor
                                </label>


                                <input
                                    type="text"
                                    name="nomor_surat"
                                    id="create-nomor_surat"
                                    class="form-control"
                                    placeholder="Contoh: B/005/667/I-SMKN4/2025"
                                    required
                                >


                                <small
                                    class="text-danger error-msg"
                                    id="create-error-nomor_surat"
                                ></small>

                            </div>


                            {{-- =========================================
                                 TANGGAL
                                 ========================================= --}}

                            <div class="col-md-4">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Tanggal
                                </label>


                                <input
                                    type="date"
                                    name="tanggal_surat"
                                    id="create-tanggal_surat"
                                    class="form-control"
                                    required
                                >


                                <small
                                    class="text-danger error-msg"
                                    id="create-error-tanggal_surat"
                                ></small>

                            </div>


                            {{-- =========================================
                                 SIFAT
                                 ========================================= --}}

                            <div class="col-md-2">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Sifat
                                </label>


                                <input
                                    type="text"
                                    name="sifat"
                                    id="create-sifat"
                                    class="form-control"
                                    value="Biasa"
                                >

                            </div>


                            {{-- =========================================
                                 LAMPIRAN
                                 ========================================= --}}

                            <div class="col-md-2">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Lampiran
                                </label>


                                <input
                                    type="text"
                                    name="lampiran"
                                    id="create-lampiran"
                                    class="form-control"
                                    value="-"
                                >

                            </div>


                            {{-- =========================================
                                 PERIHAL
                                 ========================================= --}}

                            <div class="col-md-4">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Perihal
                                </label>


                                <input
                                    type="text"
                                    name="perihal"
                                    id="create-perihal"
                                    class="form-control"
                                    placeholder="Contoh: Undangan"
                                    required
                                >


                                <small
                                    class="text-danger error-msg"
                                    id="create-error-perihal"
                                ></small>

                            </div>


                            {{-- =========================================
                                 TUJUAN
                                 ========================================= --}}

                            <div class="col-md-8">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Tujuan
                                </label>


                                <input
                                    type="text"
                                    name="tujuan"
                                    id="create-tujuan"
                                    class="form-control"
                                    placeholder="Contoh: Bapak/Ibu orang tua/wali murid kelas X"
                                    required
                                >


                                <small
                                    class="text-danger error-msg"
                                    id="create-error-tujuan"
                                ></small>

                            </div>


                            {{-- =========================================
                                 ALAMAT TUJUAN
                                 ========================================= --}}

                            <div class="col-12">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Alamat Tujuan
                                </label>


                                <textarea
                                    name="alamat_tujuan"
                                    id="create-alamat_tujuan"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Contoh: Tanjungpinang"
                                ></textarea>

                            </div>


                            {{-- =========================================
                                 ISI SURAT
                                 ========================================= --}}

                            <div class="col-12">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Isi Surat
                                </label>


                                <textarea
                                    name="isi_surat_input"
                                    id="create-isi-surat-input"
                                    class="form-control"
                                    rows="7"
                                    style="
                                        min-height: 180px;
                                        resize: vertical;
                                    "
                                    placeholder="Ketik isi surat di sini..."
                                >Sehubungan telah dilaksanakannya Penilaian Akhir Semester (PAS) Ganjil Tahun Pelajaran 2025/2026 bagi siswa kelas X, dengan ini kami mengundang Bapak/Ibu untuk pengambilan rapor ananda pada</textarea>

                            </div>


                            {{-- =========================================
                                 HARI TANGGAL ACARA
                                 ========================================= --}}

                            <div class="col-md-4">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Hari, Tanggal Acara
                                </label>


                                <input
                                    type="text"
                                    name="hari_tanggal_acara"
                                    id="create-hari_tanggal_acara"
                                    class="form-control"
                                    placeholder="Contoh: Jumat, 19 Desember 2025"
                                >

                            </div>


                            {{-- =========================================
                                 PUKUL
                                 ========================================= --}}

                            <div class="col-md-4">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Pukul
                                </label>


                                <input
                                    type="text"
                                    name="pukul"
                                    id="create-pukul"
                                    class="form-control"
                                    placeholder="Contoh: 08.00 s.d. 09.30"
                                >

                            </div>


                            {{-- =========================================
                                 TEMPAT
                                 ========================================= --}}

                            <div class="col-md-4">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Tempat
                                </label>


                                <input
                                    type="text"
                                    name="tempat"
                                    id="create-tempat"
                                    class="form-control"
                                    placeholder="Contoh: SMK Negeri 4 Tanjungpinang"
                                >

                            </div>


                            {{-- =========================================
                                 ISI PENUTUP
                                 ========================================= --}}

                            <div class="col-12">

                                <label
                                    class="form-label fw-semibold"
                                >
                                    Isi Penutup
                                </label>


                                <textarea
                                    name="isi_penutup"
                                    id="create-isi-penutup-input"
                                    class="form-control"
                                    rows="4"
                                    style="
                                        min-height: 120px;
                                        resize: vertical;
                                    "
                                    placeholder="Ketik penutup surat di sini..."
                                >Atas perhatian dan kehadiran Bapak/Ibu diucapkan terima kasih.</textarea>

                            </div>

                        </div>


                        {{-- =================================================
                             INPUT KHUSUS TEMPLATE
                             ================================================= --}}

                        <div
                            id="create-template-fields-wrapper"
                            class="mt-4 d-none"
                        >

                            <hr>


                            <div class="section-title">
                                Data Sesuai Template
                            </div>


                            <div
                                id="create-template-fields"
                                class="row g-3"
                            ></div>

                        </div>


                        {{-- =================================================
                             FIELD CUSTOM LAMA
                             ================================================= --}}

                        @if($customs->count())

                            <div class="mt-4">

                                <hr>


                                <div class="section-title">
                                    Data Tambahan
                                </div>


                                <div class="row g-3">

                                    @foreach($customs as $cf)

                                        <div class="col-md-6">

                                            <label class="form-label">
                                                {{ $cf->label }}
                                            </label>


                                            @if($cf->type === 'textarea')

                                                <textarea
                                                    name="custom[{{ $cf->key }}]"
                                                    data-custom-key="{{ $cf->key }}"
                                                    class="form-control"
                                                    rows="2"
                                                ></textarea>

                                            @else

                                                <input
                                                    type="{{ $cf->type }}"
                                                    name="custom[{{ $cf->key }}]"
                                                    data-custom-key="{{ $cf->key }}"
                                                    class="form-control"
                                                >

                                            @endif


                                            <small
                                                class="text-danger error-msg"
                                                id="create-error-custom-{{ $cf->key }}"
                                            ></small>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endif

                    </div>


                    {{-- =================================================
                         PREVIEW TOOLBAR
                         ================================================= --}}

                    <div class="preview-toolbar">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <div>

                                <i class="fa fa-file-lines me-1"></i>

                                <span class="fw-semibold">
                                    Preview Surat
                                </span>

                                <small class="text-muted ms-2">
                                    A4 Portrait
                                </small>

                            </div>


                            <span
                                class="badge bg-light text-dark border"
                            >
                                Preview Otomatis
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                         PREVIEW A4
                         ================================================= --}}

                    <div class="paper-wrapper">

                        <div
                            id="create-paper-preview"
                            class="paper-a4"
                        >

                            <div class="preview-empty">

                                <div>

                                    <i
                                        class="fa fa-file-lines fa-2x mb-3"
                                    ></i>


                                    <div>
                                        Pilih Template Surat terlebih dahulu.
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     FOOTER
                     ===================================================== --}}

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa fa-save me-1"></i>

                        Simpan Surat

                    </button>


                    <button
                        type="button"
                        id="btn-save-download"
                        class="btn btn-success"
                    >

                        <i class="fa fa-download me-1"></i>

                        Simpan & Unduh PDF

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


@push('scripts')

<script>

$(document).ready(function () {

    "use strict";


    /* =========================================================
       ELEMENT
       ========================================================= */

    const $form =
        $('#form-create');

    const $template =
        $('#create-template-surat');

    const $preview =
        $('#create-paper-preview');

    const $paperWrapper =
        $('#modal-create .paper-wrapper');

    const $jenis =
        $('#create-jenis-surat');

    const $isiSurat =
        $('#create-isi-surat');


    /* =========================================================
       TEMPLATE DARI DATABASE
       ========================================================= */

    const templateStore =
        @json($templates->pluck('isi_template', 'id'));


    /* =========================================================
       ESCAPE INPUT
       ========================================================= */

    function escapePreview(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value === null ||
            value === undefined
                ? ''
                : String(value);

        return div.innerHTML
            .replace(/\n/g, '<br>');

    }


    /* =========================================================
       DECODE TEMPLATE HTML
       ========================================================= */

    function decodeTemplateHtml(html) {

        let result =
            String(html ?? '');


        for (let i = 0; i < 3; i++) {

            if (
                !/&lt;\/?[a-z][^>]*&gt;/i.test(result) &&
                !/&amp;lt;/i.test(result)
            ) {

                break;

            }


            const textarea =
                document.createElement('textarea');


            textarea.innerHTML =
                result;


            const decoded =
                textarea.value;


            if (
                decoded === result
            ) {

                break;

            }


            result =
                decoded;

        }


        return result;

    }


    /* =========================================================
       AMBIL NILAI FORM
       ========================================================= */

    function getValues() {

        const values = {};


        if (!$form.length) {

            return values;

        }


        new FormData(
            $form[0]
        ).forEach(
            function (value, name) {


                /* ---------------------------------------------
                   HIDDEN HTML PREVIEW JANGAN DIBACA
                   --------------------------------------------- */

                if (
                    name === 'isi_surat'
                ) {

                    return;

                }


                /* ---------------------------------------------
                   FIELD TEMPLATE DINAMIS
                   --------------------------------------------- */

                if (
                    name.startsWith(
                        'template_data['
                    )
                ) {

                    const key =
                        name
                            .replace(
                                'template_data[',
                                ''
                            )
                            .replace(
                                ']',
                                ''
                            );


                    values[key] =
                        value;


                    return;

                }


                /* ---------------------------------------------
                   FIELD CUSTOM
                   --------------------------------------------- */

                if (
                    name.startsWith(
                        'custom['
                    )
                ) {

                    const key =
                        name
                            .replace(
                                'custom[',
                                ''
                            )
                            .replace(
                                ']',
                                ''
                            );


                    values[key] =
                        value;


                    return;

                }


                values[name] =
                    value;

            }
        );


        /* =====================================================
           FIELD KHUSUS SURAT
           ===================================================== */

        values.sifat =
            $('#create-sifat').val() || '';


        values.lampiran =
            $('#create-lampiran').val() || '';


        values.isi_surat =
            $('#create-isi-surat-input').val() || '';


        values.hari_tanggal_acara =
            $('#create-hari_tanggal_acara').val() || '';


        values.pukul =
            $('#create-pukul').val() || '';


        values.tempat =
            $('#create-tempat').val() || '';


        values.isi_penutup =
            $('#create-isi-penutup-input').val() || '';


        /* =====================================================
           FORMAT TANGGAL INDONESIA
           ===================================================== */

        const tanggal =
            $('#create-tanggal_surat').val() || '';


        if (tanggal) {

            const parts =
                tanggal.split('-');


            if (
                parts.length === 3
            ) {

                const tahun =
                    parts[0];

                const bulan =
                    parseInt(
                        parts[1],
                        10
                    );

                const hari =
                    parseInt(
                        parts[2],
                        10
                    );


                const namaBulan = [

                    '',

                    'Januari',

                    'Februari',

                    'Maret',

                    'April',

                    'Mei',

                    'Juni',

                    'Juli',

                    'Agustus',

                    'September',

                    'Oktober',

                    'November',

                    'Desember'

                ];


                values.tanggal_surat =
                    `${hari} ${namaBulan[bulan]} ${tahun}`;

            }

        }


        return values;

    }


    /* =========================================================
       FIT A4 KE PANEL PREVIEW
       ========================================================= */

    function fitPreview() {

        if (
            !$preview.length ||
            !$paperWrapper.length
        ) {

            return;

        }


        /* Kembalikan zoom ke 1
           supaya ukuran asli bisa dihitung. */

        $preview.css(
            'zoom',
            1
        );


        const wrapperWidth =
            $paperWrapper.innerWidth() -
            40;


        const paperWidth =
            $preview.outerWidth();


        if (
            !paperWidth ||
            !wrapperWidth
        ) {

            return;

        }


        let scale =
            wrapperWidth /
            paperWidth;


        /* Jangan membesarkan A4
           melebihi ukuran normal. */

        scale =
            Math.min(
                scale,
                1
            );


        /* Batas bawah supaya
           preview tidak terlalu kecil. */

        scale =
            Math.max(
                scale,
                0.55
            );


        $preview.css(
            'zoom',
            scale
        );

    }


    /* =========================================================
       STYLE ISI SURAT
       ========================================================= */

    function stylePreviewParagraphs() {

        const $bodyField =
            $preview.find(
                '[data-preview-field="isi_surat"]'
            );


        $bodyField.each(
            function () {

                const $field =
                    $(this);


                const $container =
                    $field.closest(
                        'p, div'
                    ).first();


                if (
                    $container.length
                ) {

                    $container.css({

                        'text-align':
                            'justify',

                        'text-indent':
                            '1.2cm',

                        'margin-left':
                            '0',

                        'margin-right':
                            '0',

                        'margin-bottom':
                            '22px',

                        'line-height':
                            '1.5'

                    });

                }


                $field.contents()
                    .unwrap();

            }
        );


        /* =====================================================
           STYLE PENUTUP
           ===================================================== */

        const $closingField =
            $preview.find(
                '[data-preview-field="isi_penutup"]'
            );


        $closingField.each(
            function () {

                const $field =
                    $(this);


                const $container =
                    $field.closest(
                        'p, div'
                    ).first();


                if (
                    $container.length
                ) {

                    $container.css({

                        'text-align':
                            'justify',

                        'margin-left':
                            '0',

                        'margin-right':
                            '0',

                        'margin-bottom':
                            '26px',

                        'line-height':
                            '1.5'

                    });

                }


                $field.contents()
                    .unwrap();

            }
        );

    }


    /* =========================================================
       RENDER PREVIEW
       ========================================================= */

    function renderPreview() {

        const templateId =
            String(
                $template.val() || ''
            );


        /* =====================================================
           BELUM PILIH TEMPLATE
           ===================================================== */

        if (!templateId) {

            $jenis.val('');
            $isiSurat.val('');


            $preview.html(`

                <div class="preview-empty">

                    <div>

                        <i
                            class="fa fa-file-lines fa-2x mb-3"
                        ></i>

                        <div>
                            Pilih Template Surat terlebih dahulu.
                        </div>

                    </div>

                </div>

            `);


            $preview.css(
                'zoom',
                1
            );


            return;

        }


        /* =====================================================
           JENIS SURAT
           ===================================================== */

        const option =
            $template.find(
                'option:selected'
            );


        $jenis.val(
            option.attr(
                'data-jenis'
            ) || ''
        );


        /* =====================================================
           TEMPLATE HTML
           ===================================================== */

        let html =
            templateStore[templateId] ?? '';


        if (!html) {

            $preview.html(`

                <div
                    class="preview-empty text-danger"
                >

                    <div>
                        Isi template surat tidak ditemukan.
                    </div>

                </div>

            `);


            $preview.css(
                'zoom',
                1
            );


            return;

        }


        /* =====================================================
           DECODE HTML
           ===================================================== */

        html =
            decodeTemplateHtml(
                html
            );


        /* =====================================================
           NILAI FORM
           ===================================================== */

        const values =
            getValues();


        /* =====================================================
           GANTI PLACEHOLDER
           ===================================================== */

        const placeholderRegex =
            /\{\{\s*\*?\s*([A-Za-z0-9_]+)\s*\*?\s*\}\}/g;


        html =
            html.replace(
                placeholderRegex,
                function (
                    match,
                    key
                ) {


                    if (
                        !Object.prototype.hasOwnProperty.call(
                            values,
                            key
                        )
                    ) {

                        return '';

                    }


                    const value =
                        values[key] ?? '';


                    /* -----------------------------------------
                       ISI SURAT
                       ----------------------------------------- */

                    if (
                        key === 'isi_surat'
                    ) {

                        return `

                            <span
                                data-preview-field="isi_surat"
                            >
                                ${escapePreview(value)}
                            </span>

                        `;

                    }


                    /* -----------------------------------------
                       ISI PENUTUP
                       ----------------------------------------- */

                    if (
                        key === 'isi_penutup'
                    ) {

                        return `

                            <span
                                data-preview-field="isi_penutup"
                            >
                                ${escapePreview(value)}
                            </span>

                        `;

                    }


                    return escapePreview(
                        value
                    );

                }
            );


        /* =====================================================
           MASUKKAN KE A4
           ===================================================== */

        $preview[0].innerHTML =
            html;


        /* =====================================================
           STYLE PARAGRAF
           ===================================================== */

        stylePreviewParagraphs();


        /* =====================================================
           SIMPAN HTML HASIL PREVIEW
           ===================================================== */

        $isiSurat.val(
            $preview[0].innerHTML
        );


        /* =====================================================
           FIT A4
           ===================================================== */

        setTimeout(
            function () {

                fitPreview();

            },
            20
        );

    }


    /* =========================================================
       TEMPLATE BERUBAH
       ========================================================= */

    $template.on(
        'change',
        function () {

            setTimeout(
                function () {

                    renderPreview();

                },
                150
            );

        }
    );


    /* =========================================================
       INPUT BERUBAH
       ========================================================= */

    $form.on(
        'input change',
        'input, textarea, select',
        function () {

            setTimeout(
                function () {

                    renderPreview();

                },
                30
            );

        }
    );


    /* =========================================================
       MODAL DIBUKA
       ========================================================= */

    $('#modal-create').on(
        'shown.bs.modal',
        function () {

            const $tanggal =
                $('#create-tanggal_surat');


            if (
                !$tanggal.val()
            ) {

                const now =
                    new Date();


                const local =
                    new Date(
                        now.getTime() -
                        now.getTimezoneOffset() *
                        60000
                    )
                    .toISOString()
                    .split('T')[0];


                $tanggal.val(
                    local
                );

            }


            setTimeout(
                function () {

                    renderPreview();

                    fitPreview();

                },
                100
            );

        }
    );


    /* =========================================================
       RESIZE WINDOW
       ========================================================= */

    $(window).on(
        'resize',
        function () {

            fitPreview();

        }
    );


    /* =========================================================
       SEBELUM SUBMIT
       ========================================================= */

    $form.on(
        'submit',
        function () {

            renderPreview();


            $isiSurat.val(
                $preview[0].innerHTML
            );

        }
    );


    /* =========================================================
       SIMPAN & DOWNLOAD PDF
       ========================================================= */

    $('#btn-save-download').on(
        'click',
        function () {

            renderPreview();


            $isiSurat.val(
                $preview[0].innerHTML
            );

        }
    );


    /* =========================================================
       RESET KETIKA MODAL DITUTUP
       ========================================================= */

    $('#modal-create').on(
        'hidden.bs.modal',
        function () {

            $jenis.val('');


            $preview.html(`

                <div class="preview-empty">

                    <div>

                        <i
                            class="fa fa-file-lines fa-2x mb-3"
                        ></i>

                        <div>
                            Pilih Template Surat terlebih dahulu.
                        </div>

                    </div>

                </div>

            `);


            $preview.css(
                'zoom',
                1
            );


            $isiSurat.val('');

        }
    );

});

</script>

@endpush