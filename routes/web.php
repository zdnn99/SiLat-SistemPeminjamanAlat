<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

// Halaman utama (publik)
Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// Rute untuk user yang BELUM login (Guest)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'tampilLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'prosesLogin']);

    Route::get('/register', [AuthController::class, 'tampilRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'prosesRegister']);

    Route::get('/lupa-password', [AuthController::class, 'tampilLupaPassword'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'prosesLupaPassword'])->name('password.update');
});

// ==========================================
// Rute untuk user yang SUDAH login (Auth)
// ==========================================
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Form Peminjaman (per alat, lewat slug)
    Route::get('/peminjaman/buat/{slug}', function (string $slug) {
        return view('peminjaman.form', compact('slug'));
    })->name('peminjaman.create');

    // Proses submit form peminjaman
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
});

// ==========================================
// ROUTE KHUSUS ADMIN
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});