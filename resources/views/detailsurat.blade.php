{{-- resources/views/surat/partials/detail-drawer.blade.php --}}
<div class="drawer">
  <div class="drawer-head">
    <div>
      <span class="nomor">{{ $surat->nomor_agenda ?? $surat->nomor_surat }}</span>
      <span class="sub">Surat {{ ucfirst($surat->jenis) }}</span>
    </div>
    <a href="{{ route('surat.index', ['jenis' => $surat->jenis]) }}" class="drawer-close">&times;</a>
  </div>

  <div class="drawer-body">
    <div class="field">
      <label>Perihal</label>
      <div class="val">{{ $surat->perihal }}</div>
    </div>
    <div class="field">
      <label>{{ $surat->jenis === 'masuk' ? 'Asal Surat' : 'Tujuan Surat' }}</label>
      <div class="val">{{ $surat->jenis === 'masuk' ? $surat->asal_instansi : $surat->tujuan_instansi }}</div>
    </div>
    <div class="field">
      <label>Tanggal Surat / {{ $surat->jenis === 'masuk' ? 'Diterima' : 'Dikirim' }}</label>
      <div class="val">
        {{ $surat->tanggal_surat->translatedFormat('d M Y') }} /
        {{ ($surat->tanggal_diterima ?? $surat->tanggal_kirim)->translatedFormat('d M Y') }}
      </div>
    </div>

    @if ($surat->lampiran)
      <div class="field">
        <label>Lampiran</label>
        <a class="attachment" href="{{ Storage::url($surat->lampiran) }}" target="_blank">
          {{ basename($surat->lampiran) }}
        </a>
      </div>
    @endif

    <div class="timeline">
      <div class="timeline-title">Riwayat Disposisi</div>
      @foreach ($surat->disposisi as $item)
        <div class="t-item {{ $loop->last && !$item->menunggu ? '' : 'done' }}">
          <div class="t-dot"></div>
          <div>
            <div class="t-name">{{ $item->judul }}</div>
            <div class="t-note">{{ $item->catatan ?? 'Belum ada catatan' }}</div>
            <div class="t-date">{{ $item->created_at?->translatedFormat('d M Y, H.i') ?? '—' }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="drawer-foot">
    <a href="{{ Storage::url($surat->lampiran) }}" class="btn-ghost">Unduh</a>
    <button type="button" class="btn-primary"
            onclick="document.getElementById('modal-disposisi').classList.add('open')">
      Disposisikan
    </button>
  </div>
</div>
