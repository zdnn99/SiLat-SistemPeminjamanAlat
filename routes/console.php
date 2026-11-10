<?php

use App\Services\DatabaseSequenceService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:reset-ids', function (DatabaseSequenceService $service) {
    $this->info('Merapikan dan me-reset ID database dimulai dari 1...');
    $hasil = $service->renumberAll();
    $this->info("Selesai! users: {$hasil['users']}, alat: {$hasil['alat']}, peminjaman: {$hasil['peminjaman']}.");
})->purpose('Merapikan ID pada tabel users, alat, dan peminjaman agar berurutan mulai dari 1 tanpa celah');

