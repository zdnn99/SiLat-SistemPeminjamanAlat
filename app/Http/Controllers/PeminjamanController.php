<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    /** Lama peminjaman maksimal (hari), dihitung sejak tanggal mulai. */
    private const MAKS_HARI = 14;

    /**
     * Menampilkan riwayat peminjaman milik user yang sedang masuk.
     */
    public function riwayat(Request $request)
    {
        $statusAktif = $request->query('status', 'semua');
        if (! in_array($statusAktif, ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam', 'selesai', 'ditolak'], true)) {
            $statusAktif = 'semua';
        }

        $urutAktif = $request->query('urut') === 'terlama' ? 'terlama' : 'terbaru';
        $arah = $urutAktif === 'terlama' ? 'asc' : 'desc';

        $query = Peminjaman::with('alat')
            ->where('user_id', $request->user()->id)
            ->orderBy('tanggal_pinjam', $arah)
            ->orderBy('id', $arah);

        if ($statusAktif !== 'semua') {
            $query->where('status', $statusAktif);
        }

        $peminjaman = $query->paginate(10)->withQueryString();

        return view('peminjaman.riwayat', compact('peminjaman', 'statusAktif', 'urutAktif'));
    }

    /**
     * Menampilkan detail peminjaman. Pemilik boleh melihat miliknya, admin boleh melihat semuanya.
     */
    public function show(Peminjaman $peminjaman)
    {
        $user = Auth::user();

        abort_unless(
            $user->peran === 'admin' || (int) $peminjaman->user_id === (int) $user->id,
            404
        );

        $peminjaman->load(['alat', 'user']);

        return view('peminjaman.show', compact('peminjaman'));
    }

    /**
     * Menampilkan form pengajuan peminjaman untuk satu alat (lewat slug).
     * Ketersediaan dicek per tanggal saat pengajuan dikirim (lihat store), bukan di sini,
     * karena alat yang sedang dipinjam tetap boleh diajukan untuk tanggal sesudahnya.
     */
    public function create(string $slug)
    {
        $alatDb = Alat::where('slug', $slug)->firstOrFail();
        abort_unless($alatDb->tersedia, 404);
        $alatDaftar = Alat::orderBy('id')->get();

        return view('peminjaman.form', compact('alatDb', 'alatDaftar'));
    }

    /**
     * Menyimpan pengajuan peminjaman baru (status awal: menunggu persetujuan admin).
     */
    public function store(Request $request)
    {
        // Spasi di awal/akhir tidak dihitung sebagai isi keperluan
        $request->merge(['keperluan' => trim((string) $request->input('keperluan'))]);

        $validated = $request->validate([
            'alat_id'         => ['required', 'exists:alat,id'],
            'tanggal_pinjam'  => ['required', 'date', 'after_or_equal:today'],
            'tanggal_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan'       => ['required', 'string', 'min:10', 'max:250'],
            'persetujuan'     => ['accepted'],
        ], [
            'alat_id.required'                 => 'Alat tidak terbaca. Muat ulang halaman lalu coba lagi.',
            'alat_id.exists'                   => 'Alat yang dipilih tidak ditemukan.',
            'tanggal_pinjam.required'          => 'Tanggal mulai meminjam wajib diisi.',
            'tanggal_pinjam.date'              => 'Tanggal mulai tidak valid.',
            'tanggal_pinjam.after_or_equal'    => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_kembali.required'         => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.date'             => 'Tanggal kembali tidak valid.',
            'tanggal_kembali.after_or_equal'   => 'Tanggal kembali tidak boleh sebelum tanggal mulai.',
            'keperluan.required'               => 'Keperluan peminjaman wajib diisi.',
            'keperluan.min'                    => 'Jelaskan keperluan minimal 10 karakter.',
            'keperluan.max'                    => 'Keperluan maksimal 250 karakter.',
            'persetujuan.accepted'             => 'Kamu harus menyetujui aturan peminjaman terlebih dahulu.',
        ]);

        $mulai   = Carbon::parse($validated['tanggal_pinjam'])->startOfDay();
        $kembali = Carbon::parse($validated['tanggal_kembali'])->startOfDay();

        // Lama peminjaman maksimal 14 hari sejak tanggal mulai
        if ($mulai->copy()->addDays(self::MAKS_HARI)->lt($kembali)) {
            throw ValidationException::withMessages([
                'tanggal_kembali' => 'Maksimal peminjaman adalah ' . self::MAKS_HARI . ' hari sejak tanggal mulai.',
            ]);
        }

        if (Auth::user()->status_akun === 'minta_hapus') {
            throw ValidationException::withMessages([
                'alat_id' => 'Akun Anda sedang dalam proses pengajuan penghapusan. Batalkan pengajuan hapus di menu Profil jika ingin meminjam alat.',
            ]);
        }

        $peminjaman = DB::transaction(function () use ($validated, $mulai, $kembali) {
            // Kunci baris alat supaya dua pengajuan bersamaan tidak sama-sama lolos
            $alat = Alat::whereKey($validated['alat_id'])->lockForUpdate()->firstOrFail();

            // Alat yang dimatikan admin tidak boleh diajukan, walaupun URL form dibuka langsung
            if (! $alat->tersedia) {
                throw ValidationException::withMessages([
                    'alat_id' => 'Alat ini sedang tidak tersedia untuk dipinjam.',
                ]);
            }

            // Satu pengajuan aktif per alat untuk tiap peminjam
            $sudahAda = Peminjaman::where('user_id', Auth::id())
                ->where('alat_id', $alat->id)
                ->whereIn('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam'])
                ->exists();

            if ($sudahAda) {
                throw ValidationException::withMessages([
                    'alat_id' => 'Kamu masih punya pengajuan atau peminjaman aktif untuk alat ini.',
                ]);
            }

            // Pengajuan aktif mengunci unit sampai peminjaman selesai atau ditolak.
            $sedangDigunakan = Peminjaman::where('alat_id', $alat->id)
                ->whereIn('status', ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam'])
                ->count();

            if ($sedangDigunakan >= $alat->stok) {
                throw ValidationException::withMessages([
                    'alat_id' => 'Alat ini sedang dipesan atau dipinjam. Pilih alat lain setelah peminjaman selesai atau ditolak.',
                ]);
            }

            return Peminjaman::create([
                'user_id'         => Auth::id(),
                'alat_id'         => $alat->id,
                'tanggal_pinjam'  => $mulai->toDateString(),
                'tanggal_kembali' => $kembali->toDateString(),
                'keperluan'       => $validated['keperluan'],
                'status'          => 'menunggu_persetujuan',
            ]);
        });

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('sukses', 'Pengajuan peminjaman berhasil dikirim. Menunggu persetujuan admin.');
    }
}