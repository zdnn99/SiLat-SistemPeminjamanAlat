<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageUploadWebpTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        foreach (glob(public_path('uploads/alat/*tes-webp*')) ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob(public_path('uploads/profil/*user-*')) ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob(public_path('uploads/banner/banner.*')) ?: [] as $f) {
            @unlink($f);
        }
        parent::tearDown();
    }

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

    public function test_upload_foto_alat_otomatis_menjadi_format_webp(): void
    {
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('kamera.jpg', 800, 600);

        $response = $this->actingAs($admin)->post(route('admin.alat.store'), [
            'nama_alat'   => 'Kamera Tes Webp',
            'subjudul'    => 'Kamera untuk pengujian webp',
            'deskripsi'   => 'Deskripsi lengkap alat untuk pengujian konversi format gambar webp.',
            'tersedia'    => '1',
            'foto'        => $file,
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $alat = Alat::where('nama_alat', 'Kamera Tes Webp')->first();
        $this->assertNotNull($alat);
        $this->assertStringEndsWith('.webp', $alat->foto);
        $this->assertFileExists(public_path($alat->foto));

        $info = getimagesize(public_path($alat->foto));
        $this->assertSame('image/webp', $info['mime']);

        @unlink(public_path($alat->foto));
    }

    public function test_upload_foto_profil_otomatis_menjadi_format_webp(): void
    {
        $user = $this->createSiswa();

        $file = UploadedFile::fake()->image('avatar.png', 500, 500);

        $response = $this->actingAs($user)->post(route('profil.foto'), [
            'foto' => $file,
        ]);

        $response->assertSessionHas('sukses_foto');

        $user->refresh();
        $this->assertNotNull($user->foto_profil);
        $this->assertStringEndsWith('.webp', $user->foto_profil);

        $filePath = public_path('uploads/profil/' . $user->foto_profil);
        $this->assertFileExists($filePath);

        $info = getimagesize($filePath);
        $this->assertSame('image/webp', $info['mime']);

        @unlink($filePath);
    }

    public function test_upload_banner_otomatis_menjadi_format_webp(): void
    {
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('banner_baru.jpg', 1200, 400);

        $response = $this->actingAs($admin)->post(route('admin.banner.update'), [
            'banner' => $file,
        ]);

        $response->assertRedirect('/admin/dashboard');

        $bannerPath = public_path('uploads/banner/banner.webp');
        $this->assertFileExists($bannerPath);

        $info = getimagesize($bannerPath);
        $this->assertSame('image/webp', $info['mime']);

        @unlink($bannerPath);
    }
}
