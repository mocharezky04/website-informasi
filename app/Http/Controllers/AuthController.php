<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($credentials['username'] === 'admin' && $credentials['password'] === 'blueteam2026') {
            $request->session()->regenerate();
            $request->session()->put('admin_logged_in', true);
            $request->session()->put('admin_user', [
                'username' => 'admin',
                'role' => 'SOC Lead Investigator',
                'displayName' => 'Admin Website Informasi',
            ]);

            return redirect()->route('admin.index')->with('success', 'Berhasil masuk ke Konsol Admin Website Informasi.');
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', "Kredensial tidak valid! Gunakan username: 'admin' dan password: 'blueteam2026'");
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_logged_in', 'admin_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Admin berhasil logout dari sistem.');
    }
}
