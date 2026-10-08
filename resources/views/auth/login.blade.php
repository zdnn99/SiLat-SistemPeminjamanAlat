<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SiLat Multimedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex">
        
        <!-- Sisi Kiri (Visual / Branding) -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-center items-center p-12 relative overflow-hidden">
            <!-- Background Image Studio Blur -->
            <img src="{{ asset('images/login.jpg') }}" alt="Banner Background" class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-slate-900/80 to-slate-900/90"></div>
            
            <div class="relative z-10 text-center text-white max-w-sm px-4">
                <!-- Logo Eskul -->
                <img src="{{ asset('images/logonew.png') }}" alt="Logo Ekskul Multimedia" class="w-32 h-32 object-contain mx-auto mb-6 drop-shadow-xl">
                
                <h1 class="text-4xl font-extrabold mb-2 tracking-tight">SiLat Multimedia</h1>
                <p class="text-xs text-slate-300 mb-3">Sistem Peminjaman Alat Multimedia</p>
                <p class="text-sm font-semibold text-blue-300 mb-8 uppercase tracking-widest">SMKN 5 Surakarta</p>

                <!-- Poin Fitur Singkat -->
                <div class="space-y-4 text-left">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-300 pt-1.5">Ajukan peminjaman alat kapan saja</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-300 pt-1.5">Pantau status persetujuan admin secara real-time</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-300 pt-1.5">Riwayat peminjaman tersimpan rapi dan tercatat</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 flex-shrink-0 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-300 pt-1.5">Bisa digunakan siswa, guru, dan staff sekolah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan (Form) -->
        <div class="w-full lg:w-1/2 flex justify-center items-center p-8 bg-white overflow-y-auto">
            <div class="w-full max-w-md my-auto">
                
                <div class="mb-6 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-900">Selamat Datang</h2>
                    <p class="text-gray-500 mt-1.5 text-sm">Silakan masuk menggunakan Nomor Induk dan kata sandi Anda.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Input Nomor Induk -->
                    <div>
                        <label for="nomor_induk" class="block text-sm font-medium text-gray-700 mb-1">Nomor Induk</label>
                        <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required autofocus
                            autocomplete="username" inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('nomor_induk') border-red-500 @enderror"
                            placeholder="Masukkan Nomor Induk Anda">
                        @error('nomor_induk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('password') border-red-500 @enderror"
                                placeholder="Masukkan Kata Sandi">
                            <button type="button" onclick="togglePassword('password', this)" tabindex="-1"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4 icon-eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 icon-eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ingat Saya & Lupa Password -->
                    <div class="flex items-center justify-between mt-2">
                        <label for="remember_me" class="flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" 
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4">
                            <span class="ml-2 text-sm text-gray-600">Ingat Saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                                Lupa Kata Sandi?
                            </a>
                        @endif
                    </div>

                    <!-- Tombol Login -->
                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-lg shadow-blue-600/30 text-sm mt-4">
                        Masuk
                    </button>

                    <!-- Link Register -->
                    <p class="text-center text-sm text-gray-600 mt-5">
                        Belum punya akun? 
                        <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">Daftar di sini</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            var eyeOpen = btn.querySelector('.icon-eye-open');
            var eyeClosed = btn.querySelector('.icon-eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>