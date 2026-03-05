<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest:admin,pegawai')->group(function (){
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth:admin,pegawai')->group(function () {
    
    // Rute Dashboard
    Route::get('/dashboard', function () {
        if (Auth::guard('admin')->check()) return view('admin.dashboard'); 
        if (Auth::guard('pegawai')->check()) return view('pegawai.dashboard');
    })->name('dashboard');

    // Rute Profil
    Route::get('/profil', function () {
        if (Auth::guard('pegawai')->check()) return app(PegawaiController::class)->profil();        
        abort(403, 'Akses ditolak');
    })->name('profil');

    // Rute Kompetensi
    Route::get('/kompetensi', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(PegawaiController::class)->kompetensi($request); 
        }
         
        abort(403, 'Akses ditolak');
    })->name('kompetensi');

});

