{{--
| Modal detail surat keluar untuk Kepala Sekolah: preview PDF + data + tombol Setujui.
| Dipakai di halaman Disposisi dan Disposisi Saya.
--}}
<div class="modal fade" id="modal-surat-keluar-kepsek" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Detail Surat Keluar</h5>
                    <small class="text-muted" id="sk-kepsek-judul"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div id="sk-kepsek-preview" class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height:560px;"></div>
                        <div class="text-end mt-2">
                            <a href="#" target="_blank" id="sk-kepsek-buka" class="btn btn-sm btn-light">
                                <i class="fa fa-up-right-from-square"></i> Buka di tab baru
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <table class="table table-borderless table-sm" style="font-size:14px;" id="sk-kepsek-data"></table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="sk-kepsek-setujui">
                    <i class="fa fa-check"></i> Setujui Surat
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    let suratAktif = null;
    const $modal = $('#modal-surat-keluar-kepsek');

    $(document).on('click', '.btn-detail-surat-keluar', function () {
        suratAktif = $(this).data('surat');

        $('#sk-kepsek-judul').text(suratAktif.judul || '');

        const $t = $('#sk-kepsek-data').empty();
        $.each(suratAktif.baris, function (label, nilai) {
            $t.append($('<tr>').append(
                $('<td class="text-muted" style="width:130px;">').text(label),
                $('<td>').text(nilai ?? '-')
            ));
        });

        const $box = $('#sk-kepsek-preview').empty();
        $('#sk-kepsek-buka').toggle(!!suratAktif.pdf).attr('href', suratAktif.pdf || '#');

        if (suratAktif.pdf) {
            $box.html($('<iframe>', { src: suratAktif.pdf, style: 'width:100%;height:100%;border:0;', title: 'Preview surat keluar' }));
        } else {
            $box.html('<div class="text-center text-muted"><i class="fa fa-file-circle-exclamation fa-2x mb-2"></i><div>PDF surat belum tersedia.</div></div>');
        }

        $('#sk-kepsek-setujui').prop('disabled', false).html('<i class="fa fa-check"></i> Setujui Surat');
        $modal.modal('show');
    });

    $modal.on('hidden.bs.modal', () => $('#sk-kepsek-preview').empty());

    $('#sk-kepsek-setujui').on('click', async function () {
        if (!suratAktif || !await konfirmasi('Setujui surat keluar ini?')) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

        $.ajax({
            url: suratAktif.url_setujui,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                $modal.modal('hide');
                alert(res.message || 'Surat berhasil disetujui.');
                location.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Gagal menyetujui surat keluar.');
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Setujui Surat');
            }
        });
    });
});
</script>
@endpush
