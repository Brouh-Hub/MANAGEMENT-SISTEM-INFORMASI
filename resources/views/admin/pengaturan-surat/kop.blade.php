{{-- Kop surat. Teks diatur di menu "Pengaturan Surat". Atribut data-kop dipakai preview langsung. --}}
@php $tinggi = (int) ($p->tinggi_logo ?: 78); @endphp
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:{{ $tinggi + 17 }}px; text-align:center; vertical-align:middle;">
            <img src="{{ $p->logoKiriUrl() }}" data-kop-logo="kiri" style="height:{{ $tinggi }}px; width:auto;">
        </td>
        <td style="text-align:center; vertical-align:middle; line-height:1.2;">
            <div data-kop="kop_baris_1" style="font-size:13pt;{{ $p->kop_baris_1 ? '' : ' display:none;' }}">{{ $p->kop_baris_1 }}</div>
            <div data-kop="kop_baris_2" style="font-size:15pt; font-weight:bold;{{ $p->kop_baris_2 ? '' : ' display:none;' }}">{{ $p->kop_baris_2 }}</div>
            <div data-kop="kop_baris_3" style="font-size:15pt; font-weight:bold;{{ $p->kop_baris_3 ? '' : ' display:none;' }}">{{ $p->kop_baris_3 }}</div>
            <div data-kop="kop_alamat" style="font-size:9.5pt;{{ $p->kop_alamat ? '' : ' display:none;' }}">{{ $p->kop_alamat }}</div>
            <div style="font-size:9.5pt;{{ $p->kop_telepon ? '' : ' display:none;' }}" data-kop-baris="telepon">Telepon <span data-kop="kop_telepon">{{ $p->kop_telepon }}</span></div>
            <div style="font-size:9.5pt;{{ ($p->kop_laman || $p->kop_email) ? '' : ' display:none;' }}" data-kop-baris="kontak">
                <span data-kop-bagian="laman" style="{{ $p->kop_laman ? '' : 'display:none;' }}">Laman <b data-kop="kop_laman">{{ $p->kop_laman }}</b></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span data-kop-bagian="email" style="{{ $p->kop_email ? '' : 'display:none;' }}">Pos-el <b data-kop="kop_email">{{ $p->kop_email }}</b></span>
            </div>
        </td>
        <td style="width:{{ $tinggi + 17 }}px; text-align:center; vertical-align:middle;">
            <img src="{{ $p->logoKananUrl() }}" data-kop-logo="kanan" style="height:{{ $tinggi }}px; width:auto;">
        </td>
    </tr>
</table>
<div style="border-top:3px solid #000; margin-top:5px; margin-bottom:12px;"></div>
