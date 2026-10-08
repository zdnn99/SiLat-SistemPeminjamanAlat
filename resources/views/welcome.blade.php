<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiLat Multimedia - Sistem Peminjaman Alat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    <!-- Navbar Atas -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
            <!-- Logo & Brand -->
            <a href="/" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-18 h-18">
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
                    <input type="text" id="search-input" autocomplete="off" placeholder="Cari Alat Multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">

                    <!-- Dropdown Hasil Pencarian -->
                    <div id="search-dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 max-h-80 overflow-y-auto z-50">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Nav Links & Login -->
            <div class="flex items-center gap-4 shrink-0">
                <div class="hidden lg:flex items-center gap-1.5 text-sm font-medium text-gray-700">
                    <a href="#tentang" class="px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-blue-600 transition">Tentang</a>
                    <a href="#cara-pinjam" class="px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-blue-600 transition">Cara Pinjam</a>
                </div>

                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Login
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <div class="relative bg-slate-900 w-full h-[380px] sm:h-[460px] md:h-[520px] flex flex-col justify-center items-center text-center px-4 overflow-hidden">
        <!-- Lapis 1: Gambar banner -->
        <img src="{{ asset('images/banner.jpg') }}" alt="Koleksi Lensa dan Kamera" class="absolute inset-0 w-full h-full object-cover opacity-25">

        <!-- Lapis 2: Overlay gradient gelap, menjaga teks tetap terbaca apapun kondisi gambarnya -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/50 to-slate-900/70"></div>

        <!-- Lapis 3: Teks -->
        <div class="relative z-10 max-w-2xl">
            <!-- Judul: "SiLat" biru + cahaya biru, "Multimedia" putih + cahaya putih -->
            <h1 class="text-4xl sm:text-5xl font-bold mb-3 tracking-tight">
                <span class="text-blue-400 [text-shadow:_0_0_24px_rgb(96_165_250_/_70%)]">SiLat</span>
                <span class="text-white [text-shadow:_0_0_24px_rgb(255_255_255_/_40%)]"> Multimedia</span>
            </h1>
            <!-- Subjudul: abu-abu polos, tanpa cahaya -->
            <p class="text-slate-400 text-base sm:text-lg">Sistem Peminjaman Alat Multimedia</p>
        </div>
    </div>

    <!-- Bagian Katalog Alat -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Peralatan Tersedia</h2>
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm transition">Lihat Selengkapnya</a>
        </div>

        <!-- Grid 4 Kolom (Semua Tersedia) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- 1. Sony A6000 -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400&auto=format&fit=crop" alt="Sony A6000" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Sony A6000</h3>
                    <p class="text-gray-500 text-xs mb-4">Mirrorless Camera</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 2. DJI Mavic Mini -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1527977966376-1c8408f9f108?q=80&w=400&auto=format&fit=crop" alt="DJI Mavic Mini" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">DJI Mavic Mini</h3>
                    <p class="text-gray-500 text-xs mb-4">Drone</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 3. Rode NT-USB -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=400&auto=format&fit=crop" alt="Rode NT-USB" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Rode NT-USB</h3>
                    <p class="text-gray-500 text-xs mb-4">Microphone</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 4. Canon EOS 80D -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=400&auto=format&fit=crop" alt="Canon EOS 80D" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Canon EOS 80D</h3>
                    <p class="text-gray-500 text-xs mb-4">DSLR Camera</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 5. Godox Softbox -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=400&auto=format&fit=crop" alt="Godox Softbox" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Godox Softbox</h3>
                    <p class="text-gray-500 text-xs mb-4">Lighting Kit</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 6. Sennheiser G4 -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=400&auto=format&fit=crop" alt="Sennheiser G4" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Sennheiser G4</h3>
                    <p class="text-gray-500 text-xs mb-4">Wireless Mic</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 7. DJI Ronin-S -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1589189068066-c316711d96fe?q=80&w=400&auto=format&fit=crop" alt="DJI Ronin-S" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">DJI Ronin-S</h3>
                    <p class="text-gray-500 text-xs mb-4">Gimbal Stabilizer</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

            <!-- 8. Blackmagic ATEM -->
            <div class="bg-white p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
                <div class="bg-gray-100/80 rounded-xl h-44 relative flex justify-center items-center overflow-hidden">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=400&auto=format&fit=crop" alt="Blackmagic ATEM" class="object-cover h-full w-full relative z-0">
                </div>
                <div class="mt-4 flex flex-col flex-grow">
                    <h3 class="text-gray-900 font-bold text-base">Blackmagic ATEM</h3>
                    <p class="text-gray-500 text-xs mb-4">Switcher</p>
                    <a href="{{ route('login') }}" class="mt-auto w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition shadow-sm">
                        Pinjam Alat
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- Section: Tentang -->
    <section id="tentang" class="scroll-mt-20 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">

            <!-- Tentang SiLat -->
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide">Tentang</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 mb-4">Apa itu SiLat?</h2>
                    <p class="text-gray-600 leading-relaxed">
                        SiLat (Sistem Peminjaman Alat) adalah platform website digital yang digunakan untuk mengajukan peminjaman alat atau inventaris yang dimiliki oleh Ekstrakurikuler Multimedia SMK Negeri 5 Surakarta. Siswa, Guru, dan Staff dapat mengajukan peminjaman kapan saja. Baik untuk keperluan pembelajaran, dokumentasi, maupun kegiatan lainnya. Dengan SiLat, proses peminjaman menjadi lebih mudah, cepat, dan transparan.
                    </p>
                </div>
                <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-200/80">
                    <img src="{{ asset('images/silat.jpg') }}" alt="Tentang SiLat" class="w-full h-64 sm:h-80 object-cover">
                </div>
            </div>

            <!-- Tentang Ekskul Multimedia -->
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-200/80 order-2 md:order-1">
                    <img src="{{ asset('images/ekskul.jpg') }}" alt="Ekskul Multimedia" class="w-full h-64 sm:h-80 object-cover">
                </div>
                <div class="order-1 md:order-2">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide">Tentang</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 mb-4">Ekskul Multimedia</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600 leading-relaxed">Ekstrakurikuler Multimedia merupakan kegiatan tambahan yang diselenggarakan SMK Negeri 5 Surakarta untuk mengembangkan bakat dan minat siswa terutama dalam fotografi dan videografi.</p>
                        <p class="text-gray-600 leading-relaxed">Ekstrakurikuler Ini memiliki banyak kegiatan dan materi yang diberikan kepada para peserta, mulai dari cara mengoperasikan camera, penggunaan aplikasi editing, mendesain poster digital, hingga membuat video pendek. Selain itu, peserta diharapkan dapat mengasah kemampuan dan keterampilannya di bidang teknologi mengikuti kemajuan dan pekembangan zaman.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Section: Cara Pinjam -->
    <section id="cara-pinjam" class="scroll-mt-20 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide">Panduan</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">Cara Pinjam Alat</h2>
                <p class="text-gray-600 mt-3">Empat langkah mudah untuk mulai meminjam alat multimedia.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white font-bold rounded-xl mb-4">1</div>
                    <h3 class="font-bold text-gray-900 mb-2">Login / Daftar Akun</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Masuk dengan akun siswa, guru, atau staff. Belum punya akun? Daftar dulu lewat halaman registrasi.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white font-bold rounded-xl mb-4">2</div>
                    <h3 class="font-bold text-gray-900 mb-2">Pilih Alat & Isi Form</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Pilih alat yang ingin dipinjam, lalu isi tanggal pinjam dan kembali (maksimal 2 minggu). Satu form untuk satu alat.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white font-bold rounded-xl mb-4">3</div>
                    <h3 class="font-bold text-gray-900 mb-2">Tunggu Persetujuan Admin</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Admin akan memverifikasi pengajuanmu. Setelah disetujui, kamu bisa mengambil alat sesuai jadwal.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white font-bold rounded-xl mb-4">4</div>
                    <h3 class="font-bold text-gray-900 mb-2">Kembalikan Tepat Waktu</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Kembalikan alat sesuai jadwal. Kondisi alat akan dicatat oleh admin saat proses pengembalian.</p>
                </div>

            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold px-8 py-3.5 rounded-xl transition shadow-sm">
                    Pinjam Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Kolom 1: Lokasi (rata kiri) -->
                <div>
                    <h3 class="text-gray-900 font-semibold text-sm uppercase tracking-wide mb-4">Lokasi</h3>
                    <!-- TODO: ganti div ini dengan <iframe> embed Google Maps asli sekolah -->
                    <div class="w-full h-32 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <!-- TODO: ganti dengan alamat lengkap SMK Negeri 5 Surakarta -->
                    <p class="text-sm text-gray-500 leading-relaxed">SMK Negeri 5 Surakarta</p>
                </div>

                <!-- Kolom 2: Ikuti Mulmed (rata tengah) -->
                <div class="text-center">
                    <h3 class="text-gray-900 font-semibold text-sm uppercase tracking-wide mb-4">Ikuti Mulmed</h3>
                    <!-- TODO: ganti href dengan link Instagram Mulmed asli -->
                    <a href="#" class="inline-flex items-center gap-2.5 group">
                        <span class="w-10 h-10 rounded-full bg-blue-50 group-hover:bg-blue-600 flex items-center justify-center transition">
                            <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2"/>
                                <circle cx="12" cy="12" r="4" stroke-width="2"/>
                                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                            </svg>
                        </span>
                        <span class="text-sm text-gray-600 group-hover:text-blue-600 transition">@mulmed.sman5ska</span>
                    </a>
                </div>

                <!-- Kolom 3: Hubungi Kami (rata tengah, email & telepon bisa diklik) -->
                <div class="text-center">
                    <h3 class="text-gray-900 font-semibold text-sm uppercase tracking-wide mb-4">Hubungi Kami</h3>
                    <div class="flex flex-col items-center gap-3">
                        <!-- TODO: ganti dengan email Mulmed asli -->
                        <a href="mailto:mulmed@smkn5surakarta.sch.id" class="inline-flex items-center gap-2.5 group">
                            <span class="w-9 h-9 rounded-full bg-blue-50 group-hover:bg-blue-600 flex items-center justify-center transition shrink-0">
                                <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span class="text-sm text-gray-600 group-hover:text-blue-600 transition">mulmed@smkn5surakarta.sch.id</span>
                        </a>
                        <!-- TODO: ganti dengan nomor telepon sekolah asli -->
                        <a href="tel:0271000000" class="inline-flex items-center gap-2.5 group">
                            <span class="w-9 h-9 rounded-full bg-blue-50 group-hover:bg-blue-600 flex items-center justify-center transition shrink-0">
                                <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </span>
                            <span class="text-sm text-gray-600 group-hover:text-blue-600 transition">(0271) 000000</span>
                        </a>
                    </div>
                </div>

                <!-- Kolom 4: Tautan Sekolah (rata tengah) -->
                <div class="text-center">
                    <h3 class="text-gray-900 font-semibold text-sm uppercase tracking-wide mb-4">Tautan Sekolah</h3>
                    <div class="flex items-center justify-center gap-2.5 flex-wrap">
                        <!-- TODO: ganti href dengan website sekolah asli -->
                        <a href="#" aria-label="Website Sekolah" class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-600 flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                <path stroke-linecap="round" stroke-width="2" d="M3 12h18M12 3c2.5 2.5 3.5 6 3.5 9s-1 6.5-3.5 9c-2.5-2.5-3.5-6-3.5-9s1-6.5 3.5-9z"/>
                            </svg>
                        </a>
                        <!-- TODO: ganti href dengan YouTube sekolah asli -->
                        <a href="#" aria-label="YouTube Sekolah" class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-600 flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21.6 7.2s-.21-1.49-.86-2.14c-.82-.86-1.74-.86-2.16-.91C15.6 4 12 4 12 4h-.01s-3.6 0-6.58.15c-.42.05-1.34.05-2.16.91C2.6 5.71 2.4 7.2 2.4 7.2S2.19 8.94 2.19 10.68v1.63c0 1.74.21 3.48.21 3.48s.21 1.49.85 2.14c.82.86 1.9.83 2.38.92 1.72.17 7.37.22 7.37.22s3.6-.01 6.59-.16c.42-.05 1.34-.05 2.16-.91.65-.65.86-2.14.86-2.14s.21-1.74.21-3.48v-1.63c0-1.74-.21-3.48-.21-3.48zM9.99 14.5v-5l5 2.51-5 2.49z"/>
                            </svg>
                        </a>
                        <!-- TODO: ganti href dengan Facebook sekolah asli -->
                        <a href="#" aria-label="Facebook Sekolah" class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-600 flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 9h3V5h-3c-2.21 0-4 1.79-4 4v2H7v4h3v6h4v-6h3l1-4h-4V9c0-.55.45-1 1-1z"/>
                            </svg>
                        </a>
                        <!-- TODO: ganti href dengan Instagram sekolah asli -->
                        <a href="#" aria-label="Instagram Sekolah" class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-600 flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2"/>
                                <circle cx="12" cy="12" r="4" stroke-width="2"/>
                                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Baris Bawah: Copyright -->
            <div class="border-t border-gray-200 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
                <p>Mulmedska SMK Negeri 5 Surakarta</p>
            </div>
        </div>
    </footer>

    <!-- Script -->
    <script>
        // TODO: ganti array ini dengan data asli dari database kalau katalog sudah dinamis
        const daftarAlat = [
            { nama: 'Sony A6000', kategori: 'Mirrorless Camera', foto: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=100&auto=format&fit=crop' },
            { nama: 'DJI Mavic Mini', kategori: 'Drone', foto: 'https://images.unsplash.com/photo-1527977966376-1c8408f9f108?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Rode NT-USB', kategori: 'Microphone', foto: 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Canon EOS 80D', kategori: 'DSLR Camera', foto: 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Godox Softbox', kategori: 'Lighting Kit', foto: 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Sennheiser G4', kategori: 'Wireless Mic', foto: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=100&auto=format&fit=crop' },
            { nama: 'DJI Ronin-S', kategori: 'Gimbal Stabilizer', foto: 'https://images.unsplash.com/photo-1589189068066-c316711d96fe?q=80&w=100&auto=format&fit=crop' },
            { nama: 'Blackmagic ATEM', kategori: 'Switcher', foto: 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=100&auto=format&fit=crop' },
        ];

        const loginUrl = "{{ route('login') }}";
        const searchInput = document.getElementById('search-input');
        const searchDropdown = document.getElementById('search-dropdown');

        // Membungkus bagian nama yang cocok dengan ketikan ke dalam <span> biru
        function highlightMatch(nama, kata) {
            if (!kata) return nama;
            const index = nama.toLowerCase().indexOf(kata.toLowerCase());
            if (index === -1) return nama;

            const sebelum = nama.slice(0, index);
            const cocok = nama.slice(index, index + kata.length);
            const sesudah = nama.slice(index + kata.length);

            return sebelum + '<span class="text-blue-600 font-bold">' + cocok + '</span>' + sesudah;
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
                return '<button type="button" onclick="pilihAlat()" class="w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">' +
                    '<img src="' + alat.foto + '" alt="' + alat.nama + '" class="w-10 h-10 rounded-lg object-cover shrink-0 bg-gray-100">' +
                    '<span class="flex-1 flex items-center justify-between">' +
                        '<span class="text-sm font-semibold text-gray-800">' + highlightMatch(alat.nama, kata) + '</span>' +
                        '<span class="text-xs text-gray-400">' + alat.kategori + '</span>' +
                    '</span>' +
                    '</button>';
            }).join('');

            searchDropdown.classList.remove('hidden');
        }

        searchInput.addEventListener('focus', function () {
            renderSearchDropdown(daftarAlat, searchInput.value);
        });

        searchInput.addEventListener('input', function () {
            const kata = searchInput.value;
            const hasil = daftarAlat.filter(function (alat) {
                return alat.nama.toLowerCase().includes(kata.toLowerCase());
            });
            renderSearchDropdown(hasil, kata);
        });

        // Klik alat di landing page -> arahkan ke login (bukan form, karena belum login)
        function pilihAlat() {
            window.location.href = loginUrl;
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