<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - SiLat</title>
    <link rel="icon" href="{{ asset('images/logo.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :where(a, button, input, select):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
        #cari:focus-visible { outline: none; }
    </style>
</head>
<body class="flex min-h-screen flex-col overflow-x-clip bg-gray-50 text-gray-900 antialiased">

@php
    // Aman kalau controller belum mengirim variabel ini
    $kata   = $kata ?? '';
    $status = $status ?? 'semua';
    $peran  = $peran ?? 'semua';
    $urut   = in_array($urut ?? 'terbaru', ['terbaru', 'terlama', 'nama_az', 'nama_za'], true) ? ($urut ?? 'terbaru') : 'terbaru';

    $totalSemua     = $totalSemua ?? 0;
    $jumlahMenunggu = $jumlahMenunggu ?? 0;
    $jumlahMintaHapus = $jumlahMintaHapus ?? 0;

    $filterAktif = $kata !== '' || $status !== 'semua' || $peran !== 'semua' || $urut !== 'terbaru';
    // Untuk kalimat info: urutan saja tidak dihitung sebagai penyaring
    $tanpaPenyaring = $kata === '' && $status === 'semua' && $peran === 'semua';

    $statusBadge = [
        'aktif'       => ['label' => 'Aktif',       'badge' => 'bg-green-100 text-green-700', 'dot' => 'bg-green-500'],
        'menunggu'    => ['label' => 'Menunggu',    'badge' => 'bg-blue-100 text-blue-700',   'dot' => 'bg-blue-500'],
        'nonaktif'    => ['label' => 'Nonaktif',    'badge' => 'bg-gray-100 text-gray-500',   'dot' => 'bg-gray-400'],
        'minta_hapus' => ['label' => 'Minta Hapus', 'badge' => 'bg-red-100 text-red-700',     'dot' => 'bg-red-500'],
    ];

    $labelStatusFilter = [
        'aktif'       => 'aktif',
        'menunggu'    => 'menunggu persetujuan',
        'nonaktif'    => 'nonaktif',
        'minta_hapus' => 'pengajuan hapus',
    ];

    $labelPeranFilter = [
        'siswa'         => 'siswa',
        'guru_karyawan' => 'guru/karyawan',
    ];

    // Bagian kalimat info saat ada penyaring (hanya bagian yang aktif yang dipasang)
    $bagianInfo = [];
    if ($status !== 'semua') {
        $bagianInfo[] = 'berstatus <span class="font-bold text-blue-600">' . e($labelStatusFilter[$status] ?? $status) . '</span>';
    }
    if ($peran !== 'semua') {
        $bagianInfo[] = 'sebagai <span class="font-bold text-blue-600">' . e($labelPeranFilter[$peran] ?? $peran) . '</span>';
    }
    if ($kata !== '') {
        $bagianInfo[] = 'dengan kata kunci <span class="font-bold text-blue-600">“' . e($kata) . '”</span>';
    }

    // Menu urutkan (ikon corong): klik baris yang sedang dipakai membalik arahnya
    $urutNama = in_array($urut, ['nama_az', 'nama_za'], true);
    $opsiUrut = [
        [
            'label'  => 'Nama',
            'aktif'  => $urutNama,
            'teks'   => $urut === 'nama_za' ? 'Z–A' : 'A–Z',
            'turun'  => $urut === 'nama_za',
            'tujuan' => $urut === 'nama_az' ? 'nama_za' : 'nama_az',
        ],
        [
            'label'  => 'Tanggal',
            'aktif'  => ! $urutNama,
            'teks'   => $urut === 'terlama' ? 'Terlama' : 'Terbaru',
            'turun'  => $urut !== 'terlama',
            'tujuan' => $urut === 'terbaru' ? 'terlama' : 'terbaru',
        ],
    ];
    foreach ($opsiUrut as $i => $o) {
        // Pindah urutan selalu kembali ke halaman 1 dan tetap membawa pencarian serta filter
        $opsiUrut[$i]['url'] = request()->fullUrlWithQuery(['urut' => $o['tujuan'], 'page' => null]);
    }

    $ikonFilter = 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z';
    $ikonNaik   = 'M5 10l7-7m0 0l7 7m-7-7v18';
    $ikonTurun  = 'M19 14l-7 7m0 0l-7-7m7 7V3';

    // Ikon tombol aksi
    $ikonCentang = 'M5 13l4 4L19 7';
    $ikonSilang  = 'M6 18L18 6M6 6l12 12';
    $ikonBlokir  = 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636';
    $ikonAktif   = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    $ikonSampah  = 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16';

    // Gaya tombol aksi
    $gayaAksi = [
        'utama'  => 'bg-blue-600 text-white shadow-sm hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/30',
        'hijau'  => 'bg-green-600 text-white shadow-sm hover:bg-green-700 hover:shadow-md hover:shadow-green-600/30',
        'netral' => 'border border-gray-300 bg-white text-gray-700 hover:border-amber-400 hover:bg-amber-50 hover:text-amber-700',
        'bahaya' => 'border border-red-200 bg-white text-red-600 hover:border-red-400 hover:bg-red-50',
    ];

    // Satu kali olah data untuk tabel (layar lebar) dan kartu (layar kecil)
    $baris = $users->getCollection()->map(function ($u) use ($statusBadge, $ikonCentang, $ikonSilang, $ikonBlokir, $ikonAktif, $ikonSampah) {
        $nama = $u->nama_lengkap;
        $kunciStatus = ($u->status_akun === 'minta_hapus' || $u->permintaan_hapus_pada) ? 'minta_hapus' : $u->status_akun;

        // Cek apakah user punya peminjaman aktif (sudah di-eager-load, tidak ada query tambahan)
        $adaPeminjamanAktif = $u->peminjaman->isNotEmpty();

        // Aksi yang tersedia mengikuti status akun (sama dengan aturan di controller)
        $aksi = match ($kunciStatus) {
            'menunggu' => [
                ['label' => 'Setujui', 'kartu' => 'Setujui', 'url' => route('admin.akun.setujui', $u), 'method' => 'POST', 'gaya' => 'utama', 'ikon' => $ikonCentang, 'konfirmasi' => null, 'dinonaktif' => false],
                ['label' => 'Tolak', 'kartu' => 'Tolak', 'url' => route('admin.akun.tolak', $u), 'method' => 'DELETE', 'gaya' => 'bahaya', 'ikon' => $ikonSilang, 'konfirmasi' => "Tolak pendaftaran {$nama}? Data akun ini akan dihapus permanen.", 'dinonaktif' => false],
            ],
            'aktif' => [
                ['label' => 'Nonaktifkan', 'kartu' => 'Nonaktifkan Akun', 'url' => route('admin.akun.nonaktifkan', $u), 'method' => 'PATCH', 'gaya' => 'netral', 'ikon' => $ikonBlokir, 'konfirmasi' => "Nonaktifkan akun {$nama}? Akun tidak bisa masuk sampai diaktifkan kembali.", 'dinonaktif' => false],
            ],
            'nonaktif' => [
                ['label' => 'Aktifkan', 'kartu' => 'Aktifkan', 'url' => route('admin.akun.aktifkan', $u), 'method' => 'PATCH', 'gaya' => 'hijau', 'ikon' => $ikonAktif, 'konfirmasi' => null, 'dinonaktif' => false],
                ['label' => 'Hapus', 'kartu' => 'Hapus', 'url' => route('admin.akun.hapus', $u), 'method' => 'DELETE', 'gaya' => 'bahaya', 'ikon' => $ikonSampah,
                 'konfirmasi' => $adaPeminjamanAktif ? null : "Hapus permanen akun {$nama}? Tindakan ini tidak dapat dibatalkan.",
                 'dinonaktif' => $adaPeminjamanAktif,
                 'tooltip' => $adaPeminjamanAktif ? "Tidak dapat dihapus: {$nama} masih memiliki peminjaman aktif." : null],
            ],
            'minta_hapus' => [
                ['label' => 'Hapus', 'kartu' => 'Setujui Hapus', 'url' => route('admin.akun.hapus', $u), 'method' => 'DELETE', 'gaya' => 'bahaya', 'ikon' => $ikonSampah,
                 'konfirmasi' => $adaPeminjamanAktif ? null : "Setujui permintaan hapus akun {$nama}? Data akun ini akan dihapus permanen.",
                 'dinonaktif' => $adaPeminjamanAktif,
                 'tooltip' => $adaPeminjamanAktif ? "Tidak dapat dihapus: {$nama} masih memiliki peminjaman aktif." : null],
                ['label' => 'Aktifkan', 'kartu' => 'Tolak & Aktifkan', 'url' => route('admin.akun.batalHapus', $u), 'method' => 'PATCH', 'gaya' => 'hijau', 'ikon' => $ikonAktif, 'konfirmasi' => "Tolak permintaan hapus akun {$nama} dan pulihkan status akun menjadi aktif?", 'dinonaktif' => false],
                ['label' => 'Nonaktifkan', 'kartu' => 'Nonaktifkan', 'url' => route('admin.akun.nonaktifkan', $u), 'method' => 'PATCH', 'gaya' => 'netral', 'ikon' => $ikonBlokir, 'konfirmasi' => "Tolak permintaan hapus dan nonaktifkan akun {$nama}? Akun tidak bisa masuk sampai diaktifkan kembali.", 'dinonaktif' => false],
            ],
            default => [],
        };

        return [
            'u'         => $u,
            'inisial'   => mb_strtoupper(mb_substr(trim((string) $nama), 0, 1)) ?: '?',
            'namaPeran' => $u->peran === 'siswa' ? 'Siswa' : 'Guru / Karyawan',
            'pillPeran' => $u->peran === 'siswa' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700',
            'kelas'     => $u->kelas_jurusan ?: '-',
            'tanggal'   => $u->created_at ? $u->created_at->locale('id')->translatedFormat('j M Y') : '-',
            'jam'       => $u->created_at ? $u->created_at->format('H:i') : '',
            'st'        => $statusBadge[$kunciStatus] ?? $statusBadge['nonaktif'],
            'waktuMinta'=> $u->permintaan_hapus_pada ? $u->permintaan_hapus_pada->locale('id')->translatedFormat('j M Y, H:i') : null,
            'aksi'      => $aksi,
        ];
    });

    // Halaman yang diminta sudah kosong (misalnya setelah akun terakhir di halaman itu dihapus)
    $halamanKosong = $users->isEmpty() && $users->currentPage() > 1;
@endphp

    <!-- Navbar: logo dan tombol Dashboard -->
    <header id="navbar" class="sticky top-0 z-50 border-b border-gray-200 bg-white transition-shadow duration-200">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-3 px-4 sm:gap-6 sm:px-6 lg:px-8">
            <a href="{{ route('admin.dashboard') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SiLat" class="h-14 w-14 shrink-0 transition-transform duration-300 sm:h-18 sm:w-18 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-3">
                <div class="min-w-0">
                    <span class="block text-2xl font-extrabold leading-tight text-blue-600 transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="block truncate text-xs font-semibold text-gray-700 transition-colors duration-300 group-hover:text-gray-900">Sistem Peminjaman Alat</span>
                </div>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex shrink-0 items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">
                <span><span class="hidden sm:inline">Kembali ke </span>Dashboard</span>
            </a>
        </div>
    </header>

    <main class="mx-auto w-full min-w-0 max-w-7xl flex-grow px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

        <!-- Pesan hasil tindakan -->
        @if (session('sukses_akun'))
            <div role="status" class="mb-6 flex items-start gap-2.5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                <svg class="h-5 w-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonCentang }}"/></svg>
                <span class="min-w-0 break-words font-medium">{{ session('sukses_akun') }}</span>
            </div>
        @endif

        @if (session('galat_akun'))
            <div role="alert" class="mb-6 flex items-start gap-2.5 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                <svg class="h-5 w-5 shrink-0 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="min-w-0 break-words font-medium">{{ session('galat_akun') }}</span>
            </div>
        @endif

        <!-- Kiri: judul. Kanan: pencarian dan filter (di bawah layar xl, filter turun ke bawah judul) -->
        <div class="mb-6 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="group min-w-0 xl:max-w-sm">
                <h1 class="text-2xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700 sm:text-3xl">Kelola Akun</h1>
                <p class="mt-1 text-sm text-gray-600 transition-colors duration-300 group-hover:text-gray-900">Kelola dan verifikasi akun siswa serta guru/karyawan yang terdaftar.</p>
            </div>

            <form id="filter-akun" method="GET" action="{{ route('admin.akun.kelola') }}" class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 lg:flex lg:w-auto lg:flex-wrap lg:items-end lg:justify-start xl:justify-end">
                {{-- Urutan dari menu di tabel ikut terbawa saat mencari atau memfilter --}}
                @if ($urut !== 'terbaru')
                    <input type="hidden" name="urut" value="{{ $urut }}">
                @endif

                @if ($filterAktif)
                    <a href="{{ route('admin.akun.kelola') }}" class="order-last inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-4 text-center text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95 sm:col-span-2 sm:justify-self-start lg:order-first lg:col-span-1 lg:justify-self-auto">Atur ulang</a>
                @endif

                <!-- Cari: nama, nomor induk, atau kelas/jurusan (hanya huruf, angka, dan spasi; huruf otomatis kapital) -->
                <div class="group min-w-0 sm:col-span-2 lg:col-span-1">
                    <label for="cari" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Cari akun</label>
                    <div class="flex h-11 w-full items-stretch overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 lg:w-56 motion-safe:hover:-translate-y-0.5">
                        <span class="flex select-none items-center border-r border-gray-200 bg-gray-50 px-3 text-gray-500 transition-colors duration-200 group-hover:bg-blue-50 group-hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                        </span>
                        <input id="cari" name="q" type="search" maxlength="50" value="{{ $kata }}" placeholder="Nama, NIS/NIP, kelas" autocomplete="off" autocapitalize="characters" spellcheck="false" enterkeyhint="search" class="h-full min-h-0 min-w-0 flex-1 border-0 bg-transparent px-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 md:text-sm">
                    </div>
                </div>

                <!-- Filter status -->
                <div class="group min-w-0">
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Filter status</label>
                    <div class="relative w-full transition-transform duration-200 lg:w-52 motion-safe:hover:-translate-y-0.5">
                        <select id="status" name="status" class="h-11 w-full cursor-pointer appearance-none truncate rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-base shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 md:text-sm">
                            <option value="semua" @selected($status === 'semua')>Semua Status</option>
                            <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                            <option value="menunggu" @selected($status === 'menunggu')>Menunggu Persetujuan</option>
                            <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                            <option value="minta_hapus" @selected($status === 'minta_hapus')>Minta Hapus</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500 transition-colors duration-200 group-hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>

                <!-- Filter peran -->
                <div class="group min-w-0">
                    <label for="peran" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Filter peran</label>
                    <div class="relative w-full transition-transform duration-200 lg:w-52 motion-safe:hover:-translate-y-0.5">
                        <select id="peran" name="peran" class="h-11 w-full cursor-pointer appearance-none truncate rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-base shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 md:text-sm">
                            <option value="semua" @selected($peran === 'semua')>Semua Peran</option>
                            <option value="siswa" @selected($peran === 'siswa')>Siswa</option>
                            <option value="guru_karyawan" @selected($peran === 'guru_karyawan')>Guru / Karyawan</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500 transition-colors duration-200 group-hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </form>
        </div>

        <!-- Pengingat pendaftar yang belum diproses (hilang saat sedang melihat daftar menunggu) -->
        @if ($jumlahMenunggu > 0 && $status !== 'menunggu')
            <a href="{{ route('admin.akun.kelola', ['status' => 'menunggu']) }}" class="group mb-4 flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800 transition-all duration-200 hover:border-blue-400 hover:shadow-md active:bg-blue-100 motion-safe:hover:-translate-y-0.5">
                <svg class="h-5 w-5 shrink-0 text-blue-600 transition-transform duration-200 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="min-w-0 flex-1">{{ $jumlahMenunggu }} pendaftar menunggu persetujuan Anda.</span>
                <span class="shrink-0 underline-offset-2 group-hover:underline">Lihat</span>
            </a>
        @endif

        @if ($jumlahMintaHapus > 0 && $status !== 'minta_hapus')
            <a href="{{ route('admin.akun.kelola', ['status' => 'minta_hapus']) }}" class="group mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 transition-all duration-200 hover:border-red-400 hover:shadow-md active:bg-red-100 motion-safe:hover:-translate-y-0.5">
                <svg class="h-5 w-5 shrink-0 text-red-600 transition-transform duration-200 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span class="min-w-0 flex-1">{{ $jumlahMintaHapus }} pengguna mengajukan penghapusan akun.</span>
                <span class="shrink-0 underline-offset-2 group-hover:underline">Lihat</span>
            </a>
        @endif

        @if ($users->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center transition-all duration-300 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-lg active:border-blue-400 active:bg-blue-50/50 motion-safe:hover:-translate-y-1">
                <p class="font-semibold text-gray-700">
                    @if ($halamanKosong)
                        Halaman ini sudah tidak berisi akun.
                    @elseif ($tanpaPenyaring)
                        Belum ada akun user yang terdaftar.
                    @else
                        Belum ada akun yang cocok dengan pencarian atau filter ini.
                    @endif
                </p>
                @if ($halamanKosong)
                    <a href="{{ $users->url(1) }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">Ke halaman pertama</a>
                @elseif ($filterAktif)
                    <a href="{{ route('admin.akun.kelola') }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">Atur ulang</a>
                @endif
            </section>
        @else
            <!-- Kalimat info. Di bawah layar xl tombol Urutkan ada di sini, karena tabel (dan kepala tabelnya) hanya tampil di xl ke atas -->
            <div class="mb-3 flex items-start justify-between gap-3">
                <p class="group flex min-w-0 items-start gap-2 text-sm text-gray-600 transition-colors duration-200 hover:text-gray-900">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="min-w-0">
                        @if ($tanpaPenyaring)
                            Menampilkan {{ $users->total() }} akun <span class="font-bold text-blue-600">seluruh user</span>.
                        @else
                            Menampilkan {{ $users->total() }} dari {{ $totalSemua }} akun {!! implode(' ', $bagianInfo) !!}.
                        @endif
                    </span>
                </p>

                <div class="relative shrink-0 xl:hidden" data-menu-urut>
                    <button type="button" data-menu-tombol aria-haspopup="true" aria-expanded="false" class="inline-flex h-9 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:border-blue-500 hover:text-blue-700 hover:shadow-md active:scale-95 motion-safe:hover:-translate-y-0.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonFilter }}"/></svg>
                        Urutkan
                    </button>
                    <div data-menu-panel class="absolute right-0 top-full z-30 mt-2 hidden w-64 max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 bg-white p-1.5 shadow-xl">
                        <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Urutkan berdasarkan</p>
                        @foreach ($opsiUrut as $o)
                            <a href="{{ $o['url'] }}" @if ($o['aktif']) aria-current="true" @endif class="flex items-center justify-between gap-4 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-150 {{ $o['aktif'] ? 'bg-blue-50 text-blue-700' : 'text-gray-800 hover:bg-blue-50 hover:text-blue-700 active:bg-blue-50' }}">
                                <span>{{ $o['label'] }}</span>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold">{{ $o['teks'] }}
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $o['turun'] ? $ikonTurun : $ikonNaik }}"/></svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tabel untuk layar lebar (xl ke atas) -->
            <div class="hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 xl:block">
                <table class="w-full min-w-[60rem] text-sm">
                    <caption class="sr-only">Daftar akun siswa dan guru / karyawan</caption>
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs text-gray-500">
                            <th scope="col" class="rounded-tl-2xl bg-gray-50 px-5 py-3 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Nomor Induk</th>
                            <th scope="col" class="bg-gray-50 px-4 py-3 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Nama</th>
                            <th scope="col" class="bg-gray-50 px-4 py-3 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Kelas / Jurusan</th>
                            <th scope="col" class="bg-gray-50 px-4 py-3 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Mendaftar</th>
                            <th scope="col" class="bg-gray-50 px-4 py-3 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Status</th>
                            <th scope="col" class="rounded-tr-2xl bg-gray-50 px-5 py-3 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">
                                <div class="flex items-center justify-between gap-3">
                                    <span>Aksi</span>
                                    <div class="relative" data-menu-urut>
                                        <button type="button" data-menu-tombol aria-haspopup="true" aria-expanded="false" aria-label="Urutkan daftar akun" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition-all duration-200 hover:border-blue-500 hover:text-blue-700 hover:shadow-md active:scale-95 motion-safe:hover:-translate-y-0.5">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonFilter }}"/></svg>
                                        </button>
                                        <div data-menu-panel class="absolute right-0 top-full z-30 mt-2 hidden w-64 rounded-xl border border-gray-200 bg-white p-1.5 text-left font-normal shadow-xl">
                                            <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Urutkan berdasarkan</p>
                                            @foreach ($opsiUrut as $o)
                                                <a href="{{ $o['url'] }}" @if ($o['aktif']) aria-current="true" @endif class="flex items-center justify-between gap-4 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-150 {{ $o['aktif'] ? 'bg-blue-50 text-blue-700' : 'text-gray-800 hover:bg-blue-50 hover:text-blue-700 active:bg-blue-50' }}">
                                                    <span>{{ $o['label'] }}</span>
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold">{{ $o['teks'] }}
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $o['turun'] ? $ikonTurun : $ikonNaik }}"/></svg>
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 [&>tr:last-child>td:first-child]:rounded-bl-2xl [&>tr:last-child>td:last-child]:rounded-br-2xl">
                        @foreach ($baris as $b)
                            @php $u = $b['u']; @endphp
                            <tr class="group transition-colors duration-200 hover:bg-blue-50 active:bg-blue-50">
                                <td class="whitespace-nowrap px-5 py-5">
                                    <span class="inline-block font-bold text-blue-700 transition-transform duration-300 motion-safe:group-hover:translate-x-1.5">{{ $u->nomor_induk }}</span>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        @if ($u->foto_url)
                                            <div class="relative h-10 w-10 shrink-0">
                                                <img src="{{ $u->foto_url }}" alt="Foto {{ $u->nama_lengkap }}" loading="lazy" class="h-10 w-10 rounded-xl object-cover ring-1 ring-gray-200 transition-all duration-300 group-hover:ring-blue-400 motion-safe:group-hover:scale-105" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <span style="display:none;" class="h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-bold text-blue-600" aria-hidden="true">{{ $b['inisial'] }}</span>
                                            </div>
                                        @else
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-bold text-blue-600 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white motion-safe:group-hover:scale-105" aria-hidden="true">{{ $b['inisial'] }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="max-w-[14rem] break-words font-semibold leading-tight text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $u->nama_lengkap }}</p>
                                            <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $b['pillPeran'] }}">{{ $b['namaPeran'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kelas'] }}</td>
                                <td class="whitespace-nowrap px-4 py-5">
                                    <p class="font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['tanggal'] }}</p>
                                    @if ($b['jam'] !== '')
                                        <p class="mt-0.5 text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $b['jam'] }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-5">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-110 {{ $b['st']['badge'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $b['st']['dot'] }}"></span>{{ $b['st']['label'] }}
                                    </span>
                                    @if ($b['waktuMinta'])
                                        <p class="mt-1 text-[11px] font-medium text-red-600">Diajukan {{ $b['waktuMinta'] }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-5">
                                    <div class="flex items-center gap-2">
                                        @forelse ($b['aksi'] as $a)
                                            @php $nonaktif = $a['dinonaktif'] ?? false; @endphp
                                            <form method="POST" action="{{ $a['url'] }}" class="form-aksi"@if (!$nonaktif && $a['konfirmasi']) data-konfirmasi="{{ $a['konfirmasi'] }}"@endif>
                                                @csrf
                                                @method($a['method'])
                                                <button type="submit" @disabled($nonaktif)
                                                    @if (isset($a['tooltip']) && $a['tooltip']) title="{{ $a['tooltip'] }}" @endif
                                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 {{ $gayaAksi[$a['gaya']] }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $a['ikon'] }}"/></svg>
                                                    {{ $a['label'] }}
                                                </button>
                                            </form>
                                        @empty
                                            <span class="text-gray-400">-</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Kartu untuk layar kecil dan sedang (di bawah xl) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:hidden">
                @foreach ($baris as $b)
                    @php $u = $b['u']; @endphp
                    <article class="group flex min-w-0 flex-col rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:shadow-2xl hover:shadow-blue-900/15 active:border-blue-400 active:bg-blue-50 motion-safe:hover:-translate-y-1.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $b['pillPeran'] }}">{{ $b['namaPeran'] }}</span>
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-105 {{ $b['st']['badge'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $b['st']['dot'] }}"></span>{{ $b['st']['label'] }}
                            </span>
                        </div>

                        <div class="mt-3 flex min-w-0 items-center gap-3 border-b border-gray-100 pb-3 transition-colors duration-200 group-hover:border-blue-200">
                            @if ($u->foto_url)
                                <div class="relative h-10 w-10 shrink-0">
                                    <img src="{{ $u->foto_url }}" alt="Foto {{ $u->nama_lengkap }}" loading="lazy" class="h-10 w-10 rounded-xl object-cover ring-1 ring-gray-200 transition-all duration-300 group-hover:ring-blue-400" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span style="display:none;" class="h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-bold text-blue-600" aria-hidden="true">{{ $b['inisial'] }}</span>
                                </div>
                            @else
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-bold text-blue-600 transition-colors duration-300 group-hover:bg-blue-600 group-hover:text-white" aria-hidden="true">{{ $b['inisial'] }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="break-words font-semibold leading-snug text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $u->nama_lengkap }}</p>
                                <p class="mt-0.5 break-words text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $u->peran === 'siswa' ? 'NIS' : 'NIP' }} {{ $u->nomor_induk }}</p>
                            </div>
                        </div>

                        <dl class="mb-4 mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="min-w-0">
                                <dt class="leading-snug text-gray-500">Kelas / Jurusan</dt>
                                <dd class="mt-0.5 break-words font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kelas'] }}</dd>
                            </div>
                            <div class="min-w-0">
                                <dt class="leading-snug text-gray-500">Mendaftar</dt>
                                <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['tanggal'] }}</dd>
                            </div>
                        </dl>

                        @if (count($b['aksi']))
                            <!-- mt-auto: tombol sejajar di dasar kartu walaupun tinggi isi kartu berbeda -->
                            <div class="mt-auto flex gap-2 border-t border-gray-100 pt-4 transition-colors duration-200 group-hover:border-blue-200">
                                @foreach ($b['aksi'] as $a)
                                    @php $nonaktif = $a['dinonaktif'] ?? false; @endphp
                                    <form method="POST" action="{{ $a['url'] }}" class="form-aksi min-w-0 flex-1"@if (!$nonaktif && $a['konfirmasi']) data-konfirmasi="{{ $a['konfirmasi'] }}"@endif>
                                        @csrf
                                        @method($a['method'])
                                        <button type="submit" @disabled($nonaktif)
                                            @if (isset($a['tooltip']) && $a['tooltip']) title="{{ $a['tooltip'] }}" @endif
                                            class="inline-flex w-full items-center justify-center rounded-lg px-3 py-2.5 text-sm font-semibold transition-all duration-200 hover:-translate-y-0.5 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 {{ $gayaAksi[$a['gaya']] }}">{{ $a['kartu'] }}</button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            @if ($users->hasPages())
                <div class="mt-6 overflow-x-auto [&_a]:transition-all [&_a]:duration-200 [&_a:hover]:shadow-md motion-safe:[&_a:hover]:-translate-y-0.5">{{ $users->links() }}</div>
            @endif
        @endif
    </main>

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

        // Pencarian: hanya huruf, angka, dan spasi; huruf otomatis menjadi kapital.
        // Dikirim otomatis setelah berhenti mengetik; dropdown langsung terkirim.
        const formAkun = document.getElementById('filter-akun');
        const cariAkun = document.getElementById('cari');
        let jedaPencarian;

        // Posisi kursor dijaga supaya mengetik di tengah teks tidak melompat ke akhir.
        function bersihkanPencarian() {
            var lama = cariAkun.value;
            var baru = lama.toUpperCase().replace(/[^A-Z0-9 ]/g, '');
            if (baru === lama) return;

            var kursor = cariAkun.selectionStart;
            if (kursor === null || kursor === undefined) kursor = lama.length;
            var posisi = lama.slice(0, kursor).toUpperCase().replace(/[^A-Z0-9 ]/g, '').length;
            cariAkun.value = baru;
            cariAkun.setSelectionRange(posisi, posisi);
        }

        cariAkun.addEventListener('input', function (event) {
            // Jangan ganggu keyboard HP yang sedang menyusun kata (komposisi)
            if (event.isComposing) return;
            bersihkanPencarian();

            // Tandai bahwa halaman dimuat ulang karena mengetik (bukan karena mengganti filter)
            try { sessionStorage.setItem('fokusCariAkun', '1'); } catch (e) {}
            clearTimeout(jedaPencarian);
            jedaPencarian = setTimeout(function () { formAkun.requestSubmit(); }, 450);
        });

        // Setelah komposisi selesai, bersihkan sekali lagi
        cariAkun.addEventListener('compositionend', bersihkanPencarian);

        formAkun.querySelectorAll('select').forEach(function (pilihan) {
            pilihan.addEventListener('change', function () {
                try { sessionStorage.removeItem('fokusCariAkun'); } catch (e) {}
                formAkun.requestSubmit();
            });
        });

        // Setelah dimuat ulang karena mengetik, kursor kembali ke akhir teks.
        // Tidak dilakukan saat mengganti filter agar keyboard HP tidak muncul sendiri.
        try {
            if (sessionStorage.getItem('fokusCariAkun') === '1' && cariAkun.value !== '') {
                cariAkun.focus();
                cariAkun.setSelectionRange(cariAkun.value.length, cariAkun.value.length);
            }
            sessionStorage.removeItem('fokusCariAkun');
        } catch (e) {}

        // Menu urutkan: buka/tutup lewat tombol, tutup saat klik di luar atau tekan Esc
        document.querySelectorAll('[data-menu-urut]').forEach(function (menu) {
            var tombol = menu.querySelector('[data-menu-tombol]');
            var panel = menu.querySelector('[data-menu-panel]');

            function tutup() {
                panel.classList.add('hidden');
                tombol.setAttribute('aria-expanded', 'false');
            }

            tombol.addEventListener('click', function (event) {
                event.stopPropagation();
                var tertutup = panel.classList.contains('hidden');
                panel.classList.toggle('hidden', !tertutup);
                tombol.setAttribute('aria-expanded', tertutup ? 'true' : 'false');
            });
            document.addEventListener('click', function (event) {
                if (!menu.contains(event.target)) tutup();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
                    tutup();
                    tombol.focus();
                }
            });
        });

        // Tombol aksi: minta konfirmasi untuk tindakan berisiko, lalu kunci tombol agar tidak terkirim dua kali
        document.querySelectorAll('.form-aksi').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var pesan = form.dataset.konfirmasi;
                if (pesan && !window.confirm(pesan)) {
                    event.preventDefault();
                    return;
                }
                form.querySelectorAll('button').forEach(function (tombol) { tombol.disabled = true; });
            });
        });

        // Kalau pengguna menekan Kembali, tombol yang terkunci dibuka lagi
        window.addEventListener('pageshow', function (event) {
            if (!event.persisted) return;
            document.querySelectorAll('.form-aksi button').forEach(function (tombol) { tombol.disabled = false; });
        });
    </script>
</body>
</html>