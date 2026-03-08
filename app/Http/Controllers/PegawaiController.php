<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // 1. Sapaan Berdasarkan Waktu
        $hour = date('H');
        if ($hour < 11) { $sapaan = 'Selamat Pagi'; }
        elseif ($hour < 15) { $sapaan = 'Selamat Siang'; }
        elseif ($hour < 18) { $sapaan = 'Selamat Sore'; }
        else { $sapaan = 'Selamat Malam'; }

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

        // 5. Ambil Semua Pengembangan Wajib yang BELUM diikuti
        $pengembanganWajib = DB::table('pengembangan')
            ->whereIn('id', $pengembanganDibutuhkan)
            ->whereNotIn('id', function($q) use ($nip) {
                $q->select('id_pengembangan')->from('riwayat_pengembangan')
                  ->where('nip', $nip)->whereIn('status', ['approved', 'pending']);
            })
            ->get(); // Hapus limit(3) agar muncul semua di grid

        // 6. Tabel Riwayat Pembaruan Terakhir (5 Data)
        $riwayatTerbaru = DB::table('riwayat_pengembangan')
            ->join('pengembangan', 'riwayat_pengembangan.id_pengembangan', '=', 'pengembangan.id')
            ->select('pengembangan.nama_pengembangan', 'riwayat_pengembangan.status', 'riwayat_pengembangan.tanggal_kegiatan', 'riwayat_pengembangan.updated_at')
            ->where('riwayat_pengembangan.nip', $nip)
            ->orderByDesc('riwayat_pengembangan.updated_at')
            ->limit(5)
            ->get();

        // Hapus $periodeList dan variabel chart dari compact()
        return view('pegawai.dashboard', compact(
            'pegawai', 'sapaan', 'totalKompetensi', 'totalTargetPengembangan', 
            'riwayatSelesai', 'riwayatPending', 'riwayatBelum', 
            'pengembanganWajib', 'riwayatTerbaru'
        ));
    }
}
