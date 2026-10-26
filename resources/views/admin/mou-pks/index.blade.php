@extends('layouts.main')
@section('content')

<style>
    .mou-ringkas { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:12px; }
    @media (max-width: 767.98px) { .mou-ringkas { grid-template-columns:repeat(2, minmax(0,1fr)); } }
    .mou-ringkas .kotak { background:#fff; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.06);
        cursor:pointer; border:2px solid transparent; transition:border-color .15s; }
    .mou-ringkas .kotak:hover, .mou-ringkas .kotak.aktif { border-color:var(--brand); }
    .mou-ringkas .angka { font-size:24px; font-weight:700; line-height:1.1; }
    .mou-ringkas .label { font-size:13px; color:#6c757d; }
    #detail-berkas { height:520px; }
    @media (max-width: 767.98px) { #detail-berkas { height:60vh; } }
    .detail-mou td { padding:6px 0; vertical-align:top; font-size:14px; }
    .detail-mou td:first-child { color:#6c757d; width:150px; padding-right:12px; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $title }}</h4>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
    </div>
    @if($bolehUbah)
        <button type="button" class="btn btn-primary btn-sm" id="btn-tambah">
            <i class="fa fa-plus"></i> Tambah Dokumen
        </button>
    @endif
</div>

@if($mitra->isEmpty() && $bolehUbah)
    <div class="alert alert-warning small">
        <i class="fa fa-triangle-exclamation"></i>
        Belum ada data mitra. Tambahkan mitra dulu di menu <a href="{{ route('mitra.index') }}">Kerja Sama Industri</a>.
    </div>
@endif

{{-- Ringkasan (klik untuk menyaring tabel) --}}
<div class="mou-ringkas mb-3">
    <div class="kotak aktif" data-status="">
        <div class="angka">{{ $ringkasan['total'] }}</div><div class="label">Semua dokumen</div>
    </div>
    <div class="kotak" data-status="aktif">
        <div class="angka text-success">{{ $ringkasan['aktif'] }}</div><div class="label">Aktif</div>
    </div>
    <div class="kotak" data-status="akan_berakhir">
        <div class="angka text-warning">{{ $ringkasan['akan_berakhir'] }}</div><div class="label">Berakhir ≤ {{ $hariPeringatan }} hari</div>
    </div>
    <div class="kotak" data-status="berakhir">
        <div class="angka text-danger">{{ $ringkasan['berakhir'] }}</div><div class="label">Sudah berakhir</div>
    </div>
</div>

<div class="card-panel">
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <select id="filter-mitra" class="form-select form-select-sm">
                <option value="">Semua mitra</option>
                @foreach($mitra as $m)
                    <option value="{{ $m->id }}">{{ $m->nama_perusahaan }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select id="filter-jenis" class="form-select form-select-sm">
                <option value="">Semua jenis</option>
                <option value="MoU">MoU</option>
                <option value="PKS">PKS</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select id="filter-status" class="form-select form-select-sm">
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="akan_berakhir">Akan berakhir</option>
                <option value="berakhir">Berakhir</option>
                <option value="belum_berlaku">Belum berlaku</option>
            </select>
        </div>
    </div>

    <table class="table align-middle" id="table" width="100%">
        <thead>
            <tr class="text-muted text-uppercase" style="font-size:11px;">
                <th>No</th>
                <th>Mitra</th>
                <th>Jenis</th>
                <th>Nomor Dokumen</th>
                <th>Judul</th>
                <th>Masa Berlaku</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>

{{-- ===================== MODAL TAMBAH / UBAH ===================== --}}
@if($bolehUbah)
<div class="modal fade" id="modal-form" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="form-mou" enctype="multipart/form-data" novalidate class="d-flex flex-column overflow-hidden" style="max-height:100%">
                <div class="modal-header">
                    <h5 class="modal-title" id="judul-form">Tambah Dokumen MoU/PKS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="mou-id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Mitra industri <span class="text-danger">*</span></label>
                            <select name="industri_id" id="f-industri_id" class="form-select">
                                <option value="">-- Pilih mitra --</option>
                                @foreach($mitra as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama_perusahaan }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-msg" id="e-industri_id"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" id="f-jenis" class="form-select">
                                <option value="MoU">MoU (Nota Kesepahaman)</option>
                                <option value="PKS">PKS (Perjanjian Kerja Sama)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <small class="text-danger error-msg" id="e-jenis"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nomor dokumen (sekolah) <span class="text-danger">*</span></label>
                            @include('admin.surat-keluar.partials.input-nomor', [
                                'inputId' => 'f-nomor_dokumen',
                                'name'    => 'nomor_dokumen',
                                'url'     => route('mou-pks.generate-nomor'),
                            ])
                            <small class="text-danger error-msg" id="e-nomor_dokumen"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor dokumen (mitra)</label>
                            <input type="text" name="nomor_dokumen_mitra" id="f-nomor_dokumen_mitra" class="form-control" placeholder="Opsional">
                            <small class="text-danger error-msg" id="e-nomor_dokumen_mitra"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Judul / perihal <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="f-judul" class="form-control" placeholder="Contoh: Kerja Sama Praktik Kerja Lapangan dan Rekrutmen Lulusan">
                            <small class="text-danger error-msg" id="e-judul"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ruang lingkup</label>
                            <textarea name="ruang_lingkup" id="f-ruang_lingkup" rows="3" class="form-control" placeholder="Contoh: PKL siswa, guru tamu dari industri, sinkronisasi kurikulum, rekrutmen lulusan"></textarea>
                            <small class="text-danger error-msg" id="e-ruang_lingkup"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Berlaku mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="f-tanggal_mulai" class="form-control">
                            <small class="text-danger error-msg" id="e-tanggal_mulai"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Berlaku sampai</label>
                            <input type="date" name="tanggal_berakhir" id="f-tanggal_berakhir" class="form-control">
                            <div class="d-flex flex-wrap gap-1 mt-1" id="pintas-masa">
                                <button type="button" class="btn btn-light btn-sm py-0" data-tahun="1">+1 tahun</button>
                                <button type="button" class="btn btn-light btn-sm py-0" data-tahun="2">+2 tahun</button>
                                <button type="button" class="btn btn-light btn-sm py-0" data-tahun="3">+3 tahun</button>
                                <button type="button" class="btn btn-light btn-sm py-0" data-tahun="0">Tanpa batas</button>
                            </div>
                            <small class="text-danger error-msg" id="e-tanggal_berakhir"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Penanda tangan (sekolah)</label>
                            <input type="text" name="penandatangan_sekolah" id="f-penandatangan_sekolah" class="form-control" placeholder="Nama Kepala Sekolah">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Penanda tangan (mitra)</label>
                            <input type="text" name="penandatangan_mitra" id="f-penandatangan_mitra" class="form-control" placeholder="Nama pimpinan mitra">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Berkas dokumen (PDF/JPG/PNG, maks {{ round(config('mou.maks_berkas', 10240) / 1024) }} MB)</label>
                            <input type="file" name="file" id="f-file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-check mt-1 d-none" id="wadah-hapus-file">
                                <input class="form-check-input" type="checkbox" name="hapus_file" value="1" id="f-hapus_file">
                                <label class="form-check-label small" for="f-hapus_file">
                                    Hapus berkas lama (<a href="#" target="_blank" id="link-file-lama">lihat</a>)
                                </label>
                            </div>
                            <small class="text-danger error-msg" id="e-file"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="f-keterangan" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ===================== MODAL DETAIL ===================== --}}
<div class="modal fade" id="modal-detail" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="d-judul"></h5>
                    <small class="text-muted" id="d-mitra"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="mb-2"><span class="badge" id="d-status"></span></div>
                        <table class="w-100 detail-mou table-borderless" id="d-tabel"></table>
                    </div>
                    <div class="col-lg-7">
                        <div id="detail-berkas" class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden"></div>
                        <div class="text-end mt-2">
                            <a href="#" target="_blank" class="btn btn-sm btn-light" id="d-buka"><i class="fa fa-up-right-from-square"></i> Buka berkas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    const baseUrl = @json(url('admin/mou-pks'));
    let mode = 'tambah';

    const table = $('#table').DataTable({
        serverSide: true,
        processing: true,
        order: [],
        ajax: {
            url: baseUrl,
            data: d => {
                d.status = $('#filter-status').val();
                d.jenis = $('#filter-jenis').val();
                d.mitra = $('#filter-mitra').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'mitra', name: 'mitra', orderable: false },
            { data: 'jenis', name: 'jenis' },
            { data: 'nomor_dokumen', name: 'nomor_dokumen' },
            { data: 'judul', name: 'judul' },
            { data: 'masa_berlaku', name: 'tanggal_berakhir', searchable: false },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false },
        ]
    });

    // Filter
    $('#filter-status, #filter-jenis, #filter-mitra').on('change', function () {
        $('.mou-ringkas .kotak').removeClass('aktif')
            .filter(`[data-status="${$('#filter-status').val()}"]`).addClass('aktif');
        table.ajax.reload();
    });
    $('.mou-ringkas .kotak').on('click', function () {
        $('#filter-status').val($(this).data('status')).trigger('change');
    });

    // ---------- Tambah / ubah ----------
    const $modal = $('#modal-form');

    function bersihkanError() { $('#form-mou .error-msg').text(''); }

    $('#btn-tambah').on('click', function () {
        mode = 'tambah';
        $('#form-mou')[0].reset();
        $('#mou-id').val('');
        $('#wadah-hapus-file').addClass('d-none');
        $('#judul-form').text('Tambah Dokumen MoU/PKS');
        bersihkanError();
        $modal.modal('show');
    });

    $('#table tbody').on('click', '.btn-update', function () {
        const d = $(this).data('detail');
        mode = 'ubah';
        $('#form-mou')[0].reset();
        bersihkanError();
        $('#mou-id').val(d.id);
        ['industri_id', 'jenis', 'nomor_dokumen', 'nomor_dokumen_mitra', 'judul', 'ruang_lingkup',
         'tanggal_mulai', 'tanggal_berakhir', 'penandatangan_sekolah', 'penandatangan_mitra', 'keterangan']
            .forEach(k => $(`#f-${k}`).val(d[k] ?? ''));
        $('#wadah-hapus-file').toggleClass('d-none', !d.file_url);
        $('#link-file-lama').attr('href', d.file_url || '#');
        $('#judul-form').text('Ubah Dokumen MoU/PKS');
        $modal.modal('show');
    });

    // Tombol cepat masa berlaku
    $('#pintas-masa').on('click', 'button', function () {
        const tahun = parseInt($(this).data('tahun'), 10);
        if (!tahun) { $('#f-tanggal_berakhir').val(''); return; }
        const mulai = $('#f-tanggal_mulai').val();
        if (!mulai) { notif('Isi tanggal mulai berlaku dulu.', 'info'); return; }
        const t = new Date(mulai + 'T00:00:00');
        t.setFullYear(t.getFullYear() + tahun);
        t.setDate(t.getDate() - 1);
        $('#f-tanggal_berakhir').val(t.toISOString().slice(0, 10));
    });

    $('#form-mou').on('submit', function (e) {
        e.preventDefault();
        bersihkanError();

        const fd = new FormData(this);
        let url = baseUrl;
        if (mode === 'ubah') {
            url = `${baseUrl}/${$('#mou-id').val()}`;
            fd.append('_method', 'PUT');
        }

        const $btn = $('#btn-simpan');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({ url, method: 'POST', data: fd, processData: false, contentType: false })
            .done(res => {
                $modal.modal('hide');
                notif(res.message, 'sukses');
                setTimeout(() => location.reload(), 600); // supaya angka ringkasan ikut diperbarui
            })
            .fail(xhr => {
                const errors = xhr.responseJSON?.errors ?? {};
                $.each(errors, (f, m) => $(`#e-${f}`).text(m[0]));
                if (!Object.keys(errors).length) notif(xhr.responseJSON?.message ?? 'Gagal menyimpan dokumen.', 'gagal');
            })
            .always(() => $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan'));
    });

    // ---------- Hapus ----------
    $('#table tbody').on('click', '.btn-delete', async function () {
        if (!await konfirmasi('Hapus dokumen MoU/PKS ini? Berkas yang diunggah juga ikut terhapus.')) return;

        $.post(`${baseUrl}/${$(this).data('id')}`, { _method: 'DELETE' })
            .done(res => { notif(res.message, 'sukses'); setTimeout(() => location.reload(), 600); })
            .fail(xhr => notif(xhr.responseJSON?.message ?? 'Gagal menghapus dokumen.', 'gagal'));
    });

    // ---------- Detail ----------
    $('#table tbody').on('click', '.btn-detail', function () {
        const d = $(this).data('detail');

        $('#d-judul').text(d.judul);
        $('#d-mitra').text(d.mitra);
        $('#d-status').attr('class', `badge bg-${d.status_warna}`).text(d.status_label);

        const baris = [
            ['Jenis', d.jenis],
            ['Nomor (sekolah)', d.nomor_dokumen],
            ['Nomor (mitra)', d.nomor_dokumen_mitra],
            ['Masa berlaku', d.masa_berlaku],
            ['Ruang lingkup', d.ruang_lingkup],
            ['Penanda tangan sekolah', d.penandatangan_sekolah],
            ['Penanda tangan mitra', d.penandatangan_mitra],
            ['Keterangan', d.keterangan],
        ];
        const $t = $('#d-tabel').empty();
        baris.forEach(([label, nilai]) => $t.append(
            $('<tr>').append($('<td>').text(label), $('<td>').css('white-space', 'pre-line').text(nilai || '-'))
        ));

        const $berkas = $('#detail-berkas').empty();
        $('#d-buka').toggle(!!d.file_url).attr('href', d.file_url || '#');
        if (!d.file_url) {
            $berkas.html('<div class="text-center text-muted"><i class="fa fa-file-circle-exclamation fa-2x mb-2"></i><div>Berkas belum diunggah.</div></div>');
        } else if (/\.(jpe?g|png)$/i.test(d.file_url)) {
            $berkas.html($('<img>', { src: d.file_url, style: 'max-width:100%;max-height:100%;object-fit:contain;' }));
        } else {
            $berkas.html($('<iframe>', { src: d.file_url, style: 'width:100%;height:100%;border:0;', title: 'Berkas MoU/PKS' }));
        }

        $('#modal-detail').modal('show');
    });

    $('#modal-detail').on('hidden.bs.modal', () => $('#detail-berkas').empty());
});
</script>
@endpush
