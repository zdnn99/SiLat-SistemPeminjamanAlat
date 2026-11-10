# SiLat — Sistem Peminjaman Alat Multimedia

**SiLat** adalah aplikasi web berbasis Laravel untuk mengelola peminjaman alat ekstrakulikuler Multimedia. Memungkinkan siswa/guru mengajukan peminjaman alat, dan admin memverifikasi, menyetujui, serta melacak pengembaliannya.

---

## ✨ Fitur Utama

- **Halaman Publik**: Katalog alat dengan ketersediaan stok real-time
- **Autentikasi**: Registrasi & login dengan PIN keamanan
- **Peminjaman**: Ajukan, lacak status, dan lihat riwayat peminjaman
- **Admin Panel**:
  - Kelola akun pengguna (setujui, nonaktifkan, hapus)
  - Kelola alat (tambah, ubah, hapus)
  - Proses peminjaman (setujui → ambil → kembali)
  - Riwayat peminjaman dengan filter
- **Profil**: Ubah data, foto profil, kata sandi, dan PIN
- **ID Auto-Renumber**: ID tabel dirapikan otomatis saat data dihapus

---

## 🛠️ Teknologi

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: SQLite
- **Frontend**: Tailwind CSS (via Vite)
- **Testing**: PHPUnit (26 test cases)

---

## 🚀 Cara Instalasi

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM

### Langkah-langkah

```bash
# 1. Clone repository
git clone <url-repo> silat
cd silat

# 2. Install dependensi PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Buat file database SQLite
touch database/database.sqlite   # Linux/Mac
# atau di Windows:
New-Item database/database.sqlite

# 6. Jalankan migrasi & seeder
php artisan migrate --seed

# 7. Install dependensi Node dan build aset
npm install
npm run build

# 8. Jalankan server lokal
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

---

## 👤 Akun Default (setelah seeder)

| Peran | Nomor Induk | Password |
| :--- | :--- | :--- |
| **Admin** | `00000` | *(sesuai seeder)* |

> Akun admin dibuat melalui `AdminSeeder`. Lihat [`database/seeders/AdminSeeder.php`](database/seeders/AdminSeeder.php) untuk detailnya.

---

## 📂 Struktur Penting

```
app/
  Http/Controllers/       # Controller web & admin
  Models/                 # Eloquent models (User, Alat, Peminjaman)
  Models/Concerns/        # Trait RenumbersOnDelete
  Services/               # DatabaseSequenceService (auto-renumber ID)
database/
  migrations/             # Skema tabel
  seeders/                # Data awal (admin, alat)
  database.sqlite         # File database SQLite (tidak di-push)
public/
  images/                 # Gambar statis (banner, alat, auth)
  uploads/                # Unggahan pengguna (di-gitignore)
resources/views/          # Blade templates
routes/web.php            # Definisi rute
```

---

## 🌐 Deploy ke Hosting (cPanel/Shared Hosting)

1. Upload semua file **kecuali** `node_modules/` dan `vendor/`
2. Jalankan `composer install --no-dev --optimize-autoloader` di server
3. Salin `.env.example` → `.env` dan isi nilainya:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com
   APP_KEY=  ← generate dengan: php artisan key:generate
   ```
4. Pastikan folder `storage/` dan `bootstrap/cache/` bisa ditulis (`chmod 775`)
5. Jalankan `php artisan migrate --force`
6. Aset CSS/JS sudah ter-build di `public/build/` — tidak perlu `npm run build` di server

---

## 🧪 Menjalankan Test

```bash
php artisan test
# atau
composer test
```

26 test cases tersedia mencakup alur admin, peminjaman, dan autentikasi.
