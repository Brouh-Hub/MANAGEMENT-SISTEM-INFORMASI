{{-- resources/views/surat/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Surat Masuk & Keluar')
@section('breadcrumb')
  Administrasi / <b>Surat Masuk & Keluar</b>
@endsection

@section('content')

  <div class="page-head">
    <div>
      <h1>Surat Masuk & Keluar</h1>
      <p>Catat, disposisikan, dan arsipkan surat sekolah dalam satu alur.</p>
    </div>
    <a href="{{ route('surat.create', ['jenis' => $jenis]) }}" class="btn-primary">
      + Catat Surat Baru
    </a>
  </div>

  {{-- Tab Masuk / Keluar --}}
  <div class="tab-row">
    <a href="{{ route('surat.index', ['jenis' => 'masuk']) }}"
       class="tab {{ $jenis === 'masuk' ? 'active' : '' }}">
      Surat Masuk <span class="count">{{ $jumlahMasuk }}</span>
    </a>
    <a href="{{ route('surat.index', ['jenis' => 'keluar']) }}"
       class="tab {{ $jenis === 'keluar' ? 'active' : '' }}">
      Surat Keluar <span class="count">{{ $jumlahKeluar }}</span>
    </a>
  </div>

  <div class="panel">

    {{-- Toolbar filter --}}
    <form method="GET" action="{{ route('surat.index') }}" class="toolbar">
      <input type="hidden" name="jenis" value="{{ $jenis }}">
      <input class="search" type="text" name="q" value="{{ request('q') }}"
             placeholder="Cari nomor surat, perihal, atau {{ $jenis === 'masuk' ? 'pengirim' : 'tujuan' }}…">

      <select name="kategori">
        <option value="">Semua Kategori</option>
        @foreach ($kategoriList as $kategori)
          <option value="{{ $kategori->id }}" @selected(request('kategori') == $kategori->id)>
            {{ $kategori->nama }}
          </option>
        @endforeach
      </select>

      <input class="date-input" type="text" name="rentang_tanggal" value="{{ request('rentang_tanggal') }}">

      <button type="submit" class="btn-ghost">Terapkan</button>
      <a href="{{ route('surat.export', request()->query()) }}" class="btn-ghost">Ekspor</a>
    </form>

    <div class="work-area">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No. Agenda</th>
              <th>{{ $jenis === 'masuk' ? 'Asal Surat' : 'Tujuan Surat' }}</th>
              <th>Perihal</th>
              <th>{{ $jenis === 'masuk' ? 'Tgl Terima' : 'Tgl Kirim' }}</th>
              <th>Sifat</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($suratList as $surat)
              <tr class="{{ $suratAktif && $suratAktif->id === $surat->id ? 'selected' : '' }}"
                  onclick="window.location='{{ route('surat.show', $surat) }}'">
                <td class="nomor">{{ $surat->nomor_agenda ?? '–' }}</td>
                <td>
                  {{ $jenis === 'masuk' ? $surat->asal_instansi : $surat->tujuan_instansi }}
                  <div class="sub">{{ $surat->kategori->nama }}</div>
                </td>
                <td class="perihal">{{ $surat->perihal }}</td>
                <td>{{ $surat->tanggal_diterima?->translatedFormat('d M Y') ?? $surat->tanggal_kirim?->translatedFormat('d M Y') }}</td>
                <td class="sifat {{ $surat->sifat === 'penting' ? 'penting' : '' }}">
                  {{ ucfirst($surat->sifat) }}
                </td>
                <td>
                  <span class="badge {{ $surat->status_class }}">
                    <span class="badge-dot"></span>{{ $surat->status_label }}
                  </span>
                </td>
                <td class="row-actions" onclick="event.stopPropagation()">
                  <a href="{{ route('surat.show', $surat) }}">Lihat</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" style="text-align:center; color:var(--muted); padding:30px;">
                  Belum ada surat {{ $jenis }} yang tercatat.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="pagination">
          <span>Menampilkan {{ $suratList->firstItem() ?? 0 }}–{{ $suratList->lastItem() ?? 0 }} dari {{ $suratList->total() }} surat</span>
          {{ $suratList->onEachSide(1)->links() }}
        </div>
      </div>

      {{-- Drawer detail: hanya tampil kalau ada surat yang dipilih --}}
      @if ($suratAktif)
        @include('surat.partials.detail-drawer', ['surat' => $suratAktif])
      @endif
    </div>
  </div>

@endsection
