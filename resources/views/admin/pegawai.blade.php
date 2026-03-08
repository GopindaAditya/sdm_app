@extends('layouts.admin')

@section('title', 'Data Pegawai')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col gap-6">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Data Pegawai</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola akun pegawai dan tentukan peran/jabatan struktural mereka.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <select id="filterJabatan" class="w-full sm:w-48 px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all">
                <option value="semua">Semua Jabatan</option>
                @foreach($jabatanList as $jab)
                    <option value="{{ $jab->id }}">{{ $jab->nama_jabatan }}</option>
                @endforeach
            </select>

            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="searchData" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all shadow-sm" placeholder="Cari NIP atau Nama..."/>
            </div>

            <button type="button" onclick="openModal()" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-blue-600 transition shadow-sm shrink-0">
                <span class="material-symbols-outlined text-lg">person_add</span>
                Tambah Akun
            </button>
        </div>
    </div>

    <div id="table-container" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-opacity duration-300">
        @include('admin._table_pegawai')
    </div>
</div>

<div id="formModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4 hidden flex">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
        <form id="mainForm" onsubmit="saveData(event)">
            @csrf
            <input type="hidden" name="nip_lama" id="nip_lama">

            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Tambah Akun Pegawai</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NIP (Nomor Induk) <span class="text-red-500">*</span></label>
                        <input type="text" name="nip" id="nip" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Jabatan <span class="text-red-500">*</span></label>
                        <select name="id_jabatan" id="id_jabatan" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach($jabatanList as $jab)
                                <option value="{{ $jab->id }}">{{ $jab->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900"/>
                </div>                

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password <span id="pwdAsterisk" class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required minlength="6" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900" placeholder="Minimal 6 karakter"/>
                    <p id="pwdHelp" class="text-xs text-amber-600 mt-1 hidden">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btnSubmit" class="px-4 py-2 text-sm font-bold bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm hidden" id="spinner">progress_activity</span>
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentSearch = '';
    let currentJabatan = 'semua';
    let typingTimer;

    function fetchTableData(pageUrl = '{{ route("data_pegawai") }}') {
        $('#table-container').css('opacity', '0.5');
        $.ajax({
            url: pageUrl,
            type: 'GET',
            data: { search: currentSearch, jabatan_id: currentJabatan },
            success: function(response) {
                $('#table-container').html(response);
                $('#table-container').css('opacity', '1');
            }
        });
    }

    $('#searchData').on('keyup', function() {
        clearTimeout(typingTimer);
        currentSearch = $(this).val();
        typingTimer = setTimeout(fetchTableData, 500);
    });

    $('#filterJabatan').on('change', function() {
        currentJabatan = $(this).val();
        fetchTableData();
    });

    $(document).on('click', '.pagination-wrapper a', function(e) {
        e.preventDefault(); 
        fetchTableData($(this).attr('href'));
    });

    // Modal Logic
    function openModal(nip = '', nama = '', id_jabatan = '') {
        $('#nip_lama').val(nip);
        $('#nip').val(nip);
        $('#nama').val(nama);        
        $('#id_jabatan').val(id_jabatan);
        
        if (nip !== '') {
            $('#modalTitle').text('Edit Akun Pegawai');
            $('#password').prop('required', false).attr('placeholder', '********');
            $('#pwdAsterisk').addClass('hidden');
            $('#pwdHelp').removeClass('hidden');
        } else {
            $('#modalTitle').text('Tambah Akun Pegawai');
            $('#password').prop('required', true).attr('placeholder', 'Minimal 6 karakter');
            $('#pwdAsterisk').removeClass('hidden');
            $('#pwdHelp').addClass('hidden');
        }

        $('#formModal').removeClass('hidden');
        setTimeout(() => $('#nip').focus(), 100); 
    }

    function closeModal() {
        $('#formModal').addClass('hidden');
        $('#mainForm')[0].reset();
    }

    function saveData(e) {
        e.preventDefault();
        let $btn = $('#btnSubmit'), $spinner = $('#spinner');
        $btn.prop('disabled', true).addClass('opacity-70');
        $spinner.removeClass('hidden').addClass('animate-spin');

        $.ajax({
            url: "{{ route('data_pegawai.tambah') }}",
            type: "POST",
            data: $('#mainForm').serialize(),
            success: function(response) {
                if(response.success) {
                    closeModal();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, showConfirmButton: false, timer: 1500 });
                    fetchTableData(); 
                }
            },
            error: function(xhr) {
                let errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire('Gagal!', errMsg, 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('opacity-70');
                $spinner.addClass('hidden').removeClass('animate-spin');
            }
        });
    }

    function deleteData(nip) {
        Swal.fire({
            title: 'Hapus Akun Pegawai?',
            text: "Seluruh data profil dan riwayat akan ikut terhapus permanen!",
            icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#e73908', confirmButtonText: 'Ya, Hapus!', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/data-pegawai/${nip}/hapus`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: response.message, showConfirmButton: false, timer: 1500 });
                        fetchTableData();
                    }
                });
            }
        });
    }
</script>
@endpush