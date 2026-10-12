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
                    'x' => 30,
                    'y' => 20.5,
                    'w' => 40,
                    'size' => 10,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                'diberikan_kepada' => [
                    'text' => 'Diberikan Kepada',
                    'x' => 30,
                    'y' => 25.5,
                    'w' => 40,
                    'size' => 11,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                'nama' => [
                    'text' => 'Nama : {{nama_penerima}}',
                    'x' => 20,
                    'y' => 31.5,
                    'w' => 60,
                    'size' => 18,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                'nis' => [
                    'text' => 'Nomor Induk Siswa : {{nis}}',
                    'x' => 28,
                    'y' => 40.5,
                    'w' => 50,
                    'size' => 10,
                    'color' => '#222222',
                    'align' => 'left',
                    'bold' => false,
                ],

                'ttl' => [
                    'text' => 'Tempat / Tgl. Lahir : {{ttl}}',
                    'x' => 28,
                    'y' => 43.8,
                    'w' => 55,
                    'size' => 10,
                    'color' => '#222222',
                    'align' => 'left',
                    'bold' => false,
                ],

                'konsentrasi' => [
                    'text' => 'Konsentrasi Keahlian : {{konsentrasi}}',
                    'x' => 28,
                    'y' => 47.1,
                    'w' => 58,
                    'size' => 10,
                    'color' => '#222222',
                    'align' => 'left',
                    'bold' => false,
                ],

                'kalimat_pkl' => [
                    'text' => 'Telah selesai mengikuti Praktik Kerja Lapangan (PKL) pada {{tempat_pkl}} dengan predikat {{predikat}}.',
                    'x' => 15,
                    'y' => 52.5,
                    'w' => 70,
                    'size' => 10.5,
                    'color' => '#222222',
                    'align' => 'center',
                    'bold' => false,
                ],

                'periode_pkl' => [
                    'text' => 'Tanggal : {{tanggal_mulai}} s.d. {{tanggal_selesai}}',
                    'x' => 25,
                    'y' => 56.5,
                    'w' => 50,
                    'size' => 10,
                    'color' => '#222222',
                    'align' => 'center',
                    'bold' => false,
                ],

                'tempat_pkl' => [
                    'text' => 'Tempat PKL : {{tempat_pkl}}',
                    'x' => 20,
                    'y' => 60.5,
                    'w' => 60,
                    'size' => 10.5,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                'predikat' => [
                    'text' => 'Predikat : {{predikat}}',
                    'x' => 20,
                    'y' => 64,
                    'w' => 60,
                    'size' => 10.5,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                /*
                |--------------------------------------------------------------------------
                | KIRI - KEPALA SEKOLAH
                |--------------------------------------------------------------------------
                */

                'kepala_jabatan' => [
                    'text' => 'KEPALA SEKOLAH',
                    'x' => 5,
                    'y' => 71.5,
                    'w' => 40,
                    'size' => 10,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                'kepala_sekolah' => [
                    'text' => 'SMK NEGERI 4 TANJUNGPINANG',
                    'x' => 5,
                    'y' => 74.5,
                    'w' => 40,
                    'size' => 8.5,
                    'color' => '#222222',
                    'align' => 'center',
                    'bold' => false,
                ],

                'kepala_garis' => [
                    'text' => '____________________________',
                    'x' => 7,
                    'y' => 80.5,
                    'w' => 36,
                    'size' => 9,
                    'color' => '#111111',
                    'align' => 'center',
                ],

                'kepala_nama' => [
                    'text' => 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
                    'x' => 5,
                    'y' => 83.5,
                    'w' => 40,
                    'size' => 8.5,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                    'underline' => true,
                ],

                'kepala_nip' => [
                    'text' => 'NIP 19770421 200502 2 011',
                    'x' => 5,
                    'y' => 86.5,
                    'w' => 40,
                    'size' => 8,
                    'color' => '#222222',
                    'align' => 'center',
                    'bold' => false,
                ],

                /*
                |--------------------------------------------------------------------------
                | KANAN - KEPALA / PEMBIMBING TEMPAT PKL
                |--------------------------------------------------------------------------
                */

                'pkl_jabatan' => [
                    'text' => 'KEPALA/PIMPINAN TEMPAT PKL',
                    'x' => 55,
                    'y' => 71.5,
                    'w' => 40,
                    'size' => 9.5,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                ],

                'pkl_perusahaan' => [
                    'text' => '{{tempat_pkl}}',
                    'x' => 55,
                    'y' => 74.5,
                    'w' => 40,
                    'size' => 8.5,
                    'color' => '#222222',
                    'align' => 'center',
                    'bold' => false,
                ],

                'pkl_garis' => [
                    'text' => '____________________________',
                    'x' => 57,
                    'y' => 80.5,
                    'w' => 36,
                    'size' => 9,
                    'color' => '#111111',
                    'align' => 'center',
                ],

                'pkl_nama' => [
                    'text' => '{{pimpinan_nama}}',
                    'x' => 55,
                    'y' => 83.5,
                    'w' => 40,
                    'size' => 8.5,
                    'color' => '#111111',
                    'align' => 'center',
                    'bold' => true,
                    'underline' => true,
                ],

                'pkl_nip' => [
                    'text' => '{{pimpinan_nip}}',
                    'x' => 55,
                    'y' => 86.5,
                    'w' => 40,
                    'size' => 8,
                    'color' => '#222222',
                    'align' => 'center',
                    'bold' => false,
                ],

            ],
        ],

    ],

];
