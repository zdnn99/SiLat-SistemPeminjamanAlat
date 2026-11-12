<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $alat->exists ? 'Kelola Alat' : 'Tambah Alat' }} - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Cincin fokus keyboard: spesifisitas nol supaya gaya khusus di elemen tetap menang */
        :where(a, button, summary, input, textarea, select):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen overflow-x-clip">

@php
    // Mode halaman: alat yang belum tersimpan di database = mode Tambah Alat, selain itu mode Kelola Alat
    $baru = ! $alat->exists;

    // TODO: hapus setelah semua data alat lengkap di database. Dipakai hanya sebagai isian awal kalau kolomnya masih kosong,
    // sama dengan nilai bawaan di form.blade.php.
    $bawaan = [
        'sony-mirrorless'       => ['deskripsi' => 'Kamera mirrorless ringkas dengan sensor APS-C untuk foto dan video dokumentasi kegiatan sekolah. Ringan dibawa dan mudah dipakai, termasuk oleh pemula.', 'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera']],
        'canon-dslr'            => ['deskripsi' => 'Kamera DSLR tingkat pemula untuk memotret acara, potret, dan dokumentasi. Dilengkapi jendela bidik optik dengan hasil gambar yang tajam.', 'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera']],
        'drone-dokumentasi-1'   => ['deskripsi' => 'Drone ringan dan ringkas untuk mengambil foto dan video dari udara. Cocok untuk dokumentasi kegiatan di area terbuka.', 'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan']],
        'drone-dokumentasi-2'   => ['deskripsi' => 'Drone lipat dengan kamera beresolusi tinggi untuk video udara yang stabil. Cocok untuk dokumentasi acara besar dan area yang luas.', 'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan']],
        'handycam'              => ['deskripsi' => 'Kamera video untuk merekam acara dan kegiatan dalam durasi panjang, dengan zoom optik yang praktis digunakan.', 'kelengkapan' => ['Unit handycam', 'Baterai', 'Charger atau adaptor', 'Kabel data', 'Kartu memori', 'Tas handycam']],
        'gimbal-stabilizer'     => ['deskripsi' => 'Penstabil gambar agar rekaman tetap mulus saat berjalan atau bergerak. Cocok untuk video liputan, vlog, dan dokumentasi kegiatan.', 'kelengkapan' => ['Unit gimbal', 'Baterai', 'Kabel pengisi daya', 'Plat dudukan (quick release)', 'Tas penyimpanan']],
        'tripod'                => ['deskripsi' => 'Penopang yang stabil untuk kamera dan handphone saat memotret atau merekam video, dengan tinggi yang dapat diatur.', 'kelengkapan' => ['Unit tripod', 'Dudukan handphone', 'Plat dudukan kamera', 'Tas tripod']],
        'handphone-dokumentasi' => ['deskripsi' => 'Handphone untuk dokumentasi cepat, menghasilkan foto dan video berkualitas dari perangkat yang praktis dibawa ke mana saja.', 'kelengkapan' => ['Handphone iPhone 13', 'Kabel pengisi daya', 'Adaptor charger', 'Casing pelindung']],
    ];

    // Mode tambah: tidak ada isian bawaan. Daftar kelengkapan dimulai kosong (tampil sebagai keadaan kosong yang rapi, bukan kolom kosong)
    $b = $baru
        ? ['deskripsi' => '', 'kelengkapan' => []]
        : ($bawaan[$alat->slug] ?? ['deskripsi' => '', 'kelengkapan' => []]);

    // Tiga slot foto, sama urutannya dengan galeri di form peminjaman.
    // kolom = nama kolom di tabel dan nama input file; nomor = urutan di galeri (1 utama, 2 kiri, 3 kanan)
    $slotFoto = [
        ['kolom' => 'foto',       'judul' => 'Foto Utama', 'nomor' => 1],
        ['kolom' => 'foto_kiri',  'judul' => 'Foto Kiri',  'nomor' => 2],
        ['kolom' => 'foto_kanan', 'judul' => 'Foto Kanan', 'nomor' => 3],
    ];
    // Galeri [utama, kiri, kanan] dari model: unggahan admin, atau foto bawaan, atau null
    $galeri = $alat->galeri;

    $nilaiNama        = old('nama_alat', $alat->nama_alat);
    $nilaiSubjudul    = old('subjudul', $baru ? '' : \Illuminate\Support\Str::limit($alat->subjudul, 60, ''));
    $nilaiDeskripsi   = old('deskripsi', $alat->deskripsi ?: $b['deskripsi']);
    $nilaiKelengkapan = (array) old('kelengkapan', $alat->kelengkapan ?: $b['kelengkapan']);
    // Alat baru dimulai dalam keadaan tersedia
    $nilaiTersedia    = (bool) (int) old('tersedia', $baru ? 1 : ($alat->tersedia ? 1 : 0));

    // Penghapusan hanya boleh kalau alat sudah tersimpan sebagai tidak tersedia dan belum pernah dipinjam (tidak berlaku di mode tambah)
    $punyaRiwayat = $baru ? false : \App\Models\Peminjaman::where('alat_id', $alat->id)->exists();
    $bisaHapus    = ! $baru && ! $alat->tersedia && ! $punyaRiwayat;

    // Kelas kolom isian: sama dengan form.blade.php (border merah otomatis lewat aria-invalid="true")
    $kelasInput = 'w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-base sm:text-sm outline-none transition duration-200 '
        . 'hover:border-blue-400 hover:bg-blue-50/40 hover:shadow-sm focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 '
        . 'aria-[invalid=true]:border-red-500 aria-[invalid=true]:hover:border-red-500 aria-[invalid=true]:focus:border-red-500 aria-[invalid=true]:focus:ring-red-500/20';

    // Kartu dan ikon: sama dengan form.blade.php. Di layar sentuh efek hover tidak berlaku, jadi ada padanan active: untuk umpan balik saat diketuk
    $kelasKartu = 'bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15 active:border-blue-300';
    $kelasIkon  = 'w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 active:bg-blue-600 active:text-white motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110';
    $kelasHapusBaris = 'shrink-0 inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-300 bg-white text-gray-500 transition-all duration-200 hover:border-red-300 hover:bg-red-50 hover:text-red-600 active:border-red-300 active:bg-red-50 active:text-red-600 active:scale-95';
    $kelasTambah = 'inline-flex w-full sm:w-auto items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-blue-300 bg-white text-sm font-semibold text-blue-700 transition-all duration-200 hover:bg-blue-600 hover:border-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/30 active:bg-blue-600 active:border-blue-600 active:text-white motion-safe:hover:-translate-y-0.5 active:scale-[0.98]';
    $kelasInfo  = 'group flex items-start gap-2 text-xs leading-relaxed text-gray-600 transition-colors duration-200 hover:text-gray-900';
    $kelasInfoIkon = 'w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125';
    $kelasLabel = 'block text-sm font-medium text-gray-700 mb-1.5 transition-colors duration-200 group-hover/f:text-blue-700';
    $ikonX      = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    $ikonPlus   = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/></svg>';
    $ikonInfo   = '<svg class="' . $kelasInfoIkon . '" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
@endphp

    <!-- Navbar atas -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-3 sm:gap-6">
            <a href="{{ route('admin.dashboard') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SiLat" class="w-14 h-14 shrink-0 sm:w-18 sm:h-18 transition-transform duration-300 motion-safe:group-hover:scale-105">
                <div class="min-w-0">
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block truncate">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <a href="{{ route('admin.dashboard') }}" aria-label="Kembali ke Dashboard"
               class="shrink-0 inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">
                <span><span class="hidden sm:inline">Kembali ke </span>Dashboard</span>
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 flex-grow w-full min-w-0">

        <div class="mb-5 sm:mb-6 flex min-w-0 items-start gap-4">
            <span class="hidden sm:flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/25 transition-all duration-300 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/40 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    @if ($baru)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    @endif
                </svg>
            </span>
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-gray-900 transition-colors duration-300 hover:text-blue-700">{{ $baru ? 'Tambah Alat' : 'Kelola ' . $alat->nama_alat }}</h1>
                <p class="text-gray-500 text-sm leading-relaxed mt-1.5 break-words">
                    @if ($baru)
                        Isi data alat baru. Setelah disimpan, alat langsung tampil di halaman peminjaman.
                    @else
                        Ubah data <span class="font-semibold text-gray-700">{{ $alat->nama_alat }}</span>. Perubahan langsung tampil di halaman peminjaman.
                    @endif
                </p>
            </div>
        </div>

        @if (session('sukses'))
            <div role="status" class="flex items-start gap-2 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-xl mb-6">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="min-w-0 break-words">{{ session('sukses') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="break-words">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tanpa items-start: kedua kolom meregang setinggi kolom yang paling tinggi, kartu terakhir di tiap kolom mengisi sisanya (lg:flex-1).
             Mode tambah: dikirim ke route store (POST). Mode kelola: dikirim ke route update (PUT) lewat slug alat. -->
        <form id="form-kelola" method="POST"
              action="{{ $baru ? route('admin.alat.store') : route('admin.alat.update', $alat->slug) }}"
              enctype="multipart/form-data" class="grid gap-5 sm:gap-6 lg:grid-cols-5">
            @csrf
            @unless ($baru)
                @method('PUT')
            @endunless

            <!-- KIRI: foto, ketersediaan, hapus -->
            <div class="min-w-0 lg:col-span-2 grid gap-5 sm:gap-6 md:grid-cols-2 lg:flex lg:flex-col">

                <!-- Foto alat: 3 slot, sama dengan galeri di form peminjaman.
                     1 = utama (besar, juga tampil di dashboard), 2 = kiri, 3 = kanan -->
                <section class="{{ $kelasKartu }} min-w-0 md:row-span-2 lg:row-span-1">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="{{ $kelasIkon }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Foto Alat</h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Foto utama tampil besar di halaman peminjaman dan dashboard. Foto kiri dan kanan melengkapi galeri.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($slotFoto as $s)
                            @php
                                $punyaUnggahan = str_starts_with($alat->{$s['kolom']} ?? '', 'uploads/');
                                $urlSlot = $galeri[$loop->index] ?? null;
                            @endphp

                            <div class="{{ $loop->first ? 'col-span-2' : '' }} min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-semibold text-gray-800">{{ $s['judul'] }}</span>
                                    <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-blue-50 px-1.5 text-[11px] font-bold text-blue-700">{{ $s['nomor'] }}</span>
                                </div>

                                <!-- Efek hover sama dengan galeri di form.blade.php -->
                                <div class="group relative aspect-[4/3] bg-white border border-gray-200/80 rounded-xl overflow-hidden transition-all duration-300 hover:border-blue-400 hover:shadow-xl hover:shadow-blue-900/15 hover:ring-2 hover:ring-blue-500/20 active:border-blue-400 active:ring-2 active:ring-blue-500/20 motion-safe:hover:-translate-y-1">
                                    <img id="pratinjau-{{ $s['kolom'] }}"
                                         src="{{ $urlSlot ?: 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' }}"
                                         alt="{{ $s['judul'] }} {{ $alat->nama_alat ?: 'alat' }}"
                                         onerror="this.classList.add('hidden'); document.getElementById('pratinjau-kosong-{{ $s['kolom'] }}').classList.remove('hidden');"
                                         class="absolute inset-0 w-full h-full object-contain p-3 transition-transform duration-500 ease-out motion-safe:group-hover:scale-110 {{ $urlSlot ? '' : 'hidden' }}">
                                    <div id="pratinjau-kosong-{{ $s['kolom'] }}" class="{{ $urlSlot ? 'hidden' : '' }} absolute inset-0 flex-col items-center justify-center gap-1 bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300 [&:not(.hidden)]:flex">
                                        <svg class="w-10 h-10 transition-transform duration-500 motion-safe:group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>
                                        </svg>
                                        <span class="text-[11px] font-medium text-blue-400">Foto belum tersedia</span>
                                    </div>
                                </div>

                                <div class="group/f mt-3">
                                    <label for="{{ $s['kolom'] }}" class="{{ $kelasLabel }}">{{ $baru ? 'Unggah foto' : 'Ganti foto' }}</label>
                                    <input id="{{ $s['kolom'] }}" type="file" name="{{ $s['kolom'] }}" accept="image/jpeg,image/png,image/webp"
                                           aria-invalid="{{ $errors->has($s['kolom']) ? 'true' : 'false' }}"
                                           class="block w-full min-w-0 text-xs text-gray-600 rounded-xl border border-gray-300 bg-white cursor-pointer transition duration-200 hover:border-blue-400 hover:shadow-sm active:border-blue-400 aria-[invalid=true]:border-red-500 file:mr-3 file:cursor-pointer file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="{{ $kelasInfo }} mt-2">
                                        {!! $ikonInfo !!}
                                        <span>JPG, PNG, atau WebP, maksimal 2 MB.</span>
                                    </p>
                                </div>

                                @if ($punyaUnggahan)
                                    <label class="mt-3 flex items-start gap-2 rounded-xl bg-gray-50 border border-transparent px-3 py-2.5 text-xs leading-relaxed text-gray-700 cursor-pointer transition-colors duration-200 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md hover:shadow-blue-900/5 active:border-blue-300 active:bg-blue-50 has-[:checked]:border-blue-300 has-[:checked]:bg-blue-50">
                                        <input type="checkbox" name="hapus_{{ $s['kolom'] }}" value="1" @checked(old('hapus_' . $s['kolom'])) class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 shrink-0 cursor-pointer">
                                        <span>Hapus unggahan {{ strtolower($s['judul']) }} ini.</span>
                                    </label>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- Ketersediaan (di mode tambah tidak ada kartu Hapus, jadi kartu ini yang mengisi sisa tinggi kolom di layar lebar) -->
                <section class="{{ $kelasKartu }} min-w-0 {{ $baru ? 'lg:flex-1' : '' }}">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="{{ $kelasIkon }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Ketersediaan</h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Atur apakah alat ini bisa dipinjam saat ini.</p>
                        </div>
                    </div>

                    <input type="hidden" name="tersedia" value="0">
                    <label class="flex items-center justify-between gap-4 rounded-xl bg-gray-50 border border-transparent px-4 py-3.5 cursor-pointer transition-colors duration-200 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md hover:shadow-blue-900/5 active:border-blue-300 active:bg-blue-50">
                        <span class="min-w-0">
                            <span id="judul-tersedia" class="block text-sm font-semibold leading-snug text-gray-900">{{ $nilaiTersedia ? 'Alat tersedia untuk dipinjam' : 'Alat tidak tersedia' }}</span>
                            <span id="ket-tersedia" class="block text-xs leading-relaxed text-gray-500 mt-0.5">{{ $nilaiTersedia ? 'Peminjam bisa memilih alat ini dan mengajukan peminjaman.' : 'Peminjam tidak bisa meminjam alat ini sampai ketersediaannya diaktifkan lagi.' }}</span>
                        </span>
                        <span class="relative shrink-0">
                            <input id="saklar-tersedia" type="checkbox" name="tersedia" value="1" @checked($nilaiTersedia) class="peer sr-only">
                            <span class="block h-6 w-11 rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-blue-600 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500 peer-focus-visible:ring-offset-2"></span>
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></span>
                        </span>
                    </label>

                    <p class="{{ $kelasInfo }} mt-4">
                        {!! $ikonInfo !!}
                        <span>
                            @if ($baru)
                                Alat baru dimulai dengan 1 unit. Matikan kalau alat belum siap dipinjam, lalu tekan Tambah Alat agar berlaku.
                            @else
                                Matikan saat alat rusak, hilang, atau sedang diperbaiki. Jangan lupa tekan Simpan Perubahan agar berlaku.
                            @endif
                        </span>
                    </p>
                </section>

                @unless ($baru)
                <!-- Hapus alat (di layar lebar mengisi sisa tinggi kolom supaya bawahnya sejajar dengan kolom kanan). Hanya ada di mode kelola. -->
                <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 min-w-0 lg:flex-1 transition-all duration-300 hover:border-red-300 hover:shadow-2xl hover:shadow-red-900/10 active:border-red-300">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-600/25 active:bg-red-600 active:text-white motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Hapus Alat</h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Hapus alat bila sudah tidak tersedia untuk dipinjam.</p>
                        </div>
                    </div>

                    <p class="text-sm leading-relaxed text-gray-600">Data alat dan foto unggahannya akan dihapus permanen dan tidak bisa dikembalikan.</p>

                    <button type="button" id="tombol-buka-hapus" @disabled(! $bisaHapus)
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-red-300 bg-white text-sm font-semibold text-red-600 transition-all duration-200 enabled:cursor-pointer enabled:hover:bg-red-600 enabled:hover:border-red-600 enabled:hover:text-white enabled:hover:shadow-lg enabled:hover:shadow-red-600/30 enabled:active:bg-red-600 enabled:active:border-red-600 enabled:active:text-white motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98] disabled:border-gray-200 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                        Hapus Alat Ini
                    </button>

                    <p class="{{ $kelasInfo }} mt-3">
                        {!! $ikonInfo !!}
                        <span>
                            @if ($alat->tersedia)
                                Untuk bisa menghapus, matikan ketersediaan lalu simpan perubahan terlebih dahulu.
                            @elseif ($punyaRiwayat)
                                Alat ini sudah punya riwayat peminjaman, jadi tidak bisa dihapus agar riwayat tetap utuh. Biarkan statusnya tidak tersedia.
                            @else
                                Alat sudah tidak tersedia dan belum pernah dipinjam, jadi aman untuk dihapus.
                            @endif
                        </span>
                    </p>
                </section>
                @endunless
            </div>

            <!-- KANAN: informasi dan kelengkapan -->
            <div class="min-w-0 lg:col-span-3 flex flex-col gap-5 sm:gap-6">

                <section class="{{ $kelasKartu }}">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <span class="{{ $kelasIkon }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Informasi Alat</h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Nama dan deskripsi yang dilihat peminjam.</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="group/f">
                            <label for="nama_alat" class="{{ $kelasLabel }}">Nama Alat <span class="text-red-500">*</span></label>
                            <input id="nama_alat" type="text" name="nama_alat" value="{{ $nilaiNama }}" maxlength="100" required
                                   placeholder="Contoh: Kamera Sony A6000"
                                   aria-invalid="{{ $errors->has('nama_alat') ? 'true' : 'false' }}" class="{{ $kelasInput }}">
                        </div>

                        <div class="group/f">
                            <label for="subjudul" class="{{ $kelasLabel }}">Subjudul Alat <span class="text-red-500">*</span></label>
                            <input id="subjudul" type="text" name="subjudul" value="{{ $nilaiSubjudul }}" maxlength="60" required
                                   placeholder="Contoh: Sony A6000"
                                   aria-invalid="{{ $errors->has('subjudul') ? 'true' : 'false' }}" class="{{ $kelasInput }}">
                            <p class="{{ $kelasInfo }} mt-1.5">
                                {!! $ikonInfo !!}
                                <span>Teks pendek di bawah nama alat, misalnya tipe atau model (maksimal 60 karakter).</span>
                            </p>
                        </div>

                        <div class="group/f">
                            <label for="deskripsi" class="{{ $kelasLabel }}">Deskripsi Alat <span class="text-red-500">*</span></label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" minlength="10" maxlength="500" required
                                      aria-invalid="{{ $errors->has('deskripsi') ? 'true' : 'false' }}"
                                      class="{{ $kelasInput }} min-h-[7rem] resize-none">{{ $nilaiDeskripsi }}</textarea>
                            <div class="mt-1.5 flex items-start justify-between gap-3">
                                <p class="{{ $kelasInfo }}">
                                    {!! $ikonInfo !!}
                                    <span>Jelaskan fungsi dan kegunaan alat dengan singkat (minimal 10 karakter).</span>
                                </p>
                                <p class="text-xs text-gray-500 shrink-0 tabular-nums"><span id="hitung-deskripsi">0</span>/500</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Kelengkapan: kartu terakhir di kolom kanan, di layar lebar mengisi sisa tinggi kolom supaya bawahnya sejajar dengan kolom kiri.
                     Isinya flex-col agar keadaan kosong ikut meregang dan kartu tidak terlihat kosong atau timpang. -->
                <section class="{{ $kelasKartu }} flex flex-col lg:flex-1">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <span class="{{ $kelasIkon }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Kelengkapan alat <span class="text-xs font-medium text-gray-500">(opsional)</span></h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Satu kolom untuk satu item yang didapat peminjam.</p>
                        </div>
                    </div>

                    <div id="daftar-kelengkapan" class="space-y-2.5 {{ count($nilaiKelengkapan) ? '' : 'hidden' }}">
                        @foreach ($nilaiKelengkapan as $item)
                            <div data-baris class="flex items-center gap-2">
                                <input type="text" name="kelengkapan[]" value="{{ $item }}" maxlength="100" autocomplete="off" spellcheck="false" placeholder="Contoh: Baterai cadangan" aria-label="Item kelengkapan" class="{{ $kelasInput }} min-w-0">
                                <button type="button" data-hapus-baris aria-label="Hapus item" class="{{ $kelasHapusBaris }}">{!! $ikonX !!}</button>
                            </div>
                        @endforeach
                    </div>

                    <!-- Keadaan kosong: tampil bila belum ada item. Tombol Tambah Item di bawahnya tetap menjadi satu-satunya aksi. -->
                    <div id="kelengkapan-kosong" class="{{ count($nilaiKelengkapan) ? 'hidden' : '' }} min-h-[9rem] flex-1 flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-gray-50/70 px-4 py-8 text-center transition-colors duration-200 hover:border-blue-300 hover:bg-blue-50/60 [&:not(.hidden)]:flex">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <p class="text-sm font-semibold text-gray-700">Belum ada item kelengkapan</p>
                        <p class="max-w-xs text-xs leading-relaxed text-gray-500">Boleh dikosongkan kalau alat ini tidak punya perlengkapan tambahan. Tekan Tambah Item untuk mulai mengisi.</p>
                    </div>

                    <template id="tpl-kelengkapan">
                        <div data-baris class="flex items-center gap-2">
                            <input type="text" name="kelengkapan[]" value="" maxlength="100" autocomplete="off" spellcheck="false" placeholder="Contoh: Baterai cadangan" aria-label="Item kelengkapan" class="{{ $kelasInput }} min-w-0">
                            <button type="button" data-hapus-baris aria-label="Hapus item" class="{{ $kelasHapusBaris }}">{!! $ikonX !!}</button>
                        </div>
                    </template>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" data-tambah="kelengkapan" class="{{ $kelasTambah }}">{!! $ikonPlus !!} Tambah Item</button>
                        <p class="text-xs text-gray-500 tabular-nums sm:text-right"><span id="hitung-kelengkapan">0</span> dari 20 item</p>
                    </div>

                    <p class="{{ $kelasInfo }} mt-4">
                        {!! $ikonInfo !!}
                        <span>Daftar ini tampil bagi peminjam dan dipakai admin untuk mencocokkan isi alat saat pengambilan dan pengembalian. Tulis nama item sejelas mungkin, hanya huruf, angka, dan spasi. Huruf pertama otomatis kapital dan sisanya huruf kecil.</span>
                    </p>
                </section>
            </div>

            <!-- Tombol aksi: satu baris penuh di bawah kedua kolom -->
            <div class="lg:col-span-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md active:border-blue-300 active:bg-blue-50 active:scale-[0.98]">
                    Batal
                </a>
                <button type="submit" id="btn-simpan"
                        class="w-full sm:w-auto sm:px-10 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold shadow-sm transition-all duration-200 enabled:cursor-pointer enabled:hover:bg-blue-700 enabled:hover:shadow-lg enabled:hover:shadow-blue-600/30 motion-safe:enabled:hover:-translate-y-0.5 enabled:active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed">
                    {{ $baru ? 'Tambah Alat' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>

        @unless ($baru)
            <!-- Form hapus dipisah dari form utama (form di dalam form tidak valid); tombolnya terhubung lewat atribut form="form-hapus" -->
            <form id="form-hapus" method="POST" action="{{ route('admin.alat.destroy', $alat->slug) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            @if ($bisaHapus)
                <dialog id="dialog-hapus" aria-labelledby="judul-dialog-hapus"
                        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-gray-900/50">
                    <div class="p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <h2 id="judul-dialog-hapus" class="mt-4 text-lg font-bold leading-snug text-gray-900 break-words">Hapus {{ $alat->nama_alat }}?</h2>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">Data alat dan foto unggahannya akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.</p>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50/70 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" id="tombol-batal-hapus"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 active:bg-blue-50 active:scale-[0.98]">
                            Batal
                        </button>
                        <button type="submit" form="form-hapus"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-red-600 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/30 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                            Ya, Hapus Alat
                        </button>
                    </div>
                </dialog>
            @endif
        @endunless
    </main>

    <!-- Footer admin (sama dengan halaman riwayat admin) -->
    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-center text-xs text-gray-600 sm:flex-row sm:px-6 sm:text-left lg:px-8 [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
            <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
            <p>&copy; {{ date('Y') }} Mulmedska SMK Negeri 5 Surakarta</p>
        </div>
    </footer>

    <script>
        // Bayangan navbar muncul saat halaman digulir
        (function () {
            var navbar = document.getElementById('navbar');
            function periksa() { navbar.classList.toggle('shadow-md', window.scrollY > 8); }
            window.addEventListener('scroll', periksa, { passive: true });
            periksa();
        })();

        // Pratinjau tiga foto (utama, kiri, kanan) yang baru dipilih
        (function () {
            ['foto', 'foto_kiri', 'foto_kanan'].forEach(function (kolom) {
                var input = document.getElementById(kolom);
                var gambar = document.getElementById('pratinjau-' + kolom);
                var kosong = document.getElementById('pratinjau-kosong-' + kolom);

                input.addEventListener('change', function () {
                    var berkas = input.files[0];
                    if (!berkas) return;
                    gambar.src = URL.createObjectURL(berkas);
                    gambar.classList.remove('hidden');
                    kosong.classList.add('hidden');
                });
            });
        })();

        // Penghitung karakter deskripsi
        (function () {
            var kolom = document.getElementById('deskripsi');
            var hitung = document.getElementById('hitung-deskripsi');
            function perbarui() { hitung.textContent = kolom.value.length; }
            kolom.addEventListener('input', perbarui);
            perbarui();
        })();

        // Teks saklar ketersediaan ikut berubah saat saklar digeser
        (function () {
            var saklar = document.getElementById('saklar-tersedia');
            var judul = document.getElementById('judul-tersedia');
            var ket = document.getElementById('ket-tersedia');

            function perbarui() {
                if (saklar.checked) {
                    judul.textContent = 'Alat tersedia untuk dipinjam';
                    ket.textContent = 'Peminjam bisa memilih alat ini dan mengajukan peminjaman.';
                } else {
                    judul.textContent = 'Alat tidak tersedia';
                    ket.textContent = 'Peminjam tidak bisa meminjam alat ini sampai ketersediaannya diaktifkan lagi.';
                }
            }
            saklar.addEventListener('change', perbarui);
        })();

        // Daftar kelengkapan: tambah dan hapus baris (maksimal 20) dengan penghitung item dan keadaan kosong.
        // Aturan pengetikan: hanya huruf, angka, dan spasi (simbol dan titik koma langsung terhapus saat diketik atau ditempel),
        // huruf pertama kapital dan sisanya huruf kecil, contoh "Baterai cadangan".
        (function () {
            var BATAS = 20;
            var wadah = document.getElementById('daftar-kelengkapan');
            var kosong = document.getElementById('kelengkapan-kosong');
            var hitung = document.getElementById('hitung-kelengkapan');
            var tombolTambah = document.querySelector('[data-tambah="kelengkapan"]');
            var SELEKTOR = 'input[name="kelengkapan[]"]';

            function perbarui() {
                var jumlah = wadah.children.length;
                hitung.textContent = jumlah;
                // Daftar disembunyikan saat kosong, diganti keadaan kosong
                wadah.classList.toggle('hidden', jumlah === 0);
                kosong.classList.toggle('hidden', jumlah > 0);
                tombolTambah.disabled = jumlah >= BATAS;
                tombolTambah.classList.toggle('opacity-50', jumlah >= BATAS);
                tombolTambah.classList.toggle('cursor-not-allowed', jumlah >= BATAS);
            }

            // Satu aturan untuk semua: buang selain huruf/angka/spasi, rapatkan spasi ganda, tanpa spasi di awal,
            // lalu huruf pertama kapital dan sisanya kecil. Spasi di akhir sengaja dibiarkan agar kata berikutnya bisa diketik.
            function rapikan(teks) {
                var t = teks
                    .replace(/[^\p{L}\p{N}\s]/gu, '')
                    .replace(/\s+/g, ' ')
                    .replace(/^ /, '');
                if (t === '') return t;
                return t.charAt(0).toLocaleUpperCase('id') + t.slice(1).toLocaleLowerCase('id');
            }

            // Kolom dirapikan dengan posisi kursor dijaga, supaya mengetik di tengah teks tidak melompat ke akhir
            function terapkan(kolom) {
                var lama = kolom.value;
                var baru = rapikan(lama);
                if (baru === lama) return;

                var kursor = kolom.selectionStart;
                if (kursor === null || kursor === undefined) kursor = lama.length;
                var posisi = rapikan(lama.slice(0, kursor)).length;
                kolom.value = baru;
                kolom.setSelectionRange(posisi, posisi);
            }

            tombolTambah.addEventListener('click', function () {
                if (wadah.children.length >= BATAS) return;
                wadah.appendChild(document.getElementById('tpl-kelengkapan').content.cloneNode(true));
                // Daftar harus tampil dulu sebelum kolom baru bisa difokuskan
                perbarui();
                var kolom = wadah.lastElementChild.querySelector('input');
                if (kolom) kolom.focus();
            });

            wadah.addEventListener('click', function (event) {
                var tombol = event.target.closest('[data-hapus-baris]');
                if (!tombol) return;
                tombol.closest('[data-baris]').remove();
                perbarui();
            });

            wadah.addEventListener('input', function (event) {
                if (!event.target.matches(SELEKTOR)) return;
                // Jangan ganggu keyboard HP yang sedang menyusun kata (komposisi)
                if (event.isComposing) return;
                terapkan(event.target);
            });

            // Setelah komposisi selesai, rapikan sekali lagi
            wadah.addEventListener('compositionend', function (event) {
                if (event.target.matches(SELEKTOR)) terapkan(event.target);
            });

            // Spasi di akhir dibuang saat kolom ditinggalkan
            wadah.addEventListener('focusout', function (event) {
                if (!event.target.matches(SELEKTOR)) return;
                event.target.value = rapikan(event.target.value).replace(/ +$/, '');
            });

            // Isian yang sudah ada (data lama atau bawaan) ikut dirapikan, mis. "Plat dudukan (quick release)" menjadi "Plat dudukan quick release"
            wadah.querySelectorAll(SELEKTOR).forEach(function (kolom) {
                kolom.value = rapikan(kolom.value).replace(/ +$/, '');
            });

            perbarui();
        })();

        // Dialog konfirmasi hapus alat (hanya ada di mode kelola)
        (function () {
            var dialog = document.getElementById('dialog-hapus');
            var buka = document.getElementById('tombol-buka-hapus');
            if (!dialog || !buka) return;

            buka.addEventListener('click', function () { dialog.showModal(); });
            document.getElementById('tombol-batal-hapus').addEventListener('click', function () { dialog.close(); });
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) dialog.close();
            });
        })();

        // Cegah klik ganda saat menyimpan
        (function () {
            var form = document.getElementById('form-kelola');
            var tombol = document.getElementById('btn-simpan');
            var teksAwal = tombol.textContent.trim();
            var terkirim = false;

            form.addEventListener('submit', function (event) {
                if (terkirim) {
                    event.preventDefault();
                    return;
                }
                terkirim = true;
                tombol.textContent = 'Menyimpan...';
                tombol.classList.add('opacity-70', 'pointer-events-none');
            });

            // Kalau pengguna menekan tombol Kembali di browser, tombol dipulihkan
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    terkirim = false;
                    tombol.textContent = teksAwal;
                    tombol.classList.remove('opacity-70', 'pointer-events-none');
                }
            });
        })();
    </script>
</body>
</html>