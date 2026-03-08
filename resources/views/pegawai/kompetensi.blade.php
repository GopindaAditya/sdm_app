@extends('layouts.pegawai')

@section('title', 'Data Kompetensi')

@section('content')
<div class="flex flex-col gap-8">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-0 top-0 h-full w-1.5 bg-primary rounded-l"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kompetensi</p>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $totalKompetensi }}</h3>
                </div>
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-primary">
                    <span class="material-symbols-outlined text-2xl">insights</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-slate-500 dark:text-slate-400 text-xs">Sesuai standar jabatan</span>
            </div>
        </div>    
    
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-0 top-0 h-full w-1.5 bg-green-500 rounded-l"></div>
            
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Selesai</p>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $totalSelesai }}</h3>
                </div>
                <div class="p-2.5 bg-green-500/10 rounded-xl text-green-500">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
            </div>
            
            <div class="mt-4 flex items-center text-sm">
                <span class="bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-md flex items-center font-semibold text-xs">
                    Kompetensi sudah terpenuhi
                </span>
            </div>
        </div>
    
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-0 top-0 h-full w-1.5 bg-red-500 rounded-l"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum Diikuti</p>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $totalBelum }}</h3>
                </div>
                <div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-xl text-red-500">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded-md flex items-center font-semibold text-xs">
                    <span class="material-symbols-outlined text-sm mr-1">arrow_upward</span> Perlu Tindakan
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-center mb-0">
        <div class="relative w-full sm:max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
            <input id="searchInput" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 transition-shadow text-slate-900 dark:text-white" placeholder="Cari Kompetensi..." type="text"/>
        </div>
        
        <div class="flex gap-2 flex-wrap">
            <button type="button" class="btn-filter active px-4 py-2 bg-primary text-white border border-primary rounded-full text-sm font-medium hover:bg-primary/90 transition-colors" data-filter="semua">Semua</button>
            <button type="button" class="btn-filter px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" data-filter="terpenuhi">Terpenuhi</button>
            <button type="button" class="btn-filter px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" data-filter="belum">Belum terpenuhi</button>            
        </div>
    </div>

    <div id="table-container">
        @include('pegawai._table_kompetensi', ['data' => $kompetensi])
    </div>
    
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let currentSearch = '';
        let currentFilter = 'semua';
        let typingTimer;
        
        function fetchFilteredData(pageUrl = '{{ route("kompetensi.filter") }}') {
            $('#table-container').css('opacity', '0.5'); 
            
            $.ajax({
                url: pageUrl,
                type: 'GET',
                data: {
                    search: currentSearch,
                    filter: currentFilter
                },
                success: function(response) {
                    $('#table-container').html(response);
                    $('#table-container').css('opacity', '1');
                },
                error: function(xhr) {
                    console.error("Error AJAX:", xhr.responseText);
                    $('#table-container').css('opacity', '1');
                    alert('Gagal memuat data tabel. Cek console untuk detail error.');
                }
            });
        }

        $('#searchInput').on('keyup', function() {
            clearTimeout(typingTimer);
            currentSearch = $(this).val();
            
            typingTimer = setTimeout(function() {
                fetchFilteredData();
            }, 500);
        });
        
        $('.btn-filter').on('click', function() {
            $('.btn-filter').removeClass('bg-primary text-white border-primary active')
                            .addClass('bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300');
                        
            $(this).removeClass('bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300')
                   .addClass('bg-primary text-white border-primary active');
                        
            currentFilter = $(this).data('filter');
            fetchFilteredData();
        });

        $(document).on('click', '.pagination-wrapper a', function(e) {
            e.preventDefault();
            fetchFilteredData($(this).attr('href'));
        });
    });
</script>
@endpush