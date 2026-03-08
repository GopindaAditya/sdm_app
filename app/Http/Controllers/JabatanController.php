<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Kompetensi;
use Illuminate\Http\Request;
class JabatanController extends Controller
{    
    public function jabatan(Request $request)
    {
        $today = now()->toDateString();
        
        $jabatan = Jabatan::withCount(['kompetensi as total_kompetensi' => function ($query) use ($today) {
            $query->where('jabatan_kompetensi.mulai_berlaku', '<=', $today)
                  ->where(function ($q) use ($today) {
                      $q->whereNull('jabatan_kompetensi.akhir_berlaku')
                        ->orWhere('jabatan_kompetensi.akhir_berlaku', '>=', $today);
                  });
        }])
        ->orderBy('nama_jabatan')
        ->paginate(10);

        if ($request->ajax()) {
            return view('admin._table_jabatan', compact('jabatan'))->render();
        }

        return view('admin.jabatan', compact('jabatan'));
    }
    
    public function tambahJabatan(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255'
        ]);
        
        $jabatan = Jabatan::updateOrCreate(
            ['id' => $request->id], 
            ['nama_jabatan' => $request->nama_jabatan] 
        );

        $message = $request->id ? 'Jabatan berhasil diperbarui!' : 'Jabatan baru berhasil ditambahkan!';
        
        return response()->json(['success' => true, 'message' => $message]);
    }
    
    public function hapusJabatan($id)
    {
        $jabatan = Jabatan::findOrFail($id);
                
        $jabatan->kompetensi()->detach();            
        $jabatan->delete();

        return response()->json(['success' => true, 'message' => 'Jabatan berhasil dihapus!']);
    }
    
    public function kompetensi($id)
    {
        $jabatan = Jabatan::findOrFail($id);
        
        $kategoriList = Kompetensi::orderBy('kategori')
            ->orderBy('nama_kompetensi')
            ->get()
            ->groupBy('kategori');

        $mappedIds = $jabatan->kompetensi()
            ->whereNull('akhir_berlaku')
            ->pluck('kompetensi.id') 
            ->toArray();

        return view('admin.jabatan_kompetensi', compact('jabatan', 'kategoriList', 'mappedIds'));
    }
    
    public function updateKompetensi(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $kompetensiIds = $request->input('kompetensi', []);
        $today = now()->toDateString();
        
        $syncData = [];
        foreach ($kompetensiIds as $k_id) {
            $syncData[$k_id] = [
                'mulai_berlaku' => $today,
                'akhir_berlaku' => null
            ];
        }
        
        $jabatan->kompetensi()->sync($syncData);

        return redirect()->route('jabatan')->with('success', 'Standar kompetensi berhasil diperbarui!');
    }
}