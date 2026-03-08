<?php

namespace App\Http\Controllers;

use App\Models\Kompetensi;
use App\Models\Pegawai;
use App\Models\Jabatan;
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

        $kompetensi = Kompetensi::when($search, function ($q) use ($search) {
                $q->where('nama_kompetensi', 'like', "%{$search}%");
            })
            ->when($filter !== 'semua', function ($q) use ($filter) {
                $q->where('kategori', $filter);
            })
            ->orderBy('kategori')
            ->orderBy('nama_kompetensi')
            ->paginate(10);

        if ($request->ajax()) {
            return view('admin._table_kompetensi', compact('kompetensi'))->render();
        }

        return view('admin.kompetensi', compact('kompetensi'));
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
        $today = now()->toDateString();

        // Ambil standar kompetensi sesuai jabatan pegawai
        $query = DB::table('jabatan_kompetensi as jk')
            ->select(
                'k.id', 'k.nama_kompetensi', 'k.kategori',
                DB::raw("IF(kp.id_kompetensi IS NOT NULL, 'Terpenuhi', 'Belum Terpenuhi') AS status_dimiliki")
            )
            ->join('kompetensi as k', 'jk.id_kompetensi', '=', 'k.id')
            ->leftJoin('kompetensi_pegawai as kp', function($join) use ($nip) {
                $join->on('k.id', '=', 'kp.id_kompetensi')->where('kp.nip', '=', $nip);
            })
            ->where('jk.id_jabatan', $pegawai->id_jabatan)
            ->where('jk.mulai_berlaku', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('jk.akhir_berlaku')->orWhere('jk.akhir_berlaku', '>=', $today);
            });

        if ($search) $query->where('k.nama_kompetensi', 'like', "%{$search}%");

        $data = $query->distinct()->get();

        if ($filter == 'terpenuhi') $data = $data->where('status_dimiliki', 'Terpenuhi');
        if ($filter == 'belum') $data = $data->where('status_dimiliki', 'Belum Terpenuhi');

        return $data->sortBy('status_dimiliki')->values();
    }

    public function kompetensiPegawai()
    {
        $nip = Auth::user()->nip;
        $semuaData = $this->queryKompetensiPegawai($nip);

        $kompetensi = new LengthAwarePaginator(
            $semuaData->forPage(request('page', 1), 10),
            $semuaData->count(), 10, request('page', 1),
            ['path' => route('kompetensi.filter')]
        );

        return view('pegawai.kompetensi', [
            'kompetensi' => $kompetensi,
            'totalKompetensi' => $semuaData->count(),
            'totalSelesai' => $semuaData->where('status_dimiliki', 'Terpenuhi')->count(),
            'totalBelum' => $semuaData->where('status_dimiliki', 'Belum Terpenuhi')->count(),
        ]);
    }

    public function filterDataKompetensi(Request $request)
    {
        $semuaData = $this->queryKompetensiPegawai(Auth::user()->nip, $request->search, $request->filter);
        
        $kompetensi = new LengthAwarePaginator(
            $semuaData->forPage($request->page ?? 1, 10),
            $semuaData->count(), 10, $request->page ?? 1,
            ['path' => route('kompetensi.filter')]
        );

        return view('pegawai._table_kompetensi', compact('kompetensi'))->render();
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

        $pegawai = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama')->paginate(10);

        $kompetensiRaw = DB::table('kompetensi_pegawai')
            ->join('kompetensi', 'kompetensi_pegawai.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('kompetensi_pegawai.nip', $pegawai->pluck('nip'))
            ->when($tahun !== 'semua', function($q) use ($tahun) {
                $q->whereYear('kompetensi_pegawai.created_at', $tahun);
            })
            ->select('kompetensi_pegawai.nip', 'kompetensi.nama_kompetensi')
            ->get()->groupBy('nip');

        foreach ($pegawai as $p) {
            $p->kompetensi_dimiliki = isset($kompetensiRaw[$p->nip]) ? $kompetensiRaw[$p->nip]->pluck('nama_kompetensi')->toArray() : [];
        }

        if ($request->ajax()) return view('admin._table_rekap', compact('pegawai', 'tahun'))->render();

        $listTahun = range(date('Y'), date('Y') - 5);
        return view('admin.rekap', compact('pegawai', 'tahun', 'listTahun'));
    }

    public function rekapGap(Request $request)
    {
        $search = $request->input('search');

        $pegawai = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama')->paginate(10);
        
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique();

        $kompWajib = DB::table('jabatan_kompetensi')->whereIn('id_jabatan', $jabatanIds)->whereNull('akhir_berlaku')
            ->select('id_jabatan', DB::raw('count(id_kompetensi) as total'))->groupBy('id_jabatan')->pluck('total', 'id_jabatan');

        $kompDimiliki = DB::table('kompetensi_pegawai')->whereIn('nip', $pegawai->pluck('nip'))
            ->select('nip', DB::raw('count(distinct id_kompetensi) as total'))->groupBy('nip')->pluck('total', 'nip');

        foreach ($pegawai as $p) {
            $p->kompetensi_total = $kompWajib[$p->id_jabatan] ?? 0;
            $p->kompetensi_dimiliki = $kompDimiliki[$p->nip] ?? 0;
            $p->persentase = $p->kompetensi_total > 0 ? min(100, round(($p->kompetensi_dimiliki / $p->kompetensi_total) * 100)) : 0;
        }

        return $request->ajax() ? view('admin._table_rekap_gap', compact('pegawai'))->render() : view('admin.rekap_gap', compact('pegawai'));
    }

    public function analisisDiklat(Request $request)
    {
        $search = $request->input('search');

        $pegawai = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama')->paginate(10);
        
        $standarKompetensi = DB::table('jabatan_kompetensi')
            ->join('kompetensi', 'jabatan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('jabatan_kompetensi.id_jabatan', $pegawai->pluck('id_jabatan'))
            ->whereNull('jabatan_kompetensi.akhir_berlaku')
            ->select('jabatan_kompetensi.id_jabatan', 'kompetensi.id', 'kompetensi.nama_kompetensi')
            ->get()->groupBy('id_jabatan');

        $kompDimiliki = DB::table('kompetensi_pegawai')->whereIn('nip', $pegawai->pluck('nip'))
            ->select('nip', 'id_kompetensi')->get()->groupBy('nip');

        foreach ($pegawai as $p) {            
            $standar = $standarKompetensi->get($p->id_jabatan, collect());
                        
            $dataDimiliki = $kompDimiliki->get($p->nip);
            $dimilikiIds = $dataDimiliki ? $dataDimiliki->pluck('id_kompetensi')->toArray() : [];
            
            $p->kebutuhan_diklat = $standar->whereNotIn('id', $dimilikiIds)->pluck('nama_kompetensi')->toArray();
            $p->jumlah_kebutuhan = count($p->kebutuhan_diklat);
            $p->jumlah_standar = $standar->count();
        }

        return $request->ajax() 
            ? view('admin._table_analisis_diklat', compact('pegawai'))->render() 
            : view('admin.analisis_diklat', compact('pegawai'));
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
        
        $kompetensiQuery = DB::table('kompetensi_pegawai')
            ->join('kompetensi', 'kompetensi_pegawai.id_kompetensi', '=', 'kompetensi.id')
            ->whereIn('kompetensi_pegawai.nip', $nips)
            ->select('kompetensi_pegawai.nip', 'kompetensi.nama_kompetensi')
            ->distinct();

        if ($tahun !== 'semua') {
            $kompetensiQuery->whereYear('kompetensi_pegawai.created_at', $tahun);
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
        
        $kompWajib = DB::table('jabatan_kompetensi')
            ->whereIn('id_jabatan', $jabatanIds)->whereNull('akhir_berlaku')
            ->select('id_jabatan', DB::raw('count(id_kompetensi) as total'))
            ->groupBy('id_jabatan')->pluck('total', 'id_jabatan')->toArray();

        $kompDimiliki = DB::table('kompetensi_pegawai')
            ->whereIn('nip', $nips)
            ->select('nip', DB::raw('count(distinct id_kompetensi) as total'))
            ->groupBy('nip')->pluck('total', 'nip')->toArray();
        
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
            ->whereNull('jabatan_kompetensi.akhir_berlaku')
            ->select('jabatan_kompetensi.id_jabatan', 'kompetensi.id', 'kompetensi.nama_kompetensi')
            ->get()->groupBy('id_jabatan');
        
        $dimilikiRaw = DB::table('kompetensi_pegawai')
            ->whereIn('nip', $nips)
            ->select('nip', 'id_kompetensi')
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