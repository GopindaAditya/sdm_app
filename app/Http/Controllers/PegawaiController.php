<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KompetensiPegawai;
use App\Models\Periode;
use App\Models\SyaratKompetensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{    
    public function profil()
    {
        $pegawai = Auth::guard('pegawai')->user()->load('jabatan');
        
        return view('pegawai.profil', compact('pegawai'));
    }

    public function kompetensi(Request $request)
    {
        $pegawai = Auth::guard('pegawai')->user();
        
        $semuaPeriode = Periode::orderBy('tahun', 'desc')->get();
        $periodeAktif = Periode::where('status', 'Aktif')->first();
        $selectedPeriodeId = $request->input('periode_id', $periodeAktif ? $periodeAktif->id : null);
        
        $search = $request->input('search');
        $selectedKategori = $request->input('kategori');
        $selectedStatus = $request->input('status'); // Ambil filter status baru

        $kategoriList = ['Kompetensi Manajerial', 'Kompetensi Teknis', 'Kultur Sosial'];

        $syaratKompetensi = collect();
        $kompetensiDimilikiIds = [];

        if ($pegawai->id_jabatan && $selectedPeriodeId) {
            
            // 1. Ambil ID kompetensi yang SUDAH dimiliki (pindah ke atas agar bisa dipakai filter)
            $kompetensiDimilikiIds = KompetensiPegawai::where('nip', $pegawai->nip)
                ->where('id_periode', $selectedPeriodeId)
                ->pluck('id_kompetensi')
                ->toArray();

            // 2. Base Query
            $query = SyaratKompetensi::with('kompetensi')
                ->where('id_jabatan', $pegawai->id_jabatan)
                ->where('id_periode', $selectedPeriodeId);

            // 3. Filter Search & Kategori
            if ($search || $selectedKategori) {
                $query->whereHas('kompetensi', function ($q) use ($search, $selectedKategori) {
                    if ($selectedKategori) $q->where('kategori', $selectedKategori);
                    if ($search) {
                        $q->where(function($subQ) use ($search) {
                            $subQ->where('nama_kompetensi', 'like', '%' . $search . '%')
                                 ->orWhere('kategori', 'like', '%' . $search . '%');
                        });
                    }
                });
            }

            // 4. LOGIKA FILTER STATUS KEPEMILIKAN
            if ($selectedStatus === 'dimiliki') {
                // Tampilkan hanya yang ID-nya ada di dalam array $kompetensiDimilikiIds
                $query->whereIn('id_kompetensi', $kompetensiDimilikiIds);
            } elseif ($selectedStatus === 'belum_dimiliki') {
                // Tampilkan hanya yang ID-nya TIDAK ADA di dalam array
                $query->whereNotIn('id_kompetensi', $kompetensiDimilikiIds);
            }

            // 5. Eksekusi
            $syaratKompetensi = $query->get();
        }

        return view('pegawai.kompetensi', compact(
            'semuaPeriode', 'selectedPeriodeId', 'syaratKompetensi', 
            'kompetensiDimilikiIds', 'search', 'selectedKategori', 'kategoriList', 'selectedStatus'
        ));
    }
}
