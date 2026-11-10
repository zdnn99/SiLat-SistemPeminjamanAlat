<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen overflow-x-clip">

@php
    use Carbon\Carbon;

    // Aman kalau controller belum mengirim variabel ini
    $statusAktif = $statusAktif ?? 'semua';
    $urutAktif   = $urutAktif ?? 'terbaru';
    $filterAktif = $statusAktif !== 'semua' || $urutAktif !== 'terbaru';

    // Total seluruh pengajuan milik user ini (tanpa filter) untuk kalimat info.
    // Bisa dikirim dari controller sebagai $totalSemua
    $totalSemua = $totalSemua ?? \App\Models\Peminjaman::where('user_id', auth()->id())->count();

    // Nama status untuk kalimat info (huruf kecil)
    $labelFilter = [
        'menunggu_persetujuan' => 'menunggu persetujuan',
        'menunggu_pengambilan' => 'menunggu pengambilan',
        'dipinjam'             => 'sedang dipinjam',
        'selesai'              => 'selesai',
        'ditolak'              => 'ditolak',
    ];

    // TODO: hapus setelah AlatSeeder dibuat (foto dan subjudul akan diambil dari model Alat).
    // Sama dengan data di dashboard: foto utama = public/images/alat/{foto}1.jpg
    $katalog = [
        'sony-mirrorless'       => ['foto' => 'sony',     'sub' => 'Sony A6000',                    'ikon' => 'kamera'],
        'canon-dslr'            => ['foto' => 'cannon',   'sub' => 'Canon EOS 1200D',               'ikon' => 'kamera'],
        'drone-dokumentasi-1'   => ['foto' => 'drone1-',  'sub' => 'DJI Mini 2',                    'ikon' => 'drone'],
        'drone-dokumentasi-2'   => ['foto' => 'drone2-',  'sub' => 'DJI Mavic Air 2',               'ikon' => 'drone'],
        'handycam'              => ['foto' => 'handycam', 'sub' => 'Sony HDR-CX405',                'ikon' => 'video'],
        'gimbal-stabilizer'     => ['foto' => 'gimbal',   'sub' => 'Rekaman Mulus Saat Bergerak',   'ikon' => 'gimbal'],
        'tripod'                => ['foto' => 'tripod',   'sub' => 'Penopang Kamera Dan Handphone', 'ikon' => 'tripod'],
        'handphone-dokumentasi' => ['foto' => 'ipone',    'sub' => 'iPhone 13',                     'ikon' => 'hp'],
    ];

    $ikonAlat = [
        'kamera' => '<path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>',
        'video'  => '<path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'hp'     => '<path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'drone'  => '<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="5" cy="18" r="2.2"/><circle cx="19" cy="18" r="2.2"/><path d="M7 7.5l3 3m7-3l-3 3M7 16.5l3-3m7 3l-3-3"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.5"/>',
        'gimbal' => '<rect x="7" y="4" width="10" height="5" rx="1.5"/><path d="M4 6.5h3m10 0h3M12 9v4"/><rect x="9.5" y="13" width="5" height="8" rx="2"/>',
        'tripod' => '<rect x="8.5" y="2.5" width="7" height="4" rx="1"/><path d="M12 6.5V11m0 0l-5 10m5-10v10m0-10l5 10"/>',
    ];

    // Warna sama dengan dashboard: Menunggu abu-abu, Dipinjam biru, Selesai hijau, Ditolak merah
    $statusMap = [
        'menunggu_persetujuan' => ['label' => 'Menunggu Persetujuan', 'badge' => 'bg-gray-100 text-gray-700',     'dot' => 'bg-gray-500'],
        'menunggu_pengambilan' => ['label' => 'Menunggu Pengambilan', 'badge' => 'bg-amber-100 text-amber-800',   'dot' => 'bg-amber-500'],
        'dipinjam'             => ['label' => 'Sedang Dipinjam',      'badge' => 'bg-blue-100 text-blue-700',     'dot' => 'bg-blue-500'],
        'selesai'              => ['label' => 'Selesai',              'badge' => 'bg-green-100 text-green-700',   'dot' => 'bg-green-500'],
        'ditolak'              => ['label' => 'Ditolak',              'badge' => 'bg-red-100 text-red-700',       'dot' => 'bg-red-500'],
    ];

    $hariIni = Carbon::now()->startOfDay();

    // Satu kali olah data untuk tabel (layar lebar) dan kartu (layar kecil)
    $baris = $peminjaman->getCollection()->map(function ($p) use ($katalog, $statusMap, $hariIni) {
        $slug = $p->alat->slug;
        $al = $katalog[$slug] ?? ['foto' => $slug, 'sub' => $p->alat->deskripsi ?? '', 'ikon' => 'kamera'];
        $al['nama'] = $p->alat->nama_alat;

        $pinjam  = Carbon::parse($p->tanggal_pinjam)->startOfDay();
        $kembali = Carbon::parse($p->tanggal_kembali)->startOfDay();
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
        $warnaPengembalian = match (true) {
            $terlambat => 'font-semibold text-red-700',
            $p->status === 'selesai' => 'font-semibold text-green-700',
            $p->status === 'dipinjam' => 'text-gray-600',
            default => 'text-gray-400',
        };
        $ket = match (true) {
            $p->status === 'menunggu_persetujuan'      => 'Menunggu keputusan admin',
            $p->status === 'menunggu_pengambilan'       => $p->catatan_admin ?: 'Menunggu pengambilan alat',
            $terlambat                                  => 'Terlambat ' . abs($sisa) . ' hari, segera kembalikan',
            $p->status === 'dipinjam' && $sisa === 0    => 'Kembalikan hari ini',
            $p->status === 'dipinjam'                   => 'Kembali ' . $sisa . ' hari lagi',
            $p->status === 'selesai'                    => $p->kondisi_alat_kembali ? 'Dikembalikan, kondisi ' . mb_strtolower($p->kondisi_alat_kembali) : 'Sudah dikembalikan',
            default                                     => $p->catatan_admin ? $p->catatan_admin : 'Pengajuan tidak disetujui',
        };

        return [
            'alat'               => $al,
            'tampil'             => $statusMap[$p->status],
            'pinjam_f'           => $pinjam->locale('id')->translatedFormat('j M Y'),
            'kembali_f'          => $kembali->locale('id')->translatedFormat('j M Y'),
            'pengembalian'       => $pengembalian,
            'pengembalian_warna' => $warnaPengembalian,
            'ket_teks'           => $ket,
            // ?dari=riwayat memberi tahu halaman detail bahwa user datang dari riwayat (tombol kembali menyesuaikan)
            'detail_url'         => route('peminjaman.show', $p) . '?dari=riwayat',
        ];
    });
@endphp

    <!-- Navbar: logo dan tombol Dashboard -->
    <header id="navbar" class="sticky top-0 z-50 border-b border-gray-200 bg-white transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-3 sm:gap-6">
            <a href="{{ route('dashboard') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="h-14 w-14 shrink-0 transition-transform duration-300 sm:h-18 sm:w-18 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-3">
                <div class="min-w-0">
                    <span class="block text-2xl font-extrabold leading-tight text-blue-600 transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="block truncate text-xs font-semibold text-gray-700 transition-colors duration-300 group-hover:text-gray-900">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <a href="{{ route('dashboard') }}#peminjaman-saya" class="inline-flex shrink-0 items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">
                <span><span class="hidden sm:inline">Kembali ke </span>Dashboard</span>
            </a>
        </div>
    </header>

    <main class="mx-auto w-full min-w-0 max-w-7xl flex-grow px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

        <!-- Kiri: judul dan subjudul. Kanan: filter (di bawah layar xl, filter turun ke bawah judul) -->
        <div class="mb-6 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="group min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700 sm:text-3xl">Riwayat Peminjaman</h1>
                <p class="mt-1 text-sm text-gray-600 transition-colors duration-300 group-hover:text-gray-900">Semua pengajuan peminjaman alat yang pernah kamu buat.</p>
            </div>

            <form method="GET" action="{{ route('peminjaman.riwayat') }}" class="grid w-full grid-cols-1 gap-3 sm:flex sm:w-auto sm:flex-wrap sm:items-end sm:justify-start xl:justify-end">
                @if ($filterAktif)
                    <a href="{{ route('peminjaman.riwayat') }}" class="order-last inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-4 text-center text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95 sm:order-first">Atur ulang</a>
                @endif

                <div class="group min-w-0">
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Filter status</label>
                    <div class="relative w-full transition-transform duration-200 sm:w-60 md:w-52 motion-safe:hover:-translate-y-0.5">
                        <select id="status" name="status" onchange="this.form.requestSubmit()" class="h-11 w-full cursor-pointer appearance-none truncate rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="semua" @selected($statusAktif === 'semua')>Semua Status</option>
                            <option value="menunggu_persetujuan" @selected($statusAktif === 'menunggu_persetujuan')>Menunggu Persetujuan</option>
                            <option value="menunggu_pengambilan" @selected($statusAktif === 'menunggu_pengambilan')>Menunggu Pengambilan</option>
                            <option value="dipinjam" @selected($statusAktif === 'dipinjam')>Sedang Dipinjam</option>
                            <option value="selesai" @selected($statusAktif === 'selesai')>Selesai</option>
                            <option value="ditolak" @selected($statusAktif === 'ditolak')>Ditolak</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500 transition-colors duration-200 group-hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>

                <div class="group min-w-0">
                    <label for="urut" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Urutkan tanggal</label>
                    <div class="relative w-full transition-transform duration-200 sm:w-60 md:w-52 motion-safe:hover:-translate-y-0.5">
                        <select id="urut" name="urut" onchange="this.form.requestSubmit()" class="h-11 w-full cursor-pointer appearance-none truncate rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="terbaru" @selected($urutAktif === 'terbaru')>Terbaru ke terlama</option>
                            <option value="terlama" @selected($urutAktif === 'terlama')>Terlama ke terbaru</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500 transition-colors duration-200 group-hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </form>
        </div>

        @if ($baris->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center transition-all duration-300 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-lg active:border-blue-400 active:bg-blue-50/50 motion-safe:hover:-translate-y-1">
                @if ($filterAktif)
                    <p class="font-semibold text-gray-700">Tidak ada riwayat yang cocok dengan filter ini.</p>
                    <a href="{{ route('peminjaman.riwayat') }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">Atur ulang</a>
                @else
                    <p class="font-semibold text-gray-700">Belum ada riwayat peminjaman.</p>
                    <a href="{{ route('dashboard') }}#peralatan" class="mt-3 inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/40 active:scale-[0.98]">Pilih Alat Sekarang</a>
                @endif
            </section>
        @else
            <p class="group mb-3 flex items-start gap-2 text-sm text-gray-600 transition-colors duration-200 hover:text-gray-900">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    @if ($statusAktif === 'semua')
                        Menampilkan {{ $peminjaman->total() }} pengajuan yang pernah kamu buat <span class="font-bold text-blue-600">semua status</span>.
                    @else
                        Menampilkan {{ $peminjaman->total() }} dari {{ $totalSemua }} pengajuan <span class="font-bold text-blue-600">{{ $labelFilter[$statusAktif] ?? $statusAktif }}</span>.
                    @endif
                </span>
            </p>

            <!-- Tabel untuk layar lebar (xl ke atas) -->
            <div class="hidden overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 xl:block">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[60rem] text-sm">
                        <caption class="sr-only">Riwayat peminjaman saya</caption>
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr class="text-left text-xs text-gray-500">
                                <th scope="col" class="rounded-tl-2xl bg-gray-50 px-6 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Alat</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Mulai Pinjam</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Batas Pengembalian</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Pengembalian</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Status</th>
                                <th scope="col" class="rounded-tr-2xl bg-gray-50 px-6 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($baris as $b)
                                @php $al = $b['alat']; @endphp
                                <tr class="baris-pinjam group cursor-pointer transition-colors duration-200 hover:bg-blue-50 active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500" tabindex="0" aria-label="Lihat detail peminjaman {{ $al['nama'] }}" data-url="{{ $b['detail_url'] }}">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3.5 transition-transform duration-300 motion-safe:group-hover:translate-x-1.5">
                                            <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-gray-100 bg-white transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                                <img src="{{ asset('images/alat/' . $al['foto'] . '1.jpg') }}" alt="" loading="lazy"
                                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                                     class="h-full w-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                                <div class="absolute inset-0 hidden items-center justify-center bg-blue-50 text-blue-400 [&:not(.hidden)]:flex">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$al['ikon']] !!}</svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $al['nama'] }}</p>
                                                <p class="text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $al['sub'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam_f'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali_f'] }}</td>
                                    <td class="min-w-[8rem] px-4 py-5 {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</td>
                                    <td class="px-4 py-5">
                                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-110 {{ $b['tampil']['badge'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $b['tampil']['dot'] }}"></span>
                                            {{ $b['tampil']['label'] }}
                                        </span>
                                    </td>
                                    <td class="max-w-[15rem] px-6 py-5">
                                        <p class="line-clamp-2 break-words text-sm font-semibold leading-snug text-gray-800 transition-colors duration-200 group-hover:text-blue-700" title="{{ $b['ket_teks'] }}">{{ $b['ket_teks'] }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kartu untuk layar kecil dan sedang (di bawah xl) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:hidden">
                @foreach ($baris as $b)
                    @php $al = $b['alat']; @endphp
                    <article class="baris-pinjam group min-w-0 cursor-pointer rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:shadow-2xl hover:shadow-blue-900/15 active:scale-[0.98] active:border-blue-400 active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 motion-safe:hover:-translate-y-1.5" tabindex="0" aria-label="Lihat detail peminjaman {{ $al['nama'] }}" data-url="{{ $b['detail_url'] }}">
                        <div class="flex items-center gap-3">
                            <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-gray-100 bg-white transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                <img src="{{ asset('images/alat/' . $al['foto'] . '1.jpg') }}" alt="" loading="lazy"
                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                     class="h-full w-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                <div class="absolute inset-0 hidden items-center justify-center bg-blue-50 text-blue-400 [&:not(.hidden)]:flex">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$al['ikon']] !!}</svg>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold leading-snug text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $al['nama'] }}</p>
                                <p class="text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $al['sub'] }}</p>
                            </div>
                        </div>

                        <span class="mt-3 inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-105 {{ $b['tampil']['badge'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $b['tampil']['dot'] }}"></span>
                            {{ $b['tampil']['label'] }}
                        </span>

                        <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 text-xs transition-colors duration-200 group-hover:border-blue-200">
                            <div>
                                <dt class="leading-snug text-gray-500">Mulai Pinjam</dt>
                                <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam_f'] }}</dd>
                            </div>
                            <div>
                                <dt class="leading-snug text-gray-500">Batas Kembali</dt>
                                <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali_f'] }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="leading-snug text-gray-500">Pengembalian</dt>
                                <dd class="mt-0.5 {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</dd>
                            </div>
                        </dl>

                        <p class="mt-3 break-words text-sm font-semibold leading-snug text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['ket_teks'] }}</p>
                    </article>
                @endforeach
            </div>

            @if ($peminjaman->hasPages())
                <div class="mt-6 overflow-x-auto [&_a]:transition-all [&_a]:duration-200 [&_a:hover]:shadow-md motion-safe:[&_a:hover]:-translate-y-0.5">
                    {{ $peminjaman->links() }}
                </div>
            @endif
        @endif
    </main>

    <!-- Footer ringkas (sama dengan halaman form peminjaman) -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-center text-xs text-gray-600 sm:text-left [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
            <p>Ada kendala dengan pengajuanmu?<span class="mt-0.5 block font-bold text-blue-600">Temui admin Ekskul Multimedia secara langsung.</span></p>
            <p>&copy; {{ date('Y') }} SiLat Multimedia &middot; Mulmedska SMK Negeri 5 Surakarta</p>
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

        // Baris / kartu bisa diklik dan bisa dibuka dengan Enter atau Spasi
        document.querySelectorAll('.baris-pinjam').forEach(function (baris) {
            baris.addEventListener('click', function () {
                window.location.href = baris.dataset.url;
            });
            baris.addEventListener('keydown', function (event) {
                if (event.target !== baris) return;
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    window.location.href = baris.dataset.url;
                }
            });
        });
    </script>
</body>
</html>