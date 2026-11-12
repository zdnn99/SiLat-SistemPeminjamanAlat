<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatatanAdminFormatTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'nama_lengkap' => 'Admin Penguji',
            'nomor_induk' => '00000',
            'peran' => 'admin',
            'password' => 'password',
            'pin' => '123456',
        ]);
    }

    private function createSiswa(): User
    {
        return User::create([
            'nama_lengkap' => 'Siswa Penguji',
            'nomor_induk' => '1234567890',
            'peran' => 'siswa',
            'kelas_jurusan' => 'XI Multimedia',
            'password' => 'password',
            'pin' => '123456',
        ]);
    }

    private function createAlat(): Alat
    {
        return Alat::create([
            'nama_alat' => 'Kamera Canon 3000D',
            'slug' => 'kamera-canon-3000d',
            'subjudul' => 'Kamera DSLR',
            'deskripsi' => 'Kamera DSLR untuk praktek multimedia.',
            'stok' => 1,
        ]);
    }

    public function test_catatan_serah_terima_diformat_huruf_depan_besar_dan_selain_itu_kecil(): void
    {
        Carbon::setTestNow('2026-11-12 08:00:00');
        $admin = $this->createAdmin();
        $siswa = $this->createSiswa();
        $alat = $this->createAlat();

        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-11-12',
            'tanggal_kembali' => '2026-11-13',
            'keperluan' => 'Praktek fotografi di sekolah',
            'status' => 'menunggu_pengambilan',
            'catatan_pengambilan' => 'Ambil di ruang lab.',
        ]);

        // Input huruf campuran & besar di serah terima
        $response = $this->actingAs($admin)->post(route('admin.peminjaman.ambil', $peminjaman), [
            'catatan_admin' => 'kAMERA DAN LENSA DISERAHKAN LENGKAP.',
        ]);

        $response->assertRedirect(route('peminjaman.show', $peminjaman));

        $peminjaman->refresh();
        $this->assertSame('Kamera dan lensa diserahkan lengkap.', $peminjaman->catatan_admin);
    }

    public function test_edit_catatan_diformat_huruf_depan_besar_dan_selain_itu_kecil(): void
    {
        Carbon::setTestNow('2026-11-12 08:00:00');
        $admin = $this->createAdmin();
        $siswa = $this->createSiswa();
        $alat = $this->createAlat();

        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-11-12',
            'tanggal_kembali' => '2026-11-13',
            'tanggal_diambil' => '2026-11-12 08:00:00',
            'keperluan' => 'Praktek fotografi di sekolah',
            'status' => 'dipinjam',
            'catatan_admin' => 'Catatan lama',
        ]);

        // Admin mengedit catatan dengan input acak huruf besar/kecil
        $response = $this->actingAs($admin)->patch(route('admin.peminjaman.catatan', $peminjaman), [
            'catatan' => 'bARANG DALAM KONDISI BAIK DAN LENGKAP',
        ]);

        $response->assertRedirect();

        $peminjaman->refresh();
        $this->assertSame('Barang dalam kondisi baik dan lengkap', $peminjaman->catatan_admin);
    }

    public function test_edit_catatan_pada_status_selesai_tetap_menjaga_prefix_dan_format(): void
    {
        Carbon::setTestNow('2026-11-12 08:00:00');
        $admin = $this->createAdmin();
        $siswa = $this->createSiswa();
        $alat = $this->createAlat();

        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-11-12',
            'tanggal_kembali' => '2026-11-13',
            'tanggal_diambil' => '2026-11-12 08:00:00',
            'tanggal_dikembalikan' => '2026-11-13 14:00:00',
            'keperluan' => 'Praktek fotografi di sekolah',
            'status' => 'selesai',
            'kondisi_alat_kembali' => 'Baik',
            'catatan_admin' => 'Catatan pengembalian: Sudah dicek',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.peminjaman.catatan', $peminjaman), [
            'catatan' => 'sELURUH KELENGKAPAN SESUAI DAN BERSIH',
        ]);

        $response->assertRedirect();

        $peminjaman->refresh();
        $this->assertSame('Catatan pengembalian: Seluruh kelengkapan sesuai dan bersih', $peminjaman->catatan_admin);
    }
}
