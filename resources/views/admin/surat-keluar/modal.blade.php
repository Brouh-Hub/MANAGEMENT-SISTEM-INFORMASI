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

                    {{-- Isi surat dari template (supaya surat yang dikembalikan bisa direvisi) --}}
                    <div id="update-template-fields-wrapper" class="d-none">
                        <hr>
                        <div class="small text-muted mb-2">Isi surat (template)</div>
                        <div id="update-template-fields"></div>
                        <hr>
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
