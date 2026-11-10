<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Cincin fokus keyboard: spesifisitas nol supaya gaya khusus di elemen tetap menang */
        :where(a, button, summary, input, textarea):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }

        @keyframes dropdown-muncul { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes panah-geser { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(-3px); } }
        @keyframes panah-turun { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(3px); } }

        @media (prefers-reduced-motion: no-preference) {
            .dropdown-animasi { animation: dropdown-muncul .16s ease-out; }
        }

        @media (prefers-reduced-motion: no-preference) and (hover: hover) {
            .tombol-kembali:hover svg { animation: panah-geser .6s ease-in-out infinite; }
            .tombol-ke-form:hover svg { animation: panah-turun .6s ease-in-out infinite; }
        }
    </style>

    <!-- Fallback foto rusak (untuk thumbnail dropdown pencarian) -->
    <script>
        function fotoRusak(img) {
            img.onerror = null;
            img.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" fill="#eff6ff"/>' +
                '<path d="M12 15h3l1.5-2h7L25 15h3a1 1 0 011 1v9a1 1 0 01-1 1H12a1 1 0 01-1-1v-9a1 1 0 011-1z" fill="none" stroke="#93c5fd" stroke-width="1.5"/>' +
                '<circle cx="20" cy="20.5" r="3" fill="none" stroke="#93c5fd" stroke-width="1.5"/></svg>'
            );
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    @php
        // TODO: ganti dengan Alat::all() setelah AlatSeeder dibuat (daftar ini sama dengan di welcome.blade.php).
        // 'foto' = awalan nama file di public/images/alat/, lalu ditambah angka 1, 2, 3 dan .jpg
        // (contoh: 'sony' -> sony1.jpg, sony2.jpg, sony3.jpg; 'drone1-' -> drone1-1.jpg, drone1-2.jpg, drone1-3.jpg).
        // 'deskripsi' dan 'kelengkapan' masih DRAFT, cocokkan dengan kondisi alat sebenarnya di ekskul.
        $alatList = [
            [
                'slug' => 'sony-mirrorless', 'foto' => 'sony', 'nama' => 'Sony Mirrorless Camera', 'sub' => 'Sony A6000',
                'deskripsi' => 'Kamera mirrorless ringkas dengan sensor APS-C untuk foto dan video dokumentasi kegiatan sekolah. Ringan dibawa dan mudah dipakai, termasuk oleh pemula.',
                'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera'],
            ],
            [
                'slug' => 'canon-dslr', 'foto' => 'cannon', 'nama' => 'Canon DSLR Camera', 'sub' => 'Canon EOS 1200D',
                'deskripsi' => 'Kamera DSLR tingkat pemula untuk memotret acara, potret, dan dokumentasi. Dilengkapi jendela bidik optik dengan hasil gambar yang tajam.',
                'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera'],
            ],
            [
                'slug' => 'drone-dokumentasi-1', 'foto' => 'drone1-', 'nama' => 'Drone Dokumentasi 1', 'sub' => 'DJI Mini 2',
                'deskripsi' => 'Drone ringan dan ringkas untuk mengambil foto dan video dari udara. Cocok untuk dokumentasi kegiatan di area terbuka.',
                'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan'],
            ],
            [
                'slug' => 'drone-dokumentasi-2', 'foto' => 'drone2-', 'nama' => 'Drone Dokumentasi 2', 'sub' => 'DJI Mavic Air 2',
                'deskripsi' => 'Drone lipat dengan kamera beresolusi tinggi untuk video udara yang stabil. Cocok untuk dokumentasi acara besar dan area yang luas.',
                'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan'],
            ],
            [
                'slug' => 'handycam', 'foto' => 'handycam', 'nama' => 'Handycam', 'sub' => 'Sony HDR-CX405',
                'deskripsi' => 'Kamera video untuk merekam acara dan kegiatan dalam durasi panjang, dengan zoom optik yang praktis digunakan.',
                'kelengkapan' => ['Unit handycam', 'Baterai', 'Charger atau adaptor', 'Kabel data', 'Kartu memori', 'Tas handycam'],
            ],
            [
                'slug' => 'gimbal-stabilizer', 'foto' => 'gimbal', 'nama' => 'Gimbal Stabilizer', 'sub' => 'Rekaman Mulus Saat Bergerak',
                'deskripsi' => 'Penstabil gambar agar rekaman tetap mulus saat berjalan atau bergerak. Cocok untuk video liputan, vlog, dan dokumentasi kegiatan.',
                'kelengkapan' => ['Unit gimbal', 'Baterai', 'Kabel pengisi daya', 'Plat dudukan (quick release)', 'Tas penyimpanan'],
            ],
            [
                'slug' => 'tripod', 'foto' => 'tripod', 'nama' => 'Tripod Stabilizer', 'sub' => 'Penopang Kamera Dan Handphone',
                'deskripsi' => 'Penopang yang stabil untuk kamera dan handphone saat memotret atau merekam video, dengan tinggi yang dapat diatur.',
                'kelengkapan' => ['Unit tripod', 'Dudukan handphone', 'Plat dudukan kamera', 'Tas tripod'],
            ],
            [
                'slug' => 'handphone-dokumentasi', 'foto' => 'ipone', 'nama' => 'Handphone Dokumentasi', 'sub' => 'iPhone 13',
                'deskripsi' => 'Handphone untuk dokumentasi cepat, menghasilkan foto dan video berkualitas dari perangkat yang praktis dibawa ke mana saja.',
                'kelengkapan' => ['Handphone iPhone 13', 'Kabel pengisi daya', 'Adaptor charger', 'Casing pelindung'],
            ],
        ];

        // Alat yang sedang dibuka, diambil dari slug di URL
        $alat = collect($alatList)->firstWhere('slug', request()->route('slug'));
        abort_unless($alat, 404);
        $alat['nama'] = $alatDb->nama_alat;
        $alat['sub'] = $alatDb->deskripsi ?? $alat['sub'];
        $alat['deskripsi'] = $alatDb->deskripsi ?? $alat['deskripsi'];
        $alat['foto'] = $alatDb->foto ?? $alat['foto'];
        $alat['kelengkapan'] = $alatDb->kelengkapan ?? $alat['kelengkapan'];

        $fotoAlat = str_starts_with($alatDb->foto ?? '', 'uploads/')
            ? collect([$alatDb->foto_url, null, null])
            : collect([1, 2, 3])->map(fn ($nomor) => asset('images/alat/' . $alat['foto'] . $nomor . '.jpg'));

        // Data untuk dropdown pencarian (klik alat -> form alat tersebut)
        $daftarPencarian = $alatDaftar->filter(fn ($a) => $a->tersedia)->map(function ($a) {
            return [
                'nama' => $a->nama_alat,
                'sub'  => $a->deskripsi ?? '',
                'foto' => $a->foto_url,
                'url'  => route('peminjaman.create', $a->slug),
            ];
        })->values();

        // Data peminjam (diambil dari akun yang login)
        $peran = Auth::user()->peran ?? 'siswa';
        $namaLengkap = Auth::user()->nama_lengkap ?? 'SISWA';
        $nomorInduk = Auth::user()->nomor_induk ?? '12345';
        $kelasJurusan = Auth::user()->kelas_jurusan ?? '-';
        $fotoPeminjam = Auth::user()->foto_profil ? asset('uploads/profil/' . Auth::user()->foto_profil) : null;
        $inisialPeminjam = mb_strtoupper(mb_substr($namaLengkap, 0, 1, 'UTF-8'), 'UTF-8');

        $labelPeran = [
            'siswa'         => 'Siswa',
            'guru_karyawan' => 'Guru / Karyawan',
            'admin'         => 'Admin',
        ];
        $labelInduk = [
            'siswa'         => 'Nomor Induk Siswa',
            'guru_karyawan' => 'Nomor Induk Pegawai',
        ];
        $tampilKelas = $peran === 'siswa';

        $hariIni = now()->format('Y-m-d');

        // Kelas bersama untuk kolom isian. Border merah muncul otomatis lewat aria-invalid="true"
        // (diatur dari server saat ada error, dan dari JavaScript saat validasi di browser).
        // Saat kursor di atas kolom (dan tidak sedang error): border berubah biru muda.
        $kelasInput = 'w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-sm outline-none transition duration-200 '
            . 'hover:border-blue-400 hover:bg-blue-50/40 hover:shadow-sm focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 '
            . 'aria-[invalid=true]:border-red-500 aria-[invalid=true]:hover:border-red-500 aria-[invalid=true]:focus:border-red-500 aria-[invalid=true]:focus:ring-red-500/20';

        // Aturan peminjaman (10 poin). Cocokkan dengan kebijakan ekskul yang benar-benar berlaku.
        $aturan = [
            ['judul' => 'Persetujuan & Validasi Pengajuan',
             'isi'   => 'Pengajuan peminjaman dinyatakan sah secara operasional hanya setelah mendapatkan persetujuan resmi dari Admin. Peminjam wajib menunjukkan identitas resmi yang valid (Kartu Pelajar/KTP) saat pengambilan alat, mengisi formulir secara transparan dan jujur, serta dibatasi maksimal satu unit alat utama per satu kali pengajuan.'],
            ['judul' => 'Prosedur Serah Terima & Pengecekan Fisik',
             'isi'   => 'Peminjam wajib melakukan pemeriksaan menyeluruh terhadap kondisi fisik, fungsi teknis, dan kelengkapan unit (termasuk baterai, kartu memori, kabel, dan tas pelindung) bersama Admin pada saat pengambilan, serta menandatangani berita acara serah terima.'],
            ['judul' => 'Batas Waktu & Ketentuan Perpanjangan',
             'isi'   => 'Maksimal durasi peminjaman adalah 14 (empat belas) hari kalender sejak tanggal mulai peminjaman dan wajib dikembalikan tepat waktu. Perpanjangan masa pinjam hanya dapat diproses atas izin dan persetujuan Admin saat melakukan konfirmasi pengembalian unit.'],
            ['judul' => 'Batasan Penggunaan Perangkat',
             'isi'   => 'Perangkat hanya diperkenankan untuk digunakan sesuai dengan maksud dan tujuan yang telah disetujui dalam formulir. Dilarang keras memindahtangankan, menyewakan kembali, membongkar komponen, atau mengoperasikan perangkat di area berisiko tinggi (seperti medan ekstrem atau berair) tanpa perlindungan standar.'],
            ['judul' => 'Tanggung Jawab Pemeliharaan & Penyimpanan',
             'isi'   => 'Peminjam wajib merawat kebersihan, keamanan, dan keutuhan perangkat selama masa peminjaman berada di bawah kuasanya, termasuk wajib menggunakan tas pelindung resmi saat melakukan mobilitas di luar ruangan.'],
            ['judul' => 'Risiko Kerusakan & Kehilangan (Ganti Rugi)',
             'isi'   => 'Segala bentuk kerusakan, cacat fisik, atau kehilangan unit maupun aksesori pendukung yang diakibatkan oleh kelalaian peminjam merupakan tanggung jawab penuh peminjam, yang wajib diselesaikan melalui perbaikan profesional atau penggantian unit dengan spesifikasi setara.'],
            ['judul' => 'Prosedur Pengembalian & Privasi Data',
             'isi'   => 'Perangkat wajib dikembalikan dalam kondisi bersih, utuh, dan berfungsi normal. Peminjam diwajibkan untuk melakukan pencadangan (backup) dan membersihkan (wipe/format) seluruh data pribadi atau file kerja dari media penyimpanan perangkat sebelum diserahkan kembali kepada Admin.'],
            ['judul' => 'Sanksi Administratif & Pelanggaran',
             'isi'   => 'Keterlambatan pengembalian mendapat toleransi maksimal 1 (satu) hari kalender setelah batas pengembalian. Mulai hari keterlambatan kedua, dikenakan denda Rp5.000 per hari untuk setiap unit alat sampai alat dikembalikan. Keterlambatan dan pelanggaran lain juga dicatat dalam sistem penalti, yang dapat berakibat pada pembatasan akses peminjaman, pemblokiran akun (blacklisted), hingga penjatuhan sanksi administratif dari pihak sekolah atau lembaga.'],
            ['judul' => 'Hak Prerogatif Pengelola (Admin)',
             'isi'   => 'Tim pengelola atau Admin memiliki hak penuh untuk menolak pengajuan, membatalkan persetujuan yang sedang berjalan, atau menarik kembali unit perangkat secara mendadak demi kepentingan darurat institusi maupun akibat indikasi pelanggaran aturan.'],
            ['judul' => 'Pernyataan Kepatuhan & Integritas',
             'isi'   => 'Dengan memberikan tanda centang atau menyetujui Syarat & Ketentuan ini, peminjam menyatakan secara sadar bahwa telah membaca, memahami, dan mengikatkan diri untuk mematuhi seluruh aturan serta siap menanggung segala bentuk konsekuensi maupun risiko penggunaan perangkat.'],
        ];
        if ($alatDb->aturan) {
            $aturan = array_merge($aturan, collect($alatDb->aturan)->map(fn ($isi, $indeks) => [
                'judul' => 'Aturan khusus ' . ($indeks + 1),
                'isi' => $isi,
            ])->all());
        }
    @endphp

    <!-- Navbar Atas.
         Profil dihapus di halaman ini: ini halaman tugas (mengisi form), bukan halaman utama,
         jadi ruangnya dipakai untuk tombol "Kembali ke Dashboard" yang jelas. -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4 sm:gap-6">
            <!-- Logo & Brand -->
            <a href="{{ url('/dashboard') }}" class="group flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-18 h-18 transition-transform duration-300 motion-safe:group-hover:scale-105">
                <div>
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-xl hidden md:block">
                <div class="relative" id="search-wrapper">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 z-10">
                        <img src="https://img.icons8.com/ios-glyphs/30/4b5563/search--v1.png" alt="Search" class="w-4 h-4">
                    </span>
                    <input type="text" id="search-input" autocomplete="off" placeholder="Cari Alat Multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 hover:border-blue-400 hover:bg-white hover:shadow-md hover:shadow-blue-900/5 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200">

                    <!-- Dropdown Hasil Pencarian -->
                    <div id="search-dropdown" class="dropdown-animasi hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 max-h-80 overflow-y-auto z-50">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Tombol kembali (satu-satunya aksi di kanan) -->
            <a href="{{ url('/dashboard') }}" aria-label="Kembali ke Dashboard"
               class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Kembali ke Dashboard
            </a>
        </div>
    </header>

    <!-- Konten Form Peminjaman -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">

        <div class="mb-8 flex items-start gap-4">
            <span class="hidden sm:flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/25 transition-all duration-300 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/40 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 transition-colors duration-300 hover:text-blue-700">Form Peminjaman Alat</h1>
                <p class="text-gray-500 text-sm mt-1.5">Lengkapi data di bawah ini untuk mengajukan peminjaman. Pengajuan akan diverifikasi oleh admin.</p>
            </div>
        </div>

        @if($errors->any())
            <div role="alert" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- BAGIAN ATAS: satu kartu, info alat (kiri) + galeri 3 foto (kanan).
             Tinggi kedua kolom selalu sama: foto besar memanjang mengikuti tinggi kolom kiri.
             Di HP urutannya galeri dulu, lalu info. -->
        <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
            <div class="grid lg:grid-cols-5">

                <!-- Info Alat (kiri) -->
                <div class="order-2 lg:order-1 lg:col-span-2 p-6 sm:p-8 flex flex-col border-t border-gray-100 lg:border-t-0 lg:border-r">

                    <!-- Nama alat (titik fokus) -->
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight transition-colors duration-300 hover:text-blue-700">{{ $alat['nama'] }}</h2>
                    <p class="text-gray-500 mt-1">{{ $alat['sub'] }}</p>

                    <!-- Status -->
                    <div class="mt-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1 transition-all duration-200 hover:bg-emerald-600 hover:text-white hover:shadow-md hover:shadow-emerald-600/30 motion-safe:hover:scale-110">
                            <span class="relative flex w-1.5 h-1.5">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 motion-safe:animate-ping"></span>
                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            </span>
                            Tersedia
                        </span>
                    </div>

                    <!-- Deskripsi -->
                    <div class="group border-t border-gray-100 mt-6 pt-6">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide transition-colors duration-200 group-hover:text-blue-600">Deskripsi Alat</p>
                        <p class="text-sm text-gray-600 leading-relaxed mt-2 transition-colors duration-200 group-hover:text-gray-900">{{ $alat['deskripsi'] }}</p>
                    </div>

                    <!-- Kelengkapan -->
                    <div class="group/kel border-t border-gray-100 mt-6 pt-6">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide transition-colors duration-200 group-hover/kel:text-blue-600">Kelengkapan Alat</p>
                        <p class="text-sm text-gray-500 mt-1 mb-3">Yang kamu dapatkan saat meminjam alat ini:</p>

                        <ul class="grid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-2">
                            @foreach ($alat['kelengkapan'] as $item)
                                <li class="group flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-100 px-3 py-2 text-sm text-gray-700 transition-all duration-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md hover:shadow-blue-600/25 motion-safe:hover:translate-x-1.5 motion-safe:hover:scale-[1.03]">
                                    <svg class="w-4 h-4 shrink-0 text-blue-600 transition-all duration-200 group-hover:text-white motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="group mt-4 flex items-start gap-2 text-xs text-gray-600 transition-colors duration-200 hover:text-gray-900">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Kelengkapan diperiksa bersama admin saat pengambilan dan pengembalian alat.</span>
                        </p>
                    </div>

                    <!-- Tombol menuju form (menempel di dasar kolom) -->
                    <a href="#form-peminjaman" class="tombol-ke-form mt-auto pt-8 block focus:outline-none">
                        <span class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 rounded-xl transition-all duration-200 shadow-sm hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30 active:scale-[0.98]">
                            Isi Form Peminjaman
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </span>
                    </a>
                </div>

                <!-- Galeri Foto (kanan): satu foto besar (angka 1) + dua foto kecil (angka 2 dan 3) -->
                <div class="order-1 lg:order-2 lg:col-span-3 p-5 sm:p-6 bg-gray-50/70 flex flex-col gap-3">

                    <!-- Foto besar: di layar lebar memanjang mengisi sisa tinggi kolom -->
                    <div class="group relative aspect-[4/3] lg:aspect-auto lg:flex-1 lg:min-h-[18rem] bg-white border border-gray-200/80 rounded-xl overflow-hidden transition-all duration-300 hover:border-blue-400 hover:shadow-xl hover:shadow-blue-900/15 hover:ring-2 hover:ring-blue-500/20 motion-safe:hover:-translate-y-1">
                        <img id="foto-utama" src="" alt="{{ $alat['nama'] }}" class="hidden absolute inset-0 w-full h-full object-contain p-3 transition-transform duration-500 ease-out motion-safe:group-hover:scale-110">

                        <!-- Tampil kalau foto besar belum ada -->
                        <div id="foto-kosong" class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300">
                            <svg class="w-16 h-16 transition-transform duration-500 motion-safe:group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>
                            </svg>
                            <span class="text-xs font-medium text-blue-400">Foto belum tersedia</span>
                        </div>
                    </div>

                    <!-- Dua foto kecil (klik untuk menukar dengan foto besar) -->
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ([1, 2] as $i)
                            <button type="button" data-thumb="{{ $i }}" disabled
                                    aria-label="Tampilkan foto pendukung {{ $i }} sebagai foto besar"
                                    class="group relative aspect-[16/10] bg-white border border-gray-200/80 rounded-xl overflow-hidden transition-all duration-200 enabled:cursor-pointer enabled:hover:border-blue-400 enabled:hover:shadow-md enabled:hover:ring-2 enabled:hover:ring-blue-500/20 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-default">
                                <img src="" alt="" class="hidden absolute inset-0 w-full h-full object-contain p-2 transition-transform duration-300 motion-safe:group-enabled:group-hover:scale-105">
                                <span data-kosong class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300">
                                    <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>
                                    </svg>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <p class="group flex items-start gap-2 text-xs text-gray-600 transition-colors duration-200 hover:text-gray-900">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Foto hanya sebagai ilustrasi. Tampilan dan kondisi fisik alat dapat berbeda dari gambar.</span>
                    </p>
                </div>
            </div>
        </section>

        <!-- BAGIAN BAWAH: Form + Aturan (satu <form>, selebar halaman) -->
        <form id="form-peminjaman" method="POST" action="{{ route('peminjaman.store') }}" class="mt-8 space-y-8 scroll-mt-24">
            @csrf
            <input type="hidden" name="alat_id" value="{{ $alatDb->id }}">

            <!-- Kartu Form Pengajuan -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 sm:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">

                <!-- Info Kebijakan (selebar kartu) -->
                <div class="group flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 mb-8 transition-colors duration-200 hover:border-blue-300 hover:bg-blue-100/70">
                    <span class="w-8 h-8 flex items-center justify-center text-blue-600 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11v5m0-8h.01"/>
                        </svg>
                    </span>
                    <p class="text-sm text-blue-900">Peminjaman maksimal <span class="font-bold">14 hari</span> sejak tanggal mulai, dan akan diverifikasi admin sebelum alat bisa diambil.</p>
                </div>

                <!-- Data Peminjam (otomatis dari akun, tidak bisa diubah) -->
                <div class="pb-8 border-b border-gray-100">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Data Peminjam</h2>
                            <p class="text-xs text-gray-500">Terisi otomatis dari akunmu dan tidak dapat diubah di sini.</p>
                        </div>
                    </div>

                    <!-- Kartu ringkasan (hanya tampilan, bukan kolom isian) -->
                    <div class="group rounded-2xl bg-gray-50 border border-gray-200/80 p-5 sm:p-6 transition-all duration-300 hover:border-blue-200 hover:bg-blue-50/40">
                        <div class="flex items-center gap-4">
                            @if ($fotoPeminjam)
                                <img src="{{ $fotoPeminjam }}" alt="Foto profil" class="w-14 h-14 rounded-full object-cover shrink-0 ring-2 ring-blue-100 transition-transform duration-300 motion-safe:group-hover:scale-105">
                            @else
                                <div class="w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center font-extrabold text-2xl leading-none shrink-0 ring-2 ring-blue-100 transition-transform duration-300 motion-safe:group-hover:scale-105">
                                    {{ $inisialPeminjam }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="text-lg font-bold text-gray-900 truncate">{{ $namaLengkap }}</p>
                                <span class="inline-block mt-1 bg-blue-50 group-hover:bg-white text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full transition-colors duration-300">
                                    {{ $labelPeran[$peran] ?? ucfirst($peran) }}
                                </span>
                            </div>

                            <svg class="w-5 h-5 text-gray-400 shrink-0 transition-colors duration-300 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="Terisi otomatis dan tidak dapat diubah">
                                <title>Terisi otomatis dan tidak dapat diubah</title>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>

                        <dl class="mt-5 pt-5 border-t border-gray-200 grid sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="group/sel rounded-lg p-2 -m-2 transition-all duration-200 hover:bg-white hover:shadow-sm">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 transition-colors duration-200 group-hover/sel:text-blue-600">{{ $labelInduk[$peran] ?? 'Nomor Induk' }}</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 transition-colors duration-200 group-hover/sel:text-blue-700">{{ $nomorInduk }}</dd>
                            </div>

                            @if ($tampilKelas)
                                <div class="group/sel rounded-lg p-2 -m-2 transition-all duration-200 hover:bg-white hover:shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 transition-colors duration-200 group-hover/sel:text-blue-600">Kelas &amp; Jurusan</dt>
                                    <dd class="mt-1 text-base font-semibold text-gray-900 transition-colors duration-200 group-hover/sel:text-blue-700">{{ $kelasJurusan }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Detail Peminjaman -->
                <div class="pt-8">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Detail Peminjaman</h2>
                            <p class="text-xs text-gray-500">Tentukan lama peminjaman dan jelaskan keperluanmu.</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <!-- Tanggal Mulai & Kembali -->
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="group/f">
                                <label for="tanggal_pinjam" class="block text-sm font-medium text-gray-700 mb-1 transition-colors duration-200 group-hover/f:text-blue-700">
                                    Tanggal Mulai Meminjam <span class="text-red-500">*</span>
                                </label>
                                <input id="tanggal_pinjam" type="date" name="tanggal_pinjam" required
                                    min="{{ $hariIni }}" value="{{ old('tanggal_pinjam') }}"
                                    aria-invalid="{{ $errors->has('tanggal_pinjam') ? 'true' : 'false' }}"
                                    class="{{ $kelasInput }} cursor-pointer">
                                <p id="info-mulai" class="text-xs text-gray-400 mt-1">Pilih tanggal mulai peminjaman</p>
                            </div>

                            <div class="group/f">
                                <label for="tanggal_kembali" class="block text-sm font-medium text-gray-700 mb-1 transition-colors duration-200 group-hover/f:text-blue-700">
                                    Tanggal Kembali <span class="text-red-500">*</span>
                                </label>
                                <input id="tanggal_kembali" type="date" name="tanggal_kembali" required disabled
                                    value="{{ old('tanggal_kembali') }}"
                                    aria-invalid="{{ $errors->has('tanggal_kembali') ? 'true' : 'false' }}"
                                    class="{{ $kelasInput }} enabled:cursor-pointer disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed disabled:hover:border-gray-300 disabled:hover:bg-gray-50 disabled:hover:shadow-none">
                                <p id="info-kembali" class="text-xs text-gray-400 mt-1">Pilih tanggal mulai terlebih dahulu</p>
                            </div>
                        </div>

                        <!-- Keperluan (wajib, 10 sampai 250 karakter) -->
                        <div class="group/f">
                            <label for="keperluan" class="block text-sm font-medium text-gray-700 mb-1 transition-colors duration-200 group-hover/f:text-blue-700">
                                Keperluan Peminjaman <span class="text-red-500">*</span>
                            </label>
                            <textarea id="keperluan" name="keperluan" rows="3" minlength="10" maxlength="250" required
                                placeholder="Contoh: Dokumentasi Masa Pengenalan Lingkungan Sekolah (MPLS)."
                                aria-invalid="{{ $errors->has('keperluan') ? 'true' : 'false' }}"
                                class="{{ $kelasInput }} min-h-[7rem] resize-none">{{ old('keperluan') }}</textarea>
                            <div class="mt-1.5 flex items-start justify-between gap-3">
                                <p class="group flex items-start gap-2 text-xs text-gray-600 transition-colors duration-200 hover:text-gray-900">
                                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Membantu admin memahami konteks pengajuanmu (minimal 10 karakter).</span>
                                </p>
                                <p class="text-xs text-gray-500 shrink-0 tabular-nums"><span id="hitung-keperluan">0</span>/250 karakter</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Aturan Peminjaman + persetujuan + tombol kirim (di footer kartu) -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Aturan Peminjaman yang Harus Disepakati</h2>
                            <p class="text-xs text-gray-500">Harap dibaca dengan teliti sebelum mengajukan peminjaman.</p>
                        </div>
                    </div>

                    <ol class="lg:columns-2 lg:gap-x-12">
                        @foreach ($aturan as $r)
                            <li class="group break-inside-avoid mb-3 flex items-start gap-3 rounded-xl p-3 -mx-3 transition-all duration-200 hover:bg-blue-50 hover:shadow-md hover:shadow-blue-900/5 hover:ring-1 hover:ring-blue-200 motion-safe:hover:translate-x-1">
                                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 text-xs font-bold flex items-center justify-center shrink-0 transition-all duration-200 group-hover:bg-blue-600 group-hover:text-white motion-safe:group-hover:scale-110">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $r['judul'] }}</p>
                                    <p class="text-sm text-gray-600 leading-relaxed mt-0.5 transition-colors duration-200 group-hover:text-gray-900">{{ $r['isi'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <!-- Checkbox Persetujuan -->
                    <div class="mt-4 flex items-start gap-2.5 bg-gray-50 rounded-xl border border-transparent px-4 py-3.5 transition-colors duration-200 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md hover:shadow-blue-900/5 has-[:checked]:border-blue-300 has-[:checked]:bg-blue-50">
                        <input id="persetujuan" type="checkbox" name="persetujuan" value="1" required
                            class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4 shrink-0 cursor-pointer">
                        <label for="persetujuan" class="text-sm text-gray-700 leading-relaxed cursor-pointer">
                            Saya telah membaca dan menyetujui seluruh aturan peminjaman di atas, serta bersedia bertanggung jawab atas alat yang saya pinjam.
                        </label>
                    </div>
                </div>

                <!-- Footer kartu: tombol aksi -->
                <div class="border-t border-gray-100 bg-gray-50/70 px-6 sm:px-8 py-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ url('/dashboard') }}"
                       class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md active:scale-[0.98]">
                        Batal
                    </a>
                    <button type="submit" id="btn-kirim"
                        class="w-full sm:w-auto sm:px-10 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold shadow-sm transition-all duration-200 enabled:cursor-pointer enabled:hover:bg-blue-700 enabled:hover:shadow-lg enabled:hover:shadow-blue-600/30 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98] disabled:bg-gray-300 disabled:text-gray-500 disabled:shadow-none disabled:cursor-not-allowed">
                        Ajukan Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </main>

    <!-- Footer ringkas: halaman ini halaman tugas, jadi cukup satu baris -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-600 [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
            <p>Ada kendala saat mengisi form?<span class="mt-0.5 block font-bold text-blue-600">Temui admin Ekskul Multimedia secara langsung.</span></p>
            <p>&copy; {{ date('Y') }} SiLat Multimedia &middot; Mulmedska SMK Negeri 5 Surakarta</p>
        </div>
    </footer>

    <!-- Script -->
    <script>
        // ===== Link anchor tanpa # di bilah alamat =====
        document.addEventListener('click', function (event) {
            var link = event.target.closest('a[href^="#"]');
            if (!link) return;

            var target = document.getElementById(link.getAttribute('href').slice(1));
            if (!target) return;

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', window.location.pathname + window.location.search);
        });

        // ===== Bayangan navbar muncul saat halaman digulir =====
        (function () {
            var navbar = document.getElementById('navbar');
            function periksa() { navbar.classList.toggle('shadow-md', window.scrollY > 8); }
            window.addEventListener('scroll', periksa, { passive: true });
            periksa();
        })();

        // ===== Galeri: foto 1 besar, foto 2 dan 3 kecil (klik foto kecil = tukar dengan foto besar) =====
        (function () {
            const daftar = @json($fotoAlat);
            const namaAlat = @json($alat['nama']);

            const utama = document.getElementById('foto-utama');
            const kosong = document.getElementById('foto-kosong');
            const slotThumb = Array.prototype.slice.call(document.querySelectorAll('[data-thumb]'));

            // susunan[0] = foto besar, susunan[1] dan [2] = foto kecil (null = file fotonya belum ada)
            const susunan = [null, null, null];

            function tampil() {
                if (susunan[0]) {
                    utama.src = susunan[0];
                    utama.alt = namaAlat;
                    utama.classList.remove('hidden');
                    kosong.classList.add('hidden');
                } else {
                    utama.classList.add('hidden');
                    kosong.classList.remove('hidden');
                }

                slotThumb.forEach(function (tombol, i) {
                    const url = susunan[i + 1];
                    const gambar = tombol.querySelector('img');
                    const pengganti = tombol.querySelector('[data-kosong]');

                    if (url) {
                        gambar.src = url;
                        gambar.classList.remove('hidden');
                        pengganti.classList.add('hidden');
                        tombol.disabled = false;
                    } else {
                        gambar.classList.add('hidden');
                        pengganti.classList.remove('hidden');
                        tombol.disabled = true;
                    }
                });
            }

            slotThumb.forEach(function (tombol, i) {
                tombol.addEventListener('click', function () {
                    const sementara = susunan[0];
                    susunan[0] = susunan[i + 1];
                    susunan[i + 1] = sementara;
                    tampil();
                });
            });

            // Cek foto mana yang benar-benar ada. Posisinya tetap: angka 1 besar, angka 2 dan 3 kecil.
            let selesai = 0;

            function catat() {
                selesai++;
                if (selesai === daftar.length) tampil();
            }

            daftar.forEach(function (url, i) {
                if (!url) {
                    catat();
                    return;
                }

                const gambar = new Image();
                gambar.onload = function () { susunan[i] = url; catat(); };
                gambar.onerror = function () { catat(); };
                gambar.src = url;
            });
        })();

        // ===== Search Dropdown (klik alat -> form alat tersebut) =====
        const daftarAlat = @json($daftarPencarian);

        const searchInput = document.getElementById('search-input');
        const searchDropdown = document.getElementById('search-dropdown');

        // Membungkus bagian teks yang cocok dengan ketikan ke dalam <span> biru
        function escapeHtml(teks) {
            return String(teks).replace(/[&<>"']/g, function (karakter) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[karakter];
            });
        }

        function highlightMatch(teks, kata) {
            if (!kata) return escapeHtml(teks);
            const index = teks.toLowerCase().indexOf(kata.toLowerCase());
            if (index === -1) return escapeHtml(teks);

            return escapeHtml(teks.slice(0, index)) +
                '<span class="text-blue-600 font-bold">' + escapeHtml(teks.slice(index, index + kata.length)) + '</span>' +
                escapeHtml(teks.slice(index + kata.length));
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
                return '<a href="' + escapeHtml(alat.url) + '" class="group w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 transition-colors">' +
                    '<img src="' + escapeHtml(alat.foto) + '" alt="" onerror="fotoRusak(this)" class="w-10 h-10 rounded-lg object-contain shrink-0 bg-blue-50 transition-transform duration-200 group-hover:scale-105">' +
                    '<span class="flex-1 min-w-0">' +
                        '<span class="block text-sm font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">' + highlightMatch(alat.nama, kata) + '</span>' +
                        '<span class="block text-xs text-gray-400 truncate">' + highlightMatch(alat.sub, kata) + '</span>' +
                    '</span>' +
                    '</a>';
            }).join('');

            searchDropdown.classList.remove('hidden');
        }

        searchInput.addEventListener('focus', function () {
            renderSearchDropdown(daftarAlat, searchInput.value);
        });

        searchInput.addEventListener('input', function () {
            const kata = searchInput.value.trim();
            const hasil = daftarAlat.filter(function (alat) {
                return (alat.nama + ' ' + alat.sub).toLowerCase().includes(kata.toLowerCase());
            });
            renderSearchDropdown(hasil, kata);
        });

        document.addEventListener('click', function (event) {
            var wrapper = document.getElementById('search-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                searchDropdown.classList.add('hidden');
            }
        });

        // ===== Tanggal: kembali maksimal 14 hari dari tanggal mulai =====
        (function () {
            const mulai = document.getElementById('tanggal_pinjam');
            const kembali = document.getElementById('tanggal_kembali');
            const infoMulai = document.getElementById('info-mulai');
            const infoKembali = document.getElementById('info-kembali');

            const teksMulaiAwal = infoMulai.textContent;
            const teksKembaliAwal = infoKembali.textContent;
            const teksKembaliSiap = 'Maksimal 14 hari dari tanggal mulai';

            // Tampilan pilihan dalam bahasa Indonesia, supaya tidak ambigu di browser dengan bahasa lain
            const format = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            function keDate(nilai) {
                const p = nilai.split('-').map(Number);
                return new Date(p[0], p[1] - 1, p[2]);
            }

            function keIso(d) {
                return d.getFullYear() + '-' +
                    String(d.getMonth() + 1).padStart(2, '0') + '-' +
                    String(d.getDate()).padStart(2, '0');
            }

            function aturTanggal(dariPengguna) {
                // Batas tanggal berubah: pesan kesalahan lama pada tanggal kembali tidak berlaku lagi
                kembali.setCustomValidity('');
                if (dariPengguna) kembali.setAttribute('aria-invalid', 'false');

                if (!mulai.value) {
                    kembali.value = '';
                    kembali.disabled = true;
                    kembali.removeAttribute('min');
                    kembali.removeAttribute('max');
                    infoMulai.textContent = teksMulaiAwal;
                    infoKembali.textContent = teksKembaliAwal;
                    return;
                }

                const awal = keDate(mulai.value);
                const akhir = new Date(awal.getFullYear(), awal.getMonth(), awal.getDate() + 14);

                kembali.disabled = false;
                kembali.min = keIso(awal);
                kembali.max = keIso(akhir);

                if (kembali.value && (kembali.value < kembali.min || kembali.value > kembali.max)) {
                    kembali.value = '';
                }

                infoMulai.textContent = format.format(awal);

                if (kembali.value) {
                    const k = keDate(kembali.value);
                    const selisih = Math.round((k - awal) / 86400000);
                    infoKembali.textContent = format.format(k) + ' \u00b7 ' + (selisih === 0 ? 'hari yang sama' : selisih + ' hari');
                } else {
                    infoKembali.textContent = teksKembaliSiap;
                }
            }

            mulai.addEventListener('change', function () { aturTanggal(true); });
            kembali.addEventListener('change', function () { aturTanggal(true); });
            aturTanggal(false);
        })();

        // ===== Penghitung karakter keperluan =====
        (function () {
            const keperluan = document.getElementById('keperluan');
            const hitung = document.getElementById('hitung-keperluan');

            function perbarui() { hitung.textContent = keperluan.value.length; }

            keperluan.addEventListener('input', perbarui);
            perbarui();
        })();

        // ===== Validasi bahasa Indonesia, penanda error merah, dan tombol kirim =====
        (function () {
            const form = document.getElementById('form-peminjaman');
            const tombol = document.getElementById('btn-kirim');
            const setuju = document.getElementById('persetujuan');
            const keperluan = document.getElementById('keperluan');
            const pesanKeperluanPendek = 'Jelaskan keperluan minimal 10 karakter.';

            const pesan = {
                tanggal_pinjam: {
                    valueMissing: 'Tanggal mulai meminjam wajib diisi.',
                    rangeUnderflow: 'Tanggal mulai tidak boleh sebelum hari ini.',
                    badInput: 'Tanggal tidak valid.'
                },
                tanggal_kembali: {
                    valueMissing: 'Tanggal kembali wajib diisi.',
                    rangeUnderflow: 'Tanggal kembali tidak boleh sebelum tanggal mulai.',
                    rangeOverflow: 'Durasi peminjaman melebihi batas maksimal 14 hari.',
                    badInput: 'Tanggal tidak valid.'
                },
                keperluan: {
                    valueMissing: 'Keperluan peminjaman wajib diisi.',
                    tooShort: pesanKeperluanPendek
                },
                persetujuan: {
                    valueMissing: 'Kamu harus menyetujui aturan peminjaman terlebih dahulu.'
                }
            };

            // Spasi saja tidak dihitung sebagai keperluan
            function cekKeperluan() {
                const nilai = keperluan.value;
                keperluan.setCustomValidity(nilai.length > 0 && nilai.trim().length < 10 ? pesanKeperluanPendek : '');
            }

            form.querySelectorAll('[name]').forEach(function (el) {
                const peta = pesan[el.name];
                if (!peta) return;

                el.addEventListener('invalid', function () {
                    el.setAttribute('aria-invalid', 'true');
                    if (el.validity.customError) return; // pesan sudah diatur

                    for (const kunci in peta) {
                        if (el.validity[kunci]) {
                            el.setCustomValidity(peta[kunci]);
                            break;
                        }
                    }
                });

                function bersihkan() {
                    el.setCustomValidity('');
                    el.setAttribute('aria-invalid', 'false');
                    if (el === keperluan) cekKeperluan();
                }

                el.addEventListener('input', bersihkan);
                el.addEventListener('change', bersihkan);
            });

            cekKeperluan();

            // Tombol kirim hanya aktif setelah aturan disetujui
            function sinkronTombol() { tombol.disabled = !setuju.checked; }
            setuju.addEventListener('change', sinkronTombol);
            sinkronTombol();

            // Setelah dikirim: tombol tetap biru, teks berubah, klik ganda diblokir
            let terkirim = false;
            form.addEventListener('submit', function (event) {
                if (terkirim) {
                    event.preventDefault();
                    return;
                }
                terkirim = true;
                tombol.textContent = 'Mengirim...';
                tombol.classList.add('opacity-70', 'pointer-events-none');
            });

            // Kalau pengguna menekan tombol Kembali di browser, tombol dipulihkan
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    terkirim = false;
                    tombol.textContent = 'Ajukan Peminjaman';
                    tombol.classList.remove('opacity-70', 'pointer-events-none');
                    sinkronTombol();
                }
            });
        })();
    </script>
</body>
</html>