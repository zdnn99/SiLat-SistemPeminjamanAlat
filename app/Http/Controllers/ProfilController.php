<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    private const FOLDER_FOTO = 'uploads/profil';

    public function show()
    {
        return view('profil.show', ['user' => Auth::user()]);
    }

    /**
     * Ganti foto profil (tanpa PIN).
     */
    public function updateFoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 2 MB.',
        ]);

        $user = Auth::user();
        $file = $request->file('foto');
        $namaDasar = 'user-' . $user->id . '-' . time();
        $targetDir = public_path(self::FOLDER_FOTO);
        $namaFile = app(ImageService::class)->saveAsWebp($file, $targetDir, $namaDasar, maxWidth: 800, maxHeight: 800);

        $this->hapusFileFoto($user);

        $user->foto_profil = $namaFile;
        $user->save();

        return back()->with('sukses_foto', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil, tampilan kembali ke huruf inisial.
     */
    public function hapusFoto(): RedirectResponse
    {
        $user = Auth::user();

        $this->hapusFileFoto($user);

        $user->foto_profil = null;
        $user->save();

        return back()->with('sukses_foto', 'Foto profil dihapus. Tampilan kembali ke inisial nama.');
    }

    /**
     * Ubah nama lengkap (dan kelas untuk siswa).
     * Siswa dan guru dikonfirmasi PIN; admin langsung tersimpan tanpa PIN.
     */
    public function updateData(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $admin = $user->peran === 'admin';
        $siswa = $user->peran === 'siswa';

        $aturan = [
            'nama_lengkap' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        ];

        if (! $admin) {
            $aturan['pin_info'] = ['required', 'digits:6'];
        }

        if ($siswa) {
            $aturan['kelas_jurusan'] = ['required', Rule::in(self::daftarKelas())];
        }

        $validator = Validator::make($request->all(), $aturan, [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'kelas_jurusan.required' => 'Pilih Kelas & Jurusan Anda.',
            'kelas_jurusan.in' => 'Kelas & Jurusan tidak valid.',
            'pin_info.required' => 'Masukkan PIN Pemulihan untuk konfirmasi.',
            'pin_info.digits' => 'PIN Pemulihan terdiri dari 6 angka.',
        ]);

        if ($validator->fails()) {
            return $this->kembali('data-diri')
                ->withErrors($validator)
                ->withInput($request->except('pin_info'));
        }

        if (! $admin) {
            $pesan = $this->pinBermasalah($user, (string) $request->input('pin_info'));

            if ($pesan) {
                return $this->kembali('data-diri')
                    ->withErrors(['pin_info' => $pesan])
                    ->withInput($request->except('pin_info'));
            }
        }

        $user->nama_lengkap = strtoupper(preg_replace('/\s+/', ' ', trim($request->input('nama_lengkap'))));

        if ($siswa) {
            $user->kelas_jurusan = $request->input('kelas_jurusan');
        }

        $user->save();

        return $this->kembali('data-diri')->with('sukses_info', 'Data diri berhasil diperbarui.');
    }

    /**
     * Ganti kata sandi.
     * Siswa dan guru dikonfirmasi PIN; admin langsung tersimpan tanpa PIN.
     */
    public function updateKataSandi(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $admin = $user->peran === 'admin';

        $aturan = [
            'password_baru' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        if (! $admin) {
            $aturan['pin_sandi'] = ['required', 'digits:6'];
        }

        $validator = Validator::make($request->all(), $aturan, [
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min' => 'Kata sandi baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'pin_sandi.required' => 'Masukkan PIN Pemulihan untuk konfirmasi.',
            'pin_sandi.digits' => 'PIN Pemulihan terdiri dari 6 angka.',
        ]);

        if ($validator->fails()) {
            return $this->kembali('kata-sandi')->withErrors($validator);
        }

        // PIN dicek lebih dulu, supaya pengecekan "sama dengan sandi lama" tidak bisa dipakai menebak sandi
        if (! $admin) {
            $pesan = $this->pinBermasalah($user, (string) $request->input('pin_sandi'));

            if ($pesan) {
                return $this->kembali('kata-sandi')->withErrors(['pin_sandi' => $pesan]);
            }
        }

        if (Hash::check($request->input('password_baru'), $user->password)) {
            return $this->kembali('kata-sandi')
                ->withErrors(['password_baru' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama.']);
        }

        $user->password = Hash::make($request->input('password_baru'));
        $user->save();

        return $this->kembali('kata-sandi')->with('sukses_sandi', 'Kata sandi berhasil diubah.');
    }

    /**
     * Ganti PIN Pemulihan, dikonfirmasi PIN lama (berlaku untuk semua peran, termasuk admin).
     */
    public function updatePin(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'pin_lama' => ['required', 'digits:6'],
            'pin_baru' => ['required', 'digits:6', 'confirmed'],
        ], [
            'pin_lama.required' => 'Masukkan PIN Pemulihan lama.',
            'pin_lama.digits' => 'PIN Pemulihan terdiri dari 6 angka.',
            'pin_baru.required' => 'Masukkan PIN Pemulihan baru.',
            'pin_baru.digits' => 'PIN Pemulihan baru harus 6 angka.',
            'pin_baru.confirmed' => 'Konfirmasi PIN baru tidak sama.',
        ]);

        if ($validator->fails()) {
            return $this->kembali('pin-pemulihan')->withErrors($validator);
        }

        if ($pesan = $this->pinBermasalah($user, (string) $request->input('pin_lama'))) {
            return $this->kembali('pin-pemulihan')->withErrors(['pin_lama' => $pesan]);
        }

        if ($request->input('pin_baru') === $request->input('pin_lama')) {
            return $this->kembali('pin-pemulihan')
                ->withErrors(['pin_baru' => 'PIN baru tidak boleh sama dengan PIN lama.']);
        }

        $user->pin = Hash::make($request->input('pin_baru'));
        $user->save();

        return $this->kembali('pin-pemulihan')->with('sukses_pin', 'PIN Pemulihan berhasil diganti. Hafalkan PIN yang baru.');
    }

    /**
     * Mengajukan permintaan penghapusan akun oleh user sendiri.
     * Perlu konfirmasi PIN dan dicek agar tidak ada peminjaman aktif.
     */
    public function ajukanHapus(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->peran === 'admin') {
            return $this->kembali('hapus-akun')->withErrors(['pin_hapus' => 'Akun admin tidak dapat diajukan untuk dihapus.']);
        }

        $validator = Validator::make($request->all(), [
            'password_hapus' => ['required', 'string'],
            'pin_hapus' => ['required', 'digits:6'],
        ], [
            'password_hapus.required' => 'Masukkan kata sandi akun.',
            'pin_hapus.required' => 'Masukkan PIN Pemulihan untuk konfirmasi penghapusan akun.',
            'pin_hapus.digits' => 'PIN Pemulihan terdiri dari 6 angka.',
        ]);

        if ($validator->fails()) {
            return $this->kembali('hapus-akun')->withErrors($validator);
        }

        if (! Hash::check($request->input('password_hapus'), $user->password)) {
            return $this->kembali('hapus-akun')->withErrors(['password_hapus' => 'Kata sandi salah.']);
        }

        if ($pesan = $this->pinBermasalah($user, (string) $request->input('pin_hapus'))) {
            return $this->kembali('hapus-akun')->withErrors(['pin_hapus' => $pesan]);
        }

        $peminjamanMenunggu = \App\Models\Peminjaman::where('user_id', $user->id)
            ->where('status', 'menunggu_persetujuan')
            ->exists();

        if ($peminjamanMenunggu) {
            return $this->kembali('hapus-akun')->withErrors([
                'pin_hapus' => 'Batalkan atau selesaikan peminjaman Anda terlebih dahulu sebelum menghapus akun.',
            ]);
        }

        $peminjamanAktifLain = \App\Models\Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['menunggu_pengambilan', 'dipinjam'])
            ->exists();

        if ($peminjamanAktifLain) {
            return $this->kembali('hapus-akun')->withErrors([
                'pin_hapus' => 'Selesaikan peminjaman Anda terlebih dahulu sebelum menghapus akun.',
            ]);
        }

        $user->permintaan_hapus_pada = now();
        $user->status_akun = 'minta_hapus';
        $user->save();

        return $this->kembali('hapus-akun')->with('sukses_hapus', 'Permintaan penghapusan akun berhasil diajukan dan sedang menunggu persetujuan admin.');
    }

    /**
     * Membatalkan permintaan penghapusan akun yang sebelumnya diajukan.
     */
    public function batalHapus(): RedirectResponse
    {
        $user = Auth::user();

        $user->permintaan_hapus_pada = null;
        if ($user->status_akun === 'minta_hapus') {
            $user->status_akun = 'aktif';
        }
        $user->save();

        return $this->kembali('hapus-akun')->with('sukses_hapus', 'Permintaan penghapusan akun berhasil dibatalkan.');
    }

    // ===== Fungsi bantu =====

    /**
     * Cek PIN dengan batas 5 percobaan per 5 menit.
     * Mengembalikan pesan error kalau bermasalah, atau null kalau PIN benar.
     */
    private function pinBermasalah(User $user, string $pin): ?string
    {
        $kunci = 'profil-pin:' . $user->id;

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);

            return "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.";
        }

        if (! Hash::check($pin, (string) $user->pin)) {
            RateLimiter::hit($kunci, 300);

            return 'PIN Pemulihan salah.';
        }

        RateLimiter::clear($kunci);

        return null;
    }

    private function hapusFileFoto(User $user): void
    {
        if ($user->foto_profil) {
            File::delete(public_path(self::FOLDER_FOTO . '/' . basename($user->foto_profil)));
        }
    }

    private function kembali(string $bagian): RedirectResponse
    {
        return redirect()->to(route('profil.show') . '#' . $bagian);
    }

    /**
     * Semua kelas yang sah, diambil dari config/kelas.php (kunci "daftar").
     * Aman untuk daftar bersarang (per jurusan) maupun daftar datar.
     */
    private static function daftarKelas(): array
    {
        return collect(config('kelas.daftar', []))->flatten()->values()->all();
    }
}