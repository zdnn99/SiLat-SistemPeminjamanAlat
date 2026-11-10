<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - SiLat</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :where(a, button, input, select):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
    </style>
</head>
<body class="flex min-h-screen flex-col overflow-x-clip bg-gray-50 text-gray-900 antialiased">

@php
    // Aman kalau controller belum mengirim variabel ini
    $kata = $kata ?? '';
    $status = $status ?? 'semua';
    $urut = $urut ?? 'terbaru';

    // Kolom cari hanya berisi angka (awalan PJM- sudah tertulis tetap di kotaknya)
    $kataAngka = preg_replace('/\D/', '', (string) $kata);
    $filterAktif = $kata !== '' || $status !== 'semua' || $urut !== 'terbaru';

    // Total seluruh data (tanpa filter) untuk kalimat info. Bisa dikirim dari controller sebagai $totalSemua
    $totalSemua = $totalSemua ?? \App\Models\Peminjaman::count();

    $labelFilter = [
        'menunggu_persetujuan' => 'menunggu persetujuan',
        'menunggu_pengambilan' => 'menunggu pengambilan',
        'dipinjam'             => 'sedang dipinjam',
        'selesai'              => 'selesai',
        'ditolak'              => 'ditolak',
    ];

    // Ikon cadangan kalau alat belum punya foto
    $ikonFoto = '<path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>';

    // Warna status sama dengan dashboard siswa
    $statusMap = [
        'menunggu_persetujuan' => ['label' => 'Menunggu Persetujuan', 'badge' => 'bg-gray-100 text-gray-700',     'dot' => 'bg-gray-500'],
        'menunggu_pengambilan' => ['label' => 'Menunggu Pengambilan', 'badge' => 'bg-amber-100 text-amber-800',   'dot' => 'bg-amber-500'],
        'dipinjam'             => ['label' => 'Sedang Dipinjam',      'badge' => 'bg-blue-100 text-blue-700',     'dot' => 'bg-blue-500'],
        'selesai'              => ['label' => 'Selesai',              'badge' => 'bg-green-100 text-green-700',   'dot' => 'bg-green-500'],
        'ditolak'              => ['label' => 'Ditolak',              'badge' => 'bg-red-100 text-red-700',       'dot' => 'bg-red-500'],
    ];

    // Satu kali olah data untuk tabel (layar lebar) dan kartu (layar kecil)
    $baris = $peminjaman->getCollection()->map(function ($item) use ($statusMap) {
        $kembaliHari = $item->tanggal_kembali->copy()->startOfDay();
        $sisa = (int) today()->diffInDays($kembaliHari, false);
        $terlambat = $item->status === 'dipinjam' && $sisa < 0;

        $pengembalian = match (true) {
            $terlambat => 'Terlambat ' . abs($sisa) . ' hari',
            $item->status === 'dipinjam' => 'Belum dikembalikan',
            $item->status === 'selesai' && $item->tanggal_dikembalikan => 'Dikembalikan ' . $item->tanggal_dikembalikan->locale('id')->translatedFormat('j M Y'),
            $item->status === 'selesai' => 'Sudah dikembalikan',
            default => '-',
        };

        $warnaPengembalian = match (true) {
            $terlambat => 'font-semibold text-red-700',
            $item->status === 'selesai' => 'font-semibold text-green-700',
            $item->status === 'dipinjam' => 'text-gray-600',
            default => 'text-gray-400',
        };

        return [
            'item'               => $item,
            'kode'               => 'PJM-' . str_pad($item->id, 4, '0', STR_PAD_LEFT),
            'status'             => $statusMap[$item->status],
            'pinjam'             => $item->tanggal_pinjam->locale('id')->translatedFormat('j M Y'),
            'kembali'            => $item->tanggal_kembali->locale('id')->translatedFormat('j M Y'),
            'pengembalian'       => $pengembalian,
            'pengembalian_warna' => $warnaPengembalian,
        ];
    });
@endphp

    <!-- Navbar: logo dan tombol Dashboard -->
    <header id="navbar" class="sticky top-0 z-50 border-b border-gray-200 bg-white transition-shadow duration-200">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-3 px-4 sm:gap-6 sm:px-6 lg:px-8">
            <a href="{{ route('admin.dashboard') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="h-14 w-14 shrink-0 transition-transform duration-300 sm:h-18 sm:w-18 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-3">
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

        <!-- Kiri: judul. Kanan: pencarian dan filter (di bawah layar xl, filter turun ke bawah judul) -->
        <div class="mb-6 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="group min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-blue-700 sm:text-3xl">Riwayat Peminjaman</h1>
                <p class="mt-1 text-sm text-gray-600 transition-colors duration-300 group-hover:text-gray-900">Seluruh pengajuan peminjaman alat dari semua peminjam.</p>
            </div>

            <form id="filter-riwayat" method="GET" action="{{ route('admin.peminjaman.riwayat') }}" class="grid w-full grid-cols-1 gap-3 sm:flex sm:w-auto sm:flex-wrap sm:items-end sm:justify-start xl:justify-end">
                @if ($filterAktif)
                    <a href="{{ route('admin.peminjaman.riwayat') }}" class="order-last inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-4 text-center text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95 sm:order-first">Atur ulang</a>
                @endif

                <!-- Cari: awalan PJM- tetap, user cukup mengetik angka -->
                <div class="group min-w-0">
                    <label for="cari" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Cari kode peminjaman</label>
                    <div class="flex h-11 w-full items-stretch overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm transition-all duration-200 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 sm:w-56 motion-safe:hover:-translate-y-0.5">
                        <span class="flex select-none items-center border-r border-gray-200 bg-gray-50 px-3 text-base font-semibold text-gray-600 md:text-sm transition-colors duration-200 group-hover:bg-blue-50 group-hover:text-blue-700">PJM-</span>
                        <input id="cari" name="q" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" value="{{ $kataAngka }}" placeholder="0001" autocomplete="off" class="h-full min-h-0 min-w-0 flex-1 border-0 bg-transparent px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0">
                    </div>
                </div>

                <div class="group min-w-0">
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Filter status</label>
                    <div class="relative w-full transition-transform duration-200 sm:w-60 md:w-52 motion-safe:hover:-translate-y-0.5">
                        <select id="status" name="status" class="h-11 w-full cursor-pointer appearance-none truncate rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="semua" @selected($status === 'semua')>Semua Status</option>
                            <option value="menunggu_persetujuan" @selected($status === 'menunggu_persetujuan')>Menunggu Persetujuan</option>
                            <option value="menunggu_pengambilan" @selected($status === 'menunggu_pengambilan')>Menunggu Pengambilan</option>
                            <option value="dipinjam" @selected($status === 'dipinjam')>Sedang Dipinjam</option>
                            <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                            <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-gray-500 transition-colors duration-200 group-hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>

                <div class="group min-w-0">
                    <label for="urut" class="mb-1 block text-sm font-medium text-gray-700 transition-colors duration-200 group-hover:text-blue-700">Urutkan tanggal</label>
                    <div class="relative w-full transition-transform duration-200 sm:w-60 md:w-52 motion-safe:hover:-translate-y-0.5">
                        <select id="urut" name="urut" class="h-11 w-full cursor-pointer appearance-none truncate rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-sm shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md hover:shadow-blue-900/10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="terbaru" @selected($urut === 'terbaru')>Terbaru ke terlama</option>
                            <option value="terlama" @selected($urut === 'terlama')>Terlama ke terbaru</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-gray-500 transition-colors duration-200 group-hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </form>
        </div>

        @if ($peminjaman->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center transition-all duration-300 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-lg active:border-blue-400 active:bg-blue-50/50 motion-safe:hover:-translate-y-1">
                <p class="font-semibold text-gray-700">{{ $kata !== '' || $status !== 'semua' ? 'Tidak ada riwayat yang cocok dengan pencarian atau filter ini.' : 'Belum ada riwayat peminjaman.' }}</p>
                @if ($filterAktif)
                    <a href="{{ route('admin.peminjaman.riwayat') }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">Atur ulang</a>
                @endif
            </section>
        @else
            <p class="group mb-3 flex items-start gap-2 text-sm text-gray-600 transition-colors duration-200 hover:text-gray-900">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>
                    @if ($status === 'semua')
                        Menampilkan {{ $peminjaman->total() }} pengajuan <span class="font-bold text-blue-600">semua status</span>.
                    @else
                        Menampilkan {{ $peminjaman->total() }} dari {{ $totalSemua }} pengajuan <span class="font-bold text-blue-600">{{ $labelFilter[$status] ?? $status }}</span>.
                    @endif
                </span>
            </p>

            <!-- Tabel untuk layar lebar (xl ke atas) -->
            <div class="hidden overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 xl:block">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[60rem] text-sm">
                        <caption class="sr-only">Riwayat seluruh peminjaman</caption>
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs text-gray-500">
                                <th scope="col" class="rounded-tl-2xl bg-gray-50 px-5 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Kode</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Peminjam</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Alat</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Mulai Pinjam</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Batas Pengembalian</th>
                                <th scope="col" class="bg-gray-50 px-4 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Pengembalian</th>
                                <th scope="col" class="rounded-tr-2xl bg-gray-50 px-5 py-4 font-semibold transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($baris as $b)
                                @php $item = $b['item']; @endphp
                                <tr class="baris-pinjam group cursor-pointer transition-colors duration-200 hover:bg-blue-50 active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500" tabindex="0" data-detail-url="{{ route('peminjaman.show', $item) }}" aria-label="Buka detail {{ $b['kode'] }}">
                                    <td class="whitespace-nowrap px-5 py-5">
                                        <a href="{{ route('peminjaman.show', $item) }}?dari=riwayat" class="inline-block font-bold text-blue-700 transition-transform duration-300 motion-safe:group-hover:translate-x-1.5">{{ $b['kode'] }}</a>
                                    </td>
                                    <td class="px-4 py-5">
                                        <p class="font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $item->user->nama_lengkap }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $item->user->nomor_induk }}{{ $item->user->kelas_jurusan ? ' · ' . $item->user->kelas_jurusan : '' }}</p>
                                    </td>
                                    <td class="px-4 py-5">
                                        <div class="flex items-center gap-3.5">
                                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-100 bg-white transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                                @if ($item->alat->foto_url)
                                                    <img src="{{ $item->alat->foto_url }}" alt="" loading="lazy" class="h-full w-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                                @else
                                                    <span class="flex h-full w-full items-center justify-center bg-blue-50 text-blue-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonFoto !!}</svg></span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $item->alat->nama_alat }}</p>
                                                <p class="mt-0.5 max-w-[12rem] whitespace-normal break-words text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $item->alat->subjudul }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali'] }}</td>
                                    <td class="min-w-[8rem] px-4 py-5 {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</td>
                                    <td class="px-5 py-5">
                                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-110 {{ $b['status']['badge'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $b['status']['dot'] }}"></span>{{ $b['status']['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kartu untuk layar kecil dan sedang (di bawah xl) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:hidden xl:hidden">
                @foreach ($baris as $b)
                    @php $item = $b['item']; @endphp
                    <article class="baris-pinjam group flex min-w-0 cursor-pointer flex-col rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:shadow-2xl hover:shadow-blue-900/15 active:scale-[0.98] active:border-blue-400 active:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 motion-safe:hover:-translate-y-1.5" tabindex="0" data-detail-url="{{ route('peminjaman.show', $item) }}" aria-label="Buka detail {{ $b['kode'] }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-bold text-blue-700">{{ $b['kode'] }}</p>
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold transition-all duration-200 group-hover:shadow-md motion-safe:group-hover:scale-105 {{ $b['status']['badge'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $b['status']['dot'] }}"></span>{{ $b['status']['label'] }}
                            </span>
                        </div>

                        <div class="mt-3 min-w-0 border-b border-gray-100 pb-3 transition-colors duration-200 group-hover:border-blue-200">
                            <p class="break-words font-semibold leading-snug text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $item->user->nama_lengkap }}</p>
                            <p class="mt-0.5 break-words text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $item->user->nomor_induk }}{{ $item->user->kelas_jurusan ? ' · ' . $item->user->kelas_jurusan : '' }}</p>
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-100 bg-white transition-all duration-300 group-hover:border-blue-400 group-hover:shadow-md group-hover:ring-2 group-hover:ring-blue-500/20">
                                @if ($item->alat->foto_url)
                                    <img src="{{ $item->alat->foto_url }}" alt="" loading="lazy" class="h-full w-full object-contain p-0.5 transition-transform duration-300 motion-safe:group-hover:scale-125">
                                @else
                                    <span class="flex h-full w-full items-center justify-center bg-blue-50 text-blue-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonFoto !!}</svg></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold leading-snug text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $item->alat->nama_alat }}</p>
                                <p class="line-clamp-2 text-xs text-gray-500 transition-colors duration-200 group-hover:text-gray-700">{{ $item->alat->subjudul }}</p>
                            </div>
                        </div>

                        <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 text-xs transition-colors duration-200 group-hover:border-blue-200">
                            <div>
                                <dt class="leading-snug text-gray-500">Mulai Pinjam</dt>
                                <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['pinjam'] }}</dd>
                            </div>
                            <div>
                                <dt class="leading-snug text-gray-500">Batas Kembali</dt>
                                <dd class="mt-0.5 font-semibold text-gray-800 transition-colors duration-200 group-hover:text-blue-700">{{ $b['kembali'] }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="leading-snug text-gray-500">Pengembalian</dt>
                                <dd class="mt-0.5 {{ $b['pengembalian_warna'] }}">{{ $b['pengembalian'] }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('peminjaman.show', $item) }}?dari=riwayat" class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">Lihat Detail</a>
                    </article>
                @endforeach
            </div>

            @if ($peminjaman->hasPages())
                <div class="mt-6 overflow-x-auto [&_a]:transition-all [&_a]:duration-200 [&_a:hover]:shadow-md motion-safe:[&_a:hover]:-translate-y-0.5">{{ $peminjaman->links() }}</div>
            @endif
        @endif
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-center text-xs text-gray-600 sm:flex-row sm:px-6 sm:text-left lg:px-8 [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
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

        // Pencarian: hanya angka, kirim otomatis setelah berhenti mengetik
        const formRiwayat = document.getElementById('filter-riwayat');
        const cariRiwayat = document.getElementById('cari');
        let jedaPencarian;
        cariRiwayat.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            clearTimeout(jedaPencarian);
            jedaPencarian = setTimeout(function () { formRiwayat.requestSubmit(); }, 450);
        });
        formRiwayat.querySelectorAll('select').forEach(function (pilihan) {
            pilihan.addEventListener('change', function () { formRiwayat.requestSubmit(); });
        });

        // Setelah halaman dimuat ulang karena mencari, kursor kembali ke akhir angka
        if (new URLSearchParams(window.location.search).has('q') && cariRiwayat.value !== '') {
            cariRiwayat.focus();
            cariRiwayat.setSelectionRange(cariRiwayat.value.length, cariRiwayat.value.length);
        }

        // Baris / kartu bisa diklik dan bisa dibuka dengan Enter atau Spasi
        document.querySelectorAll('[data-detail-url]').forEach(function (baris) {
            baris.addEventListener('click', function (event) {
                if (event.target.closest('a, button')) return;
                window.location.href = baris.dataset.detailUrl + (baris.dataset.detailUrl.indexOf('?') !== -1 ? '&' : '?') + 'dari=riwayat';
            });
            baris.addEventListener('keydown', function (event) {
                if (event.target !== baris) return;
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    window.location.href = baris.dataset.detailUrl + (baris.dataset.detailUrl.indexOf('?') !== -1 ? '&' : '?') + 'dari=riwayat';
                }
            });
        });
    </script>
</body>
</html>