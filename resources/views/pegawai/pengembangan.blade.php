@extends('layouts.pegawai')

@section('title', 'Pengembangan Kompetensi')

@section('content')
<div class="max-w-6xl mx-auto">
    
    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex">
                <span class="material-symbols-outlined text-emerald-500 mr-3">check_circle</span>
                <p class="text-sm text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg mb-6 shadow-sm">
            <div class="flex">
                <span class="material-symbols-outlined text-red-500 mr-3">error</span>
                <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div id="ajax-alert-container" class="mb-6 hidden">
        <div id="ajax-alert" class="border-l-4 p-4 rounded-r-lg shadow-sm flex">
            <span id="ajax-alert-icon" class="material-symbols-outlined mr-3"></span>
            <p id="ajax-alert-text" class="text-sm font-medium"></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-0 top-0 h-full w-1.5 bg-primary rounded-l"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pengembangan</p>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $totalPengembangan }}</h3>
                </div>
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-primary">
                    <span class="material-symbols-outlined text-2xl">library_books</span>
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
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Sertifikat Disetujui</p>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $totalSelesai }}</h3>
                </div>
                <div class="p-2.5 bg-green-500/10 rounded-xl text-green-500">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
            </div>
            
            <div class="mt-4 flex items-center text-sm">
                <span class="bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-md flex items-center font-semibold text-xs">
                    Sertifikat telah diverifikasi
                </span>
            </div>
        </div>
    
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-0 top-0 h-full w-1.5 bg-red-500 rounded-l"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Perlu Tindakan</p>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $totalBelum }}</h3>
                </div>
                <div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-xl text-red-500">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded-md flex items-center font-semibold text-xs">
                    <span class="material-symbols-outlined text-sm mr-1">arrow_upward</span> Segera unggah sertifikat
                </span>
            </div>
        </div>
        
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-center mb-6">
        <div class="relative w-full sm:max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
            <input id="searchInput" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 transition-shadow text-slate-900 dark:text-white" placeholder="Cari pengembangan..." type="text"/>
        </div>
        
        <div class="flex gap-2 flex-wrap">
            <button type="button" class="btn-filter active px-4 py-2 bg-primary text-white border border-primary rounded-full text-sm font-medium hover:bg-primary/90 transition-colors" data-filter="semua">Semua</button>
            <button type="button" class="btn-filter px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" data-filter="selesai">Selesai</button>
            <button type="button" class="btn-filter px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" data-filter="pending">Menunggu Review</button>
            <button type="button" class="btn-filter px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" data-filter="belum">Belum Unggah</button>
        </div>
    </div>

    <div id="table-container">
        @include('pegawai._table_pengembangan', ['data' => $pengembangan])
    </div>
</div>

<div id="uploadModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        
        <form action="{{ route('pengembangan.upload') }}" method="POST" enctype="multipart/form-data" id="formUploadSertifikat">
            @csrf
            <input type="hidden" name="id_pengembangan" id="modal_id_pengembangan">

            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 id="modal-title" class="text-lg font-display font-semibold text-slate-900 dark:text-white">Unggah Sertifikat</h3>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                    <p id="modal_nama_pengembangan" class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Pelatihan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan sertifikat yang diunggah sesuai dengan nama pengembangan di atas.</p>
                </div>
                
                <div id="dropzone_container" class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:border-primary transition-colors group">
                    <input type="file" name="sertifikat" id="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    
                    <div id="icon_container" class="w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <span id="upload_icon" class="material-symbols-outlined text-2xl">cloud_upload</span>
                    </div>
                    
                    <p id="file_name_display" class="text-sm font-medium text-slate-900 dark:text-white mb-1">Klik untuk unggah atau seret file kesini</p>
                    <p id="file_info_display" class="text-xs text-slate-500 dark:text-slate-400">PDF, JPG, atau PNG (Maks. 2MB)</p>
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
        let currentSearch = '';
        let currentFilter = 'semua';
        let currentPerPage = 10; 
        let typingTimer;        
        
        function fetchFilteredData(pageUrl = window.location.pathname) {
            $('#table-container').css('opacity', '0.5'); 
            
            $.ajax({
                url: pageUrl,
                type: 'GET',                
                data: {
                    search: currentSearch,
                    filter: currentFilter,
                    per_page: currentPerPage
                },
                success: function(response) {                    
                    let extractedContent = $(response).find('#table-container').html();
                    if (extractedContent) {
                        $('#table-container').html(extractedContent);
                    } else {
                        $('#table-container').html(response);
                    }
                    $('#table-container').css('opacity', '1');
                },
                error: function(xhr) {
                    showAlert('error', 'Koneksi gagal saat memuat data tabel.');
                    $('#table-container').css('opacity', '1');
                }
            });
        }
        
        function showAlert(type, message) {
            let $container = $('#ajax-alert-container');
            let $alert = $('#ajax-alert');
            let $icon = $('#ajax-alert-icon');
            let $text = $('#ajax-alert-text');

            $alert.removeClass('bg-emerald-50 border-emerald-500 bg-red-50 border-red-500');
            $icon.removeClass('text-emerald-500 text-red-500');
            $text.removeClass('text-emerald-700 text-red-700');

            if (type === 'success') {
                $alert.addClass('bg-emerald-50 border-emerald-500');
                $icon.addClass('text-emerald-500').text('check_circle');
                $text.addClass('text-emerald-700').text(message);
            } else {
                $alert.addClass('bg-red-50 border-red-500');
                $icon.addClass('text-red-500').text('error');
                $text.addClass('text-red-700').text(message);
            }

            $container.removeClass('hidden').hide().fadeIn();
            setTimeout(() => { $container.fadeOut(() => $container.addClass('hidden')); }, 5000);
        }
        
        $('#searchInput').on('keyup', function() {
            clearTimeout(typingTimer);
            currentSearch = $(this).val();
            typingTimer = setTimeout(fetchFilteredData, 500); 
        });

        $('.btn-filter').on('click', function() {
            $('.btn-filter').removeClass('bg-primary text-white border-primary active')
                            .addClass('bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300');
            $(this).removeClass('bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300')
                   .addClass('bg-primary text-white border-primary active');
            
            currentFilter = $(this).data('filter');
            fetchFilteredData(); 
        });
        
        $(document).on('change', '#perPage', function() {
            currentPerPage = $(this).val();
            fetchFilteredData(); 
        });

        $(document).on('click', '.pagination-wrapper a', function(e) {
            e.preventDefault(); 
            fetchFilteredData($(this).attr('href'));
        });
        
        $('#formUploadSertifikat').on('submit', function(e) {
            e.preventDefault(); 
            
            let formData = new FormData(this); 
            let $submitBtn = $(this).find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false, 
                processData: false, 
                success: function(response) {
                    if (response.success) {
                        $('#uploadModal').addClass('hidden'); 
                        resetUploadUI(); 
                        $('#formUploadSertifikat')[0].reset(); 
                        showAlert('success', response.message);
                        
                        fetchFilteredData(); 
                    }
                },
                error: function(xhr) {
                    let errMsg = 'Gagal menyimpan sertifikat.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message; 
                    }
                    showAlert('error', errMsg);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text('Simpan');
                }
            });
        });
                
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            
            let deleteUrl = $(this).data('url');

            Swal.fire({
                title: 'Hapus Sertifikat?',
                text: "File akan dihapus secara permanen dan tidak dapat dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', 
                cancelButtonColor: '#64748b',  
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true 
            }).then((result) => {
                if (result.isConfirmed) {                    
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}' 
                        },
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message);
                                fetchFilteredData(); 
                            }
                        },
                        error: function(xhr) {
                            let errMsg = 'Gagal menghapus data.';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                            showAlert('error', errMsg);
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-trigger-modal', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let tanggal = $(this).data('tanggal'); 
            
            $('#modal_id_pengembangan').val(id);
            $('#modal_nama_pengembangan').text(nama);
            
            if (tanggal) {
                $('#modal-title').text('Edit Data Sertifikat');
                $('input[name="tanggal_kegiatan"]').val(tanggal);
                $('#file_sertifikat').prop('required', false);
                $('#file_info_display').text('Biarkan kosong jika tidak ingin mengganti file Anda.');
            } else {
                $('#modal-title').text('Unggah Sertifikat Baru');
                $('input[name="tanggal_kegiatan"]').val('');
                $('#file_sertifikat').prop('required', true);
                $('#file_info_display').text('PDF, JPG, atau PNG (Maks. 2MB)');
            }
            
            $('#uploadModal').removeClass('hidden');
        });

        $('.btn-close-modal').on('click', function() {
            $('#uploadModal').addClass('hidden');
            resetUploadUI();
            $('#formUploadSertifikat')[0].reset();
        });

        $('#file_sertifikat').on('change', function(e) {
            let fileName = e.target.files[0] ? e.target.files[0].name : null;
            if(fileName) {
                $('#file_name_display').text(fileName).addClass('text-emerald-600');
                $('#file_info_display').text('Berkas siap diunggah');
                $('#upload_icon').text('check_circle');
                $('#icon_container').removeClass('bg-primary/10 text-primary').addClass('bg-emerald-500/10 text-emerald-500');
                $('#dropzone_container').removeClass('border-slate-300').addClass('border-emerald-500 bg-emerald-50');
            } else {
                resetUploadUI();
            }
        });

        function resetUploadUI() {
            $('#file_sertifikat').val('');
            $('#file_name_display').text('Klik untuk unggah atau seret file kesini').removeClass('text-emerald-600');
            $('#file_info_display').text('PDF, JPG, atau PNG (Maks. 2MB)');
            $('#upload_icon').text('cloud_upload');
            $('#icon_container').removeClass('bg-emerald-500/10 text-emerald-500').addClass('bg-primary/10 text-primary');
            $('#dropzone_container').removeClass('border-emerald-500 bg-emerald-50').addClass('border-slate-300');
        }
    });
</script>
@endpush