<!-- Modal Tambah -->
<div class="modal fade" id="modal-create">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-create">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Surat Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control">
                        <small class="text-danger error-msg" id="create-error-nomor_surat"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">No. Agenda</label>
                        {{-- Dibuat otomatis saat disimpan (format 001/SM/X/2026) --}}
                        <input type="text" id="create-no_agenda" class="form-control bg-light" readonly placeholder="Otomatis saat disimpan">
                        <small class="text-muted d-block" style="font-size:12px;">
                            <i class="fa fa-wand-magic-sparkles"></i> Nomor dibuat otomatis saat surat disimpan.
                        </small>
                        <small class="text-danger error-msg" id="create-error-no_agenda"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Perihal</label>
                        <input type="text" name="perihal" class="form-control">
                        <small class="text-danger error-msg" id="create-error-perihal"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Pengirim</label>
                        <input type="text" name="pengirim" class="form-control">
                        <small class="text-danger error-msg" id="create-error-pengirim"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alamat Asal Pengirim</label>
                        <textarea name="alamat_asal_pengirim" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control">
                            <small class="text-danger error-msg" id="create-error-tanggal_surat"></small>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Tanggal Terima</label>
                            <input type="date" name="tanggal_terima" class="form-control">
                            <small class="text-danger error-msg" id="create-error-tanggal_terima"></small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">File Surat (PDF/Gambar)</label>
                        <input type="file" name="file_surat" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah -->
<div class="modal fade" id="modal-update">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-update">
                @csrf
                <input type="hidden" id="update-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Surat Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nomor Surat</label>
                        <input type="text" id="update-nomor_surat" name="nomor_surat" class="form-control">
                        <small class="text-danger error-msg" id="update-error-nomor_surat"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">No. Agenda</label>
                        <input type="text" id="update-no_agenda" class="form-control bg-light" readonly>
                        <small class="text-muted d-block" style="font-size:12px;">No. Agenda yang sudah tercatat tidak bisa diubah.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Perihal</label>
                        <input type="text" id="update-perihal" name="perihal" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Pengirim</label>
                        <input type="text" id="update-pengirim" name="pengirim" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alamat Asal Pengirim</label>
                        <textarea id="update-alamat_asal_pengirim" name="alamat_asal_pengirim" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">Tanggal Surat</label>
                            <input type="date" id="update-tanggal_surat" name="tanggal_surat" class="form-control">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Tanggal Terima</label>
                            <input type="date" id="update-tanggal_terima" name="tanggal_terima" class="form-control">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">File Surat (kosongkan jika tidak diubah)</label>
                        <input type="file" name="file_surat" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail (read-only) -->
<div class="modal fade" id="modal-detail">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Surat Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">

                    {{-- Preview file surat --}}
                    <div class="col-lg-7">
                        <div id="detail-preview" class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height:560px;">
                            {{-- diisi lewat JavaScript --}}
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-2" id="detail-file-actions" style="display:none !important;">
                            <a href="#" target="_blank" id="detail-file-open" class="btn btn-sm btn-light">
                                <i class="fa fa-up-right-from-square"></i> Buka di tab baru
                            </a>
                            <a href="#" download id="detail-file-download" class="btn btn-sm btn-light">
                                <i class="fa fa-download"></i> Unduh
                            </a>
                        </div>
                    </div>

                    {{-- Data surat --}}
                    <div class="col-lg-5">
                        <table class="table table-borderless" style="font-size:14px;">
                            <tr><td class="text-muted" width="140">Nomor Surat</td><td id="detail-nomor_surat"></td></tr>
                            <tr><td class="text-muted">No. Agenda</td><td id="detail-no_agenda"></td></tr>
                            <tr><td class="text-muted">Perihal</td><td id="detail-perihal"></td></tr>
                            <tr><td class="text-muted">Pengirim</td><td id="detail-pengirim"></td></tr>
                            <tr><td class="text-muted">Alamat Pengirim</td><td id="detail-alamat_asal_pengirim"></td></tr>
                            <tr><td class="text-muted">Tanggal Surat</td><td id="detail-tanggal_surat"></td></tr>
                            <tr><td class="text-muted">Tanggal Terima</td><td id="detail-tanggal_terima"></td></tr>
                            <tr><td class="text-muted">Konfirmasi Kepala TU</td><td id="detail-konfirmasi_kepala_tu" style="white-space:pre-line;"></td></tr>
                            <tr><td class="text-muted">Keputusan Kepsek</td><td id="detail-keputusan_kepsek" style="white-space:pre-line;"></td></tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
