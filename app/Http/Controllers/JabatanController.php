<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Kompetensi;
use Illuminate\Http\Request;
class JabatanController extends Controller
{    
    public function jabatan(Request $request)
    {
        $search = $request->input('search'); 
        $perPage = $request->input('per_page', 10); 

        $query = Jabatan::withCount('kompetensi as total_kompetensi')
            ->when($search, function($q) use ($search) { 
                $q->where('nama_jabatan', 'like', "%{$search}%");
            })
            ->orderBy('nama_jabatan');

        if ($perPage === 'semua') {
            $totalData = $query->count();
            $perPage = $totalData > 0 ? $totalData : 1; 
        }

        $jabatan = $query->paginate($perPage)->withQueryString();
                
        if ($request->ajax()) {
            return view('admin._table_jabatan', compact('jabatan', 'perPage'))->render();
        }

        return view('admin.jabatan', compact('jabatan', 'perPage'));
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
            ->pluck('kompetensi.id') 
            ->toArray();

        return view('admin.jabatan_kompetensi', compact('jabatan', 'kategoriList', 'mappedIds'));
    }
    
    public function updateKompetensi(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $kompetensiIds = $request->input('kompetensi', []);
        
        $jabatan->kompetensi()->sync($kompetensiIds);

        return redirect()->route('jabatan')->with('success', 'Standar kompetensi berhasil diperbarui!');
    }
}