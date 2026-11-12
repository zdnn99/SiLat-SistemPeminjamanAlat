<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AlatAdminController extends Controller
{
    /**
     * Halaman Tambah Alat. Memakai view yang sama dengan Kelola Alat;
     * model kosong (belum tersimpan, $alat->exists === false) menandakan view berada di mode tambah.
     */
    public function create(): View
    {
        return view('admin.alat.edit', ['alat' => new Alat()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validasi($request);

        $alat = new Alat();
        $alat->nama_alat   = trim($validated['nama_alat']);
        $alat->subjudul    = trim($validated['subjudul']);
        $alat->slug        = $this->buatSlug($alat->nama_alat);
        $alat->deskripsi   = trim($validated['deskripsi']);
        // Ketersediaan di model dihitung dari stok (lihat Alat::getTersediaAttribute),
        // jadi saklar "tersedia" diterjemahkan ke stok: 1 unit kalau tersedia, 0 kalau tidak.
        $alat->stok        = (bool) $validated['tersedia'] ? 1 : 0;
        $alat->kelengkapan = $this->rapikanKelengkapan($validated['kelengkapan'] ?? []) ?: null;

        if ($request->hasFile('foto')) {
            $alat->foto = $this->simpanFoto($request->file('foto'), $alat->slug);
        }

        $alat->save();

        return redirect()->route('admin.dashboard')
            ->with('sukses', $alat->nama_alat . ' berhasil ditambahkan.');
    }

    public function edit(Alat $alat): View
    {
        return view('admin.alat.edit', compact('alat'));
    }

    public function update(Request $request, Alat $alat): RedirectResponse
    {
        $validated = $this->validasi($request);

        $alat->nama_alat   = trim($validated['nama_alat']);
        if (! empty($validated['subjudul'])) {
            $alat->subjudul = trim($validated['subjudul']);
        }
        $alat->deskripsi   = trim($validated['deskripsi']);

        if ($request->has('stok')) {
            $alat->stok = (int) $request->input('stok');
        } elseif ($request->has('tersedia')) {
            $alat->stok = $this->hitungStok($alat, (bool) $request->input('tersedia'));
        }

        $inputKelengkapan = $request->input('kelengkapan');
        if (is_string($inputKelengkapan)) {
            $inputKelengkapan = preg_split('/\r\n|\r|\n/', $inputKelengkapan);
        }
        $alat->kelengkapan = $this->rapikanKelengkapan((array) ($inputKelengkapan ?? [])) ?: null;

        if ($request->has('aturan')) {
            $inputAturan = $request->input('aturan');
            if (is_string($inputAturan)) {
                $inputAturan = preg_split('/\r\n|\r|\n/', $inputAturan);
            }
            $alat->aturan = collect((array) ($inputAturan ?? []))
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all() ?: null;
        }

        $fotoLamaUnggahan = str_starts_with($alat->foto ?? '', 'uploads/');

        if ($request->hasFile('foto')) {
            $fotoBaru = $this->simpanFoto($request->file('foto'), $alat->slug);

            if ($fotoLamaUnggahan) {
                File::delete(public_path($alat->foto));
            }
            $alat->foto = $fotoBaru;
        } elseif ($request->boolean('hapus_foto') && $fotoLamaUnggahan) {
            File::delete(public_path($alat->foto));
            $alat->foto = null; // kembali memakai foto bawaan di public/images/alat
        }

        $alat->save();

        return redirect()->route('admin.alat.edit', $alat->slug)
            ->with('sukses', 'Data alat berhasil diperbarui.');
    }

    /**
     * Hapus alat. Hanya boleh kalau alat sudah tersimpan sebagai tidak tersedia
     * dan belum pernah dipinjam (aturan yang sama dengan tombol di halaman Kelola Alat).
     */
    public function destroy(Alat $alat): RedirectResponse
    {
        if ($alat->tersedia) {
            return back()->withErrors([
                'alat' => 'Alat masih tersedia. Matikan ketersediaan dan simpan perubahan terlebih dahulu.',
            ]);
        }

        if (Peminjaman::where('alat_id', $alat->id)->exists()) {
            return back()->withErrors([
                'alat' => 'Alat ini sudah punya riwayat peminjaman, jadi tidak bisa dihapus agar riwayat tetap utuh.',
            ]);
        }

        if (str_starts_with($alat->foto ?? '', 'uploads/')) {
            File::delete(public_path($alat->foto));
        }

        $nama = $alat->nama_alat;
        $alat->delete();

        return redirect()->route('admin.dashboard')
            ->with('sukses', $nama . ' berhasil dihapus.');
    }

    /** Aturan validasi yang sama untuk tambah dan ubah. */
    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama_alat'     => ['required', 'string', 'max:100'],
            'subjudul'      => ['nullable', 'string', 'max:60'],
            'deskripsi'     => ['required', 'string', 'min:10', 'max:500'],
            'tersedia'      => ['nullable'],
            'stok'          => ['nullable', 'integer', 'min:0'],
            'kelengkapan'   => ['nullable'],
            'aturan'        => ['nullable'],
            'foto'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto'    => ['nullable', 'boolean'],
        ], [
            'nama_alat.required' => 'Nama alat wajib diisi.',
            'subjudul.required'  => 'Subjudul alat wajib diisi.',
            'subjudul.max'       => 'Subjudul alat maksimal 60 karakter.',
            'deskripsi.required' => 'Deskripsi alat wajib diisi.',
            'deskripsi.min'      => 'Deskripsi alat minimal 10 karakter.',
            'deskripsi.max'      => 'Deskripsi alat maksimal 500 karakter.',
            'foto.image'         => 'File foto harus berupa gambar.',
            'foto.mimes'         => 'Foto harus berformat jpg, png, atau webp.',
            'foto.max'           => 'Ukuran foto maksimal 2 MB.',
        ]);
    }

    /**
     * Menerjemahkan saklar "tersedia" ke kolom stok (model Alat menghitung ketersediaan dari stok).
     * - Dimatikan: stok diturunkan sampai sama dengan jumlah unit yang sedang diajukan/dipinjam,
     *   jadi sisa stok 0 tetapi peminjaman yang berjalan tetap sah.
     * - Dinyalakan: kalau alat memang sudah tersedia, stok tidak diubah; kalau sebelumnya tidak tersedia,
     *   stok dibuat satu unit di atas jumlah yang sedang dipakai.
     */
    private function hitungStok(Alat $alat, bool $tersedia): int
    {
        $dipakai = $alat->jumlah_sedang_dipinjam;

        if ($tersedia) {
            return $alat->tersedia ? (int) $alat->stok : $dipakai + 1;
        }

        return min((int) $alat->stok, $dipakai);
    }

    /** Slug dari nama alat; kalau sudah dipakai ditambah angka di belakangnya (-2, -3, dst). */
    private function buatSlug(string $nama): string
    {
        $dasar = Str::slug($nama) ?: 'alat';
        $slug  = $dasar;
        $urut  = 2;

        while (Alat::where('slug', $slug)->exists()) {
            $slug = $dasar . '-' . $urut++;
        }

        return $slug;
    }

    /** Pindahkan foto unggahan ke public/uploads/alat dan kembalikan path relatifnya. */
    private function simpanFoto(UploadedFile $berkas, string $slug): string
    {
        $namaDasar = $slug . '-' . time();
        $targetDir = public_path('uploads/alat');
        $namaBerkas = app(ImageService::class)->saveAsWebp($berkas, $targetDir, $namaDasar, maxWidth: 1600, maxHeight: 1600);

        return 'uploads/alat/' . $namaBerkas;
    }

    /**
     * Buang baris kosong, rapikan spasi, dan besarkan huruf pertama tiap item.
     * Kalau semuanya kosong hasilnya array kosong (disimpan null supaya daftar bawaan dipakai lagi).
     */
    private function rapikanKelengkapan(array $daftar): array
    {
        return collect($daftar)
            ->map(fn ($item) => $this->hurufDepanKapital(trim((string) $item)))
            ->filter()
            ->values()
            ->all();
    }

    /** Huruf pertama menjadi kapital, sisanya dibiarkan ("Tali kamera", bukan "Tali Kamera"). */
    private function hurufDepanKapital(string $teks): string
    {
        if ($teks === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($teks, 0, 1)) . mb_substr($teks, 1);
    }
}