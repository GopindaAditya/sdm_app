<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{    
    public function indexAdmin(Request $request)
    {
        $search = $request->input('search');
        $jabatan_id = $request->input('jabatan_id', 'semua');

        $query = DB::table('pegawai')
            ->leftJoin('jabatan', 'pegawai.id_jabatan', '=', 'jabatan.id')
            ->select('pegawai.*', 'jabatan.nama_jabatan');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('pegawai.nama', 'like', '%'.$search.'%')
                  ->orWhere('pegawai.nip', 'like', '%'.$search.'%');
            });
        }

        if ($jabatan_id !== 'semua') {
            $query->where('pegawai.id_jabatan', $jabatan_id);
        }

        $pegawai = $query->orderBy('pegawai.nama')->paginate(10);
        $jabatanList = DB::table('jabatan')->orderBy('nama_jabatan')->get();

        if ($request->ajax()) {
            return view('admin._table_pegawai', compact('pegawai'))->render();
        }

        return view('admin.pegawai', compact('pegawai', 'jabatanList'));
    }

    // 2. Simpan Data Pegawai (Tambah/Edit)
    public function storeAdmin(Request $request)
    {
        // Jika form mengirimkan NIP lama, berarti ini mode Edit
        $isEdit = $request->has('nip_lama') && !empty($request->nip_lama);

        $rules = [
            'nama' => 'required|string|max:255',
            'id_jabatan' => 'required|integer',            
        ];

        // Validasi NIP unik. Jika edit, kecualikan NIP miliknya sendiri
        if ($isEdit) {
            $rules['nip'] = 'required|string|max:50|unique:pegawai,nip,' . $request->nip_lama . ',nip';
            // Password opsional saat edit
            $rules['password'] = 'nullable|string|min:6'; 
        } else {
            $rules['nip'] = 'required|string|max:50|unique:pegawai,nip';
            $rules['password'] = 'required|string|min:6';
        }

        $request->validate($rules);

        $data = [
            'nip' => $request->nip,
            'nama' => $request->nama,            
            'id_jabatan' => $request->id_jabatan,
            'updated_at' => now()
        ];

        // Hash password hanya jika diisi (saat tambah baru ATAU saat edit jika user mengetik password baru)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($isEdit) {
            DB::table('pegawai')->where('nip', $request->nip_lama)->update($data);
            $message = 'Data pegawai berhasil diperbarui!';
        } else {
            $data['created_at'] = now();
            DB::table('pegawai')->insert($data);
            $message = 'Akun pegawai baru berhasil ditambahkan!';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    // 3. Hapus Akun Pegawai
    public function destroyAdmin($nip)
    {
        DB::table('pegawai')->where('nip', $nip)->delete();
        // Catatan: Jika ada relasi di tabel riwayat_pengembangan, pastikan di-cascade atau dihapus di sini juga
        return response()->json(['success' => true, 'message' => 'Akun pegawai berhasil dihapus!']);
    }


    // =========================================================
    // FUNGSI KHUSUS ADMIN: DETAIL, VERIFIKASI & GAP
    // =========================================================

    // 1. Tampilkan Halaman Detail Pegawai & Analisis GAP
    public function detailAdmin($nip)
    {
        $pegawai = DB::table('pegawai')
            ->leftJoin('jabatan', 'pegawai.id_jabatan', '=', 'jabatan.id')
            ->select('pegawai.*', 'jabatan.nama_jabatan')
            ->where('pegawai.nip', $nip)
            ->first();

        if (!$pegawai) abort(404);

        // A. Ambil Riwayat Sertifikat (Untuk UI Verifikasi)
        $riwayat = DB::table('riwayat_pengembangan')
            ->join('pengembangan', 'riwayat_pengembangan.id_pengembangan', '=', 'pengembangan.id')
            ->select('riwayat_pengembangan.*', 'pengembangan.nama_pengembangan')
            ->where('riwayat_pengembangan.nip', $nip)
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('tanggal_kegiatan')
            ->get();

        // B. Ambil Periode Aktif (ASUMSI: Anda punya tabel periode dengan status aktif)
        // Sesuaikan query ini dengan struktur tabel periode Anda!
        $periodeAktif = DB::table('periode')->orderBy('id', 'desc')->first(); 
        $idPeriodeAktif = $periodeAktif ? $periodeAktif->id : 1; 

        // C. Ambil SEMUA Kompetensi Terpenuhi dari tabel kompetensi_pegawai milik Anda
        // Di sinilah kehebatan tabel ledger Anda bekerja!
        $kompetensiTerpenuhiIds = DB::table('kompetensi_pegawai')
            ->where('nip', $nip)
            ->where('id_periode', $idPeriodeAktif)
            ->pluck('id_kompetensi')
            ->toArray();

        // D. Cari tahu mana kompetensi yang BISA dipenuhi lewat diklat (Untuk UI Labeling)
        $kompBisaDiklat = DB::table('pengembangan_kompetensi')->pluck('id_kompetensi')->toArray();

        // E. Ambil Standar Kompetensi Jabatan
        $standarKompetensi = [];
        if ($pegawai->id_jabatan) {
            $standarKompetensi = DB::table('jabatan_kompetensi')
                ->join('kompetensi', 'jabatan_kompetensi.id_kompetensi', '=', 'kompetensi.id')
                ->where('jabatan_kompetensi.id_jabatan', $pegawai->id_jabatan)
                ->whereNull('jabatan_kompetensi.akhir_berlaku')
                ->select('kompetensi.*')
                ->orderBy('kompetensi.kategori')
                ->get();
        }

        return view('admin.pegawai_detail', compact(
            'pegawai', 'riwayat', 'standarKompetensi', 'kompetensiTerpenuhiIds', 'kompBisaDiklat'
        ));
    }

    // 2. Verifikasi Sertifikat & Otomatis Insert ke kompetensi_pegawai
    // 2. Verifikasi Sertifikat (REVISI: Melempar ID Kompetensi ke JS untuk DOM Magic)
    public function updateStatusSertifikatAdmin(Request $request, $id)
    {
        $status = $request->input('status');         

        DB::beginTransaction();
        try {
            DB::table('riwayat_pengembangan')->where('id', $id)->update([
                'status' => $status,                
                'updated_at' => now()
            ]);

            $kompetensiBaru = []; // Menampung ID yang baru lulus

            if ($status === 'approved') {
                $riwayat = DB::table('riwayat_pengembangan')->where('id', $id)->first();
                $outputKompetensi = DB::table('pengembangan_kompetensi')
                    ->where('id_pengembangan', $riwayat->id_pengembangan)
                    ->pluck('id_kompetensi');

                $periodeAktif = DB::table('periode')->orderBy('id', 'desc')->first();
                $idPeriodeAktif = $periodeAktif ? $periodeAktif->id : 1;

                foreach ($outputKompetensi as $idKomp) {
                    $exists = DB::table('kompetensi_pegawai')
                        ->where('nip', $riwayat->nip)
                        ->where('id_kompetensi', $idKomp)
                        ->where('id_periode', $idPeriodeAktif)
                        ->exists();

                    if (!$exists) {
                        DB::table('kompetensi_pegawai')->insert([
                            'nip' => $riwayat->nip,
                            'id_kompetensi' => $idKomp,
                            'id_periode' => $idPeriodeAktif,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $kompetensiBaru[] = $idKomp; // Catat ID yang baru masuk
                    }
                }
            }

            DB::commit();
            $msg = $status == 'approved' ? 'Sertifikat disetujui & kompetensi diperbarui!' : 'Sertifikat ditolak.';
            
            // Lempar ke JS beserta array kompetensinya
            return response()->json([
                'success' => true, 
                'message' => $msg,
                'kompetensi_baru' => $kompetensiBaru
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()], 500);
        }
    }

    // 3. Simpan Pemenuhan Manual ke kompetensi_pegawai
    public function storeKompetensiManualAdmin(Request $request, $nip)
    {
        $request->validate([
            'id_kompetensi' => 'required|integer',
            // 'keterangan' => 'required|string|max:255' // Validasi ini dipakai jika Anda jadi menambahkan kolom keterangan
        ]);

        $periodeAktif = DB::table('periode')->orderBy('id', 'desc')->first();
        $idPeriodeAktif = $periodeAktif ? $periodeAktif->id : 1;

        // Cek apakah sudah ada
        $exists = DB::table('kompetensi_pegawai')
            ->where('nip', $nip)
            ->where('id_kompetensi', $request->id_kompetensi)
            ->where('id_periode', $idPeriodeAktif)
            ->exists();

        if (!$exists) {
            DB::table('kompetensi_pegawai')->insert([
                'nip' => $nip,
                'id_kompetensi' => $request->id_kompetensi,
                'id_periode' => $idPeriodeAktif,
                // 'sumber' => 'manual', // Jika ada kolom sumber
                // 'keterangan' => $request->keterangan, // Jika ada kolom keterangan
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Kompetensi berhasil ditandai terpenuhi secara manual!']);
    }
    
    public function profil()
    {
        $pegawai = Auth::guard('pegawai')->user()->load('jabatan');
        
        return view('pegawai.profil', compact('pegawai'));
    }    
    
    public function dashboard()
    {
        $pegawai = Auth::user()->load('jabatan');
        $nip = $pegawai->nip;
        $today = now()->toDateString();

        // 1. Sapaan Berdasarkan Waktu
        $hour = date('H');
        if ($hour < 11) { $sapaan = 'Selamat Pagi'; }
        elseif ($hour < 15) { $sapaan = 'Selamat Siang'; }
        elseif ($hour < 18) { $sapaan = 'Selamat Sore'; }
        else { $sapaan = 'Selamat Malam'; }

        // 2. Ambil ID Kompetensi sesuai jabatan saat ini
        $kompetensiIds = [];
        if ($pegawai->jabatan) {
            $kompetensiIds = DB::table('jabatan_kompetensi')
                ->where('id_jabatan', $pegawai->id_jabatan)
                ->where('mulai_berlaku', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('akhir_berlaku')->orWhere('akhir_berlaku', '>=', $today);
                })->pluck('id_kompetensi')->toArray();
        }
        $totalKompetensi = count($kompetensiIds);

        // 3. Hitung Target Pengembangan
        $pengembanganDibutuhkan = DB::table('pengembangan_kompetensi')
            ->whereIn('id_kompetensi', $kompetensiIds)
            ->where('mulai_berlaku', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('akhir_berlaku')->orWhere('akhir_berlaku', '>=', $today);
            })
            ->pluck('id_pengembangan')->unique()->toArray();
        $totalTargetPengembangan = count($pengembanganDibutuhkan);

        // 4. Hitung Status Riwayat Pengembangan
        $riwayatSelesai = DB::table('riwayat_pengembangan')->where('nip', $nip)->where('status', 'approved')->count();
        $riwayatPending = DB::table('riwayat_pengembangan')->where('nip', $nip)->where('status', 'pending')->count();
        $riwayatBelum = max(0, $totalTargetPengembangan - ($riwayatSelesai + $riwayatPending));

        // 5. Ambil Semua Pengembangan Wajib yang BELUM diikuti
        $pengembanganWajib = DB::table('pengembangan')
            ->whereIn('id', $pengembanganDibutuhkan)
            ->whereNotIn('id', function($q) use ($nip) {
                $q->select('id_pengembangan')->from('riwayat_pengembangan')
                  ->where('nip', $nip)->whereIn('status', ['approved', 'pending']);
            })
            ->get(); // Hapus limit(3) agar muncul semua di grid

        // 6. Tabel Riwayat Pembaruan Terakhir (5 Data)
        $riwayatTerbaru = DB::table('riwayat_pengembangan')
            ->join('pengembangan', 'riwayat_pengembangan.id_pengembangan', '=', 'pengembangan.id')
            ->select('pengembangan.nama_pengembangan', 'riwayat_pengembangan.status', 'riwayat_pengembangan.tanggal_kegiatan', 'riwayat_pengembangan.updated_at')
            ->where('riwayat_pengembangan.nip', $nip)
            ->orderByDesc('riwayat_pengembangan.updated_at')
            ->limit(5)
            ->get();

        // Hapus $periodeList dan variabel chart dari compact()
        return view('pegawai.dashboard', compact(
            'pegawai', 'sapaan', 'totalKompetensi', 'totalTargetPengembangan', 
            'riwayatSelesai', 'riwayatPending', 'riwayatBelum', 
            'pengembanganWajib', 'riwayatTerbaru'
        ));
    }

    // =========================================================
    // FUNGSI KHUSUS ADMIN: DASHBOARD UTAMA (REVISI CLEAN UI)
    // =========================================================

    public function dashboardAdmin()
    {
        // 1. Ambil Angka KPI (Key Performance Indicators)
        $totalPegawai = \App\Models\Pegawai::count();
        $totalJabatan = DB::table('jabatan')->count();
        $sertifikatPending = DB::table('riwayat_pengembangan')->where('status', 'pending')->count();
        $sertifikatApproved = DB::table('riwayat_pengembangan')->where('status', 'approved')->count();

        // 2. Data Grafik BARU: Komposisi Pegawai Berdasarkan Jabatan (Top 5)
        $pegawaiPerJabatan = DB::table('pegawai')
            ->join('jabatan', 'pegawai.id_jabatan', '=', 'jabatan.id')
            ->select('jabatan.nama_jabatan', DB::raw('count(pegawai.nip) as total'))
            ->groupBy('jabatan.id', 'jabatan.nama_jabatan')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $labelsJabatan = $pegawaiPerJabatan->pluck('nama_jabatan')->toArray();
        $dataJabatan = $pegawaiPerJabatan->pluck('total')->toArray();

        // 3. Ambil 5 Antrean Terbaru
        $recentPending = DB::table('riwayat_pengembangan')
            ->join('pegawai', 'riwayat_pengembangan.nip', '=', 'pegawai.nip')
            ->join('pengembangan', 'riwayat_pengembangan.id_pengembangan', '=', 'pengembangan.id')
            ->where('riwayat_pengembangan.status', 'pending')
            ->select('riwayat_pengembangan.*', 'pegawai.nama as nama_pegawai', 'pengembangan.nama_pengembangan')
            ->orderBy('riwayat_pengembangan.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPegawai', 'totalJabatan', 'sertifikatPending', 'sertifikatApproved', 
            'labelsJabatan', 'dataJabatan', 'recentPending'
        ));
    }

    // =========================================================
    // FUNGSI KHUSUS PEGAWAI: UPDATE PROFIL
    // =========================================================

    public function updateProfil(Request $request)
    {
        $user = Auth::guard('pegawai')->user();

        // Validasi input
        $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
            'password' => 'nullable|string|min:6|confirmed', // Harus cocok dengan password_confirmation
        ]);

        $updateData = [];

        // 1. Proses Upload Foto Profil
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil && Storage::exists('public/profile' . $user->foto_profil)) {
                Storage::delete('public/profile' . $user->foto_profil);
            }
            
            // Simpan foto baru ke folder storage/app/public/profil
            $path = $request->file('foto')->store('profil', 'public');
            $updateData['foto_profil'] = $path;
        }

        // 2. Proses Reset Password
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['updated_at'] = now();
        }

        // Lakukan eksekusi update jika ada data yang diubah
        if (!empty($updateData)) {
            DB::table('pegawai')->where('nip', $user->nip)->update($updateData);
            return back()->with('success', 'Profil berhasil diperbarui!');
        }

        return back()->with('info', 'Tidak ada perubahan data yang disimpan.');
    }
}
