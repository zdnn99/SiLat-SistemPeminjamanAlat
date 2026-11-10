<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\AlatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PeminjamanTanggalDikembalikanTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_catalog_item_can_open_the_loan_form(): void
    {
        $user = $this->createUser();
        $this->seed(AlatSeeder::class);

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('peminjaman.create', 'sony-mirrorless'))
            ->assertOk()
            ->assertSee('Sony Mirrorless Camera');

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sony A6000')
            ->assertDontSee('Kamera mirrorless ringkas dengan sensor APS-C');
    }

    public function test_dashboard_shows_the_date_the_item_was_returned(): void
    {
        Carbon::setTestNow('2026-10-21 10:30:00');

        $user = User::create([
            'nama_lengkap' => 'Penguji',
            'nomor_induk' => '1234567890',
            'peran' => 'siswa',
            'kelas_jurusan' => 'XI Multimedia',
            'password' => 'password',
            'pin' => '123456',
        ]);
        $alat = Alat::create([
            'nama_alat' => 'Sony Mirrorless Camera',
            'slug' => 'sony-mirrorless',
            'stok' => 1,
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $user->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-10-14',
            'tanggal_kembali' => '2026-10-28',
            'status' => 'dipinjam',
        ]);

        $peminjaman->status = 'selesai';
        $peminjaman->save();

        $this->assertSame('2026-10-21', $peminjaman->fresh()->tanggal_dikembalikan->toDateString());

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('21 Okt 2026')
            ->assertSee('Tanggal Dikembalikan')
            ->assertSee(route('peminjaman.create', 'sony-mirrorless'));

        Carbon::setTestNow();
    }

    public function test_approved_request_waiting_for_pickup_shows_admin_note_without_a_return_countdown(): void
    {
        Carbon::setTestNow('2026-11-04 10:30:00');

        $user = $this->createUser();
        $alat = $this->createAlat();
        $peminjaman = Peminjaman::create([
            'user_id' => $user->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-11-24',
            'tanggal_kembali' => '2026-12-07',
            'status' => 'menunggu_pengambilan',
            'catatan_pengambilan' => 'Pengambilan alat di lab.',
        ]);

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Pengambilan')
            ->assertDontSee('data-filter="menunggu_pengambilan"', false)
            ->assertSee('Pengambilan alat di lab.')
            ->assertDontSee('Kembali 33 hari lagi');

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Menunggu pengambilan')
            ->assertSee('Pengambilan alat di lab.');

        Carbon::setTestNow();
    }

    public function test_dashboard_shows_only_five_latest_loans_and_links_to_full_history(): void
    {
        Carbon::setTestNow('2026-10-21 10:30:00');

        $user = $this->createUser();
        $alat = $this->createAlat();

        foreach (range(1, 7) as $day) {
            Peminjaman::create([
                'user_id' => $user->id,
                'alat_id' => $alat->id,
                'tanggal_pinjam' => Carbon::parse('2026-10-01')->addDays($day)->toDateString(),
                'tanggal_kembali' => Carbon::parse('2026-10-01')->addDays($day + 7)->toDateString(),
                'status' => 'dipinjam',
            ]);
        }

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Menampilkan 5 Peminjaman Terbaru')
            ->assertSee('8 Okt 2026')
            ->assertDontSee('>2 Okt 2026<')
            ->assertSee(route('peminjaman.riwayat'))
            ->assertSee("infoFilterTeks.textContent = 'Menampilkan ' + tampil + ' Peminjaman.';", false)
            ->assertSee("linkRiwayat.classList.toggle('hidden', kode !== 'semua');", false);

        Carbon::setTestNow();
    }

    public function test_dashboard_shows_rejection_note_without_an_added_reason_prefix(): void
    {
        $user = $this->createUser();
        $alat = $this->createAlat();
        Peminjaman::create([
            'user_id' => $user->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali' => today()->addDays(7)->toDateString(),
            'status' => 'ditolak',
            'catatan_admin' => 'Alat akan dipakai untuk kegiatan sekolah.',
        ]);

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Alat akan dipakai untuk kegiatan sekolah.')
            ->assertDontSee('Alasan:');
    }

    public function test_history_page_can_filter_by_status_and_keeps_user_records_private(): void
    {
        $user = $this->createUser();
        $alat = $this->createAlat();
        $otherUser = $this->createUser('0987654321');

        Peminjaman::create([
            'user_id' => $user->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-10-10',
            'tanggal_kembali' => '2026-10-17',
            'status' => 'selesai',
        ]);
        Peminjaman::create([
            'user_id' => $user->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-10-11',
            'tanggal_kembali' => '2026-10-18',
            'status' => 'dipinjam',
        ]);
        Peminjaman::create([
            'user_id' => $otherUser->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-10-12',
            'tanggal_kembali' => '2026-10-19',
            'status' => 'selesai',
        ]);

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('peminjaman.riwayat', ['status' => 'selesai']))
            ->assertOk()
            ->assertSee('Riwayat Peminjaman')
            ->assertSee('Selesai')
            ->assertSee('10 Okt 2026')
            ->assertDontSee('>11 Okt 2026<')
            ->assertDontSee('>12 Okt 2026<');
    }

    private function createUser(string $nomorInduk = '1234567890'): User
    {
        return User::create([
            'nama_lengkap' => 'Penguji',
            'nomor_induk' => $nomorInduk,
            'peran' => 'siswa',
            'kelas_jurusan' => 'XI Multimedia',
            'password' => 'password',
            'pin' => '123456',
        ]);
    }

    private function createAlat(): Alat
    {
        return Alat::create([
            'nama_alat' => 'Sony Mirrorless Camera',
            'slug' => 'sony-mirrorless',
            'stok' => 1,
        ]);
    }
}