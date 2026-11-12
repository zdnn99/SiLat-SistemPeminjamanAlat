<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeminjamanAdminController extends Controller
{
    public const KONDISI = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    public function riwayat(Request $request): View
    {
        $kata = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'semua');
        $urut = $request->query('urut') === 'terlama' ? 'terlama' : 'terbaru';

        if (! in_array($status, ['menunggu_persetujuan', 'menunggu_pengambilan', 'dipinjam', 'selesai', 'ditolak'], true)) {
            $status = 'semua';
        }

        $query = Peminjaman::with(['alat', 'user']);
        if ($kata !== '') {
            if (preg_match('/^(?:(?:PMJ|PJM)-?)?0*(\d+)$/i', $kata, $cocok)) {
                $query->whereKey((int) $cocok[1]);
            } else {
                $query->where(function ($pencarian) use ($kata) {
                    $pencarian->whereHas('user', fn ($user) => $user->where('nama_lengkap', 'like', '%' . $kata . '%'))
                        ->orWhereHas('alat', fn ($alat) => $alat->where('nama_alat', 'like', '%' . $kata . '%'));
                });
            }
        }

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $arah = $urut === 'terlama' ? 'asc' : 'desc';
        $peminjaman = $query->orderBy('tanggal_pinjam', $arah)->orderBy('id', $arah)
            ->paginate(15)
            ->withQueryString();

        return view('admin.peminjaman.riwayat', compact('peminjaman', 'kata', 'status', 'urut'));
    }

    public function setujui(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $request->merge(['catatan_pengambilan' => $this->rapikan($request->input('catatan_pengambilan'))]);
        $validated = $request->validate([
            'catatan_pengambilan' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($peminjaman, $validated) {
            $peminjaman = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();
            $this->pastikanStatus($peminjaman, 'menunggu_persetujuan');

            $peminjaman->status = 'menunggu_pengambilan';
            $peminjaman->catatan_pengambilan = $validated['catatan_pengambilan'];
            $peminjaman->catatan_admin = null;
            $peminjaman->save();
        });

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('sukses', 'Pengajuan disetujui. Peminjam menunggu pengambilan alat.');
    }

    public function ambil(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $request->merge(['catatan_admin' => $this->rapikanCatatan($request->input('catatan_admin'))]);
        $validated = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ], [
            'catatan_admin.required' => 'Catatan saat alat diserahkan wajib diisi.',
            'catatan_admin.max' => 'Catatan admin maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($peminjaman, $validated) {
            $peminjaman = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();
            $this->pastikanStatus($peminjaman, 'menunggu_pengambilan');

            if (now()->startOfDay()->lt($peminjaman->tanggal_pinjam)) {
                throw ValidationException::withMessages([
                    'status' => 'Alat baru dapat ditandai diambil mulai tanggal peminjaman yang diajukan.',
                ]);
            }

            $peminjaman->status = 'dipinjam';
            $peminjaman->tanggal_diambil = now();
            $peminjaman->catatan_admin = $validated['catatan_admin'];
            $peminjaman->save();
        });

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('sukses', 'Pengambilan alat berhasil dicatat. Masa peminjaman dimulai.');
    }

    public function tolak(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $request->merge(['catatan_admin' => $this->rapikan($request->input('catatan_admin'))]);
        $validated = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
            'catatan_admin.max' => 'Catatan admin maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($peminjaman, $validated) {
            $peminjaman = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();
            $this->pastikanStatus($peminjaman, 'menunggu_persetujuan');

            $peminjaman->status = 'ditolak';
            $peminjaman->catatan_admin = $validated['catatan_admin'];
            $peminjaman->save();
        });

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('sukses', 'Pengajuan peminjaman berhasil ditolak.');
    }

    public function kembali(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $request->merge(['catatan_admin' => $this->rapikan($request->input('catatan_admin'))]);
        $validated = $request->validate([
            'kondisi_alat_kembali' => ['required', 'string', 'in:' . implode(',', self::KONDISI)],
            'catatan_admin' => ['nullable', 'string', 'max:500'],
        ], [
            'kondisi_alat_kembali.required' => 'Kondisi alat saat dikembalikan wajib dipilih.',
            'kondisi_alat_kembali.in' => 'Kondisi alat yang dipilih tidak valid.',
            'catatan_admin.max' => 'Catatan admin maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($peminjaman, $validated) {
            $peminjaman = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();
            $this->pastikanStatus($peminjaman, 'dipinjam');

            $catatanPengembalian = trim($validated['catatan_admin'] ?? '');
            $peminjaman->catatan_admin = $catatanPengembalian !== ''
                ? 'Catatan pengembalian: ' . $catatanPengembalian
                : null;

            $peminjaman->kondisi_alat_kembali = $validated['kondisi_alat_kembali'];
            $peminjaman->status = 'selesai';
            $peminjaman->tanggal_dikembalikan = now();
            $peminjaman->save();
        });

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('sukses', 'Pengembalian alat berhasil dicatat.');
    }

    /**
     * Ubah catatan admin dari halaman detail.
     * Saat menunggu pengambilan, yang diubah adalah catatan tempat pengambilan;
     * pada status lain yang diubah adalah catatan admin.
     */
    public function ubahCatatan(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $request->merge(['catatan' => $this->rapikanCatatan($request->input('catatan'))]);

        $wajib = $peminjaman->status !== 'selesai';
        $validated = $request->validate([
            'catatan' => [$wajib ? 'required' : 'nullable', 'string', 'max:500'],
        ], [
            'catatan.required' => 'Catatan tidak boleh kosong.',
            'catatan.max' => 'Catatan admin maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($peminjaman, $validated) {
            $peminjaman = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();

            if ($peminjaman->status === 'menunggu_persetujuan') {
                throw ValidationException::withMessages([
                    'catatan' => 'Catatan baru bisa diubah setelah pengajuan disetujui atau ditolak.',
                ]);
            }

            $catatan = trim($validated['catatan'] ?? '');
            if ($catatan !== '') {
                $catatan = $this->rapikanCatatan($catatan);
            }

            if ($peminjaman->status === 'menunggu_pengambilan') {
                $peminjaman->catatan_pengambilan = $catatan !== '' ? $catatan : null;
            } elseif ($peminjaman->status === 'selesai') {
                // Pertahankan format Catatan pengembalian secara terkunci & otomatis
                $catatanBersih = preg_replace('/^Catatan pengembalian:\s*/i', '', $catatan);
                if ($catatanBersih !== '') {
                    $catatanBersih = $this->rapikanCatatan($catatanBersih);
                }
                $peminjaman->catatan_admin = $catatanBersih !== '' ? 'Catatan pengembalian: ' . $catatanBersih : null;
            } else {
                $peminjaman->catatan_admin = $catatan !== '' ? $catatan : null;
            }

            $peminjaman->save();
        });

        return back()->with('sukses', 'Catatan admin berhasil diperbarui.');
    }

    /** Format catatan: huruf depan besar (kapital), seluruh karakter selain itu kecil. */
    private function rapikanCatatan(mixed $teks): string
    {
        $t = trim(preg_replace('/\s+/', ' ', (string) $teks));
        if ($t === '') {
            return '';
        }

        $lower = mb_strtolower($t, 'UTF-8');

        $result = preg_replace_callback('/\p{L}/u', function ($matches) {
            return mb_strtoupper($matches[0], 'UTF-8');
        }, $lower, 1);

        return $result ?? $t;
    }

    /** Merapikan spasi, mencegah capslock/lowercase penuh, dan memastikan huruf pertama kapital. */
    private function rapikan(mixed $teks): string
    {
        $t = trim(preg_replace('/\s+/', ' ', (string) $teks));
        if ($t === '') {
            return '';
        }

        if (mb_strlen($t) > 3 && mb_strtoupper($t, 'UTF-8') === $t) {
            $t = mb_strtolower($t, 'UTF-8');
        }

        return mb_strtoupper(mb_substr($t, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($t, 1, null, 'UTF-8');
    }

    private function pastikanStatus(Peminjaman $peminjaman, string $status): void
    {
        if ($peminjaman->status !== $status) {
            throw ValidationException::withMessages([
                'status' => 'Status pengajuan sudah berubah. Muat ulang halaman sebelum melakukan tindakan.',
            ]);
        }
    }
}