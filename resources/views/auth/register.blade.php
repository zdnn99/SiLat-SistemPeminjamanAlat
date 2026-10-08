<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SiLat Multimedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex">
        
        <!-- Sisi Kiri (Visual / Branding) -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-center items-center p-12 relative overflow-hidden">
            <img src="{{ asset('images/bg-studio.jpg') }}" alt="Background Studio" class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-slate-900/80 to-slate-900/90"></div>
            
            <div class="relative z-10 text-center text-white max-w-sm px-4">
                <img src="{{ asset('images/logo-eskul.png') }}" alt="Logo Ekskul Multimedia" class="w-24 h-24 object-contain mx-auto mb-6 drop-shadow-xl">
                <h1 class="text-4xl font-extrabold mb-2 tracking-tight">SiLat Multimedia</h1>
                <p class="text-xs font-semibold text-blue-300 mb-3 uppercase tracking-widest">SMKN 5 Surakarta</p>
                <p class="text-sm text-slate-300 mb-8">Sistem Peminjaman Alat Multimedia</p>

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
            <div class="w-full max-w-md my-auto py-6">
                
                <div class="mb-6 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-900">Buat Akun</h2>
                    <p class="text-gray-500 mt-1.5 text-sm">Daftar untuk meminjam alat di SiLat Multimedia</p>
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

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Mendaftar Sebagai (Radio Button) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mendaftar Sebagai:</label>
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="peran" value="siswa" id="radio-siswa" checked onchange="toggleForm()"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Siswa</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="peran" value="guru_karyawan" id="radio-guru" onchange="toggleForm()"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Guru / Karyawan</span>
                            </label>
                        </div>
                        @error('peran')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Induk (NIS/NIP) - hanya angka -->
                    <div>
                        <label for="nomor_induk" id="label-nomor-induk" class="block text-sm font-medium text-gray-700 mb-1">Nomor Induk Siswa (NIS)</label>
                        <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required autofocus
                            inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('nomor_induk') border-red-500 @enderror">
                        @error('nomor_induk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Lengkap - hanya huruf, otomatis kapital -->
                    <div>
                        <label for="nama_lengkap" id="label-nama-lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap (Sesuai Presensi)</label>
                        <input id="nama_lengkap" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z\s]/g, '')"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm uppercase @error('nama_lengkap') border-red-500 @enderror">
                        @error('nama_lengkap')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas & Jurusan (khusus Siswa) -->
                    <div id="form-kelas-jurusan">
                        <label for="kelas_jurusan" class="block text-sm font-medium text-gray-700 mb-1">Kelas & Jurusan</label>
                        <select id="kelas_jurusan" name="kelas_jurusan" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition bg-white text-sm @error('kelas_jurusan') border-red-500 @enderror">
                            <option value="" disabled selected>-- Pilih Kelas & Jurusan --</option>
                            <optgroup label="Kelas X">
                                <option value="X DPIB A" {{ old('kelas_jurusan') == 'X DPIB A' ? 'selected' : '' }}>X DPIB A</option>
                                <option value="X DPIB B" {{ old('kelas_jurusan') == 'X DPIB B' ? 'selected' : '' }}>X DPIB B</option>
                                <option value="X PPLG A" {{ old('kelas_jurusan') == 'X PPLG A' ? 'selected' : '' }}>X PPLG A</option>
                            </optgroup>
                            <optgroup label="Kelas XI">
                                <option value="XI DPIB A" {{ old('kelas_jurusan') == 'XI DPIB A' ? 'selected' : '' }}>XI DPIB A</option>
                                <option value="XI DPIB B" {{ old('kelas_jurusan') == 'XI DPIB B' ? 'selected' : '' }}>XI DPIB B</option>
                                <option value="XI PPLG A" {{ old('kelas_jurusan') == 'XI PPLG A' ? 'selected' : '' }}>XI PPLG A</option>
                            </optgroup>
                            <optgroup label="Kelas XII">
                                <option value="XII DPIB A" {{ old('kelas_jurusan') == 'XII DPIB A' ? 'selected' : '' }}>XII DPIB A</option>
                                <option value="XII DPIB B" {{ old('kelas_jurusan') == 'XII DPIB B' ? 'selected' : '' }}>XII DPIB B</option>
                                <option value="XII PPLG A" {{ old('kelas_jurusan') == 'XII PPLG A' ? 'selected' : '' }}>XII PPLG A</option>
                            </optgroup>
                        </select>
                        @error('kelas_jurusan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Grid Sejajar -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Input Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input id="password" type="password" name="password" required
                                    class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('password') border-red-500 @enderror">
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

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Ulangi Password</label>
                            <div class="relative">
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm">
                                <button type="button" onclick="togglePassword('password_confirmation', this)" tabindex="-1"
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

                    <!-- Input PIN -->
                    <div>
                        <label for="pin" class="block text-sm font-medium text-gray-700 mb-0">PIN (6 Angka)</label>
                        <p class="text-red-500 text-xs mb-1.5">*Harap hafalkan PIN ini untuk keperluan Lupa Password</p>
                        <div class="relative">
                            <input id="pin" type="password" name="pin" maxlength="6" inputmode="numeric" pattern="[0-9]*" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm @error('pin') border-red-500 @enderror">
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

                    <!-- Tombol Register -->
                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-lg shadow-blue-600/30 text-sm mt-3">
                        Daftar Sekarang
                    </button>

                    <!-- Link Login -->
                    <p class="text-center text-sm text-gray-600 mt-4">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">Masuk di sini</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        function toggleForm() {
            var isGuru = document.getElementById('radio-guru').checked;
            var formKelas = document.getElementById('form-kelas-jurusan');
            var selectKelas = document.getElementById('kelas_jurusan');
            var labelNomorInduk = document.getElementById('label-nomor-induk');
            var labelNamaLengkap = document.getElementById('label-nama-lengkap');

            if (isGuru) {
                formKelas.style.display = 'none';
                selectKelas.removeAttribute('required');
                labelNomorInduk.innerText = 'Nomor Induk Pegawai (NIP)';
                labelNamaLengkap.innerText = 'Nama Lengkap';
            } else {
                formKelas.style.display = 'block';
                selectKelas.setAttribute('required', 'required');
                labelNomorInduk.innerText = 'Nomor Induk Siswa (NIS)';
                labelNamaLengkap.innerText = 'Nama Lengkap (Sesuai Presensi)';
            }
        }

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

        // Jalankan saat halaman pertama kali dimuat
        toggleForm();
    </script>
</body>
</html>