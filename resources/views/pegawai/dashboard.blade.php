@extends('layouts.pegawai')

@section('title', 'Dashboard Overview')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $sapaan }}, {{ explode(' ', $pegawai->nama)[0] }}! 👋</h2>
        <p class="text-slate-600 dark:text-slate-400 mt-1">Berikut adalah ringkasan pengembangan kompetensi Anda saat ini.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('kompetensi') }}" class="flex items-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
            <span class="material-symbols-outlined text-lg mr-2">verified</span>
            Lihat Kompetensi
        </a>
        <a href="{{ route('pengembangan') }}" class="flex items-center px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition shadow-lg shadow-blue-500/30">
            <span class="material-symbols-outlined text-lg mr-2">upload</span>
            Unggah Sertifikat
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
        <div class="absolute right-0 top-0 h-full w-1.5 bg-secondary rounded-l"></div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Sertifikat Disetujui</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $riwayatSelesai }}</h3>
            </div>
            <div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-xl text-secondary">
                <span class="material-symbols-outlined text-2xl">verified</span>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-slate-500 dark:text-slate-400 text-xs">Target: <span class="font-medium text-slate-900 dark:text-white">{{ $totalTargetPengembangan }} Sertifikat</span></span>
            <div class="ml-auto w-20 bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                <div class="bg-secondary h-2 rounded-full" style="width: {{ $totalTargetPengembangan > 0 ? ($riwayatSelesai/$totalTargetPengembangan)*100 : 0 }}%"></div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
        <div class="absolute right-0 top-0 h-full w-1.5 bg-accent rounded-l"></div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Menunggu Review</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $riwayatPending }}</h3>
            </div>
            <div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 rounded-xl text-accent">
                <span class="material-symbols-outlined text-2xl">hourglass_empty</span>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="bg-orange-100 text-accent dark:bg-orange-900/30 dark:text-orange-400 px-2 py-0.5 rounded-md flex items-center font-semibold text-xs">
                Sedang diproses Admin
            </span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
        <div class="absolute right-0 top-0 h-full w-1.5 bg-red-500 rounded-l"></div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum Diikuti</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-3">{{ $riwayatBelum }}</h3>
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

<div class="mb-8 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 relative overflow-hidden">  
    <div class="flex items-center justify-between mb-6 relative z-10">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pengembangan Wajib yang Belum Terpenuhi</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Segera ikuti pelatihan di bawah ini untuk memenuhi standar kompetensi jabatan Anda.</p>
        </div>
        <a href="{{ route('pengembangan') }}" class="hidden sm:flex items-center text-sm font-semibold text-primary hover:text-blue-700 bg-blue-50 dark:bg-blue-900/30 px-4 py-2 rounded-lg transition-colors">
            Lihat Semua <span class="material-symbols-outlined text-sm ml-1">arrow_forward</span>
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 relative z-10">
        @forelse($pengembanganWajib as $wajib)
            <div class="flex items-start p-4 bg-slate-50 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary/50 dark:hover:border-primary/50 transition-colors cursor-pointer group">
                <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/50 text-primary flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white group-hover:shadow-md transition-all">
                    <span class="material-symbols-outlined">menu_book</span>
                </div>
                <div class="ml-4 flex-1">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-tight mb-1">{{ $wajib->nama_pengembangan }}</h4>
                    <span class="text-[10px] font-bold text-red-600 bg-red-100 dark:bg-red-900/50 px-2 py-0.5 rounded-md inline-flex items-center mt-1">
                        <span class="material-symbols-outlined text-[10px] mr-1">warning</span> BELUM DIIKUTI
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 flex flex-col items-center justify-center py-10 text-slate-400 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                <span class="material-symbols-outlined text-5xl mb-3 text-emerald-500">task_alt</span>
                <p class="text-base font-medium text-slate-700 dark:text-slate-300">Hebat! Tidak ada tanggungan.</p>
                <p class="text-sm text-center mt-1">Anda telah menyelesaikan semua pengembangan wajib yang disyaratkan.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/20">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Riwayat Pengembangan Terakhir</h3>
        <a href="{{ route('pengembangan') }}" class="text-sm text-primary hover:text-blue-700 dark:hover:text-blue-400 font-medium flex items-center">
            Lihat Detail <span class="material-symbols-outlined text-sm ml-1">arrow_forward</span>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-900 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 tracking-wider border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-6 py-4">Nama Pengembangan</th>
                    <th class="px-6 py-4">Tanggal Kegiatan</th>
                    <th class="px-6 py-4">Terakhir Diperbarui</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($riwayatTerbaru as $riwayat)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                            {{ $riwayat->nama_pengembangan }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $riwayat->tanggal_kegiatan ? \Carbon\Carbon::parse($riwayat->tanggal_kegiatan)->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ \Carbon\Carbon::parse($riwayat->updated_at)->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4">
                            @if($riwayat->status == 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Disetujui</span>
                            @elseif($riwayat->status == 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Menunggu</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">
                            Belum ada riwayat pengembangan yang dicatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection