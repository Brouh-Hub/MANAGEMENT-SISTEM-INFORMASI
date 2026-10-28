<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
        }

        .sertifikat {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .background {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
        }

        .field {
            position: absolute;
            line-height: 1.15;
            white-space: normal;
        }
    </style>
</head>

<body>

@php
    $pkl = \App\Models\Pkl::with('kerjasama.mitra')
        ->whereHas('siswa', function ($q) use ($sertifikat) {
            if (filled($sertifikat->nis)) {
                $q->where('nis', $sertifikat->nis);
            } else {
                $q->whereNull('nis')->where('nama', $sertifikat->nama_penerima);
            }
        })
        ->latest('id')
        ->first();

    $principal = $template['principal'] ?? [];

    $isi = [
        'nomor_sertifikat' => $sertifikat->nomor_sertifikat ?? '-',
        'nama_penerima' => $sertifikat->nama_penerima ?? '-',
        'nis' => $sertifikat->nis ?? '-',

        'ttl' => (
            $sertifikat->tempat_lahir &&
            $sertifikat->tanggal_lahir
        )
            ? $sertifikat->tempat_lahir . ' / ' .
                \Carbon\Carbon::parse(
                    $sertifikat->tanggal_lahir
                )->translatedFormat('d F Y')
            : '-',

        'konsentrasi' => $sertifikat->konsentrasi ?? '-',

        'mitra' =>
            $pkl?->kerjasama?->mitra?->nama_perusahaan
            ?? $sertifikat->mitra
            ?? '-',

        'tanggal_mulai' => $pkl?->tanggal_mulai
            ? \Carbon\Carbon::parse(
                $pkl->tanggal_mulai
            )->translatedFormat('d F Y')
            : '-',

        'tanggal_selesai' => $pkl?->tanggal_selesai
            ? \Carbon\Carbon::parse(
                $pkl->tanggal_selesai
            )->translatedFormat('d F Y')
            : '-',

        'predikat' => strtoupper(
            $sertifikat->predikat ?? '-'
        ),

        /*
        | Kepala Sekolah
        */

        'kepala_jabatan' =>
            $principal['jabatan'] ?? 'KEPALA SEKOLAH',

        'kepala_sekolah' =>
            $principal['sekolah'] ?? '',

        'kepala_nama' =>
            $principal['nama'] ?? '',

        'kepala_nip' =>
            $principal['nip'] ?? '',

        /*
        | Kepala / Pembimbing Tempat PKL
        */

        'pimpinan_jabatan' =>
            $pkl?->pembimbing_pkl
                ? 'PEMBIMBING PKL'
                : 'KEPALA / PEMBIMBING',

        'pimpinan_nama' =>
            $pkl?->pembimbing_pkl ?? '',

        'pimpinan_nip' => '',
    ];
@endphp

<div class="sertifikat">

    <img
        src="{{ $bg }}"
        class="background"
        alt=""
    >

    @foreach ($posisi as $key => $field)

        @php
            if (isset($field['text'])) {
                $text = $field['text'];

$replacements = [

    '{{nomor_sertifikat}}'
        => $isi['nomor_sertifikat'],

    '{{nama}}'
        => $isi['nama_penerima'],

    '{{nama_penerima}}'
        => $isi['nama_penerima'],

    '{{nis}}'
        => $isi['nis'],

    '{{ttl}}'
        => $isi['ttl'],

    '{{konsentrasi}}'
        => $isi['konsentrasi'],

    '{{tempat_pkl}}'
        => $isi['mitra'],

    '{{mitra}}'
        => $isi['mitra'],

    '{{tanggal_mulai}}'
        => $isi['tanggal_mulai'],

    '{{tanggal_selesai}}'
        => $isi['tanggal_selesai'],

    '{{predikat}}'
        => $isi['predikat'],

    '{{kepala_jabatan}}'
        => $isi['kepala_jabatan'],

    '{{kepala_sekolah}}'
        => $isi['kepala_sekolah'],

    '{{kepala_nama}}'
        => $isi['kepala_nama'],

    '{{kepala_nip}}'
        => $isi['kepala_nip'],

    '{{pimpinan_jabatan}}'
        => $isi['pimpinan_jabatan'],

    '{{pimpinan_nama}}'
        => $isi['pimpinan_nama'],

    '{{pimpinan_nip}}'
        => $isi['pimpinan_nip'],
];

                $text = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $text
                );
            } else {
                $text = $isi[$field['field'] ?? ''] ?? '';
            }

            if ($text === null) {
                $text = '';
            }

            $style = [
                'left:' . ($field['x'] ?? 0) . '%',
                'top:' . ($field['y'] ?? 0) . '%',
                'width:' . ($field['w'] ?? 100) . '%',
                'font-size:' . ($field['size'] ?? 12) . 'pt',
                'color:' . ($field['color'] ?? '#222222'),
            ];

            if (($field['align'] ?? 'left') === 'center') {
                $style[] = 'text-align:center';
            } elseif (($field['align'] ?? 'left') === 'right') {
                $style[] = 'text-align:right';
            }

            if (!empty($field['bold'])) {
                $style[] = 'font-weight:bold';
            }

            if (!empty($field['italic'])) {
                $style[] = 'font-style:italic';
            }

            if (!empty($field['underline'])) {
                $style[] = 'text-decoration:underline';
            }

            if (!empty($field['serif'])) {
                $style[] = 'font-family:DejaVu Serif, serif';
            }

            if (!empty($field['upper'])) {
                $text = strtoupper($text);
            }
        @endphp

        @if(!empty($field['line']))
            <div
                class="field"
                style="left:{{ $field['x'] ?? 0 }}%;top:{{ $field['y'] ?? 0 }}%;width:{{ $field['w'] ?? 100 }}%;border-top:1px solid {{ $field['color'] ?? '#111111' }}"
            ></div>
        @elseif(trim((string) $text) !== '')
            <div
                class="field"
                style="{{ implode(';', $style) }}"
            >
                {!! nl2br(e($text)) !!}
            </div>
        @endif

    @endforeach

</div>

</body>
</html>
