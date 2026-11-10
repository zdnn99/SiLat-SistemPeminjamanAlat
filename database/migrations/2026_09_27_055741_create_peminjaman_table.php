<?php
// database/migrations/xxxx_xx_xx_create_peminjaman_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('alat_id')->constrained('alat')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali');
            $table->text('keperluan')->nullable();
            $table->enum('status', ['menunggu_persetujuan', 'dipinjam', 'selesai', 'ditolak'])
                ->default('menunggu_persetujuan');
            $table->text('catatan_admin')->nullable();
            $table->text('kondisi_alat_kembali')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};