<!DOCTYPE html>
<html lang="id" class="scroll-pt-20">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">

@php
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

        <!-- Sisi Kiri (Visual / Branding), sama dengan halaman Masuk dan Daftar. Hanya tampil di layar lebar -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-center items-center p-12 relative overflow-hidden">
            <img src="{{ asset('images/password.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35">
            <!-- Overlay sedikit dipertebal supaya teks di atas foto kontrasnya aman -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/85 via-slate-900/88 to-slate-900/95"></div>

            <div class="relative z-10 text-center text-white max-w-sm px-4">
                <!-- Logo Eskul (bisa diklik, kembali ke beranda) -->
                <a href="{{ url('/') }}" aria-label="Kembali ke beranda SiLat" class="inline-block mb-6 transition duration-300 hover:scale-105 active:scale-95">
                    <img src="{{ asset('images/logonew.png') }}" alt="Logo Ekskul Multimedia" class="w-32 h-32 object-contain mx-auto drop-shadow-xl">
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
            <img src="{{ asset('images/password.jpg') }}" alt="" aria-hidden="true" loading="lazy"
                class="lg:hidden absolute inset-0 -z-10 w-full h-full object-cover opacity-[0.35] pointer-events-none select-none">
            <div class="lg:hidden absolute inset-0 -z-10 bg-gradient-to-b from-blue-50/30 to-white/20 pointer-events-none"></div>

            <!-- Navbar (hanya HP/tablet, karena panel kiri disembunyikan) -->
            <header class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-200/80">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <a href="{{ url('/') }}" aria-label="SiLat, ke beranda" class="group flex items-center gap-2.5 transition active:scale-95">
                        <img src="{{ asset('images/logo.png') }}" alt="" class="w-9 h-9 object-contain transition duration-200 group-hover:scale-110">
                        <span class="text-xl font-extrabold text-blue-600 tracking-tight">SiLat</span>
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/30 transition duration-200 hover:bg-blue-700 hover:shadow-md active:scale-95">
                        Beranda
                    </a>
                </div>
            </header>

            <div class="flex-1 flex px-4 sm:px-8 py-6">
                <div class="w-full max-w-md mx-auto my-auto rounded-2xl bg-white/85 backdrop-blur-sm p-5 sm:p-6 shadow-lg ring-1 ring-black/5 lg:rounded-none lg:bg-transparent lg:backdrop-blur-none lg:p-0 lg:shadow-none lg:ring-0">

                    <div class="mb-4 text-center lg:text-left">
                        <h2 class="text-3xl font-bold text-gray-900">Lupa Kata Sandi</h2>
                        <p class="text-gray-500 mt-1.5 text-sm">Masukkan Nomor Induk, PIN, dan Kata Sandi baru Anda.</p>
                    </div>

                    <!-- Penjelasan singkat -->
                    <div class="flex items-start gap-2.5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 mb-4 text-sm text-gray-700 leading-relaxed">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Gunakan PIN yang Anda buat saat mendaftar. Kalau PIN juga terlupa, hubungi admin secara langsung untuk reset manual.</p>
                    </div>

                    @if($errors->any())
                        <div role="alert" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form-password" method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf

                        <!-- Nomor Induk -->
                        <div>
                            <label for="nomor_induk" class="block text-sm font-medium text-gray-700 mb-1">Nomor Induk</label>
                            <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required
                                autocomplete="username" inputmode="numeric" pattern="[0-9]*" maxlength="20"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                @error('nomor_induk') aria-invalid="true" aria-describedby="nomor_induk_error" @enderror
                                class="w-full px-4 py-2.5 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm {{ $gaya('nomor_induk') }}"
                                placeholder="Masukkan Nomor Induk Anda">
                            @error('nomor_induk')
                                <p id="nomor_induk_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- PIN -->
                        <div>
                            <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">PIN Pemulihan (6 Angka)</label>
                            <div class="relative">
                                <input id="pin" type="password" name="pin" minlength="6" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autocomplete="off"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    @error('pin') aria-invalid="true" aria-describedby="pin_error" @enderror
                                    class="w-full px-4 py-2.5 pr-10 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm {{ $gaya('pin') }}"
                                    placeholder="Masukkan 6 digit PIN Anda">
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
                            @error('pin')
                                <p id="pin_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kata Sandi Baru (bertumpuk di HP yang sempit) -->
                        <div class="pt-4 border-t border-gray-100">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="password_baru" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                                    <div class="relative">
                                        <input id="password_baru" type="password" name="password_baru" required minlength="6" autocomplete="new-password"
                                            @error('password_baru') aria-invalid="true" aria-describedby="password_baru_error" @enderror
                                            class="w-full px-4 py-2.5 pr-10 rounded-xl border bg-white focus:ring-2 outline-none transition text-sm {{ $gaya('password_baru') }}"
                                            placeholder="Minimal 6 karakter">
                                        <button type="button" onclick="togglePassword('password_baru', this)"
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
                                    @error('password_baru')
                                        <p id="password_baru_error" role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_baru_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Ulangi Kata Sandi</label>
                                    <div class="relative">
                                        <input id="password_baru_confirmation" type="password" name="password_baru_confirmation" required autocomplete="new-password"
                                            class="w-full px-4 py-2.5 pr-10 rounded-xl border bg-white border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm"
                                            placeholder="Ulangi Kata Sandi baru">
                                        <button type="button" onclick="togglePassword('password_baru_confirmation', this)"
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
                        </div>

                        <button id="tombol-ubah" type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl shadow-lg shadow-blue-600/30 text-sm transition duration-300 hover:bg-blue-700 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-600/40 active:translate-y-0 active:scale-[0.99] disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none">
                            Ubah Kata Sandi
                        </button>

                        <p class="text-center text-sm text-gray-600 !mt-4">
                            Sudah ingat kata sandi?
                            <a href="{{ route('login') }}" class="font-semibold text-blue-600 transition hover:text-blue-700 hover:underline underline-offset-4 active:text-blue-800">Masuk di sini</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
            return v.valueMissing ? 'Nomor Induk wajib diisi' : 'Nomor Induk hanya boleh berisi angka';
        });
        pasangPesan('pin', function (v) {
            return v.valueMissing ? 'PIN Pemulihan wajib diisi' : 'PIN harus tepat 6 angka';
        });
        pasangPesan('password_baru', function (v) {
            return v.valueMissing ? 'Kata Sandi Baru wajib diisi' : 'Kata Sandi Baru minimal 6 karakter';
        });
        pasangPesan('password_baru_confirmation', function () { return 'Ulangi Kata Sandi wajib diisi'; });

        // ===== Cek kecocokan kedua kata sandi =====
        var sandi = document.getElementById('password_baru');
        var sandi2 = document.getElementById('password_baru_confirmation');
        var sandiError = document.getElementById('sandi-error');

        function cekKecocokan() {
            var tidakCocok = sandi2.value !== '' && sandi.value !== sandi2.value;
            sandiError.classList.toggle('hidden', !tidakCocok);
            tandaiError(sandi2, tidakCocok);
            return !tidakCocok;
        }

        sandi.addEventListener('input', cekKecocokan);
        sandi2.addEventListener('input', cekKecocokan);

        // ===== Kirim form: cek kata sandi, lalu cegah kirim ganda =====
        var form = document.getElementById('form-password');
        var tombol = document.getElementById('tombol-ubah');
        var teksAwal = tombol.textContent.trim();

        form.addEventListener('submit', function (e) {
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

        // Fokus otomatis hanya di layar lebar (di HP keyboard akan langsung menutupi halaman).
        // Kalau ada kolom yang salah, fokus ke kolom itu.
        if (window.matchMedia('(min-width: 1024px)').matches) {
            var fokusAwal = document.querySelector('[aria-invalid="true"]') || document.getElementById('nomor_induk');
            fokusAwal.focus();
        }
    </script>
</body>
</html>