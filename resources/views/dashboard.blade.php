<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :where(a, button, summary, input):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }

        @keyframes dropdown-muncul { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes footer-goyang { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(-10deg); } 75% { transform: rotate(10deg); } }
        @keyframes panah-maju { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(4px); } }

        @media (prefers-reduced-motion: no-preference) {
            .dropdown-animasi { animation: dropdown-muncul .16s ease-out; }
        }

        /* Animasi ikon memakai :hover, jadi hanya dijalankan di perangkat yang benar-benar punya kursor.
           Di layar sentuh :hover "menempel" setelah diketuk, sehingga ikon akan terus bergerak. */
        @media (prefers-reduced-motion: no-preference) and (hover: hover) {
            .footer-link:hover svg { animation: footer-goyang .4s ease-in-out; }
            .tautan-panah:hover svg { animation: panah-maju .6s ease-in-out infinite; }
        }
    </style>

    <!-- Fallback foto rusak (untuk thumbnail dropdown pencarian dan tabel) -->
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
    use Carbon\Carbon;

    $alatList = $alatDaftar->map(function ($alat) {
        $slug = $alat->slug;
        $ikon = match (true) {
            str_contains($slug, 'drone') => 'drone',
            str_contains($slug, 'handycam') => 'video',
            str_contains($slug, 'gimbal') => 'gimbal',
            str_contains($slug, 'tripod') => 'tripod',
            str_contains($slug, 'handphone') => 'hp',
            default => 'kamera',
        };

        return [
            'slug' => $slug,
            'foto_url' => $alat->foto_url,
            'nama' => $alat->nama_alat,
            'sub' => $alat->subjudul,
            'ikon' => $ikon,
            'tersedia' => $alat->tersedia,
            'status_ketersediaan' => $alat->status_ketersediaan,
        ];
    });

    // Dipakai tabel "Peminjaman Saya" untuk mencari alat dari slug
    $katalog = collect($alatList)->keyBy('slug');

    $ikonAlat = [
        'kamera' => '<path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>',
        'video'  => '<path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'hp'     => '<path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'drone'  => '<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="5" cy="18" r="2.2"/><circle cx="19" cy="18" r="2.2"/><path d="M7 7.5l3 3m7-3l-3 3M7 16.5l3-3m7 3l-3-3"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.5"/>',
        'gimbal' => '<rect x="7" y="4" width="10" height="5" rx="1.5"/><path d="M4 6.5h3m10 0h3M12 9v4"/><rect x="9.5" y="13" width="5" height="8" rx="2"/>',
        'tripod' => '<rect x="8.5" y="2.5" width="7" height="4" rx="1"/><path d="M12 6.5V11m0 0l-5 10m5-10v10m0-10l5 10"/>',
    ];

    // Data untuk dropdown pencarian di navbar
    $daftarPencarian = collect($alatList)->map(function ($a) {
        return [
            'id'   => 'alat-' . $a['slug'],
            'nama' => $a['nama'],
            'sub'  => $a['sub'],
            'foto' => $a['foto_url'],
        ];
    })->values();

    // Hari ini (dasar untuk menghitung sisa hari dan keterlambatan)
    $hariIni = Carbon::now()->startOfDay();

    // Tampilan tiap status (warna paten): Menunggu abu-abu, Dipinjam biru, Selesai hijau, Ditolak kuning, Terlambat merah.
    // 'terlambat' bukan nilai enum: status turunan dari "dipinjam" yang lewat batas pengembalian.
    $statusMap = [
        'menunggu_persetujuan' => [
            'label' => 'Menunggu Persetujuan',
            'badge' => 'bg-gray-100 text-gray-700',
            'dot'   => 'bg-gray-500',
            'ikon'  => 'bg-gray-200 text-gray-600',
            'path'  => 'M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'menunggu_pengambilan' => [
            'label' => 'Menunggu Pengambilan',
            'badge' => 'bg-amber-100 text-amber-800',
            'dot'   => 'bg-amber-500',
            'ikon'  => 'bg-amber-100 text-amber-700',
            'path'  => 'M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'dipinjam' => [
            'label' => 'Sedang Dipinjam',
            'badge' => 'bg-blue-100 text-blue-700',
            'dot'   => 'bg-blue-500',
            'ikon'  => 'bg-blue-100 text-blue-600',
            'path'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'badge' => 'bg-green-100 text-green-700',
            'dot'   => 'bg-green-500',
            'ikon'  => 'bg-green-100 text-green-600',
            'path'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'badge' => 'bg-red-100 text-red-700',
            'dot'   => 'bg-red-500',
            'ikon'  => 'bg-red-100 text-red-600',
            'path'  => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    // Kartu ringkasan sekaligus tombol filter (urutan tampil).
    // 'ikon'  = warna kotak dan ikon saat kartu belum dipilih: kotak pastel (-100) dengan ikon sewarna yang lebih tua (-600),
    //           pola yang sama dengan kartu status lain.
    // 'aktif' = warna ikon saat kartu terpilih (latar biru): versi terang dari warna ikon aslinya (-300) supaya seragam dan konsisten.
    $kartuFilter = [
        'semua' => [
            'label' => 'Pengajuan Terbaru',
            'ikon'  => 'bg-amber-100 text-amber-600',
            'aktif' => 'group-aria-[pressed=true]:text-amber-300',
            'path'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        ],
    ];
    foreach (['menunggu_persetujuan', 'dipinjam', 'selesai', 'ditolak'] as $kode) {
        $kartuFilter[$kode] = $statusMap[$kode];
    }
    $kartuFilter['menunggu_persetujuan']['aktif'] = 'group-aria-[pressed=true]:text-gray-200';
    $kartuFilter['dipinjam']['aktif']             = 'group-aria-[pressed=true]:text-sky-300';
    $kartuFilter['selesai']['aktif']              = 'group-aria-[pressed=true]:text-green-300';
    $kartuFilter['ditolak']['ikon']               = 'bg-red-100 text-red-600';
    $kartuFilter['ditolak']['aktif']              = 'group-aria-[pressed=true]:text-red-300';

    // Satu kali olah data untuk tabel (layar lebar) dan kartu (layar kecil).
    $baris = $peminjaman->map(function ($p) use ($katalog, $statusMap, $hariIni) {
        $slug = $p->alat->slug;
        $alatData = $katalog->get($slug, [
            'slug' => $slug,
            'foto_url' => $p->alat->foto_url,
            'nama' => $p->alat->nama_alat,
            'sub' => $p->alat->deskripsi ?? '',
            'ikon' => 'kamera',
        ]);
        $pinjam    = Carbon::parse($p->tanggal_pinjam)->startOfDay();
        $kembali   = Carbon::parse($p->tanggal_kembali)->startOfDay();
        $tanggalDikembalikan = $p->tanggal_dikembalikan?->locale('id')->translatedFormat('j M Y');
        $sisa      = (int) $hariIni->diffInDays($kembali, false);
        $terlambat = $p->status === 'dipinjam' && $sisa < 0;
        $pengembalian = match (true) {
            $terlambat => 'Terlambat ' . abs($sisa) . ' hari',
            $p->status === 'dipinjam' => 'Belum dikembalikan',
            $p->status === 'selesai' => $tanggalDikembalikan
                ? 'Dikembalikan pada ' . $tanggalDikembalikan
                : 'Sudah dikembalikan',
            default => '-',
        };
        // Warna teks kolom Pengembalian mengikuti warna status: Terlambat merah, Selesai hijau
        $warnaPengembalian = match (true) {
            $terlambat => 'font-semibold text-red-700',
            $p->status === 'selesai' => 'font-semibold text-green-700',
            $p->status === 'dipinjam' => 'text-gray-600',
            default => 'text-gray-400',
        };

        // Keterangan singkat di kolom paling kanan. Semua berupa teks biasa dengan warna yang sama.
        $ket = match (true) {
            $p->status === 'menunggu_persetujuan'      => 'Menunggu keputusan admin',
            $p->status === 'menunggu_pengambilan'       => $p->catatan_pengambilan ?: $p->catatan_admin ?: 'Menunggu pengambilan alat',
            $terlambat                                  => 'Terlambat ' . abs($sisa) . ' hari, segera kembalikan',
            $p->status === 'dipinjam' && $sisa === 0    => 'Kembalikan hari ini',
            $p->status === 'dipinjam'                   => 'Kembali ' . $sisa . ' hari lagi',
            $p->status === 'selesai'                    => $p->kondisi_alat_kembali ? 'Dikembalikan, kondisi ' . mb_strtolower($p->kondisi_alat_kembali) : 'Sudah dikembalikan',
            default                                     => $p->catatan_admin ?: 'Pengajuan tidak disetujui',
        };

        return [
            'status'         => $p->status,
            'alat_data'      => $alatData,
            'tampil'         => $statusMap[$p->status],
            'pinjam_f'       => $pinjam->locale('id')->translatedFormat('j M Y'),
            'kembali_f'      => $kembali->locale('id')->translatedFormat('j M Y'),
            'pengembalian'   => $pengembalian,
            'pengembalian_warna' => $warnaPengembalian,
            'pinjam_ts'      => $pinjam->timestamp,   // dipakai untuk mengurutkan berdasarkan tanggal
            'terlambat'      => $terlambat,
            'ket_teks'       => $ket,
            'detail_url'     => route('peminjaman.show', $p),
        ];
    });

    $jumlah          = $baris->countBy('status');
    $jumlahTerlambat = $baris->where('terlambat', true)->count();
    $kosong          = $baris->isEmpty();

    // Langkah singkat untuk tampilan "belum ada peminjaman" (garis waktu di panel kanan)
    $langkahKosong = [
        ['judul' => 'Pilih alat dari katalog',           'isi' => 'Lihat alat yang berstatus Tersedia.'],
        ['judul' => 'Isi tanggal dan keperluan',          'isi' => 'Maksimal 14 hari, satu form untuk satu alat.'],
        ['judul' => 'Pantau persetujuan admin',           'isi' => 'Statusnya muncul di sini setelah dikirim.'],
        ['judul' => 'Ambil dan kembalikan alat tepat waktu', 'isi' => 'Kondisi alat dicatat admin saat pengembalian.'],
    ];

    // Cara Pinjam (versi untuk pengguna yang sudah masuk, jadi langkah "Login / Daftar" tidak diperlukan lagi)
    $langkah = [
        ['judul' => 'Pilih Alat',               'isi' => 'Pilih alat yang berstatus Tersedia di bagian Peralatan, lalu klik Pinjam Alat. Alat berstatus Dipinjam belum bisa diajukan.', 'path' => 'M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z'],
        ['judul' => 'Isi Form',                 'isi' => 'Isi tanggal pinjam dan tanggal kembali (maksimal 14 hari) beserta keperluanmu. Satu form untuk satu alat.',                  'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['judul' => 'Tunggu Persetujuan Admin', 'isi' => 'Admin akan memverifikasi pengajuanmu. Pantau statusnya di Peminjaman Saya, lalu ambil alat setelah disetujui.',                 'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['judul' => 'Kembalikan Tepat Waktu',   'isi' => 'Kembalikan alat sesuai jadwal. Kondisi alat akan dicatat oleh admin saat proses pengembalian.',                              'path' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
    ];

    // Ikon footer. 'fill' => 'none' berarti ikon garis, 'currentColor' berarti ikon solid.
    $ikon = [
        'ig'   => ['fill' => 'none',         'isi' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>'],
        'mail' => ['fill' => 'none',         'isi' => '<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
        'tel'  => ['fill' => 'none',         'isi' => '<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>'],
        'web'  => ['fill' => 'none',         'isi' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 3.5 6 3.5 9s-1 6.5-3.5 9c-2.5-2.5-3.5-6-3.5-9s1-6.5 3.5-9z"/>'],
        'yt'   => ['fill' => 'currentColor', 'isi' => '<path d="M21.6 7.2s-.21-1.49-.86-2.14c-.82-.86-1.74-.86-2.16-.91C15.6 4 12 4 12 4h-.01s-3.6 0-6.58.15c-.42.05-1.34.05-2.16.91C2.6 5.71 2.4 7.2 2.4 7.2S2.19 8.94 2.19 10.68v1.63c0 1.74.21 3.48.21 3.48s.21 1.49.85 2.14c.82.86 1.9.83 2.38.92 1.72.17 7.37.22 7.37.22s3.6-.01 6.59-.16c.42-.05 1.34-.05 2.16-.91.65-.65.86-2.14.86-2.14s.21-1.74.21-3.48v-1.63c0-1.74-.21-3.48-.21-3.48zM9.99 14.5v-5l5 2.51-5 2.49z"/>'],
        'fb'   => ['fill' => 'currentColor', 'isi' => '<path d="M14 9h3V5h-3c-2.21 0-4 1.79-4 4v2H7v4h3v6h4v-6h3l1-4h-4V9c0-.55.45-1 1-1z"/>'],
    ];

    $grupFooter = [
        [
            'judul'  => 'Ekskul Multimedia',
            'item'   => [
                ['ikon' => 'ig',   'teks' => '@mulmedsk5a',           'tip' => 'Instagram Ekskul Multimedia',         'url' => 'https://www.instagram.com/mulmedsk5a'],
                ['ikon' => 'mail', 'teks' => 'mulmedsk5.a@gmail.com', 'tip' => 'Kirim email ke Ekskul Multimedia',    'url' => 'mailto:mulmedsk5.a@gmail.com'],
            ],
        ],
        [
            'judul'  => 'Kontak Sekolah',
            'item'   => [
                ['ikon' => 'tel',  'teks' => '0271 713916',   'tip' => 'Telepon SMK Negeri 5 Surakarta',      'url' => 'tel:0271713916'],
                ['ikon' => 'mail', 'teks' => 'info@smkn.net', 'tip' => 'Kirim email ke SMK Negeri 5 Surakarta', 'url' => 'mailto:info@smkn.net'],
            ],
        ],
        [
            'judul'  => 'Tautan Sekolah',
            'item'   => [
                ['ikon' => 'web', 'teks' => 'Website',   'tip' => 'smkn5surakarta.sch.id',          'url' => 'https://www.smkn5surakarta.sch.id/'],
                ['ikon' => 'ig',  'teks' => 'Instagram', 'tip' => 'Instagram @smknegeri5surakarta', 'url' => 'https://www.instagram.com/smknegeri5surakarta'],
                ['ikon' => 'yt',  'teks' => 'YouTube',   'tip' => 'YouTube @smknegeri5surakarta592', 'url' => 'https://youtube.com/@smknegeri5surakarta592'],
                ['ikon' => 'fb',  'teks' => 'Facebook',  'tip' => 'Facebook sekolah',               'url' => 'https://www.facebook.com/pakazissolo'],
            ],
        ],
    ];
@endphp

    <!-- Navbar Atas -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-3 sm:gap-6">
            <!-- Logo & Brand (di HP logo mengecil dan subjudul dipotong supaya tombol profil dan menu tidak terdorong keluar layar) -->
            <a href="{{ url('/dashboard') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3 sm:shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-14 h-14 shrink-0 sm:w-18 sm:h-18 transition-transform duration-300 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-3">
                <div class="min-w-0">
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block truncate transition-colors duration-300 group-hover:text-gray-900">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 min-w-0 max-w-[30rem] hidden md:block">
                <div class="relative" id="search-wrapper">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </span>
                    <input type="text" id="search-input" autocomplete="off" placeholder="Cari Alat Multimedia..." class="w-full pl-11 pr-4 py-2.5 bg-gray-200/70 border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-500 hover:border-blue-400 hover:bg-white hover:shadow-md hover:shadow-blue-900/5 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200">

                    <!-- Dropdown Hasil Pencarian -->
                    <div id="search-dropdown" class="dropdown-animasi hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 max-h-80 overflow-y-auto z-50">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Nav Links, Profil, Menu Mobile (urutan link mengikuti urutan bagian di halaman) -->
            <div class="flex items-center gap-3 shrink-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm font-medium text-gray-700">
                    <a href="#peminjaman-saya" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Peminjaman Saya</a>
                    <a href="#peralatan" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Peralatan</a>
                    <a href="#cara-pinjam" class="px-3 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:scale-95">Cara Pinjam</a>
                </nav>

                @include('partials.profil-dropdown')

                <!-- Menu mobile (tanpa JS, memakai <details>) -->
                <details id="menu-mobile" class="lg:hidden relative">
                    <summary aria-label="Buka menu" class="list-none cursor-pointer w-10 h-10 flex items-center justify-center rounded-lg text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 active:scale-95 [&::-webkit-details-marker]:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </summary>
                    <div class="dropdown-animasi absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 text-sm font-medium text-gray-700">
                        <a href="#peminjaman-saya" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6 active:bg-blue-50 active:text-blue-700">Peminjaman Saya</a>
                        <a href="#peralatan" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6 active:bg-blue-50 active:text-blue-700">Peralatan</a>
                        <a href="#cara-pinjam" class="block px-4 py-2.5 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:pl-6 active:bg-blue-50 active:text-blue-700">Cara Pinjam</a>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <!-- Hero Section (identik dengan landing page) -->
    <div class="relative bg-slate-900 w-full h-[420px] sm:h-[480px] md:h-[540px] flex flex-col justify-center items-center text-center px-4 overflow-hidden">
        <!-- Lapis 1: Foto banner, blur ringan, opacity penuh (scale-110 menutup tepi yang pudar akibat blur) -->
        <img src="{{ \App\Support\Banner::url() }}" alt="" fetchpriority="high" class="absolute inset-0 w-full h-full object-cover scale-110 blur-[1px]">

        <!-- Lapis 2: Bayangan hanya di tengah supaya teks terbaca. Gelapnya ditahan di sepanjang area teks (0% sampai 40%),
             lalu memudar ke tepi sehingga foto di pinggir tetap cerah -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_75%_60%_at_50%_50%,rgba(15,23,42,0.65)_0%,rgba(15,23,42,0.55)_40%,rgba(15,23,42,0)_100%)]"></div>

        <!-- Lapis 3: Teks (membesar pelan saat kursor di atasnya) -->
        <div class="group relative z-10 max-w-4xl transition-transform duration-500 ease-out motion-safe:hover:scale-105">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-bold mb-4 tracking-tight [text-shadow:0_1px_3px_rgb(0_0_0_/_60%),0_4px_28px_rgb(0_0_0_/_55%)]">
                <span class="text-blue-400 transition-colors duration-300 group-hover:text-blue-300">SiLat</span>
                <span class="text-white"> Multimedia</span>
            </h1>
            <p class="text-white font-medium text-lg sm:text-xl md:text-2xl [text-shadow:0_1px_3px_rgb(0_0_0_/_65%),0_3px_20px_rgb(0_0_0_/_60%)]">Sistem Peminjaman Alat Multimedia</p>
        </div>
    </div>

    <!-- Konten Utama. Warna latar tiap bagian berselang-seling:
         Peminjaman Saya (abu) -> Peralatan (putih) -> Cara Pinjam (abu) -> Footer (putih) -->
    <main class="flex-grow w-full">

        <!-- Bagian 1: Peminjaman Saya (abu) -->
        <section id="peminjaman-saya" class="scroll-mt-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="group mb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700">Peminjaman Saya</h2>
                    <p class="text-gray-500 text-sm mt-1 transition-colors duration-300 group-hover:text-gray-700">
                        @if ($kosong)
                            Belum ada pengajuan. Mulai dari katalog untuk menemukan alat yang kamu butuhkan.
                        @else
                            Pantau semua pengajuanmu di sini. Klik kartu status untuk menyaring daftar.
                        @endif
                    </p>
                </div>

                <!-- Peringatan keterlambatan (hanya muncul kalau ada).
                     Gaya kartu sama dengan panel lain (putih, rounded-2xl, border lembut), aksen merah seperti badge "Terlambat" -->
                @if ($jumlahTerlambat > 0)
                    <div role="alert" class="group mb-6 flex items-center gap-4 rounded-2xl border border-gray-200/80 bg-white px-5 py-4 shadow-sm transition-all duration-300 hover:border-red-300 hover:bg-red-50/50 hover:shadow-xl hover:shadow-red-900/10 motion-safe:hover:-translate-y-1">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 transition-all duration-300 group-hover:bg-red-600 group-hover:text-white motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm sm:text-base transition-colors duration-300 group-hover:text-red-700">{{ $jumlahTerlambat }} peminjaman terlambat dikembalikan</p>
                            <p class="text-sm text-gray-500 mt-0.5 transition-colors duration-300 group-hover:text-gray-700">Segera kembalikan alat ke Ekskul Multimedia, dan hubungi admin kalau ada kendala.</p>
                        </div>
                    </div>
                @endif

                <!-- Kartu ringkasan (sekaligus tombol filter). "Peminjaman Terbaru" aktif secara bawaan.
                     Tinggi kartu dibuat tetap (min-h) supaya tidak berubah ketika ada data.
                     Hover (hanya kartu yang belum dipilih): border + latar biru muda, terangkat, ikon miring.
                     Aktif (aria-pressed=true): biru solid blue-600, sama dengan teks "SiLat".
                     Ikon tiap kartu saat aktif memakai versi terang dari warna aslinya (lihat 'aktif' di $kartuFilter),
                     jadi tetap terlihat di atas latar biru. -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                    @foreach ($kartuFilter as $kode => $k)
                        @php $aktifAwal = $kode === 'semua'; @endphp
                        <button type="button" data-filter="{{ $kode }}" data-label="{{ $k['label'] }}" aria-pressed="{{ $aktifAwal ? 'true' : 'false' }}"
                                @disabled($kosong)
                                class="filter-kartu group {{ $kode === 'semua' ? 'col-span-2 sm:col-span-1' : '' }} {{ $kosong ? 'cursor-default' : 'cursor-pointer' }} min-h-[6.25rem] text-left rounded-2xl border border-gray-200/80 bg-white shadow-sm p-4 flex items-center gap-3.5 transition-all duration-300
                                       aria-[pressed=false]:hover:border-blue-500 aria-[pressed=false]:hover:bg-blue-50 aria-[pressed=false]:hover:ring-2 aria-[pressed=false]:hover:ring-blue-500/20 aria-[pressed=false]:hover:shadow-2xl aria-[pressed=false]:hover:shadow-blue-900/15 motion-safe:aria-[pressed=false]:hover:-translate-y-1.5
                                       aria-[pressed=true]:hover:shadow-xl aria-[pressed=true]:hover:shadow-blue-600/40 motion-safe:aria-[pressed=true]:hover:-translate-y-0.5 active:scale-[0.98]
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500
                                       aria-[pressed=true]:border-blue-600 aria-[pressed=true]:bg-blue-600 aria-[pressed=true]:shadow-lg aria-[pressed=true]:shadow-blue-600/30">
                            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-all duration-300 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6 {{ $k['ikon'] }} group-aria-[pressed=true]:bg-white/20 {{ $k['aktif'] }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $k['path'] }}"/>
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-2xl font-bold text-gray-900 leading-tight transition-colors group-aria-[pressed=true]:text-white group-aria-[pressed=false]:group-hover:text-blue-700">{{ $kode === 'semua' ? $baris->count() : ($jumlah[$kode] ?? 0) }}</span>
                                <span class="block text-xs text-gray-500 transition-colors group-aria-[pressed=true]:text-blue-100 group-aria-[pressed=false]:group-hover:text-gray-700">{{ $k['label'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                @unless ($kosong)
                <!-- Baris keterangan: ikon "i" biru di kiri, teks, lalu tautan aksi.
                     Filter "Peminjaman Terbaru": "Menampilkan N Peminjaman Terbaru" plus tautan riwayat.
                     Filter status lain: "Menampilkan N Peminjaman" (tanpa titik, sama dengan dashboard admin), plus tombol "Tampilkan semua".
                     Ikon urutan di sini hanya untuk layar di bawah xl (di layar lebar ikonnya ada di header tabel).
                     Baris ini "relative": menu urutan di layar kecil dipasang relatif terhadap baris (rata kiri), bukan terhadap tombolnya,
                     supaya menu selebar 14rem tidak pernah keluar dari layar HP ketika tombol berada di sebelah kanan teks. -->
                <div id="info-filter" class="relative mb-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-gray-600">
                    <p class="group flex items-center gap-2 transition-colors duration-200 hover:text-gray-900">
                        <svg class="w-4 h-4 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="info-filter-teks">Menampilkan {{ $baris->count() }} Peminjaman Terbaru</span>
                    </p>

                    <div class="urut-wrapper xl:hidden">
                        <button type="button" aria-haspopup="true" aria-expanded="false" aria-label="Urutkan daftar" title="Urutkan"
                                class="tombol-menu-urut cursor-pointer inline-flex items-center justify-center w-10 h-10 sm:w-8 sm:h-8 rounded-lg border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:border-blue-500 hover:bg-blue-50 hover:text-blue-600 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500
                                       aria-[expanded=true]:border-blue-600 aria-[expanded=true]:bg-blue-50 aria-[expanded=true]:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </button>

                        <div role="group" aria-label="Urutkan daftar" class="menu-urut hidden absolute left-0 top-full mt-2 w-56 max-w-full bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-30 text-left">
                            <p class="px-4 pt-1.5 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Urutkan berdasarkan</p>

                            <button type="button" data-urut="status" aria-pressed="false"
                                    class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                           aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                <span>Status</span>
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="label-urut">A–Z</span>
                                    <svg class="ikon-urut w-3.5 h-3.5 rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                    </svg>
                                </span>
                            </button>

                            <button type="button" data-urut="tanggal" aria-pressed="true"
                                    class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                           aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                <span>Tanggal</span>
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="label-urut">Terbaru</span>
                                    <svg class="ikon-urut w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Tautan ke seluruh riwayat saat daftar tidak difilter (teks sama dengan dashboard admin). -->
                    <span id="link-riwayat">
                        <a href="{{ route('peminjaman.riwayat') }}" class="tautan-panah inline-flex items-center gap-1 rounded-lg px-2 py-1 -mx-2 font-semibold text-blue-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-800 active:scale-95 active:bg-blue-50">
                            Lihat Riwayat Lengkap
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </span>

                    <!-- Tombol reset (tampil saat filter status lain aktif) -->
                    <button type="button" class="reset-filter hidden cursor-pointer rounded-lg px-2 py-1 -mx-2 font-semibold text-blue-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-800 active:scale-95 active:bg-blue-50" id="reset-toolbar">Tampilkan semua</button>
                </div>
                @endunless

                <div id="daftar-pinjam-isi">
                    @if ($kosong)
                        <!-- Tampilan belum ada peminjaman: kiri ajakan dengan ikon besar, kanan garis waktu 4 langkah -->
                        <section aria-labelledby="peminjaman-kosong-judul" class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5">
                            <div class="grid lg:grid-cols-[1.1fr_0.9fr]">

                                <div class="group relative isolate flex flex-col justify-center overflow-hidden p-6 sm:p-9 lg:p-12">
                                    <!-- Hiasan lembut di pojok supaya sisi kiri tidak terasa kosong -->
                                    <span aria-hidden="true" class="pointer-events-none absolute -left-20 -top-20 -z-10 h-56 w-56 rounded-full bg-blue-100/60 blur-3xl transition-transform duration-700 group-hover:scale-125"></span>
                                    <span aria-hidden="true" class="pointer-events-none absolute -bottom-24 right-0 -z-10 h-52 w-52 rounded-full bg-indigo-100/50 blur-3xl transition-transform duration-700 group-hover:scale-125"></span>

                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/30 transition-all duration-300 group-hover:bg-blue-700 group-hover:shadow-xl group-hover:shadow-blue-600/40 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6">
                                            <svg class="h-7 w-7 transition-transform duration-300 motion-safe:group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="11" cy="11" r="7"></circle>
                                                <path d="m20 20-4-4"></path>
                                            </svg>
                                        </span>
                                        <span class="group/pil inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 transition-all duration-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white motion-safe:hover:scale-105">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600 transition-colors group-hover/pil:bg-white"></span>
                                            Mulai perjalanan peminjaman
                                        </span>
                                    </div>

                                    <h3 id="peminjaman-kosong-judul" class="mt-5 text-2xl font-bold tracking-tight text-gray-900 transition-colors duration-300 group-hover:text-blue-700 sm:text-3xl">
                                        Belum ada peminjaman
                                    </h3>
                                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-gray-600 transition-colors duration-300 group-hover:text-gray-900 sm:text-base">
                                        Alat yang kamu butuhkan mungkin sudah tersedia. Jelajahi katalog, pilih alat, lalu kirim pengajuan dengan tanggal dan keperluanmu.
                                    </p>

                                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                                        <a href="#peralatan" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/40 active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            Pilih Alat Sekarang
                                        </a>
                                        <a href="#cara-pinjam" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 transition-all duration-200 hover:-translate-y-1 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 hover:shadow-lg active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                            Lihat Cara Pinjam
                                        </a>
                                    </div>

                                    <p class="mt-6 flex items-start gap-2 text-xs leading-relaxed text-gray-500">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500 transition-transform duration-200 group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Pengajuanmu akan muncul di sini agar mudah dipantau setelah dikirim.</span>
                                    </p>
                                </div>

                                <div class="border-t border-gray-100 bg-gray-50/70 p-6 sm:p-9 lg:border-l lg:border-t-0 lg:p-12">
                                    <div class="mb-6">
                                        <p class="text-base font-bold text-gray-900">Mulai dalam 4 langkah</p>
                                        <p class="mt-1 text-xs text-gray-500">Prosesnya sederhana dan mudah dipantau.</p>
                                    </div>

                                    <ol class="space-y-5">
                                        @foreach ($langkahKosong as $i => $l)
                                            <li class="group relative flex gap-4 rounded-xl p-2 -m-2 transition-all duration-200 hover:bg-white hover:shadow-md hover:shadow-blue-900/5 motion-safe:hover:translate-x-1">
                                                @unless ($loop->last)
                                                    <!-- Garis penghubung antar langkah -->
                                                    <span aria-hidden="true" class="absolute left-[1.625rem] top-11 bottom-[-1.75rem] w-px bg-blue-200"></span>
                                                @endunless
                                                <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white ring-4 ring-gray-50 transition-all duration-200 group-hover:bg-blue-700 group-hover:ring-white motion-safe:group-hover:scale-110">{{ $i + 1 }}</span>
                                                <div class="min-w-0 pt-1">
                                                    <p class="text-sm font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $l['judul'] }}</p>
                                                    <p class="mt-0.5 text-xs leading-relaxed text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $l['isi'] }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                        </section>
                    @else
                    <!-- Tabel untuk layar lebar (xl ke atas, sama dengan dashboard admin); layar lebih kecil memakai kartu.
                         Sebelumnya tabel sudah tampil di lg (1024px), padahal 6 kolomnya butuh sekitar 1100px sehingga isinya terdesak. -->
                    <div id="tabel-pinjam" class="hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 xl:block">
                        <table class="w-full text-sm">
                            <caption class="sr-only">Daftar peminjaman saya</caption>
                            <thead>
                                <tr class="border-b border-gray-200 text-left text-gray-500 text-xs">
                                    <th scope="col" class="px-6 py-4 font-semibold bg-gray-50 rounded-tl-2xl transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Alat</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Mulai Pinjam</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Batas Pengembalian</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Tanggal Dikembalikan</th>
                                    <th scope="col" class="px-4 py-4 font-semibold bg-gray-50 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Status</th>
                                    <th scope="col" class="px-6 py-4 font-semibold bg-gray-50 rounded-tr-2xl transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>Keterangan</span>

                                            <!-- Tombol urut berada di ujung kanan kolom -->
                                            <div class="urut-wrapper relative">
                                                <button type="button" aria-haspopup="true" aria-expanded="false" aria-label="Urutkan daftar" title="Urutkan"
                                                        class="tombol-menu-urut cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-lg border border-blue-300 bg-white text-blue-600 transition-all duration-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500
                                                               aria-[expanded=true]:border-blue-600 aria-[expanded=true]:bg-blue-50 aria-[expanded=true]:text-blue-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                                    </svg>
                                                </button>

                                                <div role="group" aria-label="Urutkan daftar" class="menu-urut hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-30 text-left font-normal">
                                                    <p class="px-4 pt-1.5 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Urutkan berdasarkan</p>

                                                    <button type="button" data-urut="status" aria-pressed="false"
                                                            class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                                                   aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                                        <span>Status</span>
                                                        <span class="inline-flex items-center gap-1.5 text-xs">
                                                            <span class="label-urut">A–Z</span>
                                                            <svg class="ikon-urut w-3.5 h-3.5 rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                                            </svg>
                                                        </span>
                                                    </button>

                                                    <button type="button" data-urut="tanggal" aria-pressed="true"
                                                            class="tombol-urut cursor-pointer w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 hover:pl-6 active:bg-blue-50
                                                                   aria-[pressed=true]:bg-blue-50 aria-[pressed=true]:text-blue-600 aria-[pressed=true]:font-semibold">
                                                        <span>Tanggal</span>
                                                        <span class="inline-flex items-center gap-1.5 text-xs">
                                                            <span class="label-urut">Terbaru</span>
                                                            <svg class="ikon-urut w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-5-5m5 5l5-5"/>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($baris as $b)
                                    @php $al = $b['alat_data']; @endphp
                                    <tr class="baris-pinjam group cursor-pointer transition-colors duration-200 hover:bg-blue-50 active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500" tabindex="0" aria-label="Lihat detail peminjaman {{ $b['alat_data']['nama'] }}" data-url="{{ $b['detail_url'] }}" data-status="{{ $b['status'] }}" data-label-status="{{ mb_strtolower($b['tampil']['label']) }}" data-tanggal="{{ $b['pinjam_ts'] }}">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3.5 transition-transform duration-300 motion-safe:group-hover:translate-x-1.5">
                                                <div class="relative w-12 h-12 shrink-0 rounded-xl border border-gray-100 bg-white overflow-hidden transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                                    <img src="{{ $al['foto_url'] }}" alt="" loading="lazy"
                                                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                                         class="w-full h-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                                    <div class="hidden absolute inset-0 items-center justify-center bg-blue-50 text-blue-400 [&:not(.hidden)]:flex">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$al['ikon']] !!}</svg>
                                                    </div>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $al['nama'] }}</p>
                                                    <p class="text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $al['sub'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5 font-semibold text-gray-800 whitespace-nowrap transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam_f'] }}</td>
                                        <td class="px-4 py-5 font-semibold text-gray-800 whitespace-nowrap transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali_f'] }}</td>
                                        <td class="px-4 py-5 whitespace-nowrap {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</td>
                                        <td class="px-4 py-5">
                                            <span class="inline-flex items-center gap-1.5 {{ $b['tampil']['badge'] }} text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-110">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $b['tampil']['dot'] }}"></span>
                                                {{ $b['tampil']['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 max-w-[17rem]">
                                            <p class="text-sm font-semibold text-gray-800 leading-snug line-clamp-2 transition-colors duration-200 group-hover:text-blue-700" title="{{ $b['ket_teks'] }}">{{ $b['ket_teks'] }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Kartu (layar di bawah xl): 1 kolom di HP, 2 kolom di sm, 3 kolom di lg -->
                    <div id="kartu-pinjam" class="xl:hidden grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($baris as $b)
                            @php $al = $b['alat_data']; @endphp
                            <article class="baris-pinjam group min-w-0 cursor-pointer rounded-2xl border border-gray-200/80 bg-white shadow-sm p-5 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:shadow-2xl hover:shadow-blue-900/15 motion-safe:hover:-translate-y-1.5 active:scale-[0.98] active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" tabindex="0" aria-label="Lihat detail peminjaman {{ $b['alat_data']['nama'] }}" data-url="{{ $b['detail_url'] }}" data-status="{{ $b['status'] }}" data-label-status="{{ mb_strtolower($b['tampil']['label']) }}" data-tanggal="{{ $b['pinjam_ts'] }}">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-12 h-12 shrink-0 rounded-xl border border-gray-100 bg-white overflow-hidden transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                        <img src="{{ $al['foto_url'] }}" alt="" loading="lazy"
                                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                             class="w-full h-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                        <div class="hidden absolute inset-0 items-center justify-center bg-blue-50 text-blue-400 [&:not(.hidden)]:flex">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$al['ikon']] !!}</svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 leading-snug break-words transition-colors duration-200 group-hover:text-blue-700">{{ $al['nama'] }}</p>
                                        <p class="text-xs text-gray-500 break-words transition-colors duration-200 group-hover:text-gray-700">{{ $al['sub'] }}</p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1.5 mt-3 {{ $b['tampil']['badge'] }} text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-105">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $b['tampil']['dot'] }}"></span>
                                    {{ $b['tampil']['label'] }}
                                </span>

                                <dl class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs transition-colors duration-200 group-hover:border-blue-200">
                                    <div>
                                        <dt class="text-gray-500 leading-snug">Mulai Pinjam</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam_f'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 leading-snug">Batas Pengembalian</dt>
                                        <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali_f'] }}</dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-gray-500 leading-snug">Tanggal Dikembalikan</dt>
                                        <dd class="mt-0.5 {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</dd>
                                    </div>
                                </dl>

                                <p class="mt-3 text-sm font-semibold text-gray-800 leading-snug break-words transition-colors duration-200 group-hover:text-blue-700">{{ $b['ket_teks'] }}</p>
                            </article>
                        @endforeach
                    </div>
                    @endif
                </div>

                @unless ($kosong)
                    <!-- Muncul kalau filter tidak punya hasil -->
                    <div id="hasil-kosong" class="hidden bg-white rounded-2xl border border-dashed border-gray-300 px-6 py-12 text-center transition-all duration-300 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-lg motion-safe:hover:-translate-y-1">
                        <p id="hasil-kosong-teks" class="font-semibold text-gray-700"></p>
                        <button type="button" class="reset-filter cursor-pointer rounded-lg px-3 py-1 text-sm text-blue-600 hover:bg-blue-100 hover:text-blue-800 font-semibold mt-1 transition-all duration-200 active:scale-95 active:bg-blue-100">Tampilkan semua</button>
                    </div>
                @endunless
            </div>
        </section>

        <!-- Bagian 2: Peralatan (putih) -->
        <section id="peralatan" class="scroll-mt-20 bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="group mb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700">Peralatan Tersedia</h2>
                    <p class="text-gray-500 text-sm mt-1 transition-colors duration-300 group-hover:text-gray-700">Pilih alat yang kamu butuhkan, lalu isi formulir peminjaman.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($alatList as $a)
                        <div id="alat-{{ $a['slug'] }}" class="group {{ $a['tersedia'] ? 'bg-white' : 'bg-gray-100' }} p-3 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col scroll-mt-28 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-900/15 hover:border-blue-400 motion-safe:hover:-translate-y-2">
                            <div class="{{ $a['tersedia'] ? 'bg-white border-gray-100' : 'bg-gray-200 border-gray-300' }} border rounded-xl h-44 relative flex justify-center items-center overflow-hidden transition-colors duration-300">
                                @if ($a['tersedia'])
                                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10 transition-all duration-300 group-hover:bg-green-600 group-hover:shadow-md motion-safe:group-hover:scale-110">Tersedia</span>
                                @else
                                    <span class="absolute top-3 right-3 bg-gray-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm z-10 transition-all duration-300 group-hover:bg-gray-600 group-hover:shadow-md motion-safe:group-hover:scale-110">Dipinjam</span>
                                @endif

                                <img src="{{ $a['foto_url'] }}" alt="{{ $a['nama'] }}" loading="lazy"
                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                     class="object-contain h-full w-full p-4 transition-transform duration-500 ease-out motion-safe:group-hover:scale-105 {{ $a['tersedia'] ? '' : 'grayscale mix-blend-multiply' }}">

                                <!-- Muncul otomatis kalau file foto belum ada -->
                                <div class="hidden absolute inset-0 {{ $a['tersedia'] ? 'bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300 group-hover:text-blue-500' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400' }} items-center justify-center transition-colors [&:not(.hidden)]:flex">
                                    <svg class="w-16 h-16 transition-transform duration-500 motion-safe:group-hover:scale-125 motion-safe:group-hover:-rotate-6" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$a['ikon']] !!}</svg>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col flex-grow">
                                <h3 class="text-gray-900 font-bold text-base break-words transition-colors duration-300 group-hover:text-blue-700">{{ $a['nama'] }}</h3>
                                <p class="text-gray-500 text-xs mb-4 break-words transition-colors duration-300 group-hover:text-gray-700">{{ $a['sub'] }}</p>
                                @if ($a['tersedia'])
                                    <a href="{{ route('peminjaman.create', $a['slug']) }}" class="mt-auto w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl text-center transition-all duration-200 shadow-sm hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-600/40 active:scale-[0.98]">
                                        Pinjam Alat
                                    </a>
                                @else
                                    <button type="button" disabled class="mt-auto w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2.5 rounded-xl text-center cursor-not-allowed">
                                        Tidak Tersedia
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pengingat foto: ikon biru supaya tertangkap mata, teks abu agar tidak terkesan peringatan bahaya -->
                <p class="group mt-6 flex items-start gap-2 text-xs sm:text-sm text-gray-600 transition-colors duration-200 hover:text-gray-900">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Foto hanya sebagai ilustrasi. Tampilan dan kondisi fisik alat dapat berbeda dari gambar.</span>
                </p>
            </div>
        </section>
    </main>

    <!-- Bagian 3: Cara Pinjam (abu, tampilan identik dengan landing page) -->
    <section id="cara-pinjam" class="scroll-mt-20 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="group text-center max-w-2xl mx-auto mb-12">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide transition-colors duration-300 group-hover:text-blue-800">Panduan</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 transition-colors duration-300 group-hover:text-blue-700">Cara Pinjam Alat</h2>
                <p class="text-gray-600 mt-3 transition-colors duration-300 group-hover:text-gray-900">Empat langkah mudah untuk meminjam alat multimedia.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                @foreach ($langkah as $i => $l)
                    <div class="group relative bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm transition-all duration-300 hover:shadow-2xl hover:shadow-blue-900/15 hover:border-blue-400 motion-safe:hover:-translate-y-2">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white font-bold rounded-xl transition-all duration-300 group-hover:bg-blue-700 group-hover:shadow-lg group-hover:shadow-blue-600/30 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6">{{ $i + 1 }}</div>
                            <div class="w-10 h-10 flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-600/30 motion-safe:group-hover:scale-110 motion-safe:group-hover:rotate-6">
                                <svg class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $l['path'] }}"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2 transition-colors duration-300 group-hover:text-blue-700">{{ $l['judul'] }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed transition-colors duration-300 group-hover:text-gray-900">{{ $l['isi'] }}</p>

                        @unless ($loop->last)
                            <!-- Penghubung antar langkah (hanya di layar lebar) -->
                            <span class="hidden lg:block absolute top-11 -right-6 w-6 h-px bg-blue-200 transition-colors duration-300 group-hover:bg-blue-500"></span>
                        @endunless
                    </div>
                @endforeach
            </div>

            <div class="text-center">
                <a href="#peralatan" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold px-8 py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-600/40 motion-safe:hover:scale-105 active:scale-[0.98]">
                    Pilih Alat Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <div class="grid lg:grid-cols-12 gap-x-12 gap-y-8 pb-8">
                <div class="lg:col-span-5 space-y-4">
                    <div class="relative h-56 rounded-2xl overflow-hidden border border-gray-200 bg-gray-100 transition-all duration-300 hover:border-blue-400 hover:shadow-xl hover:shadow-blue-900/10">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.201368359219!2d110.79482417476349!3d-7.5530087924607!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a14257d546c99%3A0xd7caf9142b4a2170!2sSMK%20Negeri%205%20Surakarta!5e0!3m2!1sid!2sid!4v1792193800974!5m2!1sid!2sid" title="Peta lokasi SMK Negeri 5 Surakarta" class="absolute inset-0 w-full h-full border-0 pointer-events-none lg:pointer-events-auto" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        <a href="https://www.google.com/maps/search/?api=1&amp;query=SMK+Negeri+5+Surakarta" target="_blank" rel="noopener noreferrer" aria-label="Buka peta di Google Maps" class="absolute inset-0 lg:hidden"></a>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&amp;query=SMK+Negeri+5+Surakarta" target="_blank" rel="noopener noreferrer" aria-label="Buka alamat sekolah di Google Maps" class="footer-link group flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-blue-600 transition-all duration-300 group-hover:text-blue-800 motion-safe:group-hover:scale-125 motion-safe:group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700 group-hover:text-blue-700 group-hover:underline underline-offset-4 leading-relaxed transition-colors duration-300">Jl. LU Adi Sucipto No. 42, Kerten, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57143</p>
                    </a>
                </div>

                <div class="lg:col-span-7 grid sm:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-8 content-start">
                    @foreach ($grupFooter as $g)
                        <div class="group/grup min-w-0">
                            <h3 class="text-gray-900 font-bold text-sm uppercase tracking-wide transition-colors duration-300 group-hover/grup:text-blue-600">{{ $g['judul'] }}</h3>
                            <span class="block w-8 h-0.5 rounded-full bg-blue-600 mt-2 mb-4 transition-all duration-300 group-hover/grup:w-14"></span>
                            <ul class="space-y-3">
                                @foreach ($g['item'] as $it)
                                    @php $ic = $ikon[$it['ikon']]; @endphp
                                    <li>
                                        <a href="{{ $it['url'] }}" title="{{ $it['tip'] }}" @if (str_starts_with($it['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif class="footer-link group inline-flex items-center gap-3 max-w-full transition-transform duration-200 motion-safe:hover:translate-x-1">
                                            <span class="w-9 h-9 rounded-full bg-blue-50 group-hover:bg-blue-600 flex items-center justify-center transition-all duration-200 motion-safe:group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-blue-600/30 shrink-0">
                                                <svg class="w-4.5 h-4.5 text-blue-600 group-hover:text-white transition-colors" fill="{{ $ic['fill'] }}" stroke="{{ $ic['fill'] === 'none' ? 'currentColor' : 'none' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ic['isi'] !!}</svg>
                                            </span>
                                            <span class="min-w-0 break-words text-sm font-medium text-gray-700 group-hover:text-blue-600 group-hover:underline underline-offset-4 transition">{{ $it['teks'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-200 pt-5 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500 [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
                <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
                <p>Mulmedska SMK Negeri 5 Surakarta</p>
            </div>
        </div>
    </footer>

    <!-- Script -->
    <script>
        // ===== Link anchor tanpa # di bilah alamat =====
        (function () {
            var hashAwal = window.location.hash;
            var menu = document.getElementById('menu-mobile');

            function bersihkanUrl() {
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }

            document.addEventListener('click', function (event) {
                var link = event.target.closest('a[href^="#"]');
                if (!link) return;

                event.preventDefault();
                if (menu) menu.removeAttribute('open');

                var id = link.getAttribute('href').slice(1);
                var target = id ? document.getElementById(id) : null;
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                bersihkanUrl();
            });

            // Menu mobile ikut menutup saat mengetuk di luar menu atau menekan Esc
            if (menu) {
                document.addEventListener('click', function (event) {
                    if (menu.hasAttribute('open') && !menu.contains(event.target)) menu.removeAttribute('open');
                });
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') menu.removeAttribute('open');
                });
            }

            // Datang lewat /dashboard#peralatan: tetap gulir ke bagian itu, lalu hapus #
            if (hashAwal) {
                bersihkanUrl();
                window.addEventListener('load', function () {
                    var target = document.getElementById(decodeURIComponent(hashAwal.slice(1)));
                    if (target) target.scrollIntoView({ block: 'start' });
                });
            }
        })();

        // ===== Bayangan navbar muncul saat halaman digulir =====
        (function () {
            var navbar = document.getElementById('navbar');
            function periksa() { navbar.classList.toggle('shadow-md', window.scrollY > 8); }
            window.addEventListener('scroll', periksa, { passive: true });
            periksa();
        })();

        // ===== Search Dropdown =====
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
                return '<button type="button" onclick="pilihAlat(\'' + escapeHtml(alat.id) + '\')" class="group w-full text-left flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-blue-50 hover:pl-6 active:bg-blue-50 transition-all duration-200">' +
                    '<img src="' + escapeHtml(alat.foto) + '" alt="" onerror="fotoRusak(this)" class="w-10 h-10 rounded-lg object-contain shrink-0 bg-blue-50 transition-transform duration-200 group-hover:scale-105">' +
                    '<span class="flex-1 min-w-0">' +
                        '<span class="block text-sm font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">' + highlightMatch(alat.nama, kata) + '</span>' +
                        '<span class="block text-xs text-gray-400 truncate">' + highlightMatch(alat.sub, kata) + '</span>' +
                    '</span>' +
                    '</button>';
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

        // Klik alat di dropdown -> gulir ke kartunya dan beri sorotan sebentar
        function pilihAlat(idAlat) {
            searchDropdown.classList.add('hidden');
            searchInput.value = '';

            const kartu = document.getElementById(idAlat);
            if (kartu) {
                kartu.scrollIntoView({ behavior: 'smooth', block: 'center' });
                kartu.classList.add('ring-2', 'ring-blue-500');
                setTimeout(function () {
                    kartu.classList.remove('ring-2', 'ring-blue-500');
                }, 1500);
            }
        }

        document.addEventListener('click', function (event) {
            var wrapper = document.getElementById('search-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                searchDropdown.classList.add('hidden');
            }
        });

        // ===== Filter + Urutan Peminjaman Saya =====
        (function () {
            const kartuFilter = document.querySelectorAll('.filter-kartu');
            if (kartuFilter.length === 0) return;

            const kosong = document.querySelectorAll('#daftar-pinjam-isi .baris-pinjam').length === 0;

            const semuaBaris = document.querySelectorAll('#daftar-pinjam-isi .baris-pinjam');
            const barisTabel = document.querySelectorAll('#tabel-pinjam .baris-pinjam');
            const wadahTabel = document.querySelector('#tabel-pinjam tbody');
            const wadahKartu = document.getElementById('kartu-pinjam');   // null kalau belum ada peminjaman
            const isiDaftar = document.getElementById('daftar-pinjam-isi');
            const hasilKosong = document.getElementById('hasil-kosong');  // null kalau belum ada peminjaman
            const hasilKosongTeks = document.getElementById('hasil-kosong-teks');
            const infoFilter = document.getElementById('info-filter');
            const infoFilterTeks = document.getElementById('info-filter-teks');
            const resetToolbar = document.getElementById('reset-toolbar');
            const linkRiwayat = document.getElementById('link-riwayat');
            const tombolUrut = document.querySelectorAll('.tombol-urut');
            const wrapperUrut = document.querySelectorAll('.urut-wrapper');
            let filterAktif = 'semua';

            semuaBaris.forEach(function (baris) {
                baris.addEventListener('click', function () {
                    window.location.href = baris.dataset.url;
                });
                baris.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        window.location.href = baris.dataset.url;
                    }
                });
            });

            // ----- Menu urutan (ikon filter di header tabel, dan di baris keterangan untuk layar kecil) -----
            function tutupSemuaMenu() {
                wrapperUrut.forEach(function (w) {
                    w.querySelector('.menu-urut').classList.add('hidden');
                    w.querySelector('.tombol-menu-urut').setAttribute('aria-expanded', 'false');
                });
            }

            wrapperUrut.forEach(function (w) {
                const tombol = w.querySelector('.tombol-menu-urut');
                const menu = w.querySelector('.menu-urut');

                tombol.addEventListener('click', function () {
                    const bukaBaru = menu.classList.contains('hidden');
                    tutupSemuaMenu();
                    if (bukaBaru) {
                        menu.classList.remove('hidden');
                        tombol.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // Klik di luar menu = tutup
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.urut-wrapper')) tutupSemuaMenu();
            });

            // Tekan Esc = tutup
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') tutupSemuaMenu();
            });

            // ----- Urutan -----
            // Tiap kunci mengingat arahnya sendiri. Bawaan: Status A-Z, Tanggal Terbaru. Yang aktif saat ini: Tanggal.
            const urut = { kunci: 'tanggal', arah: { status: 'asc', tanggal: 'desc' } };
            const teksArah = {
                status:  { asc: 'A–Z',    desc: 'Z–A' },
                tanggal: { asc: 'Terlama', desc: 'Terbaru' }
            };

            function terapkanUrutan() {
                [wadahTabel, wadahKartu].filter(Boolean).forEach(function (wadah) {
                    const item = Array.from(wadah.querySelectorAll('.baris-pinjam'));
                    item.sort(function (a, b) {
                        const selisihTanggal = Number(a.dataset.tanggal) - Number(b.dataset.tanggal);
                        let hasil;
                        if (urut.kunci === 'status') {
                            hasil = a.dataset.labelStatus.localeCompare(b.dataset.labelStatus, 'id');
                            if (hasil === 0) hasil = selisihTanggal;   // status sama: urutkan menurut tanggal
                        } else {
                            hasil = selisihTanggal;
                        }
                        return urut.arah[urut.kunci] === 'asc' ? hasil : -hasil;
                    });
                    item.forEach(function (el) { wadah.appendChild(el); });
                });

                tombolUrut.forEach(function (tombol) {
                    const kunci = tombol.dataset.urut;
                    tombol.setAttribute('aria-pressed', kunci === urut.kunci ? 'true' : 'false');
                    tombol.querySelector('.label-urut').textContent = teksArah[kunci][urut.arah[kunci]];
                    // Panah ke atas = ascending, ke bawah = descending
                    tombol.querySelector('.ikon-urut').classList.toggle('rotate-180', urut.arah[kunci] === 'asc');
                });
            }

            tombolUrut.forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    const kunci = tombol.dataset.urut;
                    if (urut.kunci === kunci) {
                        // Klik opsi yang sedang aktif = balik arah
                        urut.arah[kunci] = urut.arah[kunci] === 'asc' ? 'desc' : 'asc';
                    } else {
                        urut.kunci = kunci;
                    }
                    terapkanUrutan();
                    tutupSemuaMenu();
                });
            });

            // ----- Filter -----
            function terapkanFilter(kode) {
                filterAktif = kode;

                // Tampilan aktif diatur lewat CSS (variant aria-[pressed=true]), cukup ubah atribut aria-pressed
                kartuFilter.forEach(function (kartu) {
                    kartu.setAttribute('aria-pressed', kartu.dataset.filter === kode ? 'true' : 'false');
                });
                tutupSemuaMenu();

                // Belum ada peminjaman: tidak ada baris yang perlu disaring, tampilan kosong tetap ditampilkan
                if (kosong) return;

                semuaBaris.forEach(function (baris) {
                    const cocok = kode === 'semua' || baris.dataset.status === kode;
                    baris.classList.toggle('hidden', !cocok);
                });

                let tampil = 0;
                barisTabel.forEach(function (baris) {
                    if (!baris.classList.contains('hidden')) tampil++;
                });

                // Tabel disembunyikan lewat CSS di layar kecil, jadi hitung dari kartu kalau tabel tidak terlihat
                if (barisTabel.length === 0 && wadahKartu) {
                    wadahKartu.querySelectorAll('.baris-pinjam').forEach(function (baris) {
                        if (!baris.classList.contains('hidden')) tampil++;
                    });
                }

                isiDaftar.classList.toggle('hidden', tampil === 0);
                hasilKosong.classList.toggle('hidden', tampil !== 0);
                infoFilter.classList.toggle('hidden', tampil === 0);

                resetToolbar.classList.toggle('hidden', kode === 'semua');
                linkRiwayat.classList.toggle('hidden', kode !== 'semua');

                if (kode === 'semua') {
                    infoFilterTeks.textContent = 'Menampilkan ' + tampil + ' Peminjaman Terbaru';
                } else {
                    const label = document.querySelector('.filter-kartu[data-filter="' + kode + '"]').dataset.label;
                    infoFilterTeks.textContent = 'Menampilkan ' + tampil + ' Peminjaman.';
                    hasilKosongTeks.textContent = 'Belum ada peminjaman berstatus ' + label.toLowerCase();
                }
            }

            kartuFilter.forEach(function (kartu) {
                kartu.addEventListener('click', function () {
                    const kode = kartu.dataset.filter;
                    // Klik kartu status yang sedang aktif sekali lagi = kembali ke "Peminjaman Terbaru"
                    terapkanFilter(kode !== 'semua' && filterAktif === kode ? 'semua' : kode);
                });
            });

            document.querySelectorAll('.reset-filter').forEach(function (tombol) {
                tombol.addEventListener('click', function () { terapkanFilter('semua'); });
            });

            terapkanUrutan();
        })();
    </script>
</body>
</html>