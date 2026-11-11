<?php

namespace App\Support;

class Banner
{
    /**
     * URL banner yang dipakai di hero.
     * Memakai gambar terbaru yang diunggah admin (public/uploads/banner/banner.*),
     * dan kembali ke images/banner.jpg bila belum pernah diganti.
     * Parameter ?v= berisi waktu ubah file supaya browser langsung memuat gambar baru.
     */
    public static function url(): string
    {
        $file = glob(public_path('uploads/banner/banner.*')) ?: [];

        if (! empty($file)) {
            return asset('uploads/banner/' . basename($file[0])) . '?v=' . filemtime($file[0]);
        }

        if (file_exists(public_path('images/banner.webp'))) {
            return asset('images/banner.webp');
        }

        return asset('images/banner.jpg');
    }
}