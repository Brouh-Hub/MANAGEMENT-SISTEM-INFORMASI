@extends('layouts.main')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Atur Posisi — {{ $template->nama_template }}</h4>
        <small class="text-muted">Geser kotak teks ke posisi yang sesuai di atas gambar</small>
    </div>
    <a href="{{ route('template-sertifikat.index') }}" class="btn btn-light border">
        <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <div class="card-panel mb-3">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label small fw-semibold mb-0">Ganti Gambar Latar</label>
                <input type="file" id="fileGambarBaru" accept=".jpg,.jpeg,.png" class="form-control form-control-sm" style="max-width: 320px;">
                <button type="button" id="btnUploadGambar" class="btn btn-sm text-white" style="background: var(--brand);">
                    <i class="fa fa-upload me-1"></i> Upload
                </button>
                <small class="text-muted">JPG/PNG, maks 5MB</small>
            </div>
            <small class="text-danger d-none" id="error_fileGambarBaru"></small>
        </div>

        <div class="card-panel">
            <div id="editorStage" style="position: relative; display: inline-block; max-width: 100%; user-select: none;">
                <img id="templateImage" src="/storage/{{ $template->file_path }}" style="max-width: 100%; display: block; border-radius: 8px;">
                {{-- kotak field digenerate oleh JS setelah gambar dimuat --}}
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card-panel">
            <h6 class="fw-bold mb-3">Pengaturan Field</h6>
            <div id="fieldSettings">
                <p class="text-muted small">Klik salah satu kotak di gambar untuk mengatur ukuran, warna, dan perataan teksnya.</p>
            </div>

            <hr>

            <button type="button" id="btnSimpanPosisi" class="btn text-white w-100" style="background: var(--brand);">
                <i class="fa fa-save me-1"></i> Simpan Posisi
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {

    const struktur = @json($struktur);
    const templateId = {{ $template->id }};
    const img = document.getElementById('templateImage');
    const stage = document.getElementById('editorStage');

    let scale = 1;
    let selectedField = null;
    const fieldState = {}; // field_name -> { x, y, font_size, color, align } dalam koordinat ASLI gambar

    struktur.forEach(f => {
        fieldState[f.field] = {
            x: parseFloat(f.x),
            y: parseFloat(f.y),
            font_size: parseFloat(f.font_size),
            color: f.color,
            align: f.align,
            label: f.label || f.field
        };
    });

    function renderBoxes() {
        stage.querySelectorAll('.field-box').forEach(el => el.remove());

        Object.keys(fieldState).forEach(fieldName => {
            const f = fieldState[fieldName];
            const box = document.createElement('div');
            box.className = 'field-box';
            box.dataset.field = fieldName;
            box.textContent = f.label;
            box.style.position = 'absolute';
            box.style.left = (f.x * scale) + 'px';
            box.style.top = (f.y * scale) + 'px';
            box.style.transform = 'translate(-50%, -50%)';
            box.style.padding = '4px 10px';
            box.style.fontSize = Math.max(10, f.font_size * scale) + 'px';
            box.style.color = f.color;
            box.style.background = 'rgba(255,255,255,0.55)';
            box.style.border = '1px dashed #1625C4';
            box.style.borderRadius = '4px';
            box.style.cursor = 'move';
            box.style.whiteSpace = 'nowrap';
            box.style.textAlign = f.align;
            stage.appendChild(box);
        });
    }

    function computeScale() {
        scale = img.clientWidth / img.naturalWidth;
        renderBoxes();
    }

    if (img.complete) {
        computeScale();
    } else {
        img.onload = computeScale;
    }
    window.addEventListener('resize', computeScale);

    // ---- Upload / ganti gambar latar ----
    $('#btnUploadGambar').on('click', function () {
        const fileInput = document.getElementById('fileGambarBaru');
        $('#error_fileGambarBaru').addClass('d-none').text('');

        if (!fileInput.files.length) {
            $('#error_fileGambarBaru').removeClass('d-none').text('Pilih file gambar dulu.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Mengupload...');

        $.ajax({
            url: `/admin/template-sertifikat/${templateId}/gambar`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                // cache-busting supaya gambar baru langsung kelihatan, bukan versi cache lama
                img.src = '/storage/' + res.data.file_path + '?t=' + Date.now();
                fileInput.value = '';
            },
            error: function (xhr) {
                $('#error_fileGambarBaru').removeClass('d-none').text(xhr.responseJSON?.message || 'Gagal upload gambar.');
            },
            complete: function () {
                $('#btnUploadGambar').prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload');
            }
        });
    });

    // ---- Drag logic (mouse & touch via Pointer Events) ----
    let dragging = null;
    let dragOffsetX = 0, dragOffsetY = 0;

    stage.addEventListener('pointerdown', function (e) {
        const box = e.target.closest('.field-box');
        if (!box) return;

        dragging = box;
        const rect = box.getBoundingClientRect();
        dragOffsetX = e.clientX - rect.left;
        dragOffsetY = e.clientY - rect.top;

        selectField(box.dataset.field);
        e.preventDefault();
    });

    document.addEventListener('pointermove', function (e) {
        if (!dragging) return;

        const stageRect = stage.getBoundingClientRect();
        let left = e.clientX - stageRect.left - dragOffsetX + (dragging.offsetWidth / 2);
        let top = e.clientY - stageRect.top - dragOffsetY + (dragging.offsetHeight / 2);

        left = Math.max(0, Math.min(left, stage.clientWidth));
        top = Math.max(0, Math.min(top, stage.clientHeight));

        dragging.style.left = left + 'px';
        dragging.style.top = top + 'px';

        const fieldName = dragging.dataset.field;
        fieldState[fieldName].x = left / scale;
        fieldState[fieldName].y = top / scale;
    });

    document.addEventListener('pointerup', function () {
        dragging = null;
    });

    // ---- Panel pengaturan field terpilih ----
    function selectField(fieldName) {
        selectedField = fieldName;
        const f = fieldState[fieldName];

        stage.querySelectorAll('.field-box').forEach(el => {
            el.style.borderColor = el.dataset.field === fieldName ? '#FF6B00' : '#1625C4';
            el.style.borderWidth = el.dataset.field === fieldName ? '2px' : '1px';
        });

        $('#fieldSettings').html(`
            <div class="mb-2 fw-semibold small">${f.label}</div>
            <div class="mb-2">
                <label class="form-label small">Ukuran Font</label>
                <input type="number" min="6" max="100" id="set_font_size" class="form-control form-control-sm" value="${f.font_size}">
            </div>
            <div class="mb-2">
                <label class="form-label small">Warna</label>
                <input type="color" id="set_color" class="form-control form-control-sm form-control-color" value="${f.color}">
            </div>
            <div class="mb-2">
                <label class="form-label small">Perataan</label>
                <select id="set_align" class="form-select form-select-sm">
                    <option value="left" ${f.align === 'left' ? 'selected' : ''}>Kiri</option>
                    <option value="center" ${f.align === 'center' ? 'selected' : ''}>Tengah</option>
                    <option value="right" ${f.align === 'right' ? 'selected' : ''}>Kanan</option>
                </select>
            </div>
        `);

        $('#set_font_size, #set_color, #set_align').on('input change', function () {
            fieldState[selectedField].font_size = parseFloat($('#set_font_size').val());
            fieldState[selectedField].color = $('#set_color').val();
            fieldState[selectedField].align = $('#set_align').val();
            renderBoxes();
            selectField(selectedField); // re-render panel supaya highlight tetap konsisten
        });
    }

    // ---- Simpan ----
    $('#btnSimpanPosisi').on('click', function () {
        const payload = Object.keys(fieldState).map(fieldName => {
            const f = fieldState[fieldName];
            return {
                field: fieldName,
                label: f.label,
                x: Math.round(f.x),
                y: Math.round(f.y),
                font_size: f.font_size,
                color: f.color,
                align: f.align
            };
        });

        $.ajax({
            url: `/admin/template-sertifikat/${templateId}/posisi`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                struktur_template: payload
            },
            success: function () {
                alert('Posisi berhasil disimpan.');
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Gagal simpan posisi.');
            }
        });
    });

});
</script>
@endpush
