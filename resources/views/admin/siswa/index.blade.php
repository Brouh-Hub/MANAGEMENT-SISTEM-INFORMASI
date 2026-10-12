    @extends('layouts.main')

    @section('content')
    <div class="card-panel">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0 fw-bold">Data Siswa</h5>
            <div class="d-flex gap-2">
                <select id="filterJurusan" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusan as $j) <option value="{{ $j }}">{{ $j }}</option> @endforeach
                </select>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="fa fa-file-excel"></i> Import Excel
                </button>
                <button class="btn btn-sm btn-primary" id="btnTambah"><i class="fa fa-plus"></i> Tambah Siswa</button>
            </div>
        </div>

        <table id="tabelSiswa" class="table table-hover w-100">
            <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jurusan</th><th>Aksi</th></tr></thead>
        </table>
    </div>

    {{-- Modal tambah/edit --}}
    <div class="modal fade" id="modalSiswa" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h6 class="modal-title" id="judulModal">Tambah Siswa</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="siswaId">
                <div class="mb-2"><label class="form-label">NIS</label><input id="nis" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Nama</label><input id="nama" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Kelas</label><input id="kelas" class="form-control" placeholder="XI RPL 1"></div>
                <div class="mb-2"><label class="form-label">Jurusan</label>
                    <select id="jurusan" class="form-select">
                        @foreach($jurusan as $j) <option value="{{ $j }}">{{ $j }}</option> @endforeach
                    </select></div>
                <div id="errBox" class="text-danger small"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" id="btnSimpan">Simpan</button></div>
        </div></div>
    </div>

    {{-- Modal import --}}
    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Import Data Siswa</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="small text-muted mb-2">
                    Baris pertama harus berisi heading: <b>NIS, Nama, Kelas, Jurusan</b>.
                    Kolom Jurusan boleh kosong, nanti dibaca dari Kelas (contoh: XI RPL 1).
                    Format kolom NIS sebaiknya <b>Text</b> supaya angka 0 di depan tidak hilang.
                </p>
                <input type="file" id="fileExcel" class="form-control" accept=".xlsx,.xls,.csv">
                <div id="hasilImport" class="small mt-3"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-success" id="btnImport">Import</button></div>
        </div></div>
    </div>
    @endsection

    @push('scripts')
    <script>
    $(function () {
        const modal = new bootstrap.Modal('#modalSiswa');
        const tabel = $('#tabelSiswa').DataTable({
            ajax: '{{ route("siswa.data") }}',
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'nis' }, { data: 'nama' }, { data: 'kelas' },
                { data: 'jurusan', render: d => `<span class="badge bg-primary">${d}</span>` },
                { data: null, orderable: false, render: r => `
                    <button class="btn btn-sm btn-warning btn-edit" data-row='${JSON.stringify(r)}'><i class="fa fa-pen"></i></button>
                    <button class="btn btn-sm btn-danger btn-hapus" data-id="${r.id}"><i class="fa fa-trash"></i></button>` }
            ]
        });

        $('#filterJurusan').on('change', function () {
            tabel.column(4).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
        });

        $('#btnTambah').on('click', function () {
            $('#judulModal').text('Tambah Siswa');
            $('#siswaId, #nis, #nama, #kelas, #errBox').val('').text('');
            modal.show();
        });

        $(document).on('click', '.btn-edit', function () {
            const r = $(this).data('row');
            $('#judulModal').text('Edit Siswa');
            $('#siswaId').val(r.id); $('#nis').val(r.nis); $('#nama').val(r.nama);
            $('#kelas').val(r.kelas); $('#jurusan').val(r.jurusan); $('#errBox').text('');
            modal.show();
        });

        $('#btnSimpan').on('click', function () {
            const id = $('#siswaId').val();
            $.ajax({
                url: id ? '{{ url("siswa") }}/' + id : '{{ route("siswa.store") }}',
                method: id ? 'PUT' : 'POST',
                data: { nis: $('#nis').val(), nama: $('#nama').val(), kelas: $('#kelas').val(), jurusan: $('#jurusan').val() },
                success: () => { modal.hide(); tabel.ajax.reload(); },
                error: xhr => {
                    const e = xhr.responseJSON?.errors || {};
                    $('#errBox').html(Object.values(e).flat().join('<br>'));
                }
            });
        });

        $(document).on('click', '.btn-hapus', function () {
            if (!confirm('Hapus siswa ini?')) return;
            $.ajax({ url: '{{ url("siswa") }}/' + $(this).data('id'), method: 'DELETE', success: () => tabel.ajax.reload() });
        });

        $('#btnImport').on('click', function () {
            const file = $('#fileExcel')[0].files[0];
            if (!file) return alert('Pilih file dulu');
            const fd = new FormData(); fd.append('file', file);
            $.ajax({
                url: '{{ route("siswa.import") }}', method: 'POST', data: fd,
                processData: false, contentType: false,
                success: res => {
                    let html = `<div class="text-success fw-bold">${res.message}</div>`;
                    if (res.gagal.length) html += '<ul class="text-danger mt-2">' + res.gagal.map(g => `<li>${g}</li>`).join('') + '</ul>';
                    $('#hasilImport').html(html);
                    tabel.ajax.reload();
                },
                error: xhr => $('#hasilImport').html(`<div class="text-danger">${xhr.responseJSON?.message || 'Import gagal'}</div>`)
            });
        });
    });
    </script>
    @endpush
