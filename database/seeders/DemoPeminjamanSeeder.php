<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['nomor_induk' => '12345'],
            [
                'nama_lengkap' => 'SISWA DEMO',
                'peran' => 'siswa',
                'kelas_jurusan' => 'XI PPLG A',
                'password' => Hash::make('siswa123'),
                'pin' => Hash::make('654321'),
            ]
        );

        $alat = Alat::firstOrCreate(
            ['slug' => 'sony-mirrorless'],
            [
                'nama_alat' => 'Sony Mirrorless Camera',
                'deskripsi' => 'Sony A6000',
                'stok' => 5,
            ]
        );

        $skenario = [
            [
                'keperluan' => 'Data dummy untuk pratinjau halaman detail peminjaman.',
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_kembali' => now()->addDays(7)->toDateString(),
                'status' => 'menunggu_persetujuan',
                'catatan_admin' => null,
                'kondisi_alat_kembali' => null,
            ],
            [
                'keperluan' => 'Demo: sedang dipinjam dan belum jatuh tempo.',
                'tanggal_pinjam' => now()->subDays(2)->toDateString(),
                'tanggal_kembali' => now()->addDays(5)->toDateString(),
                'status' => 'dipinjam',
                'catatan_admin' => null,
                'kondisi_alat_kembali' => null,
            ],
            [
                'keperluan' => 'Demo: batas pengembalian hari ini.',
                'tanggal_pinjam' => now()->subDays(7)->toDateString(),
                'tanggal_kembali' => now()->toDateString(),
                'status' => 'dipinjam',
                'catatan_admin' => null,
                'kondisi_alat_kembali' => null,
            ],
            [
                'keperluan' => 'Demo: terlambat mengembalikan alat.',
                'tanggal_pinjam' => now()->subDays(10)->toDateString(),
                'tanggal_kembali' => now()->subDays(3)->toDateString(),
                'status' => 'dipinjam',
                'catatan_admin' => null,
                'kondisi_alat_kembali' => null,
            ],
            [
                'keperluan' => 'Demo: peminjaman selesai dan alat sudah dikembalikan.',
                'tanggal_pinjam' => now()->subDays(14)->toDateString(),
                'tanggal_kembali' => now()->subDays(7)->toDateString(),
                'status' => 'selesai',
                'catatan_admin' => null,
                'kondisi_alat_kembali' => 'Baik',
            ],
            [
                'keperluan' => 'Demo: pengajuan ditolak admin.',
                'tanggal_pinjam' => now()->subDays(5)->toDateString(),
                'tanggal_kembali' => now()->addDays(2)->toDateString(),
                'status' => 'ditolak',
                'catatan_admin' => 'Contoh alasan penolakan: jadwal penggunaan alat tidak tersedia.',
                'kondisi_alat_kembali' => null,
            ],
        ];

        foreach ($skenario as $data) {
            Peminjaman::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'alat_id' => $alat->id,
                    'keperluan' => $data['keperluan'],
                ],
                [
                    'tanggal_pinjam' => $data['tanggal_pinjam'],
                    'tanggal_kembali' => $data['tanggal_kembali'],
                    'status' => $data['status'],
                    'catatan_admin' => $data['catatan_admin'],
                    'kondisi_alat_kembali' => $data['kondisi_alat_kembali'],
                ]
            );
        }
    }
}
