<div class="modal fade" id="modal-update">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-update">
                @csrf
                <input type="hidden" id="update-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Disposisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Instruksi</label>
                        <textarea id="update-instruksi" name="instruksi" class="form-control" rows="2"></textarea>
                        <small class="text-danger error-msg" id="update-error-instruksi"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Catatan</label>
                        <textarea id="update-catatan" name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tujuan Disposisi</label>
                        <select id="update-tujuan_user_id" name="tujuan_user_id" class="form-control">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}@if($user->jabatan) ({{ $user->jabatan }})@endif</option>
                            @endforeach
                        </select>
                        <small class="text-danger error-msg" id="update-error-tujuan_user_id"></small>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">Sifat</label>
                            <select id="update-sifat" name="sifat" class="form-control">
                                <option value="biasa">Biasa</option>
                                <option value="segera">Segera</option>
                                <option value="sangat_segera">Sangat Segera</option>
                                <option value="rahasia">Rahasia</option>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Status</label>
                            <select id="update-status" name="status" class="form-control">
                                <option value="pending">Pending</option>
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
