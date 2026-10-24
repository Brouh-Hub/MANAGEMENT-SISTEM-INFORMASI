@extends('layouts.main')
@php $title = $title ?? 'Disposisi Saya'; @endphp
@section('content')

<style>
    .aksi-wrap { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

    .btn-aksi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
    }
    .btn-aksi i { font-size: 11px; }
    .btn-aksi:hover { transform: translateY(-2px); }
    .btn-aksi:active { transform: translateY(0); }
    .btn-aksi:disabled { opacity: .65; pointer-events: none; }

    /* Lihat: soft biru */
    .btn-aksi-lihat {
        background: #EEF0FE;
        color: #1625C4;
        border-color: #DDE1FB;
    }
    .btn-aksi-lihat:hover {
        background: #1625C4;
        color: #fff;
        box-shadow: 0 6px 14px rgba(22,37,196,.25);
    }

    /* Mulai Proses: gradasi biru */
    .btn-aksi-proses {
        background: linear-gradient(135deg, #1625C4, #4A5CF0);
        color: #fff;
        box-shadow: 0 4px 12px rgba(22,37,196,.28);
    }
    .btn-aksi-proses:hover {
        color: #fff;
        box-shadow: 0 8px 18px rgba(22,37,196,.38);
    }

    /* Tandai Selesai: gradasi hijau */
    .btn-aksi-selesai {
        background: linear-gradient(135deg, #1B8A3E, #34C759);
        color: #fff;
        box-shadow: 0 4px 12px rgba(27,138,62,.28);
    }
    .btn-aksi-selesai:hover {
        color: #fff;
        box-shadow: 0 8px 18px rgba(27,138,62,.38);
    }

    /* Terima: outline biru */
    .btn-aksi-terima {
        background: #fff;
        color: #1625C4;
        border-color: #1625C4;
    }
    .btn-aksi-terima:hover {
        background: #1625C4;
        color: #fff;
        box-shadow: 0 6px 14px rgba(22,37,196,.25);
    }

    /* Penanda belum dibaca */
    .dot-baru {
        display: inline-block; width: 8px; height: 8px; border-radius: 50%;
        background: #E53E3E; margin-right: 6px; vertical-align: middle;
    }
    .row-baru { background: #FFF8F8; }

    /* Kotak laporan hasil */
    .laporan-box {
        background: #F5F6FC; border-radius: 12px; padding: 12px 14px;
        font-size: 13px; white-space: pre-line;
    }

    /* Sudah selesai */
    .aksi-done {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        background: #E3F9E5;
        color: #1B8A3E;
        font-size: 12.5px;
        font-weight: 600;
    }

    /* Versi lebar penuh (di dalam modal) */
    .btn-aksi-block {
        width: 100%;
        padding: 13px 16px;
        font-size: 14px;
    }
    .btn-aksi-block i { font-size: 13px; }
    .aksi-done.btn-aksi-block { justify-content: center; }
</style>

<h3 class="fw-bold mb-1">Disposisi Saya</h3>
<p class="text-muted mb-4">Daftar surat yang didisposisikan kepada Anda.</p>

<div class="card-panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-muted text-uppercase" style="font-size:11px;">
                    <th>No</th>
                    <th>Nomor Surat</th>
                    <th>Perihal</th>
                    <th>Sifat</th>
                    <th>Instruksi</th>
                    <th>Tanggal Disposisi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disposisi as $i => $d)
                    <tr style="font-size:13px;" class="{{ $d->dibaca_at ? '' : 'row-baru' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $d->suratMasuk->nomor_surat ?? '-' }}</td>
                        <td>
                            @if(!$d->dibaca_at)<span class="dot-baru" title="Belum dibaca"></span>@endif
                            {{ $d->suratMasuk->perihal ?? '-' }}
                        </td>
                        <td>
                            @php
                                $sifatBadge = match($d->sifat) {
                                    'sangat_segera' => 'bg-danger',
                                    'segera' => 'bg-warning text-dark',
                                    'rahasia' => 'bg-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge rounded-pill {{ $sifatBadge }}">{{ ucwords(str_replace('_', ' ', $d->sifat)) }}</span>
                        </td>
                        <td>{{ Str::limit($d->instruksi, 50) }}</td>
                        <td>{{ $d->tanggal_disposisi ? \Carbon\Carbon::parse($d->tanggal_disposisi)->format('d M Y') : '-' }}</td>
                        <td>
                            @php
                                $statusBadge = match($d->status) {
                                    'selesai'  => 'bg-success',
                                    'diproses' => 'bg-primary',
                                    'diterima' => 'bg-info text-dark',
                                    default    => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge rounded-pill {{ $statusBadge }}">{{ $d->status_label }}</span>
                            @if($d->status === 'pending')
                                <div class="text-muted" style="font-size:11px;">
                                    {{ $d->dibaca_at ? 'Dibaca ' . $d->dibaca_at->diffForHumans() : 'Belum dibaca' }}
                                </div>
                            @elseif($d->status === 'selesai' && $d->selesai_at)
                                <div class="text-muted" style="font-size:11px;">{{ $d->selesai_at->format('d M Y H:i') }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="aksi-wrap">
                                <button class="btn-aksi btn-aksi-lihat btn-lihat-detail"
                                        data-surat-id="{{ $d->suratMasuk->id ?? '' }}"
                                        data-disposisi-id="{{ $d->id }}"
                                        data-status="{{ $d->status }}"
                                        data-dibaca="{{ $d->dibaca_at ? 1 : 0 }}"
                                        data-instruksi="{{ $d->instruksi }}"
                                        data-catatan="{{ $d->catatan }}"
                                        data-laporan="{{ $d->laporan_hasil }}"
                                        data-file="{{ $d->file_hasil_url }}"
                                        title="Lihat Detail">
                                    <i class="fa fa-eye"></i> Lihat
                                </button>

                                @include('admin.disposisi.partials.tombol-status', ['d' => $d, 'block' => false])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada disposisi untuk Anda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()?->jabatan === 'Kepala Sekolah')

    <h4 class="fw-bold mt-4 mb-1">
        Persetujuan Surat Keluar
    </h4>

    <p class="text-muted mb-3">
        Daftar surat keluar dari Tata Usaha yang menunggu persetujuan Anda.
    </p>

    <div class="card-panel mb-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted text-uppercase"
                        style="font-size: 11px;">
                        <th>No</th>
                        <th>Nomor Surat</th>
                        <th>Perihal</th>
                        <th>Tujuan</th>
                        <th>Tanggal Surat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($suratKeluarMenunggu as $i => $surat)
                        <tr style="font-size: 13px;">
                            <td>{{ $i + 1 }}</td>

                            <td>{{ $surat->nomor_surat ?? '-' }}</td>

                            <td>{{ $surat->perihal ?? '-' }}</td>

                            <td>{{ $surat->tujuan ?? '-' }}</td>

                            <td>
                                {{ $surat->tanggal_surat
                                    ? \Carbon\Carbon::parse($surat->tanggal_surat)->format('d-m-Y')
                                    : '-' }}
                            </td>

                            <td>
                                <span class="badge bg-warning text-dark">
                                    Menunggu Persetujuan
                                </span>
                            </td>

                            <td>
                                @include('admin.disposisi.partials.tombol-surat-keluar', ['surat' => $surat])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="text-center text-muted py-4">
                                Belum ada surat keluar yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal detail surat keluar (preview + Setujui) --}}
    @include('admin.disposisi.partials.modal-surat-keluar')

@endif

<!-- Modal Detail Surat -->
<div class="modal fade" id="modalDetailSurat" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="border:none; border-radius:16px;">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Detail Surat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-7">
            <iframe id="pdfViewer" src="" style="width:100%; height:520px; border:1px solid #ddd; border-radius:10px;"></iframe>
          </div>
          <div class="col-md-5">
            <p class="mb-1"><strong id="detailNomorSurat"></strong></p>
            <p class="text-muted mb-1" id="detailPengirim"></p>
            <p class="mb-3" id="detailPerihal"></p>

            <p class="small fw-semibold mb-1">Instruksi Kepala Sekolah</p>
            <div class="laporan-box mb-2" id="detailInstruksi"></div>
            <div class="small text-muted mb-3" id="detailCatatan"></div>

            <div id="detailLaporanWrap" class="mb-3" style="display:none;">
                <p class="small fw-semibold mb-1">Laporan Hasil Anda</p>
                <div class="laporan-box" id="detailLaporan"></div>
                <a href="#" target="_blank" id="detailFile" class="small d-inline-block mt-2" style="display:none;">
                    <i class="fa fa-paperclip"></i> Lihat lampiran hasil
                </a>
            </div>

            <div id="modalActionArea"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Laporan Hasil (saat Tandai Selesai) -->
<div class="modal fade" id="modalLaporan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border:none; border-radius:16px;">
      <form id="formLaporan" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Laporan Hasil Tindak Lanjut</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="laporanDisposisiId">

          <div class="mb-3">
            <label class="form-label small fw-semibold">Apa yang sudah dikerjakan? <span class="text-danger">*</span></label>
            <textarea id="laporanHasil" class="form-control" rows="5" maxlength="5000"
                      placeholder="Contoh: Sudah koordinasi dengan PT X, MoU ditandatangani tanggal 20 Oktober 2026."></textarea>
            <small class="text-danger" id="errorLaporan"></small>
          </div>

          <div class="mb-1">
            <label class="form-label small fw-semibold">Lampiran hasil (opsional)</label>
            <input type="file" id="fileHasil" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            <small class="text-muted">PDF, gambar, atau Word. Maksimal 5 MB.</small>
            <small class="text-danger d-block" id="errorFile"></small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-aksi btn-aksi-selesai" id="btnKirimLaporan">
            <i class="fa fa-check"></i> Kirim & Tandai Selesai
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
    const modalDetail  = new bootstrap.Modal(document.getElementById('modalDetailSurat'));
    const modalLaporan = new bootstrap.Modal(document.getElementById('modalLaporan'));

    // Tombol aksi sesuai status (dipakai di dalam modal detail)
    function tombolStatus(status, id) {
        if (status === 'pending') {
            return `<button class="btn-aksi btn-aksi-terima btn-aksi-block btn-status" data-id="${id}" data-next="diterima">
                        <i class="fa fa-hand"></i> Terima Disposisi
                    </button>`;
        }
        if (status === 'diterima') {
            return `<button class="btn-aksi btn-aksi-proses btn-aksi-block btn-status" data-id="${id}" data-next="diproses">
                        <i class="fa fa-play"></i> Mulai Proses
                    </button>`;
        }
        if (status === 'diproses') {
            return `<button class="btn-aksi btn-aksi-selesai btn-aksi-block btn-buka-laporan" data-id="${id}">
                        <i class="fa fa-check"></i> Tandai Selesai
                    </button>`;
        }
        return `<span class="aksi-done btn-aksi-block"><i class="fa fa-circle-check"></i> Sudah selesai</span>`;
    }

    function kirimStatus(id, formData, $btn) {
        const originalHtml = $btn.html();
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

        formData.append('_method', 'PUT');

        $.ajax({
            url: `/admin/disposisi-saya/${id}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                alert(res.message);
                location.reload();
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors ?? {};
                if (errors.laporan_hasil) $('#errorLaporan').text(errors.laporan_hasil[0]);
                if (errors.file_hasil) $('#errorFile').text(errors.file_hasil[0]);

                if (!errors.laporan_hasil && !errors.file_hasil) {
                    alert(xhr.responseJSON?.message ?? 'Gagal memperbarui status.');
                }
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // Terima / Mulai Proses
    $(document).on('click', '.btn-status', function () {
        const fd = new FormData();
        fd.append('status', $(this).data('next'));
        kirimStatus($(this).data('id'), fd, $(this));
    });

    // Buka form laporan sebelum Tandai Selesai
    $(document).on('click', '.btn-buka-laporan', function () {
        $('#formLaporan')[0].reset();
        $('#errorLaporan, #errorFile').text('');
        $('#laporanDisposisiId').val($(this).data('id'));
        modalDetail.hide();
        modalLaporan.show();
    });

    $('#formLaporan').on('submit', function (e) {
        e.preventDefault();
        $('#errorLaporan, #errorFile').text('');

        const laporan = $('#laporanHasil').val().trim();
        if (laporan.length < 5) {
            $('#errorLaporan').text('Laporan hasil wajib diisi (minimal 5 karakter).');
            return;
        }

        const fd = new FormData();
        fd.append('status', 'selesai');
        fd.append('laporan_hasil', laporan);

        const file = $('#fileHasil')[0].files[0];
        if (file) fd.append('file_hasil', file);

        kirimStatus($('#laporanDisposisiId').val(), fd, $('#btnKirimLaporan'));
    });

    // Lihat detail surat
    $(document).on('click', '.btn-lihat-detail', function () {
        const $btn        = $(this);
        const suratId     = $btn.data('surat-id');
        const disposisiId = $btn.data('disposisi-id');
        const status      = $btn.data('status');

        if (!suratId) {
            alert('Data surat tidak ditemukan.');
            return;
        }

        // Tandai sudah dibaca saat pertama kali dibuka
        if (!$btn.data('dibaca')) {
            $.post(`/admin/disposisi-saya/${disposisiId}/baca`);
            $btn.data('dibaca', 1);
            $btn.closest('tr').removeClass('row-baru').find('.dot-baru').remove();
        }

        $.get(`/admin/disposisi/${suratId}/detail`, function (res) {
            $('#pdfViewer').attr('src', res.pdf_url || 'about:blank');
            $('#detailNomorSurat').text(res.nomor_surat);
            $('#detailPengirim').text('Dari: ' + res.pengirim);
            $('#detailPerihal').text(res.perihal);

            $('#detailInstruksi').text($btn.data('instruksi') || '-');
            const catatan = $btn.data('catatan');
            $('#detailCatatan').text(catatan ? 'Catatan: ' + catatan : '');

            const laporan = $btn.data('laporan');
            const fileUrl = $btn.data('file');
            if (status === 'selesai' && laporan) {
                $('#detailLaporan').text(laporan);
                $('#detailLaporanWrap').show();
                if (fileUrl) {
                    $('#detailFile').attr('href', fileUrl).show();
                } else {
                    $('#detailFile').hide();
                }
            } else {
                $('#detailLaporanWrap').hide();
            }

            $('#modalActionArea').html(tombolStatus(status, disposisiId));

            modalDetail.show();
        }).fail(function (xhr) {
            alert('Gagal ambil detail surat: ' + (xhr.responseJSON?.message ?? xhr.status));
        });
    });
});
</script>
@endpush

{{-- Setujui surat keluar: lihat partials/modal-surat-keluar.blade.php --}}
