<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nomor_induk')->unique(); // Untuk NIS (Siswa) atau NIP (Guru/Karyawan)
            $table->enum('peran', ['admin', 'siswa', 'guru_karyawan'])->default('siswa');
            $table->string('kelas_jurusan')->nullable(); // Kosong jika peran adalah guru_karyawan
            $table->string('password'); // Tetap password agar tidak bentrok dengan Auth Laravel
            $table->string('pin'); 
            $table->rememberToken();
            $table->timestamps();
        });

        // Biarkan tabel password_reset_tokens dan sessions jika ada di bawah sini
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};