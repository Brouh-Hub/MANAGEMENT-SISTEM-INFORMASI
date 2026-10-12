<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Kepala Sekolah',
            'email'    => 'kepsek@sekolah.com',
            'password' => Hash::make('password123'),
            'role'     => 'super_admin',
            'jabatan'  => 'Kepala Sekolah',
        ]);

        User::create([
            'name'     => 'Waka Kurikulum',
            'email'    => 'wakakur@sekolah.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'jabatan'  => 'Waka Kurikulum',
        ]);

        User::create([
            'name'     => 'Tata Usaha',
            'email'    => 'tu@sekolah.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'jabatan'  => 'TU',
        ]);
    }
}
