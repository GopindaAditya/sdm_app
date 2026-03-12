<?php

namespace App\Http\Controllers;

use App\Models\Kompetensi;
use App\Models\Pengembangan;
use App\Models\RiwayatPengembangan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengembanganController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); 

        $query = Pengembangan::when($search, function ($q) use ($search) {
                $q->where('nama_pengembangan', 'like', "%{$search}%");
            })
            ->withCount('kompetensi')
            ->orderBy('nama_pengembangan');

        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }

        $pengembangan = $query->paginate($perPage)->withQueryString();
        
        if ($request->ajax()) {
            return view('admin._table_pengembangan', compact('pengembangan', 'perPage'))->render();
        }

        return view('admin.pengembangan', compact('pengembangan', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate(['nama_pengembangan' => 'required|string|max:255']);

        Pengembangan::updateOrCreate(
            ['id' => $request->id],
            ['nama_pengembangan' => $request->nama_pengembangan]
        );

        $message = $request->id ? 'Program pengembangan berhasil diperbarui!' : 'Program pengembangan baru berhasil ditambahkan!';
        return response()->json(['success' => true, 'message' => $message]);
    }

    public function destroy($id)
    {
        $pengembangan = Pengembangan::findOrFail($id);
        
        if ($pengembangan->riwayatPengembangan()->exists()) {
            return response()->json(['success' => false, 'message' => 'Gagal! Pengembangan ini sudah ada di riwayat pegawai.'], 400);
        }

        $pengembangan->kompetensi()->detach(); 
        $pengembangan->delete();

        return response()->json(['success' => true, 'message' => 'Berhasil dihapus!']);
    }

    public function kompetensi($id)
    {
        $pengembangan = Pengembangan::findOrFail($id);
        
        $kategoriList = Kompetensi::orderBy('kategori')
            ->orderBy('nama_kompetensi')
            ->get()
            ->groupBy('kategori');

        $mappedIds = $pengembangan->kompetensi()
            ->pluck('kompetensi.id')
            ->toArray();

        return view('admin.pengembangan_kompetensi', compact('pengembangan', 'kategoriList', 'mappedIds'));
    }

    public function updateKompetensi(Request $request, $id)
    {
        $pengembangan = Pengembangan::findOrFail($id);
        $kompetensiIds = $request->input('kompetensi', []);

        $pengembangan->kompetensi()->sync($kompetensiIds);

        return redirect()->route('pengembangan')->with('success', 'Output kompetensi berhasil diperbarui!');
    }

    /*
    |--------------------------------------------------------------------------
    | PEGAWAI 
    |--------------------------------------------------------------------------
    */
    private function queryPengembanganPegawai($nip, $search = null, $filter = 'semua')
    {
        $pegawai = Auth::user();
        
        $idKompetensiWajib = DB::table('jabatan_kompetensi')
            ->where('id_jabatan', $pegawai->id_jabatan)
            ->pluck('id_kompetensi');
        
        $query = Pengembangan::whereHas('kompetensi', function($q) use ($idKompetensiWajib) {
                $q->whereIn('kompetensi.id', $idKompetensiWajib);
            })
            ->with(['riwayatPengembangan' => function($q) use ($nip) {
                $q->where('nip', $nip);
            }])
            ->when($search, function($q) use ($search) {
                $q->where('nama_pengembangan', 'like', "%{$search}%");
            });

        $data = $query->get()->map(function($item) {
            $riwayat = $item->riwayatPengembangan->first();
            $item->status_pengembangan = $riwayat ? $riwayat->status : 'Belum Mengikuti';
            $item->tanggal_kegiatan = $riwayat ? $riwayat->tanggal_kegiatan : null;
            $item->riwayat_id = $riwayat ? $riwayat->id : null;
            $item->sertifikat = $riwayat ? $riwayat->sertifikat : null;
            $item->updated_at = $riwayat ? $riwayat->updated_at : null;
            return $item;
        });
        
        if ($filter == 'selesai') {
            $data = $data->where('status_pengembangan', 'approved');
        } elseif ($filter == 'pending') {
            $data = $data->whereIn('status_pengembangan', ['Menunggu Review', 'pending']);
        } elseif ($filter == 'belum') {
            $data = $data->whereIn('status_pengembangan', ['Belum Mengikuti', 'rejected']);
        }

        return $data->sortByDesc('status_pengembangan')->values();
    }

    public function pengembanganPegawai(Request $request)
    {
        $nip = Auth::user()->nip;
        
        $search = $request->input('search');
        $filter = $request->input('filter', 'semua');
        $perPage = $request->input('per_page', 10);
        
        $semuaData = $this->queryPengembanganPegawai($nip, $search, $filter);
                
        $dataDashboard = $this->queryPengembanganPegawai($nip); 
        $totalPengembangan = $dataDashboard->count();
        $totalSelesai = $dataDashboard->where('status_pengembangan', 'approved')->count();
        $totalBelum = $dataDashboard->whereIn('status_pengembangan', ['Belum Mengikuti', 'rejected'])->count();
        
        $totalDataFilter = $semuaData->count();
        $perPageAngka = ($perPage === 'semua') ? ($totalDataFilter > 0 ? $totalDataFilter : 1) : (int)$perPage;
        $currentPage = $request->input('page', 1);

        $pengembangan = new LengthAwarePaginator(
            $semuaData->forPage($currentPage, $perPageAngka),
            $totalDataFilter,
            $perPageAngka,
            $currentPage,
            ['path' => url()->current()] 
        );
        $pengembangan->withQueryString();

        if ($request->ajax()) {
            return view('pegawai._table_pengembangan', compact('pengembangan', 'perPage'))->render();
        }
        
        return view('pegawai.pengembangan', compact('pengembangan', 'perPage', 'totalPengembangan', 'totalSelesai', 'totalBelum'));
    }

    public function getKompetensiForUpload($id_pengembangan)
    {
        $pegawai = Auth::user();
        $riwayatId = request('riwayat_id'); 

        // 1. Ambil ID Kompetensi yang SUDAH DIMILIKI (Approved dari riwayat LAIN)
        $ownedIds = DB::table('kompetensi_pegawai as kp')
            ->join('riwayat_pengembangan as rp', 'kp.id_riwayat_peng', '=', 'rp.id')
            ->where('rp.nip', $pegawai->nip)
            ->where('rp.status', 'approved')
            // JANGAN anggap 'owned' jika itu milik riwayat yang sedang diedit
            ->when($riwayatId, function($q) use ($riwayatId) {
                return $q->where('kp.id_riwayat_peng', '!=', $riwayatId);
            })
            ->pluck('kp.id_kompetensi')
            ->toArray();

        // 2. Ambil Rekomendasi Admin (Default mapping)
        $defaultAdminIds = DB::table('pengembangan_kompetensi')
            ->where('id_pengembangan', $id_pengembangan)
            ->pluck('id_kompetensi')
            ->toArray();

        // 3. LOGIKA KRUSIAL: Ambil apa yang pernah Anda pilih di riwayat ini
        $pilihanLamaUser = [];
        if ($riwayatId) {
            $pilihanLamaUser = DB::table('kompetensi_pegawai')
                ->where('id_riwayat_peng', $riwayatId)
                ->pluck('id_kompetensi')
                ->toArray();
        }

        // Tentukan mana yang harus tercentang di UI:
        // Jika sedang EDIT (ada pilihan lama), pakai itu. Jika BARU, pakai default admin.
        $finalSelectedIds = !empty($pilihanLamaUser) ? $pilihanLamaUser : $defaultAdminIds;

        // 4. Ambil Semua Kompetensi Jabatan
        $data = DB::table('kompetensi as k')
            ->join('jabatan_kompetensi as jk', 'k.id', '=', 'jk.id_kompetensi')
            ->where('jk.id_jabatan', $pegawai->id_jabatan)
            ->select('k.id', 'k.nama_kompetensi', 'k.kategori')
            ->orderBy('k.nama_kompetensi', 'asc')
            ->get()
            ->map(function($komp) use ($ownedIds, $defaultAdminIds) {
                $komp->is_owned = in_array($komp->id, $ownedIds);
                $komp->is_default = in_array($komp->id, $defaultAdminIds);
                return $komp;
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'selected_ids' => $finalSelectedIds // Ini berisi pilihan lama Anda saat edit
        ]);
    }

    public function uploadSertifikat(Request $request)
    {
        $request->validate([
            'id_pengembangan' => 'required|exists:pengembangan,id',
            'tanggal_kegiatan' => 'required|date',
            'sertifikat' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', 
            'kompetensi' => 'required|array|min:1', 
        ]);

        $nip = Auth::user()->nip;
        return DB::transaction(function () use ($request, $nip) {
            
            $riwayat = RiwayatPengembangan::where('nip', $nip)
                ->where('id_pengembangan', $request->id_pengembangan)
                ->first();

            if (!$riwayat && !$request->hasFile('sertifikat')) {
                return response()->json(['success' => false, 'message' => 'Sertifikat wajib diunggah untuk data baru.'], 422);
            }

            $filename = $riwayat ? $riwayat->sertifikat : null;

            if ($request->hasFile('sertifikat')) {
                if ($filename) {
                    Storage::disk('public')->delete('sertifikat/' . $filename);
                }
                
                $file = $request->file('sertifikat');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('sertifikat', $filename, 'public');
            }

            $riwayatBaru = RiwayatPengembangan::updateOrCreate(
                ['nip' => $nip, 'id_pengembangan' => $request->id_pengembangan],
                [                
                    'tanggal_kegiatan' => $request->tanggal_kegiatan,
                    'sertifikat' => $filename,
                    'status' => 'pending'
                ]
            );

            DB::table('kompetensi_pegawai')->where('id_riwayat_peng', $riwayatBaru->id)->delete();

            $dataKompetensi = [];
            foreach ($request->kompetensi as $idKomp) {
                if ($idKomp == 0) continue;

                $dataKompetensi[] = [
                    'id_riwayat_peng' => $riwayatBaru->id,
                    'id_kompetensi' => $idKomp,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($dataKompetensi)) {
                DB::table('kompetensi_pegawai')->insert($dataKompetensi);
            }

            return response()->json(['success' => true, 'message' => 'Data sertifikat dan kompetensi berhasil dikirim untuk review.']);
        });
    }

    public function hapusSertifikat(Request $request, $id_pengembangan)
    {
        $nip = Auth::user()->nip;
        $riwayat = RiwayatPengembangan::where('nip', $nip)->where('id_pengembangan', $id_pengembangan)->first();

        if ($riwayat) {
            if ($riwayat->status == 'approved') {
                return response()->json(['success' => false, 'message' => 'Sertifikat disetujui tidak dapat dihapus.'], 403);
            }

            if ($riwayat->sertifikat) Storage::disk('public')->delete('sertifikat/' . $riwayat->sertifikat);
            $riwayat->delete();

            return response()->json(['success' => true, 'message' => 'Sertifikat berhasil dihapus.']);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
    }
}