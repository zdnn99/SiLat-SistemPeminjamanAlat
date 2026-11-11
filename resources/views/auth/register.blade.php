<!DOCTYPE html>
<html lang="id" class="scroll-pt-20">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">

    @php
        // Daftar kelas & jurusan. Ubah di sini kalau ada penambahan atau perubahan kelas.
        // Catatan: kalau validasi di controller memakai config/kelas.php, daftar di sana harus sama dengan ini.
        $jurusanX = ['TKP', 'DPIB A', 'DPIB B', 'PPLG A', 'PPLG B', 'PPLG C', 'TK A', 'TK B', 'TE A', 'TE B', 'TE C', 'TE D', 'TE E', 'TM A', 'TM B', 'TM C', 'TM D', 'TO A', 'TO B', 'TO C', 'TO D'];
        $jurusanXiXii = ['TKP', 'DPIB A', 'DPIB B', 'RPL A', 'RPL B', 'RPL C', 'TITL A', 'TITL B', 'TEI A', 'TEI B', 'TEI C', 'TOI A', 'TOI B', 'TP A', 'TP B', 'TP C', 'TP D', 'TKR A', 'TKR B', 'TKR C', 'TSM A', 'TSM B'];

        $kelasList = [
            'X' => array_map(fn ($j) => 'X ' . $j, $jurusanX),
            'XI' => array_map(fn ($j) => 'XI ' . $j, $jurusanXiXii),
            'XII' => array_map(fn ($j) => 'XII ' . $j, $jurusanXiXii),
        ];

        // Peran yang dipilih (dipulihkan setelah gagal mendaftar, supaya tampilan tidak kembali ke Siswa)
        $peranDipilih = old('peran', 'siswa');

        // Warna garis kolom: merah kalau ada error dari server, biru saat fokus kalau normal
        $gayaNormal = 'border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500/20';
        $gayaError  = 'border-red-500 focus:border-red-500 focus:ring-red-500/20';
        $gaya = fn ($nama) => $errors->has($nama) ? $gayaError : $gayaNormal;

        $fitur = [
            ['path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                                  'teks' => 'Ajukan peminjaman alat kapan saja'],
            ['path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                'teks' => 'Pantau status persetujuan admin secara real-time'],
            ['path' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2', 'teks' => 'Riwayat peminjaman tersimpan rapi dan tercatat'],
            ['path' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4', 'teks' => 'Bisa digunakan siswa, guru, dan staf sekolah'],
        ];
    @endphp

    <!-- Tinggi dikunci setinggi layar di desktop supaya panel kiri tidak ikut memanjang -->
    <div class="min-h-dvh lg:h-screen lg:overflow-hidden flex">

        <!-- Sisi Kiri (Visual / Branding), hanya tampil di layar lebar -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-center items-center p-12 relative overflow-hidden">
            <!-- Background Image Studio Blur -->
            <img src="{{ asset('images/register.webp') }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35">
            <!-- Overlay sedikit dipertebal supaya teks di atas foto kontrasnya aman -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/85 via-slate-900/88 to-slate-900/95"></div>

            <div class="relative z-10 text-center text-white max-w-sm px-4">
                <!-- Logo Eskul (bisa diklik, kembali ke beranda) -->
                <a href="{{ url('/') }}" aria-label="Kembali ke beranda SiLat" class="inline-block mb-6 transition duration-300 hover:scale-105 active:scale-95">
                    <img src="{{ asset('images/logonew.webp') }}" alt="Logo Ekskul Multimedia" class="w-32 h-32 object-contain mx-auto drop-shadow-xl">
                </a>

                <h1 class="text-4xl font-extrabold mb-2 tracking-tight">SiLat Multimedia</h1>
                <p class="text-sm text-slate-100 mb-3">Sistem Peminjaman Alat Multimedia</p>
                <p class="text-sm font-semibold text-blue-300 mb-8 uppercase tracking-widest">SMKN 5 Surakarta</p>

                <!-- Poin Fitur Singkat -->
                <div class="space-y-2 text-left">
                    @foreach ($fitur as $f)
                        <div class="group flex items-start gap-3 -mx-3 px-3 py-2 rounded-xl transition duration-200 hover:bg-white/10">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-blue-500/25 flex items-center justify-center transition duration-200 group-hover:bg-blue-500/50 group-hover:scale-110">
                                <svg class="w-4 h-4 text-blue-200 transition-colors duration-200 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['path'] }}"/>
                                </svg>
                            </div>
                            <p class="text-sm text-slate-100 pt-1.5">{{ $f['teks'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sisi Kanan (Form). Di layar lebar, kalau form lebih tinggi dari layar hanya kolom ini yang scroll.
             Di HP/tablet diberi foto tipis sebagai latar dan navbar di atas -->
        <div class="relative isolate w-full lg:w-1/2 flex flex-col bg-white lg:overflow-y-auto">

            <!-- Latar tipis (hanya HP/tablet) -->
            <img src="{{ asset('images/register.webp') }}" alt="" aria-hidden="true" loading="lazy"
                class="lg:hidden absolute inset-0 -z-10 w-full h-full object-cover opacity-[0.35] pointer-events-none select-none">
            <div class="lg:hidden absolute inset-0 -z-10 bg-gradient-to-b from-blue-50/30 to-white/20 pointer-events-none"></div>

            <!-- Navbar (hanya HP/tablet, karena panel kiri disembunyikan) -->
            <header class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-200/80">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <a href="{{ url('/') }}" aria-label="SiLat, ke beranda" class="group flex items-center gap-2.5 transition active:scale-95">
                        <img src="{{ asset('images/logo.webp') }}" alt="" class="w-9 h-9 object-contain transition duration-200 group-hover:scale-110">
                        <span class="text-xl font-extrabold text-blue-600 tracking-tight">SiLat</span>
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/30 transition duration-200 hover:bg-blue-700 hover:shadow-md active:scale-95">
                        Beranda
                    </a>
                </div>
            </header>

            <div class="flex-1 flex px-4 sm:px-8 py-6 lg:py-4">
                <div class="w-full max-w-md mx-auto my-auto rounded-2xl bg-white/85 backdrop-blur-sm p-5 sm:p-6 shadow-lg ring-1 ring-black/5 lg:rounded-none lg:bg-transparent lg:backdrop-blur-none lg:p-0 lg:shadow-none lg:ring-0">

                    <div class="mb-4 text-center lg:text-left">
                        <h2 class="text-3xl font-bold text-gray-900">Buat Akun</h2>
                        <p class="text-gray-500 mt-1.5 text-sm">Mendaftar untuk mulai meminjam alat di SiLat Multimedia</p>
                    </div>

                    @if($errors->any())
                        <div role="alert" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    @if ($error === 'Nomor Induk sudah terdaftar' || str_contains($error, 'has already been taken'))
                                        <li>Silahkan login atau jika ada kendala, hubungi admin/temui secara langsung.</li>
                                    @else
                                        <li>{{ $error }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form-register" method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <!-- Mendaftar Sebagai (Radio Button) -->
                        <div>
                            <span id="label-peran" class="block text-sm font-medium text-gray-700 mb-2">Mendaftar Sebagai:</span>
                            <div role="radiogroup" aria-labelledby="label-peran" class="flex items-center space-x-6">
                                <label class="group flex items-center cursor-pointer">
                                    <input type="radio" name="peran" value="siswa" id="radio-siswa" {{ $peranDipilih === 'siswa' ? 'checked' : '' }} onchange="toggleForm()"
                                        class="w-4 h-4 text-blue-600 border-gray-300 cursor-pointer transition hover:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 transition-colors group-hover:text-blue-600">Siswa</span>
                                </label>
                                <label class="group flex items-center cursor-pointer">
                                    <input type="radio" name="peran" value="guru_karyawan" id="radio-guru" {{ $peranDipilih === 'guru_karyawan' ? 'checked' : '' }} onchange="toggleForm()"
                                        class="w-4 h-4 text-blue-600 border-gray-300 cursor-pointer transition hover:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 transition-colors group-hover:text-blue-600">Guru / Karyawan</span>
                                </label>
                            </div>
                            @error('peran')
                                <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nomor Induk (NIS/NIP) - hanya angka -->
                        <div>
                            <label for="nomor_induk" id="label-nomor-induk" class="block text-sm font-medium text-gray-700 mb-1">Nomor Induk Siswa (NIS)</label>
                            <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required
                                autocomplete="username" inputmode="numeric" pattern="[0-9]*" maxlength="5"
                                data-maxlength-siswa="5" data-maxlength-guru="20"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                @error('nomor_induk') aria-invalid="true" aria-describedby="nomor_induk_error" @enderror
                                class="w-full px-4 py-2.5 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm {{ $gaya('nomor_induk') }}"
                                placeholder="Masukkan NIS Anda">
                            @error('nomor_induk')
                                <p id="nomor_induk_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Lengkap - hanya huruf, otomatis kapital -->
                        <div>
                            <label for="nama_lengkap" id="label-nama-lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap (Sesuai Presensi)</label>
                            <input id="nama_lengkap" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                                autocomplete="name"
                                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z\s]/g, '')"
                                @error('nama_lengkap') aria-invalid="true" aria-describedby="nama_lengkap_error" @enderror
                                class="w-full px-4 py-2.5 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm uppercase placeholder:normal-case {{ $gaya('nama_lengkap') }}"
                                placeholder="Masukkan Nama Lengkap Anda">
                            @error('nama_lengkap')
                                <p id="nama_lengkap_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kelas & Jurusan (khusus Siswa): kotak pencarian + daftar bertingkat -->
                        <div id="form-kelas-jurusan">
                            <label for="kelas_search" class="block text-sm font-medium text-gray-700 mb-1">Kelas & Jurusan</label>

                            <div class="relative" id="kelas-wrapper">
                                <!-- Nilai yang benar-benar dikirim ke server -->
                                <input type="hidden" id="kelas_jurusan" name="kelas_jurusan" value="{{ old('kelas_jurusan') }}">

                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                </span>

                                <input id="kelas_search" type="text" autocomplete="off" role="combobox"
                                    aria-expanded="false" aria-controls="kelas-list"
                                    value="{{ old('kelas_jurusan') }}"
                                    @error('kelas_jurusan') aria-invalid="true" @enderror
                                    placeholder="Cari atau pilih kelas Anda"
                                    class="w-full pl-11 pr-10 py-2.5 rounded-xl border focus:ring-2 outline-none transition bg-white text-sm {{ $gaya('kelas_jurusan') }}">

                                <button type="button" id="kelas-toggle" tabindex="-1" aria-label="Buka daftar kelas"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 transition hover:text-blue-600 active:text-blue-700">
                                    <svg id="kelas-chevron" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Panel daftar kelas -->
                                <div id="kelas-panel" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
                                    <div id="kelas-chips" class="flex items-center gap-2 px-3 py-2.5 border-b border-gray-100 bg-gray-50 overflow-x-auto">
                                        <button type="button" data-tingkat="semua" class="kelas-chip">Semua</button>
                                        <button type="button" data-tingkat="X" class="kelas-chip">Kelas X</button>
                                        <button type="button" data-tingkat="XI" class="kelas-chip">Kelas XI</button>
                                        <button type="button" data-tingkat="XII" class="kelas-chip">Kelas XII</button>
                                    </div>
                                    <div id="kelas-list" role="listbox" class="relative max-h-60 overflow-y-auto pb-1"></div>
                                </div>
                            </div>

                            <p id="kelas-error" role="alert" class="hidden text-red-500 text-xs mt-1">Silakan pilih Kelas & Jurusan Anda.</p>
                            @error('kelas_jurusan')
                                <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Grid Sejajar (bertumpuk di HP yang sempit) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Input Kata Sandi -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                                <div class="relative">
                                    <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password"
                                        @error('password') aria-invalid="true" aria-describedby="password_error" @enderror
                                        class="w-full px-4 py-2.5 pr-10 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm {{ $gaya('password') }}"
                                        placeholder="Minimal 6 karakter">
                                    <button type="button" onclick="togglePassword('password', this)"
                                        aria-label="Tampilkan kata sandi" aria-pressed="false"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center rounded-r-xl text-gray-400 transition hover:text-blue-600 active:text-blue-700 focus:outline-none focus-visible:text-blue-600">
                                        <svg class="w-4 h-4 icon-eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="w-4 h-4 icon-eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p id="password_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Konfirmasi Kata Sandi -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Ulangi Kata Sandi</label>
                                <div class="relative">
                                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                        class="w-full px-4 py-2.5 pr-10 rounded-xl border bg-white border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm"
                                        placeholder="Ulangi Kata Sandi">
                                    <button type="button" onclick="togglePassword('password_confirmation', this)"
                                        aria-label="Tampilkan kata sandi" aria-pressed="false"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center rounded-r-xl text-gray-400 transition hover:text-blue-600 active:text-blue-700 focus:outline-none focus-visible:text-blue-600">
                                        <svg class="w-4 h-4 icon-eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="w-4 h-4 icon-eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Muncul langsung saat kedua kata sandi berbeda -->
                            <p id="sandi-error" role="alert" class="hidden sm:col-span-2 text-red-500 text-xs -mt-2">Kata Sandi tidak cocok.</p>
                        </div>

                        <!-- Input PIN Pemulihan -->
                        <div>
                            <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">PIN Pemulihan (6 Angka)</label>
                            <div class="relative">
                                <input id="pin" type="password" name="pin" minlength="6" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autocomplete="off"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    @error('pin') aria-invalid="true" aria-describedby="pin_error" @enderror
                                    class="w-full px-4 py-2.5 pr-10 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm {{ $gaya('pin') }}"
                                    placeholder="Masukkan 6 digit PIN Pemulihan">
                                <button type="button" onclick="togglePassword('pin', this)"
                                    aria-label="Tampilkan PIN" aria-pressed="false"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center rounded-r-xl text-gray-400 transition hover:text-blue-600 active:text-blue-700 focus:outline-none focus-visible:text-blue-600">
                                    <svg class="w-4 h-4 icon-eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg class="w-4 h-4 icon-eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Catatan netral (bukan pesan error): ikon biru, teks abu gelap -->
                            <p class="flex items-start gap-1.5 text-xs text-gray-600 mt-1.5">
                                <svg class="w-4 h-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Harap hafalkan PIN ini, dibutuhkan untuk fitur Lupa Kata Sandi.</span>
                            </p>
                            @error('pin')
                                <p id="pin_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan: akun baru perlu disetujui admin -->
                        <p class="flex items-start gap-1.5 text-xs text-gray-600">
                            <svg class="w-4 h-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Akun baru bisa dipakai setelah disetujui oleh admin Ekskul Multimedia.</span>
                        </p>

                        <!-- Tombol Register -->
                        <button id="tombol-daftar" type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl shadow-lg shadow-blue-600/30 text-sm transition duration-300 hover:bg-blue-700 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-600/40 active:translate-y-0 active:scale-[0.99] disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none">
                            Daftar Sekarang
                        </button>

                        <!-- Link Login -->
                        <p class="text-center text-sm text-gray-600 !mt-3">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="font-semibold text-blue-600 transition hover:text-blue-700 hover:underline underline-offset-4 active:text-blue-800">Masuk di sini</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        // ===== Pembantu: tandai / lepas tanda error pada kolom =====
        var GAYA_NORMAL = ['border-gray-300', 'hover:border-gray-400', 'focus:border-blue-500', 'focus:ring-blue-500/20'];
        var GAYA_ERROR  = ['border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20'];

        function tandaiError(el, ada) {
            (ada ? GAYA_NORMAL : GAYA_ERROR).forEach(function (k) { el.classList.remove(k); });
            (ada ? GAYA_ERROR : GAYA_NORMAL).forEach(function (k) { el.classList.add(k); });
            if (ada) {
                el.setAttribute('aria-invalid', 'true');
            } else {
                el.removeAttribute('aria-invalid');
            }
        }

        // ===== Data kelas (berasal dari daftar di bagian atas file) =====
        const kelasData = @json($kelasList);
        const kelasLabel = { X: 'Kelas X', XI: 'Kelas XI', XII: 'Kelas XII' };
        const kelasUrutan = ['X', 'XI', 'XII'];

        const kelasInput = document.getElementById('kelas_search');
        const kelasValue = document.getElementById('kelas_jurusan');
        const kelasPanel = document.getElementById('kelas-panel');
        const kelasList = document.getElementById('kelas-list');
        const kelasChips = document.getElementById('kelas-chips');
        const kelasChevron = document.getElementById('kelas-chevron');
        const kelasToggle = document.getElementById('kelas-toggle');
        const kelasWrapper = document.getElementById('kelas-wrapper');
        const kelasError = document.getElementById('kelas-error');

        let kelasKataCari = '';
        let kelasTingkat = 'semua';
        let kelasItems = [];
        let kelasIndex = -1;

        function kelasTerbuka() {
            return !kelasPanel.classList.contains('hidden');
        }

        // Menebalkan bagian nama yang cocok dengan ketikan
        function kelasSorot(teks, kata) {
            if (!kata) return teks;
            var idx = teks.toLowerCase().indexOf(kata.toLowerCase());
            if (idx === -1) return teks;
            return teks.slice(0, idx) +
                '<span class="text-blue-600 font-bold">' + teks.slice(idx, idx + kata.length) + '</span>' +
                teks.slice(idx + kata.length);
        }

        function kelasItemHtml(nama, kata) {
            var aktif = nama === kelasValue.value;
            return '<button type="button" role="option" data-nilai="' + nama + '"' + (aktif ? ' data-aktif="1"' : '') +
                ' class="kelas-item w-full text-left flex items-center justify-between px-4 py-2 text-sm transition hover:bg-blue-50 active:bg-blue-100 ' +
                (aktif ? 'text-blue-600 font-semibold' : 'text-gray-700') + '">' +
                '<span>' + kelasSorot(nama, kata) + '</span>' +
                (aktif ? '<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>' : '') +
                '</button>';
        }

        function kelasPerbaruiChip() {
            kelasChips.querySelectorAll('.kelas-chip').forEach(function (chip) {
                var aktif = chip.dataset.tingkat === kelasTingkat;
                chip.className = 'kelas-chip shrink-0 px-3 py-1 rounded-full text-xs font-semibold border transition active:scale-95 ' +
                    (aktif
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-600');
            });
        }

        function kelasSetSorotan(i) {
            kelasIndex = i;
            kelasItems.forEach(function (el, n) {
                el.classList.toggle('bg-blue-50', n === i);
            });
            if (i >= 0 && kelasItems[i]) {
                kelasItems[i].scrollIntoView({ block: 'nearest' });
            }
        }

        function kelasRender() {
            var kata = kelasKataCari.trim();
            var kataNorm = kata.toLowerCase().replace(/\s+/g, '');
            var html = '';
            var adaHasil = false;

            kelasUrutan.forEach(function (tingkat) {
                if (kelasTingkat !== 'semua' && kelasTingkat !== tingkat) return;

                var hasil = kelasData[tingkat].filter(function (nama) {
                    return !kataNorm || nama.toLowerCase().replace(/\s+/g, '').includes(kataNorm);
                });
                if (hasil.length === 0) return;

                adaHasil = true;
                html += '<div class="sticky top-0 z-10 bg-white px-4 pt-2 pb-1 text-[11px] font-bold text-gray-400 uppercase tracking-wide">' + kelasLabel[tingkat] + '</div>';
                hasil.forEach(function (nama) {
                    html += kelasItemHtml(nama, kata);
                });
            });

            if (!adaHasil) {
                html =
                    '<div class="flex flex-col items-center justify-center text-center py-6 px-4">' +
                        '<p class="text-sm font-semibold text-gray-500">Kelas tidak ditemukan</p>' +
                        '<p class="text-xs text-gray-400 mt-1">Coba ketik jurusan, contoh: TKR atau XI TP</p>' +
                    '</div>';
            }

            kelasList.innerHTML = html;
            kelasItems = Array.from(kelasList.querySelectorAll('.kelas-item'));
            kelasSetSorotan(kata && kelasItems.length ? 0 : -1);
            kelasPerbaruiChip();
        }

        function kelasBuka() {
            kelasPanel.classList.remove('hidden');
            kelasChevron.classList.add('rotate-180');
            kelasInput.setAttribute('aria-expanded', 'true');
            kelasPanel.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        function kelasTutup() {
            kelasPanel.classList.add('hidden');
            kelasChevron.classList.remove('rotate-180');
            kelasInput.setAttribute('aria-expanded', 'false');
            // Kembalikan teks ke pilihan terakhir (atau kosong kalau belum memilih)
            kelasInput.value = kelasValue.value;
        }

        function kelasBukaBaru() {
            kelasKataCari = '';
            kelasTingkat = 'semua';
            kelasRender();
            kelasBuka();

            // Kalau sudah ada pilihan, geser daftar ke posisi pilihan itu
            var aktif = kelasList.querySelector('[data-aktif="1"]');
            if (aktif) {
                kelasList.scrollTop = Math.max(0, aktif.offsetTop - 60);
            }
            kelasInput.select();
        }

        function kelasPilih(nilai) {
            kelasValue.value = nilai;
            kelasError.classList.add('hidden');
            tandaiError(kelasInput, false);
            kelasTutup();
        }

        kelasInput.addEventListener('focus', function () {
            if (!kelasTerbuka()) kelasBukaBaru();
        });

        kelasInput.addEventListener('click', function () {
            if (!kelasTerbuka()) kelasBukaBaru();
        });

        kelasInput.addEventListener('input', function () {
            kelasValue.value = '';              // pilihan lama tidak berlaku lagi saat mengetik
            kelasInput.value = kelasInput.value.toUpperCase();
            kelasKataCari = kelasInput.value;
            if (!kelasTerbuka()) kelasBuka();
            kelasRender();
        });

        kelasInput.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!kelasTerbuka()) kelasBukaBaru();
                if (kelasItems.length) kelasSetSorotan(Math.min(kelasIndex + 1, kelasItems.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (kelasItems.length) kelasSetSorotan(Math.max(kelasIndex - 1, 0));
            } else if (e.key === 'Enter') {
                if (kelasTerbuka()) {
                    e.preventDefault();             // jangan kirim form saat Enter di daftar
                    if (kelasIndex >= 0 && kelasItems[kelasIndex]) {
                        kelasPilih(kelasItems[kelasIndex].dataset.nilai);
                    } else if (kelasItems.length === 1) {
                        kelasPilih(kelasItems[0].dataset.nilai);
                    }
                }
            } else if (e.key === 'Escape' || e.key === 'Tab') {
                if (kelasTerbuka()) kelasTutup();
            }
        });

        kelasToggle.addEventListener('click', function () {
            if (kelasTerbuka()) {
                kelasTutup();
            } else {
                kelasBukaBaru();
                kelasInput.focus();
            }
        });

        kelasChips.addEventListener('click', function (e) {
            var chip = e.target.closest('.kelas-chip');
            if (!chip) return;
            kelasTingkat = chip.dataset.tingkat;
            kelasRender();
            kelasInput.focus();
        });

        kelasList.addEventListener('click', function (e) {
            var item = e.target.closest('.kelas-item');
            if (item) kelasPilih(item.dataset.nilai);
        });

        document.addEventListener('click', function (e) {
            if (kelasTerbuka() && !kelasWrapper.contains(e.target)) kelasTutup();
        });

        // ===== Ganti tampilan sesuai peran =====
        function toggleForm() {
            var isGuru = document.getElementById('radio-guru').checked;
            var formKelas = document.getElementById('form-kelas-jurusan');
            var labelNomorInduk = document.getElementById('label-nomor-induk');
            var labelNamaLengkap = document.getElementById('label-nama-lengkap');
            var inputNomorInduk = document.getElementById('nomor_induk');

            if (isGuru) {
                if (kelasTerbuka()) kelasTutup();
                formKelas.style.display = 'none';
                kelasValue.disabled = true;     // tidak ikut terkirim untuk guru / karyawan (pilihan siswa tidak hilang)
                kelasError.classList.add('hidden');
                labelNomorInduk.innerText = 'Nomor Induk Pegawai (NIP)';
                labelNamaLengkap.innerText = 'Nama Lengkap';
                inputNomorInduk.placeholder = 'Masukkan NIP Anda';
                // NIP guru/karyawan: tidak ada batas panjang
                inputNomorInduk.removeAttribute('maxlength');
                inputNomorInduk.removeAttribute('pattern');
            } else {
                formKelas.style.display = 'block';
                kelasValue.disabled = false;
                labelNomorInduk.innerText = 'Nomor Induk Siswa (NIS)';
                labelNamaLengkap.innerText = 'Nama Lengkap (Sesuai Presensi)';
                inputNomorInduk.placeholder = 'Masukkan NIS Anda';
                // NIS siswa: wajib tepat 5 digit
                inputNomorInduk.maxLength = 5;
                inputNomorInduk.pattern = '[0-9]{5}';
            }
        }

        // ===== Lihat / sembunyikan kata sandi & PIN =====
        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            var tampil = input.type === 'password';
            var nama = inputId === 'pin' ? 'PIN' : 'kata sandi';

            input.type = tampil ? 'text' : 'password';
            btn.querySelector('.icon-eye-open').classList.toggle('hidden', tampil);
            btn.querySelector('.icon-eye-closed').classList.toggle('hidden', !tampil);
            btn.setAttribute('aria-label', (tampil ? 'Sembunyikan ' : 'Tampilkan ') + nama);
            btn.setAttribute('aria-pressed', tampil ? 'true' : 'false');
        }

        // ===== Pesan validasi dalam bahasa Indonesia (menggantikan pesan bawaan browser) =====
        function pasangPesan(id, buatPesan) {
            var el = document.getElementById(id);
            el.addEventListener('invalid', function () { el.setCustomValidity(buatPesan(el.validity)); });
            el.addEventListener('input', function () { el.setCustomValidity(''); });
        }

        pasangPesan('nomor_induk', function (v) {
            var nama = document.getElementById('radio-guru').checked ? 'NIP' : 'NIS';
            return v.valueMissing ? nama + ' wajib diisi' : nama + ' hanya boleh berisi angka';
        });
        pasangPesan('nama_lengkap', function () { return 'Nama Lengkap wajib diisi'; });
        pasangPesan('password', function (v) {
            return v.valueMissing ? 'Kata Sandi wajib diisi' : 'Kata Sandi minimal 6 karakter';
        });
        pasangPesan('password_confirmation', function () { return 'Ulangi Kata Sandi wajib diisi'; });
        pasangPesan('pin', function (v) {
            return v.valueMissing ? 'PIN Pemulihan wajib diisi' : 'PIN harus tepat 6 angka';
        });

        // ===== Cek kecocokan kedua kata sandi =====
        var sandi = document.getElementById('password');
        var sandi2 = document.getElementById('password_confirmation');
        var sandiError = document.getElementById('sandi-error');

        function cekKecocokan() {
            var tidakCocok = sandi2.value !== '' && sandi.value !== sandi2.value;
            sandiError.classList.toggle('hidden', !tidakCocok);
            tandaiError(sandi2, tidakCocok);
            return !tidakCocok;
        }

        sandi.addEventListener('input', cekKecocokan);
        sandi2.addEventListener('input', cekKecocokan);

        // ===== Kirim form: cek kelas & kata sandi, lalu cegah kirim ganda =====
        var form = document.getElementById('form-register');
        var tombol = document.getElementById('tombol-daftar');
        var teksAwal = tombol.textContent.trim();

        form.addEventListener('submit', function (e) {
            var isGuru = document.getElementById('radio-guru').checked;

            // Siswa wajib memilih kelas sebelum form dikirim
            if (!isGuru && !kelasValue.value) {
                e.preventDefault();
                kelasError.classList.remove('hidden');
                tandaiError(kelasInput, true);
                kelasInput.focus();
                return;
            }

            if (!cekKecocokan()) {
                e.preventDefault();
                sandi2.focus();
                return;
            }

            tombol.disabled = true;
            tombol.textContent = 'Memproses...';
        });

        // Kalau pengguna menekan tombol Back dan halaman dipulihkan dari cache, kembalikan tombolnya
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                tombol.disabled = false;
                tombol.textContent = teksAwal;
            }
        });

        // ===== Jalankan saat halaman pertama kali dimuat =====
        if (kelasValue.value) {
            kelasInput.value = kelasValue.value;
        }
        kelasPerbaruiChip();
        toggleForm();

        // Fokus otomatis hanya di layar lebar (di HP keyboard akan langsung menutupi halaman).
        // Kalau ada kolom yang salah, fokus ke kolom itu.
        if (window.matchMedia('(min-width: 1024px)').matches) {
            var fokusAwal = document.querySelector('[aria-invalid="true"]') || document.getElementById('nomor_induk');
            fokusAwal.focus();
        }
    </script>
</body>
</html>