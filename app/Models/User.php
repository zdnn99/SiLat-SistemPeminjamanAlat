<?php

namespace App\Models;

use App\Models\Concerns\RenumbersOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, RenumbersOnDelete;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'nomor_induk',
        'peran',
        'kelas_jurusan',
        'password',
        'pin',
        'status_akun', // menunggu (belum disetujui admin), aktif, nonaktif, atau minta_hapus
        'foto_profil',
        'permintaan_hapus_pada',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin', // PIN disembunyikan juga demi keamanan saat query API/data
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password'              => 'hashed',
            'permintaan_hapus_pada' => 'datetime',
        ];
    }

    /**
     * URL foto profil pengguna atau null jika belum ada.
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_profil ? asset('uploads/profil/' . $this->foto_profil) : null;
    }

    /**
     * Semua peminjaman milik user ini.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }
}
