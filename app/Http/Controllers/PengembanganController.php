<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $semuaData = $baseQuery->get();
        
        $totalPengembangan = $semuaData->count();
        $totalSelesai = $semuaData->where('status_pengembangan', 'approved')->count();
        $totalBelum = $semuaData->whereIn('status_pengembangan', ['Belum Mengikuti', 'rejected'])->count();
        
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
                
        $currentItems = $semuaData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $pengembangan = new LengthAwarePaginator(
            $currentItems,
            $totalPengembangan,
            $perPage,
            $currentPage,
            ['path' => route('pengembangan.filter')]
        );

        return view('pegawai.pengembangan', compact('pengembangan', 'totalPengembangan', 'totalSelesai', 'totalBelum'));
    }

    public function filterDataPengembangan(Request $request)
    {
        $nip = Auth::guard('pegawai')->user()->nip;
        $today = now()->toDateString();
        
        $search = $request->input('search');
        $filter = $request->input('filter', 'semua');

        $baseQuery = DB::table('pengembangan as pg')
            ->select('pg.id', 'pg.nama_pengembangan', DB::raw("COALESCE(rp.status, 'Belum Mengikuti') AS status_pengembangan"), 'rp.tanggal_kegiatan', 'rp.sertifikat', 'rp.updated_at')
            ->join('pengembangan_kompetensi as pk', 'pg.id', '=', 'pk.id_pengembangan')
            ->join('jabatan_kompetensi as jk', 'pk.id_kompetensi', '=', 'jk.id_kompetensi')
            ->join('pegawai as p', 'jk.id_jabatan', '=', 'p.id_jabatan')
            ->leftJoin('riwayat_pengembangan as rp', function($join) use ($nip) {
                $join->on('pg.id', '=', 'rp.id_pengembangan')->where('rp.nip', '=', $nip);
            })
            ->where('p.nip', '=', $nip)
            ->where('jk.mulai_berlaku', '<=', $today)
            ->where(function ($query) use ($today) { $query->whereNull('jk.akhir_berlaku')->orWhere('jk.akhir_berlaku', '>=', $today); })
            ->where('pk.mulai_berlaku', '<=', $today)
            ->where(function ($query) use ($today) { $query->whereNull('pk.akhir_berlaku')->orWhere('pk.akhir_berlaku', '>=', $today); })
            ->distinct();

        // Terapkan Search
        if (!empty($search)) {
            $baseQuery->where('pg.nama_pengembangan', 'like', '%' . $search . '%');
        }

        $semuaData = $baseQuery->get();

        // Terapkan Filter
        if ($filter == 'selesai') {
            $semuaData = $semuaData->where('status_pengembangan', 'approved');
        } elseif ($filter == 'pending') {
            $semuaData = $semuaData->whereIn('status_pengembangan', ['Menunggu Review', 'pending']);
        } elseif ($filter == 'belum') {
            $semuaData = $semuaData->whereIn('status_pengembangan', ['Belum Mengikuti', 'rejected']);
        }

        $semuaData = $semuaData->sortByDesc('status_pengembangan')->sortBy('nama_pengembangan')->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $semuaData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $pengembangan = new LengthAwarePaginator(
            $currentItems, $semuaData->count(), $perPage, $currentPage,
            // Arahkan link pagination AJAX ke route filter ini
            ['path' => route('pengembangan.filter')] 
        );

        // HANYA RENDER FILE TABEL KECIL (Ini yang bikin sangat cepat!)
        return view('pegawai._table_pengembangan', compact('pengembangan'))->render();
    }
    
    public function uploadSertifikat(Request $request)
    {
        $request->validate([
            'id_pengembangan' => 'required|exists:pengembangan,id',
            'tanggal_kegiatan' => 'required|date',
            // File jadi nullable agar bisa sekadar edit tanggal tanpa upload ulang file
            'sertifikat' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', 
        ]);

        $nip = Auth::guard('pegawai')->user()->nip;
        $id_pengembangan = $request->id_pengembangan;

        // Cek data lama
        $riwayatLama = DB::table('riwayat_pengembangan')->where('nip', $nip)->where('id_pengembangan', $id_pengembangan)->first();

        // Validasi Manual: Jika data baru tapi file kosong, tolak!
        if (!$riwayatLama && !$request->hasFile('sertifikat')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Sertifikat wajib diunggah untuk data baru.'], 422);
            }
            return back()->with('error', 'Sertifikat wajib diunggah.');
        }

        // Default: pakai nama file lama
        $filename = $riwayatLama ? $riwayatLama->sertifikat : null;

        // Jika user mengunggah file baru, proses filenya
        if ($request->hasFile('sertifikat')) {
            $file = $request->file('sertifikat');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('sertifikat', $filename, 'public');
            
            // Hapus file lama jika ada
            if ($riwayatLama && $riwayatLama->sertifikat) {
                Storage::disk('public')->delete('sertifikat/' . $riwayatLama->sertifikat);
            }
        }
        
        DB::table('riwayat_pengembangan')->updateOrInsert(
            ['nip' => $nip, 'id_pengembangan' => $id_pengembangan, 'id_periode' => 1],
            ['tanggal_kegiatan' => $request->tanggal_kegiatan, 'sertifikat' => $filename, 'status' => 'pending', 'updated_at' => now()]
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan.']);
        }
        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function hapusSertifikat(Request $request, $id_pengembangan)
    {
        $nip = Auth::guard('pegawai')->user()->nip; 
        
        $riwayat = DB::table('riwayat_pengembangan')->where('nip', $nip)->where('id_pengembangan', $id_pengembangan)->first();

        if ($riwayat) {
            if ($riwayat->status == 'approved') {
                return request()->ajax() 
                    ? response()->json(['success' => false, 'message' => 'Sertifikat disetujui tidak dapat dihapus.'], 403)
                    : back()->with('error', 'Sertifikat disetujui tidak dapat dihapus.');
            }

            if ($riwayat->sertifikat) {
                Storage::disk('public')->delete('sertifikat/' . $riwayat->sertifikat);
            }

            DB::table('riwayat_pengembangan')->where('nip', $nip)->where('id_pengembangan', $id_pengembangan)->delete();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Sertifikat berhasil dihapus.']);
            }
            return back()->with('success', 'Data sertifikat berhasil dihapus.');
        }

        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }
        return back()->with('error', 'Data tidak ditemukan.');
    }    
}
