<?php

namespace Database\Seeders;

use App\Models\SuratKeluarField;
use Illuminate\Database\Seeder;

class SuratKeluarFieldSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'nomor_surat',   'label' => 'Nomor Surat',   'type' => 'text'],
            ['key' => 'perihal',       'label' => 'Perihal',       'type' => 'text'],
            ['key' => 'tujuan',        'label' => 'Tujuan',        'type' => 'text'],
            ['key' => 'alamat_tujuan', 'label' => 'Alamat Tujuan', 'type' => 'textarea'],
            ['key' => 'keterangan',    'label' => 'Keterangan',    'type' => 'textarea'],
            ['key' => 'tanggal_surat', 'label' => 'Tanggal Surat', 'type' => 'date'],
        ];

        foreach ($defaults as $i => $d) {
            SuratKeluarField::updateOrCreate(
                ['key' => $d['key']],
                $d + ['is_default' => true, 'is_active' => true, 'urutan' => $i + 1]
            );
        }
    }
}
