<!-- Modal Tambah -->
<div class="modal fade" id="modal-create">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-create">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control">
                        <small class="text-danger error-msg" id="create-error-name"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                        <small class="text-danger error-msg" id="create-error-email"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-danger error-msg" id="create-error-password"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jabatan</label>
                        <select name="jabatan" class="form-select">
                            <option value="">-- Pilih jabatan --</option>
                            @foreach($daftarJabatan ?? config('jabatan.daftar', []) as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger error-msg" id="create-error-jabatan"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <small class="text-danger error-msg" id="create-error-role"></small>
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
                    <h5 class="modal-title">Ubah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nama</label>
                        <input type="text" id="update-name" name="name" class="form-control">
                        <small class="text-danger error-msg" id="update-error-name"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" id="update-email" name="email" class="form-control">
                        <small class="text-danger error-msg" id="update-error-email"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jabatan</label>
                        <select id="update-jabatan" name="jabatan" class="form-select">
                            <option value="">-- Pilih jabatan --</option>
                            @foreach($daftarJabatan ?? config('jabatan.daftar', []) as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger error-msg" id="update-error-jabatan"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select id="update-role" name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
