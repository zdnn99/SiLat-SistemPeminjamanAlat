<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->string('foto_kiri')->nullable()->after('foto');
            $table->string('foto_kanan')->nullable()->after('foto_kiri');
        });
    }

    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn(['foto_kiri', 'foto_kanan']);
        });
    }
};