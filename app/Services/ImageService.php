<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Kualitas kompresi default WebP (0-100).
     * Nilai 82 memberikan rasio kompresi tinggi dengan kualitas visual yang sangat terjaga.
     */
    public const DEFAULT_QUALITY = 82;

    /**
     * Simpan file unggahan sebagai gambar berformat WebP.
     * Mengatur transparansi (alpha channel), memperbaiki orientasi EXIF,
     * serta melakukan downscale proporsional jika resolusi melebihi batas maksimum.
     *
     * @param UploadedFile $file File gambar yang diunggah
     * @param string $targetDirectory Direktori absolut tujuan penyimpanan
     * @param string $filenameWithoutExt Nama file tanpa ekstensi
     * @param int $quality Kualitas WebP (1-100)
     * @param int|null $maxWidth Lebar maksimum (null untuk menjaga dimensi asli)
     * @param int|null $maxHeight Tinggi maksimum (null untuk menjaga dimensi asli)
     * @return string Nama file yang tersimpan beserta ekstensinya (misal: "contoh.webp")
     */
    public function saveAsWebp(
        UploadedFile $file,
        string $targetDirectory,
        string $filenameWithoutExt,
        int $quality = self::DEFAULT_QUALITY,
        ?int $maxWidth = null,
        ?int $maxHeight = null
    ): string {
        File::ensureDirectoryExists($targetDirectory);

        $realPath = $file->getRealPath();

        // Jika GD atau imagewebp tidak tersedia, gunakan fallback simpan file asli
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring') || ! $realPath) {
            return $this->fallbackSave($file, $targetDirectory, $filenameWithoutExt);
        }

        try {
            $contents = file_get_contents($realPath);
            if ($contents === false) {
                return $this->fallbackSave($file, $targetDirectory, $filenameWithoutExt);
            }

            $image = @imagecreatefromstring($contents);
            if (! $image) {
                return $this->fallbackSave($file, $targetDirectory, $filenameWithoutExt);
            }

            // Pastikan truecolor agar warna dan alpha channel diproses sempurna
            if (! imageistruecolor($image)) {
                imagepalettetotruecolor($image);
            }

            // Perbaiki rotasi gambar jika ada metadata orientasi EXIF (sering terjadi pada kamera HP)
            $image = $this->sesuaikanOrientasiExif($image, $realPath);

            // Downscale proporsional jika dimensi melebihi batas maksimal
            $image = $this->sesuaikanUkuran($image, $maxWidth, $maxHeight);

            // Pertahankan transparansi untuk format PNG / WebP transparan
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $targetFilename = $filenameWithoutExt . '.webp';
            $targetPath = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $targetFilename;

            $saved = @imagewebp($image, $targetPath, max(1, min(100, $quality)));
            imagedestroy($image);

            if (! $saved || ! file_exists($targetPath)) {
                return $this->fallbackSave($file, $targetDirectory, $filenameWithoutExt);
            }

            return $targetFilename;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengonversi gambar ke WebP: ' . $e->getMessage());
            return $this->fallbackSave($file, $targetDirectory, $filenameWithoutExt);
        }
    }

    /**
     * Fallback menyimpan file dengan ekstensi bawaan jika konversi WebP gagal.
     */
    protected function fallbackSave(UploadedFile $file, string $targetDirectory, string $filenameWithoutExt): string
    {
        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg';
        $targetFilename = $filenameWithoutExt . '.' . $ext;
        $file->move($targetDirectory, $targetFilename);

        return $targetFilename;
    }

    /**
     * Memperbaiki rotasi gambar berdasarkan EXIF Orientation tag (JPEG).
     *
     * @param \GdImage|resource $image
     * @param string $path
     * @return \GdImage|resource
     */
    protected function sesuaikanOrientasiExif($image, string $path)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        if (empty($exif['Orientation'])) {
            return $image;
        }

        $derajat = match ((int) $exif['Orientation']) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($derajat !== 0) {
            $rotated = imagerotate($image, $derajat, 0);
            if ($rotated !== false) {
                imagedestroy($image);
                return $rotated;
            }
        }

        return $image;
    }

    /**
     * Mengecilkan dimensi gambar secara proporsional jika melebihi batas maxWidth / maxHeight.
     *
     * @param \GdImage|resource $image
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     * @return \GdImage|resource
     */
    protected function sesuaikanUkuran($image, ?int $maxWidth, ?int $maxHeight)
    {
        $lebarAsli = imagesx($image);
        $tinggiAsli = imagesy($image);

        if ($lebarAsli <= 0 || $tinggiAsli <= 0) {
            return $image;
        }

        // Tentukan faktor skala
        $rasioLebar = ($maxWidth && $lebarAsli > $maxWidth) ? ($maxWidth / $lebarAsli) : 1.0;
        $rasioTinggi = ($maxHeight && $tinggiAsli > $maxHeight) ? ($maxHeight / $tinggiAsli) : 1.0;
        $rasio = min($rasioLebar, $rasioTinggi);

        // Jika tidak perlu diperkecil, kembalikan gambar asli
        if ($rasio >= 1.0) {
            return $image;
        }

        $lebarBaru = max(1, (int) round($lebarAsli * $rasio));
        $tinggiBaru = max(1, (int) round($tinggiAsli * $rasio));

        $kanvas = imagecreatetruecolor($lebarBaru, $tinggiBaru);

        // Pertahankan transparansi pada kanvas baru
        imagealphablending($kanvas, false);
        imagesavealpha($kanvas, true);
        $transparan = imagecolorallocatealpha($kanvas, 0, 0, 0, 127);
        imagefilledrectangle($kanvas, 0, 0, $lebarBaru, $tinggiBaru, $transparan);

        imagecopyresampled($kanvas, $image, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebarAsli, $tinggiAsli);
        imagedestroy($image);

        return $kanvas;
    }
}
