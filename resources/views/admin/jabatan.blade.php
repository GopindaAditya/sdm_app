@extends('layouts.admin')

@section('title', 'Manajemen Data Jabatan')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col gap-6">

    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex items-center">
                <span class="material-symbols-outlined text-emerald-500 mr-3">check_circle</span>
                <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Jabatan</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data jabatan dan petakan standar kompetensi untuk masing-masing peran.</p>
        </div>
        <button type="button" onclick="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-blue-600 transition shadow-sm">
            <span class="material-symbols-outlined text-lg">add</span>
            Tambah Jabatan
        </button>
    </div>

    <div id="table-container" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-opacity duration-300">
        @include('admin._table_jabatan')
    </div>
</div>

<div id="jabatanModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4 hidden flex">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md border border-slate-200 dark:border-slate-800 overflow-hidden">
        <form id="formJabatan" onsubmit="saveData(event)">
            @csrf
            <input type="hidden" name="id" id="jabatan_id">

            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Tambah Jabatan</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_jabatan" id="nama_jabatan" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all"/>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btnSubmit" class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm hidden" id="spinner">progress_activity</span>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modal = $('#jabatanModal');
    const form = $('#formJabatan');

    // --- FUNGSI REFRESH TABEL AJAX ---
    function fetchTableData(pageUrl = '{{ route("jabatan") }}') {
        $('#table-container').css('opacity', '0.5');
        $.ajax({
            url: pageUrl,
            type: 'GET',
            success: function(response) {
                $('#table-container').html(response);
                $('#table-container').css('opacity', '1');
            },
            error: function() {
                Swal.fire('Gagal!', 'Tidak dapat memuat ulang tabel.', 'error');
                $('#table-container').css('opacity', '1');
            }
        });
    }

    // Pagination Click (AJAX)
    $(document).on('click', '.pagination-wrapper a', function(e) {
        e.preventDefault(); 
        fetchTableData($(this).attr('href'));
    });

    // --- FUNGSI MODAL ---
    function openModal(id = null, nama = '') {
        $('#jabatan_id').val(id);
        $('#nama_jabatan').val(nama);
        $('#modalTitle').text(id ? 'Edit Jabatan' : 'Tambah Jabatan');
        modal.removeClass('hidden');
        setTimeout(() => $('#nama_jabatan').focus(), 100); 
    }

    function closeModal() {
        modal.addClass('hidden');
        form[0].reset();
    }

    function saveData(e) {
        e.preventDefault();
        let formData = form.serialize();
        let $btn = $('#btnSubmit');
        let $spinner = $('#spinner');

        $btn.prop('disabled', true).addClass('opacity-70');
        $spinner.removeClass('hidden').addClass('animate-spin');

        $.ajax({
            url: "{{ route('jabatan.tambah') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                if(response.success) {
                    closeModal();
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: response.message, showConfirmButton: false, timer: 1500
                    });
                    fetchTableData(); // REFRESH TABEL SECARA AJAX
                }
            },
            error: function(xhr) {
                let errMsg = 'Terjadi kesalahan sistem.';
                if(xhr.responseJSON && xhr.responseJSON.message) errMsg = xhr.responseJSON.message;
                Swal.fire('Gagal!', errMsg, 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('opacity-70');
                $spinner.addClass('hidden').removeClass('animate-spin');
            }
        });
    }

    function deleteData(id) {
        Swal.fire({
            title: 'Hapus Jabatan?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e73908',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/jabatan/${id}/hapus`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success', title: 'Terhapus!', text: response.message, showConfirmButton: false, timer: 1500
                            });
                            fetchTableData(); // REFRESH TABEL SECARA AJAX
                        }
                    }
                });
            }
        });
    }
</script>
@endpush