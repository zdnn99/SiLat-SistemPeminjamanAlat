<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('alat') || \App\Models\Alat::count() === 0) {
                    $sqlPath = database_path('silat_multimedia.sql');
                    if (file_exists($sqlPath)) {
                        \Illuminate\Support\Facades\DB::unprepared(file_get_contents($sqlPath));
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error saat database belum siap diinisialisasi
            }
        }
    }
}
