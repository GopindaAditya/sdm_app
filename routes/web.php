<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\KompetensiController;
use App\Http\Controllers\PengembanganController;

/*
|--------------------------------------------------------------------------
| 1. GUEST ROUTES (Login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:admin,pegawai')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:admin,pegawai');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED ROUTES (Shared: Admin & Pegawai)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:admin,pegawai')->group(function () {

    // --- Dashboard & General ---
    Route::get('/dashboard', function () {
        return Auth::guard('admin')->check() 
            ? app(PegawaiController::class)->dashboardAdmin() 
            : app(PegawaiController::class)->dashboard();
    })->name('dashboard');

    Route::get('/profil', function () {
        if (Auth::guard('pegawai')->check()) return app(PegawaiController::class)->profil();        
        abort(403, 'Akses ditolak');
    })->name('profil');

    Route::post('/profil/update', [PegawaiController::class, 'updateProfil'])->name('profil.update');

    // --- Rute Halaman Utama (Dynamic Berdasarkan Role) ---
    Route::get('/kompetensi', function (Request $request) {
        return Auth::guard('admin')->check() 
            ? app(KompetensiController::class)->index($request) 
            : app(KompetensiController::class)->kompetensiPegawai($request);
    })->name('kompetensi');

    Route::get('/pengembangan', function (Request $request) {
        return Auth::guard('admin')->check()
            ? app(PengembanganController::class)->index($request)
            : app(PengembanganController::class)->pengembanganPegawai($request);
    })->name('pengembangan');

    // --- Aksi Khusus Pegawai ---
    Route::post('/pengembangan/upload', [PengembanganController::class, 'uploadSertifikat'])->name('pengembangan.upload');
    Route::get('/pengembangan/upload/{id}/kompetensi', [PengembanganController::class, 'getKompetensiForUpload'])->name('pengembangan.kompetensi.api');
    Route::delete('/pengembangan/{id_pengembangan}/hapus-sertifikat', [PengembanganController::class, 'hapusSertifikat'])->name('pengembangan.hapus.sertifikat');


    /*
    |--------------------------------------------------------------------------
    | 3. STRICT ADMIN ROUTES (Hanya Admin yang Bisa Masuk)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:admin')->group(function () {
        
        // Aksi Kompetensi (Admin)
        Route::post('/kompetensi', [KompetensiController::class, 'store'])->name('kompetensi.tambah');
        Route::delete('/kompetensi/{id}/hapus', [KompetensiController::class, 'destroy'])->name('kompetensi.hapus');        
        Route::post('/kompetensi/quick-add', [KompetensiController::class, 'quickAdd'])->name('kompetensi.quick_add');

        // Aksi Pengembangan (Admin)
        Route::post('/pengembangan', [PengembanganController::class, 'store'])->name('pengembangan.tambah');
        Route::delete('/pengembangan/{id}/hapus', [PengembanganController::class, 'destroy'])->name('pengembangan.hapus');
        Route::get('/pengembangan/{id}/kompetensi', [PengembanganController::class, 'kompetensi'])->name('pengembangan.kompetensi');
        Route::post('/pengembangan/{id}/kompetensi', [PengembanganController::class, 'updateKompetensi'])->name('pengembangan.kompetensi.update');

        // Master Data Jabatan
        Route::group(['prefix' => 'jabatan'], function () {
            Route::get('/', [JabatanController::class, 'jabatan'])->name('jabatan');
            Route::post('/', [JabatanController::class, 'tambahJabatan'])->name('jabatan.tambah');
            Route::delete('/{id}/hapus', [JabatanController::class, 'hapusJabatan'])->name('jabatan.hapus');
            Route::get('/{id}/kompetensi', [JabatanController::class, 'kompetensi'])->name('jabatan.kompetensi');
            Route::post('/{id}/kompetensi', [JabatanController::class, 'updateKompetensi'])->name('jabatan.kompetensi.update');
        });

        // Manajemen Pegawai & Verifikasi
        Route::group(['prefix' => 'data-pegawai'], function () {
            Route::get('/', [PegawaiController::class, 'indexAdmin'])->name('data_pegawai');
            Route::post('/', [PegawaiController::class, 'storeAdmin'])->name('data_pegawai.tambah');
            Route::delete('/{nip}/hapus', [PegawaiController::class, 'destroyAdmin'])->name('data_pegawai.hapus');
            Route::get('/{nip}/detail', [PegawaiController::class, 'detailAdmin'])->name('data_pegawai.detail');
            
            // FIX: Typo URL yang dobel diperbaiki di sini
            Route::post('/export-pilihan', [PegawaiController::class, 'exportPegawaiTerpilih'])->name('data_pegawai.export_pilihan');
            
            // Verifikasi
            Route::get('/riwayat-sertifikat/{id}/detail-review', [PegawaiController::class, 'getDetailReviewSertifikat'])->name('admin.sertifikat.review');
            Route::post('/sertifikat/{id}/status', [PegawaiController::class, 'updateStatusSertifikatAdmin'])->name('data_pegawai.sertifikat.status');
        });

        // Rekapitulasi, Laporan & Ekspor
        Route::get('/rekap-kompetensi', [KompetensiController::class, 'rekapKompetensi'])->name('rekap_kompetensi');
        Route::get('/rekap-kompetensi/export', [KompetensiController::class, 'exportKompetensi'])->name('export_rekap_kompetensi');

        Route::get('/rekap-gap', [KompetensiController::class, 'rekapGap'])->name('rekap_gap');
        Route::get('/rekap-gap/export', [KompetensiController::class, 'exportGap'])->name('export_rekap_gap');

        Route::get('/analisis-diklat', [KompetensiController::class, 'analisisDiklat'])->name('analisis_diklat');
        Route::get('/analisis-diklat/export', [KompetensiController::class, 'exportAnalisisDiklat'])->name('export_analisis_diklat');
    });

});