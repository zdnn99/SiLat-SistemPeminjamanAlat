<?php

namespace Tests\Unit;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'silat_test_img_' . uniqid();
        File::ensureDirectoryExists($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_mengonversi_png_ke_format_webp(): void
    {
        $pngPath = $this->tempDir . DIRECTORY_SEPARATOR . 'sample.png';
        $im = imagecreatetruecolor(300, 200);
        $blue = imagecolorallocate($im, 0, 100, 255);
        imagefilledrectangle($im, 0, 0, 299, 199, $blue);
        imagepng($im, $pngPath);
        imagedestroy($im);

        $uploadedFile = new UploadedFile($pngPath, 'sample.png', 'image/png', null, true);
        $service = new ImageService();

        $savedFilename = $service->saveAsWebp(
            file: $uploadedFile,
            targetDirectory: $this->tempDir,
            filenameWithoutExt: 'hasil_tes_png'
        );

        $this->assertSame('hasil_tes_png.webp', $savedFilename);
        $targetPath = $this->tempDir . DIRECTORY_SEPARATOR . $savedFilename;
        $this->assertFileExists($targetPath);

        $info = getimagesize($targetPath);
        $this->assertSame('image/webp', $info['mime']);
        $this->assertSame(300, $info[0]);
        $this->assertSame(200, $info[1]);
    }

    public function test_mengonversi_jpeg_dan_melakukan_downscale_proporsional(): void
    {
        $jpegPath = $this->tempDir . DIRECTORY_SEPARATOR . 'sample.jpg';
        $im = imagecreatetruecolor(2000, 1000);
        $color = imagecolorallocate($im, 200, 50, 50);
        imagefilledrectangle($im, 0, 0, 1999, 999, $color);
        imagejpeg($im, $jpegPath, 90);
        imagedestroy($im);

        $uploadedFile = new UploadedFile($jpegPath, 'sample.jpg', 'image/jpeg', null, true);
        $service = new ImageService();

        $savedFilename = $service->saveAsWebp(
            file: $uploadedFile,
            targetDirectory: $this->tempDir,
            filenameWithoutExt: 'hasil_tes_resize',
            quality: 80,
            maxWidth: 1000,
            maxHeight: 1000
        );

        $this->assertSame('hasil_tes_resize.webp', $savedFilename);
        $targetPath = $this->tempDir . DIRECTORY_SEPARATOR . $savedFilename;
        $this->assertFileExists($targetPath);

        $info = getimagesize($targetPath);
        $this->assertSame('image/webp', $info['mime']);
        $this->assertSame(1000, $info[0]);
        $this->assertSame(500, $info[1]); // aspek rasio 2:1 tetap terjaga
    }
}
