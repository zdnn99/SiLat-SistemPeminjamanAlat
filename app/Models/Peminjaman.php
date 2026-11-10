<?php
// app/Models/Peminjaman.php

namespace App\Models;

use App\Models\Concerns\RenumbersOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory, RenumbersOnDelete;

    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'alat_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_diambil',
        'tanggal_dikembalikan',
        'keperluan',
        'status', // menunggu_persetujuan | menunggu_pengambilan | dipinjam | selesai | ditolak
        'catatan_admin',
        'catatan_pengambilan',
        'kondisi_alat_kembali',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali' => 'date',
        'tanggal_diambil' => 'datetime',
        'tanggal_dikembalikan' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $peminjaman) {
            if ($peminjaman->isDirty('status') && $peminjaman->status === 'dipinjam' && ! $peminjaman->tanggal_diambil) {
                $peminjaman->tanggal_diambil = now();
            }
            if ($peminjaman->isDirty('status') && $peminjaman->status === 'selesai' && ! $peminjaman->tanggal_dikembalikan) {
                $peminjaman->tanggal_dikembalikan = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class);
    }
}