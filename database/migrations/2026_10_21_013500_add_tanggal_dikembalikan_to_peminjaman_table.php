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
            $table->date('tanggal_dikembalikan')->nullable()->after('tanggal_kembali');
        });

        DB::table('peminjaman')
            ->where('status', 'selesai')
            ->whereNotNull('updated_at')
            ->update(['tanggal_dikembalikan' => DB::raw('DATE(updated_at)')]);
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('tanggal_dikembalikan');
        });
    }
};