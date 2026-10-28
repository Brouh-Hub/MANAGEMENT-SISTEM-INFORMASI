@extends('layouts.main')
@php $title = $title ?? 'Disposisi'; @endphp
@section('content')

<style>
    .keputusan-tabs .nav-link { font-size: 13px; font-weight: 600; color: #4a4f55; background: #F1F2F8; margin: 0 2px; }
    .keputusan-tabs .nav-link.active { background: #1625C4; color: #fff; }
    .chip-alasan {
        border: 1px solid #DDE1FB; background: #F5F6FC; color: #1625C4;
        border-radius: 999px; padding: 4px 10px; font-size: 12px; cursor: pointer;
    }
    .chip-alasan:hover, .chip-alasan.aktif { background: #1625C4; color: #fff; border-color: #1625C4; }
    .chip-instruksi-wrap { display: inline-flex; align-items: center; }
    .chip-instruksi-wrap .chip-instruksi { border-top-right-radius: 0; border-bottom-right-radius: 0; padding-right: 8px; }
    .chip-hapus {
        border: 1px solid #DDE1FB; border-left: none; background: #F5F6FC; color: #8a8f98;
        border-radius: 0 999px 999px 0; padding: 4px 9px 4px 6px; font-size: 13px; line-height: 1; cursor: pointer;
    }
    .chip-hapus:hover { background: #FDE8E8; color: #C53030; border-color: #F5C2C2; }
</style>

<h3 class="fw-bold mb-1">Disposisi Surat</h3>
<p class="text-muted mb-4">Tentukan tujuan disposisi untuk surat masuk.</p>

<div class="card-panel">
    <table id="tableSurat" class="table align-middle w-100">
        <thead>
            <tr class="text-muted text-uppercase" style="font-size:11px;">
                <th>No</th>
                <th>Nomor Surat</th>
                <th>Pengirim</th>
                <th>Perihal</th>
                <th>Tanggal Terima</th>
                <th>Status Disposisi</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Surat Keluar Menunggu Persetujuan -->
<h5 class="fw-bold mt-5 mb-1">Surat Keluar Menunggu Persetujuan</h5>
<p class="text-muted mb-3">Surat keluar yang diajukan Tata Usaha dan perlu persetujuan Anda.</p>

<div class="card-panel">
    <table class="table align-middle w-100">
        <thead>
            <tr class="text-muted text-uppercase" style="font-size:11px;">
                <th>No</th>
                <th>Nomor Surat</th>
                <th>Perihal</th>
                <th>Tujuan</th>
                <th>Tanggal Surat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suratKeluarMenunggu as $i => $surat)
                <tr style="font-size:13px;">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $surat->nomor_surat ?? '-' }}</td>
                    <td>{{ $surat->perihal ?? '-' }}</td>
                    <td>{{ $surat->tujuan ?? '-' }}</td>
                    <td>{{ $surat->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->format('d-m-Y') : '-' }}</td>
                    <td>
                        @include('admin.disposisi.partials.tombol-surat-keluar', ['surat' => $surat])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada surat keluar yang menunggu persetujuan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Riwayat aktivitas Kepala Sekolah (timeline) --}}
@include('admin.disposisi.partials.riwayat-aktivitas')

{{-- Modal detail surat keluar (preview + Setujui) --}}
@include('admin.disposisi.partials.modal-surat-keluar')

<!-- Modal Detail & Disposisi -->
<div class="modal fade" id="modalDisposisi" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Surat & Disposisi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-7">
            <iframe id="pdfViewer" src="" style="width:100%; height:520px; border:1px solid #ddd; border-radius:6px;"></iframe>
          </div>
          <div class="col-md-5">
            <p class="mb-1"><strong id="detailNomorSurat"></strong></p>
            <p class="text-muted mb-1" id="detailPengirim"></p>
            <p class="mb-3" id="detailPerihal"></p>

            {{-- Info kalau surat sudah diputuskan --}}
            <div id="infoKeputusan" class="alert mb-3" style="display:none;"></div>

            <div id="areaKeputusan">
              <ul class="nav nav-pills nav-fill keputusan-tabs mb-3" role="tablist">
                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabTeruskan" type="button">
                    <i class="fa fa-share"></i> Teruskan
                  </button>
                </li>
                <li class="nav-item tab-putuskan">
                  <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabArsip" type="button">
                    <i class="fa fa-box-archive"></i> Arsipkan
                  </button>
                </li>
              </ul>

              <div class="tab-content">
                {{-- TERUSKAN --}}
                <div class="tab-pane fade show active" id="tabTeruskan">
                <form id="formDisposisi">
                  @csrf
                  <input type="hidden" name="disposisi_id" id="disposisi_id">

                  <div class="mb-3">
                    <label class="form-label">Teruskan ke</label>
                    <select name="tujuan_user_id" class="form-select" required>
                      <option value="">-- Pilih Penerima --</option>
                      @foreach($penerimaOptions as $u)
                        <option value="{{ $u->id }}">{{ $u->jabatan }} — {{ $u->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Sifat</label>
                    <select name="sifat" class="form-select" required>
                      <option value="biasa">Biasa</option>
                      <option value="segera">Segera</option>
                      <option value="sangat_segera">Sangat Segera</option>
                      <option value="rahasia">Rahasia</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Instruksi</label>
                    <div id="instruksiCepatWrap" style="{{ $instruksiTerakhir->isEmpty() ? 'display:none;' : '' }}">
                      <div class="small text-muted mb-1" id="labelInstruksiCepat">
                        <i class="fa {{ $instruksiDariRiwayat ? 'fa-clock-rotate-left' : 'fa-lightbulb' }}"></i>
                        {{ $instruksiDariRiwayat ? 'Terakhir Anda pakai' : 'Contoh instruksi' }}
                      </div>
                      <div class="d-flex flex-wrap gap-1 mb-2" id="instruksiCepat">
                        @foreach($instruksiTerakhir as $ins)
                          <span class="chip-instruksi-wrap">
                            <button type="button" class="chip-alasan chip-instruksi" data-target="#instruksiDisposisi"
                                    data-text="{{ $ins }}" title="{{ $ins }}">{{ \Illuminate\Support\Str::limit($ins, 40) }}</button>
                            <button type="button" class="chip-hapus" data-text="{{ $ins }}" title="Hapus dari daftar" aria-label="Hapus dari daftar">&times;</button>
                          </span>
                        @endforeach
                      </div>
                    </div>
                    <textarea name="instruksi" id="instruksiDisposisi" class="form-control" rows="3" required
                              placeholder="Tulis instruksi, atau klik salah satu pilihan di atas"></textarea>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-control" rows="2"></textarea>
                  </div>

                  <button type="submit" class="btn btn-primary w-100">Kirim Disposisi</button>
                </form>
                </div>

                {{-- ARSIPKAN (cukup diketahui) --}}
                <div class="tab-pane fade" id="tabArsip">
                  <p class="small text-muted">Surat cukup diketahui dan tidak perlu ditindaklanjuti. Surat tetap tersimpan di arsip.</p>
                  <form id="formArsip">
                    <label class="form-label">Alasan (opsional)</label>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                      @foreach($alasanArsip as $a)
                        <button type="button" class="chip-alasan" data-target="#alasanArsip" data-text="{{ $a }}">{{ $a }}</button>
                      @endforeach
                    </div>
                    <textarea id="alasanArsip" class="form-control mb-3" rows="3" maxlength="2000" placeholder="Tulis catatan tambahan bila perlu"></textarea>
                    <button type="submit" class="btn btn-secondary w-100">
                      <i class="fa fa-box-archive"></i> Arsipkan Surat
                    </button>
                  </form>
                </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Riwayat -->
<div class="modal fade" id="modalRiwayat" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Riwayat Disposisi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr class="text-muted" style="font-size:12px;">
                <th>Tujuan</th>
                <th>Sifat</th>
                <th>Status</th>
                <th>Instruksi</th>
                <th>Tanggal</th>
                <th>Tindak Lanjut</th>
              </tr>
            </thead>
            <tbody id="riwayatBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    const baseUrl = '{{ route("disposisi.index") }}';

    const table = $('#tableSurat').DataTable({
        processing: true,
        serverSide: true,
        ajax: baseUrl,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nomor_surat', name: 'nomor_surat' },
            { data: 'pengirim', name: 'pengirim' },
            { data: 'perihal', name: 'perihal' },
            { data: 'tanggal_terima', name: 'tanggal_terima' },
            { data: 'status_disposisi', name: 'status_disposisi' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
        ]
    });

    const modalDisposisi = new bootstrap.Modal(document.getElementById('modalDisposisi'));
    const modalRiwayat = new bootstrap.Modal(document.getElementById('modalRiwayat'));

    // Lihat detail & disposisikan
    $(document).on('click', '.btn-lihat-disposisi', function () {
        const id = $(this).data('id');

        $.get(`/admin/disposisi/${id}/detail`, function (res) {
            $('#disposisi_id').val(res.disposisi_id);
            $('#pdfViewer').attr('src', res.pdf_url || 'about:blank');
            $('#detailNomorSurat').text(res.nomor_surat);
            $('#detailPengirim').text('Dari: ' + res.pengirim);
            $('#detailPerihal').text(res.perihal);
            tampilkanKeputusan(res);
            modalDisposisi.show();
        }).fail(function (xhr) {
            alert('Gagal ambil detail surat: ' + (xhr.responseJSON?.message ?? xhr.status));
        });
    });

    // Kirim disposisi
    $('#formDisposisi').on('submit', function (e) {
        e.preventDefault();

        const disposisiId = $('#disposisi_id').val();

        if (!disposisiId) {
            alert('Disposisi awal untuk surat ini belum ditemukan. Pastikan surat sudah otomatis masuk ke akun Anda.');
            return;
        }

        $.ajax({
            url: `/admin/disposisi/${disposisiId}/teruskan`,
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                simpanInstruksiTerakhir($('#instruksiDisposisi').val());
                modalDisposisi.hide();
                $('#formDisposisi')[0].reset();
                table.ajax.reload(null, false);
                alert(res.message);
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (xhr.status === 422 && errors) {
                    let msg = '';
                    Object.values(errors).forEach(e => msg += e[0] + '\n');
                    alert(msg);
                } else {
                    alert(xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengirim disposisi. (Status: ' + xhr.status + ')');
                }
            }
        });
    });

    // Tampilkan / sembunyikan pilihan keputusan sesuai status surat
    function tampilkanKeputusan(res) {
        const $info = $('#infoKeputusan').hide().removeClass('alert-secondary alert-danger alert-info');
        $('#formDisposisi, #formArsip').each(function () { this.reset(); });
        $('.chip-alasan').removeClass('aktif');
        $('.keputusan-tabs .nav-link').first().tab('show');

        if (res.status === 'diarsipkan' || res.status === 'ditolak') {
            const ditolak = res.status === 'ditolak';
            $info.addClass(ditolak ? 'alert-danger' : 'alert-secondary').html(
                `<strong><i class="fa ${ditolak ? 'fa-ban' : 'fa-box-archive'}"></i> Surat ${res.status_label.toLowerCase()}</strong>` +
                (res.diputuskan ? `<div class="small">${res.diputuskan}</div>` : '')
            );
            if (res.alasan) {
                $info.append($('<div class="mt-1" style="white-space:pre-line;">').text('Alasan: ' + res.alasan));
            }
            $info.show();
            $('#areaKeputusan').hide();
            return;
        }

        $('#areaKeputusan').show();

        // Sudah pernah diteruskan: masih bisa diteruskan lagi, tapi tidak bisa diarsipkan/ditolak
        if (res.status === 'diteruskan') {
            $('.tab-putuskan').hide();
            $info.addClass('alert-info').text('Surat ini sudah diteruskan. Anda masih bisa meneruskan ke penerima lain.').show();
        } else {
            $('.tab-putuskan').show();
        }
    }

    // Buat satu pilihan instruksi (teks + tombol hapus)
    function buatChipInstruksi(teks) {
        const pendek = teks.length > 40 ? teks.slice(0, 40) + '...' : teks;
        return $('<span class="chip-instruksi-wrap">').append(
            $('<button type="button" class="chip-alasan chip-instruksi" data-target="#instruksiDisposisi">')
                .attr('data-text', teks).attr('title', teks).text(pendek),
            $('<button type="button" class="chip-hapus" title="Hapus dari daftar" aria-label="Hapus dari daftar">&times;</button>')
                .attr('data-text', teks)
        );
    }

    function rapikanDaftarInstruksi() {
        const ada = $('#instruksiCepat .chip-instruksi-wrap').length > 0;
        $('#instruksiCepatWrap').toggle(ada);
    }

    // Taruh instruksi yang baru dipakai di urutan pertama pilihan cepat
    function simpanInstruksiTerakhir(teks) {
        teks = (teks || '').replace(/\s+/g, ' ').trim();
        if (!teks) return;

        const $wrap = $('#instruksiCepat');

        $wrap.find('.chip-instruksi-wrap').filter(function () {
            return String($(this).find('.chip-instruksi').attr('data-text')).toLowerCase() === teks.toLowerCase();
        }).remove();

        $wrap.prepend(buatChipInstruksi(teks));
        $wrap.find('.chip-instruksi-wrap').slice({{ (int) config('disposisi.jumlah_instruksi_terakhir', 6) }}).remove();

        $('#labelInstruksiCepat').html('<i class="fa fa-clock-rotate-left"></i> Terakhir Anda pakai');
        rapikanDaftarInstruksi();
    }

    // Hapus satu instruksi dari daftar (riwayat disposisi tidak ikut terhapus)
    $(document).on('click', '.chip-hapus', function () {
        const $chip = $(this).closest('.chip-instruksi-wrap');
        const teks = $(this).attr('data-text');

        $chip.css('opacity', .4);

        $.ajax({
            url: '{{ route("disposisi.instruksi.hapus") }}',
            method: 'POST',
            data: { instruksi: teks },
            success: function () {
                $chip.remove();
                rapikanDaftarInstruksi();
            },
            error: function (xhr) {
                $chip.css('opacity', 1);
                alert(xhr.responseJSON?.message ?? 'Gagal menghapus instruksi.');
            }
        });
    });

    // Pilihan alasan cepat
    $(document).on('click', '.chip-alasan', function () {
        const $target = $($(this).data('target'));
        $(this).closest('.d-flex').find('.chip-alasan').removeClass('aktif');
        $(this).addClass('aktif');
        $target.val($(this).data('text')).trigger('focus');
    });

    async function kirimKeputusan(keputusan, alasan, $btn) {
        const disposisiId = $('#disposisi_id').val();
        if (!disposisiId) {
            alert('Disposisi awal untuk surat ini belum ditemukan.');
            return;
        }

        if (!await konfirmasi('Yakin ingin mengarsipkan surat ini? Surat tidak akan diteruskan ke siapa pun.')) return;

        const html = $btn.html();
        $btn.prop('disabled', true).text('Memproses...');

        $.ajax({
            url: `/admin/disposisi/${disposisiId}/putuskan`,
            method: 'POST',
            data: { keputusan: keputusan, alasan: alasan },
            success: function (res) {
                modalDisposisi.hide();
                table.ajax.reload(null, false);
                alert(res.message);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Gagal menyimpan keputusan.');
            },
            complete: function () {
                $btn.prop('disabled', false).html(html);
            }
        });
    }

    $('#formArsip').on('submit', function (e) {
        e.preventDefault();
        kirimKeputusan('diarsipkan', $('#alasanArsip').val().trim(), $(this).find('button[type=submit]'));
    });

    // Riwayat disposisi
    $(document).on('click', '.btn-riwayat-disposisi', function () {
        const id = $(this).data('id');
        const body = $('#riwayatBody').empty();

        $.get(`/admin/disposisi/${id}/riwayat`, function (res) {
            if (!res.items.length) {
                body.append('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat disposisi.</td></tr>');
            } else {
                res.items.forEach(function (i) {
                    const tr = $('<tr>').css('font-size', '13px');
                    [i.tujuan, i.sifat, i.status, i.instruksi, i.tanggal].forEach(function (v) {
                        tr.append($('<td>').text(v ?? '-'));
                    });

                    // Kolom tindak lanjut: kapan dibaca + laporan hasil dari penerima
                    const tl = $('<td>');
                    tl.append($('<div class="text-muted small">').text(i.dibaca ? 'Dibaca ' + i.dibaca : 'Belum dibaca'));
                    if (i.alasan) {
                        tl.append($('<div class="mt-1" style="white-space:pre-line;">').text('Alasan: ' + i.alasan));
                        if (i.diputuskan) tl.append($('<div class="text-muted small">').text('Diputuskan ' + i.diputuskan));
                    }
                    if (i.laporan) {
                        tl.append($('<div class="mt-1" style="white-space:pre-line;">').text(i.laporan));
                        if (i.selesai) tl.append($('<div class="text-muted small">').text('Selesai ' + i.selesai));
                        if (i.file_url) {
                            tl.append($('<a target="_blank" class="small">').attr('href', i.file_url).html('<i class="fa fa-paperclip"></i> Lampiran'));
                        }
                    }
                    tr.append(tl);
                    body.append(tr);
                });
            }
            modalRiwayat.show();
        }).fail(function (xhr) {
            alert('Gagal ambil riwayat: ' + (xhr.responseJSON?.message ?? xhr.status));
        });
    });

    // Hapus disposisi
    $(document).on('click', '.btn-hapus-disposisi', async function () {
        const id = $(this).data('disposisi-id');

        if (!await konfirmasi('Yakin ingin menghapus disposisi ini?')) return;

        $.ajax({
            url: `${baseUrl}/${id}`,
            method: 'POST',
            data: { _method: 'DELETE' },
            success: function (res) {
                table.ajax.reload(null, false);
                alert(res.message);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Gagal menghapus disposisi.');
            }
        });
    });

    // Setujui surat keluar: lihat partials/modal-surat-keluar.blade.php
});
</script>
@endpush