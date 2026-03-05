@extends('layouts.pegawai')

@section('title', 'Pengembangan Kompetensi')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex">
                <span class="material-symbols-outlined text-emerald-500 mr-3">check_circle</span>
                <p class="text-sm text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Pengembangan</p>
                <p class="text-3xl font-display font-bold text-slate-900 dark:text-white">{{ $totalPengembangan }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-2xl">library_books</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Sertifikat Disetujui</p>
                <p class="text-3xl font-display font-bold text-emerald-500">{{ $totalSelesai }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Perlu Tindakan</p>
                <p class="text-3xl font-display font-bold text-amber-500">{{ $totalBelum }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500">
                <span class="material-symbols-outlined text-2xl">warning</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="relative w-full sm:max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400">search</span>
            </div>
            <input class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg leading-5 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Cari Pengembangan..." type="text"/>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
            <button class="px-4 py-1.5 rounded-full bg-slate-800 text-white dark:bg-white dark:text-slate-900 text-sm font-medium whitespace-nowrap">Semua</button>
            <button class="px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm font-medium whitespace-nowrap transition-colors">Selesai</button>
            <button class="px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm font-medium whitespace-nowrap transition-colors">Belum Unggah</button>
        </div>
    </div>

    <div id="table-container">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 font-medium">Nama Pengembangan</th>
                            <th class="px-6 py-4 font-medium">Tanggal Diikuti</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($pengembangan as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $item->nama_pengembangan }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                    {{ $item->tanggal_kegiatan ? \Carbon\Carbon::parse($item->tanggal_kegiatan)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->status_pengembangan == 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                            <span class="material-symbols-outlined text-[14px]">check</span> Selesai
                                        </span>
                                    @elseif($item->status_pengembangan == 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                            <span class="material-symbols-outlined text-[14px]">hourglass_empty</span> Menunggu Review
                                        </span>
                                    @elseif($item->status_pengembangan == 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-red-500/10 text-red-600 border border-red-500/20">
                                            <span class="material-symbols-outlined text-[14px]">close</span> Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Belum Unggah
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(in_array($item->status_pengembangan, ['approved', 'pending']))
                                        <a href="{{ asset('storage/sertifikat/' . $item->sertifikat) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span> Lihat File
                                        </a>
                                    @else
                                        <button type="button" 
                                            class="btn-trigger-modal inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors shadow-sm"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->nama_pengembangan }}">
                                            <span class="material-symbols-outlined text-[18px]">upload</span> Unggah Sertifikat
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 italic">
                                    Belum ada daftar pengembangan kompetensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($pengembangan->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30 pagination-wrapper">
                    {{ $pengembangan->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<div id="uploadModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        
        <form action="{{ route('pengembangan.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_pengembangan" id="modal_id_pengembangan">

            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-display font-semibold text-slate-900 dark:text-white">Unggah Sertifikat</h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                    <p id="modal_nama_pengembangan" class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Pelatihan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan sertifikat yang diunggah sesuai dengan nama pengembangan di atas.</p>
                </div>
                
                <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:border-primary transition-colors group">
                    <input type="file" name="sertifikat" accept=".pdf,.jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">cloud_upload</span>
                    </div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white mb-1">Klik untuk unggah atau seret file kesini</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">PDF, JPG, atau PNG (Maks. 2MB)</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Kegiatan/Sertifikat <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_kegiatan" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white"/>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex justify-end gap-3">
                <button type="button" class="btn-close-modal px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        
        // 1. Logika AJAX Pagination (Sama seperti halaman kompetensi)
        $(document).on('click', '.pagination-wrapper a', function(event) {
            event.preventDefault(); 
            let pageUrl = $(this).attr('href');
            
            $('#table-container').css('opacity', '0.5');

            $.ajax({
                url: pageUrl,
                type: 'GET',
                success: function(response) {
                    let newTableContent = $(response).find('#table-container').html();
                    $('#table-container').html(newTableContent);
                    $('#table-container').css('opacity', '1');
                },
                error: function() {
                    alert('Gagal memuat data.');
                    $('#table-container').css('opacity', '1');
                }
            });
        });

        // 2. Logika Menampilkan & Menyembunyikan Modal Upload
        // Menggunakan delegasi event agar tombol di halaman AJAX tetap berfungsi
        $(document).on('click', '.btn-trigger-modal', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            
            // Isi data ke dalam modal
            $('#modal_id_pengembangan').val(id);
            $('#modal_nama_pengembangan').text(nama);
            
            // Tampilkan modal
            $('#uploadModal').removeClass('hidden');
        });

        // Menutup Modal
        $('.btn-close-modal').on('click', function() {
            $('#uploadModal').addClass('hidden');
        });

        // Menutup modal jika klik di luar area modal (backdrop)
        $('#uploadModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden');
            }
        });
    });
</script>
@endpush