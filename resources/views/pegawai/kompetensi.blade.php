@extends('layouts.pegawai')

@section('title', 'Data Kompetensi')

@section('content')
<div class="flex flex-col gap-8">
    
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Data Kompetensi Pegawai</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Employee Competency Data Overview</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Kompetensi</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalKompetensi }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-2xl">library_books</span>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Selesai</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white">0</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center text-green-500">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Belum Terpenuhi</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalKompetensi }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-500">
                <span class="material-symbols-outlined text-2xl">pending</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="relative w-full sm:max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400">search</span>
            </div>
            <input class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg leading-5 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Cari Kompetensi..." type="text"/>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">Semua</button>
            <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Teknis</button>
            <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Managerial</button>
            <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Kultur Sosial</button>
        </div>
    </div>

    <div id="table-container">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Kompetensi</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($kompetensi as $index => $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    {{ ($kompetensi->currentPage() - 1) * $kompetensi->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                                    {{ $item->nama_kompetensi }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">                                    
                                    @if($item->kategori == 'Kompetensi Teknis')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary uppercase tracking-wider">
                                            {{$item->kategori}}
                                        </span>
                                    @elseif($item->kategori == 'Kompetensi Manajerial')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-orange-500/10 text-orange-600 uppercase tracking-wider">
                                            {{$item->kategori}}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                            {{$item->kategori}}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 whitespace-nowrap text-sm text-slate-500 text-center italic">
                                    Belum ada data kompetensi yang ditetapkan untuk jabatan Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($kompetensi->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 pagination-wrapper">
                    {{ $kompetensi->links() }}
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Event listener untuk klik link pagination
        $(document).on('click', '.pagination-wrapper a', function(event) {
            event.preventDefault(); 
            
            let pageUrl = $(this).attr('href');
            
            // Berikan efek transparan agar pengguna tahu sedang loading
            $('#table-container').css('opacity', '0.5');

            $.ajax({
                url: pageUrl,
                type: 'GET',
                success: function(response) {
                    // Ambil isi HTML dari div #table-container yang baru lalu timpa yang lama
                    let newTableContent = $(response).find('#table-container').html();
                    $('#table-container').html(newTableContent);
                    
                    // Kembalikan opacity
                    $('#table-container').css('opacity', '1');
                },
                error: function() {
                    alert('Gagal memuat data. Silakan coba lagi.');
                    $('#table-container').css('opacity', '1');
                }
            });
        });
    });
</script>
@endpush