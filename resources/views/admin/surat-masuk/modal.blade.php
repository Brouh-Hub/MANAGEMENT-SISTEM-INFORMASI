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
                        <input type="text" name="no_agenda" class="form-control">
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
                        <input type="text" id="update-no_agenda" name="no_agenda" class="form-control">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Surat Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless" style="font-size:14px;">
                    <tr><td class="text-muted" width="160">Nomor Surat</td><td id="detail-nomor_surat"></td></tr>
                    <tr><td class="text-muted">No. Agenda</td><td id="detail-no_agenda"></td></tr>
                    <tr><td class="text-muted">Perihal</td><td id="detail-perihal"></td></tr>
                    <tr><td class="text-muted">Pengirim</td><td id="detail-pengirim"></td></tr>
                    <tr><td class="text-muted">Alamat Pengirim</td><td id="detail-alamat_asal_pengirim"></td></tr>
                    <tr><td class="text-muted">Tanggal Surat</td><td id="detail-tanggal_surat"></td></tr>
                    <tr><td class="text-muted">Tanggal Terima</td><td id="detail-tanggal_terima"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
