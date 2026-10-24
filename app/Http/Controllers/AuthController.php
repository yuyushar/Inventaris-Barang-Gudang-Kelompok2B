<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLogin()
    {
        return view('login');
    }

    // Memproses data login (Cek Email & Password)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Jika email dan password cocok di database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Cek role dan arahkan ke dashboard masing-masing
            $role = Auth::user()->role;
            if ($role === 'admin') {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/petugas/dashboard');
            }
        }

        // Jika gagal, kembalikan ke halaman login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}