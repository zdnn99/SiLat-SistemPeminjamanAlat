<?php

namespace Database\Seeders;

use App\Models\Alat;
use Illuminate\Database\Seeder;

class AlatSeeder extends Seeder
{
    /**
     * 8 alat ekskul Multimedia. Slug dipakai untuk URL dan jangan diubah.
     * Kolom 'foto' berisi awalan nama file di public/images/alat/ (ditambah angka 1, 2, 3 dan .jpg).
     * Stok diisi 1 untuk semua alat; sesuaikan bila ada alat dengan jumlah unit lebih dari satu.
     */
    public function run(): void
    {
        $daftar = [
            [
                'nama_alat' => 'Sony Mirrorless Camera',
                'slug' => 'sony-mirrorless',
                'foto' => 'sony',
                'deskripsi' => 'Kamera mirrorless ringkas dengan sensor APS-C untuk foto dan video dokumentasi kegiatan sekolah. Ringan dibawa dan mudah dipakai, termasuk oleh pemula.',
                'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Canon DSLR Camera',
                'slug' => 'canon-dslr',
                'foto' => 'cannon',
                'deskripsi' => 'Kamera DSLR tingkat pemula untuk memotret acara, potret, dan dokumentasi. Dilengkapi jendela bidik optik dengan hasil gambar yang tajam.',
                'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Drone Dokumentasi 1',
                'slug' => 'drone-dokumentasi-1',
                'foto' => 'drone1-',
                'deskripsi' => 'Drone ringan dan ringkas untuk mengambil foto dan video dari udara. Cocok untuk dokumentasi kegiatan di area terbuka.',
                'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Drone Dokumentasi 2',
                'slug' => 'drone-dokumentasi-2',
                'foto' => 'drone2-',
                'deskripsi' => 'Drone lipat dengan kamera beresolusi tinggi untuk video udara yang stabil. Cocok untuk dokumentasi acara besar dan area yang luas.',
                'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Handycam',
                'slug' => 'handycam',
                'foto' => 'handycam',
                'deskripsi' => 'Kamera video untuk merekam acara dan kegiatan dalam durasi panjang, dengan zoom optik yang praktis digunakan.',
                'kelengkapan' => ['Unit handycam', 'Baterai', 'Charger atau adaptor', 'Kabel data', 'Kartu memori', 'Tas handycam'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Gimbal Stabilizer',
                'slug' => 'gimbal-stabilizer',
                'foto' => 'gimbal',
                'deskripsi' => 'Penstabil gambar agar rekaman tetap mulus saat berjalan atau bergerak. Cocok untuk video liputan, vlog, dan dokumentasi kegiatan.',
                'kelengkapan' => ['Unit gimbal', 'Baterai', 'Kabel pengisi daya', 'Plat dudukan (quick release)', 'Tas penyimpanan'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Tripod Stabilizer',
                'slug' => 'tripod',
                'foto' => 'tripod',
                'deskripsi' => 'Penopang yang stabil untuk kamera dan handphone saat memotret atau merekam video, dengan tinggi yang dapat diatur.',
                'kelengkapan' => ['Unit tripod', 'Dudukan handphone', 'Plat dudukan kamera', 'Tas tripod'],
                'aturan' => [],
            ],
            [
                'nama_alat' => 'Handphone Dokumentasi',
                'slug' => 'handphone-dokumentasi',
                'foto' => 'ipone',
                'deskripsi' => 'Handphone untuk dokumentasi cepat, menghasilkan foto dan video berkualitas dari perangkat yang praktis dibawa ke mana saja.',
                'kelengkapan' => ['Handphone iPhone 13', 'Kabel pengisi daya', 'Adaptor charger', 'Casing pelindung'],
                'aturan' => [],
            ],
        ];

        foreach ($daftar as $alat) {
            $rekaman = Alat::firstOrNew(['slug' => $alat['slug']]);
            $nilai = $alat + ['stok' => 1];
            $kelengkapan = $nilai['kelengkapan'];
            $aturan = $nilai['aturan'];
            unset($nilai['kelengkapan'], $nilai['aturan']);

            $rekaman->fill($nilai);
            $rekaman->kelengkapan ??= $kelengkapan;
            $rekaman->aturan ??= $aturan;
            $rekaman->save();
        }
    }
}