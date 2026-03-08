@extends('layouts.pegawai')

@section('title', 'Profil Pegawai')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
    <div class="bg-primary px-6 py-4">
        <h3 class="text-lg font-medium text-white">Informasi Data Diri</h3>
        <p class="text-sm text-blue-100 mt-1">Preview detail informasi kepegawaian Anda.</p>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col items-center justify-center bg-gray-50 p-4 border border-gray-200 rounded-lg">
                <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center text-gray-500 mb-3">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <span class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Foto Profil</span>
            </div>

            <div class="md:col-span-2 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">NIP</label>
                    <div class="mt-1 text-lg font-semibold text-gray-900">{{ $pegawai->nip }}</div>
                </div>
                
                <hr class="border-gray-100">

                <div>
                    <label class="block text-sm font-medium text-gray-500">Nama Lengkap</label>
                    <div class="mt-1 text-lg font-semibold text-gray-900">{{ $pegawai->nama }}</div>
                </div>

                <hr class="border-gray-100">

                <div>
                    <label class="block text-sm font-medium text-gray-500">Jabatan Saat Ini</label>
                    <div class="mt-1 text-lg font-semibold text-gray-900">
                        @if($pegawai->jabatan)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $pegawai->jabatan->nama_jabatan }}
                            </span>
                        @else
                            <span class="text-gray-400 italic">Belum memiliki jabatan</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection