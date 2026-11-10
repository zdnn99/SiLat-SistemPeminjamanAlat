<?php
// database/seeders/AdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::updateOrCreate(
                ['nomor_induk' => '00000'],
                [
                    'nama_lengkap' => 'Administrator Sistem',
                    'peran' => 'admin',
                    'kelas_jurusan' => null,
                    'password' => Hash::make('password123'),
                    'pin' => Hash::make('123456'),
                ]
            );

            User::where('peran', 'admin')
                ->where('id', '!=', $admin->id)
                ->update(['peran' => 'guru_karyawan']);
        });
    }
}