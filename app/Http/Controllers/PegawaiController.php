<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\RiwayatPengembangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Storage};

class PegawaiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. MANAJEMEN PEGAWAI (ADMIN)
    |--------------------------------------------------------------------------
    */
    public function indexAdmin(Request $request)
    {
        $search = $request->input('search');
        $jabatan_id = $request->input('jabatan_id', 'semua');
        $perPage = $request->input('per_page', 10); 

        $query = Pegawai::with('jabatan')
            ->when($search, function($q) use ($search) {                
                $q->where(function($subQ) use ($search) {
                    $subQ->where('nama', 'like', "%{$search}%")
                         ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->when($jabatan_id !== 'semua', function($q) use ($jabatan_id) {
                $q->where('id_jabatan', $jabatan_id);
            })
            ->orderBy('nama');
        
        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }

        $pegawai = $query->paginate($perPage)->withQueryString();
        $jabatanList = Jabatan::orderBy('nama_jabatan')->get();

        if ($request->ajax()) {
            return view('admin._table_pegawai', compact('pegawai', 'perPage'))->render();
        }

        return view('admin.pegawai', compact('pegawai', 'jabatanList', 'perPage'));
    }

    public function storeAdmin(Request $request)
    {
        $isEdit = $request->filled('nip_lama');

        $request->validate([
            'nama'       => 'required|string|max:255',
            'id_jabatan' => 'required|integer',
            'nip'        => 'required|string|max:50|unique:pegawai,nip,' . ($request->nip_lama ?? 'NULL') . ',nip',
            'password'   => $isEdit ? 'nullable|string|min:6' : 'required|string|min:6',
        ]);

        $data = $request->only(['nip', 'nama', 'id_jabatan']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $searchKey = ['nip' => $request->nip_lama ?? $request->nip];

        Pegawai::updateOrCreate($searchKey, $data);

        $message = $request->filled('nip_lama') ? 'Data pegawai diperbarui!' : 'Pegawai baru ditambahkan!';
        return response()->json(['success' => true, 'message' => $message]);
    }

    public function destroyAdmin($nip)
    {
        Pegawai::where('nip', $nip)->delete();
        return response()->json(['success' => true, 'message' => 'Akun pegawai berhasil dihapus!']);
    }

    public function exportPegawaiTerpilih(Request $request)
    {
        $nips = $request->input('nips', []);

        if (empty($nips)) {
            return back()->with('error', 'Tidak ada pegawai yang dipilih.');
        }

        $pegawai = Pegawai::with('jabatan')->whereIn('nip', $nips)->orderBy('nama')->get();
        $jabatanIds = $pegawai->pluck('id_jabatan')->filter()->unique()->toArray();

        $standarKompDetail = DB::table('jabatan_kompetensi as jk')
            ->join('kompetensi as k', 'jk.id_kompetensi', '=', 'k.id')
            ->whereIn('jk.id_jabatan', $jabatanIds)
            ->select('jk.id_jabatan', 'k.id', 'k.nama_kompetensi')
            ->get()
            ->groupBy('id_jabatan');

        $ownedKompDetail = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->join('kompetensi as k', 'kp.id_kompetensi', '=', 'k.id')
            ->whereIn('rp.nip', $nips)
            ->where('rp.status', 'approved')
            ->select('rp.nip', 'k.id', 'k.nama_kompetensi')
            ->get()
            ->groupBy('nip');

        $fileName = "Profil_dan_Kompetensi_Pegawai_" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        echo '
        <table border="1">
            <tr>
                <th colspan="9" style="font-size: 16px; font-weight: bold; background-color: #cfe2ff;">PROFIL DAN REKAP KOMPETENSI PEGAWAI TERPILIH</th>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <th style="font-weight: bold;">No</th>
                <th style="font-weight: bold;">NIP</th>
                <th style="font-weight: bold;">Nama Pegawai</th>
                <th style="font-weight: bold;">Jabatan</th>
                <th style="font-weight: bold;">Target Standar</th>
                <th style="font-weight: bold;">Jml Dimiliki</th>
                <th style="font-weight: bold;">Daftar Kompetensi Dimiliki</th>
                <th style="font-weight: bold;">Jml GAP</th>
                <th style="font-weight: bold;">Kebutuhan Diklat (GAP)</th>
            </tr>';

        $no = 1;
        foreach ($pegawai as $p) {
            $listStandar = $standarKompDetail[$p->id_jabatan] ?? collect();
            $total = $listStandar->count();
            
            $listDimiliki = ($ownedKompDetail[$p->nip] ?? collect())->unique('id');
            $ownedIds = $listDimiliki->pluck('id')->toArray();
            $dimilikiString = $listDimiliki->isEmpty() ? '-' : implode(', ', $listDimiliki->pluck('nama_kompetensi')->toArray());
            
            $listGap = $listStandar->filter(function($item) use ($ownedIds) { return !in_array($item->id, $ownedIds); });
            $gapCount = $listGap->count();
            $gapString = $listGap->isEmpty() ? 'Sudah Terpenuhi' : implode(', ', $listGap->pluck('nama_kompetensi')->toArray());

            echo '
            <tr>
                <td align="center" valign="top">' . $no++ . '</td>
                <td valign="top" style="mso-number-format:\'\@\';">' . $p->nip . '</td>
                <td valign="top">' . $p->nama . '</td>
                <td valign="top">' . ($p->jabatan->nama_jabatan ?? '-') . '</td>
                <td align="center" valign="top">' . $total . '</td>
                <td align="center" valign="top" style="color: #198754; font-weight: bold;">' . $listDimiliki->count() . '</td>
                <td valign="top">' . $dimilikiString . '</td>
                <td align="center" valign="top" style="color: ' . ($gapCount > 0 ? '#dc3545' : '#198754') . '; font-weight: bold;">' . $gapCount . '</td>
                <td valign="top" style="color: ' . ($gapCount > 0 ? '#dc3545' : '#198754') . ';">' . $gapString . '</td>
            </tr>';
        }
        echo '</table>';
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | 2. OPERASI, DETAIL & VERIFIKASI (ADMIN)
    |--------------------------------------------------------------------------
    */

    public function detailAdmin($nip)
    {
        $pegawai = Pegawai::with('jabatan')->where('nip', $nip)->firstOrFail();
        
        $riwayat = RiwayatPengembangan::with('pengembangan')
            ->where('nip', $nip)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'approved' THEN 2 ELSE 3 END")
            ->orderByDesc('tanggal_kegiatan')
            ->get();
                
        $kompetensiTerpenuhi = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->where('rp.nip', $nip)
            ->where('rp.status', 'approved')
            ->pluck('kp.id_kompetensi', 'kp.id_kompetensi') 
            ->toArray();
        
        $standarKompetensi = $pegawai->id_jabatan && $pegawai->jabatan 
            ? $pegawai->jabatan->kompetensi 
            : collect();

        $kompBisaDiklat = DB::table('pengembangan_kompetensi')
            ->pluck('id_kompetensi')
            ->toArray();
        
        return view('admin.pegawai_detail', compact(
            'pegawai', 'riwayat', 'standarKompetensi', 'kompetensiTerpenuhi', 'kompBisaDiklat'
        ));
    }

    public function getDetailReviewSertifikat($id)
    {
        $riwayat = RiwayatPengembangan::findOrFail($id);
        $pegawai = Pegawai::where('nip', $riwayat->nip)->first();

        $ownedIds = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->where('rp.nip', $riwayat->nip)
            ->where('rp.status', 'approved')
            ->where('rp.id', '!=', $id) 
            ->pluck('kp.id_kompetensi')
            ->toArray();

        $defaultAdminIds = DB::table('pengembangan_kompetensi')
            ->where('id_pengembangan', $riwayat->id_pengembangan)
            ->pluck('id_kompetensi')
            ->toArray();

        $kompetensiJabatan = DB::table('kompetensi as k')
            ->join('jabatan_kompetensi as jk', 'k.id', '=', 'jk.id_kompetensi')
            ->where('jk.id_jabatan', $pegawai->id_jabatan)
            ->select('k.id', 'k.nama_kompetensi', 'k.kategori')
            ->get()
            ->map(function($komp) use ($ownedIds, $defaultAdminIds) {
                $komp->is_owned = in_array($komp->id, $ownedIds);
                $komp->is_default = in_array($komp->id, $defaultAdminIds);
                return $komp;
            });

        $selectedIds = DB::table('kompetensi_pegawai')
            ->where('id_riwayat_peng', $id)
            ->pluck('id_kompetensi')
            ->toArray();

        return response()->json([
            'data' => $kompetensiJabatan,
            'selected_ids' => $selectedIds
        ]);
    }
    
    public function updateStatusSertifikatAdmin(Request $request, $id)
    {
        $status = $request->input('status'); 
        $kompetensiAdmin = $request->input('kompetensi_admin', []); 

        DB::beginTransaction();
        try {
            $riwayat = RiwayatPengembangan::findOrFail($id);
            
            $riwayat->update(['status' => $status]);

            DB::table('kompetensi_pegawai')->where('id_riwayat_peng', $id)->delete();

            if ($status === 'approved' && !empty($kompetensiAdmin)) {
                $insertData = [];
                foreach ($kompetensiAdmin as $idKomp) {
                    $insertData[] = [
                        'id_riwayat_peng' => $id,
                        'id_kompetensi' => $idKomp,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                DB::table('kompetensi_pegawai')->insert($insertData);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $status == 'approved' ? 'Sertifikat disetujui dan kompetensi pegawai diperbarui!' : 'Sertifikat telah ditolak.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // public function storeKompetensiManualAdmin(Request $request, $nip)
    // {        
    //     $request->validate([
    //         'id_kompetensi' => 'required|integer',
    //         'tanggal_kegiatan' => 'required|date' 
    //     ]);        

    //     DB::table('kompetensi_pegawai')->updateOrInsert(
    //         ['nip' => $nip, 'id_kompetensi' => $request->id_kompetensi],
    //         [               
    //             'tanggal_kegiatan' => $request->tanggal_kegiatan, 
    //             'updated_at' => now(), 
    //             'created_at' => now()
    //         ]
    //     );

    //     return response()->json(['success' => true, 'message' => 'Kompetensi berhasil ditandai terpenuhi!']);
    // }
    /*
    |--------------------------------------------------------------------------
    | 3. DASHBOARD (ADMIN & PEGAWAI)
    |--------------------------------------------------------------------------
    */

    public function dashboardAdmin()
    {
        $totalPegawai = Pegawai::count();
        $totalJabatan = Jabatan::count();
        $sertifikatPending = RiwayatPengembangan::where('status', 'pending')->count();
        $sertifikatApproved = RiwayatPengembangan::where('status', 'approved')->count();

        $pegawaiPerJabatan = DB::table('pegawai')
            ->join('jabatan', 'pegawai.id_jabatan', '=', 'jabatan.id')
            ->select('jabatan.nama_jabatan', DB::raw('count(pegawai.nip) as total'))
            ->groupBy('jabatan.id', 'jabatan.nama_jabatan')
            ->orderByDesc('total')->limit(5)->get();

        $labelsJabatan = $pegawaiPerJabatan->pluck('nama_jabatan');
        $dataJabatan = $pegawaiPerJabatan->pluck('total');

        $recentPending = RiwayatPengembangan::with(['pegawai', 'pengembangan'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPegawai', 'totalJabatan', 'sertifikatPending', 'sertifikatApproved', 
            'labelsJabatan', 'dataJabatan', 'recentPending'
        ));
    }

    public function dashboard()
    {
        $pegawai = Auth::user()->load('jabatan');
        $nip = $pegawai->nip;
        $today = now()->toDateString();

        $hour = date('H');
        $sapaan = ($hour < 11) ? 'Pagi' : (($hour < 15) ? 'Siang' : (($hour < 18) ? 'Sore' : 'Malam'));
        
        $kompetensiIds = $pegawai->jabatan ? $pegawai->jabatan->kompetensi()
            ->pluck('kompetensi.id')->toArray() : [];
        
        $totalKompetensi = count($kompetensiIds);
        
        $pengembanganDibutuhkan = DB::table('pengembangan_kompetensi')
            ->whereIn('id_kompetensi', $kompetensiIds)
            ->pluck('id_pengembangan')->unique();

        $totalTargetPengembangan = $pengembanganDibutuhkan->count();

        $riwayatSelesai = RiwayatPengembangan::where(['nip' => $nip, 'status' => 'approved'])->count();
        $riwayatPending = RiwayatPengembangan::where(['nip' => $nip, 'status' => 'pending'])->count();
        $riwayatBelum = max(0, $totalTargetPengembangan - ($riwayatSelesai + $riwayatPending));

        $pengembanganWajib = DB::table('pengembangan')
            ->whereIn('id', $pengembanganDibutuhkan)
            ->whereNotIn('id', function($q) use ($nip) {
                $q->select('id_pengembangan')->from('riwayat_pengembangan')
                  ->where('nip', $nip)->whereIn('status', ['approved', 'pending']);
            })->get();

        $riwayatTerbaru = RiwayatPengembangan::with('pengembangan')
            ->where('nip', $nip)->latest('updated_at')->limit(5)->get();

        return view('pegawai.dashboard', compact(
            'pegawai', 'sapaan', 'totalKompetensi', 'totalTargetPengembangan', 
            'riwayatSelesai', 'riwayatPending', 'riwayatBelum', 'pengembanganWajib', 'riwayatTerbaru'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | 4. PROFIL PEGAWAI
    |--------------------------------------------------------------------------
    */

    public function profil()
    {
        return view('pegawai.profil', ['pegawai' => Auth::user()->load('jabatan')]);
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [];

        if ($request->hasFile('foto')) {
            if ($user->foto_profil) Storage::disk('public')->delete($user->foto_profil);
            $data['foto_profil'] = $request->file('foto')->store('profil', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if (!empty($data)) {
            Pegawai::where('nip', $user->nip)->update($data);
            return back()->with('success', 'Profil berhasil diperbarui!');
        }

        return back()->with('info', 'Tidak ada perubahan.');
    }
}