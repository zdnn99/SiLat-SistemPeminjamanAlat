<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nama_lengkap' => 'SISWA',
            'nomor_induk' => '12345',
            'peran' => 'siswa',
            'kelas_jurusan' => 'XI PPLG A',
            'password' => Hash::make('siswa123'),
            'pin' => Hash::make('654321'),
        ]);

        // 3. Bikin Akun Guru (Login pakai NIP: 19800101)
        User::create([
            'nama_lengkap' => 'Bapak Guru Fulan',
            'nomor_induk' => '19800101',
            'peran' => 'guru_karyawan',
            'kelas_jurusan' => null,
            'password' => Hash::make('guru123'),
            'pin' => Hash::make('112233'),
        ]);

        $this->call(AdminSeeder::class);
        $this->call(AlatSeeder::class);
        // Data peminjaman contoh dimatikan: sekarang pengajuan nyata masuk ke dashboard admin.
        // $this->call(DemoPeminjamanSeeder::class);
    }
}