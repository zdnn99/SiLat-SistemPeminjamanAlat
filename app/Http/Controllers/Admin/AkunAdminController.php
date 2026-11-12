<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkunAdminController extends Controller
{
    // ===== Halaman Kelola Akun User (resources/views/admin/akun/kelola.blade.php) =====

    public function kelola(Request $request): View
    {
        // Pencarian hanya huruf, angka, dan spasi, selalu kapital (sejalan dengan kolom cari di halaman)
        $kata   = trim(mb_strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', (string) $request->query('q', ''))));
        $status = (string) $request->query('status', 'semua');
        $peran  = (string) $request->query('peran', 'semua');

        // Urutan dari menu di kepala tabel: tanggal daftar (terbaru/terlama) atau nama (A-Z/Z-A)
        $urut = (string) $request->query('urut', 'terbaru');

        if (! in_array($urut, ['terbaru', 'terlama', 'nama_az', 'nama_za'], true)) {
            $urut = 'terbaru';
        }

        if (! in_array($status, ['aktif', 'menunggu', 'nonaktif', 'minta_hapus'], true)) {
            $status = 'semua';
        }

        if (! in_array($peran, ['siswa', 'guru_karyawan'], true)) {
            $peran = 'semua';
        }

        $query = User::where('peran', '!=', 'admin');

        // Pencarian: nama lengkap, nomor induk, atau kelas jurusan
        if ($kata !== '') {
            $query->where(function ($sub) use ($kata) {
                $sub->where('nama_lengkap', 'like', '%' . $kata . '%')
                    ->orWhere('nomor_induk', 'like', '%' . $kata . '%')
                    ->orWhere('kelas_jurusan', 'like', '%' . $kata . '%');
            });
        }

        if ($status === 'minta_hapus') {
            $query->where(function ($sub) {
                $sub->where('status_akun', 'minta_hapus')
                    ->orWhereNotNull('permintaan_hapus_pada');
            });
        } elseif ($status !== 'semua') {
            $query->where('status_akun', $status);
        }

        if ($peran !== 'semua') {
            $query->where('peran', $peran);
        }

        // id dipakai sebagai pengurut kedua supaya urutan stabil saat nilainya sama
        match ($urut) {
            'nama_az' => $query->orderBy('nama_lengkap')->orderBy('id'),
            'nama_za' => $query->orderByDesc('nama_lengkap')->orderByDesc('id'),
            'terlama' => $query->orderBy('created_at')->orderBy('id'),
            default   => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $users = $query->with(['peminjaman' => function ($q) {
            // Hanya load peminjaman aktif agar bisa dicek di blade tanpa query tambahan
            $q->whereIn('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam'])
              ->select('id', 'user_id', 'status');
        }])->paginate(15)->withQueryString();

        $totalSemua        = User::where('peran', '!=', 'admin')->count();
        $jumlahAktif       = User::where('peran', '!=', 'admin')->where('status_akun', 'aktif')->count();
        $jumlahMenunggu    = User::where('peran', '!=', 'admin')->where('status_akun', 'menunggu')->count();
        $jumlahNonaktif    = User::where('peran', '!=', 'admin')->where('status_akun', 'nonaktif')->count();
        $jumlahMintaHapus  = User::where('peran', '!=', 'admin')->where(function ($sub) {
            $sub->where('status_akun', 'minta_hapus')->orWhereNotNull('permintaan_hapus_pada');
        })->count();

        return view('admin.akun.kelola', compact(
            'users',
            'kata',
            'status',
            'peran',
            'urut',
            'totalSemua',
            'jumlahAktif',
            'jumlahMenunggu',
            'jumlahNonaktif',
            'jumlahMintaHapus'
        ));
    }

    // Setujui pendaftaran: status_akun jadi 'aktif' (nilai yang dicek AuthController saat login)
    public function setujui(User $user): RedirectResponse
    {
        if (! $this->masihMenunggu($user)) {
            return back()->with('galat_akun', 'Akun ini sudah diproses sebelumnya.');
        }

        $user->status_akun = 'aktif';
        $user->save();

        return back()->with(
            'sukses_akun',
            'Akun ' . $user->nama_lengkap . ' disetujui. Sekarang ia bisa masuk ke SiLat.'
        );
    }

    // Tolak pendaftaran: datanya dihapus supaya pendaftar bisa mendaftar ulang dengan Nomor Induk yang sama
    public function tolak(User $user): RedirectResponse
    {
        if (! $this->masihMenunggu($user)) {
            return back()->with('galat_akun', 'Akun ini sudah diproses sebelumnya.');
        }

        $nama = $user->nama_lengkap;
        $user->delete();

        return back()->with(
            'sukses_akun',
            'Pendaftaran ' . $nama . ' ditolak dan datanya dihapus.'
        );
    }

    // Nonaktifkan akun aktif atau akun yang meminta hapus
    public function nonaktifkan(User $user): RedirectResponse
    {
        if ($user->peran === 'admin') {
            return back()->with('galat_akun', 'Akun admin tidak bisa dinonaktifkan.');
        }

        if (! in_array($user->status_akun, ['aktif', 'minta_hapus'], true)) {
            return back()->with('galat_akun', 'Akun ini tidak dapat dinonaktifkan.');
        }

        $user->permintaan_hapus_pada = null;
        $user->status_akun = 'nonaktif';
        $user->save();

        return back()->with(
            'sukses_akun',
            'Akun ' . $user->nama_lengkap . ' berhasil dinonaktifkan.'
        );
    }

    // Aktifkan kembali akun yang nonaktif
    public function aktifkan(User $user): RedirectResponse
    {
        if ($user->peran === 'admin') {
            return back()->with('galat_akun', 'Operasi tidak valid untuk akun admin.');
        }

        if ($user->status_akun === 'aktif') {
            return back()->with('galat_akun', 'Akun ini sudah aktif.');
        }

        $user->status_akun = 'aktif';
        $user->save();

        return back()->with(
            'sukses_akun',
            'Akun ' . $user->nama_lengkap . ' berhasil diaktifkan kembali.'
        );
    }

    // Tolak permintaan hapus akun dan kembalikan ke aktif
    public function batalHapus(User $user): RedirectResponse
    {
        if ($user->peran === 'admin') {
            return back()->with('galat_akun', 'Operasi tidak valid untuk akun admin.');
        }

        if ($user->status_akun !== 'minta_hapus' && ! $user->permintaan_hapus_pada) {
            return back()->with('galat_akun', 'Akun ini tidak sedang mengajukan penghapusan.');
        }

        $user->permintaan_hapus_pada = null;
        $user->status_akun = 'aktif';
        $user->save();

        return back()->with(
            'sukses_akun',
            'Permintaan hapus akun ' . $user->nama_lengkap . ' ditolak. Akun kembali aktif.'
        );
    }

    // Hapus akun permanen
    public function hapus(User $user): RedirectResponse
    {
        if ($user->peran === 'admin') {
            return back()->with('galat_akun', 'Akun admin tidak bisa dihapus dari sini.');
        }

        // Blokir penghapusan jika user masih punya peminjaman aktif
        $peminjamanAktif = Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam'])
            ->exists();

        if ($peminjamanAktif) {
            return back()->with(
                'galat_akun',
                'Tidak dapat menghapus akun ' . $user->nama_lengkap . ' karena masih memiliki peminjaman aktif (menunggu persetujuan, menunggu pengambilan, atau sedang dipinjam).'
            );
        }

        $nama = $user->nama_lengkap;
        $user->delete();

        return back()->with(
            'sukses_akun',
            'Akun ' . $nama . ' berhasil dihapus permanen.'
        );
    }

    // Hanya akun non-admin yang berstatus 'menunggu' yang boleh disetujui/ditolak
    private function masihMenunggu(User $user): bool
    {
        return $user->peran !== 'admin' && $user->status_akun === 'menunggu';
    }
}