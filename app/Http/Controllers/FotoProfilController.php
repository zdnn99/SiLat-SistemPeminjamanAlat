<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;

class FotoProfilController extends Controller
{
    /**
     * Cadangan untuk foto profil yang tercatat di database tetapi file-nya tidak ada di disk.
     * Hanya terpanggil kalau file asli tidak ada: file yang ada dilayani langsung oleh web server.
     * Mengembalikan gambar inisial nama supaya tampilan tidak rusak.
     */
    public function cadangan(string $file): Response
    {
        $inisial = '?';

        // Nama file foto selalu berpola user-{id}-{waktu}.{ekstensi}
        if (preg_match('/^user-(\d+)-\d+\.(webp|jpe?g|png)$/i', $file, $cocok)) {
            $nama = User::whereKey((int) $cocok[1])->value('nama_lengkap');

            if ($nama) {
                $inisial = mb_strtoupper(mb_substr(trim($nama), 0, 1, 'UTF-8'), 'UTF-8');
            }
        }

        $inisial = htmlspecialchars($inisial, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
            . '<rect width="100" height="100" fill="#2563eb"/>'
            . '<text x="50" y="50" font-family="Arial, Helvetica, sans-serif" font-size="48" font-weight="700" '
            . 'fill="#ffffff" text-anchor="middle" dominant-baseline="central">' . $inisial . '</text>'
            . '</svg>';

        // no-store: begitu file asli dipulihkan, browser tidak menyimpan versi inisial ini
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }
}