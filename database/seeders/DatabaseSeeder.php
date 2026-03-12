<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();                  

        DB::table('admins')->insert([
            'username' => 'admin',
            'password' => Hash::make('admin123'),            
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        
        $dataJabatan = [
            'Statistisi Ahli Pertama' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi pada hasil', 'Pelayanan publik', 'Pengembangan diri dan orang lain', 'Mengelola perubahan', 'Pengambilan keputusan'],
                'Kompetensi Teknis' => ['Melaksanakan pengumpulan pengolahan, dan penyajian data', 'Mengolah statistik dasar', 'Membantu analisis dan diseminasi data', 'Menjamin mutu data dasar'],
                'Kultur Sosial' => ['Adaptasi lingkungan kerja', 'Etika pelayanan publik', 'Kerja sama lintas unit'],
                'Pengembangan' => ['Diklat pengumpulan pengolahan, dan penyajian data', 'Diklat statistik dasar', 'Diklat analisis dan diseminasi data', 'Diklat penjaminan mutu data dasar']
            ],
            'Statistisi Ahli Muda' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi pada hasil', 'Pelayanan publik', 'Pengembangan diri dan orang lain', 'Mengelola perubahan', 'Pengambilan keputusan'],
                'Kompetensi Teknis' => ['Menyusun instrumen pengumpulan data', 'Analisis data menengah', 'Evaluasi kualitas statistik', 'Menyusun laporan analisis'],
                'Kultur Sosial' => ['Kolaborasi lintas unit', 'Penanganan kebutuhan pengguna statistik', 'Etika layanan data'],
                'Pengembangan' => ['Diklat penyusunan instrumen pengumpulan data', 'Diklat Analisis data menengah', 'Diklat Evaluasi kualitas statistik', 'Diklat penyusunan laporan analisis']
            ],
            'Analis Keuangan APBN Pertama' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi hasil', 'Pengelolaan perubahan'],
                'Kompetensi Teknis' => ['Analisis pelaksanaan APBN', 'Penyusunan & evaluasi laporan keuangan pemerintah', 'Monitoring dan pengendalian anggaran', 'Pemahaman regulasi keuangan negara'],
                'Kultur Sosial' => ['Kemampuan menjaga tata kelola pemerintahan yang baik'],
                'Pengembangan' => ['Diklat pengelolaan APBN', 'Pelatihan aplikasi SAKTI/OM-SPAN', 'Workshop audit dan pelaporan keuangan', 'Sertifikasi pengelolaan keuangan negara']
            ],
            'Statistisi Terampil' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi hasil/pelayanan'],
                'Kompetensi Teknis' => ['Pengumpulan data statistik', 'Pengolahan data statistik', 'Validasi dan verifikasi data', 'Penyajian data statistik sederhana', 'Teknik sampling / metode survei'],
                'Kultur Sosial' => ['Kemampuan berinteraksi dengan responden dan masyarakat'],
                'Pengembangan' => ['Pelatihan metodologi survei', 'Pelatihan pengolahan data (Excel/SPSS/R)', 'Diklat quality control data', 'Workshop visualisasi data']
            ],
        ];
        
        $this->command->info('Memulai pemetaan arsitektur database...');

        foreach ($dataJabatan as $namaJabatan => $detail) {
                        
            $jabatanId = DB::table('jabatan')->insertGetId([
                'nama_jabatan' => $namaJabatan,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $idKompetensiTeknis = []; 
            
            $kategoriKompetensi = [
                'Kompetensi Manajerial' => $detail['Kompetensi Manajerial'] ?? [],
                'Kompetensi Teknis'     => $detail['Kompetensi Teknis'] ?? [],
                'Kultur Sosial'         => $detail['Kultur Sosial'] ?? []
            ];
            
            foreach ($kategoriKompetensi as $kategori => $listKompetensi) {
                foreach ($listKompetensi as $namaKompetensi) {
                                        
                    $kompetensi = DB::table('kompetensi')
                        ->where('nama_kompetensi', $namaKompetensi)
                        ->where('kategori', $kategori)
                        ->first();

                    if (!$kompetensi) {
                        $kompetensiId = DB::table('kompetensi')->insertGetId([
                            'nama_kompetensi' => $namaKompetensi,
                            'kategori' => $kategori,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        $kompetensiId = $kompetensi->id;
                    }
                    
                    if ($kategori === 'Kompetensi Teknis') {                        
                        $idKompetensiTeknis[] = $kompetensiId;
                    }
                    
                    DB::table('jabatan_kompetensi')->insert([
                        'id_jabatan' => $jabatanId,
                        'id_kompetensi' => $kompetensiId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $listPengembangan = $detail['Pengembangan'] ?? [];
                        
            foreach ($listPengembangan as $index => $namaPengembangan) {
                
                $pengembangan = DB::table('pengembangan')->where('nama_pengembangan', $namaPengembangan)->first();
                
                if (!$pengembangan) {
                    $pengembanganId = DB::table('pengembangan')->insertGetId([
                        'nama_pengembangan' => $namaPengembangan,                        
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $pengembanganId = $pengembangan->id;
                }
                                
                if (isset($idKompetensiTeknis[$index])) {
                    $idKompTeknis = $idKompetensiTeknis[$index];
                    
                    $cekRelasi = DB::table('pengembangan_kompetensi')
                        ->where('id_pengembangan', $pengembanganId)
                        ->where('id_kompetensi', $idKompTeknis)
                        ->first();

                    if (!$cekRelasi) {
                        DB::table('pengembangan_kompetensi')->insert([
                            'id_pengembangan' => $pengembanganId,
                            'id_kompetensi' => $idKompTeknis,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }

        DB::table('pegawai')->insert([
            [
                'nip' => '19900101',
                'password' => Hash::make('budi123'),
                'nama' => 'Budi Pertama',
                'id_jabatan' => 1,                
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);

        $this->command->info('Seeder Done');
    }
}