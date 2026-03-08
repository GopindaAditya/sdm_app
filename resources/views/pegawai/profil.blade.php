@extends('layouts.pegawai')

@section('title', 'Profil Pegawai')

@section('content')
<div class="w-full pb-8">   

    @if(session('success') || session('info') || $errors->any())
    <div id="alert-container" class="mb-2 space-y-4">
        @if(session('success'))
            <div class="alert-notif bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm transition-opacity duration-500">
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-emerald-500 mr-3">check_circle</span>
                    <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('info'))
            <div class="alert-notif bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm transition-opacity duration-500">
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-blue-500 mr-3">info</span>
                    <p class="text-sm text-blue-700 font-medium">{{ session('info') }}</p>
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert-notif bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm transition-opacity duration-500">
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-red-500 mr-3">error</span>
                    <ul class="text-sm text-red-700 font-medium list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
    @endif

    <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        
        <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="flex flex-col items-center">
                    <div class="w-full bg-slate-50 dark:bg-slate-900/50 p-6 border border-slate-100 dark:border-slate-800 rounded-2xl flex flex-col items-center justify-center relative group">
                        
                        <div class="w-32 h-32 rounded-full border-4 border-white dark:border-slate-800 shadow-md overflow-hidden relative mb-4 bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                            @if($pegawai->foto_profil)
                                <img id="previewFoto" src="{{ asset('storage/' . $pegawai->foto_profil) }}" alt="Foto Profil" class="w-full h-full object-cover">
                            @else
                                <img id="previewFoto" src="" alt="Preview" class="w-full h-full object-cover hidden">
                                <span id="iconFoto" class="material-symbols-outlined text-5xl text-slate-400">person</span>
                            @endif
                            
                            <label for="foto" class="absolute inset-0 bg-slate-900/60 hidden group-hover:flex items-center justify-center cursor-pointer transition-all backdrop-blur-sm">
                                <span class="material-symbols-outlined text-white text-3xl">photo_camera</span>
                            </label>
                        </div>
                        
                        <input type="file" name="foto" id="foto" accept="image/jpeg, image/png, image/jpg" class="hidden" onchange="previewImage(event)">
                        
                        <label for="foto" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold shadow-sm cursor-pointer hover:bg-slate-50 transition-colors">
                            Ubah Foto
                        </label>
                        <p class="text-[10px] text-slate-400 mt-3 text-center">Format: JPG, PNG. Maks: 2MB.</p>
                    </div>
                </div>

                <div class="md:col-span-2 flex flex-col gap-6">
                    
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-3 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-2">
                            <span class="material-symbols-outlined text-slate-400 text-[18px]">badge</span> Informasi Dasar
                        </h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nomor Induk Pegawai</label>
                                <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $pegawai->nip }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                                <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $pegawai->nama }}
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-500 mb-1">Jabatan Saat Ini</label>
                                <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $pegawai->jabatan->nama_jabatan ?? 'Belum memiliki jabatan' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-2">
                            <span class="material-symbols-outlined text-slate-400 text-[18px]">lock</span> Keamanan Akun
                        </h4>
                        <p class="text-xs text-slate-500 mb-4 mt-1">Kosongkan kolom di bawah jika Anda tidak ingin mengubah kata sandi.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
                                <input type="password" name="password" id="password" minlength="6"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 placeholder:text-slate-300" 
                                    placeholder="Minimal 6 karakter">
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Konfirmasi Kata Sandi</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" minlength="6"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 placeholder:text-slate-300" 
                                    placeholder="Ulangi kata sandi baru">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-2 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary text-white border border-primary rounded-full text-sm font-bold hover:bg-primary/90 transition-colors shadow-sm flex items-center gap-2">
                            Simpan Perubahan
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('previewFoto');
        const icon = document.getElementById('iconFoto');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if(icon) icon.classList.add('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }


    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert-notif');
            alerts.forEach(function(alert) {
                alert.classList.add('opacity-0');
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 3000);
    });
</script>
@endpush