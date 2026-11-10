<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\AlatSeeder;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PeminjamanAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_can_log_in(): void
    {
        $this->seed(AdminSeeder::class);

        $this->withSession([
            '_token' => 'test-token',
            'url.intended' => route('dashboard'),
        ])
            ->post(route('login'), [
                '_token' => 'test-token',
                'nomor_induk' => '00000',
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(User::where('nomor_induk', '00000')->first());
    }

    public function test_admin_is_redirected_away_from_the_regular_dashboard(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = User::where('nomor_induk', '00000')->firstOrFail();

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Permintaan Peminjaman')
            ->assertSee('Peralatan Tersedia')
            ->assertSee(route('admin.peminjaman.riwayat'))
            ->assertDontSee('hover:underline')
            ->assertDontSee('Peminjaman Saya');
    }

    public function test_admin_dashboard_renders_every_equipment_card_and_manage_link(): void
    {
        $this->seed([AdminSeeder::class, AlatSeeder::class]);
        $admin = User::where('nomor_induk', '00000')->firstOrFail();

        $response = $this->withoutVite()
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('lg:grid-cols-4', false)
            ->assertSee('Sony A6000')
            ->assertSee('Canon EOS 1200D')
            ->assertDontSee('Kamera DSLR tingkat pemula untuk memotret acara');

        $this->assertSame(8, substr_count($response->getContent(), 'id="alat-'));
        $this->assertSame(8, substr_count($response->getContent(), '/admin/alat/'));
    }

    public function test_welcome_page_shows_current_equipment_availability(): void
    {
        [, $peminjaman] = $this->createAdminAndPeminjaman();
        Alat::create([
            'nama_alat' => 'Tripod Stabilizer',
            'slug' => 'tripod',
            'stok' => 1,
        ]);

        $this->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertSee('id="alat-' . $peminjaman->alat->slug . '"', false)
            ->assertSee('Dipinjam')
            ->assertSee('Tidak Tersedia')
            ->assertSee('Tersedia')
            ->assertDontSee('Dipesan');
    }

    public function test_admin_dashboard_hides_waiting_for_pickup_summary_card_but_keeps_row_status(): void
    {
        [$admin] = $this->createAdminAndPeminjaman('menunggu_pengambilan');

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Pengambilan')
            ->assertSee('Lihat Semua Pengajuan')
            ->assertDontSee('data-filter="menunggu_pengambilan"', false);
    }

    public function test_admin_can_approve_a_pending_request(): void
    {
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->post(route('admin.peminjaman.setujui', $peminjaman), [
                '_token' => 'test-token',
                'catatan_pengambilan' => 'Ambil alat di lab Multimedia.',
            ])
            ->assertRedirect(route('peminjaman.show', $peminjaman));

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'menunggu_pengambilan',
            'catatan_pengambilan' => 'Ambil alat di lab Multimedia.',
            'catatan_admin' => null,
        ]);
    }

    public function test_admin_can_mark_an_approved_request_as_picked_up(): void
    {
        Carbon::setTestNow('2026-11-05 08:00:00');
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman('menunggu_pengambilan');
        $peminjaman->catatan_pengambilan = 'Ambil alat di lab';
        $peminjaman->save();

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Tandai Alat Diambil')
            ->assertSee('Ambil alat di lab');

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->post(route('admin.peminjaman.ambil', $peminjaman), [
                '_token' => 'test-token',
                'catatan_admin' => 'Kamera dan tas diserahkan lengkap.',
            ])
            ->assertRedirect(route('peminjaman.show', $peminjaman));

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'dipinjam',
            'catatan_pengambilan' => 'Ambil alat di lab',
            'catatan_admin' => 'Kamera dan tas diserahkan lengkap.',
        ]);

        $this->withoutVite()
            ->actingAs($peminjaman->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kembali 7 hari lagi')
            ->assertDontSee('Kamera dan tas diserahkan lengkap.');

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Kamera dan tas diserahkan lengkap.')
            ->assertDontSee('Ambil alat di lab');

        Carbon::setTestNow();
    }

    public function test_admin_cannot_mark_an_item_picked_up_before_the_requested_start_date(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman('menunggu_pengambilan');

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->from(route('peminjaman.show', $peminjaman))
            ->post(route('admin.peminjaman.ambil', $peminjaman), [
                '_token' => 'test-token',
                'catatan_admin' => 'Alat diserahkan lengkap.',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'menunggu_pengambilan',
        ]);

        Carbon::setTestNow();
    }

    public function test_admin_must_provide_a_reason_to_reject_a_request(): void
    {
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->from(route('peminjaman.show', $peminjaman))
            ->post(route('admin.peminjaman.tolak', $peminjaman), [
                '_token' => 'test-token',
                'catatan_admin' => '   ',
            ])
            ->assertSessionHasErrors('catatan_admin');

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'menunggu_persetujuan',
        ]);
    }

    public function test_active_pending_request_blocks_another_date_until_rejected(): void
    {
        Carbon::setTestNow('2026-11-06 08:00:00');
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman();
        $peminjamLain = User::create([
            'nama_lengkap' => 'Peminjam Kedua',
            'nomor_induk' => 'SISWA002',
            'peran' => 'siswa',
            'kelas_jurusan' => 'XI Multimedia',
            'password' => 'password',
            'pin' => '123456',
        ]);
        $dataPengajuan = [
            'alat_id' => $peminjaman->alat_id,
            'tanggal_pinjam' => '2026-11-20',
            'tanggal_kembali' => '2026-11-25',
            'keperluan' => 'Dokumentasi kegiatan sekolah.',
            'persetujuan' => '1',
        ];

        $this->actingAs($peminjamLain)
            ->from(route('peminjaman.create', $peminjaman->alat->slug))
            ->post(route('peminjaman.store'), $dataPengajuan)
            ->assertSessionHasErrors('alat_id');

        $peminjaman->update(['status' => 'ditolak']);

        $this->actingAs($peminjamLain)
            ->post(route('peminjaman.store'), $dataPengajuan)
            ->assertRedirect(route('peminjaman.show', Peminjaman::latest('id')->first()));

        $this->assertSame(2, Peminjaman::count());
        Carbon::setTestNow();
    }

    public function test_pending_request_reserves_equipment_without_marking_it_as_borrowed(): void
    {
        [, $peminjaman] = $this->createAdminAndPeminjaman();

        $this->assertFalse($peminjaman->alat->tersedia);
        $this->assertSame('Dipesan', $peminjaman->alat->status_ketersediaan);

        $peminjaman->update(['status' => 'dipinjam']);

        $this->assertSame('Dipinjam', $peminjaman->alat->fresh()->status_ketersediaan);
    }

    public function test_admin_can_record_a_return_and_return_date(): void
    {
        Carbon::setTestNow('2026-11-04 10:30:00');
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman('dipinjam');
        $peminjaman->catatan_admin = 'Telah diserahkan';
        $peminjaman->save();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->post(route('admin.peminjaman.kembali', $peminjaman), [
                '_token' => 'test-token',
                'kondisi_alat_kembali' => 'Baik',
                'catatan_admin' => 'Kelengkapan sesuai.',
            ])
            ->assertRedirect(route('peminjaman.show', $peminjaman));

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'selesai',
            'kondisi_alat_kembali' => 'Baik',
            'catatan_admin' => 'Catatan pengembalian: Kelengkapan sesuai.',
        ]);
        $this->assertSame('2026-11-04', $peminjaman->fresh()->tanggal_dikembalikan->toDateString());

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Catatan pengembalian: Kelengkapan sesuai.')
            ->assertDontSee('Telah diserahkan');

        Carbon::setTestNow();
    }

    public function test_admin_can_view_the_borrower_details_and_actions(): void
    {
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman();

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Data Peminjam')
            ->assertSee('Tindakan')
            ->assertSee('Setujui Pengajuan')
            ->assertSee('Cantumkan Tempat Pengambilan Barang')
            ->assertSee('Tulis Alasan Penolakan')
            ->assertSee('Pengajuan ini menunggu keputusan admin');
    }

    public function test_admin_can_edit_equipment_details_and_rules(): void
    {
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman();

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('admin.alat.edit', $peminjaman->alat))
            ->assertOk()
            ->assertSee('Kelola Sony Mirrorless Camera')
            ->assertSee('Kelengkapan alat')
            ->assertSee('Aturan khusus alat');

        $this->actingAs($admin)
            ->put(route('admin.alat.update', $peminjaman->alat), [
                'nama_alat' => 'Sony Mirrorless Camera Baru',
                'deskripsi' => 'Kamera untuk dokumentasi kegiatan sekolah.',
                'stok' => 1,
                'kelengkapan' => "Body kamera\nBaterai cadangan",
                'aturan' => "Gunakan tali pengaman\nKembalikan dalam keadaan bersih",
            ])
            ->assertRedirect(route('admin.alat.edit', $peminjaman->alat));

        $this->assertSame(['Body kamera', 'Baterai cadangan'], $peminjaman->alat->fresh()->kelengkapan);
        $this->assertSame(['Gunakan tali pengaman', 'Kembalikan dalam keadaan bersih'], $peminjaman->alat->fresh()->aturan);
    }

    public function test_admin_history_search_accepts_peminjaman_code(): void
    {
        [$admin, $peminjaman] = $this->createAdminAndPeminjaman();

        $this->withoutVite()
            ->actingAs($admin)
            ->get(route('admin.peminjaman.riwayat', ['q' => 'PMJ-001']))
            ->assertOk()
            ->assertSee('PJM-0001')
            ->assertSee('Peminjam Penguji')
            ->assertSee(route('peminjaman.show', Peminjaman::first()))
            ->assertSee('data-detail-url="' . route('peminjaman.show', Peminjaman::first()) . '"', false)
            ->assertSee('Cari kode peminjaman')
            ->assertSee('Sony A6000')
            ->assertSee('Lihat Detail')
            ->assertDontSee('>Detail</th>', false)
            ->assertSee('Temui admin Ekskul Multimedia secara langsung.')
            ->assertSee('Mulmedska SMK Negeri 5 Surakarta')
            ->assertSee('z-10 h-4 w-4', false)
            ->assertSee('lg:hidden', false);

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.riwayat', ['q' => 'PJM-9999']))
            ->assertOk()
            ->assertSee('Tidak ada riwayat yang cocok dengan pencarian atau filter ini.');
    }

    /** @return array{User, Peminjaman} */
    private function createAdminAndPeminjaman(string $status = 'menunggu_persetujuan'): array
    {
        $admin = User::create([
            'nama_lengkap' => 'Admin Penguji',
            'nomor_induk' => 'ADMIN001',
            'peran' => 'admin',
            'password' => 'password',
            'pin' => '123456',
        ]);
        $peminjam = User::create([
            'nama_lengkap' => 'Peminjam Penguji',
            'nomor_induk' => 'SISWA001',
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
            'user_id' => $peminjam->id,
            'alat_id' => $alat->id,
            'tanggal_pinjam' => '2026-11-05',
            'tanggal_kembali' => '2026-11-12',
            'keperluan' => 'Dokumentasi kegiatan sekolah.',
            'status' => $status,
        ]);

        return [$admin, $peminjaman];
    }
}
