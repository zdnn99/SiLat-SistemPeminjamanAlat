<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (Auth::user()->peran === 'admin') {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/dashboard');
    }

    // 3. Menampilkan Halaman Register
    public function tampilRegister()
    {
        return view('auth.register');
    }

    // 4. Memproses Register (Siswa & Guru/Karyawan)
    public function prosesRegister(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_induk' => 'required|string|unique:users,nomor_induk',
            'peran' => 'required|in:siswa,guru_karyawan',
            'password' => 'required|string|min:6|confirmed', 
            'pin' => 'required|digits:6',
        ]);

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_induk' => $request->nomor_induk,
            'peran' => $request->peran,
            'kelas_jurusan' => $request->peran === 'siswa' ? $request->kelas_jurusan : null,
            'password' => Hash::make($request->password), 
            'pin' => Hash::make($request->pin),
        ]);

        Auth::login($user);

        return redirect('/dashboard');
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