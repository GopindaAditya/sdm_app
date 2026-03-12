<?php

namespace App\Http\Controllers;

use App\Models\Kompetensi;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Pagination\LengthAwarePaginator;

class KompetensiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. MASTER DATA KOMPETENSI (ADMIN)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'semua');
        $perPage = $request->input('per_page', 10); 

        $query = Kompetensi::when($search, function ($q) use ($search) {
                $q->where('nama_kompetensi', 'like', "%{$search}%");
            })
            ->when($filter !== 'semua', function ($q) use ($filter) {
                $q->where('kategori', $filter);
            })
            ->orderBy('kategori')
            ->orderBy('nama_kompetensi');

        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }

        $kompetensi = $query->paginate($perPage)->withQueryString();
        
        if ($request->ajax()) {
            return view('admin._table_kompetensi', compact('kompetensi', 'perPage'))->render();
        }

        return view('admin.kompetensi', compact('kompetensi', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kompetensi' => 'required|string|max:255',
            'kategori'        => 'required|string|max:100',
        ]);

        Kompetensi::updateOrCreate(
            ['id' => $request->id],
            [
                'nama_kompetensi' => $request->nama_kompetensi,
                'kategori'        => $request->kategori
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => $request->id ? 'Kompetensi diperbarui!' : 'Kompetensi baru ditambahkan!'
        ]);
    }

    public function destroy($id)
    {
        $kompetensi = Kompetensi::findOrFail($id);
        
        if ($kompetensi->jabatan()->exists() || $kompetensi->pengembangan()->exists()) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal! Kompetensi sedang digunakan di Jabatan atau Program Pengembangan.'
            ], 400);
        }

        $kompetensi->delete();
        return response()->json(['success' => true, 'message' => 'Kompetensi berhasil dihapus!']);
    }

    /*
    |--------------------------------------------------------------------------
    | 2.  PEGAWAI (DAFTAR KOMPETENSI JABATAN)
    |--------------------------------------------------------------------------
    */
    private function queryKompetensiPegawai($nip, $search = null, $filter = 'semua')
    {
        $pegawai = Auth::user();
        
        $query = DB::table('jabatan_kompetensi as jk')
            ->select(
                'k.id', 
                'k.nama_kompetensi', 
                'k.kategori',
                DB::raw("IF(kp.id_kompetensi IS NOT NULL, 'Terpenuhi', 'Belum Terpenuhi') AS status_dimiliki")
            )
            ->join('kompetensi as k', 'jk.id_kompetensi', '=', 'k.id')
            ->leftJoin('kompetensi_pegawai as kp', function($join) use ($nip) {
                $join->on('k.id', '=', 'kp.id_kompetensi')
                    ->whereIn('kp.id_riwayat_peng', function($q) use ($nip) {
                        $q->select('id')
                        ->from('riwayat_pengembangan')
                        ->where('nip', $nip)
                        ->where('status', 'approved'); 
                    });
            })
            ->where('jk.id_jabatan', $pegawai->id_jabatan);

        if ($search) {
            $query->where('k.nama_kompetensi', 'like', "%{$search}%");
        }

        $data = $query->distinct()->get();

        return $data->sortBy('status_dimiliki')->values();
    }

    public function kompetensiPegawai(Request $request)
    {
        $nip = Auth::user()->nip;
        
        $semuaData = $this->queryKompetensiPegawai($nip);
        
        $totalKompetensi = $semuaData->count();
        $totalSelesai = $semuaData->where('status_dimiliki', 'Terpenuhi')->count();
        $totalBelum = $semuaData->where('status_dimiliki', 'Belum Terpenuhi')->count();
        
        $search = $request->input('search');
        $filter = $request->input('filter', 'semua');
        $perPage = $request->input('per_page', 10); 
                
        if (!empty($search)) {
            $semuaData = $semuaData->filter(function ($item) use ($search) {
                return stripos($item->nama_kompetensi, $search) !== false;
            });
        }
                
        if ($filter !== 'semua') {
            if (in_array($filter, ['selesai', 'Terpenuhi'])) {
                $semuaData = $semuaData->where('status_dimiliki', 'Terpenuhi');
            } elseif (in_array($filter, ['belum', 'Belum Terpenuhi'])) {
                $semuaData = $semuaData->where('status_dimiliki', 'Belum Terpenuhi');
            } else {                
                $semuaData = $semuaData->filter(function ($item) use ($filter) {
                    return stripos($item->kategori, $filter) !== false;
                });
            }
        }
                
        $semuaData = $semuaData->values();
                
        $totalDataFilter = $semuaData->count();
        $perPageAngka = ($perPage === 'semua') ? ($totalDataFilter > 0 ? $totalDataFilter : 1) : (int)$perPage;
        $currentPage = $request->input('page', 1);

        $kompetensi = new LengthAwarePaginator(
            $semuaData->forPage($currentPage, $perPageAngka),
            $totalDataFilter, 
            $perPageAngka, 
            $currentPage,
            ['path' => url()->current()] 
        );
        $kompetensi->withQueryString();
                
        if ($request->ajax()) {
            return view('pegawai._table_kompetensi', compact('kompetensi', 'perPage'))->render();
        }
     
        return view('pegawai.kompetensi', compact(
            'kompetensi', 'perPage', 'totalKompetensi', 'totalSelesai', 'totalBelum'
        ));
    }

    public function quickAdd(Request $request)
    {
        $request->validate([
            'nama_kompetensi' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
        ]);

        $kompetensi = Kompetensi::create([
            'nama_kompetensi' => $request->nama_kompetensi,
            'kategori' => $request->kategori,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kompetensi baru berhasil ditambahkan!',
            'data' => $kompetensi
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. ANALISIS & REKAPITULASI (ADMIN)
    |--------------------------------------------------------------------------
    */
    public function rekapKompetensi(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); 

        $query = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {                
                $q->where(function($subQ) use ($search) {
                    $subQ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');
        
        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }
        
        $pegawai = $query->paginate($perPage)->withQueryString();

        $kompetensiRaw = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->join('kompetensi as k', 'kp.id_kompetensi', '=', 'k.id')
            ->whereIn('rp.nip', $pegawai->pluck('nip'))
            ->where('rp.status', 'approved') 
            ->when($tahun !== 'semua', function($q) use ($tahun) {
                $q->whereYear('rp.tanggal_kegiatan', $tahun);
            })
            ->select('rp.nip', 'k.nama_kompetensi')
            ->get()
            ->groupBy('nip');

        foreach ($pegawai as $p) {
            $p->kompetensi_dimiliki = isset($kompetensiRaw[$p->nip]) 
                ? $kompetensiRaw[$p->nip]->pluck('nama_kompetensi')->toArray() 
                : [];
        }
        
        if ($request->ajax()) {
            return view('admin._table_rekap', compact('pegawai', 'tahun', 'perPage'))->render();
        }

        $listTahun = range(date('Y'), date('Y') - 5);
        return view('admin.rekap', compact('pegawai', 'tahun', 'listTahun', 'perPage'));
    }
    
    public function rekapGap(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); 

        $query = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {                
                $q->where(function($subQ) use ($search) {
                    $subQ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');

        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }
        
        $pegawai = $query->paginate($perPage)->withQueryString();
        
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique();
        $nips = $pegawai->pluck('nip');

        // 1. Ambil DETAIL Kompetensi STANDAR JABATAN
        $standarKompDetail = DB::table('jabatan_kompetensi as jk')
            ->join('kompetensi as k', 'jk.id_kompetensi', '=', 'k.id')
            ->whereIn('jk.id_jabatan', $jabatanIds)
            ->select('jk.id_jabatan', 'k.id', 'k.nama_kompetensi')
            ->get()
            ->groupBy('id_jabatan');

        // 2. Ambil ID Kompetensi yang SUDAH DIMILIKI (Approved)
        $ownedKompIds = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->whereIn('rp.nip', $nips)
            ->where('rp.status', 'approved')
            ->select('rp.nip', 'kp.id_kompetensi')
            ->get()
            ->groupBy('nip');

        foreach ($pegawai as $p) {
            // Ambil list standar
            $p->list_standar = $standarKompDetail[$p->id_jabatan] ?? collect();
            $p->kompetensi_total = $p->list_standar->count();

            // Cari yang BELUM DIMILIKI (GAP)
            $ownedIds = ($ownedKompIds[$p->nip] ?? collect())->pluck('id_kompetensi')->toArray();
            
            $p->list_gap = $p->list_standar->filter(function($item) use ($ownedIds) {
                return !in_array($item->id, $ownedIds);
            });
            $p->kompetensi_gap = $p->list_gap->count();
        }

        if ($request->ajax()) {
            return view('admin._table_rekap_gap', compact('pegawai', 'perPage'))->render();
        }

        return view('admin.rekap_gap', compact('pegawai', 'perPage'));
    }

    public function analisisDiklat(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); 

        $query = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {                
                $q->where(function($subQ) use ($search) {
                    $subQ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');
        
        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }
        
        $pegawai = $query->paginate($perPage)->withQueryString();
        
        $standarKompetensi = DB::table('jabatan_kompetensi')
            ->join('kompetensi', 'jabatan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('jabatan_kompetensi.id_jabatan', $pegawai->pluck('id_jabatan'))
            ->select('jabatan_kompetensi.id_jabatan', 'kompetensi.id', 'kompetensi.nama_kompetensi')
            ->get()->groupBy('id_jabatan');

        // FIX: Lakukan join ke riwayat_pengembangan untuk mencari berdasarkan NIP
        $kompDimiliki = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->whereIn('rp.nip', $pegawai->pluck('nip'))
            ->where('rp.status', 'approved') // Pastikan hanya data yang sudah valid/disetujui
            ->select('rp.nip', 'kp.id_kompetensi')
            ->get()
            ->groupBy('nip');

        foreach ($pegawai as $p) {            
            $standar = $standarKompetensi->get($p->id_jabatan, collect());
                        
            $dataDimiliki = $kompDimiliki->get($p->nip);
            $dimilikiIds = $dataDimiliki ? $dataDimiliki->pluck('id_kompetensi')->toArray() : [];
            
            // Logika GAP: Standar yang tidak ada di dimilikiIds
            $p->kebutuhan_diklat = $standar->whereNotIn('id', $dimilikiIds)->pluck('nama_kompetensi')->toArray();
            $p->jumlah_kebutuhan = count($p->kebutuhan_diklat);
            $p->jumlah_standar = $standar->count();
        }
        
        return $request->ajax() 
            ? view('admin._table_analisis_diklat', compact('pegawai', 'perPage'))->render() 
            : view('admin.analisis_diklat', compact('pegawai', 'perPage'));
    }

    /*
    |--------------------------------------------------------------------------
    | 4. EXPORT EXCEL
    |--------------------------------------------------------------------------
    */
    public function exportKompetensi(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');
        
        $query = Pegawai::with('jabatan');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
            });
        }
        $pegawai = $query->orderBy('nama')->get();
        $nips = $pegawai->pluck('nip')->toArray();
        
        // FIX: Join ke riwayat_pengembangan
        $kompetensiQuery = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->join('kompetensi as k', 'kp.id_kompetensi', '=', 'k.id')
            ->whereIn('rp.nip', $nips)
            ->where('rp.status', 'approved')
            ->select('rp.nip', 'k.nama_kompetensi')
            ->distinct();

        if ($tahun !== 'semua') {
            $kompetensiQuery->whereYear('rp.tanggal_kegiatan', $tahun);
        }
        $kompetensiRaw = $kompetensiQuery->get()->groupBy('nip');
        
        $namaTahun = $tahun === 'semua' ? 'Semua_Tahun' : $tahun;
        $fileName = "Rekap_Kompetensi_BPS_Bali_{$namaTahun}.xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Cache-Control: max-age=0");
        
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
        
        $query = Pegawai::with('jabatan');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
            });
        }
        $pegawai = $query->orderBy('nama')->get();
        
        $nips = $pegawai->pluck('nip')->toArray();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();
        
        // Ambil detail standar kompetensi dari jabatan
        $standarKompDetail = DB::table('jabatan_kompetensi as jk')
            ->join('kompetensi as k', 'jk.id_kompetensi', '=', 'k.id')
            ->whereIn('jk.id_jabatan', $jabatanIds)
            ->select('jk.id_jabatan', 'k.id', 'k.nama_kompetensi')
            ->get()
            ->groupBy('id_jabatan');

        // Ambil detail kompetensi yang SUDAH dimiliki (Status Approved)
        $ownedKompDetail = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->join('kompetensi as k', 'kp.id_kompetensi', '=', 'k.id')
            ->whereIn('rp.nip', $nips)
            ->where('rp.status', 'approved')
            ->select('rp.nip', 'k.id', 'k.nama_kompetensi')
            ->get()
            ->groupBy('nip');
        
        $fileName = "Rekap_GAP_Kompetensi_BPS_Bali_" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        echo '
        <table border="1">
            <tr>
                <th colspan="8" style="font-size: 16px; font-weight: bold; background-color: #d1e7dd;">REKAP GAP KOMPETENSI PEGAWAI BPS PROVINSI BALI</th>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">No</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">NIP</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">Nama Pegawai</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">Jabatan</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">Kompetensi Total</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">Kompetensi Belum Dimiliki (Angka)</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">Kompetensi Belum Dimiliki (Nama Kompetensi)</th>
                <th style="font-weight: bold; vertical-align: middle; text-align: center;">Selisih</th>
            </tr>';

        $no = 1;
        foreach ($pegawai as $p) {
            $listStandar = $standarKompDetail[$p->id_jabatan] ?? collect();
            $total = $listStandar->count();
            
            $listDimiliki = ($ownedKompDetail[$p->nip] ?? collect())->unique('id');
            $ownedIds = $listDimiliki->pluck('id')->toArray();
            
            // Filter Standar yang ID-nya TIDAK ADA di dalam array OwnedIds
            $listGap = $listStandar->filter(function($item) use ($ownedIds) {
                return !in_array($item->id, $ownedIds);
            });
            
            $gapCount = $listGap->count();
            $gapNames = $listGap->pluck('nama_kompetensi')->toArray();
            
            // Teks untuk nama kompetensi
            $gapString = empty($gapNames) ? '-' : implode(', ', $gapNames);
            
            // Pewarnaan teks (merah jika ada GAP, hijau jika sudah lengkap)
            $color = $gapCount > 0 ? '#dc3545' : '#198754'; // Merah / Hijau
            $selisihTeks = $gapCount > 0 ? $gapCount : '0';

            // mso-number-format:'\@'; digunakan agar NIP (angka panjang) tidak berubah menjadi format scientific di Excel
            echo '
            <tr>
                <td align="center" valign="top">' . $no++ . '</td>
                <td valign="top" style="mso-number-format:\'\@\';">' . $p->nip . '</td>
                <td valign="top">' . $p->nama . '</td>
                <td valign="top">' . ($p->jabatan->nama_jabatan ?? '-') . '</td>
                <td align="center" valign="top"><b>' . $total . '</b></td>
                <td align="center" valign="top" style="color: ' . $color . '; font-weight: bold;">' . $gapCount . '</td>
                <td valign="top" style="color: ' . $color . ';">' . $gapString . '</td>
                <td align="center" valign="top" style="color: ' . $color . '; font-weight: bold;">' . $selisihTeks . '</td>
            </tr>';
        }

        echo '</table>';
        exit;
    }

    public function exportAnalisisDiklat(Request $request)
    {
        $search = $request->input('search');
        
        $query = Pegawai::with('jabatan');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
            });
        }
        $pegawai = $query->orderBy('nama')->get();
        
        $nips = $pegawai->pluck('nip')->toArray();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();
        
        $standarRaw = DB::table('jabatan_kompetensi')
            ->join('kompetensi', 'jabatan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('jabatan_kompetensi.id_jabatan', $jabatanIds)
            ->select('jabatan_kompetensi.id_jabatan', 'kompetensi.id', 'kompetensi.nama_kompetensi')
            ->get()->groupBy('id_jabatan');
        
        // FIX: Join ke riwayat_pengembangan
        $dimilikiRaw = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->whereIn('rp.nip', $nips)
            ->where('rp.status', 'approved')
            ->select('rp.nip', 'kp.id_kompetensi')
            ->get()->groupBy('nip');

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
                <td align="center" valign="top">' . $no++ . '</td>
                <td valign="top">' . $p->nip . '</td>
                <td valign="top">' . $p->nama . '</td>
                <td valign="top">' . ($p->jabatan->nama_jabatan ?? '-') . '</td>
                <td valign="top" style="color: ' . (empty($kebutuhan) ? 'green' : 'red') . ';">' . $kebutuhanString . '</td>
            </tr>';
        }

        echo '</table>';
        exit;
    }
}