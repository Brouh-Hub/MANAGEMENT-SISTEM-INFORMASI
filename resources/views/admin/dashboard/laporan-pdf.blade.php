<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Surat</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111; }
        h1 { font-size: 15px; margin: 0; text-align: center; }
        h2 { font-size: 12px; margin: 18px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #999; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .kop .nama { font-size: 14px; font-weight: bold; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #888; padding: 4px 6px; vertical-align: top; }
        th { background: #E8EBF8; text-align: left; }
        .r { text-align: right; }
        .c { text-align: center; }
        .ringkas td { border: none; padding: 2px 0; }
        .kotak { width: 100%; }
        .kotak td { width: 25%; border: 1px solid #ccc; text-align: center; padding: 8px 4px; }
        .kotak .angka { font-size: 18px; font-weight: bold; display: block; }
        .ttd { margin-top: 28px; width: 100%; }
        .ttd td { border: none; }
    </style>
</head>
<body>

    <div class="kop">
        <div class="nama">{{ $sekolah->nama ?? 'SMK Negeri 4 Tanjungpinang' }}</div>
        @if($sekolah?->alamat)
            <div class="muted">{{ $sekolah->alamat }}</div>
        @endif
    </div>

    <h1>LAPORAN REKAP SURAT MASUK DAN SURAT KELUAR</h1>
    <p class="c muted" style="margin:4px 0 0;">
        Periode {{ $dari->translatedFormat('d F Y') }} s.d. {{ $sampai->translatedFormat('d F Y') }}
    </p>

    <h2>Ringkasan</h2>
    <table class="kotak">
        <tr>
            <td><span class="angka">{{ $laporan['total_masuk'] }}</span>Surat Masuk</td>
            <td><span class="angka">{{ $laporan['total_keluar'] }}</span>Surat Keluar</td>
            <td><span class="angka">{{ $laporan['persen_selesai'] }}%</span>Disposisi Selesai</td>
            <td><span class="angka">{{ $laporan['prioritas_tinggi'] }}</span>Prioritas Tinggi</td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <th style="width:50%;">Status Surat Masuk</th>
            <th>Status Surat Keluar</th>
        </tr>
        <tr>
            <td>
                <table class="ringkas">
                    @foreach($laporan['masuk_per_status'] as $label => $jumlah)
                        <tr><td>{{ $label }}</td><td class="r">{{ $jumlah }}</td></tr>
                    @endforeach
                </table>
            </td>
            <td>
                <table class="ringkas">
                    @foreach($laporan['keluar_per_status'] as $label => $jumlah)
                        <tr><td>{{ $label }}</td><td class="r">{{ $jumlah }}</td></tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <h2>Rekap per Bulan</h2>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="r" style="width:18%;">Surat Masuk</th>
                <th class="r" style="width:18%;">Surat Keluar</th>
                <th class="r" style="width:18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan['per_bulan'] as $b)
                <tr>
                    <td>{{ $b['label'] }}</td>
                    <td class="r">{{ $b['masuk'] }}</td>
                    <td class="r">{{ $b['keluar'] }}</td>
                    <td class="r">{{ $b['masuk'] + $b['keluar'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th>Total</th>
                <th class="r">{{ $laporan['total_masuk'] }}</th>
                <th class="r">{{ $laporan['total_keluar'] }}</th>
                <th class="r">{{ $laporan['total_masuk'] + $laporan['total_keluar'] }}</th>
            </tr>
        </tbody>
    </table>

    <h2>Daftar Surat Masuk</h2>
    <table>
        <thead>
            <tr>
                <th class="c" style="width:4%;">No</th>
                <th style="width:12%;">Tgl Terima</th>
                <th style="width:11%;">No. Agenda</th>
                <th style="width:16%;">Nomor Surat</th>
                <th style="width:18%;">Pengirim</th>
                <th>Perihal</th>
                <th style="width:13%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan['daftar_masuk'] as $i => $s)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($s['tanggal'])->format('d-m-Y') }}</td>
                    <td>{{ $s['no_agenda'] }}</td>
                    <td>{{ $s['nomor'] }}</td>
                    <td>{{ $s['pihak'] }}</td>
                    <td>{{ $s['perihal'] }}</td>
                    <td>{{ $s['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="c muted">Tidak ada surat masuk pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Daftar Surat Keluar</h2>
    <table>
        <thead>
            <tr>
                <th class="c" style="width:4%;">No</th>
                <th style="width:12%;">Tgl Surat</th>
                <th style="width:18%;">Nomor Surat</th>
                <th style="width:20%;">Tujuan</th>
                <th>Perihal</th>
                <th style="width:15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan['daftar_keluar'] as $i => $s)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($s['tanggal'])->format('d-m-Y') }}</td>
                    <td>{{ $s['nomor'] }}</td>
                    <td>{{ $s['pihak'] }}</td>
                    <td>{{ $s['perihal'] }}</td>
                    <td>{{ $s['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="c muted">Tidak ada surat keluar pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width:60%;"></td>
            <td class="c">
                Tanjungpinang, {{ now()->translatedFormat('d F Y') }}<br>
                Dicetak oleh,<br><br><br><br>
                <b>{{ $dicetak->name ?? '-' }}</b><br>
                {{ $dicetak->jabatan ?? '' }}
            </td>
        </tr>
    </table>

</body>
</html>