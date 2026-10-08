<?php
// app/Models/Alat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';

    protected $fillable = [
        'nama_alat',
        'slug',
        'deskripsi',
        'stok',
        'foto',
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
        return $this->peminjaman()
            ->whereIn('status', ['menunggu_persetujuan', 'dipinjam'])
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
}