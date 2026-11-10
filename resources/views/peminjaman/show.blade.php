<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - SiLat Multimedia</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Cincin fokus keyboard: spesifisitas nol supaya gaya khusus di elemen tetap menang */
        :where(a, button, summary, input, textarea, select):focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }

        @keyframes panah-maju { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(3px); } }

        @media (prefers-reduced-motion: no-preference) and (hover: hover) {
            .tombol-lagi:hover svg { animation: panah-maju .6s ease-in-out infinite; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen overflow-x-clip">

@php
    use Carbon\Carbon;

    $p = $peminjaman;

    // Admin dan peminjam memakai halaman yang sama; bagian khusus admin dibungkus @if ($admin)
    $admin        = Auth::user()->peran === 'admin';
    $dashboardUrl = $admin ? route('admin.dashboard') : url('/dashboard');
    $riwayatUrl   = $admin ? route('admin.peminjaman.riwayat') : route('peminjaman.riwayat');

    // Asal halaman: dari riwayat membawa ?dari=riwayat, dari dashboard tidak membawa apa-apa.
    // Tombol kembali menyesuaikan: dari riwayat -> kembali ke riwayat, selain itu -> kembali ke dashboard.
    $dariRiwayat = request('dari') === 'riwayat';
    $urlKembali  = $dariRiwayat ? $riwayatUrl : $dashboardUrl;
    $tujuanKembali = $dariRiwayat ? 'Riwayat' : 'Dashboard';

    $u            = $p->user;
    $labelPeran   = ['siswa' => 'Siswa', 'guru_karyawan' => 'Guru / Karyawan', 'admin' => 'Admin'];
    $labelInduk   = ['siswa' => 'Nomor Induk Siswa', 'guru_karyawan' => 'Nomor Induk Pegawai'];

    // text-base di layar kecil supaya browser HP tidak memperbesar halaman otomatis saat kolom diketuk
    $kelasInput   = 'w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-base sm:text-sm outline-none transition duration-200 '
        . 'hover:border-blue-400 hover:bg-blue-50/40 hover:shadow-sm focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
    // Kolom teks yang diketik admin: otomatis huruf kecil (placeholder tetap normal)
    $kelasTeks    = $kelasInput . ' resize-none';

    // TODO: hapus setelah AlatSeeder dibuat (foto, subjudul, dan kelengkapan akan diambil dari model Alat).
    // Foto utama = public/images/alat/{foto}1.jpg. Kelengkapan masih DRAFT, sama dengan halaman form.
    $katalog = [
        'sony-mirrorless'       => ['foto' => 'sony',     'sub' => 'Sony A6000',                    'ikon' => 'kamera', 'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera']],
        'canon-dslr'            => ['foto' => 'cannon',   'sub' => 'Canon EOS 1200D',               'ikon' => 'kamera', 'kelengkapan' => ['Body kamera', 'Lensa kit', 'Baterai', 'Charger', 'Kartu memori', 'Tali kamera', 'Tas kamera']],
        'drone-dokumentasi-1'   => ['foto' => 'drone1-',  'sub' => 'DJI Mini 2',                    'ikon' => 'drone',  'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan']],
        'drone-dokumentasi-2'   => ['foto' => 'drone2-',  'sub' => 'DJI Mavic Air 2',               'ikon' => 'drone',  'kelengkapan' => ['Unit drone', 'Remote controller', 'Baterai', 'Baling-baling cadangan', 'Kabel pengisi daya', 'Tas atau case penyimpanan']],
        'handycam'              => ['foto' => 'handycam', 'sub' => 'Sony HDR-CX405',                'ikon' => 'video',  'kelengkapan' => ['Unit handycam', 'Baterai', 'Charger atau adaptor', 'Kabel data', 'Kartu memori', 'Tas handycam']],
        'gimbal-stabilizer'     => ['foto' => 'gimbal',   'sub' => 'Rekaman Mulus Saat Bergerak',   'ikon' => 'gimbal', 'kelengkapan' => ['Unit gimbal', 'Baterai', 'Kabel pengisi daya', 'Plat dudukan (quick release)', 'Tas penyimpanan']],
        'tripod'                => ['foto' => 'tripod',   'sub' => 'Penopang Kamera Dan Handphone', 'ikon' => 'tripod', 'kelengkapan' => ['Unit tripod', 'Dudukan handphone', 'Plat dudukan kamera', 'Tas tripod']],
        'handphone-dokumentasi' => ['foto' => 'ipone',    'sub' => 'iPhone 13',                     'ikon' => 'hp',     'kelengkapan' => ['Handphone iPhone 13', 'Kabel pengisi daya', 'Adaptor charger', 'Casing pelindung']],
    ];

    $ikonAlat = [
        'kamera' => '<path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>',
        'video'  => '<path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'hp'     => '<path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'drone'  => '<circle cx="5" cy="6" r="2.2"/><circle cx="19" cy="6" r="2.2"/><circle cx="5" cy="18" r="2.2"/><circle cx="19" cy="18" r="2.2"/><path d="M7 7.5l3 3m7-3l-3 3M7 16.5l3-3m7 3l-3-3"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.5"/>',
        'gimbal' => '<rect x="7" y="4" width="10" height="5" rx="1.5"/><path d="M4 6.5h3m10 0h3M12 9v4"/><rect x="9.5" y="13" width="5" height="8" rx="2"/>',
        'tripod' => '<rect x="8.5" y="2.5" width="7" height="4" rx="1"/><path d="M12 6.5V11m0 0l-5 10m5-10v10m0-10l5 10"/>',
    ];

    $alat = $katalog[$p->alat->slug] ?? ['foto' => $p->alat->slug, 'sub' => $p->alat->deskripsi ?? '', 'ikon' => 'kamera', 'kelengkapan' => []];
    $namaAlat = $p->alat->nama_alat;

    // Tanggal-tanggal
    $hariIni      = Carbon::now()->startOfDay();
    $pinjam       = Carbon::parse($p->tanggal_pinjam)->startOfDay();
    $kembali      = Carbon::parse($p->tanggal_kembali)->startOfDay();
    $diambil      = $p->tanggal_diambil ? Carbon::parse($p->tanggal_diambil) : null;
    $dikembalikan = $p->tanggal_dikembalikan ? Carbon::parse($p->tanggal_dikembalikan) : null;
    $diajukan     = $p->created_at ? Carbon::parse($p->created_at) : null;

    $sisa      = (int) $hariIni->diffInDays($kembali, false);
    $terlambat = $p->status === 'dipinjam' && $sisa < 0;
    $durasi    = abs((int) $pinjam->diffInDays($kembali));

    // Keterlambatan: yang masih berjalan, atau yang sudah selesai tetapi dikembalikan lewat batas
    $hariTelat = 0;
    if ($terlambat) {
        $hariTelat = abs($sisa);
    } elseif ($p->status === 'selesai' && $dikembalikan && $dikembalikan->copy()->startOfDay()->gt($kembali)) {
        $hariTelat = abs((int) $kembali->diffInDays($dikembalikan->copy()->startOfDay()));
    }
    // Sesuai aturan di form: toleransi 1 hari, mulai hari kedua Rp5.000 per hari
    $denda = max(0, $hariTelat - 1) * 5000;
    $rupiah = fn ($angka) => 'Rp' . number_format($angka, 0, ',', '.');

    $tgl      = fn ($c) => $c ? $c->copy()->locale('id')->translatedFormat('j F Y') : null;
    $tglHari  = fn ($c) => $c ? $c->copy()->locale('id')->translatedFormat('l, j F Y') : null;
    $tglWaktu = fn ($c) => $c ? $c->copy()->locale('id')->translatedFormat('j F Y') . ' ' . $c->copy()->format('H.i') : null;

    $kode = 'PJM-' . str_pad($p->id, 4, '0', STR_PAD_LEFT);

    // Status peminjaman (status tetap sedang dipinjam meskipun melewati batas pengembalian)
    $kunci = $p->status;
    $statusMap = [
        'menunggu_persetujuan' => ['label' => 'Menunggu Persetujuan', 'badge' => 'bg-gray-200 text-gray-900 ring-1 ring-inset ring-gray-400 shadow-md shadow-gray-500/20', 'dot' => 'bg-gray-700'],
        'menunggu_pengambilan' => ['label' => 'Menunggu Pengambilan', 'badge' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
        'dipinjam'             => ['label' => 'Sedang Dipinjam',      'badge' => 'bg-blue-100 text-blue-700',     'dot' => 'bg-blue-500'],
        'selesai'              => ['label' => 'Selesai',              'badge' => 'bg-green-100 text-green-700',   'dot' => 'bg-green-500'],
        'ditolak'              => ['label' => 'Ditolak',              'badge' => 'bg-red-100 text-red-700',       'dot' => 'bg-red-500'],
    ];
    $status = $statusMap[$kunci];

    // Banner status: teks dan warna menyesuaikan kondisi
    $gayaBanner = [
        'menunggu_persetujuan' => ['wadah' => 'hover:border-gray-400 hover:bg-gray-50 hover:shadow-gray-900/10',        'ikon' => 'bg-gray-100 text-gray-600 group-hover:bg-gray-600 group-hover:text-white',       'judul' => 'group-hover:text-gray-900',   'path' => 'M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'menunggu_pengambilan' => ['wadah' => 'hover:border-amber-300 hover:bg-amber-50/50 hover:shadow-amber-900/10', 'ikon' => 'bg-amber-100 text-amber-700 group-hover:bg-amber-600 group-hover:text-white', 'judul' => 'group-hover:text-amber-800', 'path' => 'M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'dipinjam'             => ['wadah' => 'hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-blue-900/10',       'ikon' => 'bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white',       'judul' => 'group-hover:text-blue-700',   'path' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        'selesai'              => ['wadah' => 'hover:border-green-300 hover:bg-green-50/50 hover:shadow-green-900/10',    'ikon' => 'bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white',    'judul' => 'group-hover:text-green-700',  'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'ditolak'              => ['wadah' => 'hover:border-red-300 hover:bg-red-50/50 hover:shadow-red-900/10',       'ikon' => 'bg-red-100 text-red-600 group-hover:bg-red-600 group-hover:text-white',           'judul' => 'group-hover:text-red-700',   'path' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ][$kunci];

    $kondisi = $p->kondisi_alat_kembali ? mb_strtolower($p->kondisi_alat_kembali) : null;

    $catAdmin = $p->catatan_admin ? trim($p->catatan_admin) : null;
    $catAmbil = $p->catatan_pengambilan ? trim($p->catatan_pengambilan) : null;

    $teksBanner = match (true) {
        $kunci === 'menunggu_persetujuan' => [
            'judul' => 'Pengajuanmu sedang menunggu persetujuan admin',
            'isi'   => 'Admin akan memverifikasi pengajuan ini. Kamu bisa mengambil alat di Ekskul Multimedia setelah pengajuan disetujui.',
        ],
        $kunci === 'menunggu_pengambilan' => [
            'judul' => 'Pengajuan disetujui, menunggu pengambilan',
            'isi'   => 'Anda dapat mengambil alat setelah peminjaman disetujui, mulai ' . $tglHari($pinjam) . '. Hitung mundur pengembalian dimulai setelah alat dicatat diambil.',
        ],
        $kunci === 'dipinjam' && $terlambat => [
            'judul' => 'Terlambat ' . $hariTelat . ' hari',
            'isi'   => 'Segera kembalikan alat ke Ekskul Multimedia dan hubungi admin kalau ada kendala.'
                . ($denda > 0 ? ' Perkiraan denda saat ini ' . $rupiah($denda) . ' (Rp5.000 per hari setelah toleransi 1 hari), nilai akhir ditetapkan admin.' : ' Masih dalam toleransi 1 hari, setelah itu dikenakan denda Rp5.000 per hari.'),
        ],
        $kunci === 'dipinjam' && $sisa === 0 => [
            'judul' => 'Hari ini batas pengembalian',
            'isi'   => 'Kembalikan alat ke Ekskul Multimedia hari ini supaya tidak terlambat.',
        ],
        $kunci === 'dipinjam' => [
            'judul' => 'Alat sedang kamu pinjam',
            'isi'   => ($diambil ? 'Alat diambil pada ' . $tglWaktu($diambil) . '. ' : '') . 'Kembalikan dalam ' . $sisa . ' hari lagi, paling lambat ' . $tglHari($kembali) . ', ke Ekskul Multimedia.',
        ],
        $kunci === 'selesai' => [
            'judul' => 'Peminjaman selesai',
            'isi'   => ($dikembalikan ? 'Alat dikembalikan pada ' . $tglWaktu($dikembalikan) : 'Alat sudah dikembalikan')
                . ($kondisi ? ' dalam kondisi ' . $kondisi : '') . '.'
                . ($hariTelat > 0 ? ' Pengembalian terlambat ' . $hariTelat . ' hari' . ($denda > 0 ? ', perkiraan denda ' . $rupiah($denda) . '.' : ', masih dalam toleransi.') : ''),
        ],
        default => [
            'judul' => 'Pengajuan tidak disetujui',
            'isi'   => $p->catatan_admin
                ? 'Alasan dari admin tercantum pada bagian Catatan Admin di bawah.'
                : 'Kamu bisa mengajukan kembali dengan jadwal atau alat lain.',
        ],
    };

    // Teks banner untuk admin (sudut pandang pengelola, bukan peminjam)
    if ($admin) {
        $teksBanner = match (true) {
            $kunci === 'menunggu_persetujuan' => [
                'judul' => 'Pengajuan ini menunggu keputusan admin',
                'isi'   => 'Periksa data peminjam dan jadwalnya, lalu setujui atau tolak melalui bagian Tindakan.',
            ],
            $kunci === 'menunggu_pengambilan' => [
                'judul' => 'Disetujui, menunggu pengambilan alat',
                'isi'   => 'Peminjam dapat mengambil alat setelah peminjaman disetujui. Catat pengambilan melalui bagian Tindakan.',
            ],
            $kunci === 'dipinjam' && $terlambat => [
                'judul' => 'Terlambat ' . $hariTelat . ' hari',
                'isi'   => 'Hubungi peminjam agar alat segera dikembalikan.'
                    . ($denda > 0 ? ' Perkiraan denda saat ini ' . $rupiah($denda) . '.' : ' Masih dalam toleransi 1 hari.'),
            ],
            $kunci === 'dipinjam' && $pinjam->gt($hariIni) => [
                'judul' => 'Disetujui, peminjaman belum dimulai',
                'isi'   => 'Peminjam dapat mengambil alat mulai ' . $tglHari($pinjam) . '. Batas pengembalian ' . $tglHari($kembali) . '.',
            ],
            $kunci === 'dipinjam' && $sisa === 0 => [
                'judul' => 'Hari ini batas pengembalian',
                'isi'   => 'Catat pengembalian di bagian Tindakan setelah alat diterima dan diperiksa.',
            ],
            $kunci === 'dipinjam' => [
                'judul' => 'Peminjaman sedang berjalan',
                'isi'   => ($diambil ? 'Alat diambil pada ' . $tglWaktu($diambil) . '. ' : '') . 'Batas pengembalian ' . $tglHari($kembali) . ', ' . $sisa . ' hari lagi.',
            ],
            $kunci === 'selesai' => [
                'judul' => 'Peminjaman selesai',
                'isi'   => ($dikembalikan ? 'Alat dikembalikan pada ' . $tglWaktu($dikembalikan) : 'Alat sudah dikembalikan')
                    . ($kondisi ? ' dalam kondisi ' . $kondisi : '') . '.',
            ],
            default => [
                'judul' => 'Pengajuan ini ditolak',
                'isi'   => 'Alasan penolakan tercatat pada bagian Catatan Admin.',
            ],
        };
    }

    // Teks kolom pengembalian
    $pengembalian = match (true) {
        $terlambat => 'Terlambat ' . $hariTelat . ' hari',
        $kunci === 'dipinjam' => 'Belum dikembalikan',
        $kunci === 'selesai' => ($dikembalikan ? 'Dikembalikan pada ' . $tglWaktu($dikembalikan) : 'Sudah dikembalikan') . ($hariTelat > 0 ? ' (terlambat ' . $hariTelat . ' hari)' : ''),
        default => '-',
    };
    $warnaPengembalian = match (true) {
        $terlambat || ($kunci === 'selesai' && $hariTelat > 0) => 'text-red-700',
        $kunci === 'selesai' => 'text-green-700',
        default => 'text-gray-900',
    };

    // Linimasa: tanggal hanya ditampilkan kalau datanya memang ada
    $langkah = [
        ['judul' => 'Pengajuan dikirim', 'isi' => 'Pengajuan peminjaman kamu kirim ke admin.', 'tanggal' => $tglWaktu($diajukan), 'state' => 'selesai'],
    ];
    if ($kunci === 'menunggu_persetujuan') {
        $langkah[] = ['judul' => 'Menunggu keputusan admin', 'isi' => 'Admin sedang memverifikasi pengajuanmu.', 'tanggal' => null, 'state' => 'sekarang'];
        $langkah[] = ['judul' => 'Pengajuan disetujui', 'isi' => 'Menunggu keputusan admin.', 'tanggal' => null, 'state' => 'menunggu'];
        $langkah[] = ['judul' => 'Alat diambil', 'isi' => 'Rencana pengambilan alat.', 'tanggal' => $tgl($pinjam), 'state' => 'menunggu'];
        $langkah[] = ['judul' => 'Alat dikembalikan', 'isi' => 'Batas pengembalian.', 'tanggal' => $tgl($kembali), 'state' => 'menunggu'];
    } elseif ($kunci === 'menunggu_pengambilan') {
        $langkah[] = ['judul' => 'Pengajuan disetujui', 'isi' => 'Admin menyetujui pengajuanmu.', 'tanggal' => null, 'state' => 'selesai'];
        $langkah[] = ['judul' => 'Menunggu pengambilan', 'isi' => $catAmbil ?: $catAdmin ?: 'Ambil alat mulai tanggal yang diajukan.', 'tanggal' => $tgl($pinjam), 'state' => 'sekarang'];
        $langkah[] = ['judul' => 'Alat dipinjam', 'isi' => 'Masa peminjaman dimulai setelah alat diambil.', 'tanggal' => null, 'state' => 'menunggu'];
        $langkah[] = ['judul' => 'Menunggu pengembalian', 'isi' => 'Batas pengembalian.', 'tanggal' => $tgl($kembali), 'state' => 'menunggu'];
    } elseif ($kunci === 'ditolak') {
        $langkah[] = ['judul' => 'Pengajuan ditolak', 'isi' => $p->catatan_admin ? 'Lihat alasannya pada Catatan Admin.' : 'Admin tidak menyetujui pengajuan ini.', 'tanggal' => null, 'state' => 'tolak'];
    } else {
        $langkah[] = ['judul' => 'Pengajuan disetujui', 'isi' => 'Admin menyetujui pengajuanmu.', 'tanggal' => null, 'state' => 'selesai'];
        $langkah[] = ['judul' => 'Menunggu pengambilan', 'isi' => $catAdmin ?: 'Alat telah diambil peminjam.', 'tanggal' => $diambil ? $tglWaktu($diambil) : $tgl($pinjam), 'state' => 'selesai'];
        $langkah[] = ['judul' => 'Alat dipinjam', 'isi' => 'Masa peminjaman dimulai.', 'tanggal' => $diambil ? $tglWaktu($diambil) : $tgl($pinjam), 'state' => 'selesai'];
        if ($kunci === 'selesai') {
            $langkah[] = ['judul' => 'Alat dikembalikan', 'isi' => $kondisi ? 'Kondisi alat saat dikembalikan: ' . $kondisi . '.' : 'Alat sudah kembali ke Ekskul Multimedia.', 'tanggal' => $dikembalikan ? $tglWaktu($dikembalikan) : null, 'state' => 'selesai'];
        } else {
            $langkah[] = ['judul' => 'Menunggu pengembalian', 'isi' => $terlambat ? 'Batas pengembalian sudah lewat ' . $hariTelat . ' hari, segera kembalikan ke Ekskul Multimedia.' : 'Kembalikan paling lambat pada batas pengembalian.', 'tanggal' => $tgl($kembali), 'state' => 'sekarang'];
        }
    }
    $gayaTitik = [
        'selesai'  => 'bg-green-600 text-white',
        'sekarang' => 'bg-blue-600 text-white ring-4 ring-blue-100',
        'menunggu' => 'bg-gray-200 text-gray-500',
        'tolak'    => 'bg-red-600 text-white ring-4 ring-red-100',
        'telat'    => 'bg-red-600 text-white ring-4 ring-red-100',
    ];

    $catatanAdminTampil = $p->status === 'menunggu_pengambilan'
        ? ($catAmbil ?: $catAdmin)
        : $catAdmin;

    // Admin boleh mengubah catatan setelah pengajuan diputuskan (saat masih menunggu persetujuan belum ada catatan)
    $bolehUbahCatatan = $admin && $p->status !== 'menunggu_persetujuan';
    $catatanWajib     = $p->status !== 'selesai';
    // Petunjuk di bawah kolom ubah catatan: menjelaskan siapa yang membaca catatan ini dan apa isi yang sebaiknya ditulis
    $petunjukCatatan  = match ($p->status) {
        'menunggu_pengambilan' => 'Peminjam membaca catatan ini sebagai petunjuk tempat pengambilan alat, jadi pastikan lokasinya jelas.',
        'dipinjam'             => 'Catatan serah terima ini tampil bagi peminjam. Cantumkan kondisi alat dan tempat pengembalian barang.',
        'selesai'              => 'Catatan ini tampil bagi peminjam sebagai bukti kondisi alat saat dikembalikan.',
        default                => 'Alasan penolakan ini tampil bagi peminjam. Tulis dengan jelas agar ia bisa mengajukan ulang dengan benar.',
    };
    $formUbahTerbuka  = $errors->has('catatan');

    $adaCatatan = $catatanAdminTampil || $kunci === 'selesai' || $bolehUbahCatatan;
    $bisaPinjamLagi = in_array($p->status, ['selesai', 'ditolak'], true) && ! $admin;
@endphp

    <!-- Navbar Atas (halaman tugas seperti form: logo dan satu tombol kembali) -->
    <header id="navbar" class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-3 sm:gap-6">
            <a href="{{ $dashboardUrl }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SiLat" class="w-14 h-14 shrink-0 sm:w-18 sm:h-18 transition-transform duration-300 motion-safe:group-hover:scale-105">
                <div class="min-w-0">
                    <span class="text-blue-600 font-extrabold block leading-tight text-2xl transition-colors duration-300 group-hover:text-blue-700">SiLat</span>
                    <span class="text-gray-700 font-semibold text-xs block truncate">Sistem Peminjaman Alat</span>
                </div>
            </a>

            <!-- Tombol kembali: ke Riwayat kalau dibuka dari riwayat, ke Dashboard kalau dibuka dari dashboard -->
            <a href="{{ $urlKembali }}" aria-label="Kembali ke {{ $tujuanKembali }}"
               class="shrink-0 inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 active:scale-95">
                <span><span class="hidden sm:inline">Kembali ke </span>{{ $tujuanKembali }}</span>
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 flex-grow w-full min-w-0">

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

        <!-- Header halaman: judul di kiri, kode dan status di kanan -->
        <div class="mb-5 sm:mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <span class="hidden sm:flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/25 transition-all duration-300 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/40 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-gray-900 transition-colors duration-300 hover:text-blue-700">Detail Peminjaman</h1>
                    <p class="text-gray-500 text-sm leading-relaxed mt-1.5">Rincian lengkap pengajuan peminjaman alat ini.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 sm:flex-col sm:items-end sm:gap-2">
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold transition-all duration-200 hover:shadow-md motion-safe:hover:scale-105 {{ $status['badge'] }}">
                    <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                    {{ $status['label'] }}
                </span>
                <span class="text-xs font-bold tracking-wide text-gray-500 transition-colors duration-200 hover:text-blue-700">Kode Pengajuan: <span class="text-blue-700">{{ $kode }}</span></span>
            </div>
        </div>

        <!-- Banner status -->
        <div role="status" class="group mb-5 sm:mb-6 flex items-start gap-3 sm:gap-4 rounded-2xl border border-gray-200/80 bg-white px-4 py-4 sm:px-5 shadow-sm transition-all duration-300 hover:shadow-xl motion-safe:hover:-translate-y-1 {{ $gayaBanner['wadah'] }}">
            <span class="flex h-10 w-10 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl transition-all duration-300 motion-safe:group-hover:scale-110 motion-safe:group-hover:-rotate-6 {{ $gayaBanner['ikon'] }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $gayaBanner['path'] }}"/>
                </svg>
            </span>
            <div class="min-w-0 pt-0.5">
                <p class="font-bold leading-snug text-gray-900 text-sm sm:text-base break-words transition-colors duration-300 {{ $gayaBanner['judul'] }}">{{ $teksBanner['judul'] }}</p>
                <p class="text-sm leading-relaxed text-gray-500 mt-1 break-words transition-colors duration-300 group-hover:text-gray-700">{{ $teksBanner['isi'] }}</p>
            </div>
        </div>

        <div class="grid gap-5 sm:gap-6 lg:grid-cols-5 lg:items-start">

            <!-- KIRI: kartu alat (ikut menempel saat halaman digulir di layar lebar) -->
            <section class="min-w-0 lg:col-span-2 lg:sticky lg:top-28 bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                <div class="group relative aspect-[4/3] bg-white border-b border-gray-100 overflow-hidden">
                    <img src="{{ asset('images/alat/' . $alat['foto'] . '1.jpg') }}" alt="{{ $namaAlat }}" loading="lazy"
                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                         class="absolute inset-0 w-full h-full object-contain p-4 transition-transform duration-500 ease-out motion-safe:group-hover:scale-110">
                    <div class="hidden absolute inset-0 flex-col items-center justify-center gap-2 bg-gradient-to-br from-blue-50 to-blue-100 text-blue-300 [&:not(.hidden)]:flex">
                        <svg class="w-16 h-16 transition-transform duration-500 motion-safe:group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $ikonAlat[$alat['ikon']] !!}</svg>
                        <span class="text-xs font-medium text-blue-400">Foto belum tersedia</span>
                    </div>
                </div>

                <div class="p-5 sm:p-6 lg:p-7">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Alat yang dipinjam</p>
                    <h2 class="mt-1 text-xl sm:text-2xl font-bold text-gray-900 leading-tight break-words transition-colors duration-300 hover:text-blue-700">{{ $namaAlat }}</h2>
                    <p class="text-gray-500 mt-1 break-words">{{ $alat['sub'] }}</p>

                    @if (count($alat['kelengkapan']))
                        <div class="group/kel border-t border-gray-100 mt-5 pt-5 sm:mt-6 sm:pt-6">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide transition-colors duration-200 group-hover/kel:text-blue-600">Kelengkapan Alat</p>
                            <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-1 2xl:grid-cols-2">
                                @foreach ($alat['kelengkapan'] as $item)
                                    <li class="group flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-100 px-3 py-2.5 text-sm leading-snug text-gray-700 transition-all duration-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md hover:shadow-blue-600/25 active:border-blue-600 active:bg-blue-600 active:text-white motion-safe:hover:translate-x-1.5 motion-safe:hover:scale-[1.03]">
                                        <svg class="w-4 h-4 shrink-0 text-blue-600 transition-all duration-200 group-hover:text-white group-active:text-white motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="min-w-0 break-words">{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <p class="group mt-4 flex items-start gap-2 text-xs leading-relaxed text-gray-600 transition-colors duration-200 hover:text-gray-900">
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500 transition-all duration-200 group-hover:text-blue-700 motion-safe:group-hover:scale-125" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Kelengkapan diperiksa bersama admin saat pengambilan dan pengembalian alat.</span>
                            </p>
                        </div>
                    @endif

                    @if ($bisaPinjamLagi)
                        <a href="{{ route('peminjaman.create', $p->alat->slug) }}" class="tombol-lagi mt-6 flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 rounded-xl transition-all duration-200 shadow-sm hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30 active:scale-[0.98]">
                            Pinjam Alat Ini Lagi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </section>

            <!-- KANAN: ringkasan, linimasa, catatan admin -->
            <div class="min-w-0 lg:col-span-3 space-y-5 sm:space-y-6">

                @if ($admin)
                    <!-- Data peminjam (khusus admin) -->
                    <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                        <div class="flex items-center gap-3 mb-5 sm:mb-6">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-900 leading-tight">Data Peminjam</h2>
                                <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Akun yang mengajukan peminjaman ini.</p>
                            </div>
                        </div>

                        <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            @foreach (array_filter([
                                ['Nama Lengkap', $u->nama_lengkap],
                                ['Peran', $labelPeran[$u->peran] ?? ucfirst($u->peran)],
                                [$labelInduk[$u->peran] ?? 'Nomor Induk', $u->nomor_induk],
                                $u->peran === 'siswa' ? ['Kelas & Jurusan', $u->kelas_jurusan ?: '-'] : null,
                            ]) as [$label, $nilai])
                                <div class="group/sel min-w-0 rounded-lg p-2 -m-2 transition-all duration-200 hover:bg-blue-50 hover:shadow-sm">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 transition-colors duration-200 group-hover/sel:text-blue-600">{{ $label }}</dt>
                                    <dd class="mt-1 text-base font-semibold leading-snug text-gray-900 break-words transition-colors duration-200 group-hover/sel:text-blue-700">{{ $nilai }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif

                <!-- Ringkasan peminjaman -->
                <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Ringkasan Peminjaman</h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">{{ $admin ? 'Data pengajuan dari peminjam.' : 'Data yang kamu isi saat mengajukan peminjaman.' }}</p>
                        </div>
                    </div>

                    @php
                        $labelAmbil = $diambil ? 'Barang Diambil' : 'Mulai Pinjam';
                        $nilaiAmbil = $diambil ? $tglWaktu($diambil) : $tglHari($pinjam);

                        $labelKembali = $kunci === 'selesai' ? 'Barang Dikembalikan' : 'Pengembalian';
                        $nilaiKembali = $kunci === 'selesai'
                            ? ($dikembalikan ? $tglWaktu($dikembalikan) . ($hariTelat > 0 ? ' (terlambat ' . $hariTelat . ' hari)' : '') : 'Sudah dikembalikan')
                            : $pengembalian;
                    @endphp

                    <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                        @foreach ([
                            ['Kode Pengajuan', $kode, 'text-gray-900'],
                            ['Diajukan Pada', $tglWaktu($diajukan) ?? '-', 'text-gray-900'],
                            [$labelAmbil, $nilaiAmbil, 'text-gray-900'],
                            ['Batas Pengembalian', $tglHari($kembali), 'text-gray-900'],
                            ['Durasi Peminjaman', $durasi === 0 ? 'Hari yang sama' : $durasi . ' hari', 'text-gray-900'],
                            [$labelKembali, $nilaiKembali, $warnaPengembalian],
                        ] as [$label, $nilai, $warna])
                            <div class="group/sel min-w-0 rounded-lg p-2 -m-2 transition-all duration-200 hover:bg-blue-50 hover:shadow-sm">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 transition-colors duration-200 group-hover/sel:text-blue-600">{{ $label }}</dt>
                                <dd class="mt-1 text-base font-semibold leading-snug break-words transition-colors duration-200 group-hover/sel:text-blue-700 {{ $warna }}">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="group/sel mt-6 pt-5 border-t border-gray-100 rounded-lg transition-all duration-200">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 transition-colors duration-200 group-hover/sel:text-blue-600">Keperluan Peminjaman</p>
                        <p class="mt-2 text-sm leading-relaxed text-gray-700 whitespace-pre-line break-words transition-colors duration-200 group-hover/sel:text-gray-900">{{ $p->keperluan ?: 'Tidak ada keterangan keperluan.' }}</p>
                    </div>
                </section>

                @unless ($admin)
                <!-- Linimasa status (khusus peminjam) -->
                <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900 leading-tight">Perjalanan Pengajuan</h2>
                            <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Tahapan pengajuan ini dari dikirim sampai alat kembali.</p>
                        </div>
                    </div>

                    <ol>
                        @foreach ($langkah as $l)
                            <li class="group relative flex gap-3 sm:gap-4 rounded-xl p-2 -m-2 {{ $loop->last ? '' : 'mb-5' }} transition-all duration-200 hover:bg-blue-50 hover:shadow-md hover:shadow-blue-900/5 motion-safe:hover:translate-x-1">
                                @unless ($loop->last)
                                    <span aria-hidden="true" class="absolute left-[1.625rem] top-11 bottom-[-1.5rem] w-px bg-blue-200"></span>
                                @endunless

                                <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold transition-all duration-200 motion-safe:group-hover:scale-110 {{ $gayaTitik[$l['state']] }}">
                                    @if ($l['state'] === 'selesai')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($l['state'] === 'tolak')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1 pt-1">
                                    <div class="flex flex-col gap-0.5 sm:flex-row sm:flex-wrap sm:items-baseline sm:justify-between sm:gap-x-3">
                                        <p class="text-sm font-semibold leading-snug text-gray-900 transition-colors duration-200 group-hover:text-blue-700">{{ $l['judul'] }}</p>
                                        @if ($l['tanggal'])
                                            <p class="text-xs font-semibold text-gray-500 transition-colors duration-200 group-hover:text-blue-600">{{ $l['tanggal'] }}</p>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs leading-relaxed text-gray-500 break-words transition-colors duration-200 group-hover:text-gray-700">{{ $l['isi'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>

                @endunless

                @if ($admin)
                    <!-- Tindakan (khusus admin) -->
                    <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                        <div class="flex items-center gap-3 mb-5 sm:mb-6">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-900 leading-tight">Tindakan</h2>
                                <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Keputusan dan pencatatan untuk pengajuan ini.</p>
                            </div>
                        </div>

                        @if ($p->status === 'menunggu_persetujuan')
                            <div class="grid gap-6 xl:grid-cols-2">
                                <form method="POST" action="{{ route('admin.peminjaman.setujui', $p) }}" class="min-w-0 space-y-4">
                                    @csrf
                                    <div>
                                        <label for="catatan_pengambilan" class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Cantumkan Tempat Pengambilan Barang <span class="text-red-500">*</span>
                                        </label>
                                        <textarea id="catatan_pengambilan" name="catatan_pengambilan" rows="3" maxlength="500" required
                                            placeholder="Contoh: Ambil alat di lab Multimedia."
                                            class="{{ $kelasTeks }}">{{ old('catatan_pengambilan') }}</textarea>
                                    </div>

                                    <p class="group flex items-start gap-2 text-xs leading-relaxed text-gray-600">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Jika disetujui, peminjam dapat mengambil alat mulai {{ $tgl($pinjam) }}.</span>
                                    </p>

                                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                        Setujui Pengajuan
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.peminjaman.tolak', $p) }}" class="min-w-0 space-y-4 border-t border-gray-100 pt-6 xl:border-t-0 xl:pt-0">
                                    @csrf
                                    <div>
                                        <label for="catatan_admin" class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Tulis Alasan Penolakan <span class="text-red-500">*</span>
                                        </label>
                                        <textarea id="catatan_admin" name="catatan_admin" rows="3" maxlength="500" required
                                            placeholder="Jelaskan alasan pengajuan tidak dapat disetujui."
                                            class="{{ $kelasTeks }}">{{ old('catatan_admin') }}</textarea>
                                    </div>

                                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-red-300 bg-white text-sm font-semibold text-red-600 transition-all duration-200 hover:bg-red-600 hover:border-red-600 hover:text-white hover:shadow-lg hover:shadow-red-600/30 active:scale-[0.98]">
                                        Tolak Pengajuan
                                    </button>
                                </form>
                            </div>
                        @elseif ($p->status === 'menunggu_pengambilan')
                            <form method="POST" action="{{ route('admin.peminjaman.ambil', $p) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="catatan_serah_terima" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Catatan Serah Terima <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="catatan_serah_terima" name="catatan_admin" rows="3" maxlength="500" required
                                        placeholder="Contoh: Kamera dan kelengkapannya diserahkan dalam kondisi baik."
                                        class="{{ $kelasTeks }}">{{ old('catatan_admin') }}</textarea>
                                </div>
                                <p class="group flex items-start gap-2 text-sm leading-relaxed text-gray-600">
                                    <svg class="w-4 h-4 mt-1 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Tulis catatan serah terima selengkap mungkin dan cantumkan tempat pengembalian barang.</span>
                                </p>
                                <div class="flex sm:justify-end">
                                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                        Tandai Alat Diambil
                                    </button>
                                </div>
                            </form>
                        @elseif ($p->status === 'dipinjam')
                            <form method="POST" action="{{ route('admin.peminjaman.kembali', $p) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="kondisi_alat_kembali" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Kondisi alat saat dikembalikan <span class="text-red-500">*</span>
                                    </label>
                                    <select id="kondisi_alat_kembali" name="kondisi_alat_kembali" required class="{{ $kelasInput }} cursor-pointer">
                                        <option value="" disabled @selected(! old('kondisi_alat_kembali'))>Pilih kondisi alat</option>
                                        @foreach (\App\Http\Controllers\Admin\PeminjamanAdminController::KONDISI as $opsi)
                                            <option value="{{ $opsi }}" @selected(old('kondisi_alat_kembali') === $opsi)>{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="catatan_admin" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Catatan pengembalian <span class="font-normal text-gray-400">(opsional)</span>
                                    </label>
                                    <textarea id="catatan_admin" name="catatan_admin" rows="3" maxlength="500"
                                        placeholder="Contoh: Lensa kit ada goresan halus di bagian depan."
                                        class="{{ $kelasTeks }}">{{ old('catatan_admin') }}</textarea>
                                </div>

                                <p class="group flex items-start gap-2 text-xs leading-relaxed text-gray-600">
                                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Periksa kelengkapan alat bersama peminjam sebelum menandai alat sudah dikembalikan.</span>
                                </p>

                                <div class="flex sm:justify-end">
                                    <button type="submit"
                                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                        Tandai Dikembalikan
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm leading-relaxed text-gray-500">Pengajuan ini sudah {{ $p->status === 'selesai' ? 'selesai' : 'ditolak' }}, tidak ada tindakan lain yang perlu dilakukan.</p>
                        @endif
                    </section>
                @endif

                <!-- Catatan admin (peminjam: hanya kalau ada isinya; admin: bisa diubah setelah pengajuan diputuskan) -->
                @if ($adaCatatan)
                    <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6 lg:p-8 transition-all duration-300 hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-900/15">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 transition-all duration-300 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/25 motion-safe:hover:-translate-y-0.5 motion-safe:hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-900 leading-tight">Catatan Admin</h2>
                                <p class="text-xs leading-relaxed text-gray-500 mt-0.5">Keterangan yang dicatat admin untuk pengajuan ini.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if ($kunci === 'selesai')
                                <div class="group/sel rounded-lg p-2 -m-2 transition-all duration-200 hover:bg-blue-50 hover:shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 transition-colors duration-200 group-hover/sel:text-blue-600">Kondisi alat saat dikembalikan</p>
                                    <p class="mt-1.5">
                                        <span class="inline-block rounded-full bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700 transition-colors duration-200 group-hover/sel:bg-white">{{ $p->kondisi_alat_kembali ?: 'Tidak dicatat' }}</span>
                                    </p>
                                </div>
                            @endif

                            <!-- Tampilan catatan -->
                            <div id="tampil-catatan" class="space-y-4" @if ($bolehUbahCatatan && $formUbahTerbuka) hidden @endif>
                                @if ($catatanAdminTampil)
                                    <div class="group/sel rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 transition-all duration-200 hover:border-blue-200 hover:bg-blue-50 hover:shadow-md hover:shadow-blue-900/5">
                                        <p class="text-sm leading-relaxed text-gray-700 whitespace-pre-line break-words transition-colors duration-200 group-hover/sel:text-gray-900">{{ $catatanAdminTampil }}</p>
                                    </div>
                                @elseif ($kunci === 'selesai')
                                    <p class="text-sm leading-relaxed text-gray-500">Admin tidak menambahkan catatan lain.</p>
                                @else
                                    <p class="text-sm leading-relaxed text-gray-500">Belum ada catatan.</p>
                                @endif

                                @if ($bolehUbahCatatan)
                                    <button type="button" id="tombol-ubah-catatan" aria-expanded="false" aria-controls="form-ubah-catatan"
                                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-blue-300 bg-white text-sm font-semibold text-blue-700 transition-all duration-200 hover:bg-blue-600 hover:border-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ $catatanAdminTampil ? 'Ubah Catatan' : 'Tambah Catatan' }}
                                    </button>
                                @endif
                            </div>

                            <!-- Form ubah catatan (khusus admin) -->
                            @if ($bolehUbahCatatan)
                                <form id="form-ubah-catatan" method="POST" action="{{ route('admin.peminjaman.catatan', $p) }}" class="space-y-4" @unless ($formUbahTerbuka) hidden @endunless>
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="ubah_catatan" class="block text-sm font-medium text-gray-700 mb-1.5">
                                            {{ $p->status === 'menunggu_pengambilan' ? 'Catatan tempat pengambilan' : 'Catatan admin' }}
                                            @if ($catatanWajib)
                                                <span class="text-red-500">*</span>
                                            @else
                                                <span class="font-normal text-gray-400">(opsional)</span>
                                            @endif
                                        </label>
                                        <textarea id="ubah_catatan" name="catatan" rows="4" maxlength="500" @if ($catatanWajib) required @endif
                                            placeholder="Tulis catatan untuk pengajuan ini."
                                            class="{{ $kelasTeks }}">{{ old('catatan', $catatanAdminTampil) }}</textarea>
                                        <p class="mt-1.5 text-xs leading-relaxed text-gray-500">{{ $petunjukCatatan }}</p>
                                    </div>

                                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                        <button type="button" id="tombol-batal-catatan"
                                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 transition-all duration-200 hover:bg-gray-100 active:scale-[0.98]">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 motion-safe:hover:-translate-y-0.5 active:scale-[0.98]">
                                            Simpan Catatan
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </main>

    @if ($admin)
        <!-- Footer admin (sama dengan halaman riwayat admin) -->
        <footer class="border-t border-gray-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-center text-xs text-gray-600 sm:flex-row sm:px-6 sm:text-left lg:px-8 [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
                <p>&copy; {{ date('Y') }} SiLat Multimedia</p>
                <p>&copy; {{ date('Y') }} Mulmedska SMK Negeri 5 Surakarta</p>
            </div>
        </footer>
    @else
        <!-- Footer ringkas peminjam (sama dengan halaman riwayat siswa) -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-center text-xs text-gray-600 sm:text-left [&>p]:transition-colors [&>p]:duration-200 [&>p:hover]:text-gray-900">
                <p>Ada kendala dengan pengajuanmu?<span class="mt-0.5 block font-bold text-blue-600">Temui admin Ekskul Multimedia secara langsung.</span></p>
                <p>&copy; {{ date('Y') }} SiLat Multimedia &middot; Mulmedska SMK Negeri 5 Surakarta</p>
            </div>
        </footer>
    @endif

    <script>
        // Bayangan navbar muncul saat halaman digulir
        (function () {
            var navbar = document.getElementById('navbar');
            function periksa() { navbar.classList.toggle('shadow-md', window.scrollY > 8); }
            window.addEventListener('scroll', periksa, { passive: true });
            periksa();
        })();

        // Catatan admin: tombol ubah membuka form, tombol batal menutupnya kembali
        (function () {
            var tombolUbah = document.getElementById('tombol-ubah-catatan');
            var formUbah   = document.getElementById('form-ubah-catatan');
            var tampilan   = document.getElementById('tampil-catatan');
            var tombolBatal = document.getElementById('tombol-batal-catatan');
            if (!formUbah || !tampilan) return;

            var kolom = document.getElementById('ubah_catatan');
            var isiAwal = kolom ? kolom.value : '';

            function atur(buka) {
                formUbah.hidden = !buka;
                tampilan.hidden = buka;
                if (tombolUbah) tombolUbah.setAttribute('aria-expanded', buka ? 'true' : 'false');
                if (buka && kolom) {
                    kolom.focus();
                    kolom.setSelectionRange(kolom.value.length, kolom.value.length);
                } else if (!buka && tombolUbah) {
                    tombolUbah.focus();
                }
            }

            if (tombolUbah) tombolUbah.addEventListener('click', function () { atur(true); });
            if (tombolBatal) tombolBatal.addEventListener('click', function () {
                if (kolom) kolom.value = isiAwal;
                atur(false);
            });
        })();
    </script>
</body>
</html>