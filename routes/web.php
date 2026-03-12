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
| 2. AUTHENTICATED ROUTES (Admin & Pegawai)
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

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA: KOMPETENSI
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'kompetensi'], function () {
        Route::get('/', function (Request $request) {
            return Auth::guard('admin')->check() 
                ? app(KompetensiController::class)->index($request) 
                : app(KompetensiController::class)->kompetensiPegawai($request);
        })->name('kompetensi');

        // Admin Only Actions
        Route::post('/', [KompetensiController::class, 'store'])->name('kompetensi.tambah');
        Route::delete('/{id}/hapus', [KompetensiController::class, 'destroy'])->name('kompetensi.hapus');        
        Route::post('/quick-add', [KompetensiController::class, 'quickAdd'])->name('kompetensi.quick_add');
    });

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA: PENGEMBANGAN / DIKLAT
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'pengembangan'], function () {
        Route::get('/', function (Request $request) {
            return Auth::guard('admin')->check()
                ? app(PengembanganController::class)->index($request)
                : app(PengembanganController::class)->pengembanganPegawai($request);
        })->name('pengembangan');

        // Admin Master Data
        Route::post('/', [PengembanganController::class, 'store'])->name('pengembangan.tambah');
        Route::delete('/{id}/hapus', [PengembanganController::class, 'destroy'])->name('pengembangan.hapus');
        Route::get('/{id}/kompetensi', [PengembanganController::class, 'kompetensi'])->name('pengembangan.kompetensi');
        Route::post('/{id}/kompetensi', [PengembanganController::class, 'updateKompetensi'])->name('pengembangan.kompetensi.update');

        // Pegawai Actions        
        Route::post('/upload', [PengembanganController::class, 'uploadSertifikat'])->name('pengembangan.upload');
        Route::get('/upload/{id}/kompetensi', [PengembanganController::class, 'getKompetensiForUpload'])->name('pengembangan.kompetensi.api');
        Route::delete('/{id_pengembangan}/hapus-sertifikat', [PengembanganController::class, 'hapusSertifikat'])->name('pengembangan.hapus.sertifikat');
    });

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA: JABATAN (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'jabatan'], function () {
        Route::get('/', [JabatanController::class, 'jabatan'])->name('jabatan');
        Route::post('/', [JabatanController::class, 'tambahJabatan'])->name('jabatan.tambah');
        Route::delete('/{id}/hapus', [JabatanController::class, 'hapusJabatan'])->name('jabatan.hapus');
        Route::get('/{id}/kompetensi', [JabatanController::class, 'kompetensi'])->name('jabatan.kompetensi');
        Route::post('/{id}/kompetensi', [JabatanController::class, 'updateKompetensi'])->name('jabatan.kompetensi.update');
    });

    /*
    |--------------------------------------------------------------------------
    | MANAJEMEN PEGAWAI & VERIFIKASI (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'data-pegawai'], function () {
        Route::get('/', [PegawaiController::class, 'indexAdmin'])->name('data_pegawai');
        Route::post('/', [PegawaiController::class, 'storeAdmin'])->name('data_pegawai.tambah');
        Route::delete('/{nip}/hapus', [PegawaiController::class, 'destroyAdmin'])->name('data_pegawai.hapus');
        Route::get('/{nip}/detail', [PegawaiController::class, 'detailAdmin'])->name('data_pegawai.detail');
        Route::post('/data-pegawai/export-pilihan', [PegawaiController::class, 'exportPegawaiTerpilih'])->name('data_pegawai.export_pilihan');
        
        // Verifikasi & Manual Override
        Route::get('riwayat-sertifikat/{id}/detail-review', [PegawaiController::class, 'getDetailReviewSertifikat'])->name('admin.sertifikat.review');
        Route::post('/sertifikat/{id}/status', [PegawaiController::class, 'updateStatusSertifikatAdmin'])->name('data_pegawai.sertifikat.status');
        // Route::post('/{nip}/kompetensi-manual', [PegawaiController::class, 'storeKompetensiManualAdmin'])->name('data_pegawai.kompetensi.manual');
    });

    /*
    |--------------------------------------------------------------------------
    | REKAPITULASI, LAPORAN & EKSPOR (Admin Only)
    |--------------------------------------------------------------------------
    */
    // Rekap Kompetensi
    Route::get('/rekap-kompetensi', [KompetensiController::class, 'rekapKompetensi'])->name('rekap_kompetensi');
    Route::get('/rekap-kompetensi/export', [KompetensiController::class, 'exportKompetensi'])->name('export_rekap_kompetensi');

    // Rekap GAP
    Route::get('/rekap-gap', [KompetensiController::class, 'rekapGap'])->name('rekap_gap');
    Route::get('/rekap-gap/export', [KompetensiController::class, 'exportGap'])->name('export_rekap_gap');

    // Analisis Diklat
    Route::get('/analisis-diklat', [KompetensiController::class, 'analisisDiklat'])->name('analisis_diklat');
    Route::get('/analisis-diklat/export', [KompetensiController::class, 'exportAnalisisDiklat'])->name('export_analisis_diklat');

});