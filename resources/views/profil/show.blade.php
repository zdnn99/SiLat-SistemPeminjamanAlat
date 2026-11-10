<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Hover kolom isian dan label di dalam kartu (termasuk komponen field-sandi dan kelas-picker).
           Hanya untuk perangkat dengan kursor: di layar sentuh :hover "menempel" setelah diketuk. */
        .kartu-form form label { transition: color .2s; }

        @media (hover: hover) {
            .kartu-form form input:not([type="hidden"]):not([type="file"]):not([readonly]):not(:disabled):not(:focus):not(.border-red-500):hover {
                border-color: #93c5fd;
            }
            .kartu-form form label:hover { color: #2563eb; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    @php
        $isAdmin = $user->peran === 'admin';
        $isSiswa = $user->peran === 'siswa';
        $urlBeranda = $isAdmin ? url('/admin/dashboard') : url('/dashboard');
        $fotoUrl = $user->foto_profil ? asset('uploads/profil/' . $user->foto_profil) : null;
        $inisial = strtoupper(mb_substr($user->nama_lengkap ?? 'U', 0, 1));

        // Nama selalu ditampilkan huruf besar semua, apa pun isi di database.
        // $namaAwal = nama yang sudah dirapikan (huruf besar, spasi tunggal): pembanding JavaScript untuk mendeteksi perubahan.
        // Dibandingkan dengan nama mentah dari database, tombol Simpan akan menyala sejak halaman dibuka bila di database tertulis huruf campuran.
        $namaTampil = mb_strtoupper($user->nama_lengkap ?? '', 'UTF-8');
        $namaAwal = trim(preg_replace('/\s+/', ' ', $namaTampil));

        $labelPeran = ['siswa' => 'Siswa', 'guru_karyawan' => 'Guru / Karyawan', 'admin' => 'Admin'][$user->peran] ?? ucfirst($user->peran);

        // "Bergabung sejak" hanya untuk siswa dan guru. Akun admin sudah ada sebelum sistem berjalan, jadi tidak dicantumkan.
        // Waktu dikonversi ke WIB dulu supaya bulannya tidak meleset bila zona waktu aplikasi UTC.
        $bergabung = (! $isAdmin && $user->created_at)
            ? $user->created_at->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('F Y')
            : null;

        $labelNomorInduk = $isSiswa ? 'Nomor Induk Siswa (NIS)' : ($isAdmin ? 'Nomor Induk' : 'Nomor Induk Pegawai (NIP)');
        $singkatanInduk = $isSiswa ? 'NIS' : ($isAdmin ? 'No. Induk' : 'NIP');

        // Bidang yang dikunci (tidak bisa diubah pengguna)
        $dataTerkunci = [
            ['label' => $labelNomorInduk, 'nilai' => $user->nomor_induk],
            ['label' => 'Peran',          'nilai' => $labelPeran],
        ];

        // Menu navigasi ke bagian halaman (tampil di navbar layar xl ke atas)
        $menuBagian = [
            ['id' => 'data-diri',     'label' => 'Data Diri'],
            ['id' => 'kata-sandi',    'label' => 'Kata Sandi'],
            ['id' => 'pin-pemulihan', 'label' => 'PIN Pemulihan'],
        ];

        // Satu pesan sukses (dari bagian mana pun) ditampilkan sebagai toast
        $pesanSukses = session('sukses_foto') ?? session('sukses_info') ?? session('sukses_sandi') ?? session('sukses_pin') ?? session('sukses_hapus');

        // Hapus Akun: backend dikerjakan belakangan, jadi alamat form dibuat aman
        $hapusSiap = \Illuminate\Support\Facades\Route::has('profil.hapus.ajukan');
        $hapusBatalSiap = \Illuminate\Support\Facades\Route::has('profil.hapus.batal');
        $urlHapusAjukan = $hapusSiap ? route('profil.hapus.ajukan') : '#';
        $urlHapusBatal = $hapusBatalSiap ? route('profil.hapus.batal') : '#';

        $waktuMintaMentah = $user->getAttributes()['permintaan_hapus_pada'] ?? null;
        $waktuMinta = $waktuMintaMentah ? \Carbon\Carbon::parse($waktuMintaMentah) : null;
        // PRATINJAU sementara: buka /profil?pratinjau=hapus untuk melihat tampilan "menunggu admin" (hapus saat backend jadi)
        if (! $waktuMinta && request('pratinjau') === 'hapus') {
            $waktuMinta = now();
        }
        $teksWaktuMinta = $waktuMinta ? $waktuMinta->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y') : null;
    @endphp

    <!-- Navbar Atas.
         Layar di bawah xl (HP dan tablet): hanya logo + nama di kiri dan tombol "Dashboard" (teks saja) di kanan.
         Tidak ada tombol hamburger, tidak ada profil dropdown, dan subjudul disembunyikan di HP supaya tidak sesak.
         Layar xl ke atas: menu bagian halaman (Data Diri, Kata Sandi, PIN Pemulihan) tepat di tengah, profil dropdown,
         dan tombol "Kembali ke Dashboard". -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Logo & Brand -->
            <a href="{{ $urlBeranda }}" class="group flex min-w-0 items-center gap-2 sm:gap-3 xl:shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-14 h-14 shrink-0 sm:w-18 sm:h-18 transition-transform duration-300 motion-safe:group-hover:scale-105">
                <div class="min-w-0">
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="hidden sm:block truncate text-gray-700 font-semibold text-xs">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Menu bagian halaman (layar xl ke atas), tepat di tengah antara logo dan profil -->
            <nav class="flex-1 min-w-0 hidden xl:flex items-center justify-center gap-1 text-sm font-medium text-gray-700" aria-label="Bagian halaman profil">
                @foreach ($menuBagian as $m)
                    <a href="#{{ $m['id'] }}" class="whitespace-nowrap px-3.5 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 motion-safe:hover:-translate-y-0.5 active:scale-95">{{ $m['label'] }}</a>
                @endforeach
            </nav>

            <!-- Profil dropdown (hanya layar xl ke atas) + tombol Dashboard -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                <div class="hidden xl:block">
                    @include('partials.profil-dropdown')
                </div>

                <a href="{{ $urlBeranda }}"
                   class="inline-flex items-center justify-center whitespace-nowrap px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200 hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-95">
                    <span class="xl:hidden">Dashboard</span>
                    <span class="hidden xl:inline">Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Toast sukses -->
    @if($pesanSukses)
        <div id="toast-sukses" role="status" aria-live="polite"
            class="fixed top-24 left-1/2 -translate-x-1/2 z-[60] w-[calc(100%-2rem)] max-w-md flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold shadow-lg transition-opacity duration-500">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="min-w-0 break-words">{{ $pesanSukses }}</span>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-grow w-full space-y-6">

        <!-- ===== Banner Profil (foto + identitas) ===== -->
        <section class="relative overflow-hidden bg-[#0B1F4D] rounded-2xl shadow-sm px-5 sm:px-8 py-7 text-white transition-all duration-300 hover:shadow-2xl hover:shadow-blue-900/30">
            <span class="pointer-events-none absolute -right-12 -top-16 w-56 h-56 rounded-full bg-white/10"></span>
            <span class="pointer-events-none absolute right-28 -bottom-24 w-48 h-48 rounded-full bg-white/5"></span>

            <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">

                <!-- Foto profil -->
                <div class="flex flex-col items-center gap-2 shrink-0 self-center">
                    <form id="form-foto" method="POST" action="{{ route('profil.foto') }}" enctype="multipart/form-data" class="flex flex-col items-center">
                        @csrf
                        <div class="relative transition-transform duration-300 motion-safe:hover:scale-105">
                            @if($fotoUrl)
                                <img id="avatar-img" src="{{ $fotoUrl }}" alt="Foto profil" class="w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover ring-4 ring-white shadow-lg shadow-black/40">
                            @else
                                <div id="avatar-fallback" class="w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-blue-600 text-white flex items-center justify-center text-5xl font-bold ring-4 ring-white shadow-lg shadow-black/40">
                                    {{ $inisial }}
                                </div>
                                <img id="avatar-img" alt="Foto profil" class="hidden w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover ring-4 ring-white shadow-lg shadow-black/40">
                            @endif

                            <label for="foto" title="Ganti foto"
                                class="absolute bottom-0 right-0 w-10 h-10 rounded-full bg-white hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center cursor-pointer shadow-md hover:shadow-lg border-2 border-blue-600 transition-all duration-200 motion-safe:hover:scale-110 active:scale-95">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </label>
                        </div>

                        <input id="foto" name="foto" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">

                        <div id="foto-aksi" class="hidden items-center gap-2 mt-3">
                            <button type="submit" class="bg-white hover:bg-blue-50 text-blue-700 text-xs font-semibold px-3.5 py-2 rounded-lg transition-all duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">Simpan Foto</button>
                            <button type="button" id="foto-batal" class="bg-white/15 hover:bg-white/25 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition-all duration-200 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">Batal</button>
                        </div>

                        <p id="foto-error" class="hidden bg-white text-red-600 text-xs text-center max-w-[14rem] rounded-lg px-2.5 py-1.5 mt-3"></p>
                        @error('foto')
                            <p class="bg-white text-red-600 text-xs text-center max-w-[14rem] rounded-lg px-2.5 py-1.5 mt-3">{{ $message }}</p>
                        @enderror
                    </form>

                    @if($fotoUrl)
                        <form method="POST" action="{{ route('profil.foto.hapus') }}" onsubmit="return confirm('Hapus foto profil dan kembali ke inisial nama?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-blue-100 hover:text-white hover:underline transition-colors duration-200">Hapus Foto</button>
                        </form>
                    @endif
                </div>

                <!-- Identitas -->
                <div class="min-w-0 text-center sm:text-left">
                    <h1 class="text-xs font-semibold uppercase tracking-widest text-blue-100 transition-colors duration-200 hover:text-white">Profil Saya</h1>
                    <p class="text-2xl sm:text-3xl font-bold leading-tight break-words mt-0.5 transition-colors duration-300 hover:text-blue-200">{{ $namaTampil }}</p>

                    <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2">
                        <span class="bg-white/15 ring-1 ring-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full transition-all duration-200 hover:bg-white hover:text-blue-700 hover:ring-white motion-safe:hover:-translate-y-0.5">{{ $labelPeran }}</span>
                        <span class="bg-white/15 ring-1 ring-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full transition-all duration-200 hover:bg-white hover:text-blue-700 hover:ring-white motion-safe:hover:-translate-y-0.5">{{ $singkatanInduk }} {{ $user->nomor_induk }}</span>
                        @if($isSiswa && $user->kelas_jurusan)
                            <span class="bg-white/15 ring-1 ring-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full transition-all duration-200 hover:bg-white hover:text-blue-700 hover:ring-white motion-safe:hover:-translate-y-0.5">{{ $user->kelas_jurusan }}</span>
                        @endif
                    </div>

                    @if($bergabung)
                        <p class="text-xs text-blue-100 mt-3 transition-colors duration-200 hover:text-white">Bergabung sejak {{ $bergabung }}</p>
                    @endif
                </div>

                <p class="hidden lg:block ml-auto max-w-xs text-sm text-blue-100 text-right transition-colors duration-200 hover:text-white">Kelola foto, data diri, dan keamanan akun kamu.</p>
            </div>
        </section>

        <!-- ===== Grid 50:50 ===== -->
        <div class="kartu-form grid lg:grid-cols-2 gap-6">

            <!-- ===== Data Diri (admin: selebar penuh, tanpa PIN) ===== -->
            <section id="data-diri" class="min-w-0 scroll-mt-24 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-7 flex flex-col transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15 {{ $isAdmin ? 'lg:col-span-2' : '' }}">
                <div class="flex items-start gap-3.5 mb-5">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 leading-tight transition-colors duration-300 hover:text-blue-700">Data Diri</h2>
                        <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-200 hover:text-gray-700">{{ $isAdmin ? 'Ubah nama kamu.' : 'Ubah nama' . ($isSiswa ? ' atau kelas' : '') . ' kamu. Perubahan disimpan setelah PIN Pemulihan dimasukkan.' }}</p>
                    </div>
                </div>

                <form id="form-data" method="POST" action="{{ route('profil.data') }}" data-kelas-awal="{{ $user->kelas_jurusan }}" class="flex-1 flex flex-col gap-4" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="grid sm:grid-cols-2 {{ $isAdmin ? 'lg:grid-cols-4' : '' }} gap-4">

                        <!-- 1. Nama Lengkap (bisa diedit, selalu huruf besar) -->
                        <div class="sm:col-span-2">
                            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input id="nama_lengkap" name="nama_lengkap" type="text" required maxlength="255"
                                value="{{ old('nama_lengkap', $namaTampil) }}" data-awal="{{ $namaAwal }}"
                                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z\s]/g, '')"
                                placeholder="Masukkan Nama Lengkap Anda"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_lengkap') ? 'border-red-500' : 'border-gray-300' }} focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm uppercase placeholder:normal-case">
                            @error('nama_lengkap')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 2. Kelas & Jurusan (bisa diedit, khusus siswa) -->
                        @if($isSiswa)
                            <div class="sm:col-span-2">
                                @include('partials.kelas-picker', [
                                    'kelasNilai' => old('kelas_jurusan', $user->kelas_jurusan),
                                    'kelasAwal' => $user->kelas_jurusan,
                                ])
                            </div>
                        @endif

                        <!-- 3 & 4. Nomor Induk dan Peran (terkunci) -->
                        @foreach($dataTerkunci as $d)
                            <div class="min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $d['label'] }}</label>
                                <div class="relative">
                                    <div aria-disabled="true" class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-500 cursor-not-allowed select-none truncate transition-colors duration-200 hover:bg-gray-100 hover:border-gray-300">{{ $d['nilai'] }}</div>
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    @unless($isAdmin)
                        <p class="text-xs text-gray-400 -mt-1 transition-colors duration-200 hover:text-gray-600">Nomor Induk dan Peran tidak dapat diubah. Hubungi admin jika ada kesalahan.</p>
                    @endunless

                    <!-- PIN (hanya siswa dan guru) + tombol: menempel di dasar kartu supaya sejajar dengan kartu Kata Sandi -->
                    <div class="mt-auto flex flex-col gap-4">
                        @unless($isAdmin)
                            <x-field-sandi id="pin_info" name="pin_info" label="PIN Pemulihan (6 Angka)"
                                placeholder="Masukkan 6 digit PIN Pemulihan" :angka="true"
                                bantuan="Dibutuhkan untuk menyimpan perubahan." />
                        @endunless

                        <button type="submit"
                            class="self-start bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600 enabled:hover:shadow-lg enabled:hover:shadow-blue-600/30 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>

            <!-- ===== Kata Sandi ===== -->
            <section id="kata-sandi" class="min-w-0 scroll-mt-24 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-7 flex flex-col transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                <div class="flex items-start gap-3.5 mb-5">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 leading-tight transition-colors duration-300 hover:text-blue-700">Kata Sandi</h2>
                        <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-200 hover:text-gray-700">{{ $isAdmin ? 'Gunakan kata sandi baru yang kuat dan belum pernah dipakai.' : 'Gunakan kata sandi baru yang kuat dan belum pernah dipakai. Perubahan disimpan setelah PIN Pemulihan dimasukkan.' }}</p>
                    </div>
                </div>

                <form id="form-sandi" method="POST" action="{{ route('profil.sandi') }}" class="flex-1 flex flex-col gap-4" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <x-field-sandi id="password_baru" name="password_baru" label="Kata Sandi Baru" placeholder="Minimal 6 karakter" />
                    <x-field-sandi id="password_baru_confirmation" name="password_baru_confirmation" label="Ulangi Kata Sandi Baru" placeholder="Ulangi Kata Sandi" />
                    <p id="info-sandi" class="hidden text-red-500 text-xs -mt-2">Kata sandi tidak sama.</p>

                    <!-- Tips -->
                    <div class="flex items-start gap-2.5 bg-blue-50/70 border border-blue-100 text-blue-800 rounded-xl px-4 py-3 text-xs leading-relaxed transition-all duration-200 hover:bg-blue-50 hover:border-blue-200 hover:shadow-md hover:shadow-blue-900/5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Gunakan minimal 6 karakter, gabungkan huruf dan angka, serta hindari memakai NIS/NIP atau tanggal lahir.</p>
                    </div>

                    <!-- PIN (hanya siswa dan guru) + tombol: menempel di dasar kartu -->
                    <div class="mt-auto flex flex-col gap-4">
                        @unless($isAdmin)
                            <x-field-sandi id="pin_sandi" name="pin_sandi" label="PIN Pemulihan (6 Angka)"
                                placeholder="Masukkan 6 digit PIN Pemulihan" :angka="true"
                                bantuan="Dibutuhkan untuk mengganti kata sandi." />
                        @endunless

                        <button type="submit"
                            class="self-start bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600 enabled:hover:shadow-lg enabled:hover:shadow-blue-600/30 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98]">
                            Ubah Kata Sandi
                        </button>
                    </div>
                </form>
            </section>

            <!-- ===== PIN Pemulihan ===== -->
            <section id="pin-pemulihan" class="min-w-0 scroll-mt-24 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-7 flex flex-col transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                <div class="flex items-start gap-3.5 mb-5">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 leading-tight transition-colors duration-300 hover:text-blue-700">PIN Pemulihan</h2>
                        <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-200 hover:text-gray-700">{{ $isAdmin ? 'Dipakai untuk mengatur ulang kata sandi saat lupa. Hafalkan dan jangan bagikan ke siapa pun.' : 'Dipakai untuk mengonfirmasi perubahan Data Diri, mengganti Kata Sandi, mengajukan penghapusan akun, dan mengatur ulang kata sandi saat lupa. Hafalkan dan jangan bagikan ke siapa pun.' }}</p>
                    </div>
                </div>

                <form id="form-pin" method="POST" action="{{ route('profil.pin') }}" class="flex-1 flex flex-col gap-4" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <x-field-sandi id="pin_lama" name="pin_lama" label="PIN Pemulihan Lama"
                        placeholder="Masukkan 6 digit PIN lama" :angka="true" />

                    <!-- Siswa/guru: PIN baru dan ulangi berdampingan. Admin: disusun menurun supaya tinggi kartu mengikuti kartu Kata Sandi. -->
                    <div class="grid gap-4 {{ $isAdmin ? '' : 'sm:grid-cols-2' }}">
                        <x-field-sandi id="pin_baru" name="pin_baru" label="PIN Pemulihan Baru"
                            placeholder="{{ $isAdmin ? 'Masukkan 6 digit PIN baru' : '6 digit PIN baru' }}" :angka="true" />
                        <x-field-sandi id="pin_baru_confirmation" name="pin_baru_confirmation" label="Ulangi PIN Baru"
                            placeholder="Ulangi PIN baru" :angka="true" />
                    </div>
                    <p id="info-pin" class="hidden text-red-500 text-xs -mt-2">PIN baru tidak sama.</p>

                    <button type="submit"
                        class="self-start mt-auto bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600 enabled:hover:shadow-lg enabled:hover:shadow-blue-600/30 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98]">
                        Ganti PIN Pemulihan
                    </button>
                </form>
            </section>

            <!-- ===== Akun: Keluar + Hapus Akun (siswa dan guru) ===== -->
            @unless($isAdmin)
                <section id="akun" class="min-w-0 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-7 flex flex-col transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">

                    <!-- Keluar dari Akun -->
                    <div id="keluar" class="flex flex-wrap items-start gap-x-3.5 gap-y-4 pb-6">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                        <div class="flex-1 min-w-[10rem]">
                            <h2 class="text-lg font-bold text-gray-900 leading-tight transition-colors duration-300 hover:text-blue-700">Keluar dari Akun</h2>
                            <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-200 hover:text-gray-700">Akhiri sesi kamu di perangkat ini.</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="bg-white hover:bg-red-50 text-gray-700 hover:text-red-600 border border-gray-300 hover:border-red-300 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                Keluar
                            </button>
                        </form>
                    </div>

                    <!-- Hapus Akun (permanen, perlu persetujuan admin) -->
                    <div id="hapus-akun" class="mt-auto border-t border-gray-200 pt-6">
                        <div class="flex items-start gap-3.5 mb-5">
                            <span class="w-10 h-10 shrink-0 rounded-xl bg-red-50 text-red-600 flex items-center justify-center transition-all duration-300 hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold text-red-700 leading-tight transition-colors duration-300 hover:text-red-800">Hapus Akun</h2>
                                <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-200 hover:text-gray-700">Penghapusan bersifat permanen dan harus disetujui admin. Akun tetap bisa dipakai sampai permintaan disetujui, dan tidak bisa diajukan selama masih ada peminjaman yang berjalan.</p>
                            </div>
                        </div>

                        @if($teksWaktuMinta)
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm mb-4 transition-all duration-200 hover:shadow-md hover:shadow-amber-900/10">
                                Permintaan penghapusan akun diajukan pada <span class="font-semibold">{{ $teksWaktuMinta }}</span> dan sedang menunggu persetujuan admin.
                            </div>

                            @if($hapusBatalSiap)
                                <form method="POST" action="{{ $urlHapusBatal }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                        Batalkan Permintaan
                                    </button>
                                </form>
                            @else
                                <p class="text-sm text-gray-500">Pembatalan permintaan akan tersedia setelah backend diaktifkan.</p>
                            @endif
                        @else
                            <form id="form-hapus" method="POST" action="{{ $urlHapusAjukan }}" data-siap="{{ $hapusSiap ? '1' : '0' }}" class="flex flex-col gap-4" autocomplete="off">
                                @csrf

                                <x-field-sandi id="pin_hapus" name="pin_hapus" label="PIN Pemulihan (6 Angka)"
                                    placeholder="Masukkan 6 digit PIN Pemulihan" :angka="true"
                                    bantuan="Dibutuhkan untuk mengajukan penghapusan akun." />

                                <button type="submit"
                                    @disabled(! $hapusSiap)
                                    class="self-start bg-white hover:bg-red-50 text-red-700 border border-red-300 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white enabled:hover:shadow-md enabled:hover:shadow-red-600/20 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98]">
                                    Ajukan Penghapusan Akun
                                </button>
                            </form>
                        @endif
                    </div>

                </section>
            @endunless

            <!-- ===== Keluar khusus admin ===== 
                 Di layar xl ke atas admin keluar lewat profil dropdown di navbar. Di bawah xl dropdown disembunyikan,
                 jadi kartu ini muncul sebagai gantinya supaya admin tetap bisa keluar dari halaman ini. -->
            @if($isAdmin)
                <section id="keluar-admin" class="xl:hidden lg:col-span-2 min-w-0 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-7 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                    <div class="flex flex-wrap items-start gap-x-3.5 gap-y-4">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                        <div class="flex-1 min-w-[10rem]">
                            <h2 class="text-lg font-bold text-gray-900 leading-tight transition-colors duration-300 hover:text-blue-700">Keluar dari Akun</h2>
                            <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-200 hover:text-gray-700">Akhiri sesi kamu di perangkat ini.</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="bg-white hover:bg-red-50 text-gray-700 hover:text-red-600 border border-gray-300 hover:border-red-300 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                Keluar
                            </button>
                        </form>
                    </div>
                </section>
            @endif
        </div>

    </main>

    <!-- Footer ringkas.
         Siswa dan guru: sama dengan halaman form peminjaman.
         Admin: dua hak cipta, kiri SiLat Multimedia dan kanan Mulmedska SMK Negeri 5 Surakarta.
         Hover teks footer dibungkus [@media(hover:hover)] supaya tidak "menempel" di layar sentuh. -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left text-xs text-gray-600 [&>p]:transition-colors [&>p]:duration-200 [@media(hover:hover)]:[&>p:hover]:text-gray-900">
            @if($isAdmin)
                <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
                <p class="sm:text-right">&copy; {{ date('Y') }} Mulmedska SMK Negeri 5 Surakarta</p>
            @else
                <p>Ada kendala dengan akunmu?<span class="mt-0.5 block font-bold text-blue-600">Temui admin Ekskul Multimedia secara langsung.</span></p>
                <p class="sm:text-right">&copy; {{ date('Y') }} SiLat Multimedia &middot; Mulmedska SMK Negeri 5 Surakarta</p>
            @endif
        </div>
    </footer>

    <script>
        // ===== Bayangan navbar muncul saat halaman digulir =====
        (function () {
            var navbar = document.getElementById('navbar');
            function periksa() { navbar.classList.toggle('shadow-md', window.scrollY > 8); }
            window.addEventListener('scroll', periksa, { passive: true });
            periksa();
        })();

        // ===== Toast sukses: hilang otomatis =====
        var toast = document.getElementById('toast-sukses');
        if (toast) {
            setTimeout(function () { toast.classList.add('opacity-0'); }, 4000);
            setTimeout(function () { toast.remove(); }, 4600);
        }

        // ===== Klik menu bagian: scroll halus, alamat tanpa #, kartu tujuan menyala sebentar =====
        function sorotKartu(el) {
            el.classList.add('ring-4', 'ring-blue-500/25', 'border-blue-300');
            setTimeout(function () {
                el.classList.remove('ring-4', 'ring-blue-500/25', 'border-blue-300');
            }, 1600);
        }

        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var target = document.getElementById(a.getAttribute('href').slice(1));
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', location.pathname + location.search);
                sorotKartu(target);
            });
        });

        // ===== Setelah simpan: controller mengembalikan ke /profil#bagian. Browser sudah menggulir ke bagian itu,
        //       jadi kartunya disorot sebentar lalu # dihapus dari alamat. =====
        if (location.hash) {
            window.addEventListener('load', function () {
                var tujuan = document.getElementById(decodeURIComponent(location.hash.slice(1)));
                if (tujuan) sorotKartu(tujuan);
                history.replaceState(null, '', location.pathname + location.search);
            });
        }

        // ===== Lihat / sembunyikan kata sandi & PIN =====
        document.addEventListener('click', function (e) {
            var tombol = e.target.closest('[data-lihat]');
            if (!tombol) return;

            var input = document.getElementById(tombol.dataset.lihat);
            var tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            tombol.querySelector('.lihat-buka').classList.toggle('hidden', tampil);
            tombol.querySelector('.lihat-tutup').classList.toggle('hidden', !tampil);
        });

        // ===== Foto profil: pratinjau sebelum disimpan =====
        var fotoInput = document.getElementById('foto');
        var avatarImg = document.getElementById('avatar-img');
        var avatarFallback = document.getElementById('avatar-fallback');
        var fotoAksi = document.getElementById('foto-aksi');
        var fotoError = document.getElementById('foto-error');
        var fotoAwal = avatarImg.getAttribute('src') || '';

        function tampilkanErrorFoto(pesan) {
            fotoError.textContent = pesan;
            fotoError.classList.remove('hidden');
        }

        function sembunyikanErrorFoto() {
            fotoError.classList.add('hidden');
        }

        function tampilkanAksiFoto(tampil) {
            fotoAksi.classList.toggle('hidden', !tampil);
            fotoAksi.classList.toggle('flex', tampil);
        }

        fotoInput.addEventListener('change', function () {
            var file = fotoInput.files[0];
            if (!file) return;

            if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
                tampilkanErrorFoto('Format foto harus JPG, PNG, atau WEBP.');
                fotoInput.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                tampilkanErrorFoto('Ukuran foto maksimal 2 MB.');
                fotoInput.value = '';
                return;
            }

            sembunyikanErrorFoto();

            var reader = new FileReader();
            reader.onload = function (e) {
                avatarImg.src = e.target.result;
                avatarImg.classList.remove('hidden');
                if (avatarFallback) avatarFallback.classList.add('hidden');
                tampilkanAksiFoto(true);
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('foto-batal').addEventListener('click', function () {
            fotoInput.value = '';
            sembunyikanErrorFoto();

            if (fotoAwal) {
                avatarImg.src = fotoAwal;
            } else {
                avatarImg.removeAttribute('src');
                avatarImg.classList.add('hidden');
                if (avatarFallback) avatarFallback.classList.remove('hidden');
            }
            tampilkanAksiFoto(false);
        });

        // ===== Form: tombol aktif bila ada perubahan yang sah (dan PIN terisi 6 angka, bila form punya kolom PIN) =====
        // namaPin = nama input PIN di form ini. Admin tidak punya kolom PIN di form Data Diri dan Kata Sandi,
        // jadi untuk admin hanya perubahan yang sah yang dicek.
        // periksa(form) mengembalikan { berubah: bool, sah: bool }
        function pasangForm(idForm, namaPin, periksa) {
            var form = document.getElementById(idForm);
            if (!form) return;

            var tombol = form.querySelector('button[type="submit"]');
            var inputPin = namaPin ? form.elements[namaPin] : null;

            function perbarui() {
                var hasil = periksa(form);
                var siap = hasil.sah && hasil.berubah;

                if (inputPin) {
                    siap = siap && inputPin.value.length === 6;
                }

                tombol.disabled = !siap;
            }

            form.addEventListener('input', perbarui);
            form.addEventListener('change', perbarui);   // pilihan kelas bisa berubah lewat "change", bukan "input"
            perbarui();
        }

        pasangForm('form-data', 'pin_info', function (f) {
            var kolomNama = f.elements.nama_lengkap;
            var nama = kolomNama.value.trim().replace(/\s+/g, ' ');
            var berubah = nama !== kolomNama.dataset.awal;
            var kelasValid = true;

            // Kolom kelas hanya ada untuk siswa. Nilai awalnya disimpan di atribut form supaya tidak bergantung pada isi kelas-picker.
            var kolomKelas = f.elements.kelas_jurusan;
            if (kolomKelas) {
                berubah = berubah || kolomKelas.value !== f.dataset.kelasAwal;
                kelasValid = kolomKelas.value !== '';
            }

            return { berubah: berubah, sah: nama !== '' && kelasValid };
        });

        pasangForm('form-sandi', 'pin_sandi', function (f) {
            var baru = f.elements.password_baru.value;
            var ulang = f.elements.password_baru_confirmation.value;

            document.getElementById('info-sandi').classList.toggle('hidden', !(ulang !== '' && baru !== ulang));

            return { berubah: baru !== '' || ulang !== '', sah: baru.length >= 6 && baru === ulang };
        });

        pasangForm('form-pin', null, function (f) {
            var baru = f.elements.pin_baru.value;
            var ulang = f.elements.pin_baru_confirmation.value;

            document.getElementById('info-pin').classList.toggle('hidden', !(ulang !== '' && baru !== ulang));

            return { berubah: true, sah: f.elements.pin_lama.value.length === 6 && baru.length === 6 && baru === ulang };
        });

        pasangForm('form-hapus', null, function (f) {
            return { berubah: true, sah: f.elements.pin_hapus.value.length === 6 };
        });

        // ===== Hapus Akun: konfirmasi, dan cegah kirim jika backend belum dipasang =====
        var formHapus = document.getElementById('form-hapus');
        if (formHapus) {
            formHapus.addEventListener('submit', function (e) {
                if (formHapus.dataset.siap !== '1') {
                    e.preventDefault();
                    alert('Fitur Hapus Akun belum aktif karena backend-nya belum dipasang.');
                    return;
                }
                if (!confirm('Ajukan penghapusan akun secara permanen? Permintaan akan ditinjau oleh admin.')) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>