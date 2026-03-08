<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username_or_nip' => 'required',
            'password' => 'required',
        ],[
            'username_or_nip.ruqired' => 'Username atau NIP wajib diisi.',
            'password.required' => "Password wajib diisi."
        ]);

        $identifier = $request->username_or_nip;
        $password = $request->password;

        if (Auth::guard('admin')->attempt(['username' => $identifier, 'password' => $password])) {
            $request->session()->regenerate();            
            return redirect()->intended('/dashboard'); 
        }

        if (Auth::guard('pegawai')->attempt(['nip' => $identifier,'password' => $password]))
        {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username_or_nip' => 'Username/NIP atau Password yang Anda masukkan salah.',
        ])->onlyInput('username_or_nip');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('pegawai')->check()) {
            Auth::guard('pegawai')->logout();
        }
     
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
