<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\AlatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PeminjamanWaktuRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ambil_dan_kembali_mencatat_realtime_dan_tampil_di_detail(): void
    {
        Carbon::setTestNow('2026-11-09 09:00:00');

        $this->seed([AdminSeeder::class, AlatSeeder::class]);
        $admin = User::where('peran', 'admin')->firstOrFail();
        $siswa = User::create([
            'nama_lengkap' => 'Budi Santoso',
            'nomor_induk' => '12345',
            'peran' => 'siswa',
            'kelas_jurusan' => 'X RPL 1',
            'password' => 'password',
            'pin' => '123456',
        ]);

        $alat = Alat::where('slug', 'sony-mirrorless')->firstOrFail();

        // 1. Pengajuan dibuat pada 7 November 2026 20.28
        Carbon::setTestNow('2026-11-07 20:28:00');
        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-11-09',
            'tanggal_kembali' => '2026-11-13',
            'keperluan' => 'Untuk dokumentasi kegiatan sekolah di aula.',
            'status' => 'menunggu_pengambilan',
            'catatan_pengambilan' => 'Ambil di ruang multimedia.',
        ]);

        // Cek halaman detail sebelum diambil
        $this->withoutVite()
            ->actingAs($siswa)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('7 November 2026 20.28')
            ->assertSee('Mulai Pinjam');

        // 2. Admin mencatat pengambilan alat pada 9 November 2026 09.00
        Carbon::setTestNow('2026-11-09 09:00:00');
        $this->actingAs($admin)
            ->post(route('admin.peminjaman.ambil', $peminjaman), [
                'catatan_admin' => 'Alat diserahkan lengkap dan berfungsi baik.',
            ])
            ->assertRedirect(route('peminjaman.show', $peminjaman));

        $peminjaman->refresh();
        $this->assertSame('dipinjam', $peminjaman->status);
        $this->assertSame('2026-11-09 09:00:00', $peminjaman->tanggal_diambil->format('Y-m-d H:i:s'));

        // Cek tampilan detail bagi siswa dan admin saat sedang dipinjam
        $this->withoutVite()
            ->actingAs($siswa)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Barang Diambil')
            ->assertSee('9 November 2026 09.00')
            ->assertSee('7 November 2026 20.28');

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Barang Diambil')
            ->assertSee('9 November 2026 09.00')
            ->assertSee('7 November 2026 20.28');

        // 3. Admin mencatat pengembalian alat pada 13 November 2026 10.00
        Carbon::setTestNow('2026-11-13 10:00:00');
        $this->actingAs($admin)
            ->post(route('admin.peminjaman.kembali', $peminjaman), [
                'kondisi_alat_kembali' => 'Baik',
                'catatan_admin' => 'Dikembalikan tepat waktu dan lengkap.',
            ])
            ->assertRedirect(route('peminjaman.show', $peminjaman));

        $peminjaman->refresh();
        $this->assertSame('selesai', $peminjaman->status);
        $this->assertSame('2026-11-13 10:00:00', $peminjaman->tanggal_dikembalikan->format('Y-m-d H:i:s'));

        // Cek tampilan detail bagi siswa dan admin saat selesai
        $this->withoutVite()
            ->actingAs($siswa)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Barang Diambil')
            ->assertSee('9 November 2026 09.00')
            ->assertSee('Barang Dikembalikan')
            ->assertSee('13 November 2026 10.00')
            ->assertSee('7 November 2026 20.28');

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Barang Diambil')
            ->assertSee('9 November 2026 09.00')
            ->assertSee('Barang Dikembalikan')
            ->assertSee('13 November 2026 10.00')
            ->assertSee('7 November 2026 20.28');

        Carbon::setTestNow();
    }

    public function test_footer_seluruh_layout_terformat_atas_bawah(): void
    {
        $this->seed([AdminSeeder::class, AlatSeeder::class]);
        $siswa = User::create([
            'nama_lengkap' => 'Budi Santoso',
            'nomor_induk' => '12345',
            'peran' => 'siswa',
            'kelas_jurusan' => 'X RPL 1',
            'password' => 'password',
            'pin' => '123456',
        ]);

        $snippet = '<span class="mt-0.5 block font-bold text-blue-600">Temui admin Ekskul Multimedia secara langsung.</span>';

        // 1. Form peminjaman
        $this->withoutVite()
            ->actingAs($siswa)
            ->get(route('peminjaman.create', 'sony-mirrorless'))
            ->assertOk()
            ->assertSee($snippet, false);

        // 2. Profil
        $this->withoutVite()
            ->actingAs($siswa)
            ->get(route('profil.show'))
            ->assertOk()
            ->assertSee($snippet, false);

        // 3. Riwayat peminjaman siswa
        $this->withoutVite()
            ->actingAs($siswa)
            ->get(route('peminjaman.riwayat'))
            ->assertOk()
            ->assertSee($snippet, false);
    }
}
