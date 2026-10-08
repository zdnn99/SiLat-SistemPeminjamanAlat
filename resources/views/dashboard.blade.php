<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiLat Multimedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    @php
        $namaLengkap = Auth::user()->nama_lengkap ?? 'Pengguna';
        $namaSingkat = implode(' ', array_slice(explode(' ', $namaLengkap), 0, 2));
    @endphp

    <!-- Navbar Atas -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
            <!-- Logo & Brand -->
            <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 shrink-0">
                <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=100&h=100" alt="Logo SiLat" class="w-12 h-12 object-cover rounded-xl shadow-sm">
                <div>
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-xl hidden md:block">
                <div class="relative" id="search-wrapper">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 z-10">
                        <img src="https://img.icons8.com/ios-glyphs/30/4b5563/search--v1.png" alt="Search" class="w-4 h-4">
                    </span>
                    <input type="text" id="search-input" autocomplete="off" placeholder="Cari alat multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">

                    <!-- Dropdown Hasil Pencarian -->
                    <div id="search-dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 max-h-80 overflow-y-auto z-50">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Profil Dropdown -->
            <div class="relative shrink-0" id="profile-dropdown-wrapper">
                <button type="button" onclick="toggleProfileDropdown()" id="profile-dropdown-btn"
                    class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition">
                    <!-- Avatar: default huruf inisial, warna merah tua. Kalau nanti ada kolom foto_profil di User, ganti div ini jadi <img> -->
                    <div class="w-9 h-9 rounded-full bg-red-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr($namaLengkap, 0, 1)) }}
                    </div>
                    <span class="hidden md:block text-sm font-bold max-w-[140px] truncate bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        {{ $namaSingkat }}
                    </span>
                    <svg id="profile-chevron" class="w-4 h-4 text-gray-400 hidden md:block transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="profile-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $namaLengkap }}</p>
                        <span class="inline-block mt-1.5 bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">
                            {{ Auth::user()->nomor_induk ?? '-' }}
                        </span>
                    </div>

                    <!-- TODO: buat route & controller untuk halaman Profil Saya -->
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-blue-600 hover:bg-blue-50 transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>

                    <!-- TODO: buat route & controller untuk halaman Riwayat Peminjaman -->
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-purple-600 hover:bg-purple-50 transition">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Riwayat Peminjaman
                    </a>

                    <div class="border-t border-gray-100 my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section (Tidak diubah) -->
    <div class="relative bg-slate-900 w-full h-[380px] sm:h-[460px] md:h-[520px] flex flex-col justify-center items-center text-center px-4 overflow-hidden">
        <!-- Ganti src di bawah ini dengan foto banner asli (1480x804). object-cover akan otomatis menyesuaikan tanpa gepeng -->
        <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=2000&auto=format&fit=crop" alt="Studio Banner" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
        
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-3 tracking-tight">SiLat Multimedia</h1>
            <p class="text-slate-300 text-base sm:text-lg">Sistem Peminjaman Alat Multimedia</p>
        </div>
    </div>
    
    <!-- Bagian Katalog Alat -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow">
        <!-- Header Section -->
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Peralatan Tersedia</h2>
        </div>

        <!-- Grid 4 Kolom -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- 1. Sony A6000 (Tersedia) -->
            <div id="alat-sony-a6000" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400&auto=format&fit=crop" alt="Sony A6000" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Sony A6000</h3>
                    <p class="text-gray-500 text-xs mb-4">Mirrorless Camera</p>
                    <a href="{{ route('peminjaman.create', 'sony-a6000') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 2. DJI Mavic Mini (Dipinjam - HANYA GAMBAR YANG ABU-ABU) -->
            <div id="alat-dji-mavic-mini" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Dipinjam</span>
                    <img src="https://images.unsplash.com/photo-1527977966376-1c8408f9f108?q=80&w=400&auto=format&fit=crop" alt="DJI Mavic Mini" class="object-cover h-full w-full grayscale relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">DJI Mavic Mini</h3>
                    <p class="text-gray-500 text-xs mb-4">Drone</p>
                    <button disabled class="mt-auto w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2.5 rounded-xl text-center cursor-not-allowed">
                        Tidak Tersedia
                    </button>
                </div>
            </div>

            <!-- 3. Rode NT-USB (Tersedia) -->
            <div id="alat-rode-nt-usb" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=400&auto=format&fit=crop" alt="Rode NT-USB" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Rode NT-USB</h3>
                    <p class="text-gray-500 text-xs mb-4">Microphone</p>
                    <a href="{{ route('peminjaman.create', 'rode-nt-usb') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 4. Canon EOS 80D (Tersedia) -->
            <div id="alat-canon-eos-80d" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=400&auto=format&fit=crop" alt="Canon EOS 80D" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Canon EOS 80D</h3>
                    <p class="text-gray-500 text-xs mb-4">DSLR Camera</p>
                    <a href="{{ route('peminjaman.create', 'canon-eos-80d') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 5. Godox Softbox (Dipinjam - HANYA GAMBAR YANG ABU-ABU) -->
            <div id="alat-godox-softbox" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Dipinjam</span>
                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=400&auto=format&fit=crop" alt="Godox Softbox" class="object-cover h-full w-full grayscale relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Godox Softbox</h3>
                    <p class="text-gray-500 text-xs mb-4">Lighting Kit</p>
                    <button disabled class="mt-auto w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2.5 rounded-xl text-center cursor-not-allowed">
                        Tidak Tersedia
                    </button>
                </div>
            </div>

            <!-- 6. Sennheiser G4 (Tersedia) -->
            <div id="alat-sennheiser-g4" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=400&auto=format&fit=crop" alt="Sennheiser G4" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Sennheiser G4</h3>
                    <p class="text-gray-500 text-xs mb-4">Wireless Mic</p>
                    <a href="{{ route('peminjaman.create', 'sennheiser-g4') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 7. DJI Ronin-S (Tersedia) -->
            <div id="alat-dji-ronin-s" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1589189068066-c316711d96fe?q=80&w=400&auto=format&fit=crop" alt="DJI Ronin-S" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">DJI Ronin-S</h3>
                    <p class="text-gray-500 text-xs mb-4">Gimbal Stabilizer</p>
                    <a href="{{ route('peminjaman.create', 'dji-ronin-s') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 8. Blackmagic ATEM (Dipinjam - HANYA GAMBAR YANG ABU-ABU) -->
            <div id="alat-blackmagic-atem" class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-shadow">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Dipinjam</span>
                    <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=400&auto=format&fit=crop" alt="Blackmagic ATEM" class="object-cover h-full w-full grayscale relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Blackmagic ATEM</h3>
                    <p class="text-gray-500 text-xs mb-4">Switcher</p>
                    <button disabled class="mt-auto w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2.5 rounded-xl text-center cursor-not-allowed">
                        Tidak Tersedia
                    </button>
                </div>
            </div>

        </div>

        <!-- Bagian Data Peminjaman Saya -->
        <section class="mt-16">
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Peminjaman Saya</h2>
                <p class="text-gray-500 text-sm mt-1">Daftar pengajuan peminjaman alat yang pernah kamu buat</p>
            </div>

            <!-- TODO: hitung angka-angka ini dari query/collection data asli, bukan hardcode -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">1</p>
                        <p class="text-xs text-gray-500">Menunggu Persetujuan</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">1</p>
                        <p class="text-xs text-gray-500">Sedang Dipinjam</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">1</p>
                        <p class="text-xs text-gray-500">Selesai</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">1</p>
                        <p class="text-xs text-gray-500">Ditolak</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500 text-xs uppercase tracking-wide">
                                <th class="px-6 py-3.5 font-semibold">Alat</th>
                                <th class="px-6 py-3.5 font-semibold">Tanggal Pinjam</th>
                                <th class="px-6 py-3.5 font-semibold">Tanggal Kembali</th>
                                <th class="px-6 py-3.5 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <!-- TODO: ganti isi tbody ini dengan loop data asli dari database -->
                        <tbody class="divide-y divide-gray-100">

                            <!-- Dummy 1: Menunggu Persetujuan -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=100&auto=format&fit=crop" alt="Sony A6000" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                        <span class="font-semibold text-gray-900">Sony A6000</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">28 Sep 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">5 Okt 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Menunggu Persetujuan
                                    </span>
                                </td>
                            </tr>

                            <!-- Dummy 2: Dipinjam -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=100&auto=format&fit=crop" alt="Rode NT-USB" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                        <span class="font-semibold text-gray-900">Rode NT-USB</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">20 Sep 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">27 Sep 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Sedang Dipinjam
                                    </span>
                                </td>
                            </tr>

                            <!-- Dummy 3: Selesai -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=100&auto=format&fit=crop" alt="Canon EOS 80D" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                        <span class="font-semibold text-gray-900">Canon EOS 80D</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">1 Sep 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">10 Sep 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Selesai
                                    </span>
                                </td>
                            </tr>

                            <!-- Dummy 4: Ditolak -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://images.unsplash.com/photo-1589189068066-c316711d96fe?q=80&w=100&auto=format&fit=crop" alt="DJI Ronin-S" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                        <span class="font-semibold text-gray-900">DJI Ronin-S</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">15 Agu 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">-</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Ditolak
                                    </span>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
<footer class="bg-slate-900 text-slate-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid sm:grid-cols-3 gap-10">

            <!-- Kolom 1: Brand -->
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo SiLat" class="w-10 h-10 object-cover rounded-lg">
                    <span class="text-white font-extrabold text-lg">SiLat Multimedia</span>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Sistem Peminjaman Alat Multimedia untuk siswa, guru, dan staff sekolah.
                </p>
            </div>

            <!-- Kolom 2: Navigasi Cepat -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wide mb-4">Navigasi</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/" class="hover:text-white transition">Beranda</a></li>
                    <li><a href="#tentang" class="hover:text-white transition">Tentang</a></li>
                    <li><a href="#cara-pinjam" class="hover:text-white transition">Cara Pinjam</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Login</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Sosial Media -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wide mb-4">Terhubung Dengan Kami</h3>
                <p class="text-sm text-slate-400 mb-4">Mulmedska, SMK Negeri 5 Surakarta</p>
                <div class="flex items-center gap-3">
                    <!-- TODO: ganti href dengan link Instagram asli -->
                    <a href="#" aria-label="Instagram" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-blue-600 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2"/>
                            <circle cx="12" cy="12" r="4" stroke-width="2"/>
                            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                        </svg>
                    </a>
                    <!-- TODO: ganti href dengan link Facebook asli -->
                    <a href="#" aria-label="Facebook" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-blue-600 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 9h3V5h-3c-2.21 0-4 1.79-4 4v2H7v4h3v6h4v-6h3l1-4h-4V9c0-.55.45-1 1-1z"/>
                        </svg>
                    </a>
                    <!-- TODO: ganti href dengan link YouTube asli -->
                    <a href="#" aria-label="YouTube" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-blue-600 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21.6 7.2s-.21-1.49-.86-2.14c-.82-.86-1.74-.86-2.16-.91C15.6 4 12 4 12 4h-.01s-3.6 0-6.58.15c-.42.05-1.34.05-2.16.91C2.6 5.71 2.4 7.2 2.4 7.2S2.19 8.94 2.19 10.68v1.63c0 1.74.21 3.48.21 3.48s.21 1.49.85 2.14c.82.86 1.9.83 2.38.92 1.72.17 7.37.22 7.37.22s3.6-.01 6.59-.16c.42-.05 1.34-.05 2.16-.91.65-.65.86-2.14.86-2.14s.21-1.74.21-3.48v-1.63c0-1.74-.21-3.48-.21-3.48zM9.99 14.5v-5l5 2.51-5 2.49z"/>
                        </svg>
                    </a>
                    <!-- TODO: ganti href dengan link X/Twitter asli -->
                    <a href="#" aria-label="X" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-blue-600 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.9 3h3.3l-7.2 8.2L23.4 21h-6.6l-5.2-6.8L5.6 21H2.3l7.7-8.8L1.8 3h6.8l4.7 6.2L18.9 3zm-1.2 16.2h1.8L7.4 4.7H5.5l12.2 14.5z"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- Baris Bawah: Copyright -->
        <div class="border-t border-slate-800 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
            <p>Mulmedska SMK Negeri 5 Surakarta</p>
        </div>
    </div>
</footer>

    <!-- Script -->
    <script>
        // ===== Profil Dropdown =====
        function toggleProfileDropdown() {
            document.getElementById('profile-dropdown-menu').classList.toggle('hidden');
            document.getElementById('profile-chevron').classList.toggle('rotate-180');
        }

        document.addEventListener('click', function (event) {
            var wrapper = document.getElementById('profile-dropdown-wrapper');
            var menu = document.getElementById('profile-dropdown-menu');
            var chevron = document.getElementById('profile-chevron');
            if (!wrapper.contains(event.target)) {
                menu.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        });

        // ===== Search Dropdown =====
        // TODO: ganti array ini dengan data asli dari database kalau katalog sudah dinamis
        const daftarAlat = [
            { nama: 'Sony A6000', kategori: 'Mirrorless Camera', id: 'alat-sony-a6000', foto: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=100&auto=format&fit=crop' },
            { nama: 'DJI Mavic Mini', kategori: 'Drone', id: 'alat-dji-mavic-mini', foto: 'https://images.unsplash.com/photo-1527977966376-1c8408f9f108?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Rode NT-USB', kategori: 'Microphone', id: 'alat-rode-nt-usb', foto: 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Canon EOS 80D', kategori: 'DSLR Camera', id: 'alat-canon-eos-80d', foto: 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Godox Softbox', kategori: 'Lighting Kit', id: 'alat-godox-softbox', foto: 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Sennheiser G4', kategori: 'Wireless Mic', id: 'alat-sennheiser-g4', foto: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=100&auto=format&fit=crop' },
            { nama: 'DJI Ronin-S', kategori: 'Gimbal Stabilizer', id: 'alat-dji-ronin-s', foto: 'https://images.unsplash.com/photo-1589189068066-c316711d96fe?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Blackmagic ATEM', kategori: 'Switcher', id: 'alat-blackmagic-atem', foto: 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=100&auto=format&fit=crop' },
        ];

        const searchInput = document.getElementById('search-input');
        const searchDropdown = document.getElementById('search-dropdown');

        function renderSearchDropdown(daftar) {
            if (daftar.length === 0) {
                searchDropdown.innerHTML = '<p class="px-4 py-3 text-sm text-gray-400">Alat tidak ditemukan</p>';
                searchDropdown.classList.remove('hidden');
                return;
            }

            searchDropdown.innerHTML = daftar.map(function (alat) {
                return '<button type="button" onclick="pilihAlat(\'' + alat.id + '\')" class="w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">' +
                    '<img src="' + alat.foto + '" alt="' + alat.nama + '" class="w-10 h-10 rounded-lg object-cover shrink-0 bg-gray-100">' +
                    '<span class="flex-1 flex items-center justify-between">' +
                        '<span class="text-sm font-semibold text-gray-800">' + alat.nama + '</span>' +
                        '<span class="text-xs text-gray-400">' + alat.kategori + '</span>' +
                    '</span>' +
                    '</button>';
            }).join('');

            searchDropdown.classList.remove('hidden');
        }

        searchInput.addEventListener('focus', function () {
            renderSearchDropdown(daftarAlat);
        });

        searchInput.addEventListener('input', function () {
            const kata = searchInput.value.toLowerCase();
            const hasil = daftarAlat.filter(function (alat) {
                return alat.nama.toLowerCase().includes(kata);
            });
            renderSearchDropdown(hasil);
        });

        function pilihAlat(idAlat) {
            searchDropdown.classList.add('hidden');
            searchInput.value = '';

            const kartu = document.getElementById(idAlat);
            if (kartu) {
                kartu.scrollIntoView({ behavior: 'smooth', block: 'center' });
                kartu.classList.add('ring-2', 'ring-blue-500');
                setTimeout(function () {
                    kartu.classList.remove('ring-2', 'ring-blue-500');
                }, 1500);
            }
        }

        document.addEventListener('click', function (event) {
            var wrapper = document.getElementById('search-wrapper');
            if (!wrapper.contains(event.target)) {
                searchDropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>