<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KompetensiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'semua');

        $query = DB::table('kompetensi');

        // Fitur Pencarian
        if (!empty($search)) {
            $query->where('nama_kompetensi', 'like', '%' . $search . '%');
        }

        // Fitur Filter Kategori
        if ($filter !== 'semua') {
            $query->where('kategori', $filter);
        }

        $kompetensi = $query->orderBy('kategori')->orderBy('nama_kompetensi')->paginate(10);

        // Render khusus tabel jika request dari AJAX
        if ($request->ajax()) {
            return view('admin._table_kompetensi', compact('kompetensi'))->render();
        }

        return view('admin.kompetensi', compact('kompetensi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kompetensi' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
        ]);

        $id = $request->input('id');

        if ($id) {
            DB::table('kompetensi')->where('id', $id)->update([
                'nama_kompetensi' => $request->nama_kompetensi,
                'kategori' => $request->kategori,
                'updated_at' => now()
            ]);
            $message = 'Kompetensi berhasil diperbarui!';
        } else {
            DB::table('kompetensi')->insert([
                'nama_kompetensi' => $request->nama_kompetensi,
                'kategori' => $request->kategori,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $message = 'Kompetensi baru berhasil ditambahkan!';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    // 3. Hapus Data
    public function destroy($id)
    {
        // Proteksi Data: Cek apakah kompetensi sedang dipakai di Jabatan atau Pengembangan
        $dipakaiDiJabatan = DB::table('jabatan_kompetensi')->where('id_kompetensi', $id)->exists();
        $dipakaiDiPengembangan = DB::table('pengembangan_kompetensi')->where('id_kompetensi', $id)->exists();

        if ($dipakaiDiJabatan || $dipakaiDiPengembangan) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal! Kompetensi ini tidak dapat dihapus karena sedang digunakan sebagai Standar Jabatan atau terkait dengan Program Pengembangan.'
            ], 400); // 400 Bad Request
        }

        DB::table('kompetensi')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Kompetensi berhasil dihapus!']);
    }
    public function kompetensiPegawai()
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

    // =========================================================
    // FUNGSI KHUSUS ADMIN: REKAPITULASI LAPORAN
    // =========================================================

    public function rekapKompetensi(Request $request)
    {
        // Default tahun ini, tapi bisa menerima 'semua'
        $tahun = $request->input('tahun', date('Y')); 
        $search = $request->input('search');

        $query = \App\Models\Pegawai::with('jabatan');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                  ->orWhere('nip', 'like', '%'.$search.'%');
            });
        }

        $pegawai = $query->orderBy('nama')->paginate(10);
        $nips = $pegawai->pluck('nip')->toArray();

        // 2. QUERY SUPER SINGKAT ke buku besar
        $kompetensiQuery = DB::table('kompetensi_pegawai')
            ->join('kompetensi', 'kompetensi_pegawai.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('kompetensi_pegawai.nip', $nips)
            ->select('kompetensi_pegawai.nip', 'kompetensi.nama_kompetensi')
            ->distinct();

        // LOGIKA BARU: Filter tahun HANYA JIKA bukan 'semua'
        if ($tahun !== 'semua') {
            $kompetensiQuery->whereYear('kompetensi_pegawai.created_at', $tahun);
        }

        $kompetensiRaw = $kompetensiQuery->get()->groupBy('nip');

        // 3. Mapping data
        foreach ($pegawai as $p) {
            $p->kompetensi_dimiliki = isset($kompetensiRaw[$p->nip]) 
                ? $kompetensiRaw[$p->nip]->pluck('nama_kompetensi')->toArray() 
                : [];
        }

        $tahunSekarang = date('Y');
        $listTahun = range($tahunSekarang, $tahunSekarang - 5);

        if ($request->ajax()) {
            return view('admin._table_rekap', compact('pegawai', 'tahun'))->render();
        }

        return view('admin.rekap', compact('pegawai', 'tahun', 'listTahun'));
    }

    // =========================================================
    // FUNGSI KHUSUS ADMIN: REKAP GAP KOMPETENSI
    // =========================================================

    public function rekapGap(Request $request)
    {
        $search = $request->input('search');

        $query = \App\Models\Pegawai::with('jabatan');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                  ->orWhere('nip', 'like', '%'.$search.'%');
            });
        }

        $pegawai = $query->orderBy('nama')->paginate(10);
        
        // Ambil array NIP dan ID Jabatan untuk keperluan query agregasi
        $nips = $pegawai->pluck('nip')->toArray();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();

        // 1. Hitung Kompetensi Total (Standar Wajib Jabatan)
        $kompWajib = [];
        if (!empty($jabatanIds)) {
            $kompWajib = DB::table('jabatan_kompetensi')
                ->select('id_jabatan', DB::raw('count(id_kompetensi) as total'))
                ->whereIn('id_jabatan', $jabatanIds)
                ->whereNull('akhir_berlaku')
                ->groupBy('id_jabatan')
                ->pluck('total', 'id_jabatan')
                ->toArray();
        }

        // 2. Hitung Kompetensi Sudah Dimiliki (Berdasarkan Ledger kompetensi_pegawai)
        $kompDimiliki = [];
        if (!empty($nips)) {
            $kompDimiliki = DB::table('kompetensi_pegawai')
                ->select('nip', DB::raw('count(distinct id_kompetensi) as total'))
                ->whereIn('nip', $nips)
                ->groupBy('nip')
                ->pluck('total', 'nip')
                ->toArray();
        }

        // 3. Mapping data ke object pegawai + Kalkulasi Persentase
        foreach ($pegawai as $p) {
            $p->kompetensi_total = $p->id_jabatan && isset($kompWajib[$p->id_jabatan]) ? $kompWajib[$p->id_jabatan] : 0;
            $p->kompetensi_dimiliki = isset($kompDimiliki[$p->nip]) ? $kompDimiliki[$p->nip] : 0;
            
            // Hitung persentase untuk Progress Bar UI
            $persentase = 0;
            if ($p->kompetensi_total > 0) {
                $persentase = round(($p->kompetensi_dimiliki / $p->kompetensi_total) * 100);
                if ($persentase > 100) $persentase = 100; // Cap di 100%
            } elseif ($p->kompetensi_total == 0 && $p->kompetensi_dimiliki > 0) {
                $persentase = 100; // Kasus khusus jika belum ada standar tapi sudah punya sertifikat
            }
            
            $p->persentase = $persentase;
        }

        if ($request->ajax()) {
            return view('admin._table_rekap_gap', compact('pegawai'))->render();
        }

        return view('admin.rekap_gap', compact('pegawai'));
    }

    // =========================================================
    // FUNGSI KHUSUS ADMIN: ANALISIS KEBUTUHAN DIKLAT
    // =========================================================

    public function analisisDiklat(Request $request)
    {
        $search = $request->input('search');

        $query = Pegawai::with('jabatan');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                  ->orWhere('nip', 'like', '%'.$search.'%');
            });
        }

        $pegawai = $query->orderBy('nama')->paginate(10);
        
        $nips = $pegawai->pluck('nip')->toArray();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();

        // 1. Ambil Standar Kompetensi per Jabatan (Beserta Nama Kompetensinya)
        $standarKompetensi = [];
        if (!empty($jabatanIds)) {
            $standarKompetensiRaw = DB::table('jabatan_kompetensi')
                ->join('kompetensi', 'jabatan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
                ->whereIn('jabatan_kompetensi.id_jabatan', $jabatanIds)
                ->whereNull('jabatan_kompetensi.akhir_berlaku')
                ->select('jabatan_kompetensi.id_jabatan', 'kompetensi.id as id_kompetensi', 'kompetensi.nama_kompetensi')
                ->get();
                
            foreach ($standarKompetensiRaw as $sk) {
                $standarKompetensi[$sk->id_jabatan][] = $sk;
            }
        }

        // 2. Ambil ID Kompetensi yang SUDAH DIMILIKI tiap pegawai (Dari Ledger)
        $kompDimiliki = [];
        if (!empty($nips)) {
            $kompDimilikiRaw = DB::table('kompetensi_pegawai')
                ->whereIn('nip', $nips)
                ->select('nip', 'id_kompetensi')
                ->distinct()
                ->get();
                
            foreach ($kompDimilikiRaw as $kd) {
                $kompDimiliki[$kd->nip][] = $kd->id_kompetensi;
            }
        }

        // 3. LOGIKA KLIEN: Kebutuhan = Standar - Dimiliki
        foreach ($pegawai as $p) {
            $kebutuhan = [];
            $standar = $p->id_jabatan && isset($standarKompetensi[$p->id_jabatan]) ? $standarKompetensi[$p->id_jabatan] : [];
            $dimiliki = isset($kompDimiliki[$p->nip]) ? $kompDimiliki[$p->nip] : [];

            // Cek satu per satu standar kompetensinya
            foreach ($standar as $st) {
                // Jika ID Standar TIDAK ADA di array $dimiliki, maka itu jadi Kebutuhan Diklat
                if (!in_array($st->id_kompetensi, $dimiliki)) {
                    $kebutuhan[] = $st->nama_kompetensi;
                }
            }
            $p->kebutuhan_diklat = $kebutuhan;
            $p->jumlah_kebutuhan = count($kebutuhan);
            $p->jumlah_standar = count($standar);
        }

        if ($request->ajax()) {
            return view('admin._table_analisis_diklat', compact('pegawai'))->render();
        }

        return view('admin.analisis_diklat', compact('pegawai'));
    }

    // =========================================================
    // FUNGSI KHUSUS ADMIN: EXPORT REKAP KOMPETENSI (EXCEL/CSV)
    // =========================================================

    public function exportKompetensi(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');

        // 1. Ambil Data
        $query = Pegawai::with('jabatan');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
            });
        }
        $pegawai = $query->orderBy('nama')->get();
        $nips = $pegawai->pluck('nip')->toArray();

        // 2. Ambil Kompetensi
        $kompetensiQuery = DB::table('kompetensi_pegawai')
            ->join('kompetensi', 'kompetensi_pegawai.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('kompetensi_pegawai.nip', $nips)
            ->select('kompetensi_pegawai.nip', 'kompetensi.nama_kompetensi')
            ->distinct();

        if ($tahun !== 'semua') {
            $kompetensiQuery->whereYear('kompetensi_pegawai.created_at', $tahun);
        }
        $kompetensiRaw = $kompetensiQuery->get()->groupBy('nip');

        // 3. Header untuk mendownload file sebagai Excel (.xls)
        $namaTahun = $tahun === 'semua' ? 'Semua_Tahun' : $tahun;
        $fileName = "Rekap_Kompetensi_BPS_Bali_{$namaTahun}.xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Cache-Control: max-age=0");

        // 4. Render Tabel HTML (Excel akan otomatis mengonversi ini menjadi kolom)
        $headerTahun = $tahun === 'semua' ? '(Seluruh Portofolio)' : "(Tahun {$tahun})";
        
        echo '
        <table border="1">
            <tr>
                <th colspan="5" style="font-size: 16px; font-weight: bold;">REKAP KOMPETENSI PEGAWAI BPS PROVINSI BALI</th>
            </tr>
            <tr>
                <th colspan="5" style="font-weight: bold;">Filter: ' . ($tahun === 'semua' ? 'Semua Tahun' : 'Tahun ' . $tahun) . '</th>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <th style="font-weight: bold;">No</th>
                <th style="font-weight: bold;">NIP</th>
                <th style="font-weight: bold;">Nama Pegawai</th>
                <th style="font-weight: bold;">Jabatan</th>
                <th style="font-weight: bold;">Kompetensi Dimiliki ' . $headerTahun . '</th>
            </tr>';

        $no = 1;
        foreach ($pegawai as $p) {
            $komps = isset($kompetensiRaw[$p->nip]) ? $kompetensiRaw[$p->nip]->pluck('nama_kompetensi')->toArray() : [];
            $kompString = empty($komps) ? '-' : implode(', ', $komps);

            echo '
            <tr>
                <td align="center">' . $no++ . '</td>
                <td>' . $p->nip . '</td>
                <td>' . $p->nama . '</td>
                <td>' . ($p->jabatan->nama_jabatan ?? '-') . '</td>
                <td>' . $kompString . '</td>
            </tr>';
        }

        echo '</table>';
        exit;
    }

    public function exportGap(Request $request)
    {
        $search = $request->input('search');

        // 1. Ambil Semua Data Pegawai sesuai filter
        $query = Pegawai::with('jabatan');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
            });
        }
        $pegawai = $query->orderBy('nama')->get();
        
        $nips = $pegawai->pluck('nip')->toArray();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();

        // 2. Hitung Agregasi (Total Standar vs Dimiliki)
        $kompWajib = DB::table('jabatan_kompetensi')
            ->whereIn('id_jabatan', $jabatanIds)->whereNull('akhir_berlaku')
            ->select('id_jabatan', DB::raw('count(id_kompetensi) as total'))
            ->groupBy('id_jabatan')->pluck('total', 'id_jabatan')->toArray();

        $kompDimiliki = DB::table('kompetensi_pegawai')
            ->whereIn('nip', $nips)
            ->select('nip', DB::raw('count(distinct id_kompetensi) as total'))
            ->groupBy('nip')->pluck('total', 'nip')->toArray();

        // 3. Header Excel
        $fileName = "Rekap_GAP_Kompetensi_BPS_Bali_" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        echo '
        <table border="1">
            <tr>
                <th colspan="6" style="font-size: 16px; font-weight: bold;">REKAP GAP KOMPETENSI PEGAWAI BPS PROVINSI BALI</th>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <th style="font-weight: bold;">No</th>
                <th style="font-weight: bold;">NIP</th>
                <th style="font-weight: bold;">Nama Pegawai</th>
                <th style="font-weight: bold;">Jabatan</th>
                <th style="font-weight: bold;">Standar Total</th>
                <th style="font-weight: bold;">Sudah Dimiliki</th>
                <th style="font-weight: bold;">Selisih (GAP)</th>
            </tr>';

        $no = 1;
        foreach ($pegawai as $p) {
            $total = $p->id_jabatan && isset($kompWajib[$p->id_jabatan]) ? $kompWajib[$p->id_jabatan] : 0;
            $dimiliki = isset($kompDimiliki[$p->nip]) ? $kompDimiliki[$p->nip] : 0;
            $gap = $total - $dimiliki;
            $statusGap = $gap > 0 ? $gap : 'Terpenuhi';

            echo '
            <tr>
                <td align="center">' . $no++ . '</td>
                <td>' . $p->nip . '</td>
                <td>' . $p->nama . '</td>
                <td>' . ($p->jabatan->nama_jabatan ?? '-') . '</td>
                <td align="center">' . $total . '</td>
                <td align="center">' . $dimiliki . '</td>
                <td align="center" style="color: ' . ($gap > 0 ? 'red' : 'green') . ';">' . $statusGap . '</td>
            </tr>';
        }

        echo '</table>';
        exit;
    }

    public function exportAnalisisDiklat(Request $request)
    {
        $search = $request->input('search');

        // 1. Ambil Data Pegawai
        $query = Pegawai::with('jabatan');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
            });
        }
        $pegawai = $query->orderBy('nama')->get();
        
        $nips = $pegawai->pluck('nip')->toArray();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();

        // 2. Ambil Standar Kompetensi
        $standarRaw = DB::table('jabatan_kompetensi')
            ->join('kompetensi', 'jabatan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('jabatan_kompetensi.id_jabatan', $jabatanIds)
            ->whereNull('jabatan_kompetensi.akhir_berlaku')
            ->select('jabatan_kompetensi.id_jabatan', 'kompetensi.id', 'kompetensi.nama_kompetensi')
            ->get()->groupBy('id_jabatan');

        // 3. Ambil Kompetensi Dimiliki
        $dimilikiRaw = DB::table('kompetensi_pegawai')
            ->whereIn('nip', $nips)
            ->select('nip', 'id_kompetensi')
            ->get()->groupBy('nip');

        // 4. Header Excel
        $fileName = "Rekap_Analisis_Kebutuhan_Diklat_" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        echo '
        <table border="1">
            <tr>
                <th colspan="5" style="font-size: 16px; font-weight: bold;">REKAP ANALISIS KEBUTUHAN DIKLAT BPS PROVINSI BALI</th>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <th style="font-weight: bold;">No</th>
                <th style="font-weight: bold;">NIP</th>
                <th style="font-weight: bold;">Nama Pegawai</th>
                <th style="font-weight: bold;">Jabatan</th>
                <th style="font-weight: bold;">Kebutuhan Diklat (Kompetensi Belum Dimiliki)</th>
            </tr>';

        $no = 1;
        foreach ($pegawai as $p) {
            $kebutuhan = [];
            $standar = isset($standarRaw[$p->id_jabatan]) ? $standarRaw[$p->id_jabatan] : [];
            $dimilikiIds = isset($dimilikiRaw[$p->nip]) ? $dimilikiRaw[$p->nip]->pluck('id_kompetensi')->toArray() : [];

            foreach ($standar as $st) {
                if (!in_array($st->id, $dimilikiIds)) {
                    $kebutuhan[] = $st->nama_kompetensi;
                }
            }

            $kebutuhanString = empty($kebutuhan) ? 'Sudah Memenuhi Standar' : implode(', ', $kebutuhan);

            echo '
            <tr>
                <td align="center">' . $no++ . '</td>
                <td>' . $p->nip . '</td>
                <td>' . $p->nama . '</td>
                <td>' . ($p->jabatan->nama_jabatan ?? '-') . '</td>
                <td style="color: ' . (empty($kebutuhan) ? 'green' : 'red') . ';">' . $kebutuhanString . '</td>
            </tr>';
        }

        echo '</table>';
        exit;
    }
}
