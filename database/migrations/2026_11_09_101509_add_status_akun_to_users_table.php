<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Akun yang sudah ada (admin, akun contoh) otomatis "aktif" lewat default.
            // Akun baru dari halaman daftar diisi "menunggu" oleh AuthController.
            $table->string('status_akun', 20)->default('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status_akun');
        });
    }
};