<div class="overflow-x-auto">
    <table class="w-full text-left text-sm border-collapse">
        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
            <tr>
                <th rowspan="2" class="px-6 py-4 font-semibold w-16 text-center border-b border-slate-200 dark:border-slate-700 align-middle">No</th>
                <th rowspan="2" class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 align-middle">Nama Pegawai</th>
                <th rowspan="2" class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 align-middle">Jabatan</th>
                <th colspan="2" class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700">GAP Kompetensi</th>
            </tr>
            <tr>
                <th class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700 w-40">Kompetensi Total</th>
                <th class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700 w-56">Kompetensi Sudah Dimiliki</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($pegawai as $index => $item)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 text-center text-slate-500">{{ $pegawai->firstItem() + $index }}</td>
                    <td class="px-6 py-4">
                        <h4 class="font-bold text-slate-900 dark:text-white">{{ $item->nama }}</h4>
                        <p class="text-xs text-slate-500">NIP. {{ $item->nip }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $item->jabatan->nama_jabatan ?? 'Belum Diatur' }}</span>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <span class="font-bold text-lg text-slate-700 dark:text-slate-300">
                            {{ $item->kompetensi_total }}
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center justify-center gap-1">
                            <span class="font-bold text-lg text-slate-900 dark:text-white">
                                {{ $item->kompetensi_dimiliki }}
                            </span>
                            
                            @if($item->kompetensi_total > 0)
                                @php 
                                    $gap = $item->kompetensi_total - $item->kompetensi_dimiliki; 
                                @endphp
                                
                                @if($gap > 0)
                                    <span class="px-2 py-0.5 bg-red-50 text-red-600 border border-red-200 rounded text-[10px] font-bold tracking-wide">
                                        GAP: {{ $gap }} Kompetensi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded text-[10px] font-bold tracking-wide">
                                        Memenuhi Standar
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded text-[10px] font-semibold tracking-wide">
                                    Belum Ada Standar
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">data_exploration</span>
                            <p>Data pegawai tidak ditemukan.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pegawai->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 pagination-wrapper">
        {{ $pegawai->links() }}
    </div>
@endif