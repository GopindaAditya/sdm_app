@extends('layouts.admin')

@section('title', 'Pemetaan Kompetensi Jabatan')

@section('content')
<div class="max-w-5xl mx-auto flex flex-col gap-6 relative pb-24">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('jabatan') }}" class="p-2 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary transition-colors shadow-sm">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Standar Kompetensi</h2>
                <p class="text-sm font-medium text-primary mt-1">Jabatan: {{ $jabatan->nama_jabatan }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="searchKompetensi" class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all shadow-sm" placeholder="Cari kompetensi..."/>
            </div>
            <button type="button" onclick="openModalKomp()" class="flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-blue-600 transition shadow-sm shrink-0 tooltip" title="Buat Kompetensi Baru">
                <span class="material-symbols-outlined text-lg">add</span>
                <span class="hidden sm:inline">Tambah Kompetensi</span>
            </button>
        </div>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-start">
            <span class="material-symbols-outlined text-blue-500 mr-3">info</span>
            <div>
                <p class="text-sm text-blue-800 font-medium">Petunjuk Pengisian</p>
                <p class="text-xs text-blue-700 mt-1">Centang kotak pada kompetensi yang <span class="font-bold">wajib</span> dipenuhi oleh jabatan <b>{{ $jabatan->nama_jabatan }}</b>. Klik pada nama kategori untuk menyembunyikan/menampilkan daftar.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('jabatan.kompetensi.update', $jabatan->id) }}" method="POST" id="formMapping">
        @csrf
        
        <div class="space-y-6" id="kompetensiContainer">
            @forelse($kategoriList as $kategori => $kompetensis)
                <div class="kategori-section bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all duration-300">
                    
                    <div class="kategori-header px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors select-none">
                        <div class="p-1.5 bg-primary/10 text-primary rounded-lg">
                            <span class="material-symbols-outlined text-lg">category</span>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white flex-1">{{ $kategori ?: 'Kategori Lainnya' }}</h3>
                        <span class="item-count bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 py-0.5 px-2.5 rounded-full text-xs font-semibold mr-2">
                            {{ count($kompetensis) }} Item
                        </span>
                        <span class="material-symbols-outlined text-slate-400 toggle-icon transition-transform duration-300">expand_more</span>
                    </div>

                    <div class="kategori-body p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($kompetensis as $komp)
                            <label class="kompetensi-card relative flex items-start p-4 cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:border-primary transition-all group has-[:checked]:bg-blue-50 has-[:checked]:border-primary dark:has-[:checked]:bg-blue-900/20" data-nama="{{ strtolower($komp->nama_kompetensi) }}">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="kompetensi[]" value="{{ $komp->id }}" 
                                        {{ in_array($komp->id, $mappedIds) ? 'checked' : '' }}
                                        class="kompetensi-checkbox w-5 h-5 text-primary border-slate-300 rounded focus:ring-primary focus:ring-2 bg-white dark:bg-slate-800 dark:border-slate-600">
                                </div>
                                <div class="ml-3 flex flex-col">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-white group-has-[:checked]:text-primary">{{ $komp->nama_kompetensi }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center p-10 bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2 text-slate-400">inventory_2</span>
                    <p>Belum ada data master kompetensi di sistem.</p>
                </div>
            @endforelse
        </div>

        <div id="actionFooter" class="fixed bottom-0 left-0 right-0 md:left-64 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-[0_-4px_20px_-10px_rgba(0,0,0,0.1)] p-4 px-6 flex justify-between items-center z-40 transform translate-y-full opacity-0 transition-all duration-300">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-amber-500">warning</span>
                <span class="text-sm text-slate-700 dark:text-slate-300 font-medium hidden sm:block">Terdapat perubahan pemetaan yang belum disimpan.</span>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="resetForm()" class="px-5 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2 text-sm font-bold bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors shadow-lg shadow-blue-500/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<div id="kompModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4 hidden flex">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
        
        <form id="formKomp" onsubmit="saveKompetensiBaru(event)">
            @csrf
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Master Kompetensi</h3>
                <button type="button" onclick="closeModalKomp()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Kompetensi <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kompetensi" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Kompetensi Teknis">Kompetensi Teknis</option>
                        <option value="Kompetensi Manajerial">Kompetensi Manajerial</option>
                        <option value="Kultur Sosial">Kultur Sosial</option>                        
                    </select>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                <button type="button" onclick="closeModalKomp()" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btnSubmitKomp" class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm hidden" id="spinnerKomp">progress_activity</span>
                    Simpan Master
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let initialState = "";

    function getCheckedValues() {
        return $('.kompetensi-checkbox:checked').map(function() {
            return this.value;
        }).get().sort().join(',');
    }

    function checkFooterState() {
        let currentState = getCheckedValues();
        let $footer = $('#actionFooter');
        
        if (currentState !== initialState) {
            $footer.removeClass('translate-y-full opacity-0').addClass('translate-y-0 opacity-100');
        } else {
            $footer.removeClass('translate-y-0 opacity-100').addClass('translate-y-full opacity-0');
        }
    }

    $(document).ready(function() {
        initialState = getCheckedValues();

        $(document).on('change', '.kompetensi-checkbox', checkFooterState);

        $('.kategori-header').on('click', function() {
            let $body = $(this).siblings('.kategori-body');
            let $icon = $(this).find('.toggle-icon');
            
            $body.slideToggle(300);
            if ($body.is(':visible')) {
                $icon.css('transform', 'rotate(180deg)'); 
            } else {
                $icon.css('transform', 'rotate(0deg)');   
            }
        });

        $('#searchKompetensi').on('keyup', function() {
            let val = $(this).val().toLowerCase();
            
            $('.kategori-section').each(function() {
                let hasMatch = false;
                
                $(this).find('.kompetensi-card').each(function() {
                    let text = $(this).data('nama');
                    if(text.includes(val)) {
                        $(this).show();
                        hasMatch = true;
                    } else {
                        $(this).hide();
                    }
                });
                
                if(hasMatch) {
                    $(this).show();
                    if(val.length > 0) {
                        $(this).find('.kategori-body').slideDown();
                        $(this).find('.toggle-icon').css('transform', 'rotate(180deg)');
                    }
                } else {
                    $(this).hide();
                }
            });
        });
    });

    function resetForm() {
        window.location.reload(); 
    }

    const modKomp = $('#kompModal');
    const frmKomp = $('#formKomp');

    function openModalKomp() {
        modKomp.removeClass('hidden');
        setTimeout(() => $('input[name="nama_kompetensi"]').focus(), 100); 
    }

    function closeModalKomp() {
        modKomp.addClass('hidden');
        frmKomp[0].reset();
    }

    function saveKompetensiBaru(e) {
        e.preventDefault();
        let formData = frmKomp.serialize();
        let $btn = $('#btnSubmitKomp');
        let $spinner = $('#spinnerKomp');

        $btn.prop('disabled', true).addClass('opacity-70');
        $spinner.removeClass('hidden').addClass('animate-spin');

        $.ajax({
            url: "{{ route('kompetensi.quick_add') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                if(response.success) {
                    closeModalKomp();
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: response.message, showConfirmButton: false, timer: 1500
                    });

                    let komp = response.data;
                    let safeKategori = komp.kategori ? komp.kategori : 'Kategori Lainnya';
                    
                    let htmlCard = `
                        <label class="kompetensi-card relative flex items-start p-4 cursor-pointer rounded-xl border border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 transition-all group has-[:checked]:bg-blue-50 has-[:checked]:border-primary" data-nama="${komp.nama_kompetensi.toLowerCase()}">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="kompetensi[]" value="${komp.id}" class="kompetensi-checkbox w-5 h-5 text-primary border-slate-300 rounded focus:ring-primary focus:ring-2 bg-white dark:bg-slate-800 dark:border-slate-600">
                            </div>
                            <div class="ml-3 flex flex-col">
                                <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-400 group-has-[:checked]:text-primary">${komp.nama_kompetensi} <span class="text-[10px] bg-emerald-200 text-emerald-700 px-1.5 rounded ml-1">Baru</span></span>
                            </div>
                        </label>
                    `;

                    let $kategoriHeader = $('.kategori-header h3').filter(function() {
                        return $(this).text().trim() === safeKategori;
                    });

                    if ($kategoriHeader.length > 0) {
                        let $body = $kategoriHeader.closest('.kategori-section').find('.kategori-body');
                        $body.append(htmlCard);
                        
                        $body.slideDown();
                        $kategoriHeader.closest('.kategori-section').find('.toggle-icon').css('transform', 'rotate(180deg)');
                        
                        let $itemCount = $kategoriHeader.siblings('.item-count');
                        let currentCount = parseInt($itemCount.text());
                        $itemCount.text((currentCount + 1) + ' Item');
                    } else {
                        window.location.reload();
                    }
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('opacity-70');
                $spinner.addClass('hidden').removeClass('animate-spin');
            }
        });
    }
</script>
@endpush