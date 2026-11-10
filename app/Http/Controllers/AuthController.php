<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // 1. Menampilkan Halaman Login
    public function tampilLogin()
    {
        return view('auth.login');
    }

    // 2. Memproses Login
    public function prosesLogin(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('nomor_induk', $request->nomor_induk)->first();

        if (! $user) {
            return back()->withErrors([
                'nomor_induk' => 'Nomor Induk Tidak Ditemukan.',
            ])->onlyInput('nomor_induk');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password Salah.',
            ])->onlyInput('nomor_induk');
        }

        // Kata sandi sudah benar, lalu cek status akun user.
        // Dicek SETELAH kata sandi supaya status akun tidak bocor ke orang yang tidak tahu kata sandinya.
        if ($user->status_akun === 'nonaktif') {
            return back()->withErrors([
                'nomor_induk' => 'Akun Anda telah dinonaktifkan oleh admin karena alasan tertentu. Harap daftar ulang dengan data yang benar dan sesuai, atau hubungi admin Ekskul Multimedia.',
            ])->onlyInput('nomor_induk');
        }

        if ($user->status_akun === 'menunggu') {
            return back()->withErrors([
                'nomor_induk' => 'Akun Anda masih menunggu persetujuan admin. Silakan coba lagi nanti atau hubungi admin Ekskul Multimedia.',
            ])->onlyInput('nomor_induk');
        }

        if (! in_array($user->status_akun, ['aktif', 'minta_hapus'], true)) {
            return back()->withErrors([
                'nomor_induk' => 'Akun Anda telah dinonaktifkan oleh admin karena alasan tertentu. Harap daftar ulang dengan data yang benar dan sesuai, atau hubungi admin Ekskul Multimedia.',
            ])->onlyInput('nomor_induk');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (Auth::user()->peran === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended('/dashboard');
    }

    // 3. Menampilkan Halaman Register
    public function tampilRegister()
    {
        return view('auth.register');
    }

    // 4. Memproses Register (Siswa & Guru/Karyawan)
    // Akun baru berstatus "menunggu" dan belum login otomatis. Admin yang menyetujuinya.
    public function prosesRegister(Request $request)
    {
        // Daftar kelas diambil dari config/kelas.php supaya nilai yang masuk pasti salah satu kelas yang ada
        $kelasValid = collect(config('kelas.daftar'))->flatten()->all();

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_induk' => [
                'required',
                'string',
                'unique:users,nomor_induk',
                function ($attribute, $value, $fail) use ($request) {
                    if (! ctype_digit($value)) {
                        $fail('Nomor Induk hanya boleh berisi angka.');
                    } elseif ($request->input('peran') === 'siswa' && strlen($value) !== 5) {
                        $fail('NIS siswa harus terdiri dari 5 digit angka.');
                    }
                },
            ],
            'peran' => 'required|in:siswa,guru_karyawan',
            'kelas_jurusan' => ['nullable', 'required_if:peran,siswa', Rule::in($kelasValid)],
            'password' => 'required|string|min:6|confirmed',
            'pin' => 'required|digits:6',
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_induk' => $request->nomor_induk,
            'peran' => $request->peran,
            'kelas_jurusan' => $request->peran === 'siswa' ? $request->kelas_jurusan : null,
            'password' => Hash::make($request->password),
            'pin' => Hash::make($request->pin),
            'status_akun' => 'menunggu',
        ]);

        return redirect()->route('login')->with(
            'success',
            'Pendaftaran berhasil. Akun Anda bisa dipakai setelah disetujui admin.'
        );
    }

    // 5. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // 6. Menampilkan Halaman Lupa Password
    public function tampilLupaPassword()
    {
        return view('auth.password');
    }

    // 7. Memproses Reset Password via PIN
    public function prosesLupaPassword(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required',
            'pin' => 'required|digits:6',
            'password_baru' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('nomor_induk', $request->nomor_induk)->first();

        if (! $user || ! Hash::check($request->pin, $user->pin)) {
            return back()->withErrors([
                'pin' => 'Nomor Induk atau PIN tidak sesuai.',
            ])->onlyInput('nomor_induk');
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru kamu.');
    }
}