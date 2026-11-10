<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->json('kelengkapan')->nullable();
            $table->json('aturan')->nullable();
        });

        $kelengkapan = [
            'sony-mirrorless' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera'],
            'canon-dslr' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera'],
            'drone-dokumentasi-1' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan'],
            'drone-dokumentasi-2' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan'],
            'handycam' => ['Unit handycam', 'Baterai', 'Charger atau adaptor', 'Kabel data', 'Kartu memori', 'Tas handycam'],
            'gimbal-stabilizer' => ['Unit gimbal', 'Baterai', 'Kabel pengisi daya', 'Plat dudukan (quick release)', 'Tas penyimpanan'],
            'tripod' => ['Unit tripod', 'Dudukan handphone', 'Plat dudukan kamera', 'Tas tripod'],
            'handphone-dokumentasi' => ['Handphone iPhone 13', 'Kabel pengisi daya', 'Adaptor charger', 'Casing pelindung'],
        ];

        foreach ($kelengkapan as $slug => $daftar) {
            DB::table('alat')->where('slug', $slug)->whereNull('kelengkapan')->update([
                'kelengkapan' => json_encode($daftar),
                'aturan' => json_encode([]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn(['kelengkapan', 'aturan']);
        });
    }
};