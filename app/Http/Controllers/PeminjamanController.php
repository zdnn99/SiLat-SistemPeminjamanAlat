<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan form pengajuan peminjaman untuk satu alat spesifik.
     */
    public function create(string $slug)
    {
        $alat = Alat::where('slug', $slug)->firstOrFail();

        if (! $alat->tersedia) {
            return redirect()->route('dashboard')
                ->with('error', 'Alat ini sedang tidak tersedia untuk dipinjam.');
        }

        return view('peminjaman.form', compact('alat'));
    }

    /**
     * Menyimpan pengajuan peminjaman baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'alat_id' => 'required|exists:alat,id',
            'tanggal_kembali' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(14)->format('Y-m-d'),
            'keperluan' => 'nullable|string|max:500',
            'persetujuan' => 'required|accepted',
        ], [
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum hari ini.',
            'tanggal_kembali.before_or_equal' => 'Maksimal peminjaman adalah 14 hari dari hari ini.',
            'persetujuan.accepted' => 'Kamu harus menyetujui pernyataan tanggung jawab sebelum mengajukan.',
        ]);

        $alat = Alat::findOrFail($validated['alat_id']);

        // Cek ulang ketersediaan stok di server (jaga-jaga alat baru saja habis dipinjam orang lain)
        if (! $alat->tersedia) {
            return back()->withErrors([
                'alat_id' => 'Stok alat ini baru saja habis, silakan pilih alat lain.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $alat) {
            Peminjaman::create([
                'user_id' => Auth::id(),
                'alat_id' => $alat->id,
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_kembali' => $validated['tanggal_kembali'],
                'keperluan' => $validated['keperluan'] ?? null,
                'status' => 'menunggu_persetujuan',
            ]);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Pengajuan peminjaman berhasil dibuat! Menunggu persetujuan admin.');
    }
}