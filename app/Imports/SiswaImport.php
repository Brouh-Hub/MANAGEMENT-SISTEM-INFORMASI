<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public int $berhasil = 0;
    public array $gagal = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $baris = $i + 2; // baris 1 = heading

            $nisRaw = $row['nis'] ?? '';
            $nis   = is_numeric($nisRaw) ? number_format($nisRaw, 0, '', '') : trim((string) $nisRaw);
            $nama  = trim((string) ($row['nama'] ?? ''));
            $kelas = strtoupper(trim((string) ($row['kelas'] ?? '')));

            if ($nis === '' && $nama === '' && $kelas === '') continue; // baris kosong

            if ($nis === '' || $nama === '' || $kelas === '') {
                $this->gagal[] = "Baris $baris: NIS, Nama, atau Kelas kosong";
                continue;
            }

            $jurusan = Siswa::deteksiJurusan($row['jurusan'] ?? null, $kelas);
            if (!$jurusan) {
                $this->gagal[] = "Baris $baris: jurusan tidak dikenali ($kelas)";
                continue;
            }

            // NIS sama = update, bukan dobel
            Siswa::updateOrCreate(
                ['nis' => $nis],
                ['nama' => $nama, 'kelas' => $kelas, 'jurusan' => $jurusan]
            );
            $this->berhasil++;
        }
    }
}
