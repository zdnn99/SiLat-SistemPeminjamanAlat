<?php

namespace App\Models\Concerns;

use App\Services\DatabaseSequenceService;

trait RenumbersOnDelete
{
    /**
     * Otomatis dipanggil oleh Eloquent saat model yang menggunakan trait ini di-boot.
     * Ketika satu data dihapus, urutan ID di database akan dirapikan kembali
     * agar data setelahnya mengisi kekosongan (misal 13 jadi 12) dan tidak ada gap.
     */
    public static function bootRenumbersOnDelete(): void
    {
        static::deleted(function () {
            app(DatabaseSequenceService::class)->renumberAll();
        });
    }
}
