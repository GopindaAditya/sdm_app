<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class KompetensiController extends Controller
{
    public function kompetensi()
    {
        $pegawai = Auth::guard('pegawai')->user()->load('jabatan.kompetensi');        
        
        if ($pegawai->jabatan) {            
            $kompetensi = $pegawai->jabatan->kompetensi()->paginate(10);
                        
            $totalKompetensi = $kompetensi->total(); 
        } else {            
            $kompetensi = new LengthAwarePaginator([], 0, 10);
            $totalKompetensi = 0;
        }
        
        return view('pegawai.kompetensi', compact('kompetensi', 'totalKompetensi'));
    }
}
