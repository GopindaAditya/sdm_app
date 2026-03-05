<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPengembangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PengembanganController extends Controller
{
    public function pengembangan(Request $request)
    {
        $pegawai = Auth::user();
        $nip = $pegawai->nip;
        $today = now()->toDateString();

        $baseQuery = DB::table('pengembangan as pg')
            ->select(
                'pg.id',
                'pg.nama_pengembangan',
                DB::raw("COALESCE(rp.status, 'Belum Mengikuti') AS status_pengembangan"),
                'rp.tanggal_kegiatan',
                'rp.sertifikat',
                'rp.updated_at'
            )
            ->join('pengembangan_kompetensi as pk', 'pg.id', '=', 'pk.id_pengembangan')
            ->join('jabatan_kompetensi as jk', 'pk.id_kompetensi', '=', 'jk.id_kompetensi')
            ->join('pegawai as p', 'jk.id_jabatan', '=', 'p.id_jabatan')
            ->leftJoin('riwayat_pengembangan as rp', function($join) use ($nip) {
                $join->on('pg.id', '=', 'rp.id_pengembangan')
                     ->where('rp.nip', '=', $nip);
            })
            ->where('p.nip', '=', $nip)
            ->where('jk.mulai_berlaku', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('jk.akhir_berlaku')->orWhere('jk.akhir_berlaku', '>=', $today);
            })
            ->where('pk.mulai_berlaku', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('pk.akhir_berlaku')->orWhere('pk.akhir_berlaku', '>=', $today);
            })
            ->distinct()
            ->orderByDesc('status_pengembangan')
            ->orderBy('pg.nama_pengembangan');

        // 1. Eksekusi query SATU KALI saja untuk mengambil semua data unik
        $semuaData = $baseQuery->get();

        // 2. Hitung statistik untuk Summary Cards
        $totalPengembangan = $semuaData->count();
        $totalSelesai = $semuaData->where('status_pengembangan', 'approved')->count();
        $totalBelum = $semuaData->whereIn('status_pengembangan', ['Belum Mengikuti', 'rejected'])->count();

        // 3. Paginate secara manual dari Collection (Memperbaiki bug count DISTINCT)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        
        // Potong data sesuai halaman yang sedang aktif
        $currentItems = $semuaData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $pengembangan = new LengthAwarePaginator(
            $currentItems,
            $totalPengembangan, // Sekarang totalnya pasti akurat (4 data)
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()] // Agar link pagination tidak rusak
        );

        return view('pegawai.pengembangan', compact('pengembangan', 'totalPengembangan', 'totalSelesai', 'totalBelum'));
    }
    
    public function uploadSertifikat(Request $request)
    {
        $request->validate([
            'id_pengembangan' => 'required|exists:pengembangan,id',
            'tanggal_kegiatan' => 'required|date',
            'sertifikat' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('sertifikat');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/sertifikat', $filename);
        
        RiwayatPengembangan::updateOrCreate(
            [
                'nip' => Auth::user()->nip,
                'id_pengembangan' => $request->id_pengembangan,
                'id_periode' => 1 
            ],
            [
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'sertifikat' => $filename,
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'Sertifikat berhasil diunggah dan menunggu validasi admin.');
    }
}
