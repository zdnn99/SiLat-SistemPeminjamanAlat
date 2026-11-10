<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SiLat Multimedia</title>
    <meta name="description" content="Dashboard admin SiLat, sistem peminjaman alat multimedia Ekstrakurikuler Multimedia SMK Negeri 5 Surakarta.">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== Efek gerak =====
           Semua gerakan hanya aktif bagi pengguna yang tidak memilih "kurangi gerakan" di perangkatnya. */

        /* Cincin fokus untuk pengguna keyboard (nol spesifisitas, jadi gaya khusus di elemen tetap menang) */
        :where(a, button, summary, input):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }

        @keyframes dropdown-muncul { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes panah-maju { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(4px); } }

        @media (prefers-reduced-motion: no-preference) {
            /* Dropdown (pencarian dan menu HP) muncul halus */
            .dropdown-animasi { animation: dropdown-muncul .16s ease-out; }
        }

        /* Animasi ikon panah hanya aktif pada perangkat dengan kursor agar tidak terus bergerak di layar sentuh */
        @media (prefers-reduced-motion: no-preference) and (hover: hover) {
            .tautan-panah:hover svg { animation: panah-maju .6s ease-in-out infinite; }
        }
    </style>

    <!-- Fallback foto rusak (untuk thumbnail dropdown pencarian) -->
    <script>
        function fotoRusak(img) {
            img.onerror = null;
            img.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" fill="#eff6ff"/>' +
                '<path d="M12 15h3l1.5-2h7L25 15h3a1 1 0 011 1v9a1 1 0 01-1 1H12a1 1 0 01-1-1v-9a1 1 0 011-1z" fill="none" stroke="#93c5fd" stroke-width="1.5"/>' +
                '<circle cx="20" cy="20.5" r="3" fill="none" stroke="#93c5fd" stroke-width="1.5"/></svg>'
            );
        }
    </script>
</head>
<body class="bg-white text-gray-900 antialiased flex flex-col min-h-screen">

@php
    $alatList = $alatDaftar->map(function ($alat) {
        $slug = $alat->slug;
        $ikon = match (true) {
            str_contains($slug, 'drone') => 'drone',
            str_contains($slug, 'handycam') => 'video',
            str_contains($slug, 'gimbal') => 'gimbal',
            str_contains($slug, 'tripod') => 'tripod',
            str_contains($slug, 'handphone') => 'hp',
            default => 'kamera',
        };

        return [
            'slug' => $slug,
            'foto_url' => $alat->foto_url,
            'nama' => $alat->nama_alat,
            'sub' => $alat->subjudul,
            'ikon' => $ikon,
            'tersedia' => $alat->tersedia,
            'status_ketersediaan' => $alat->status_ketersediaan,
            'stok_tersedia' => $alat->stok_tersedia,
        ];
    });

    // Dipakai daftar permintaan untuk mencari alat dari slug
    $katalog = collect($alatList)->keyBy('slug');

    $ikonAlat = [
        'kamera' => '<path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>',
        'video'  => '<path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'hp'     => '<path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'drone'  => '<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="5" cy="18" r="2.2"/><circle cx="19" cy="18" r="2.2"/><path d="M7 7.5l3 3m7-3l-3 3M7 16.5l3-3m7 3l-3-3"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.5"/>',
        'gimbal' => '<rect x="7" y="4" width="10" height="5" rx="1.5"/><path d="M4 6.5h3m10 0h3M12 9v4"/><rect x="9.5" y="13" width="5" height="8" rx="2"/>',
        'tripod' => '<rect x="8.5" y="2.5" width="7" height="4" rx="1"/><path d="M12 6.5V11m0 0l-5 10m5-10v10m0-10l5 10"/>',
    ];

    // Ikon pensil (dipakai tombol ganti gambar banner dan gambar Tentang).
    // Foto alat tidak lagi diganti dari dashboard: semuanya lewat halaman Kelola supaya tidak ada dua jalur untuk hal yang sama.
    $ikonPensil = 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z';

    // Gaya semua tombol pensil: biru (ikon dan garis tepi) saat diam, biru penuh seperti tombol "Kelola" saat kursor di atasnya.
    // Ukuran dan posisi ditambahkan di masing-masing tombol.
    $kelasPensil = 'rounded-full bg-white text-blue-600 border border-blue-300 shadow-sm flex items-center justify-center transition-all duration-200 hover:bg-blue-600 hover:border-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/40 motion-safe:hover:scale-110 active:scale-95';

    // TODO: buat route POST untuk ganti gambar Tentang, mis. route('admin.gambar.update').
    // Field yang dikirim: gambar (file), jenis (tentang), kunci (tentang|ekskul).
    $urlGambar = url('/admin/gambar');

    // Tujuan kotak "Tambah Alat". Selama route admin.alat.create belum dibuat, kotak tetap tampil tetapi belum membuka apa-apa.
    $urlTambahAlat = \Illuminate\Support\Facades\Route::has('admin.alat.create') ? route('admin.alat.create') : '#';

    // Jumlah kotak "Tambah Alat" yang tampil: selalu melengkapi baris terakhir sampai 4 kotak.
    // Alat baru mengisi kotak paling kiri, kotak tambah bergeser ke kanan; kalau 4 kotak sudah terisi alat,
    // otomatis muncul satu baris baru berisi 4 kotak tambah (8 alat -> 4 kotak, 9 alat -> 3 kotak, 12 alat -> 4 kotak).
    $jumlahKotakTambah = 4 - ($alatList->count() % 4);

    $daftarPencarian = collect($alatList)->map(function ($a) {
        return [
            'slug' => $a['slug'],
            'nama' => $a['nama'],
            'sub'  => $a['sub'],
            'foto' => $a['foto_url'],
        ];
    })->values();

    // Hari ini (dasar untuk menghitung sisa hari dan keterlambatan)
    $hariIni = \Carbon\Carbon::now()->startOfDay();

    // Tampilan tiap status (sama dengan dashboard siswa):
    // Menunggu abu-abu, Dipinjam biru, Selesai hijau, Ditolak merah.
    $statusMap = [
        'menunggu_persetujuan' => [
            'label' => 'Menunggu Persetujuan',
            'badge' => 'bg-gray-100 text-gray-700',
            'dot'   => 'bg-gray-500',
            'ikon'  => 'bg-gray-200 text-gray-600',
            'path'  => 'M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'menunggu_pengambilan' => [
            'label' => 'Menunggu Pengambilan',
            'badge' => 'bg-amber-100 text-amber-800',
            'dot'   => 'bg-amber-500',
            'ikon'  => 'bg-amber-100 text-amber-700',
            'path'  => 'M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'dipinjam' => [
            'label' => 'Sedang Dipinjam',
            'badge' => 'bg-blue-100 text-blue-700',
            'dot'   => 'bg-blue-500',
            'ikon'  => 'bg-blue-100 text-blue-600',
            'path'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'badge' => 'bg-green-100 text-green-700',
            'dot'   => 'bg-green-500',
            'ikon'  => 'bg-green-100 text-green-600',
            'path'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'badge' => 'bg-red-100 text-red-700',
            'dot'   => 'bg-red-500',
            'ikon'  => 'bg-red-100 text-red-600',
            'path'  => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    // Kartu ringkasan sekaligus tombol filter (urutan tampil).
    // 'ikon'  = warna kotak dan ikon saat kartu belum dipilih: kotak pastel (-100) dengan ikon sewarna yang lebih tua (-600),
    //           pola yang sama dengan kartu status lain.
    // 'aktif' = warna ikon saat kartu terpilih (latar biru): versi terang dari warna ikon aslinya (-300) supaya seragam dan konsisten.
    $kartuFilter = [
        'semua' => [
            'label' => 'Permintaan Terbaru',
            'ikon'  => 'bg-amber-100 text-amber-600',
            'aktif' => 'group-aria-[pressed=true]:text-amber-300',
            'path'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        ],
    ];
    foreach (['menunggu_persetujuan', 'dipinjam', 'selesai', 'ditolak'] as $kode) {
        $kartuFilter[$kode] = $statusMap[$kode];
    }
    $kartuFilter['menunggu_persetujuan']['aktif'] = 'group-aria-[pressed=true]:text-gray-200';
    $kartuFilter['dipinjam']['aktif']             = 'group-aria-[pressed=true]:text-sky-300';
    $kartuFilter['selesai']['aktif']              = 'group-aria-[pressed=true]:text-green-300';
    $kartuFilter['ditolak']['ikon']               = 'bg-red-100 text-red-600';
    $kartuFilter['ditolak']['aktif']              = 'group-aria-[pressed=true]:text-red-300';

    // Satu kali olah data untuk tabel (layar lebar) dan kartu (layar kecil).
    // $permintaan dikirim dari route /admin/dashboard: SEMUA Peminjaman beserta user dan alat (bukan hanya 5),
    // karena kartu status (Menunggu, Dipinjam, Selesai, Ditolak) dan peringatan terlambat harus menghitung seluruh data.
    // Yang dibatasi 5 hanya tampilan "Permintaan Terbaru" (lihat 'terbaru' di bawah).
    $baris = $permintaan->map(function ($p) use ($katalog, $statusMap, $hariIni) {
        $alatData = $katalog->get($p->alat->slug) ?? [
            'slug' => $p->alat->slug, 'foto_url' => $p->alat->foto_url, 'nama' => $p->alat->nama_alat,
            'sub' => '', 'ikon' => 'kamera', 'tersedia' => true,
        ];
        $pinjam  = $p->tanggal_pinjam->copy()->startOfDay();
        $kembali = $p->tanggal_kembali->copy()->startOfDay();
        $dikembalikan = $p->tanggal_dikembalikan
            ? $p->tanggal_dikembalikan->locale('id')->translatedFormat('j M Y')
            : null;
        $sisa      = (int) $hariIni->diffInDays($kembali, false);
        $terlambat = $p->status === 'dipinjam' && $sisa < 0;

        $pengembalian = match (true) {
            $terlambat => 'Terlambat ' . abs($sisa) . ' hari',
            $p->status === 'dipinjam' => 'Belum dikembalikan',
            $p->status === 'selesai' => $dikembalikan ? 'Dikembalikan pada ' . $dikembalikan : 'Sudah dikembalikan',
            default => '-',
        };
        // Warna teks kolom Pengembalian mengikuti warna status: Terlambat merah, Selesai hijau
        $warnaPengembalian = match (true) {
            $terlambat => 'font-semibold text-red-700',
            $p->status === 'selesai' => 'font-semibold text-green-700',
            $p->status === 'dipinjam' => 'text-gray-600',
            default => 'text-gray-400',
        };

        $kelas = $p->user->kelas_jurusan ?: ($p->user->peran === 'guru_karyawan' ? 'Guru / Karyawan' : '-');

        return [
            'status'             => $p->status,
            'peminjam'           => $p->user->nama_lengkap,
            'kelas'              => $kelas,
            'alat_data'          => $alatData,
            'tampil'             => $statusMap[$p->status],
            'pinjam_f'           => $pinjam->locale('id')->translatedFormat('j M Y'),
            'kembali_f'          => $kembali->locale('id')->translatedFormat('j M Y'),
            'pengembalian'       => $pengembalian,
            'pengembalian_warna' => $warnaPengembalian,
            'pinjam_ts'          => $pinjam->timestamp,   // dipakai untuk mengurutkan berdasarkan tanggal
            'dibuat_ts'          => ($p->created_at ?? $p->tanggal_pinjam)->timestamp,   // waktu pengajuan dibuat, dasar menentukan "terbaru"
            'terlambat'          => $terlambat,
            'detail_url'         => route('peminjaman.show', $p),
        ];
    })->values();

    // "Permintaan Terbaru" = 5 pengajuan yang paling baru dibuat (maksimal). Sisanya hanya muncul lewat kartu status
    // atau halaman riwayat.
    $kunciTerbaru = $baris
        ->sortByDesc(fn ($b) => [$b['dibuat_ts'], $b['pinjam_ts']])
        ->take(5)
        ->keys()
        ->all();
    $baris = $baris->map(fn ($b, $i) => $b + ['terbaru' => in_array($i, $kunciTerbaru, true)]);

    $jumlah          = $baris->countBy('status');
    $jumlahTerlambat = $baris->where('terlambat', true)->count();
    $jumlahTerbaru   = $baris->where('terbaru', true)->count();   // 0 sampai 5
    $kosong          = $baris->isEmpty();

    // 1-4 data: "Menampilkan N Permintaan Terakhir"; sudah 5: "Menampilkan Maksimal 5 Permintaan Terakhir"
    $teksTerbaru = $jumlahTerbaru >= 5
        ? 'Menampilkan Maksimal 5 Permintaan Terakhir'
        : 'Menampilkan ' . $jumlahTerbaru . ' Permintaan Terakhir';
@endphp

    <!-- Navbar Atas (bayangannya muncul saat halaman digulir) -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-3 sm:gap-6">
            <!-- Logo & Brand (di HP logo mengecil dan subjudul dipotong supaya tombol profil dan menu tidak terdorong keluar layar) -->
            <a href="{{ url('/admin/dashboard') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3 sm:shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-14 h-14 shrink-0 sm:w-18 sm:h-18 transition-transform duration-300 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-3">
                <div class="min-w-0">
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block truncate transition-colors duration-300 group-hover:text-gray-900">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 min-w-0 max-w-[30rem] hidden md:block">
                <div class="relative" id="search-wrapper">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </span>
                    <input type="text" id="search-input" autocomplete="off" placeholder="Cari Alat Multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 hover:border-blue-400 hover:bg-white hover:shadow-md hover:shadow-blue-900/5 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200">

                    <!-- Dropdown Hasil Pencarian -->
                    <div id="search-dropdown" class="dropdown-animasi hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 max-h-80 overflow-y-auto z-50">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Nav Links, Profil, Menu Mobile (tiga link konsisten: Permintaan, Peralatan, Tentang) -->
            <div class="flex items-center gap-3 shrink-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm font-medium text-gray-700">
                    <a href="#permintaan" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Permintaan</a>
                    <a href="#peralatan" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Peralatan</a>
                    <a href="#tentang" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Tentang</a>
                </nav>

                <!-- Profil Dropdown (partial yang sama dengan dashboard siswa) -->
                @include('partials.profil-dropdown')

                <!-- Menu mobile (tanpa JS untuk buka-tutup, memakai <details>) -->
                <details id="menu-mobile" class="lg:hidden relative">
                    <summary aria-label="Buka menu" class="list-none cursor-pointer w-10 h-10 flex items-center justify-center rounded-lg text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 active:scale-95 [&::-webkit-details-marker]:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </summary>
                    <div class="dropdown-animasi absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 text-sm font-medium text-gray-700">
                        <a href="#permintaan" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6 active:bg-blue-50 active:text-blue-700">Permintaan</a>
                        <a href="#peralatan" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6 active:bg-blue-50 active:text-blue-700">Peralatan</a>
                        <a href="#tentang" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6 active:bg-blue-50 active:text-blue-700">Tentang</a>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <!-- Hero Section (identik dengan dashboard siswa, ditambah tombol ganti banner khusus admin) -->
    <div id="hero" class="relative bg-slate-900 w-full h-[420px] sm:h-[480px] md:h-[540px] flex flex-col justify-center items-center text-center px-4 overflow-hidden">
        <!-- Lapis 1: Foto banner, blur ringan, opacity penuh (scale-110 menutup tepi yang pudar akibat blur) -->
        <img id="hero-foto" src="{{ \App\Support\Banner::url() }}" alt="" fetchpriority="high" class="absolute inset-0 w-full h-full object-cover scale-110 blur-[1px]">

        <!-- Lapis 2: Bayangan hanya di tengah supaya teks terbaca. Gelapnya ditahan di sepanjang area teks (0% sampai 40%),
             lalu memudar ke tepi sehingga foto di pinggir tetap cerah -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_75%_60%_at_50%_50%,rgba(15,23,42,0.65)_0%,rgba(15,23,42,0.55)_40%,rgba(15,23,42,0)_100%)]"></div>

        <!-- Tombol ganti banner (khusus admin) -->
        <button type="button" data-ganti-gambar data-judul="Ganti Banner" data-action="{{ route('admin.banner.update') }}" data-field="banner" data-fit="cover"
                title="Ganti gambar banner" aria-label="Ganti gambar banner"
                class="absolute top-4 right-4 z-20 w-11 h-11 {{ $kelasPensil }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonPensil }}"/>
            </svg>
        </button>

        <!-- Lapis 3: Teks (membesar pelan saat kursor di atasnya) -->
        <div class="group relative z-10 max-w-4xl transition-transform duration-500 ease-out motion-safe:hover:scale-105">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-bold mb-4 tracking-tight [text-shadow:0_1px_3px_rgb(0_0_0_/_60%),0_4px_28px_rgb(0_0_0_/_55%)]">
                <span class="text-blue-400 transition-colors duration-300 group-hover:text-blue-300">SiLat</span>
                <span class="text-white"> Multimedia</span>
            </h1>
            <p class="text-white font-medium text-lg sm:text-xl md:text-2xl [text-shadow:0_1px_3px_rgb(0_0_0_/_65%),0_3px_20px_rgb(0_0_0_/_60%)]">Sistem Peminjaman Alat Multimedia</p>
        </div>
    </div>

    @if (session('sukses') || $errors->hasAny(['banner', 'gambar']))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full space-y-3">
            @if (session('sukses'))
                <div class="flex items-start gap-2 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-xl">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="min-w-0 break-words">{{ session('sukses') }}</span>
                </div>
            @endif
            @foreach (['banner', 'gambar'] as $kolom)
                @error($kolom)
                    <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-3 rounded-xl">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="min-w-0 break-words">{{ $message }}</span>
                    </div>
                @enderror
            @endforeach
        </div>
    @endif

    <!-- Konten Utama. Warna latar tiap bagian berselang-seling: putih -> abu -> putih -> abu. -->
    <main class="flex-grow">

        <!-- Bagian 1: Permintaan Peminjaman (putih) -->
        <section id="permintaan" class="scroll-mt-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="group">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700">Permintaan Peminjaman</h2>
                        <p class="text-gray-500 text-sm mt-1 transition-colors duration-300 group-hover:text-gray-700">
                            @if ($kosong)
                                Belum ada pengajuan dari peminjam.
                            @else
                                Tinjau pengajuan dan pantau peminjaman yang sedang berjalan. Klik kartu status untuk menyaring daftar.
                            @endif
                        </p>
                    </div>

                    <!-- Tombol Kelola Akun di sebelah kanan judul Permintaan Peminjaman -->
                    <a href="{{ route('admin.akun.kelola') }}"
                       class="tautan-panah shrink-0 inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-600 shadow-sm transition-all duration-200 hover:bg-blue-600 hover:text-white hover:border-blue-600 hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Kelola Akun</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Notifikasi akun menunggu persetujuan (di atas notif terlambat, selalu didahulukan) -->
                @if ($jumlahMenunggu > 0)
                    <div role="alert" class="group mb-6 flex items-center gap-4 rounded-2xl border border-gray-200/80 bg-white px-5 py-4 shadow-sm transition-all duration-300 hover:border-amber-300 hover:bg-amber-50/50 hover:shadow-xl hover:shadow-amber-900/10 motion-safe:hover:-translate-y-1">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 transition-all duration-300 group-hover:bg-amber-500 group-hover:text-white motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900 text-sm sm:text-base transition-colors duration-300 group-hover:text-amber-700">{{ $jumlahMenunggu }} pendaftar menunggu persetujuan Anda</p>
                            <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-300 group-hover:text-gray-700">Tinjau dan setujui atau tolak akun pendaftar baru melalui halaman Kelola Akun.</p>
                        </div>
                        <a href="{{ route('admin.akun.kelola') }}?status=menunggu"
                           class="shrink-0 inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-white px-3.5 py-2 text-xs font-semibold text-amber-700 shadow-sm transition-all duration-200 hover:bg-amber-500 hover:text-white hover:border-amber-500 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-95">
                            Tinjau
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @endif

                <!-- Peringatan keterlambatan (hanya muncul kalau ada, dihitung dari seluruh data).
                     Gaya kartu sama dengan panel lain (putih, rounded-2xl, border lembut), aksen merah seperti badge "Terlambat" -->
                @if ($jumlahTerlambat > 0)
                    <div role="alert" class="group mb-6 flex items-center gap-4 rounded-2xl border border-gray-200/80 bg-white px-5 py-4 shadow-sm transition-all duration-300 hover:border-red-300 hover:bg-red-50/50 hover:shadow-xl hover:shadow-red-900/10 motion-safe:hover:-translate-y-1">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 transition-all duration-300 group-hover:bg-red-600 group-hover:text-white motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm sm:text-base transition-colors duration-300 group-hover:text-red-700">{{ $jumlahTerlambat }} peminjaman melewati batas pengembalian</p>
                            <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-300 group-hover:text-gray-700">Hubungi peminjam yang bersangkutan agar alat segera dikembalikan ke Ekskul Multimedia.</p>
                        </div>
                    </div>
                @endif

                <!-- Kartu ringkasan (sekaligus tombol filter). "Permintaan Terbaru" aktif secara bawaan dan hanya menampilkan maksimal 5 data.
                     Kartu status lain menghitung dan menampilkan SELURUH data berstatus itu.
                     Tinggi kartu dibuat tetap (min-h) supaya tidak berubah ketika ada data.
                     Hover (hanya kartu yang belum dipilih): border + latar biru muda, terangkat, ikon miring.
                     Aktif (aria-pressed=true): biru solid blue-600, sama dengan teks "SiLat".
                     Ikon tiap kartu saat aktif memakai versi terang dari warna aslinya (lihat 'aktif' di $kartuFilter). -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                    @foreach ($kartuFilter as $kode => $k)
                        @php $aktifAwal = $kode === 'semua'; @endphp
                        <button type="button" data-filter="{{ $kode }}" data-label="{{ $k['label'] }}" aria-pressed="{{ $aktifAwal ? 'true' : 'false' }}"
                                @disabled($kosong)
                                class="filter-kartu group {{ $kode === 'semua' ? 'col-span-2 sm:col-span-1' : '' }} {{ $kosong ? 'cursor-default' : 'cursor-pointer' }} min-h-[6.25rem] text-left rounded-2xl border border-gray-200/80 bg-white shadow-sm p-4 flex items-center gap-3.5 transition-all duration-300
                                       aria-[pressed=false]:hover:border-blue-500 aria-[pressed=false]:hover:bg-blue-50 aria-[pressed=false]:hover:ring-2 aria-[pressed=false]:hover:ring-blue-500/20 aria-[pressed=false]:hover:shadow-2xl aria-[pressed=false]:hover:shadow-blue-900/15 motion-safe:aria-[pressed=false]:hover:-translate-y-1.5
                                       aria-[pressed=true]:hover:shadow-xl aria-[pressed=true]:hover:shadow-blue-600/40 motion-safe:aria-[pressed=true]:hover:-translate-y-0.5 active:scale-[0.98]
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500
                                       aria-[pressed=true]:border-blue-600 aria-[pressed=true]:bg-blue-600 aria-[pressed=true]:shadow-lg aria-[pressed=true]:shadow-blue-600/30">
                            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-all duration-300 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6 {{ $k['ikon'] }} group-aria-[pressed=true]:bg-white/20 {{ $k['aktif'] }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $k['path'] }}"/>
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-2xl font-bold text-gray-900 leading-tight transition-colors group-aria-[pressed=true]:text-white group-aria-[pressed=false]:group-hover:text-blue-700">{{ $kode === 'semua' ? $jumlahTerbaru : ($jumlah[$kode] ?? 0) }}</span>
                                <span class="block text-xs text-gray-500 transition-colors group-aria-[pressed=true]:text-blue-100 group-aria-[pressed=false]:group-hover:text-gray-700">{{ $k['label'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                @unless ($kosong)
                <!-- Baris keterangan: ikon "i" biru di kiri, teks, lalu tautan aksi.
                     Filter "Permintaan Terbaru": 1-4 data "Menampilkan N Permintaan Terakhir", 5 data "Menampilkan Maksimal 5 Permintaan Terakhir", plus tautan riwayat.
                     Filter status lain: "Menampilkan N Permintaan" (tanpa titik, sama dengan dashboard siswa), plus tombol "Tampilkan semua".
                     Ikon urutan di sini hanya untuk layar di bawah xl (di layar lebar ikonnya ada di header tabel) -->
                <div id="info-filter" class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-gray-600">
                    <p class="group flex items-center gap-2 transition-colors duration-200 hover:text-gray-900">
                        <svg class="w-4 h-4 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="info-filter-teks">{{ $teksTerbaru }}</span>
                    </p>

                    <div class="urut-wrapper relative xl:hidden">
                        <button type="button" aria-haspopup="true" aria-expanded="false" aria-label="Urutkan daftar" title="Urutkan"
                                class="tombol-menu-urut cursor-pointer inline-flex items-center justify-center w-10 h-10 sm:w-8 sm:h-8 rounded-lg border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:border-blue-500 hover:bg-blue-50 hover:text-blue-600 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500
                                       aria-[expanded=true]:border-blue-600 aria-[expanded=true]:bg-blue-50 aria-[expanded=true]:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </button>

                        <div role="group" aria-label="Urutkan daftar" class="menu-urut hidden absolute left-0 top-full mt-2 w-56 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-30 text-left">
                            <p class="px-4 pt-1.5 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Urutkan berdasarkan</p>

                            <button type="button" data-urut="status" aria-pressed="false"
                                    class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                           aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                <span>Status</span>
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="label-urut">A–Z</span>
                                    <svg class="ikon-urut w-3.5 h-3.5 rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                    </svg>
                                </span>
                            </button>

                            <button type="button" data-urut="tanggal" aria-pressed="true"
                                    class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                           aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                <span>Tanggal</span>
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="label-urut">Terbaru</span>
                                    <svg class="ikon-urut w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Tautan ke halaman riwayat (hanya tampil saat filter "Permintaan Terbaru").
                         Dibungkus <span> supaya class "hidden" tidak dikalahkan oleh inline-flex pada tautan.
                         Permintaan yang lebih lama dari 5 terbaru dilihat di halaman ini. -->
                    <span id="link-riwayat">
                        <a href="{{ route('admin.peminjaman.riwayat') }}" class="tautan-panah inline-flex items-center gap-1 rounded-lg px-2 py-1 -mx-2 font-semibold text-blue-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-800 active:scale-95 active:bg-blue-50">
                            Lihat Semua Pengajuan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </span>

                    <!-- Tombol "Tampilkan semua": kembali ke Permintaan Terbaru (tampil saat filter status lain aktif) -->
                    <button type="button" class="reset-filter hidden cursor-pointer rounded-lg px-2 py-1 -mx-2 font-semibold text-blue-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-800 active:scale-95 active:bg-blue-50" id="reset-toolbar">Tampilkan semua</button>
                </div>
                @endunless

                <div id="daftar-pinjam-isi">
                    @if ($kosong)
                        <!-- Tampilan belum ada pengajuan: satu kotak, tulisan di tengah (sengaja berbeda dari dashboard siswa) -->
                        <div role="status" class="group flex min-h-[16rem] flex-col items-center justify-center rounded-2xl border border-gray-200/80 bg-white px-6 py-16 text-center shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-900/10">
                            <h3 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 transition-colors duration-300 group-hover:text-blue-700">Belum Ada Pengajuan Peminjaman</h3>
                            <p class="text-sm text-gray-500 mt-2">Semua riwayat pengajuan peminjaman dapat dilihat di halaman riwayat.</p>
                            <a href="{{ route('admin.peminjaman.riwayat') }}" class="tautan-panah mt-4 inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition-all duration-200 hover:bg-blue-600 hover:text-white hover:border-blue-600 hover:shadow-md active:scale-95">
                                <span>Lihat Semua Pengajuan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @else
                    <!-- Tabel untuk layar lebar (xl ke atas); layar lebih kecil memakai kartu.
                         Susunannya mengikuti tabel "Peminjaman Saya" di dashboard siswa: kolom pertama dan terakhir px-6,
                         kolom tengah px-4, tombol urut di ujung kanan kolom terakhir.
                         Semua baris ada di HTML; yang bukan 5 terbaru diberi class "hidden" dan dibuka oleh filter status. -->
                    <div id="tabel-pinjam" class="hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 xl:block">
                        <table class="w-full text-sm">
                            <caption class="sr-only">Daftar permintaan peminjaman</caption>
                            <thead>
                                <tr class="border-b border-gray-200 text-left text-gray-500 text-xs">
                                    <th scope="col" class="px-6 py-4 font-semibold bg-gray-50 rounded-tl-2xl transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Peminjam</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Alat</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Mulai Pinjam</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Batas Pengembalian</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Pengembalian</th>
                                    <th scope="col" class="px-6 py-4 font-semibold bg-gray-50 rounded-tr-2xl transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>Status</span>

                                            <!-- Tombol urut berada di ujung kanan kolom -->
                                            <div class="urut-wrapper relative">
                                                <button type="button" aria-haspopup="true" aria-expanded="false" aria-label="Urutkan daftar" title="Urutkan"
                                                        class="tombol-menu-urut cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-lg border border-blue-300 bg-white text-blue-600 transition-all duration-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500
                                                               aria-[expanded=true]:border-blue-600 aria-[expanded=true]:bg-blue-50 aria-[expanded=true]:text-blue-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                                    </svg>
                                                </button>

                                                <div role="group" aria-label="Urutkan daftar" class="menu-urut hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-30 text-left font-normal">
                                                    <p class="px-4 pt-1.5 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Urutkan berdasarkan</p>

                                                    <button type="button" data-urut="status" aria-pressed="false"
                                                            class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                                                   aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                                        <span>Status</span>
                                                        <span class="inline-flex items-center gap-1.5 text-xs">
                                                            <span class="label-urut">A–Z</span>
                                                            <svg class="ikon-urut w-3.5 h-3.5 rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                                            </svg>
                                                        </span>
                                                    </button>

                                                    <button type="button" data-urut="tanggal" aria-pressed="true"
                                                            class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                                                   aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                                        <span>Tanggal</span>
                                                        <span class="inline-flex items-center gap-1.5 text-xs">
                                                            <span class="label-urut">Terbaru</span>
                                                            <svg class="ikon-urut w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($baris as $b)
                                    @php $al = $b['alat_data']; @endphp
                                    <tr class="baris-pinjam group {{ $b['terbaru'] ? '' : 'hidden' }} cursor-pointer transition-colors duration-200 hover:bg-blue-50 active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500" tabindex="0" aria-label="Tinjau peminjaman {{ $al['nama'] }} oleh {{ $b['peminjam'] }}" data-url="{{ $b['detail_url'] }}" data-status="{{ $b['status'] }}" data-terbaru="{{ $b['terbaru'] ? '1' : '0' }}" data-label-status="{{ mb_strtolower($b['tampil']['label']) }}" data-tanggal="{{ $b['pinjam_ts'] }}">
                                        <td class="px-6 py-5">
                                            <div class="transition-transform duration-300 motion-safe:group-hover:translate-x-1.5">
                                                <p class="font-semibold text-gray-900 leading-tight transition-colors duration-200 group-hover:text-blue-700">{{ $b['peminjam'] }}</p>
                                                <p class="mt-0.5 text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $b['kelas'] }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5">
                                            <div class="flex items-center gap-3.5">
                                                <div class="relative w-12 h-12 shrink-0 rounded-xl border border-gray-100 bg-white overflow-hidden transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                                    <img src="{{ $al['foto_url'] }}" alt="" loading="lazy"
                                                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                                         class="w-full h-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                                    <div class="hidden absolute inset-0 items-center justify-center bg-blue-50 text-blue-400 [&:not(.hidden)]:flex">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$al['ikon']] !!}</svg>
                                                    </div>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $al['nama'] }}</p>
                                                    <p class="text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $al['sub'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5 font-semibold text-gray-800 whitespace-nowrap transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam_f'] }}</td>
                                        <td class="px-4 py-5 font-semibold text-gray-800 whitespace-nowrap transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali_f'] }}</td>
                                        <td class="px-4 py-5 whitespace-nowrap {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex items-center gap-1.5 {{ $b['tampil']['badge'] }} text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-110">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $b['tampil']['dot'] }}"></span>
                                                {{ $b['tampil']['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Kartu (layar di bawah xl): 1 kolom di HP, 2 kolom di sm, 3 kolom di lg -->
                    <div id="kartu-pinjam" class="xl:hidden grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($baris as $b)
                            @php $al = $b['alat_data']; @endphp
                            <article class="baris-pinjam group {{ $b['terbaru'] ? '' : 'hidden' }} min-w-0 cursor-pointer rounded-2xl border border-gray-200/80 bg-white shadow-sm p-5 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5 active:scale-[0.98] active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" tabindex="0" aria-label="Tinjau peminjaman {{ $al['nama'] }} oleh {{ $b['peminjam'] }}" data-url="{{ $b['detail_url'] }}" data-status="{{ $b['status'] }}" data-terbaru="{{ $b['terbaru'] ? '1' : '0' }}" data-label-status="{{ mb_strtolower($b['tampil']['label']) }}" data-tanggal="{{ $b['pinjam_ts'] }}">
                                <div class="pb-3 mb-3 border-b border-gray-100 transition-colors duration-200 group-hover:border-blue-200">
                                    <p class="font-semibold text-gray-900 leading-tight break-words transition-colors duration-200 group-hover:text-blue-700">{{ $b['peminjam'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 break-words transition-colors duration-200 group-hover:text-gray-700">{{ $b['kelas'] }}</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="relative w-12 h-12 shrink-0 rounded-xl border border-gray-100 bg-white overflow-hidden transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                        <img src="{{ $al['foto_url'] }}" alt="" loading="lazy"
                                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                             class="w-full h-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                        <div class="hidden absolute inset-0 items-center justify-center bg-blue-50 text-blue-400 [&:not(.hidden)]:flex">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$al['ikon']] !!}</svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 leading-snug break-words transition-colors duration-200 group-hover:text-blue-700">{{ $al['nama'] }}</p>
                                        <p class="text-xs text-gray-500 break-words transition-colors duration-200 group-hover:text-gray-700">{{ $al['sub'] }}</p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1.5 mt-3 {{ $b['tampil']['badge'] }} text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-105">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $b['tampil']['dot'] }}"></span>
                                    {{ $b['tampil']['label'] }}
                                </span>

                                <dl class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs transition-colors duration-200 group-hover:border-blue-200">
                                    <div>
                                        <dt class="text-gray-500 leading-snug">Mulai Pinjam</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam_f'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 leading-snug">Batas Pengembalian</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali_f'] }}</dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-gray-500 leading-snug">Pengembalian</dt>
                                        <dd class="mt-0.5 {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</dd>
                                    </div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                    @endif
                </div>

                @unless ($kosong)
                    <!-- Muncul kalau filter tidak punya hasil -->
                    <div id="hasil-kosong" class="hidden bg-white rounded-2xl border border-dashed border-gray-300 px-6 py-12 text-center transition-all duration-300 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-lg motion-safe:hover:-translate-y-1">
                        <p id="hasil-kosong-teks" class="font-semibold text-gray-700"></p>
                        <button type="button" class="reset-filter cursor-pointer rounded-lg px-3 py-1 text-sm text-blue-600 hover:bg-blue-100 hover:text-blue-800 font-semibold mt-1 transition-all duration-200 active:scale-95 active:bg-blue-100">Tampilkan semua</button>
                    </div>
                @endunless
            </div>
        </section>

        <!-- Bagian 2: Peralatan Tersedia (putih satunya / bg-gray-50) -->
        <section id="peralatan" class="scroll-mt-20 bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="group mb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700">Peralatan Tersedia</h2>
                    <p class="text-gray-500 text-sm mt-1 transition-colors duration-300 group-hover:text-gray-700">Kelola data dan ketersediaan alat ekskul Multimedia. Klik kotak + untuk menambah alat baru.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($alatList as $a)
                        <div id="alat-{{ $a['slug'] }}" class="group {{ $a['tersedia'] ? 'bg-white' : 'bg-gray-100' }} p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-900/15 hover:border-blue-400 motion-safe:hover:-translate-y-2">
                            <div class="{{ $a['tersedia'] ? 'bg-white border-gray-100' : 'bg-gray-200 border-gray-300' }} border rounded-xl h-44 relative flex justify-center items-center overflow-hidden transition-colors duration-300">
                                @if ($a['tersedia'])
                                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10 transition-all duration-300 group-hover:bg-green-600 group-hover:shadow-md motion-safe:group-hover:scale-110">Tersedia</span>
                                @else
                                    <span class="absolute top-3 right-3 bg-gray-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10 transition-all duration-300 group-hover:bg-gray-600 group-hover:shadow-md motion-safe:group-hover:scale-110">Dipinjam</span>
                                @endif

                                <img src="{{ $a['foto_url'] }}" alt="{{ $a['nama'] }}" loading="lazy"
                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                     class="object-contain h-full w-full p-4 transition-transform duration-500 ease-out motion-safe:group-hover:scale-105 {{ $a['tersedia'] ? '' : 'grayscale mix-blend-multiply' }}">

                                <!-- Muncul otomatis kalau file foto belum ada -->
                                <div class="hidden absolute inset-0 {{ $a['tersedia'] ? 'bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300 group-hover:text-blue-500' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400' }} items-center justify-center transition-colors [&:not(.hidden)]:flex">
                                    <svg class="w-16 h-16 transition-transform duration-500 motion-safe:group-hover:scale-125 motion-safe:group-hover:-rotate-6" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$a['ikon']] !!}</svg>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col flex-grow">
                                <h3 class="text-gray-900 font-bold text-base break-words transition-colors duration-300 group-hover:text-blue-700">{{ $a['nama'] }}</h3>
                                <p class="text-gray-500 text-xs mb-4 break-words transition-colors duration-300 group-hover:text-gray-700">{{ $a['sub'] }}</p>
                                <!-- Tombol Kelola membuka form edit alat (termasuk ganti foto). -->
                                <a href="{{ route('admin.alat.edit', $a['slug']) }}" class="mt-auto w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition-all duration-200 shadow-sm hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-600/40 active:scale-[0.98]">
                                    Kelola
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <!-- Kotak "Tambah Alat": garis biru putus-putus dengan ikon + dan teks "Tambah Alat" di tengah. Bebas klik yang mana saja.
                         Jumlahnya melengkapi baris terakhir sampai 4 kotak (lihat $jumlahKotakTambah di atas).
                         Di layar di bawah lg grid hanya 1-2 kolom, jadi cukup satu kotak lebar penuh supaya tidak ada empat kotak kembar menumpuk. -->
                    @for ($i = 0; $i < $jumlahKotakTambah; $i++)
                        <a href="{{ $urlTambahAlat }}" title="Tambah alat baru" aria-label="Tambah alat baru"
                           class="group {{ $i === 0 ? 'flex' : 'hidden lg:flex' }} col-span-full lg:col-span-1 min-h-[10rem] lg:min-h-[20rem] flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-blue-400 bg-white/60 p-6 text-blue-500 transition-all duration-300 hover:border-blue-600 hover:bg-blue-50 hover:text-blue-700 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-2 active:scale-[0.98] active:bg-blue-50 active:border-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-600 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/40 motion-safe:group-hover:scale-110 motion-safe:group-hover:rotate-90">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/>
                                </svg>
                            </span>
                            <span class="text-sm font-semibold text-blue-600 transition-colors duration-300 group-hover:text-blue-700">Tambah Alat</span>
                        </a>
                    @endfor
                </div>

                <!-- Pengingat foto: ikon biru supaya tertangkap mata, teks abu agar tidak terkesan peringatan bahaya -->
                <p class="group mt-6 flex items-start gap-2 text-xs sm:text-sm text-gray-600 transition-colors duration-200 hover:text-gray-900">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Foto hanya sebagai ilustrasi. Tampilan dan kondisi fisik alat dapat berbeda dari gambar.</span>
                </p>
            </div>
        </section>

        <!-- Bagian 3: Tentang (putih biasa lagi / bg-white) -->
        <section id="tentang" class="scroll-mt-20 bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12 space-y-16">

                <!-- Tentang SiLat -->
                <div class="grid md:grid-cols-2 gap-10 items-center">
                    <div class="group">
                        <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide transition-colors duration-300 group-hover:text-blue-800">Tentang</span>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 mb-4 transition-colors duration-300 group-hover:text-blue-700">Apa itu SiLat?</h2>
                        <p class="text-gray-600 leading-relaxed transition-colors duration-300 group-hover:text-gray-900">
                            SiLat (Sistem Peminjaman Alat) adalah platform website digital yang digunakan untuk mengajukan peminjaman alat atau inventaris milik Ekstrakurikuler Multimedia SMK Negeri 5 Surakarta. Siswa, guru, dan staf dapat mengajukan peminjaman kapan saja, baik untuk keperluan pembelajaran, dokumentasi, maupun kegiatan lainnya. Dengan SiLat, proses peminjaman menjadi lebih mudah, cepat, dan transparan.
                        </p>
                    </div>
                    <div class="group relative rounded-2xl overflow-hidden shadow-sm border border-gray-200/80 transition-all duration-300 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5">
                        <img src="{{ asset('images/tentang.jpg') }}" alt="Tentang SiLat" loading="lazy" class="w-full h-64 sm:h-80 object-cover transition-transform duration-700 ease-out motion-safe:group-hover:scale-105">

                        <button type="button" data-ganti-gambar data-judul="Ganti Gambar Tentang SiLat" data-action="{{ $urlGambar }}" data-field="gambar" data-jenis="tentang" data-kunci="tentang" data-fit="cover"
                                title="Ganti gambar" aria-label="Ganti gambar Tentang SiLat"
                                class="absolute top-3 right-3 z-10 w-10 h-10 {{ $kelasPensil }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonPensil }}"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tentang Ekskul Multimedia -->
                <div class="grid md:grid-cols-2 gap-10 items-center">
                    <div class="group relative rounded-2xl overflow-hidden shadow-sm border border-gray-200/80 order-2 md:order-1 transition-all duration-300 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5">
                        <img src="{{ asset('images/ekskul.jpg') }}" alt="Tentang Ekstrakurikuler Multimedia" loading="lazy" class="w-full h-64 sm:h-80 object-cover object-[50%_30%] transition-transform duration-700 ease-out motion-safe:group-hover:scale-105">

                        <button type="button" data-ganti-gambar data-judul="Ganti Gambar Ekskul Multimedia" data-action="{{ $urlGambar }}" data-field="gambar" data-jenis="tentang" data-kunci="ekskul" data-fit="cover"
                                title="Ganti gambar" aria-label="Ganti gambar Ekskul Multimedia"
                                class="absolute top-3 right-3 z-10 w-10 h-10 {{ $kelasPensil }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonPensil }}"/>
                            </svg>
                        </button>
                    </div>
                    <div class="group order-1 md:order-2">
                        <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide transition-colors duration-300 group-hover:text-blue-800">Tentang</span>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 mb-4 transition-colors duration-300 group-hover:text-blue-700">Ekskul Multimedia</h2>
                        <div class="space-y-4">
                            <p class="text-gray-600 leading-relaxed transition-colors duration-300 group-hover:text-gray-900">Ekstrakurikuler Multimedia merupakan kegiatan tambahan yang diselenggarakan SMK Negeri 5 Surakarta untuk mengembangkan bakat dan minat siswa, terutama dalam fotografi dan videografi.</p>
                            <p class="text-gray-600 leading-relaxed transition-colors duration-300 group-hover:text-gray-900">Ekstrakurikuler ini memiliki banyak kegiatan dan materi yang diberikan kepada para peserta, mulai dari cara mengoperasikan kamera, penggunaan aplikasi editing, mendesain poster digital, hingga membuat video pendek. Selain itu, peserta diharapkan dapat mengasah kemampuan dan keterampilannya di bidang teknologi mengikuti kemajuan dan perkembangan zaman.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Penutup halaman: putih satunya (bg-gray-50) dengan tombol kembali ke atas di tengah -->
        <div class="border-t border-gray-200 bg-gray-50 py-10 flex justify-center">
            <button type="button" id="btn-ke-atas"
                    class="inline-flex items-center gap-2 bg-white hover:bg-blue-600 text-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 text-sm font-semibold px-5 py-2.5 rounded-full shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-600/40 active:scale-[0.97]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                Kembali ke Atas
            </button>
        </div>

    </main>

    <!-- Form ganti gambar (dipakai banner dan gambar Tentang).
         Input file disembunyikan dan dibuka lewat ikon pensil; tujuan form, nama field, dan field tambahan
         diisi JavaScript dari atribut data-* tombol yang diklik. -->
    <form id="form-gambar" method="POST" action="{{ route('admin.banner.update') }}" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="file" name="banner" id="input-gambar" accept="image/jpeg,image/png,image/webp">
        <span id="field-tambahan"></span>
    </form>

    <!-- Dialog pratinjau gambar (max-h supaya tetap muat di HP yang diputar mendatar) -->
    <dialog id="dialog-gambar" class="m-auto w-[calc(100%-2rem)] max-w-lg max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl p-0 shadow-xl backdrop:bg-slate-900/60">
        <div class="p-5 sm:p-6">
            <h3 id="judul-gambar" class="text-lg font-bold text-gray-900">Ganti Gambar</h3>
            <p class="text-sm text-gray-500 mt-1">Periksa pratinjau gambar baru sebelum disimpan.</p>

            <div class="mt-4 rounded-xl overflow-hidden border border-gray-200 bg-gray-100 aspect-[16/7]">
                <img id="pratinjau-gambar" src="" alt="Pratinjau gambar" class="w-full h-full object-contain">
            </div>
            <p id="info-gambar" class="text-xs text-gray-400 mt-2 truncate"></p>
            <p id="galat-gambar" class="hidden text-sm font-medium text-red-600 mt-2"></p>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="batal-gambar" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all duration-200 active:scale-[0.98]">Batal</button>
                <button type="submit" form="form-gambar" id="simpan-gambar" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white transition-all duration-200 hover:shadow-lg hover:shadow-blue-600/30 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">Simpan Gambar</button>
            </div>
        </div>
    </dialog>

    <!-- Script -->
    <script>
        // ===== Link anchor tanpa # di bilah alamat =====
        (function () {
            var hashAwal = window.location.hash;
            var menu = document.getElementById('menu-mobile');

            function bersihkanUrl() {
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }

            document.addEventListener('click', function (event) {
                var link = event.target.closest('a[href^="#"]');
                if (!link) return;

                event.preventDefault();

                // Tautan placeholder (href="#", mis. kotak Tambah Alat sebelum route-nya dibuat): jangan lakukan apa-apa
                if (link.getAttribute('href') === '#') return;

                if (menu) menu.removeAttribute('open');

                var id = link.getAttribute('href').slice(1);
                var target = document.getElementById(id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    bersihkanUrl();
                }
            });

            // Menu mobile ikut menutup saat mengetuk di luar menu atau menekan Esc
            if (menu) {
                document.addEventListener('click', function (event) {
                    if (menu.hasAttribute('open') && !menu.contains(event.target)) menu.removeAttribute('open');
                });
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') menu.removeAttribute('open');
                });
            }

            // Datang dari halaman lain lewat /#tentang (atau setelah Setujui/Tolak akun lewat /#akun): tetap gulir ke bagian itu, lalu hapus #
            if (hashAwal) {
                bersihkanUrl();
                window.addEventListener('load', function () {
                    var target = document.getElementById(decodeURIComponent(hashAwal.slice(1)));
                    if (target) target.scrollIntoView({ block: 'start' });
                });
            }
        })();

        // ===== Bayangan navbar muncul saat halaman digulir =====
        (function () {
            var navbar = document.getElementById('navbar');
            function periksa() { navbar.classList.toggle('shadow-md', window.scrollY > 8); }
            window.addEventListener('scroll', periksa, { passive: true });
            periksa();
        })();

        // ===== Tombol "Kembali ke Atas" (penutup halaman) =====
        (function () {
            var tombol = document.getElementById('btn-ke-atas');
            if (!tombol) return;
            tombol.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();

        // ===== Ganti gambar (ikon pensil: banner dan gambar Tentang) =====
        (function () {
            var daftarTombol = document.querySelectorAll('[data-ganti-gambar]');
            var input = document.getElementById('input-gambar');
            var form = document.getElementById('form-gambar');
            var tambahan = document.getElementById('field-tambahan');
            var dialog = document.getElementById('dialog-gambar');
            var judul = document.getElementById('judul-gambar');
            var pratinjau = document.getElementById('pratinjau-gambar');
            var info = document.getElementById('info-gambar');
            var galat = document.getElementById('galat-gambar');
            var simpan = document.getElementById('simpan-gambar');
            var batal = document.getElementById('batal-gambar');
            if (!daftarTombol.length || !input || !dialog) return;

            var MAKS = 4 * 1024 * 1024; // 4 MB, sama dengan aturan di controller
            var TIPE = ['image/jpeg', 'image/png', 'image/webp'];
            var urlSementara = null;
            var sedangMenyimpan = false;

            daftarTombol.forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    // Atur tujuan form sesuai tombol yang diklik
                    form.action = tombol.dataset.action;
                    input.name = tombol.dataset.field;
                    judul.textContent = tombol.dataset.judul || 'Ganti Gambar';

                    // Banner dan gambar Tentang dipotong memenuhi bingkai
                    var penuh = tombol.dataset.fit === 'cover';
                    pratinjau.classList.toggle('object-cover', penuh);
                    pratinjau.classList.toggle('object-contain', !penuh);

                    // Field tambahan (jenis dan kunci) dibuat ulang tiap kali
                    tambahan.innerHTML = '';
                    ['jenis', 'kunci'].forEach(function (nama) {
                        if (!tombol.dataset[nama]) return;
                        var h = document.createElement('input');
                        h.type = 'hidden';
                        h.name = nama;
                        h.value = tombol.dataset[nama];
                        tambahan.appendChild(h);
                    });

                    input.click();
                });
            });

            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) return;

                if (urlSementara) URL.revokeObjectURL(urlSementara);
                urlSementara = URL.createObjectURL(file);
                pratinjau.src = urlSementara;
                info.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';

                var pesan = '';
                if (TIPE.indexOf(file.type) === -1) pesan = 'Format gambar harus JPG, PNG, atau WebP.';
                else if (file.size > MAKS) pesan = 'Ukuran gambar maksimal 4 MB.';

                galat.textContent = pesan;
                galat.classList.toggle('hidden', pesan === '');
                simpan.disabled = pesan !== '';

                dialog.showModal();
            });

            batal.addEventListener('click', function () { dialog.close(); });

            // Ditutup lewat Batal atau Esc: kosongkan pilihan file
            dialog.addEventListener('close', function () {
                if (!sedangMenyimpan) input.value = '';
            });

            form.addEventListener('submit', function () {
                sedangMenyimpan = true;
                simpan.disabled = true;
                simpan.textContent = 'Menyimpan...';
            });
        })();

        // ===== Search Dropdown =====
        const daftarAlat = @json($daftarPencarian);

        const searchInput = document.getElementById('search-input');
        const searchDropdown = document.getElementById('search-dropdown');

        // Membungkus bagian teks yang cocok dengan ketikan ke dalam <span> biru
        function escapeHtml(teks) {
            return String(teks).replace(/[&<>"']/g, function (karakter) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[karakter];
            });
        }

        function highlightMatch(teks, kata) {
            if (!kata) return escapeHtml(teks);
            const index = teks.toLowerCase().indexOf(kata.toLowerCase());
            if (index === -1) return escapeHtml(teks);

            return escapeHtml(teks.slice(0, index)) +
                '<span class="text-blue-600 font-bold">' + escapeHtml(teks.slice(index, index + kata.length)) + '</span>' +
                escapeHtml(teks.slice(index + kata.length));
        }

        function renderSearchDropdown(daftar, kata) {
            if (daftar.length === 0) {
                searchDropdown.innerHTML =
                    '<div class="flex flex-col items-center justify-center text-center py-8 px-4">' +
                        '<svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15zM9 9l6 6m0-6l-6 6"/>' +
                        '</svg>' +
                        '<p class="text-sm font-semibold text-gray-500">Alat tidak ditemukan</p>' +
                        '<p class="text-xs text-gray-400 mt-1">Coba kata kunci lain</p>' +
                    '</div>';
                searchDropdown.classList.remove('hidden');
                return;
            }

            searchDropdown.innerHTML = daftar.map(function (alat) {
                return '<button type="button" onclick="pilihAlat(\'' + escapeHtml(alat.slug) + '\')" class="group w-full text-left flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-blue-50 hover:pl-6 active:bg-blue-50 transition-all duration-200">' +
                    '<img src="' + escapeHtml(alat.foto) + '" alt="" onerror="fotoRusak(this)" class="w-10 h-10 rounded-lg object-contain shrink-0 bg-blue-50 transition-transform duration-200 group-hover:scale-105">' +
                    '<span class="flex-1 min-w-0">' +
                        '<span class="block text-sm font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">' + highlightMatch(alat.nama, kata) + '</span>' +
                        '<span class="block text-xs text-gray-400 truncate">' + highlightMatch(alat.sub, kata) + '</span>' +
                    '</span>' +
                    '</button>';
            }).join('');

            searchDropdown.classList.remove('hidden');
        }

        searchInput.addEventListener('focus', function () {
            renderSearchDropdown(daftarAlat, searchInput.value);
        });

        searchInput.addEventListener('input', function () {
            const kata = searchInput.value.trim();
            const hasil = daftarAlat.filter(function (alat) {
                return (alat.nama + ' ' + alat.sub).toLowerCase().includes(kata.toLowerCase());
            });
            renderSearchDropdown(hasil, kata);
        });

        // Klik alat di pencarian -> gulir ke kartu alat tersebut dan beri sorotan sebentar
        function pilihAlat(slug) {
            searchDropdown.classList.add('hidden');
            searchInput.value = '';

            var kartu = document.getElementById('alat-' + slug);
            if (!kartu) return;

            kartu.scrollIntoView({ behavior: 'smooth', block: 'center' });
            kartu.classList.add('ring-2', 'ring-blue-500');
            setTimeout(function () { kartu.classList.remove('ring-2', 'ring-blue-500'); }, 1500);
        }

        document.addEventListener('click', function (event) {
            var wrapper = document.getElementById('search-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                searchDropdown.classList.add('hidden');
            }
        });

        // ===== Filter + Urutan Permintaan Peminjaman =====
        (function () {
            const kartuFilter = document.querySelectorAll('.filter-kartu');
            if (kartuFilter.length === 0) return;

            const semuaBaris = document.querySelectorAll('#daftar-pinjam-isi .baris-pinjam');
            const kosong = semuaBaris.length === 0;

            const barisTabel = document.querySelectorAll('#tabel-pinjam .baris-pinjam');
            const wadahTabel = document.querySelector('#tabel-pinjam tbody');
            const wadahKartu = document.getElementById('kartu-pinjam');   // null kalau belum ada permintaan
            const isiDaftar = document.getElementById('daftar-pinjam-isi');
            const hasilKosong = document.getElementById('hasil-kosong');  // null kalau belum ada permintaan
            const hasilKosongTeks = document.getElementById('hasil-kosong-teks');
            const infoFilter = document.getElementById('info-filter');
            const infoFilterTeks = document.getElementById('info-filter-teks');
            const resetToolbar = document.getElementById('reset-toolbar');
            const linkRiwayat = document.getElementById('link-riwayat');
            const tombolUrut = document.querySelectorAll('.tombol-urut');
            const wrapperUrut = document.querySelectorAll('.urut-wrapper');
            const MAKS_TERBARU = 5;   // sama dengan batas di bagian PHP atas
            let filterAktif = 'semua';

            // Klik / Enter / Spasi pada baris = buka halaman detail (diabaikan selama URL masih placeholder "#")
            function bukaDetail(baris) {
                const url = baris.dataset.url;
                if (url && url !== '#') window.location.href = url;
            }

            semuaBaris.forEach(function (baris) {
                baris.addEventListener('click', function () { bukaDetail(baris); });
                baris.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        bukaDetail(baris);
                    }
                });
            });

            // ----- Menu urutan (ikon di header tabel, dan di baris keterangan untuk layar kecil) -----
            function tutupSemuaMenu() {
                wrapperUrut.forEach(function (w) {
                    w.querySelector('.menu-urut').classList.add('hidden');
                    w.querySelector('.tombol-menu-urut').setAttribute('aria-expanded', 'false');
                });
            }

            wrapperUrut.forEach(function (w) {
                const tombol = w.querySelector('.tombol-menu-urut');
                const menu = w.querySelector('.menu-urut');

                tombol.addEventListener('click', function () {
                    const bukaBaru = menu.classList.contains('hidden');
                    tutupSemuaMenu();
                    if (bukaBaru) {
                        menu.classList.remove('hidden');
                        tombol.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // Klik di luar menu = tutup
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.urut-wrapper')) tutupSemuaMenu();
            });

            // Tekan Esc = tutup
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') tutupSemuaMenu();
            });

            // ----- Urutan -----
            // Tiap kunci mengingat arahnya sendiri. Bawaan: Status A-Z, Tanggal Terbaru. Yang aktif saat ini: Tanggal.
            const urut = { kunci: 'tanggal', arah: { status: 'asc', tanggal: 'desc' } };
            const teksArah = {
                status:  { asc: 'A–Z',    desc: 'Z–A' },
                tanggal: { asc: 'Terlama', desc: 'Terbaru' }
            };

            function terapkanUrutan() {
                [wadahTabel, wadahKartu].filter(Boolean).forEach(function (wadah) {
                    const item = Array.from(wadah.querySelectorAll('.baris-pinjam'));
                    item.sort(function (a, b) {
                        const selisihTanggal = Number(a.dataset.tanggal) - Number(b.dataset.tanggal);
                        let hasil;
                        if (urut.kunci === 'status') {
                            hasil = a.dataset.labelStatus.localeCompare(b.dataset.labelStatus, 'id');
                            if (hasil === 0) hasil = selisihTanggal;   // status sama: urutkan menurut tanggal
                        } else {
                            hasil = selisihTanggal;
                        }
                        return urut.arah[urut.kunci] === 'asc' ? hasil : -hasil;
                    });
                    item.forEach(function (el) { wadah.appendChild(el); });
                });

                tombolUrut.forEach(function (tombol) {
                    const kunci = tombol.dataset.urut;
                    tombol.setAttribute('aria-pressed', kunci === urut.kunci ? 'true' : 'false');
                    tombol.querySelector('.label-urut').textContent = teksArah[kunci][urut.arah[kunci]];
                    // Panah ke atas = ascending, ke bawah = descending
                    tombol.querySelector('.ikon-urut').classList.toggle('rotate-180', urut.arah[kunci] === 'asc');
                });
            }

            tombolUrut.forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    const kunci = tombol.dataset.urut;
                    if (urut.kunci === kunci) {
                        // Klik opsi yang sedang aktif = balik arah
                        urut.arah[kunci] = urut.arah[kunci] === 'asc' ? 'desc' : 'asc';
                    } else {
                        urut.kunci = kunci;
                    }
                    terapkanUrutan();
                    tutupSemuaMenu();
                });
            });

            // ----- Filter -----
            // "Permintaan Terbaru" ('semua') hanya menampilkan baris bertanda data-terbaru="1" (maksimal 5).
            // Kartu status lain menampilkan SEMUA baris berstatus itu, termasuk yang lebih lama dari 5 terbaru.
            function terapkanFilter(kode) {
                filterAktif = kode;

                // Tampilan aktif diatur lewat CSS (variant aria-[pressed=true]), cukup ubah atribut aria-pressed
                kartuFilter.forEach(function (kartu) {
                    kartu.setAttribute('aria-pressed', kartu.dataset.filter === kode ? 'true' : 'false');
                });
                tutupSemuaMenu();

                // Belum ada permintaan: tidak ada baris yang perlu disaring
                if (kosong) return;

                semuaBaris.forEach(function (baris) {
                    const cocok = kode === 'semua'
                        ? baris.dataset.terbaru === '1'
                        : baris.dataset.status === kode;
                    baris.classList.toggle('hidden', !cocok);
                });

                let tampil = 0;
                barisTabel.forEach(function (baris) {
                    if (!baris.classList.contains('hidden')) tampil++;
                });

                isiDaftar.classList.toggle('hidden', tampil === 0);
                hasilKosong.classList.toggle('hidden', tampil !== 0);
                infoFilter.classList.toggle('hidden', tampil === 0);

                // "Permintaan Terbaru" -> tautan riwayat saja; status lain -> tombol kembali saja
                resetToolbar.classList.toggle('hidden', kode === 'semua');
                linkRiwayat.classList.toggle('hidden', kode !== 'semua');

                if (kode === 'semua') {
                    infoFilterTeks.textContent = tampil >= MAKS_TERBARU
                        ? 'Menampilkan Maksimal ' + MAKS_TERBARU + ' Permintaan Terakhir'
                        : 'Menampilkan ' + tampil + ' Permintaan Terakhir';
                } else {
                    const label = document.querySelector('.filter-kartu[data-filter="' + kode + '"]').dataset.label;
                    infoFilterTeks.textContent = 'Menampilkan ' + tampil + ' Permintaan';
                    hasilKosongTeks.textContent = 'Belum ada permintaan berstatus ' + label.toLowerCase();
                }
            }

            kartuFilter.forEach(function (kartu) {
                kartu.addEventListener('click', function () {
                    const kode = kartu.dataset.filter;
                    // Klik kartu status yang sedang aktif sekali lagi = kembali ke "Permintaan Terbaru"
                    terapkanFilter(kode !== 'semua' && filterAktif === kode ? 'semua' : kode);
                });
            });

            document.querySelectorAll('.reset-filter').forEach(function (tombol) {
                tombol.addEventListener('click', function () { terapkanFilter('semua'); });
            });

            terapkanUrutan();
            terapkanFilter('semua');
        })();
    </script>
</body>
</html>