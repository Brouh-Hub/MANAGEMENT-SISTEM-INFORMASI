<?php

return [

    'templates' => [

        'pkl' => [

            'label' => 'Sertifikat PKL',
            'image' => 'images/sertifikat/pkl.png',

            /*
            |--------------------------------------------------------------------------
            | KEPALA SEKOLAH
            |--------------------------------------------------------------------------
            */

            'principal' => [
                'jabatan' => 'KEPALA SEKOLAH',
                'sekolah' => 'SMK NEGERI 4 TANJUNGPINANG',
                'nama' => 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
                'nip' => 'NIP 19770421 200502 2 011',
            ],

            /*
            |--------------------------------------------------------------------------
            | FIELD SERTIFIKAT
            |--------------------------------------------------------------------------
            */

            'fields' => [

                'nomor' => [
                    'text' => 'Nomor : {{nomor_sertifikat}}',
                    'x' => 30, 'y' => 22.5, 'w' => 40,
                    'size' => 10, 'color' => '#334155', 'align' => 'center',
                ],

                'diberikan_kepada' => [
                    'text' => 'Diberikan Kepada',
                    'x' => 30, 'y' => 28.5, 'w' => 40,
                    'size' => 11, 'color' => '#111111', 'align' => 'center',
                    'italic' => true,
                ],

                'nama' => [
                    'text' => '{{nama_penerima}}',
                    'x' => 15, 'y' => 33, 'w' => 70,
                    'size' => 24, 'color' => '#0b4f8a', 'align' => 'center',
                    'bold' => true, 'serif' => true,
                ],

                'nama_garis' => [
                    'line' => true,
                    'x' => 28, 'y' => 41.5, 'w' => 44,
                    'color' => '#0b4f8a',
                ],

                'nis' => [
                    'text' => 'Nomor Induk Siswa : {{nis}}',
                    'x' => 20, 'y' => 44, 'w' => 60,
                    'size' => 10, 'color' => '#222222', 'align' => 'center',
                ],

                'ttl' => [
                    'text' => 'Tempat / Tgl. Lahir : {{ttl}}',
                    'x' => 20, 'y' => 47.4, 'w' => 60,
                    'size' => 10, 'color' => '#222222', 'align' => 'center',
                ],

                'konsentrasi' => [
                    'text' => 'Konsentrasi Keahlian : {{konsentrasi}}',
                    'x' => 20, 'y' => 50.8, 'w' => 60,
                    'size' => 10, 'color' => '#222222', 'align' => 'center',
                ],

                'kalimat_pkl' => [
                    'text' => "Telah selesai mengikuti Praktik Kerja Lapangan (PKL)
pada {{tempat_pkl}} dengan predikat {{predikat}}",
                    'x' => 12, 'y' => 55.5, 'w' => 76,
                    'size' => 10.5, 'color' => '#111111', 'align' => 'center',
                    'bold' => true,
                ],

                'periode_pkl' => [
                    'text' => 'Tanggal : {{tanggal_mulai}} s.d. {{tanggal_selesai}}',
                    'x' => 20, 'y' => 63.5, 'w' => 60,
                    'size' => 10, 'color' => '#222222', 'align' => 'center',
                ],

                /*
                |--------------------------------------------------------------------------
                | KIRI - KEPALA SEKOLAH
                |--------------------------------------------------------------------------
                */

                'kepala_jabatan' => [
                    'text' => 'KEPALA SEKOLAH',
                    'x' => 21, 'y' => 69, 'w' => 30,
                    'size' => 10, 'color' => '#111111', 'align' => 'center',
                    'bold' => true,
                ],

                'kepala_sekolah' => [
                    'text' => 'SMK NEGERI 4 TANJUNGPINANG',
                    'x' => 21, 'y' => 72, 'w' => 30,
                    'size' => 8.5, 'color' => '#222222', 'align' => 'center',
                ],

                'kepala_garis' => [
                    'line' => true,
                    'x' => 24, 'y' => 80, 'w' => 24,
                    'color' => '#111111',
                ],

                'kepala_nama' => [
                    'text' => 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
                    'x' => 21, 'y' => 80.8, 'w' => 30,
                    'size' => 8.5, 'color' => '#111111', 'align' => 'center',
                    'bold' => true,
                ],

                'kepala_nip' => [
                    'text' => 'NIP 19770421 200502 2 011',
                    'x' => 21, 'y' => 84, 'w' => 30,
                    'size' => 8, 'color' => '#222222', 'align' => 'center',
                ],

                /*
                |--------------------------------------------------------------------------
                | KANAN - KEPALA / PEMBIMBING TEMPAT PKL
                |--------------------------------------------------------------------------
                */

                'pkl_jabatan' => [
                    'text' => 'KEPALA/PIMPINAN TEMPAT PKL',
                    'x' => 51, 'y' => 69, 'w' => 30,
                    'size' => 9.5, 'color' => '#111111', 'align' => 'center',
                    'bold' => true,
                ],

                'pkl_perusahaan' => [
                    'text' => '{{tempat_pkl}}',
                    'x' => 51, 'y' => 72, 'w' => 30,
                    'size' => 8.5, 'color' => '#222222', 'align' => 'center',
                ],

                'pkl_garis' => [
                    'line' => true,
                    'x' => 54, 'y' => 80, 'w' => 24,
                    'color' => '#111111',
                ],

                'pkl_nama' => [
                    'text' => '{{pimpinan_nama}}',
                    'x' => 51, 'y' => 80.8, 'w' => 30,
                    'size' => 8.5, 'color' => '#111111', 'align' => 'center',
                    'bold' => true,
                ],

                'pkl_nip' => [
                    'text' => 'NIP {{pimpinan_nip}}',
                    'x' => 51, 'y' => 84, 'w' => 30,
                    'size' => 8, 'color' => '#222222', 'align' => 'center',
                ],

            ],
        ],

    ],

];
