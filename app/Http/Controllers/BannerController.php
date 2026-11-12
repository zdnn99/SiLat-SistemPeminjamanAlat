<?php

namespace App\Http\Controllers;

use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'banner' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'banner.required' => 'Pilih gambar banner terlebih dahulu.',
            'banner.image'    => 'File harus berupa gambar.',
            'banner.mimes'    => 'Format gambar harus JPG, PNG, atau WebP.',
            'banner.max'      => 'Ukuran gambar maksimal 4 MB.',
            'banner.uploaded' => 'Gambar gagal diunggah. Pastikan ukurannya tidak lebih dari 4 MB.',
        ]);

        $folder = public_path('uploads/banner');

        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        // Hapus banner lama (apa pun ekstensinya) supaya hanya ada satu file banner.*
        foreach (glob($folder . '/banner.*') ?: [] as $lama) {
            @unlink($lama);
        }

        $file = $request->file('banner');
        app(ImageService::class)->saveAsWebp($file, $folder, 'banner', quality: 82, maxWidth: 1920, maxHeight: 1080);

        return redirect('/admin/dashboard')->with('sukses', 'Banner berhasil diganti.');
    }
}