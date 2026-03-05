<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KompetensiPegawai;
use App\Models\Pengembangan;
use App\Models\Periode;
use App\Models\RiwayatPengembangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PegawaiController extends Controller
{    
    public function profil()
    {
        $pegawai = Auth::guard('pegawai')->user()->load('jabatan');
        
        return view('pegawai.profil', compact('pegawai'));
    }    
    
    public function dashboard()
    {
        $pegawai = Auth::user()->load('jabatan');
        $nip = $pegawai->nip;
        $today = now()->toDateString();

        // 1. Ambil Data Periode untuk Dropdown
        $periodeList = DB::table('periode')->orderByDesc('tahun')->get();

        // 2. Ambil ID Kompetensi sesuai jabatan saat ini
        $kompetensiIds = [];
        if ($pegawai->jabatan) {
            $kompetensiIds = DB::table('jabatan_kompetensi')
                ->where('id_jabatan', $pegawai->id_jabatan)
                ->where('mulai_berlaku', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('akhir_berlaku')->orWhere('akhir_berlaku', '>=', $today);
                })->pluck('id_kompetensi')->toArray();
        }
        $totalKompetensi = count($kompetensiIds);

        // 3. Hitung Target Pengembangan
        $pengembanganDibutuhkan = DB::table('pengembangan_kompetensi')
            ->whereIn('id_kompetensi', $kompetensiIds)
            ->where('mulai_berlaku', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('akhir_berlaku')->orWhere('akhir_berlaku', '>=', $today);
            })
            ->pluck('id_pengembangan')->unique()->toArray();
        $totalTargetPengembangan = count($pengembanganDibutuhkan);

        // 4. Hitung Status Riwayat Pengembangan
        $riwayatSelesai = DB::table('riwayat_pengembangan')->where('nip', $nip)->where('status', 'approved')->count();
        $riwayatPending = DB::table('riwayat_pengembangan')->where('nip', $nip)->where('status', 'pending')->count();
        $riwayatBelum = max(0, $totalTargetPengembangan - ($riwayatSelesai + $riwayatPending));

        // 5. Ambil 3 Pengembangan Wajib yang BELUM diikuti
        $pengembanganWajib = DB::table('pengembangan')
            ->whereIn('id', $pengembanganDibutuhkan)
            ->whereNotIn('id', function($q) use ($nip) {
                $q->select('id_pengembangan')->from('riwayat_pengembangan')
                  ->where('nip', $nip)->whereIn('status', ['approved', 'pending']);
            })
            ->limit(3)
            ->get();

        // 6. Tabel Riwayat Pembaruan Terakhir (5 Data)
        $riwayatTerbaru = DB::table('riwayat_pengembangan')
            ->join('pengembangan', 'riwayat_pengembangan.id_pengembangan', '=', 'pengembangan.id')
            ->select('pengembangan.nama_pengembangan', 'riwayat_pengembangan.status', 'riwayat_pengembangan.tanggal_kegiatan', 'riwayat_pengembangan.updated_at')
            ->where('riwayat_pengembangan.nip', $nip)
            ->orderByDesc('riwayat_pengembangan.updated_at')
            ->limit(5)
            ->get();

        // ==========================================
        // 7. LOGIKA CHART (Data Real 6 Bulan Terakhir)
        // ==========================================
        
        // Ambil target per kategori untuk perhitungan persentase
        $targetChart = DB::table('pengembangan_kompetensi')
            ->join('kompetensi', 'pengembangan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('pengembangan_kompetensi.id_kompetensi', $kompetensiIds)
            ->select('kompetensi.kategori', 'pengembangan_kompetensi.id_pengembangan')
            ->distinct()
            ->get();
            
        $targetTeknis = $targetChart->where('kategori', 'Kompetensi Teknis')->count();
        $targetManajerial = $targetChart->where('kategori', 'Kompetensi Manajerial')->count();

        // Ambil riwayat yang sudah selesai (approved) beserta kategorinya
        $riwayatChart = DB::table('riwayat_pengembangan')
            ->join('pengembangan_kompetensi', 'riwayat_pengembangan.id_pengembangan', '=', 'pengembangan_kompetensi.id_pengembangan')
            ->join('kompetensi', 'pengembangan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
            ->select('kompetensi.kategori', 'riwayat_pengembangan.tanggal_kegiatan')
            ->where('riwayat_pengembangan.nip', $nip)
            ->where('riwayat_pengembangan.status', 'approved')
            ->whereNotNull('riwayat_pengembangan.tanggal_kegiatan')
            ->get();

        $chartBulan = [];
        $chartTeknis = [];
        $chartManajerial = [];

        // Looping 6 bulan ke belakang
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartBulan[] = $date->translatedFormat('M Y'); // ex: Okt 2023
            
            $countTeknis = 0;
            $countManajerial = 0;

            // Hitung kumulatif sertifikat sampai dengan bulan tersebut
            foreach($riwayatChart as $row) {
                if (Carbon::parse($row->tanggal_kegiatan)->startOfDay()->lte($date->endOfMonth())) {
                    if ($row->kategori == 'Kompetensi Teknis') $countTeknis++;
                    if ($row->kategori == 'Kompetensi Manajerial') $countManajerial++;
                }
            }

            // Hitung Persentase (Jika tidak ada target, anggap 100%)
            $chartTeknis[] = $targetTeknis > 0 ? round(($countTeknis / $targetTeknis) * 100) : 100;
            $chartManajerial[] = $targetManajerial > 0 ? round(($countManajerial / $targetManajerial) * 100) : 100;
        }

        // Sapaan Berdasarkan Waktu
        $hour = date('H');
        if ($hour < 11) { $sapaan = 'Selamat Pagi'; }
        elseif ($hour < 15) { $sapaan = 'Selamat Siang'; }
        elseif ($hour < 18) { $sapaan = 'Selamat Sore'; }
        else { $sapaan = 'Selamat Malam'; }

        return view('pegawai.dashboard', compact(
            'pegawai', 'sapaan', 'totalKompetensi', 'totalTargetPengembangan', 
            'riwayatSelesai', 'riwayatPending', 'riwayatBelum', 
            'pengembanganWajib', 'riwayatTerbaru',
            'periodeList', 'chartBulan', 'chartTeknis', 'chartManajerial' // Variabel baru dipassing ke view
        ));
    }    
}
