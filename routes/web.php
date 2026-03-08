<?php

use App\Http\Controllers\KompetensiController;
use App\Http\Controllers\PengembanganController;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin,pegawai')->group(function (){
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth:admin,pegawai')->group(function () {
    
    // Rute Dashboard
    Route::get('/dashboard', function () {
        if (Auth::guard('admin')->check()) return view('admin.dashboard'); 
        if (Auth::guard('pegawai')->check()) return app(PegawaiController::class)->dashboard();
    })->name('dashboard');

    // Rute Profil
    Route::get('/profil', function () {
        if (Auth::guard('pegawai')->check()) return app(PegawaiController::class)->profil();        
        abort(403, 'Akses ditolak');
    })->name('profil');

    // Rute Kompetensi
    Route::get('/kompetensi', function () {
        if (Auth::guard('pegawai')->check()) {
            return app(KompetensiController::class)->kompetensi(); 
        }         
        abort(403, 'Akses ditolak');
    })->name('kompetensi');

    Route::get('/kompetensi/filter', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(KompetensiController::class)->filterDataKompetensi($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('kompetensi.filter');

    Route::get('/pengembangan', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(PengembanganController::class)->pengembangan($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan');

    Route::get('/pengembangan/filter', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(PengembanganController::class)->filterDataPengembangan($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.filter');

    Route::post('/pengembangan/upload', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(PengembanganController::class)->uploadSertifikat($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.upload');

    Route::delete('/pengembangan/{id_pengembangan}/hapus', function (Request $request, $id_pengembangan) {
        if (Auth::guard('pegawai')->check()) {
            return app(PengembanganController::class)->hapusSertifikat($request, $id_pengembangan); 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.hapus');

});

