<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman - SiLat Multimedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    @php
        $namaLengkap = Auth::user()->nama_lengkap ?? 'Pengguna';
        $namaSingkat = implode(' ', array_slice(explode(' ', $namaLengkap), 0, 2));
        $tanggalHariIni = now()->translatedFormat('d F Y');
        $minKembali = now()->format('Y-m-d');
        $maxKembali = now()->addDays(14)->format('Y-m-d');
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
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500">
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

                <div id="profile-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $namaLengkap }}</p>
                        <span class="inline-block mt-1.5 bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">
                            {{ Auth::user()->nomor_induk ?? '-' }}
                        </span>
                    </div>

                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-blue-600 hover:bg-blue-50 transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>

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

    <!-- Konten Form Peminjaman -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">

        <!-- Breadcrumb -->
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-blue-600 transition mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Dashboard
        </a>

        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Form Peminjaman Alat</h1>
            <p class="text-gray-500 text-sm mt-1.5">Lengkapi data di bawah ini untuk mengajukan peminjaman. Pengajuan akan diverifikasi oleh admin.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid lg:grid-cols-5 gap-8 items-start">

            <!-- Kartu Ringkasan Alat -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 lg:sticky lg:top-28">
                <!-- TODO: ganti dengan data $alat dari database -->
                <div class="bg-gray-100/80 rounded-xl h-48 relative flex justify-center items-center overflow-hidden mb-4">
                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10">Tersedia</span>
                    <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=600&auto=format&fit=crop" alt="Sony A6000" class="object-cover h-full w-full relative z-0">
                </div>

                <h2 class="text-lg font-bold text-gray-900">Sony A6000</h2>
                <p class="text-gray-500 text-sm mb-4">Mirrorless Camera</p>

                <div class="border-t border-gray-100 pt-4 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Kategori</span>
                        <span class="font-semibold text-gray-800">Camera</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-semibold text-green-600">Tersedia</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Pengelola</span>
                        <span class="font-semibold text-gray-800">Ekskul Multimedia</span>
                    </div>
                </div>
            </div>

            <!-- Form Pengajuan -->
            <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 sm:p-8">

                <!-- Info Kebijakan -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3.5 mb-6 flex gap-3">
                    <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-800">Peminjaman maksimal <span class="font-bold">14 hari</span> sejak hari ini, dan akan diverifikasi oleh admin sebelum alat bisa diambil.</p>
                </div>

                <!-- Data Peminjam (ringkas, non-editable) -->
                <div class="mb-6 pb-6 border-b border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Data Peminjam</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($namaLengkap, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $namaLengkap }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->nomor_induk ?? '-' }} &middot; {{ ucfirst(str_replace('_', ' ', Auth::user()->peran ?? '-')) }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('peminjaman.store') }}" class="space-y-5">
                    @csrf

                    <!-- TODO: ganti dengan ID alat asli dari database -->
                    <input type="hidden" name="alat_id" value="1">

                    <!-- Tanggal Pinjam & Kembali -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pinjam</label>
                            <div class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-600">
                                {{ $tanggalHariIni }}
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Otomatis mulai hari ini</p>
                        </div>

                        <div>
                            <label for="tanggal_kembali" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kembali</label>
                            <input id="tanggal_kembali" type="date" name="tanggal_kembali" required
                                min="{{ $minKembali }}" max="{{ $maxKembali }}" value="{{ old('tanggal_kembali') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('tanggal_kembali') border-red-500 @enderror">
                            @error('tanggal_kembali')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Keperluan (opsional) -->
                    <div>
                        <label for="keperluan" class="block text-sm font-medium text-gray-700 mb-1">
                            Keperluan Peminjaman <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea id="keperluan" name="keperluan" rows="3" placeholder="Contoh: Dokumentasi acara classmeeting kelas XI"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm resize-none">{{ old('keperluan') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Membantu admin memahami konteks pengajuanmu</p>
                    </div>

                    <!-- Checkbox Persetujuan -->
                    <div class="flex items-start gap-2.5 bg-gray-50 rounded-xl px-4 py-3.5">
                        <input id="persetujuan" type="checkbox" name="persetujuan" required
                            class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4 shrink-0">
                        <label for="persetujuan" class="text-xs text-gray-600 leading-relaxed">
                            Saya akan menjaga alat yang dipinjam dengan baik dan mengembalikannya tepat waktu sesuai tanggal yang diajukan.
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-lg shadow-blue-600/30 text-sm">
                        Ajukan Peminjaman
                    </button>
                </form>
            </div>

        </div>
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