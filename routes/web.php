<?php

use App\Http\Controllers\JabatanController;
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
        if (Auth::guard('admin')->check()) return app(PegawaiController::class)->dashboardAdmin(); 
        if (Auth::guard('pegawai')->check()) return app(PegawaiController::class)->dashboard();
    })->name('dashboard');

    // Rute Profil
    Route::get('/profil', function () {
        if (Auth::guard('pegawai')->check()) return app(PegawaiController::class)->profil();        
        abort(403, 'Akses ditolak');
    })->name('profil');

    // Rute Kompetensi
    Route::get('/kompetensi', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->index($request); 
        }
        if (Auth::guard('pegawai')->check()) {
            return app(KompetensiController::class)->kompetensiPegawai(); 
        }                 
        abort(403, 'Akses ditolak');
    })->name('kompetensi');

    Route::post('/kompetensi', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->store($request); 
        }        
        abort(403, 'Akses ditolak');
    })->name('kompetensi.tambah');

    Route::delete('/kompetensi/{id}/hapus', function ($id) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->destroy($id); 
        }         
        abort(403, 'Akses ditolak');
    })->name('kompetensi.hapus');

    Route::get('/kompetensi/filter', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(KompetensiController::class)->filterDataKompetensi($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('kompetensi.filter');

    Route::get('/pengembangan', function (Request $request) {
        if (Auth::guard('admin')->check()){
            return app(PengembanganController::class)->index($request);
        }
        if (Auth::guard('pegawai')->check()) {
            return app(PengembanganController::class)->pengembanganPegawai($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan');

    Route::post('/pengembangan', function (Request $request) {
        if (Auth::guard('admin')->check()){
            return app(PengembanganController::class)->store($request);
        }        
        abort(403, 'Akses ditolak');
    })->name('pengembangan.tambah');
    
    Route::delete('/pengembangan/{id}/hapus', function ($id) {
        if (Auth::guard('admin')->check()) {
            return app(PengembanganController::class)->destroy($id) ; 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.hapus');  
    
    Route::get('/pengembangan/{id}/kompetensi', function ($id) {
        if (Auth::guard('admin')->check()) {
            return app(PengembanganController::class)->kompetensi($id) ; 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.kompetensi'); 

    Route::post('/pengembangan/{id}/kompetensi', function (Request $request, $id) {
        if (Auth::guard('admin')->check()) {
            return app(PengembanganController::class)->updateKompetensi($request, $id) ; 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.kompetensi.update'); 

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

    Route::delete('/pengembangan/{id_pengembangan}/hapus-sertifikat', function (Request $request, $id_pengembangan) {
        if (Auth::guard('pegawai')->check()) {
            return app(PengembanganController::class)->hapusSertifikat($request, $id_pengembangan); 
        }         
        abort(403, 'Akses ditolak');
    })->name('pengembangan.hapus.sertifikat');

    Route::get('/jabatan', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(JabatanController::class)->jabatan($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('jabatan');

    Route::post('/jabatan', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(JabatanController::class)->tambahJabatan($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('jabatan.tambah');

    Route::delete('/jabatan/{id}/hapus', function ($id) {
        if (Auth::guard('admin')->check()) {
            return app(JabatanController::class)->hapusJabatan($id); 
        }         
        abort(403, 'Akses ditolak');
    })->name('jabatan.hapus');
    
    Route::get('/jabatan/{id}/kompetensi', function ($id) {
        if (Auth::guard('admin')->check()) {
            return app(JabatanController::class)->kompetensi($id); 
        }         
        abort(403, 'Akses ditolak');
    })->name('jabatan.kompetensi');

    Route::post('/jabatan/{id}/kompetensi', function (Request $request, $id) {
        if (Auth::guard('admin')->check()) {
            return app(JabatanController::class)->updateKompetensi($request, $id); 
        }         
        abort(403, 'Akses ditolak');
    })->name('jabatan.kompetensi.update');

    Route::post('/kompetensi/quick-add', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(JabatanController::class)->tambahKompetensi($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('kompetensi.quick_add');
    
    // ==========================================
    // MENU DATA PEGAWAI (KHUSUS ADMIN)
    // ==========================================
    Route::get('/data-pegawai', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(PegawaiController::class)->indexAdmin($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('data_pegawai');

    Route::post('/data-pegawai', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(PegawaiController::class)->storeAdmin($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('data_pegawai.tambah');

    Route::delete('/data-pegawai/{nip}/hapus', function ($nip) {
        if (Auth::guard('admin')->check()) {
            return app(PegawaiController::class)->destroyAdmin($nip); 
        }         
        abort(403, 'Akses ditolak');
    })->name('data_pegawai.hapus');

    // ==========================================
    // DETAIL PEGAWAI, VERIFIKASI & MANUAL OVERRIDE
    // ==========================================
    Route::get('/data-pegawai/{nip}/detail', function ($nip) {
        if (Auth::guard('admin')->check()) {
            return app(PegawaiController::class)->detailAdmin($nip); 
        }         
        abort(403, 'Akses ditolak');
    })->name('data_pegawai.detail');

    Route::post('/data-pegawai/sertifikat/{id}/status', function (Request $request, $id) {
        if (Auth::guard('admin')->check()) {
            return app(PegawaiController::class)->updateStatusSertifikatAdmin($request, $id); 
        }         
        abort(403, 'Akses ditolak');
    })->name('data_pegawai.sertifikat.status');

    Route::post('/data-pegawai/{nip}/kompetensi-manual', function (Request $request, $nip) {
        if (Auth::guard('admin')->check()) {
            return app(PegawaiController::class)->storeKompetensiManualAdmin($request, $nip); 
        }         
        abort(403, 'Akses ditolak');
    })->name('data_pegawai.kompetensi.manual');

    // ==========================================
    // MENU REKAP KOMPETENSI PEGAWAI (ADMIN)
    // ==========================================
    Route::get('/rekap-kompetensi', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->rekapKompetensi($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('rekap_kompetensi');

    // ==========================================
    // MENU REKAP GAP KOMPETENSI (ADMIN)
    // ==========================================
    Route::get('/rekap-gap', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->rekapGap($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('rekap_gap');

    // ==========================================
    // MENU ANALISIS KEBUTUHAN DIKLAT (ADMIN)
    // ==========================================
    Route::get('/analisis-diklat', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->analisisDiklat($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('analisis_diklat');

    // Rute Update Profil (Khusus Pegawai)
    Route::post('/profil/update', function (Request $request) {
        if (Auth::guard('pegawai')->check()) {
            return app(PegawaiController::class)->updateProfil($request);        
        }
        abort(403, 'Akses ditolak');
    })->name('profil.update');

    // Route Ekspor Excel Rekap Kompetensi
    Route::get('/rekap-kompetensi/export', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->exportKompetensi($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('export_rekap_kompetensi');

    // Route Ekspor Excel Rekap GAP
    Route::get('/rekap-gap/export', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->exportGap($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('export_rekap_gap');

    // Route Ekspor Excel Analisis Diklat
    Route::get('/analisis-diklat/export', function (Request $request) {
        if (Auth::guard('admin')->check()) {
            return app(KompetensiController::class)->exportAnalisisDiklat($request); 
        }         
        abort(403, 'Akses ditolak');
    })->name('export_analisis_diklat');
});

