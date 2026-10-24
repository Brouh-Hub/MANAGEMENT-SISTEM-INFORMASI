@php
    $fields  = \App\Models\SuratKeluarField::orderBy('urutan')->orderBy('id')->get();
    $byKey   = $fields->keyBy('key');
    $label   = fn ($k, $default) => optional($byKey->get($k))->label ?? $default;
    $customs = $fields->where('is_default', false)->where('is_active', true);
@endphp

<!-- Modal Tambah -->
@include('admin.surat-keluar.modal-create')

<!-- Modal Ubah -->
<div class="modal fade" id="modal-update">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-update">
                @csrf
                <input type="hidden" id="update-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Surat Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ $label('nomor_surat', 'Nomor Surat') }}</label>
                        <input type="text" id="update-nomor_surat" class="form-control bg-light" readonly title="Nomor surat tidak bisa diubah">
                        <small class="text-muted" style="font-size:11px;">Nomor surat dibuat sistem dan tidak bisa diubah.</small>
                        <small class="text-danger error-msg" id="update-error-nomor_surat"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ $label('perihal', 'Perihal') }}</label>
                        <input type="text" id="update-perihal" name="perihal" class="form-control">
                        <small class="text-danger error-msg" id="update-error-perihal"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ $label('tujuan', 'Tujuan') }}</label>
                        <input type="text" id="update-tujuan" name="tujuan" class="form-control">
                        <small class="text-danger error-msg" id="update-error-tujuan"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ $label('alamat_tujuan', 'Alamat Tujuan') }}</label>
                        <textarea id="update-alamat_tujuan" name="alamat_tujuan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ $label('keterangan', 'Keterangan') }}</label>
                        <textarea id="update-keterangan" name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ $label('tanggal_surat', 'Tanggal Surat') }}</label>
                        <input type="date" id="update-tanggal_surat" name="tanggal_surat" class="form-control">
                        <small class="text-danger error-msg" id="update-error-tanggal_surat"></small>
                    </div>

                    {{-- Field custom --}}
                    @foreach($customs as $cf)
                        <div class="mb-2">
                            <label class="form-label">{{ $cf->label }}</label>
                            @if($cf->type === 'textarea')
                                <textarea name="custom[{{ $cf->key }}]" data-custom-key="{{ $cf->key }}" class="form-control" rows="2"></textarea>
                            @else
                                <input type="{{ $cf->type }}" name="custom[{{ $cf->key }}]" data-custom-key="{{ $cf->key }}" class="form-control">
                            @endif
                            <small class="text-danger error-msg" id="update-error-custom-{{ $cf->key }}"></small>
                        </div>
                    @endforeach

                    <div class="mb-2">
                        <label class="form-label">File Surat (kosongkan jika tidak diubah)</label>
                        <input type="file" name="file_surat" class="form-control">
                        <small class="text-danger error-msg" id="update-error-file_surat"></small>
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
                <h5 class="modal-title">Detail Surat Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">

                    {{-- Preview PDF surat --}}
                    <div class="col-lg-7">
                        <div id="detail-preview"
                             class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden"
                             style="height:600px;"></div>

                        <div class="d-flex justify-content-end gap-2 mt-2" id="detail-file-actions" style="display:none !important;">
                            <a href="#" target="_blank" id="detail-file-open" class="btn btn-sm btn-light">
                                <i class="fa fa-up-right-from-square"></i> Buka di tab baru
                            </a>
                            <a href="#" id="detail-file-download" class="btn btn-sm btn-light">
                                <i class="fa fa-download"></i> Unduh
                            </a>
                        </div>
                    </div>

                    {{-- Data surat --}}
                    <div class="col-lg-5">
                        <table class="table table-borderless" style="font-size:14px;">
                            <tr><td class="text-muted" width="140">{{ $label('nomor_surat', 'Nomor Surat') }}</td><td id="detail-nomor_surat"></td></tr>
                            <tr><td class="text-muted">{{ $label('perihal', 'Perihal') }}</td><td id="detail-perihal"></td></tr>
                            <tr><td class="text-muted">{{ $label('tujuan', 'Tujuan') }}</td><td id="detail-tujuan"></td></tr>
                            <tr><td class="text-muted">{{ $label('alamat_tujuan', 'Alamat Tujuan') }}</td><td id="detail-alamat_tujuan"></td></tr>
                            <tr><td class="text-muted">{{ $label('keterangan', 'Keterangan') }}</td><td id="detail-keterangan"></td></tr>
                            <tr><td class="text-muted">{{ $label('tanggal_surat', 'Tanggal Surat') }}</td><td id="detail-tanggal_surat"></td></tr>
                            {{-- baris field custom disisipkan lewat JS sebelum baris Status --}}
                            <tr id="detail-status-row"><td class="text-muted">Status</td><td id="detail-status"></td></tr>
                            <tr><td class="text-muted">Kepala TU</td><td id="detail-konfirmasi" style="white-space:pre-line;"></td></tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atur Field -->
<div class="modal fade" id="modal-fields">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Field Form Surat Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:13px;">
                    Ubah teks label di atas field. Field bawaan tidak bisa dihapus.
                    Field tambahan bisa dinonaktifkan atau dihapus. Halaman dimuat ulang otomatis setelah perubahan.
                </p>

                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th width="130">Tipe</th>
                            <th width="70">Aktif</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fields as $fl)
                            <tr data-id="{{ $fl->id }}">
                                <td><input type="text" class="form-control form-control-sm f-label" value="{{ $fl->label }}"></td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $fl->type }}</span>
                                    @if($fl->is_default)
                                        <small class="text-muted">bawaan</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!$fl->is_default)
                                        <div class="form-check form-switch">
                                            <input class="form-check-input f-active" type="checkbox" @checked($fl->is_active)>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-field-save">Simpan</button>
                                    @if(!$fl->is_default)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-field-delete">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>
                <h6>Tambah Field Baru</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" id="new-field-label" class="form-control" placeholder="Label field, contoh: Lampiran">
                    </div>
                    <div class="col-md-3">
                        <select id="new-field-type" class="form-select">
                            <option value="text">Teks pendek</option>
                            <option value="textarea">Teks panjang</option>
                            <option value="date">Tanggal</option>
                            <option value="number">Angka</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="btn-field-add" class="btn btn-success w-100">+ Tambah</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
