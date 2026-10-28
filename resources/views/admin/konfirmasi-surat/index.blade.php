@extends('layouts.main')
@section('content')

<style>
    .stat-konfirmasi { display:flex; align-items:center; gap:12px; }
    .stat-konfirmasi .ikon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; }
    .stat-konfirmasi .angka { font-size:22px; font-weight:700; line-height:1; }
    .tab-konfirmasi .nav-link { color:#6c757d; font-weight:600; font-size:14px; border:0; border-bottom:2px solid transparent; border-radius:0; padding:10px 4px; margin-right:22px; }
    .tab-konfirmasi .nav-link.active { color:#1624C4; border-bottom-color:#1624C4; background:transparent; }
    .tab-konfirmasi .jumlah { background:#EEF0FD; color:#1624C4; border-radius:20px; padding:1px 8px; font-size:12px; margin-left:4px; }
    .tabel-konfirmasi td, .tabel-konfirmasi th { font-size:13px; }
    .chip-alasan { border:1px solid #dee2e6; background:#fff; border-radius:20px; padding:3px 10px; font-size:12px; margin:0 6px 6px 0; }
    .chip-alasan:hover { border-color:#dc3545; color:#dc3545; }
    #periksa-preview { height:540px; }
    @media (max-width: 991px) { #periksa-preview { height:380px; } }
</style>

<div class="mb-3">
    <h3 class="fw-bold mb-1">{{ $title }}</h3>
    <p class="text-muted mb-0">{{ $subtitle }}</p>
</div>

@unless($adaKepsek)
    <div class="alert alert-warning small">
        <i class="fa fa-triangle-exclamation"></i>
        Belum ada akun dengan jabatan <b>Kepala Sekolah</b>. Surat masuk yang disetujui belum bisa diteruskan untuk disposisi.
    </div>
@endunless

{{-- ===================== RINGKASAN ===================== --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card-panel stat-konfirmasi">
            <div class="ikon" style="background:#EEF0FD; color:#1624C4;"><i class="fa fa-inbox"></i></div>
            <div><div class="angka">{{ $suratMasuk->count() }}</div><small class="text-muted">Surat masuk menunggu</small></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-panel stat-konfirmasi">
            <div class="ikon" style="background:#FFF4E5; color:#C26A00;"><i class="fa fa-paper-plane"></i></div>
            <div><div class="angka">{{ $suratKeluar->count() }}</div><small class="text-muted">Surat keluar menunggu</small></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-panel stat-konfirmasi">
            <div class="ikon" style="background:#FDECEC; color:#B02A37;"><i class="fa fa-rotate-left"></i></div>
            <div>
                <div class="angka">{{ \App\Models\SuratMasuk::where('status_konfirmasi', 'dikembalikan')->count() + \App\Models\SuratKeluar::where('status', 'dikembalikan')->count() }}</div>
                <small class="text-muted">Masih diperbaiki TU</small>
            </div>
        </div>
    </div>
</div>

<div class="card-panel">
    <ul class="nav tab-konfirmasi border-bottom mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-masuk" type="button">
            Surat Masuk <span class="jumlah">{{ $suratMasuk->count() }}</span></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-keluar" type="button">
            Surat Keluar <span class="jumlah">{{ $suratKeluar->count() }}</span></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-riwayat" type="button">
            Riwayat</button></li>
    </ul>

    <div class="tab-content">

        {{-- ===================== SURAT MASUK ===================== --}}
        <div class="tab-pane fade show active" id="tab-masuk">
            <div class="table-responsive">
                <table class="table align-middle tabel-konfirmasi mb-0">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:11px;">
                            <th>No</th><th>No. Agenda</th><th>Nomor Surat</th><th>Pengirim</th><th>Perihal</th><th>Tgl Terima</th><th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratMasuk as $i => $s)
                            @php
                                $data = [
                                    'jenis'   => 'masuk',
                                    'id'      => $s->id,
                                    'judul'   => $s->perihal,
                                    'file'    => $s->file_path ? route('berkas', $s->file_path) : null,
                                    'baris'   => [
                                        'No. Agenda'      => $s->no_agenda,
                                        'Nomor Surat'     => $s->nomor_surat,
                                        'Perihal'         => $s->perihal,
                                        'Pengirim'        => $s->pengirim,
                                        'Alamat Pengirim' => $s->alamat_asal_pengirim ?: '-',
                                        'Tanggal Surat'   => \Carbon\Carbon::parse($s->tanggal_surat)->format('d-m-Y'),
                                        'Tanggal Terima'  => \Carbon\Carbon::parse($s->tanggal_terima)->format('d-m-Y'),
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $s->no_agenda }}</td>
                                <td>{{ $s->nomor_surat }}</td>
                                <td>{{ $s->pengirim }}</td>
                                <td>{{ $s->perihal }}</td>
                                <td>{{ \Carbon\Carbon::parse($s->tanggal_terima)->format('d-m-Y') }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary btn-periksa" data-surat='@json($data)'>
                                        <i class="fa fa-magnifying-glass"></i> Periksa
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fa fa-circle-check text-success"></i> Tidak ada surat masuk yang menunggu konfirmasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===================== SURAT KELUAR ===================== --}}
        <div class="tab-pane fade" id="tab-keluar">
            <div class="table-responsive">
                <table class="table align-middle tabel-konfirmasi mb-0">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:11px;">
                            <th>No</th><th>Nomor Surat</th><th>Perihal</th><th>Tujuan</th><th>Tgl Surat</th><th>Dibuat oleh</th><th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratKeluar as $i => $s)
                            @php
                                $data = [
                                    'jenis'   => 'keluar',
                                    'id'      => $s->id,
                                    'judul'   => $s->perihal,
                                    'file'    => $s->file_path ? route('surat-keluar.preview', $s->id) . '?v=' . optional($s->updated_at)->timestamp : null,
                                    'pdf'     => true,
                                    'baris'   => [
                                        'Nomor Surat'   => $s->nomor_surat ?: '-',
                                        'Perihal'       => $s->perihal,
                                        'Tujuan'        => $s->tujuan ?: '-',
                                        'Alamat Tujuan' => $s->alamat_tujuan ?: '-',
                                        'Tanggal Surat' => \Carbon\Carbon::parse($s->tanggal_surat)->format('d-m-Y'),
                                        'Keterangan'    => $s->keterangan ?: '-',
                                        'Dibuat oleh'   => $s->pembuat->name ?? '-',
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $s->nomor_surat ?: '-' }}</td>
                                <td>{{ $s->perihal }}</td>
                                <td>{{ $s->tujuan ?: '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($s->tanggal_surat)->format('d-m-Y') }}</td>
                                <td>{{ $s->pembuat->name ?? '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary btn-periksa" data-surat='@json($data)'>
                                        <i class="fa fa-magnifying-glass"></i> Periksa
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fa fa-circle-check text-success"></i> Tidak ada surat keluar yang menunggu konfirmasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===================== RIWAYAT ===================== --}}
        <div class="tab-pane fade" id="tab-riwayat">
            <div class="table-responsive">
                <table class="table align-middle tabel-konfirmasi mb-0">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:11px;">
                            <th>Waktu</th><th>Jenis</th><th>Nomor</th><th>Perihal</th><th>Keputusan</th><th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                            <tr>
                                <td class="text-nowrap">{{ $r['waktu']->format('d-m-Y H:i') }}</td>
                                <td><span class="badge {{ $r['jenis'] === 'Masuk' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis' }}">{{ $r['jenis'] }}</span></td>
                                <td>{{ $r['nomor'] ?: '-' }}</td>
                                <td>{{ $r['perihal'] }}</td>
                                <td>
                                    @if($r['hasil'] === 'dikembalikan')
                                        <span class="text-danger"><i class="fa fa-rotate-left"></i> Dikembalikan</span>
                                        <div class="small text-muted">{{ $r['alasan'] }}</div>
                                    @else
                                        <span class="text-success"><i class="fa fa-circle-check"></i> Disetujui</span>
                                    @endif
                                </td>
                                <td>{{ $r['oleh'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada riwayat konfirmasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL PERIKSA ===================== --}}
<div class="modal fade" id="modal-periksa" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Periksa Surat <span class="badge ms-1" id="periksa-jenis"></span></h5>
                    <small class="text-muted" id="periksa-judul"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div id="periksa-preview" class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden"></div>
                        <div class="text-end mt-2">
                            <a href="#" target="_blank" id="periksa-buka" class="btn btn-sm btn-light"><i class="fa fa-up-right-from-square"></i> Buka di tab baru</a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <table class="table table-borderless table-sm" style="font-size:14px;" id="periksa-data"></table>

                        {{-- Form kembalikan --}}
                        <div id="box-kembalikan" class="border border-danger-subtle rounded-3 p-3" style="display:none;">
                            <label class="form-label fw-semibold small mb-2">Alasan dikembalikan ke TU</label>
                            <div class="mb-1">
                                @foreach($alasanKembali as $a)
                                    <button type="button" class="chip-alasan">{{ $a }}</button>
                                @endforeach
                            </div>
                            <textarea id="input-alasan" class="form-control" rows="3" maxlength="1000" placeholder="Jelaskan apa yang perlu diperbaiki TU..."></textarea>
                            <small class="text-danger" id="error-alasan"></small>
                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-light" id="btn-batal-kembalikan">Batal</button>
                                <button type="button" class="btn btn-sm btn-danger" id="btn-kirim-kembalikan"><i class="fa fa-rotate-left"></i> Kembalikan ke TU</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="footer-keputusan">
                <button type="button" class="btn btn-outline-danger me-auto" id="btn-kembalikan"><i class="fa fa-rotate-left"></i> Kembalikan</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="btn-setujui"><i class="fa fa-check"></i> Setujui &amp; Teruskan ke Kepala Sekolah</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    let surat = null;
    const $modal = $('#modal-periksa');

    // Pilih tab dari ?tab=keluar
    const tab = new URLSearchParams(location.search).get('tab');
    if (tab) $(`[data-bs-target="#tab-${tab}"]`).trigger('click');

    function tampilkanPreview(url, pdf) {
        const $box = $('#periksa-preview').empty();
        $('#periksa-buka').toggle(!!url).attr('href', url || '#');

        if (!url) {
            $box.html('<div class="text-center text-muted"><i class="fa fa-file-circle-exclamation fa-2x mb-2"></i><div>File surat tidak diunggah.</div></div>');
            return;
        }

        const ext = url.split('?')[0].split('.').pop().toLowerCase();

        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            $box.html($('<img>', { src: url, style: 'max-width:100%;max-height:100%;object-fit:contain;' }));
        } else if (pdf || ext === 'pdf') {
            $box.html($('<iframe>', { src: url, style: 'width:100%;height:100%;border:0;', title: 'Preview surat' }));
        } else {
            $box.html('<div class="text-center text-muted"><i class="fa fa-file-word fa-2x mb-2"></i><div>File tidak bisa dipratinjau. Gunakan tombol buka.</div></div>');
        }
    }

    $(document).on('click', '.btn-periksa', function () {
        surat = $(this).data('surat');

        $('#periksa-jenis').text(surat.jenis === 'masuk' ? 'Masuk' : 'Keluar')
            .attr('class', 'badge ms-1 ' + (surat.jenis === 'masuk' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis'));
        $('#periksa-judul').text(surat.judul);

        const $t = $('#periksa-data').empty();
        $.each(surat.baris, function (label, nilai) {
            $t.append($('<tr>').append(
                $('<td class="text-muted" style="width:130px;">').text(label),
                $('<td>').text(nilai ?? '-')
            ));
        });

        $('#box-kembalikan').hide();
        $('#input-alasan').val('');
        $('#error-alasan').text('');
        $('#footer-keputusan').show();

        tampilkanPreview(surat.file, surat.pdf);
        $modal.modal('show');
    });

    $modal.on('hidden.bs.modal', () => $('#periksa-preview').empty());

    $('#btn-kembalikan').on('click', function () {
        $('#footer-keputusan').hide();
        $('#box-kembalikan').show();
        $('#input-alasan').trigger('focus');
    });

    $('#btn-batal-kembalikan').on('click', function () {
        $('#box-kembalikan').hide();
        $('#footer-keputusan').show();
    });

    // Klik alasan cepat -> ditambahkan ke textarea
    $('.chip-alasan').on('click', function () {
        const teks = $(this).text().trim();
        const $a = $('#input-alasan');
        const isi = $a.val().trim();
        $a.val(isi ? `${isi}. ${teks}` : teks).trigger('focus');
    });

    function kirim(keputusan, $btn) {
        const html = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');
        $('#error-alasan').text('');

        $.ajax({
            url: `{{ url('admin/konfirmasi-surat') }}/${surat.jenis}/${surat.id}`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', keputusan: keputusan, alasan: $('#input-alasan').val() },
            success: function (res) {
                $modal.modal('hide');
                alert(res.message);
                location.href = `{{ route('konfirmasi-surat.index') }}?tab=${surat.jenis}`;
            },
            error: function (xhr) {
                const err = xhr.responseJSON?.errors?.alasan;
                if (err) {
                    $('#error-alasan').text(err[0]);
                } else {
                    alert(xhr.responseJSON?.message ?? 'Gagal menyimpan keputusan.');
                }
                $btn.prop('disabled', false).html(html);
            }
        });
    }

    $('#btn-setujui').on('click', async function () {
        if (!await konfirmasi('Setujui surat ini dan teruskan ke Kepala Sekolah?')) return;
        kirim('setujui', $(this));
    });

    $('#btn-kirim-kembalikan').on('click', function () {
        if (!$('#input-alasan').val().trim()) {
            $('#error-alasan').text('Tuliskan alasan surat dikembalikan.');
            return;
        }
        kirim('kembalikan', $(this));
    });
});
</script>
@endpush
