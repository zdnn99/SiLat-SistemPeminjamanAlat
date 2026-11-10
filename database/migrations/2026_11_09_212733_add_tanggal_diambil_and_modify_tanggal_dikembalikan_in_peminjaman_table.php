<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dateTime('tanggal_diambil')->nullable()->after('tanggal_kembali');
            $table->dateTime('tanggal_dikembalikan')->nullable()->change();
        });

        // Isi data lama yang statusnya dipinjam atau selesai
        DB::table('peminjaman')
            ->whereIn('status', ['dipinjam', 'selesai'])
            ->whereNull('tanggal_diambil')
            ->update([
                'tanggal_diambil' => DB::raw('COALESCE(updated_at, created_at)'),
            ]);

        DB::table('peminjaman')
            ->where('status', 'selesai')
            ->whereNotNull('tanggal_dikembalikan')
            ->whereTime('tanggal_dikembalikan', '00:00:00')
            ->update([
                'tanggal_dikembalikan' => DB::raw('COALESCE(updated_at, created_at)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('tanggal_diambil');
            $table->date('tanggal_dikembalikan')->nullable()->change();
        });
    }
};
