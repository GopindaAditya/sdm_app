<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    public function jabatan(Request $request)
    {
        // Query ambil data jabatan + hitung jumlah kompetensi yang diwajibkan
        $jabatan = DB::table('jabatan')
            ->leftJoin('jabatan_kompetensi', function($join) {
                $today = now()->toDateString();
                $join->on('jabatan.id', '=', 'jabatan_kompetensi.id_jabatan')
                     ->where('jabatan_kompetensi.mulai_berlaku', '<=', $today)
                     ->where(function ($q) use ($today) {
                         $q->whereNull('jabatan_kompetensi.akhir_berlaku')
                           ->orWhere('jabatan_kompetensi.akhir_berlaku', '>=', $today);
                     });
            })
            ->select('jabatan.id', 'jabatan.nama_jabatan', DB::raw('COUNT(jabatan_kompetensi.id_kompetensi) as total_kompetensi'))
            ->groupBy('jabatan.id', 'jabatan.nama_jabatan')
            ->orderBy('jabatan.nama_jabatan')
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

        $id = $request->input('id');

        if ($id) {
            // Edit
            DB::table('jabatan')->where('id', $id)->update([
                'nama_jabatan' => $request->nama_jabatan,
                'updated_at' => now()
            ]);
            $message = 'Jabatan berhasil diperbarui!';
        } else {
            // Tambah Baru
            DB::table('jabatan')->insert([
                'nama_jabatan' => $request->nama_jabatan,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $message = 'Jabatan baru berhasil ditambahkan!';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    // 3. Hapus Jabatan (AJAX)
    public function hapusJabatan($id)
    {
        // Pastikan Anda menghapus relasi di tabel jabatan_kompetensi terlebih dahulu (atau gunakan onDelete cascade di database)
        DB::table('jabatan_kompetensi')->where('id_jabatan', $id)->delete();
        DB::table('jabatan')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Jabatan berhasil dihapus!']);
    }

    public function kompetensi($id)
    {
        $jabatan = DB::table('jabatan')->where('id', $id)->first();
        if (!$jabatan) abort(404);

        // Ambil semua kompetensi, kelompokkan berdasarkan kategori
        $kompetensi = DB::table('kompetensi')->orderBy('kategori')->orderBy('nama_kompetensi')->get();
        $kategoriList = $kompetensi->groupBy('kategori');

        // Ambil ID kompetensi yang SUDAH dimiliki jabatan ini
        $mappedIds = DB::table('jabatan_kompetensi')
            ->where('id_jabatan', $id)
            ->whereNull('akhir_berlaku') // Asumsi yang aktif
            ->pluck('id_kompetensi')
            ->toArray();

        return view('admin.jabatan_kompetensi', compact('jabatan', 'kategoriList', 'mappedIds'));
    }

    public function tambahKompetensi(Request $request)
    {
        $request->validate([
            'nama_kompetensi' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
        ]);

        // Gunakan insertGetId agar kita bisa menangkap ID yang baru dibuat
        $id = DB::table('kompetensi')->insertGetId([
            'nama_kompetensi' => $request->nama_kompetensi,
            'kategori' => $request->kategori,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Kompetensi baru berhasil ditambahkan!',
            'data' => [
                'id' => $id,
                'nama_kompetensi' => $request->nama_kompetensi,
                'kategori' => $request->kategori
            ]
        ]);
    }

    // 4. Simpan Pemetaan Kompetensi
    public function updateKompetensi(Request $request, $id)
    {
        $kompetensiIds = $request->input('kompetensi', []); // Array ID yang dicentang
        $today = now()->toDateString();

        // Logika Sederhana: Hapus semua mapping lama, masukkan yang baru
        DB::table('jabatan_kompetensi')->where('id_jabatan', $id)->delete();

        $insertData = [];
        foreach($kompetensiIds as $k_id) {
            $insertData[] = [
                'id_jabatan' => $id,
                'id_kompetensi' => $k_id,
                'mulai_berlaku' => $today,
                'akhir_berlaku' => null
            ];
        }

        if(!empty($insertData)) {
            DB::table('jabatan_kompetensi')->insert($insertData);
        }

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('jabatan')->with('success', 'Standar kompetensi berhasil diperbarui!');
    }
    
}
