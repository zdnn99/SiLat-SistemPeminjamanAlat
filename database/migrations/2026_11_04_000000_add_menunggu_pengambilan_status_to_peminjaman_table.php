<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->enum('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam', 'selesai', 'ditolak'])
                ->default('menunggu_persetujuan')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('peminjaman')->where('status', 'menunggu_pengambilan')->update(['status' => 'menunggu_persetujuan']);

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->enum('status', ['menunggu_persetujuan', 'dipinjam', 'selesai', 'ditolak'])
                ->default('menunggu_persetujuan')
                ->nullable(false)
                ->change();
        });
    }
};
