    @extends('layouts.main')
    @section('content')

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
                            <div class="d-flex gap-2 flex-wrap">
                                @if($surat->file_path)
                                    <a href="{{ route('surat-keluar.preview', $surat->id) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> Lihat PDF
                                    </a>
                                @endif
                                <button type="button" class="btn btn-sm btn-success btn-approve-surat-keluar"
                                        data-url="{{ route('surat-keluar.approve', $surat->id) }}">
                                    <i class="fa fa-check"></i> Setujui
                                </button>
                            </div>
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
                    <textarea name="instruksi" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Kirim Disposisi</button>
                </form>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

    <!-- Modal Riwayat -->
    <div class="modal fade" id="modalRiwayat" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                $('#pdfViewer').attr('src', res.pdf_url);
                $('#detailNomorSurat').text(res.nomor_surat);
                $('#detailPengirim').text('Dari: ' + res.pengirim);
                $('#detailPerihal').text(res.perihal);
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
                    modalDisposisi.hide();
                    $('#formDisposisi')[0].reset();
                    table.ajax.reload(null, false);
                    alert(res.message);
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let msg = '';
                        Object.values(errors).forEach(e => msg += e[0] + '\n');
                        alert(msg);
                    } else {
                        alert(xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengirim disposisi. (Status: ' + xhr.status + ')');
                    }
                }
            });
        });

        // Riwayat disposisi
        $(document).on('click', '.btn-riwayat-disposisi', function () {
            const id = $(this).data('id');
            const body = $('#riwayatBody').empty();

            $.get(`/admin/disposisi/${id}/riwayat`, function (res) {
                if (!res.items.length) {
                    body.append('<tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat disposisi.</td></tr>');
                } else {
                    res.items.forEach(function (i) {
                        const tr = $('<tr>');
                        [i.tujuan, i.sifat, i.status, i.instruksi, i.tanggal].forEach(function (v) {
                            tr.append($('<td>').text(v ?? '-'));
                        });
                        body.append(tr);
                    });
                }
                modalRiwayat.show();
            }).fail(function (xhr) {
                alert('Gagal ambil riwayat: ' + (xhr.responseJSON?.message ?? xhr.status));
            });
        });

        // Hapus disposisi
        $(document).on('click', '.btn-hapus-disposisi', function () {
            const id = $(this).data('disposisi-id');

            if (!confirm('Yakin ingin menghapus disposisi ini?')) return;

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

        // Setujui surat keluar
        $(document).on('click', '.btn-approve-surat-keluar', function () {
            const $btn = $(this);

            if (!confirm('Yakin ingin menyetujui surat keluar ini?')) return;

            $btn.prop('disabled', true).text('Memproses...');

            $.ajax({
                url: $btn.data('url'),
                method: 'POST',
                success: function (res) {
                    alert(res.message || 'Surat berhasil disetujui.');
                    window.location.reload();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Gagal menyetujui surat keluar.');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Setujui');
                }
            });
        });
    });
    </script>
    @endpush