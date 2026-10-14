@extends('layouts.main')
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
                    <tr style="font-size:13px;">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $d->suratMasuk->nomor_surat ?? '-' }}</td>
                        <td>{{ $d->suratMasuk->perihal ?? '-' }}</td>
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
                                    'selesai' => 'bg-success',
                                    'diproses' => 'bg-primary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge rounded-pill {{ $statusBadge }}">{{ ucfirst($d->status) }}</span>
                        </td>
                        <td>
                            <div class="aksi-wrap">
                                <button class="btn-aksi btn-aksi-lihat btn-lihat-detail"
                                        data-surat-id="{{ $d->suratMasuk->id ?? '' }}"
                                        data-disposisi-id="{{ $d->id }}"
                                        data-status="{{ $d->status }}"
                                        title="Lihat Detail">
                                    <i class="fa fa-eye"></i> Lihat
                                </button>

                                @if($d->status === 'pending')
                                    <button class="btn-aksi btn-aksi-proses btn-proses" data-id="{{ $d->id }}">
                                        <i class="fa fa-play"></i> Mulai Proses
                                    </button>
                                @elseif($d->status === 'diproses')
                                    <button class="btn-aksi btn-aksi-selesai btn-selesai" data-id="{{ $d->id }}">
                                        <i class="fa fa-check"></i> Tandai Selesai
                                    </button>
                                @else
                                    <span class="aksi-done"><i class="fa fa-circle-check"></i> Selesai</span>
                                @endif
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
                                <div class="d-flex gap-2 flex-wrap">

                                    @if($surat->file_path)
                                        <a
                                            href="{{ route('surat-keluar.preview', $surat->id) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-eye"></i>
                                            Lihat PDF
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            PDF belum tersedia
                                        </span>
                                    @endif

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success btn-approve-surat-keluar"
                                        data-url="{{ url('admin/surat-keluar/'.$surat->id.'/approve') }}">
                                        <i class="fa fa-check"></i>
                                        Setujui
                                    </button>

                                </div>
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

            <div id="modalActionArea"></div>
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
    const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailSurat'));

    function updateStatus($btn, id, status) {
        const originalHtml = $btn.html();
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

        $.ajax({
            url: `/admin/disposisi-saya/${id}`,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function (res) {
                alert(res.message);
                location.reload();
            },
            error: function () {
                alert('Gagal memperbarui status.');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    $(document).on('click', '.btn-proses', function () {
        updateStatus($(this), $(this).data('id'), 'diproses');
    });

    $(document).on('click', '.btn-selesai', function () {
        updateStatus($(this), $(this).data('id'), 'selesai');
    });

    $(document).on('click', '.btn-lihat-detail', function () {
        const suratId = $(this).data('surat-id');
        const disposisiId = $(this).data('disposisi-id');
        const status = $(this).data('status');

        if (!suratId) {
            alert('Data surat tidak ditemukan.');
            return;
        }

        $.get(`/admin/disposisi/${suratId}/detail`, function (res) {
            $('#pdfViewer').attr('src', res.pdf_url);
            $('#detailNomorSurat').text(res.nomor_surat);
            $('#detailPengirim').text('Dari: ' + res.pengirim);
            $('#detailPerihal').text(res.perihal);

            let actionBtn = '';
            if (status === 'pending') {
                actionBtn = `<button class="btn-aksi btn-aksi-proses btn-aksi-block btn-proses" data-id="${disposisiId}">
                                <i class="fa fa-play"></i> Mulai Proses
                             </button>`;
            } else if (status === 'diproses') {
                actionBtn = `<button class="btn-aksi btn-aksi-selesai btn-aksi-block btn-selesai" data-id="${disposisiId}">
                                <i class="fa fa-check"></i> Tandai Selesai
                             </button>`;
            } else {
                actionBtn = `<span class="aksi-done btn-aksi-block"><i class="fa fa-circle-check"></i> Sudah selesai</span>`;
            }
            $('#modalActionArea').html(actionBtn);

            modalDetail.show();
        }).fail(function (xhr) {
            alert('Gagal ambil detail surat: ' + (xhr.responseJSON?.message ?? xhr.status));
        });
    });
});
</script>
@endpush

@push('scripts')
<script>
$(document).on('click', '.btn-approve-surat-keluar', function () {
    const $button = $(this);
    const url = $button.data('url');

    if (!confirm('Yakin ingin menyetujui surat keluar ini?')) {
        return;
    }

    $button
        .prop('disabled', true)
        .text('Memproses...');

    $.ajax({
        url: url,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },

        success: function (response) {
            alert(response.message || 'Surat berhasil disetujui.');
            window.location.reload();
        },

        error: function (xhr) {
            alert(
                xhr.responseJSON?.message ||
                'Gagal menyetujui surat keluar.'
            );

            $button
                .prop('disabled', false)
                .html('<i class="fa fa-check"></i> Setujui');
        }
    });
});
</script>
@endpush
