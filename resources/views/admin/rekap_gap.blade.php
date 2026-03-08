@extends('layouts.admin')

@section('title', 'Rekap GAP Kompetensi Pegawai')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col gap-6">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Rekap GAP Kompetensi BPS Provinsi Bali</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Laporan kesenjangan (GAP) antara standar kompetensi jabatan dengan pencapaian riil pegawai.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="searchData" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white transition-all shadow-sm" placeholder="Cari nama / NIP..."/>
            </div>

            <button type="button" onclick="cetakLaporan()" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl text-sm font-bold hover:bg-emerald-500 hover:text-white transition shadow-sm shrink-0">
                <span class="material-symbols-outlined text-lg">print</span>
                Cetak
            </button>
        </div>
    </div>

    <div id="table-container" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-opacity duration-300">
        @include('admin._table_rekap_gap')
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentSearch = '';
    let typingTimer;

    function fetchTableData(pageUrl = '{{ route("rekap_gap") }}') {
        $('#table-container').css('opacity', '0.5').css('pointer-events', 'none');
        $.ajax({
            url: pageUrl,
            type: 'GET',
            data: { search: currentSearch },
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

    // FUNGSI CETAK: Memanggil Route Export dengan Filter Search yang aktif
    function cetakLaporan() {
        let url = `{{ route('export_rekap_gap') }}?search=${currentSearch}`;
        window.location.href = url;
        
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
        });
        Toast.fire({ icon: 'success', title: 'Menyiapkan file Excel...' });
    }

    $('#searchData').on('keyup', function() {
        clearTimeout(typingTimer);
        currentSearch = $(this).val();
        typingTimer = setTimeout(fetchTableData, 500);
    });

    $(document).on('click', '.pagination-wrapper a', function(e) {
        e.preventDefault(); 
        fetchTableData($(this).attr('href'));
    });
</script>
@endpush