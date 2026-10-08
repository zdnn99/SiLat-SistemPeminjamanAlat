<?php
// database/seeders/AdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['nomor_induk' => 'admin'], // kunci pengecekan: kalau nomor_induk ini sudah ada, tidak dibuat ulang
            [
                'nama_lengkap' => 'Administrator',
                'peran' => 'admin',
                'kelas_jurusan' => null,
                'password' => Hash::make('admin123'),
                'pin' => Hash::make('123456'),
            ]
        );
    }
}