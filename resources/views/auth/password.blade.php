<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - SiLat Multimedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex">

        <!-- Sisi Kiri (Visual / Branding) -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-center items-center p-12 relative overflow-hidden">
            <img src="{{ asset('images/password.jpg') }}" alt="Background" class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-slate-900/80 to-slate-900/90"></div>

            <div class="relative z-10 text-center text-white max-w-sm px-4">
                <img src="{{ asset('images/logonew.png') }}" alt="Logo" class="w-32 h-32 object-contain mx-auto mb-6 drop-shadow-xl">
                <h1 class="text-4xl font-extrabold mb-2 tracking-tight">SiLat Multimedia</h1>
                <p class="text-xs font-semibold text-blue-300 mb-3 uppercase tracking-widest">SMKN 5 Surakarta</p>
                <p class="text-sm text-slate-300 mb-8">Lupa Kata Sandi? Gunakan PIN yang Anda buat saat registrasi untuk membuat Kata Sandi baru.</p>

                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3.5 text-left">
                    <p class="text-sm text-slate-300 leading-relaxed">
                        PIN ini anda buat saat pertama kali mendaftar. Kalau sudah lupa PIN juga, hubungi admin secara langsung untuk reset manual.
                    </p>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan (Form) -->
        <div class="w-full lg:w-1/2 flex justify-center items-center p-8 bg-white overflow-y-auto">
            <div class="w-full max-w-md my-auto">

                <div class="mb-6 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-900">Lupa Kata Sandi</h2>
                    <p class="text-gray-500 mt-1.5 text-sm">Masukkan Nomor Induk, PIN, dan Kata Sandi baru Anda.</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf

                    <!-- Nomor Induk -->
                    <div>
                        <label for="nomor_induk" class="block text-sm font-medium text-gray-700 mb-1">Nomor Induk</label>
                        <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required autofocus
                            inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('nomor_induk') border-red-500 @enderror"
                            placeholder="Masukkan Nomor Induk Anda">
                        @error('nomor_induk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PIN -->
                    <div>
                        <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">PIN Pemulihan (6 Angka)</label>
                        <div class="relative">
                            <input id="pin" type="password" name="pin" maxlength="6" inputmode="numeric" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('pin') border-red-500 @enderror"
                                placeholder="Masukkan 6 digit PIN Anda">
                            <button type="button" onclick="togglePassword('pin', this)" tabindex="-1"
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
                        @error('pin')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Baru -->
                    <div class="grid sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                        <div>
                            <label for="password_baru" class="block text-sm font-medium text-gray-700 mb-1 mt-2">Kata Sandi Baru</label>
                            <div class="relative">
                                <input id="password_baru" type="password" name="password_baru" required
                                    class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('password_baru') border-red-500 @enderror"
                                    placeholder="Minimal 6 karakter">
                                <button type="button" onclick="togglePassword('password_baru', this)" tabindex="-1"
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
                            @error('password_baru')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_baru_confirmation" class="block text-sm font-medium text-gray-700 mb-1 mt-2">Ulangi Kata Sandi</label>
                            <div class="relative">
                                <input id="password_baru_confirmation" type="password" name="password_baru_confirmation" required
                                    class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm"
                                    placeholder="Ulangi Kata Sandi baru">
                                <button type="button" onclick="togglePassword('password_baru_confirmation', this)" tabindex="-1"
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
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-lg shadow-blue-600/30 text-sm mt-4">
                        Ubah Kata Sandi
                    </button>

                    <p class="text-center text-sm text-gray-600 mt-5">
                        Sudah ingat kata sandi?
                        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">Masuk di sini</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

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