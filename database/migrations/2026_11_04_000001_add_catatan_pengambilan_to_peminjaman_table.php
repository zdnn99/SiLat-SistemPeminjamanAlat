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
            $table->text('catatan_pengambilan')->nullable()->after('catatan_admin');
        });

        DB::table('peminjaman')
            ->whereIn('status', ['menunggu_pengambilan', 'dipinjam'])
            ->whereNotNull('catatan_admin')
            ->update(['catatan_pengambilan' => DB::raw('catatan_admin')]);
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('catatan_pengambilan');
        });
    }
};
