<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AkunAdminController;
use App\Http\Controllers\Admin\AlatAdminController;
use App\Http\Controllers\Admin\PeminjamanAdminController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\FotoProfilController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfilController;
use App\Models\Alat;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Route;

// Halaman utama (publik)
Route::get('/', function () {
    return view('welcome', [
        'alatDaftar' => Alat::with('peminjaman')->orderBy('id')->get(),
    ]);
});

// Rute inisialisasi / sinkronisasi database untuk hosting
Route::get('/seed-db', function () {
    try {
        $sql = file_get_contents(database_path('silat_multimedia.sql'));
        \Illuminate\Support\Facades\DB::unprepared($sql);
        return response()->json([
            'status' => 'success',
            'message' => 'Database berhasil disinkronkan!',
            'users' => \App\Models\User::count(),
            'alat' => \App\Models\Alat::count(),
            'peminjaman' => \App\Models\Peminjaman::count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Rute cadangan (fallback) untuk foto profil: dipakai bila file di storage/app/public/uploads/profil
// tidak dapat diakses langsung lewat symlink /storage. Cukup diakses publik karena hanya foto profil.
Route::get('uploads/profil/{file}', [FotoProfilController::class, 'cadangan'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('foto-profil.cadangan');

// ==========================================
// Rute untuk user yang BELUM login (Guest)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'tampilLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'prosesLogin']);

    Route::get('/register', [AuthController::class, 'tampilRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'prosesRegister']);

    Route::get('/lupa-password', [AuthController::class, 'tampilLupaPassword'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'prosesLupaPassword'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

// ==========================================
// Rute untuk user yang SUDAH login (Auth)
// ==========================================
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        if (auth()->user()->peran === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $userId = auth()->id();
        $query = Peminjaman::where('user_id', $userId);
        $jumlahPeminjaman = (clone $query)->count();
        $jumlahPerStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $jumlahTerlambat = (clone $query)
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', now()->toDateString())
            ->count();
        $peminjaman = (clone $query)
            ->with('alat')
            ->latest('tanggal_pinjam')
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard', [
            'peminjaman' => $peminjaman,
            'alatDaftar' => Alat::with('peminjaman')->orderBy('id')->get(),
            'jumlahPeminjaman' => $jumlahPeminjaman,
            'jumlahPerStatus' => $jumlahPerStatus,
            'jumlahTerlambat' => $jumlahTerlambat,
        ]);
    })->name('dashboard');

    Route::get('/peminjaman/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');
    Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show');

    // Batalkan pengajuan peminjaman (hanya ketika status masih menunggu_persetujuan)
    Route::delete('/peminjaman/{peminjaman}/batal', [PeminjamanController::class, 'batalkan'])->name('peminjaman.batalkan');

    // Form Peminjaman (per alat, lewat slug)
    Route::get('/peminjaman/buat/{slug}', [PeminjamanController::class, 'create'])->name('peminjaman.create');

    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

    // Profil (dipakai semua peran: siswa, guru/karyawan, admin)
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
    Route::post('/profil/foto', [ProfilController::class, 'updateFoto'])->name('profil.foto');
    Route::delete('/profil/foto', [ProfilController::class, 'hapusFoto'])->name('profil.foto.hapus');
    Route::put('/profil/data', [ProfilController::class, 'updateData'])->name('profil.data');
    Route::put('/profil/kata-sandi', [ProfilController::class, 'updateKataSandi'])->name('profil.sandi');
    Route::put('/profil/pin', [ProfilController::class, 'updatePin'])->name('profil.pin');
    Route::post('/profil/hapus', [ProfilController::class, 'ajukanHapus'])->name('profil.hapus.ajukan');
    Route::delete('/profil/hapus', [ProfilController::class, 'batalHapus'])->name('profil.hapus.batal');
});

// ==========================================
// ROUTE KHUSUS ADMIN
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        // Semua peminjaman dikirim (tanpa batas): kartu status dan peringatan terlambat menghitung seluruh data.
        // Tampilan "Permintaan Terbaru" yang dibatasi 5 diatur di admin/dashboard.blade.php.
        $permintaan = Peminjaman::with(['user', 'alat'])
            ->latest('created_at')
            ->latest('id')
            ->get();

        $alatDaftar = Alat::with('peminjaman')->orderBy('id')->get();

        // Jumlah akun yang menunggu persetujuan (untuk notif di dashboard)
        $jumlahMenunggu = \App\Models\User::where('peran', '!=', 'admin')
            ->where('status_akun', 'menunggu')
            ->count();

        // Jumlah akun yang mengajukan penghapusan (untuk notif di dashboard)
        $jumlahMintaHapus = \App\Models\User::where('peran', '!=', 'admin')
            ->where(function ($sub) {
                $sub->where('status_akun', 'minta_hapus')
                    ->orWhereNotNull('permintaan_hapus_pada');
            })->count();

        return view('admin.dashboard', compact('permintaan', 'alatDaftar', 'jumlahMenunggu', 'jumlahMintaHapus'));
    })->name('admin.dashboard');

    // Tambah alat baru (dibuka dari kotak "Tambah Alat" di dashboard admin) -> URL: /admin/tambah-alat
    Route::get('/tambah-alat', [AlatAdminController::class, 'create'])->name('admin.alat.create');
    Route::post('/alat', [AlatAdminController::class, 'store'])->name('admin.alat.store');

    // {alat:slug}: alat dicari lewat slug di URL (contoh: /admin/alat/sony-mirrorless/kelola), bukan lewat id
    Route::get('/alat/{alat:slug}/kelola', [AlatAdminController::class, 'edit'])->name('admin.alat.edit');
    Route::put('/alat/{alat:slug}', [AlatAdminController::class, 'update'])->name('admin.alat.update');
    Route::delete('/alat/{alat:slug}', [AlatAdminController::class, 'destroy'])->name('admin.alat.destroy');
    Route::get('/peminjaman', [PeminjamanAdminController::class, 'riwayat'])->name('admin.peminjaman.riwayat');

    // Kelola akun user (verifikasi pendaftar, aktif/nonaktif, hapus) -> URL: /admin/akun/kelola
    Route::get('/akun/kelola', [AkunAdminController::class, 'kelola'])->name('admin.akun.kelola');
    Route::post('/akun/{user}/setujui', [AkunAdminController::class, 'setujui'])->name('admin.akun.setujui');
    Route::delete('/akun/{user}/tolak', [AkunAdminController::class, 'tolak'])->name('admin.akun.tolak');
    Route::patch('/akun/{user}/nonaktifkan', [AkunAdminController::class, 'nonaktifkan'])->name('admin.akun.nonaktifkan');
    Route::patch('/akun/{user}/aktifkan', [AkunAdminController::class, 'aktifkan'])->name('admin.akun.aktifkan');
    Route::patch('/akun/{user}/batal-hapus', [AkunAdminController::class, 'batalHapus'])->name('admin.akun.batalHapus');
    Route::delete('/akun/{user}/hapus', [AkunAdminController::class, 'hapus'])->name('admin.akun.hapus');

    // Ganti gambar banner (ikon pensil di hero dashboard admin) -> URL: /admin/banner
    Route::post('/banner', [BannerController::class, 'update'])->name('admin.banner.update');

    // Tindakan admin pada pengajuan peminjaman
    Route::post('/peminjaman/{peminjaman}/setujui', [PeminjamanAdminController::class, 'setujui'])->name('admin.peminjaman.setujui');
    Route::post('/peminjaman/{peminjaman}/ambil', [PeminjamanAdminController::class, 'ambil'])->name('admin.peminjaman.ambil');
    Route::post('/peminjaman/{peminjaman}/tolak', [PeminjamanAdminController::class, 'tolak'])->name('admin.peminjaman.tolak');
    Route::post('/peminjaman/{peminjaman}/kembali', [PeminjamanAdminController::class, 'kembali'])->name('admin.peminjaman.kembali');

    // Ubah catatan admin dari halaman detail peminjaman
    Route::patch('/peminjaman/{peminjaman}/catatan', [PeminjamanAdminController::class, 'ubahCatatan'])->name('admin.peminjaman.catatan');
});