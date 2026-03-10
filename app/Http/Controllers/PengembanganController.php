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
            ->whereNull('akhir_berlaku')
            ->pluck('kompetensi.id')
            ->toArray();

        return view('admin.pengembangan_kompetensi', compact('pengembangan', 'kategoriList', 'mappedIds'));
    }

    public function updateKompetensi(Request $request, $id)
    {
        $pengembangan = Pengembangan::findOrFail($id);
        $kompetensiIds = $request->input('kompetensi', []);
        $today = now()->toDateString();

        $syncData = [];
        foreach($kompetensiIds as $k_id) {
            $syncData[$k_id] = [
                'mulai_berlaku' => $today,
                'akhir_berlaku' => null
            ];
        }

        $pengembangan->kompetensi()->sync($syncData);

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
        $today = now()->toDateString();
        
        $idKompetensiWajib = DB::table('jabatan_kompetensi')
            ->where('id_jabatan', $pegawai->id_jabatan)
            ->where('mulai_berlaku', '<=', $today)
            ->where(function($q) use ($today) {
                $q->whereNull('akhir_berlaku')->orWhere('akhir_berlaku', '>=', $today);
            })
            ->pluck('id_kompetensi');
        
        $query = Pengembangan::whereHas('kompetensi', function($q) use ($idKompetensiWajib, $today) {
                $q->whereIn('kompetensi.id', $idKompetensiWajib)
                  ->where('pengembangan_kompetensi.mulai_berlaku', '<=', $today)
                  ->where(function($sub) use ($today) {
                      $sub->whereNull('pengembangan_kompetensi.akhir_berlaku')
                          ->orWhere('pengembangan_kompetensi.akhir_berlaku', '>=', $today);
                  });
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

    public function uploadSertifikat(Request $request)
    {
        $request->validate([
            'id_pengembangan' => 'required|exists:pengembangan,id',
            'tanggal_kegiatan' => 'required|date',
            'sertifikat' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', 
        ]);

        $nip = Auth::user()->nip;
        $riwayat = RiwayatPengembangan::where('nip', $nip)
            ->where('id_pengembangan', $request->id_pengembangan)
            ->first();

        if (!$riwayat && !$request->hasFile('sertifikat')) {
            return response()->json(['success' => false, 'message' => 'Sertifikat wajib diunggah untuk data baru.'], 422);
        }

        $filename = $riwayat ? $riwayat->sertifikat : null;

        if ($request->hasFile('sertifikat')) {
            if ($filename) Storage::disk('public')->delete('sertifikat/' . $filename);
            
            $file = $request->file('sertifikat');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('sertifikat', $filename, 'public');
        }

        RiwayatPengembangan::updateOrCreate(
            ['nip' => $nip, 'id_pengembangan' => $request->id_pengembangan],
            [
                'id_periode' => 1, 
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'sertifikat' => $filename,
                'status' => 'pending'
            ]
        );

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan.']);
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