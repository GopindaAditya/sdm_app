<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    
    @if($kompetensi->total() > 10)
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 font-medium">
            <span>Tampilkan</span>
            <select id="perPage" class="py-1 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white transition-all cursor-pointer">
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                <option value="semua" {{ $perPage === 'semua' || $perPage == $kompetensi->total() ? 'selected' : '' }}>Semua</option>
            </select>
            <span>data</span>
        </div>
        
        {{-- <div class="text-sm text-slate-500 dark:text-slate-400">
            Total <span class="font-bold text-slate-700 dark:text-slate-300">{{ $kompetensi->total() }}</span> standar
        </div> --}}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Kompetensi</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status Pemenuhan</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($kompetensi as $index => $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            {{ ($kompetensi->currentPage() - 1) * $kompetensi->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                            {{ $item->nama_kompetensi }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->kategori == 'Kompetensi Teknis')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary uppercase tracking-wider">
                                    Teknis
                                </span>
                            @elseif($item->kategori == 'Kompetensi Manajerial')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-orange-500/10 text-orange-600 uppercase tracking-wider">
                                    Manajerial
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    Kultur Sosial
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->status_dimiliki == 'Terpenuhi')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                    <span class="material-symbols-outlined text-[14px]">check</span> Terpenuhi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                    <span class="material-symbols-outlined text-[14px]">warning</span> Belum Terpenuhi
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 whitespace-nowrap text-sm text-slate-500 text-center italic">
                            Belum ada data kompetensi yang ditetapkan untuk jabatan Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($kompetensi->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 pagination-wrapper">
            {{ $kompetensi->links() }}
        </div>
    @endif
</div>