<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiLat Multimedia - Sistem Peminjaman Alat</title>
    <meta name="description" content="SiLat adalah sistem peminjaman alat multimedia Ekstrakurikuler Multimedia SMK Negeri 5 Surakarta.">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== Efek gerak =====
           Semua gerakan hanya aktif bagi pengguna yang tidak memilih "kurangi gerakan" di perangkatnya. */

        /* Cincin fokus untuk pengguna keyboard (nol spesifisitas, jadi gaya khusus di elemen tetap menang) */
        :where(a, button, summary, input):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }

        @keyframes dropdown-muncul { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes footer-goyang { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(-10deg); } 75% { transform: rotate(10deg); } }

        @media (prefers-reduced-motion: no-preference) {
            /* Dropdown (pencarian dan menu HP) muncul halus */
            .dropdown-animasi { animation: dropdown-muncul .16s ease-out; }
        }

        @media (prefers-reduced-motion: no-preference) and (hover: hover) {
            .footer-link:hover svg { animation: footer-goyang .4s ease-in-out; }
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
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

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
        ];
    });

    $ikonAlat = [
        'kamera' => '<path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>',
        'video'  => '<path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'hp'     => '<path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'drone'  => '<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="5" cy="18" r="2.2"/><circle cx="19" cy="18" r="2.2"/><path d="M7 7.5l3 3m7-3l-3 3M7 16.5l3-3m7 3l-3-3"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.5"/>',
        'gimbal' => '<rect x="7" y="4" width="10" height="5" rx="1.5"/><path d="M4 6.5h3m10 0h3M12 9v4"/><rect x="9.5" y="13" width="5" height="8" rx="2"/>',
        'tripod' => '<rect x="8.5" y="2.5" width="7" height="4" rx="1"/><path d="M12 6.5V11m0 0l-5 10m5-10v10m0-10l5 10"/>',
    ];

    $daftarPencarian = collect($alatList)->map(function ($a) {
        return [
            'nama' => $a['nama'],
            'sub'  => $a['sub'],
            'foto' => $a['foto_url'],
        ];
    })->values();

    $langkah = [
        ['judul' => 'Login / Daftar Akun',        'isi' => 'Masuk dengan akun siswa, guru, atau staf. Belum punya akun? Daftar dulu lewat halaman registrasi.',                               'path' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['judul' => 'Pilih Alat & Isi Form',      'isi' => 'Pilih alat yang ingin dipinjam, lalu isi tanggal pinjam dan tanggal kembali (maksimal 14 hari). Satu form untuk satu alat.',      'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['judul' => 'Tunggu Persetujuan Admin',   'isi' => 'Admin akan memverifikasi pengajuanmu. Setelah disetujui, kamu bisa mengambil alat sesuai jadwal.',                                 'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['judul' => 'Kembalikan Tepat Waktu',     'isi' => 'Kembalikan alat sesuai jadwal. Kondisi alat akan dicatat oleh admin saat proses pengembalian.',                                    'path' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
    ];

    // Ikon footer. 'fill' => 'none' berarti ikon garis, 'currentColor' berarti ikon solid.
    $ikon = [
        'ig'   => ['fill' => 'none',         'isi' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>'],
        'mail' => ['fill' => 'none',         'isi' => '<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
        'tel'  => ['fill' => 'none',         'isi' => '<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>'],
        'web'  => ['fill' => 'none',         'isi' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 3.5 6 3.5 9s-1 6.5-3.5 9c-2.5-2.5-3.5-6-3.5-9s1-6.5 3.5-9z"/>'],
        'yt'   => ['fill' => 'currentColor', 'isi' => '<path d="M21.6 7.2s-.21-1.49-.86-2.14c-.82-.86-1.74-.86-2.16-.91C15.6 4 12 4 12 4h-.01s-3.6 0-6.58.15c-.42.05-1.34.05-2.16.91C2.6 5.71 2.4 7.2 2.4 7.2S2.19 8.94 2.19 10.68v1.63c0 1.74.21 3.48.21 3.48s.21 1.49.85 2.14c.82.86 1.9.83 2.38.92 1.72.17 7.37.22 7.37.22s3.6-.01 6.59-.16c.42-.05 1.34-.05 2.16-.91.65-.65.86-2.14.86-2.14s.21-1.74.21-3.48v-1.63c0-1.74-.21-3.48-.21-3.48zM9.99 14.5v-5l5 2.51-5 2.49z"/>'],
        'fb'   => ['fill' => 'currentColor', 'isi' => '<path d="M14 9h3V5h-3c-2.21 0-4 1.79-4 4v2H7v4h3v6h4v-6h3l1-4h-4V9c0-.55.45-1 1-1z"/>'],
    ];

    // Grup footer: tiga kolom vertikal dengan gaya seragam.
    $grupFooter = [
        [
            'judul' => 'Ekskul Multimedia',
            'item'  => [
                ['ikon' => 'ig',   'teks' => '@mulmedsk5a',           'tip' => 'Instagram Ekskul Multimedia',      'url' => 'https://www.instagram.com/mulmedsk5a'],
                ['ikon' => 'mail', 'teks' => 'mulmedsk5.a@gmail.com', 'tip' => 'Kirim email ke Ekskul Multimedia', 'url' => 'mailto:mulmedsk5.a@gmail.com'],
            ],
        ],
        [
            'judul' => 'Kontak Sekolah',
            'item'  => [
                ['ikon' => 'tel',  'teks' => '0271 713916',   'tip' => 'Telepon SMK Negeri 5 Surakarta',       'url' => 'tel:0271713916'],
                ['ikon' => 'mail', 'teks' => 'info@smkn.net', 'tip' => 'Kirim email ke SMK Negeri 5 Surakarta', 'url' => 'mailto:info@smkn.net'],
            ],
        ],
        [
            'judul' => 'Tautan Sekolah',
            'item'  => [
                ['ikon' => 'web', 'teks' => 'Website',   'tip' => 'smkn5surakarta.sch.id',           'url' => 'https://www.smkn5surakarta.sch.id/'],
                ['ikon' => 'ig',  'teks' => 'Instagram', 'tip' => 'Instagram @smknegeri5surakarta',  'url' => 'https://www.instagram.com/smknegeri5surakarta'],
                ['ikon' => 'yt',  'teks' => 'YouTube',   'tip' => 'YouTube @smknegeri5surakarta592', 'url' => 'https://youtube.com/@smknegeri5surakarta592'],
                ['ikon' => 'fb',  'teks' => 'Facebook',  'tip' => 'Facebook sekolah',                'url' => 'https://www.facebook.com/pakazissolo'],
            ],
        ],
    ];
@endphp

    <!-- Navbar Atas (bayangannya muncul saat halaman digulir) -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
            <!-- Logo & Brand -->
            <a href="{{ url('/') }}" class="group flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-18 h-18 transition-transform duration-300 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-3">
                <div>
                    <span class="text-blue-600 group-hover:text-blue-700 transition-colors duration-300 font-extrabold block leading-tight text-2xl">SiLat</span>
                    <span class="text-gray-700 group-hover:text-gray-900 transition-colors duration-300 font-semibold text-xs block">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-[30rem] hidden md:block">
                <div class="group/search relative" id="search-wrapper">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 group-focus-within/search:text-blue-600 transition-colors z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </span>
                    <input type="text" id="search-input" autocomplete="off" placeholder="Cari Alat Multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 hover:border-blue-400 hover:bg-white hover:shadow-md hover:shadow-blue-900/5 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:shadow-md transition-all duration-200">

                    <!-- Dropdown Hasil Pencarian -->
                    <div id="search-dropdown" class="dropdown-animasi hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 max-h-80 overflow-y-auto z-50">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Nav Links, Login, Menu Mobile -->
            <div class="flex items-center gap-3 shrink-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm font-medium text-gray-700">
                    <a href="#peralatan" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Peralatan</a>
                    <a href="#tentang" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Tentang</a>
                    <a href="#cara-pinjam" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Cara Pinjam</a>
                </nav>

                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="group inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">
                            <svg class="w-4 h-4 transition-transform duration-200 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Login
                        </a>
                    @endauth
                @endif

                <!-- Menu mobile (tanpa JS, memakai <details>) -->
                <details id="menu-mobile" class="lg:hidden relative">
                    <summary aria-label="Buka menu" class="list-none cursor-pointer w-10 h-10 flex items-center justify-center rounded-lg text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 active:scale-95 [&::-webkit-details-marker]:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </summary>
                    <div class="dropdown-animasi absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 text-sm font-medium text-gray-700">
                        <a href="#peralatan" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6">Peralatan</a>
                        <a href="#tentang" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6">Tentang</a>
                        <a href="#cara-pinjam" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6">Cara Pinjam</a>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <!-- Hero Section. Teks membesar pelan saat kursor di atasnya (sama seperti dashboard) -->
    <div id="hero" class="relative bg-slate-900 w-full h-[420px] sm:h-[480px] md:h-[540px] flex flex-col justify-center items-center text-center px-4 overflow-hidden">
        <!-- Lapis 1: Foto banner, blur ringan, opacity penuh (scale-110 menutup tepi yang pudar akibat blur dan memberi ruang saat bergeser) -->
        <img id="hero-foto" src="{{ \App\Support\Banner::url() }}" alt="" fetchpriority="high" class="absolute inset-0 w-full h-full object-cover scale-110 blur-[1px]">

        <!-- Lapis 2: Bayangan hanya di tengah supaya teks terbaca. Gelapnya ditahan di sepanjang area teks (0% sampai 40%),
             lalu memudar ke tepi sehingga foto di pinggir tetap cerah -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_75%_60%_at_50%_50%,rgba(15,23,42,0.65)_0%,rgba(15,23,42,0.55)_40%,rgba(15,23,42,0)_100%)]"></div>

        <!-- Lapis 3: Teks -->
        <div class="group relative z-10 max-w-4xl transition-transform duration-500 ease-out motion-safe:hover:scale-105">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-bold mb-4 tracking-tight [text-shadow:0_1px_3px_rgb(0_0_0_/_60%),0_4px_28px_rgb(0_0_0_/_55%)]">
                <span class="text-blue-400 transition-colors duration-300 group-hover:text-blue-300">SiLat</span>
                <span class="text-white"> Multimedia</span>
            </h1>
            <p class="text-white font-medium text-lg sm:text-xl md:text-2xl [text-shadow:0_1px_3px_rgb(0_0_0_/_65%),0_3px_20px_rgb(0_0_0_/_60%)]">Sistem Peminjaman Alat Multimedia</p>
        </div>
    </div>

    <!-- Bagian Katalog Alat -->
    <main id="peralatan" class="scroll-mt-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow w-full">
        <div class="group mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700">Peralatan Tersedia</h2>
            <p class="text-gray-500 text-sm mt-1 transition-colors duration-300 group-hover:text-gray-700">Pilih alat yang kamu butuhkan, lalu login untuk mengajukan peminjaman.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($alatList as $a)
                <!-- Pembungkus untuk efek muncul bertahap; kartu di dalamnya punya efek hover sendiri -->
                <div class="flex">
                    <div id="alat-{{ $a['slug'] }}" class="group flex-1 {{ $a['tersedia'] ? 'bg-white' : 'bg-gray-100' }} p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-900/15 hover:border-blue-400 motion-safe:hover:-translate-y-2">
                        <div class="{{ $a['tersedia'] ? 'bg-white border-gray-100' : 'bg-gray-200 border-gray-300' }} border rounded-xl h-44 relative flex justify-center items-center overflow-hidden transition-colors duration-300">
                            @if ($a['tersedia'])
                                <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10 transition-all duration-300 group-hover:bg-green-600 group-hover:shadow-md motion-safe:group-hover:scale-110">Tersedia</span>
                            @else
                                <span class="absolute top-3 right-3 bg-gray-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10 transition-all duration-300 group-hover:bg-gray-600 group-hover:shadow-md motion-safe:group-hover:scale-110">Dipinjam</span>
                            @endif

                            @if ($a['foto_url'])
                                <img src="{{ $a['foto_url'] }}" alt="{{ $a['nama'] }}" loading="lazy"
                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                     class="object-contain h-full w-full p-2 transition-transform duration-500 ease-out motion-safe:group-hover:scale-110 {{ $a['tersedia'] ? '' : 'grayscale mix-blend-multiply' }}">
                            @endif

                            <!-- Muncul otomatis kalau file foto belum ada -->
                            <div class="{{ $a['foto_url'] ? 'hidden' : '' }} absolute inset-0 {{ $a['tersedia'] ? 'bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300 group-hover:text-blue-500' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400' }} items-center justify-center transition-colors [&:not(.hidden)]:flex">
                                <svg class="w-16 h-16 transition-transform duration-500 motion-safe:group-hover:scale-125 motion-safe:group-hover:-rotate-6" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$a['ikon']] !!}</svg>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col flex-grow">
                            <h3 class="text-gray-900 font-bold text-base transition-colors duration-300 {{ $a['tersedia'] ? 'group-hover:text-blue-700' : '' }}">{{ $a['nama'] }}</h3>
                            <p class="text-gray-500 text-xs mb-4 transition-colors duration-300 {{ $a['tersedia'] ? 'group-hover:text-gray-700' : '' }}">{{ $a['sub'] }}</p>
                            @if ($a['tersedia'])
                                <a href="{{ route('login') }}" class="mt-auto w-full inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition-all duration-200 shadow-sm hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-600/40 active:scale-[0.98]">
                                    Pinjam Alat
                                </a>
                            @else
                                <button type="button" disabled class="mt-auto w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2.5 rounded-xl text-center cursor-not-allowed">
                                    Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pengingat: ikon diberi warna biru supaya tertangkap mata, teks tetap abu-abu agar tidak terkesan peringatan bahaya -->
        <p class="group mt-6 flex items-start gap-2 text-xs sm:text-sm text-gray-600 transition-colors duration-200 hover:text-gray-900">
            <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Foto hanya sebagai ilustrasi. Tampilan dan kondisi fisik alat dapat berbeda dari gambar.</span>
        </p>
    </main>

    <!-- Section: Tentang -->
    <section id="tentang" class="scroll-mt-20 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">

            <!-- Tentang SiLat -->
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="group">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide transition-colors duration-300 group-hover:text-blue-800">Tentang</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 mb-4 transition-colors duration-300 group-hover:text-blue-700">Apa itu SiLat?</h2>
                    <p class="text-gray-600 leading-relaxed transition-colors duration-300 group-hover:text-gray-900">
                        SiLat (Sistem Peminjaman Alat) adalah platform website digital yang digunakan untuk mengajukan peminjaman alat atau inventaris milik Ekstrakurikuler Multimedia SMK Negeri 5 Surakarta. Siswa, guru, dan staf dapat mengajukan peminjaman kapan saja, baik untuk keperluan pembelajaran, dokumentasi, maupun kegiatan lainnya. Dengan SiLat, proses peminjaman menjadi lebih mudah, cepat, dan transparan.
                    </p>
                </div>
                <div class="group rounded-2xl overflow-hidden shadow-sm border border-gray-200/80 transition-all duration-300 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5">
                    <img src="{{ asset('images/tentang.jpg') }}" alt="Tentang SiLat" loading="lazy" class="w-full h-64 sm:h-80 object-cover transition-transform duration-700 ease-out motion-safe:group-hover:scale-110">
                </div>
            </div>

            <!-- Tentang Ekskul Multimedia -->
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="group rounded-2xl overflow-hidden shadow-sm border border-gray-200/80 order-2 md:order-1 transition-all duration-300 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5">
                    <img src="{{ asset('images/ekskul.jpg') }}" alt="Tentang Ekstrakurikuler Multimedia" loading="lazy" class="w-full h-64 sm:h-80 object-cover object-[50%_30%] transition-transform duration-700 ease-out motion-safe:group-hover:scale-110">
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

    <!-- Section: Cara Pinjam -->
    <section id="cara-pinjam" class="scroll-mt-20 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="group text-center max-w-2xl mx-auto mb-12">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide transition-colors duration-300 group-hover:text-blue-800">Panduan</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 transition-colors duration-300 group-hover:text-blue-700">Cara Pinjam Alat</h2>
                <p class="text-gray-600 mt-3 transition-colors duration-300 group-hover:text-gray-900">Empat langkah mudah untuk mulai meminjam alat multimedia.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                @foreach ($langkah as $i => $l)
                    <div class="flex">
                        <div class="group relative flex-1 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm transition-all duration-300 hover:shadow-2xl hover:shadow-blue-900/15 hover:border-blue-400 motion-safe:hover:-translate-y-2">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white font-bold rounded-xl transition-all duration-300 group-hover:bg-blue-700 group-hover:shadow-lg group-hover:shadow-blue-600/30 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6">{{ $i + 1 }}</div>
                                <div class="w-10 h-10 flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/30 motion-safe:group-hover:scale-110 motion-safe:group-hover:rotate-6">
                                    <svg class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $l['path'] }}"/>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2 transition-colors duration-300 group-hover:text-blue-700">{{ $l['judul'] }}</h3>
                            <p class="text-gray-600 text-sm leading-relaxed transition-colors duration-300 group-hover:text-gray-900">{{ $l['isi'] }}</p>

                            @unless ($loop->last)
                                <!-- Penghubung antar langkah (hanya di layar lebar) -->
                                <span class="hidden lg:block absolute top-11 -right-6 w-6 h-px bg-blue-200 transition-colors duration-300 group-hover:bg-blue-500"></span>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center flex flex-col items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="group/btn inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold px-8 py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-600/40 motion-safe:hover:scale-105 active:scale-[0.98]">
                        Buka Dashboard
                        <svg class="w-5 h-5 transition-transform duration-200 group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="group/btn inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold px-8 py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-600/40 motion-safe:hover:scale-105 active:scale-[0.98]">
                        Pinjam Sekarang
                    </a>
                    @if (Route::has('register'))
                        <p class="text-sm text-gray-500">Belum punya akun? <a href="{{ route('register') }}" class="rounded-md px-1 -mx-1 text-blue-600 hover:bg-blue-50 hover:text-blue-800 font-semibold underline decoration-transparent hover:decoration-current underline-offset-4 transition-all duration-200">Daftar di sini</a></p>
                    @endif
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">

            <div class="grid lg:grid-cols-12 gap-x-12 gap-y-8 pb-8">

                <!-- Kiri: peta + alamat. Di layar kecil peta hanya pratinjau yang bisa diketuk (membuka Google Maps),
                     supaya jari tidak "tersangkut" di dalam peta saat menggulir halaman -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="relative h-56 rounded-2xl overflow-hidden border border-gray-200 bg-gray-100 transition-all duration-300 hover:border-blue-400 hover:shadow-xl hover:shadow-blue-900/10">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.201368359219!2d110.79482417476349!3d-7.5530087924607!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a14257d546c99%3A0xd7caf9142b4a2170!2sSMK%20Negeri%205%20Surakarta!5e0!3m2!1sid!2sid!4v1792193800974!5m2!1sid!2sid" title="Peta lokasi SMK Negeri 5 Surakarta" class="absolute inset-0 w-full h-full border-0 pointer-events-none lg:pointer-events-auto" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        <a href="https://www.google.com/maps/search/?api=1&amp;query=SMK+Negeri+5+Surakarta" target="_blank" rel="noopener noreferrer" aria-label="Buka peta di Google Maps" class="absolute inset-0 lg:hidden"></a>
                    </div>

                    <!-- Alamat di bawah peta: bisa diklik (membuka Google Maps), jadi diberi efek hover -->
                    <a href="https://www.google.com/maps/search/?api=1&amp;query=SMK+Negeri+5+Surakarta" target="_blank" rel="noopener noreferrer" aria-label="Buka alamat sekolah di Google Maps"
                       class="footer-link group flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-blue-600 transition-all duration-300 group-hover:text-blue-800 motion-safe:group-hover:scale-125 motion-safe:group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700 group-hover:text-blue-700 group-hover:underline underline-offset-4 leading-relaxed transition-colors duration-300">Jl. LU Adi Sucipto No. 42, Kerten, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57143</p>
                    </a>
                </div>

                <!-- Kanan: tiga kolom vertikal, rata atas, gaya seragam -->
                <div class="lg:col-span-7 grid sm:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-8 content-start">
                    @foreach ($grupFooter as $g)
                        <div class="group/grup min-w-0">
                            <h3 class="text-gray-900 font-bold text-sm uppercase tracking-wide transition-colors duration-300 group-hover/grup:text-blue-600">{{ $g['judul'] }}</h3>
                            <span class="block w-8 h-0.5 rounded-full bg-blue-600 mt-2 mb-4 transition-all duration-300 group-hover/grup:w-14"></span>

                            <ul class="space-y-3">
                                @foreach ($g['item'] as $it)
                                    @php $ic = $ikon[$it['ikon']]; @endphp
                                    <li>
                                        <a href="{{ $it['url'] }}" title="{{ $it['tip'] }}" @if (str_starts_with($it['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif class="footer-link group inline-flex items-center gap-3 max-w-full transition-transform duration-200 motion-safe:hover:translate-x-1">
                                            <span class="w-9 h-9 rounded-full bg-blue-50 group-hover:bg-blue-600 flex items-center justify-center transition-all duration-200 motion-safe:group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-blue-600/30 shrink-0">
                                                <svg class="w-4.5 h-4.5 text-blue-600 group-hover:text-white transition-colors" fill="{{ $ic['fill'] }}" stroke="{{ $ic['fill'] === 'none' ? 'currentColor' : 'none' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ic['isi'] !!}</svg>
                                            </span>
                                            <span class="min-w-0 break-words text-sm font-medium text-gray-700 group-hover:text-blue-600 group-hover:underline underline-offset-4 transition">{{ $it['teks'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Baris Bawah: garis abu tipis dan copyright -->
            <div class="border-t border-gray-200 pt-5 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500 [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
                <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
                <p>Mulmedska SMK Negeri 5 Surakarta</p>
            </div>
        </div>
    </footer>

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
                if (menu) menu.removeAttribute('open');

                var id = link.getAttribute('href').slice(1);
                var target = id ? document.getElementById(id) : null;
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                bersihkanUrl();
            });

            // Datang dari halaman lain lewat /#tentang: tetap gulir ke bagian itu, lalu hapus #
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

        // ===== Search Dropdown =====
        const daftarAlat = @json($daftarPencarian);
        const loginUrl = "{{ route('login') }}";

        const searchInput = document.getElementById('search-input');
        const searchDropdown = document.getElementById('search-dropdown');

        // Membungkus bagian teks yang cocok dengan ketikan ke dalam <span> biru
        function highlightMatch(teks, kata) {
            if (!kata) return teks;
            const index = teks.toLowerCase().indexOf(kata.toLowerCase());
            if (index === -1) return teks;

            return teks.slice(0, index) +
                '<span class="text-blue-600 font-bold">' + teks.slice(index, index + kata.length) + '</span>' +
                teks.slice(index + kata.length);
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
                return '<button type="button" onclick="pilihAlat()" class="group/item w-full text-left flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-blue-50 hover:pl-6 transition-all duration-200">' +
                    '<img src="' + alat.foto + '" alt="" onerror="fotoRusak(this)" class="w-10 h-10 rounded-lg object-cover shrink-0 bg-blue-50 transition-transform duration-200 group-hover/item:scale-110">' +
                    '<span class="flex-1 min-w-0">' +
                        '<span class="block text-sm font-semibold text-gray-800 group-hover/item:text-blue-700 transition-colors">' + highlightMatch(alat.nama, kata) + '</span>' +
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

        // Klik alat di pencarian -> arahkan ke login (pengunjung belum masuk)
        function pilihAlat() {
            window.location.href = loginUrl;
        }

        document.addEventListener('click', function (event) {
            var wrapper = document.getElementById('search-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                searchDropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>