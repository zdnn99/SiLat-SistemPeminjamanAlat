<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('alat', 'subjudul')) {
            Schema::table('alat', function (Blueprint $table) {
                $table->string('subjudul', 60)->nullable()->after('nama_alat');
            });
        }

        // Isi subjudul alat lama dengan nilai yang sebelumnya hanya ada di model,
        // supaya datanya tersimpan di database dan tidak bergantung pada daftar bawaan.
        $bawaan = [
            'sony-mirrorless'       => 'Sony A6000',
            'canon-dslr'            => 'Canon EOS 1200D',
            'drone-dokumentasi-1'   => 'DJI Mini 2',
            'drone-dokumentasi-2'   => 'DJI Mavic Air 2',
            'handycam'              => 'Sony HDR-CX405',
            'gimbal-stabilizer'     => 'Rekaman Mulus Saat Bergerak',
            'tripod'                => 'Penopang Kamera Dan Handphone',
            'handphone-dokumentasi' => 'iPhone 13',
        ];

        foreach ($bawaan as $slug => $subjudul) {
            DB::table('alat')
                ->where('slug', $slug)
                ->whereNull('subjudul')
                ->update(['subjudul' => $subjudul]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('alat', 'subjudul')) {
            Schema::table('alat', function (Blueprint $table) {
                $table->dropColumn('subjudul');
            });
        }
    }
};