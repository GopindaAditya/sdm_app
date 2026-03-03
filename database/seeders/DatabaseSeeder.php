<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        
        $periodeId = DB::table('periode')->insertGetId([
            'tahun' => '2026',
            'status' => 'Aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
                
        $dataKompetensi = [
            'Statistisi Ahli Pertama' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi pada hasil', 'Pelayanan publik', 'Pengembangan diri dan orang lain', 'Mengelola perubahan', 'Pengambilan keputusan'],
                'Kompetensi Teknis' => ['Melaksanakan pengumpulan pengolahan, dan penyajian data', 'Mengolah statistik dasar', 'Membantu analisis dan diseminasi data', 'Menjamin mutu data dasar'],
                'Kultur Sosial' => ['Adaptasi lingkungan kerja', 'Etika pelayanan publik', 'Kerja sama lintas unit']
            ],
            'Statistisi Ahli Muda' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi pada hasil', 'Pelayanan publik', 'Pengembangan diri dan orang lain', 'Mengelola perubahan', 'Pengambilan keputusan'],
                'Kompetensi Teknis' => ['Menyusun instrumen pengumpulan data', 'Analisis data menengah', 'Evaluasi kualitas statistik', 'Menyusun laporan analisis'],
                'Kultur Sosial' => ['Kolaborasi lintas unit', 'Penanganan kebutuhan pengguna statistik', 'Etika layanan data']
            ],
            'Statistisi Ahli Madya' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi pada hasil', 'Pelayanan publik', 'Pengembangan diri dan orang lain', 'Mengelola perubahan', 'Pengambilan keputusan'],
                'Kompetensi Teknis' => ['Perumusan metodologi statistik lanjutan', 'Analisis inferensial dan pemodelan', 'Pengembangan indikator statistik', 'Rekomendasi kebijakan berbasis data'],
                'Kultur Sosial' => ['Kepemimpinan sosial', 'Etika profesional lintas unit', 'Sensitivitas budaya organisasi']
            ],
            
            'Pranata Komputer Ahli Pertama' => [
                'Kompetensi Manajerial' => ['Kepemimpinan strategis', 'Koordinasi dan pengambilan keputusan tingkat tinggi', 'Perencanaan dan pengelolaan sumber daya TI', 'Pengembangan staf dan proyek'],
                'Kompetensi Teknis' => ['Perancangan strategi TI organisasi', 'Analisis sistem dan kebutuhan bisnis', 'Implementasi sistem kompleks', 'Audit dan keamanan sistem tingkat lanjut', 'Pengembangan inovasi TI sesuai kebutuhan organisasi'],
                'Kultur Sosial' => ['Komunikasi dan advokasi teknologi', 'Etika layanan TI dalam eskalasi kompleks', 'Pemahaman budaya organisasi']
            ],
            'Pranata Komputer Terampil' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama tim', 'Komunikasi efektif', 'Orientasi pada hasil', 'Perencanaan dan pengaturan tugas kerja'],
                'Kompetensi Teknis' => ['Mengoperasikan dan memelihara perangkat keras dan lunak komputer', 'Mengelola jaringan komputer dasar', 'Menyusun dan memperbarui dokumentasi teknis', 'Mengatasi gangguan teknis sederhana pada sistem komputer', 'Melakukan instalasi perangkat dan aplikasi sesuai kebutuhan organisasi'],
                'Kultur Sosial' => ['Kemampuan beradaptasi di lingkungan kerja', 'Etika pelayanan publik', 'Kepedulian terhadap kebutuhan pengguna layanan TI']
            ],
            'Pranata Komputer Mahir' => [
                'Kompetensi Manajerial' => ['Integritas yang kuat', 'Koordinasi dan delegasi tugas', 'Pengambilan keputusan teknis', 'Membimbing dan mengevaluasi staf bawahannya'],
                'Kompetensi Teknis' => ['Mengelola sistem komputer dan jaringan tingkat menengah', 'Mengkonfigurasi perangkat lunak dan perangkat jaringan', 'Analisis kebutuhan pengguna sistem', 'Peningkatan kinerja dan keamanan sistem', 'Pengujian dan penyelesaian masalah tingkat lanjut'],
                'Kultur Sosial' => ['Menjalin kerja sama lintas unit', 'Adaptasi terhadap dinamika organisasi', 'Etika layanan berbasis teknologi informasi']
            ],
            'Pranata Komputer Penyelia' => [
                'Kompetensi Manajerial' => ['Kepemimpinan dalam tim teknologi', 'Perencanaan strategis proyek TI', 'Evaluasi kinerja tim dan keberhasilan implementasi', 'Pemecahan masalah kompleks'],
                'Kompetensi Teknis' => ['Perancangan arsitektur jaringan/komputer', 'Pengembangan sistem aplikasi internal', 'Pengamanan data/infrastruktur TI', 'Pengelolaan database dan server', 'Penyusunan spesifikasi teknis dan SOP layanan TI'],
                'Kultur Sosial' => ['Kepemimpinan lintas kelompok', 'Pelayanan kepada pemangku kepentingan', 'Sensitivitas budaya teknologi informasi dalam organisasi']
            ],

            'Keuangan APBN Madya' => [
                'Kompetensi Manajerial' => ['Mengkoordinasikan pekerjaan teknis di unit', 'Menyusun dan mengendalikan jadwal penyusunan laporan keuangan', 'Menangani permasalahan rutin secara mandiri berdasarkan kebijakan'],
                'Kompetensi Teknis' => ['Mampu mengendalikan proses penatausahaan keuangan', 'Melakukan verifikasi dan rekonsiliasi serta menyusun laporan keuangan komprehensif'],
                'Kultur Sosial' => ['Berkomunikasi efektif dengan unit lain (PPK, bendahara, tim keuangan)', 'Menjaga etika, integritas, dan kerahasiaan data keuangan', 'Beradaptasi terhadap perubahan prosedur atau peningkatan standar kerja']
            ],
            'Keuangan APBN Muda' => [
                'Kompetensi Manajerial' => ['Mengatur prioritas tugas penatausahaan anggaran', 'Memastikan pekerjaan selesai sesuai jadwal', 'Menjaga akurasi dan kerapihan data serta bukti transaksi'],
                'Kompetensi Teknis' => ['Menatausahakan bukti transaksi keuangan sesuai standar APBN', 'Menginput dan memutakhirkan data transaksi pada sistem aplikasi keuangan pemerintah', 'Memeriksa kelengkapan dokumen pengeluaran dan penerimaan sesuai ketentuan', 'Menyusun laporan periodik sederhana (bulanan/triwulan) secara akurat'],
                'Kultur Sosial' => ['Berkomunikasi efektif dengan unit lain (PPK, bendahara, tim keuangan)', 'Menjaga etika, integritas, dan kerahasiaan data keuangan', 'Beradaptasi terhadap perubahan prosedur atau peningkatan standar kerja']
            ],
            'Pranata Keuangan APBN Mahir' => [
                'Kompetensi Manajerial' => ['Mengkoordinasikan pekerjaan teknis di unit', 'Menyusun dan mengendalikan jadwal penyusunan laporan keuangan', 'Menangani permasalahan rutin secara mandiri berdasarkan kebijakan'],
                'Kompetensi Teknis' => ['Melakukan verifikasi lanjutan atas dokumen pertanggungjawaban keuangan', 'Melaksanakan rekonsiliasi data dan analisis penyimpangan realisasi anggaran', 'Menyusun laporan keuangan lengkap untuk pertanggungjawaban unit kerja', 'Mampu memimpin prosedur teknis penatausahaan di satuan'],
                'Kultur Sosial' => ['Berkomunikasi efektif dengan unit lain (PPK, bendahara, tim keuangan)', 'Menjaga etika, integritas, dan kerahasiaan data keuangan', 'Beradaptasi terhadap perubahan prosedur atau peningkatan standar kerja']
            ],
            'Pranata Keuangan APBN Terampil' => [
                'Kompetensi Manajerial' => ['Mengatur prioritas tugas penatausahaan anggaran', 'Memastikan pekerjaan selesai sesuai jadwal', 'Menjaga akurasi dan kerapihan data serta bukti transaksi', 'Mengkoordinasikan pekerjaan teknis di unit', 'Menyusun dan mengendalikan jadwal penyusunan laporan keuangan', 'Menangani permasalahan rutin secara mandiri berdasarkan kebijakan'],
                'Kompetensi Teknis' => ['Menatausahakan bukti transaksi keuangan sesuai standar APBN', 'Menginput dan memutakhirkan data transaksi pada sistem aplikasi keuangan pemerintah', 'Memeriksa kelengkapan dokumen pengeluaran dan penerimaan sesuai ketentuan', 'Menyusun laporan periodik sederhana (bulanan/triwulan) secara akurat'],
                'Kultur Sosial' => ['Berkomunikasi efektif dengan unit lain (PPK, bendahara, tim keuangan)', 'Menjaga etika, integritas, dan kerahasiaan data keuangan', 'Beradaptasi terhadap perubahan prosedur atau peningkatan standar kerja']
            ],

            'Kearsipan Pertama' => [
                'Kompetensi Manajerial' => ['Integritas profesional dalam pelaksanaan tugas', 'Kerja Sama Tim dengan rekan kerja dan unit terkait', 'Pengembangan Diri dan menerima masukan', 'Pengambilan Keputusan Dasar untuk isu teknis sederhana'],
                'Kompetensi Teknis' => ['Pengelolaan Arsip Dinamis', 'Pengelolaan Arsip Statis Dasar', 'Pelayanan Arsip'],
                'Kultur Sosial' => ['Komunikasi Efektif dengan pengguna layanan dan tim kerja', 'Pelayanan Publik yang memenuhi standar', 'Adaptabilitas dan Kolaborasi']
            ],
            'Kearsipan Madya' => [
                'Kompetensi Manajerial' => ['Pengambilan Keputusan dan Perencanaan Program', 'Koordinasi dan Pembinaan Tim', 'Orientasi Hasil & Analisis Masalah Kompleks'],
                'Kompetensi Teknis' => ['Pengelolaan Arsip Dinamis Lanjutan', 'Pengelolaan Arsip Statis & Penyajian Informasi Arsip', 'Pembinaan Kearsipan Unit Kerja', 'Pengembangan Sistem Kearsipan'],
                'Kultur Sosial' => ['Komunikasi Antar Unit/Instansi', 'Pelayanan Publik Profesional', 'Adaptasi Perubahan & Inovasi']
            ],

            'Analis Anggaran Madya' => [
                'Kompetensi Manajerial' => ['Merencanakan', 'Mengoordinasikan', 'Memutuskan', 'Membina tim kendali kinerja'],
                'Kompetensi Teknis' => ['Memimpin analisis', 'QA dan rekomendasi', 'Kebijakan', 'Monev', 'Pengembangan metode'],
                'Kultur Sosial' => ['Komunikasi kebijakan', 'Kolaborasi', 'Layanan publik', 'Integritas dan adaptif']
            ],

            'Analis SDMA Pertama' => [
                'Kompetensi Manajerial' => ['Perencanaan kerja individu dan pengelolaan waktu', 'Ketelitian, disiplin, dan kepatuhan prosedur', 'Pemecahan masalah operasional sederhana', 'Pelaporan hasil kerja dan dokumentasi'],
                'Kompetensi Teknis' => ['Mengumpulkan dan mengolah data SDM aparatur', 'Menyusun bahan analisis sederhana', 'Membantu penyusunan dokumen perencanaan SDM', 'Mengelola database SDM dan pemutakhiran data', 'Menyusun laporan teknis rutin bidang SDM aparatur', 'Mendukung layanan administrasi SDM'],
                'Kultur Sosial' => ['Komunikasi dasar dengan pemangku kepentingan internal', 'Kerja sama tim dan orientasi layanan', 'Menjunjung etika, integritas, dan netralitas ASN', 'Adaptif terhadap perubahan sistem/aplikasi SDM']
            ],
            'Analis SDMA Muda' => [
                'Kompetensi Manajerial' => ['Perencanaan kegiatan analisis SDM dan penetapan prioritas', 'Koordinasi lintas unit dan pengelolaan pemangku kepentingan', 'Pengambilan keputusan teknis berbasis data dan risiko', 'Pembinaan teknis kepada pelaksana/jenjang di bawahnya', 'Pengendalian mutu pekerjaan dan penyusunan laporan kinerja'],
                'Kompetensi Teknis' => ['Melakukan analisis SDM aparatur tingkat unit/instansi', 'Menyusun rekomendasi teknis berbasis data', 'Melaksanakan review/quality check dokumen SDM', 'Mengembangkan dan memanfaatkan instrumen analisis', 'Melakukan monitoring & evaluasi kebijakan/program SDM aparatur', 'Menyusun laporan analitis dan bahan kebijakan teknis SDM'],
                'Kultur Sosial' => ['Komunikasi kebijakan yang jelas dan persuasif', 'Kolaborasi lintas unit/instansi', 'Orientasi pelayanan publik dan akuntabilitas', 'Menjaga integritas, etika profesi, dan budaya kerja', 'Adaptif terhadap transformasi digital dan perubahan kebijakan SDM']
            ],

            'Pengelola Barang dan Jasa Pertama' => [
                'Kompetensi Manajerial' => ['Rencana kerja individu', 'Disiplin', 'Problem solving sederhana', 'Kerja tim'],
                'Kompetensi Teknis' => ['Administrasi & dukungan proses pengadaan', 'Pengelolaan data/dokumen dan monitoring sederhana', 'Laporan'],
                'Kultur Sosial' => ['Komunikasi', 'Layanan', 'Integritas', 'Adaptif']
            ],
            'Penata Laksana Barang Mahir' => [
                'Kompetensi Manajerial' => ['Rencana kerja', 'Kendali mutu', 'Koordinasi', 'Problem solving operasional'],
                'Kompetensi Teknis' => ['Penatausahaan barang lanjutan', 'Rekonsiliasi', 'Laporan', 'Verifikasi', 'Bimbingan teknis'],
                'Kultur Sosial' => ['Komunikasi', 'Kerja tim', 'Integritas', 'Adaptif', 'Akuntabel']
            ],

            'Pranata Humas Ahli Pertama' => [
                'Kompetensi Manajerial' => ['Perencanaan dan pengorganisasian kegiatan humas', 'Pengambilan keputusan berbasis data dan situasi komunikasi terkini', 'Koordinasi karya humas lintas unit kerja dan stakeholders'],
                'Kompetensi Teknis' => ['Pelayanan informasi dan kehumasan (menyusun pesan, materi, narasi)'],
                'Kultur Sosial' => ['Komunikasi interpersonal efektif dengan pimpinan, media, dan publik', 'Adaptasi terhadap dinamika budaya dan sosial dalam penyebaran informasi', 'Keterampilan membangun hubungan baik dengan komunitas dan mitra eksternal']
            ],

            'Pranata SDM Aparatur Terampil' => [
                'Kompetensi Manajerial' => ['Perencanaan & organisasi tugas administrasi SDM', 'Koordinasi kerja tim terkait kegiatan administrasi kepegawaian', 'Pengelolaan waktu dan sumber daya untuk penyelesaian tugas tepat waktu', 'Pengambilan keputusan berbasis prosedur dan bukti kerja'],
                'Kompetensi Teknis' => ['Melaksanakan kegiatan penataan administrasi SDM', 'Menyusun dokumen dan pelaporan proses administrasi kepegawaian', 'Mengoperasikan sistem administrasi kepegawaian', 'Mengklasifikasi dan mengelola bahan, data, informasi terkait pengelolaan SDM ASN'],
                'Kultur Sosial' => ['Komunikasi efektif dalam menyampaikan informasi administrasi', 'Adaptasi terhadap perubahan tata kelola SDM ASN/teknologi informasi instansi', 'Kerja sama lintas unit kerja dalam pelayanan administrasi']
            ],

            'Pengelola Data dan Informasi' => [
                'Kompetensi Manajerial' => ['Integritas', 'Kerja sama', 'Komunikasi', 'Orientasi pada hasil', 'Pelayanan publik', 'Pengembangan diri dan orang lain', 'Pengambilan keputusan'],
                'Kompetensi Teknis' => ['Melakukan pengumpulan dan verifikasi data dari berbagai sumber', 'Melakukan entri, validasi, dan pemutakhiran data pada sistem/aplikasi', 'Mengolah data menjadi informasi yang akurat dan sistematis', 'Menyusun laporan hasil pengolahan data', 'Menggunakan aplikasi perkantoran dan sistem informasi', 'Menjaga keamanan dan kerahasiaan data'],
                'Kultur Sosial' => ['Mampu bekerja dalam lingkungan yang beragam', 'Menghargai perbedaan latar belakang sosial, budaya, dan agama', 'Adaptif terhadap perubahan kebijakan dan sistem kerja', 'Mampu membangun hubungan kerja yang harmonis']
            ],

            'Penyuluh Hukum Ahli Pertama' => [
                'Kompetensi Manajerial' => ['Perencanaan dan pengorganisasian kegiatan penyuluhan', 'Koordinasi dengan institusi/kantor desa dan lembaga bantuan hukum', 'Pengambilan keputusan yang efektif dan berbasis data/analisis situasi hukum', 'Pengelolaan waktu dan sumber daya untuk pelaksanaan penyuluhan'],
                'Kompetensi Teknis' => ['Memahami peraturan perundang-undangan dan hukum substantif', 'Menyusun, merencanakan, dan menyampaikan materi penyuluhan hukum', 'Mengidentifikasi permasalahan hukum masyarakat', 'Menyusun laporan kegiatan penyuluhan'],
                'Kultur Sosial' => ['Menjalin hubungan kerja yang baik dengan berbagai kelompok masyarakat', 'Kemampuan komunikasi yang adaptif dalam menyampaikan materi hukum', 'Mampu memahami dinamika sosial budaya lokal']
            ],
        ];
        
        foreach ($dataKompetensi as $namaJabatan => $kategoriKompetensi) {
                                    
            $jabatanId = DB::table('jabatan')->insertGetId([
                'nama_jabatan' => $namaJabatan,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

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
                    
                    DB::table('syarat_kompetensi')->insert([
                        'id_jabatan' => $jabatanId,
                        'id_kompetensi' => $kompetensiId,
                        'id_periode' => $periodeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
        DB::table('admins')->insert([
            'username' => 'admin',
            'password' => Hash::make('admin123'),            
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('pegawai')->insert([            
            [
                'nip' => '19900101',
                'password' => Hash::make('budi123'),
                'nama' => 'Budi Pertama',
                'id_jabatan' => 1,                 
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nip' => '19950505',
                'password' => Hash::make('made123'),
                'nama' => 'Made Putri',
                'id_jabatan' => 2,                 
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
        
        $this->command->info('Seeder done!');
    }
}
