<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'permintaan_hapus_pada')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('permintaan_hapus_pada')->nullable()->after('foto_profil');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'permintaan_hapus_pada')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('permintaan_hapus_pada');
            });
        }
    }
};
