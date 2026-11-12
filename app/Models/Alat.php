<?php
// app/Models/Alat.php

namespace App\Models;

use App\Models\Concerns\RenumbersOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory, RenumbersOnDelete;

    protected $table = 'alat';

    protected $fillable = [
        'nama_alat',
        'subjudul',
        'slug',
        'deskripsi',
        'stok',
        'foto',
        'foto_kiri',
        'foto_kanan',
        'kelengkapan',
        'aturan',
    ];

    protected $casts = [
        'kelengkapan' => 'array',
        'aturan' => 'array',
    ];

    /**
     * Awalan foto bawaan per slug (nama file di public/images/alat/, ditambah 1, 2, 3).
     * Hanya untuk alat dari katalog awal. Slug tidak boleh diubah karena dipakai sebagai kunci.
     */
    private const PREFIKS_FOTO_BAWAAN = [
        'sony-mirrorless'       => 'sony',
        'canon-dslr'            => 'cannon',
        'drone-dokumentasi-1'   => 'drone1-',
        'drone-dokumentasi-2'   => 'drone2-',
        'handycam'              => 'handycam',
        'gimbal-stabilizer'     => 'gimbal',
        'tripod'                => 'tripod',
        'handphone-dokumentasi' => 'ipone',
    ];

    /**
     * Semua riwayat peminjaman untuk alat ini.
     */
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }

    /**
     * Jumlah unit alat yang sedang dipinjam/diajukan (belum selesai/ditolak).
     */
    public function getJumlahSedangDipinjamAttribute(): int
    {
        if ($this->relationLoaded('peminjaman')) {
            return $this->peminjaman
                ->whereIn('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam'])
                ->count();
        }

        return $this->peminjaman()
            ->whereIn('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam'])
            ->count();
    }

    /**
     * Sisa stok yang benar-benar bisa dipinjam saat ini.
     */
    public function getStokTersediaAttribute(): int
    {
        return max(0, $this->stok - $this->jumlah_sedang_dipinjam);
    }

    /**
     * True kalau masih ada unit yang bisa dipinjam.
     */
    public function getTersediaAttribute(): bool
    {
        return $this->stok_tersedia > 0;
    }

    public function getStatusKetersediaanAttribute(): string
    {
        if ($this->tersedia) {
            return 'Tersedia';
        }

        if ($this->relationLoaded('peminjaman')) {
            return $this->peminjaman->contains('status', 'dipinjam')
                ? 'Dipinjam'
                : 'Dipesan';
        }

        return $this->peminjaman()->where('status', 'dipinjam')->exists()
            ? 'Dipinjam'
            : 'Dipesan';
    }

    /**
     * Subjudul yang tampil di bawah nama alat.
     * Dipakai nilai dari kolom subjudul (alat baru). Alat lama yang kolomnya masih kosong
     * memakai daftar bawaan di bawah, lalu terakhir deskripsi.
     */
    public function getSubjudulAttribute($nilai): string
    {
        if (filled($nilai)) {
            return $nilai;
        }

        return [
            'sony-mirrorless' => 'Sony A6000',
            'canon-dslr' => 'Canon EOS 1200D',
            'drone-dokumentasi-1' => 'DJI Mini 2',
            'drone-dokumentasi-2' => 'DJI Mavic Air 2',
            'handycam' => 'Sony HDR-CX405',
            'gimbal-stabilizer' => 'Rekaman Mulus Saat Bergerak',
            'tripod' => 'Penopang Kamera Dan Handphone',
            'handphone-dokumentasi' => 'iPhone 13',
        ][$this->slug] ?? ($this->deskripsi ?? '');
    }

    public function getFotoUrlAttribute(): string
    {
        if (! $this->foto) {
            return '';
        }

        if (str_starts_with($this->foto, 'uploads/')) {
            return asset($this->foto);
        }

        $base = 'images/alat/' . $this->foto . '1';
        if (file_exists(public_path($base . '.webp'))) {
            return asset($base . '.webp');
        }

        return asset($base . '.jpg');
    }

    /**
     * Galeri tiga slot foto: [utama, kiri, kanan].
     * Tiap slot memakai unggahan admin kalau ada, kalau tidak memakai foto bawaan,
     * dan kalau keduanya tidak ada hasilnya null (ditampilkan sebagai placeholder).
     * Dipakai bersama oleh halaman peminjaman dan halaman kelola alat.
     */
    public function getGaleriAttribute(): array
    {
        $prefiks = self::PREFIKS_FOTO_BAWAAN[$this->slug ?? ''] ?? null;

        return collect(['foto', 'foto_kiri', 'foto_kanan'])
            ->map(function (string $kolom, int $indeks) use ($prefiks) {
                $path = $this->getAttribute($kolom);

                if ($path && str_starts_with($path, 'uploads/')) {
                    return asset($path);
                }

                if ($prefiks === null) {
                    return null;
                }

                $dasar = 'images/alat/' . $prefiks . ($indeks + 1);

                foreach (['.webp', '.jpg'] as $ekstensi) {
                    if (file_exists(public_path($dasar . $ekstensi))) {
                        return asset($dasar . $ekstensi);
                    }
                }

                return null;
            })
            ->all();
    }
}