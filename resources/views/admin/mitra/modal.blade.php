{{-- =========================================================
     MODAL TAMBAH MITRA
========================================================= --}}
<div class="modal fade"
     id="modal-create"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="form-create" enctype="multipart/form-data">

                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold">
                            <i class="fa fa-building me-2"></i>
                            Tambah Mitra Industri
                        </h5>

                        <small class="text-muted">
                            Tambahkan data perusahaan/mitra industri.
                        </small>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>


                <div class="modal-body">

                    <div class="row g-3">

                        {{-- NAMA PERUSAHAAN --}}
                        <div class="col-md-12">

                            <label class="form-label">
                                Nama Perusahaan
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="nama_perusahaan"
                                   id="create-nama_perusahaan"
                                   class="form-control"
                                   placeholder="Contoh: PT Telkom Indonesia"
                                   required>

                            <small class="text-danger error-msg"
                                   id="create-error-nama_perusahaan">
                            </small>

                        </div>


                        {{-- BIDANG --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Bidang
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="bidang"
                                   id="create-bidang"
                                   class="form-control"
                                   placeholder="Contoh: Teknologi Informasi"
                                   required>

                            <small class="text-danger error-msg"
                                   id="create-error-bidang">
                            </small>

                        </div>


                        {{-- CONTACT PERSON --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Contact Person
                            </label>

                            <input type="text"
                                   name="contact_person"
                                   id="create-contact_person"
                                   class="form-control"
                                   placeholder="Nama PIC">

                            <small class="text-danger error-msg"
                                   id="create-error-contact_person">
                            </small>

                        </div>


                        {{-- EMAIL --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                   name="email"
                                   id="create-email"
                                   class="form-control"
                                   placeholder="email@perusahaan.com">

                            <small class="text-danger error-msg"
                                   id="create-error-email">
                            </small>

                        </div>


                        {{-- WEBSITE --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Website
                            </label>

                            <input type="url"
                                   name="website"
                                   id="create-website"
                                   class="form-control"
                                   placeholder="https://example.com">

                            <small class="text-danger error-msg"
                                   id="create-error-website">
                            </small>

                        </div>


                        {{-- ALAMAT --}}
                        <div class="col-md-12">

                            <label class="form-label">
                                Alamat
                            </label>

                            <textarea name="alamat"
                                      id="create-alamat"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Alamat lengkap perusahaan"></textarea>

                            <small class="text-danger error-msg"
                                   id="create-error-alamat">
                            </small>

                        </div>



                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fa fa-save me-1"></i>
                        Simpan Mitra

                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
