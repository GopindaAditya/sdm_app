<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KompetensiController extends Controller
{
    public function kompetensi()
    {
        $nip = Auth::user()->nip;
        $today = now()->toDateString();
                
        $baseQuery = DB::table('kompetensi as k')
            ->select(
                'k.id',
                'k.nama_kompetensi',
                'k.kategori',                
                DB::raw("IF(kp.id IS NOT NULL, 'Sudah Dimiliki', 'Belum Terpenuhi') as status_dimiliki")
            )
            ->join('jabatan_kompetensi as jk', 'k.id', '=', 'jk.id_kompetensi')
            ->join('pegawai as p', 'jk.id_jabatan', '=', 'p.id_jabatan')            
            ->leftJoin('kompetensi_pegawai as kp', function($join) use ($nip) {
                $join->on('k.id', '=', 'kp.id_kompetensi')
                     ->where('kp.nip', '=', $nip);
            })
            ->where('p.nip', '=', $nip)            
            ->where('jk.mulai_berlaku', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('jk.akhir_berlaku')
                      ->orWhere('jk.akhir_berlaku', '>=', $today);
            })
            ->distinct()
            ->orderBy('status_dimiliki') 
            ->orderBy('k.nama_kompetensi');

        $semuaData = $baseQuery->get();
        
        $totalKompetensi = $semuaData->count();
        $totalSelesai = $semuaData->where('status_dimiliki', 'Sudah Dimiliki')->count();
        $totalBelum = $semuaData->where('status_dimiliki', 'Belum Terpenuhi')->count();
        
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $semuaData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $kompetensi = new LengthAwarePaginator(
            $currentItems,
            $totalKompetensi,
            $perPage,
            $currentPage,
            ['path' => route('kompetensi.filter')]
        );

        return view('pegawai.kompetensi', compact('kompetensi', 'totalKompetensi', 'totalSelesai', 'totalBelum'));
    }

    public function filterDataKompetensi(Request $request)
    {
        $nip = Auth::guard('pegawai')->user()->nip;
        $pegawai = DB::table('pegawai')->where('nip', $nip)->first();
        
        $search = $request->input('search');
        $filter = $request->input('filter', 'semua');

        $baseQuery = DB::table('jabatan_kompetensi as jk')
            ->select(
                'k.id',
                'k.nama_kompetensi',
                'k.kategori',
                DB::raw("IF(kp.id_kompetensi IS NOT NULL, 'Terpenuhi', 'Belum Terpenuhi') AS status_dimiliki")
            )
            ->join('kompetensi as k', 'jk.id_kompetensi', '=', 'k.id')
            ->leftJoin('kompetensi_pegawai as kp', function($join) use ($nip) {
                $join->on('k.id', '=', 'kp.id_kompetensi')
                     ->where('kp.nip', '=', $nip);
            })
            ->where('jk.id_jabatan', $pegawai->id_jabatan)
            ->distinct();

        // Terapkan Pencarian (Berdasarkan nama kompetensi)
        if (!empty($search)) {
            $baseQuery->where('k.nama_kompetensi', 'like', '%' . $search . '%');
        }

        $semuaData = $baseQuery->get();

                
        if ($filter == 'terpenuhi') {
            $semuaData = $semuaData->where('status_dimiliki', 'Terpenuhi');
        } elseif ($filter == 'belum') {
            $semuaData = $semuaData->where('status_dimiliki', 'Belum Terpenuhi');
        }

        $semuaData = $semuaData->sortBy('status_dimiliki')->sortBy('nama_kompetensi')->values();

        // Paginate
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $semuaData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $kompetensi = new LengthAwarePaginator(
            $currentItems, $semuaData->count(), $perPage, $currentPage,
            // Arahkan link pagination AJAX ke route filter ini
            ['path' => route('kompetensi.filter')] 
        );

        // HANYA RENDER FILE TABEL KECIL
        return view('pegawai._table_kompetensi', compact('kompetensi'))->render();
    }
}
