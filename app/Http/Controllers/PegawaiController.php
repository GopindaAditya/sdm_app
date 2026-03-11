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
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('tanggal_kegiatan')
            ->get();
                
        
        $kompetensiTerpenuhi = DB::table('kompetensi_pegawai')
            ->where('nip', $nip)            
            ->pluck('verifikasi', 'id_kompetensi') 
            ->toArray();
        
        $standarKompetensi = [];
        if ($pegawai->id_jabatan) {
            $standarKompetensi = $pegawai->jabatan->kompetensi()
                ->whereNull('jabatan_kompetensi.akhir_berlaku')
                ->get();
        }

        $kompBisaDiklat = DB::table('pengembangan_kompetensi')->pluck('id_kompetensi')->toArray();
        
        return view('admin.pegawai_detail', compact(
            'pegawai', 'riwayat', 'standarKompetensi', 'kompetensiTerpenuhi', 'kompBisaDiklat'
        ));
    }

    public function updateStatusSertifikatAdmin(Request $request, $id)
    {
        $status = $request->input('status');        

        DB::beginTransaction();
        try {
            $riwayat = RiwayatPengembangan::findOrFail($id);
            $riwayat->update(['status' => $status]);

            $kompetensiBaru = [];

            if ($status === 'approved') {                
                $outputKompetensi = DB::table('pengembangan_kompetensi')
                    ->where('id_pengembangan', $riwayat->id_pengembangan)
                    ->pluck('id_kompetensi');

                foreach ($outputKompetensi as $idKomp) {                    
                    $exists = DB::table('kompetensi_pegawai')
                        ->where(['nip' => $riwayat->nip, 'id_kompetensi' => $idKomp])
                        ->exists();

                    if (!$exists) {
                        DB::table('kompetensi_pegawai')->insert([
                            'nip' => $riwayat->nip,
                            'id_kompetensi' => $idKomp,                            
                            'verifikasi' => 'Sertifikat',
                            'tanggal_kegiatan' => $riwayat->tanggal_kegiatan,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                        $kompetensiBaru[] = $idKomp;
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $status == 'approved' ? 'Disetujui & kompetensi diperbarui!' : 'Sertifikat ditolak.',
                'kompetensi_baru' => $kompetensiBaru
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeKompetensiManualAdmin(Request $request, $nip)
    {        
        $request->validate([
            'id_kompetensi' => 'required|integer',
            'tanggal_kegiatan' => 'required|date' 
        ]);        

        DB::table('kompetensi_pegawai')->updateOrInsert(
            ['nip' => $nip, 'id_kompetensi' => $request->id_kompetensi],
            [
                'verifikasi' => 'Admin',                 
                'tanggal_kegiatan' => $request->tanggal_kegiatan, 
                'updated_at' => now(), 
                'created_at' => now()
            ]
        );

        return response()->json(['success' => true, 'message' => 'Kompetensi berhasil ditandai terpenuhi!']);
    }
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
            ->whereNull('jabatan_kompetensi.akhir_berlaku')->pluck('kompetensi.id')->toArray() : [];
        
        $totalKompetensi = count($kompetensiIds);
        
        $pengembanganDibutuhkan = DB::table('pengembangan_kompetensi')
            ->whereIn('id_kompetensi', $kompetensiIds)
            ->whereNull('akhir_berlaku')->pluck('id_pengembangan')->unique();

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