@if($pegawai->total() > 10)
<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-between items-center">
    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 font-medium">
        <span>Tampilkan</span>
        <select id="perPage" class="py-1 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white transition-all cursor-pointer">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            <option value="semua" {{ $perPage === 'semua' || $perPage == $pegawai->total() ? 'selected' : '' }}>Semua</option>
        </select>
        <span>data</span>
    </div>
</div>
@endif

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm border-collapse">
        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
            <tr>
                <th rowspan="2" class="px-6 py-4 font-semibold w-16 text-center border-b border-slate-200 dark:border-slate-700 align-middle">No</th>
                <th rowspan="2" class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 align-middle">Nama Pegawai</th>
                <th rowspan="2" class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 align-middle">Jabatan</th>
                <th colspan="3" class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700 bg-slate-100/50 dark:bg-slate-800/50">GAP Kompetensi</th>
            </tr>
            <tr>
                <th class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700 w-64">Kompetensi Total</th>
                <th class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700 w-64">Kompetensi yang Belum Dimiliki</th>
                <th class="px-6 py-3 font-semibold text-center border-b border-slate-200 dark:border-slate-700 w-64">Kompetensi yang Sudah Dimiliki</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
            @forelse($pegawai as $index => $item)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors align-top">
                    <td class="px-6 py-6 text-center text-slate-500 font-medium">{{ $pegawai->firstItem() + $index }}</td>
                    <td class="px-6 py-6">
                        <h4 class="font-bold text-slate-900 dark:text-white leading-tight">{{ $item->nama }}</h4>
                        <p class="text-xs text-slate-500">NIP. {{ $item->nip }}</p>
                    </td>
                    <td class="px-6 py-6">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">
                            {{ $item->jabatan->nama_jabatan ?? 'Belum Diatur' }}
                        </span>
                    </td>
                    
                    <td class="px-6 py-6 text-center">
                        <div class="flex flex-col items-center">
                            <span class="font-black text-xl text-slate-700 dark:text-slate-300 mb-2">
                                {{ $item->kompetensi_total }}
                            </span>
                            <div class="flex flex-wrap justify-center gap-1">
                                @foreach($item->list_standar as $s)
                                    <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded border border-slate-200 dark:border-slate-600 leading-none">
                                        {{ $s->nama_kompetensi }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-6 text-center bg-red-50/20 dark:bg-red-900/5">
                        <div class="flex flex-col items-center">
                            <span class="font-black text-xl {{ $item->kompetensi_gap > 0 ? 'text-red-600' : 'text-emerald-600' }} mb-2">
                                {{ $item->kompetensi_gap }}
                            </span>
                            <div class="flex flex-wrap justify-center gap-1">
                                @forelse($item->list_gap as $g)
                                    <span class="text-[9px] px-1.5 py-0.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded border border-red-200 dark:border-red-900 leading-none">
                                        {{ $g->nama_kompetensi }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">check_circle</span> Lengkap
                                    </span>
                                @endforelse
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-6 text-center bg-emerald-50/20 dark:bg-emerald-900/5">
                        <div class="flex flex-col items-center">
                            <span class="font-black text-xl text-emerald-600 mb-2">
                                {{ $item->list_dimiliki->count() }}
                            </span>
                            <div class="flex flex-wrap justify-center gap-1">
                                @forelse($item->list_dimiliki as $d)
                                    <span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded border border-emerald-200 dark:border-emerald-800 leading-none">
                                        {{ $d->nama_kompetensi }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-slate-400 italic">Belum ada</span>
                                @endforelse
                            </div>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">Data tidak ditemukan.</td>
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