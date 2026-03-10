@extends('layouts.admin')

@section('title', 'Rekap Kompetensi Pegawai')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col gap-6">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Rekap Kompetensi Pegawai BPS Provinsi Bali</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Menampilkan daftar kompetensi yang berhasil dipenuhi pegawai dalam rentang 1 (satu) tahun berjalan.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-48">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">calendar_month</span>
                <select id="filterTahun" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white transition-all appearance-none cursor-pointer">
                    <option value="semua" {{ $tahun === 'semua' ? 'selected' : '' }}>Semua Tahun</option>
                    
                    @foreach($listTahun as $thn)
                        <option value="{{ $thn }}" {{ $thn == $tahun ? 'selected' : '' }}>Tahun {{ $thn }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="searchData" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white transition-all shadow-sm" placeholder="Cari nama pegawai..."/>
            </div>

            <button type="button" onclick="cetakLaporan()" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl text-sm font-bold hover:bg-emerald-500 hover:text-white transition shadow-sm shrink-0">
                <span class="material-symbols-outlined text-lg">print</span>
                Cetak
            </button>
        </div>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-start">
            <span class="material-symbols-outlined text-blue-500 mr-3">info</span>
            <p class="text-sm text-blue-800">
                Data pada kolom <strong>Kompetensi Dimiliki</strong> ditarik berdasarkan sertifikat/diklat yang diunggah pegawai pada tahun terpilih dan <strong>telah disetujui (Approved)</strong> oleh Admin.
            </p>
        </div>
    </div>

    <div id="table-container" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-opacity duration-300">
        @include('admin._table_rekap')
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentSearch = '';
    let currentTahun = '{{ $tahun }}';
    let currentPerPage = 10; 
    let typingTimer;

    function fetchTableData(pageUrl = '{{ route("rekap_kompetensi") }}') {
        $('#table-container').css('opacity', '0.5').css('pointer-events', 'none');
        $.ajax({
            url: pageUrl,
            type: 'GET',            
            data: { search: currentSearch, tahun: currentTahun, per_page: currentPerPage },
            success: function(response) {
                $('#table-container').html(response);
                $('#table-container').css('opacity', '1').css('pointer-events', 'auto');
            },
            error: function() {
                Swal.fire('Gagal!', 'Tidak dapat memuat ulang tabel.', 'error');
                $('#table-container').css('opacity', '1').css('pointer-events', 'auto');
            }
        });
    }

    $('#searchData').on('keyup', function() {
        clearTimeout(typingTimer);
        currentSearch = $(this).val();
        typingTimer = setTimeout(function() {
            fetchTableData('{{ route("rekap_kompetensi") }}'); 
        }, 500);
    });
    
    $('#filterTahun').on('change', function() {
        currentTahun = $(this).val();
        fetchTableData('{{ route("rekap_kompetensi") }}'); 
    });
    
    $(document).on('change', '#perPage', function() {
        currentPerPage = $(this).val();
        fetchTableData('{{ route("rekap_kompetensi") }}'); 
    });
    
    $(document).on('click', '.pagination-wrapper a', function(e) {
        e.preventDefault(); 
        fetchTableData($(this).attr('href'));
    });

    function cetakLaporan() {        
        let url = `{{ route('export_rekap_kompetensi') }}?tahun=${currentTahun}&search=${currentSearch}`;            
        window.location.href = url;
                
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
        });
        Toast.fire({ icon: 'success', title: 'File Excel sedang diunduh...' });
    }
</script>
@endpush