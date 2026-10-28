{{-- Tombol aksi disposisi sesuai status. Pakai: @include('admin.disposisi.partials.tombol-status', ['d' => $d]) --}}
@if($d->status === 'pending')
    <button class="btn-aksi btn-aksi-terima btn-status" data-id="{{ $d->id }}" data-next="diterima">
        <i class="fa fa-hand"></i> Terima
    </button>
@elseif($d->status === 'diterima')
    <button class="btn-aksi btn-aksi-proses btn-status" data-id="{{ $d->id }}" data-next="diproses">
        <i class="fa fa-play"></i> Mulai Proses
    </button>
@elseif($d->status === 'diproses')
    <button class="btn-aksi btn-aksi-selesai btn-buka-laporan" data-id="{{ $d->id }}">
        <i class="fa fa-check"></i> Tandai Selesai
    </button>
@elseif($d->status === 'selesai')
    <span class="aksi-done"><i class="fa fa-circle-check"></i> Selesai</span>
@else
    <span class="aksi-done">{{ $d->status_label }}</span>
@endif
