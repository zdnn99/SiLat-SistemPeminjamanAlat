<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SiLat Multimedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    @php
        $namaLengkap = Auth::user()->nama_lengkap ?? 'Admin';
        $namaSingkat = implode(' ', array_slice(explode(' ', $namaLengkap), 0, 2));
    @endphp

    <!-- Navbar Atas (identik dengan dashboard user) -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
            <!-- Logo & Brand -->
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center gap-3 shrink-0">
                <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=100&h=100" alt="Logo SiLat" class="w-12 h-12 object-cover rounded-xl shadow-sm">
                <div>
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-xl hidden md:block">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 z-10">
                        <img src="https://img.icons8.com/ios-glyphs/30/4b5563/search--v1.png" alt="Search" class="w-4 h-4">
                    </span>
                    <input type="text" placeholder="Cari alat multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
            </div>

            <!-- Profil Dropdown -->
            <div class="relative shrink-0" id="profile-dropdown-wrapper">
                <button type="button" onclick="toggleProfileDropdown()" id="profile-dropdown-btn"
                    class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition">
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
                            Admin
                        </span>
                    </div>

                    <!-- TODO: buat route & controller untuk halaman Profil Saya -->
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-blue-600 hover:bg-blue-50 transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>

                    <!-- TODO: buat route & controller untuk halaman Kelola Alat -->
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-purple-600 hover:bg-purple-50 transition">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kelola Alat
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

    <!-- Hero Section (identik dengan dashboard user) -->
    <div class="relative bg-slate-900 w-full h-[380px] sm:h-[460px] md:h-[520px] flex flex-col justify-center items-center text-center px-4 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=2000&auto=format&fit=crop" alt="Studio Banner" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
        
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-3 tracking-tight">SiLat Multimedia</h1>
            <p class="text-slate-300 text-base sm:text-lg">Sistem Peminjaman Alat Multimedia</p>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow">

        <!-- ===== Bagian Permintaan Peminjaman (posisi paling atas, khusus admin) ===== -->
        <section class="mb-16">
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Permintaan Peminjaman</h2>
                <p class="text-gray-500 text-sm mt-1">Daftar pengajuan yang menunggu persetujuanmu</p>
            </div>

            <!-- TODO: hitung dari query asli, bukan hardcode -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">3</p>
                        <p class="text-xs text-gray-500">Menunggu Persetujuan</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">5</p>
                        <p class="text-xs text-gray-500">Sedang Dipinjam</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">12</p>
                        <p class="text-xs text-gray-500">Selesai Bulan Ini</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900 leading-tight">8</p>
                        <p class="text-xs text-gray-500">Total Jenis Alat</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500 text-xs uppercase tracking-wide">
                                <th class="px-6 py-3.5 font-semibold">Peminjam</th>
                                <th class="px-6 py-3.5 font-semibold">Alat</th>
                                <th class="px-6 py-3.5 font-semibold">Tanggal Pinjam</th>
                                <th class="px-6 py-3.5 font-semibold">Tanggal Kembali</th>
                                <th class="px-6 py-3.5 font-semibold">Keperluan</th>
                                <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <!-- TODO: ganti isi tbody ini dengan loop data asli dari database (status menunggu_persetujuan) -->
                        <tbody class="divide-y divide-gray-100">

                            <!-- Dummy 1 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-red-800 text-white flex items-center justify-center font-bold text-xs shrink-0">A</div>
                                        <div>
                                            <p class="font-semibold text-gray-900 leading-tight">Ahmad Budi</p>
                                            <p class="text-xs text-gray-400">XI PPLG A</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800 whitespace-nowrap">Sony A6000</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">1 Okt 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">8 Okt 2026</td>
                                <td class="px-6 py-4 text-gray-500 max-w-[200px] truncate">Dokumentasi classmeeting</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">Setujui</button>
                                        <button type="button" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold px-3 py-1.5 rounded-lg transition">Tolak</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Dummy 2 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-red-800 text-white flex items-center justify-center font-bold text-xs shrink-0">S</div>
                                        <div>
                                            <p class="font-semibold text-gray-900 leading-tight">Siti Nurhaliza</p>
                                            <p class="text-xs text-gray-400">X DPIB B</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800 whitespace-nowrap">Rode NT-USB</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">2 Okt 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">5 Okt 2026</td>
                                <td class="px-6 py-4 text-gray-500 max-w-[200px] truncate">Podcast tugas sekolah</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">Setujui</button>
                                        <button type="button" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold px-3 py-1.5 rounded-lg transition">Tolak</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Dummy 3 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-red-800 text-white flex items-center justify-center font-bold text-xs shrink-0">P</div>
                                        <div>
                                            <p class="font-semibold text-gray-900 leading-tight">Pak Hendra</p>
                                            <p class="text-xs text-gray-400">Guru Multimedia</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800 whitespace-nowrap">DJI Ronin-S</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">3 Okt 2026</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">10 Okt 2026</td>
                                <td class="px-6 py-4 text-gray-500 max-w-[200px] truncate">Produksi video sekolah</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">Setujui</button>
                                        <button type="button" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold px-3 py-1.5 rounded-lg transition">Tolak</button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ===== Bagian Peralatan Tersedia (digeser ke bawah) ===== -->
        <section>
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Peralatan Tersedia</h2>
            </div>

            <!-- Grid 4 Kolom -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- 1. Sony A6000 (Tersedia) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                        <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400&auto=format&fit=crop" alt="Sony A6000" class="object-cover h-full w-full relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">Sony A6000</h3>
                        <p class="text-gray-500 text-xs mb-4">Mirrorless Camera</p>
                        <!-- TODO: arahkan ke halaman edit alat (Kelola Alat) -->
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 2. DJI Mavic Mini (Dipinjam) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Dipinjam</span>
                        <img src="https://images.unsplash.com/photo-1527977966376-1c8408f9f108?q=80&w=400&auto=format&fit=crop" alt="DJI Mavic Mini" class="object-cover h-full w-full grayscale relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">DJI Mavic Mini</h3>
                        <p class="text-gray-500 text-xs mb-4">Drone</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 3. Rode NT-USB (Tersedia) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                        <img src="https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=400&auto=format&fit=crop" alt="Rode NT-USB" class="object-cover h-full w-full relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">Rode NT-USB</h3>
                        <p class="text-gray-500 text-xs mb-4">Microphone</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 4. Canon EOS 80D (Tersedia) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                        <img src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=400&auto=format&fit=crop" alt="Canon EOS 80D" class="object-cover h-full w-full relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">Canon EOS 80D</h3>
                        <p class="text-gray-500 text-xs mb-4">DSLR Camera</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 5. Godox Softbox (Dipinjam) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Dipinjam</span>
                        <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=400&auto=format&fit=crop" alt="Godox Softbox" class="object-cover h-full w-full grayscale relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">Godox Softbox</h3>
                        <p class="text-gray-500 text-xs mb-4">Lighting Kit</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 6. Sennheiser G4 (Tersedia) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                        <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=400&auto=format&fit=crop" alt="Sennheiser G4" class="object-cover h-full w-full relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">Sennheiser G4</h3>
                        <p class="text-gray-500 text-xs mb-4">Wireless Mic</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 7. DJI Ronin-S (Tersedia) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                        <img src="https://images.unsplash.com/photo-1589189068066-c316711d96fe?q=80&w=400&auto=format&fit=crop" alt="DJI Ronin-S" class="object-cover h-full w-full relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">DJI Ronin-S</h3>
                        <p class="text-gray-500 text-xs mb-4">Gimbal Stabilizer</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

                <!-- 8. Blackmagic ATEM (Dipinjam) -->
                <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                    <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Dipinjam</span>
                        <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=400&auto=format&fit=crop" alt="Blackmagic ATEM" class="object-cover h-full w-full grayscale relative z-0">
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <h3 class="text-gray-900 font-bold text-base">Blackmagic ATEM</h3>
                        <p class="text-gray-500 text-xs mb-4">Switcher</p>
                        <a href="#" class="mt-auto w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl text-center transition">
                            Kelola
                        </a>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <!-- Script -->
    <script>
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
    </script>
</body>
</html>