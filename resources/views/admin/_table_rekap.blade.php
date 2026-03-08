<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
            <tr>
                <th class="px-6 py-4 font-semibold w-16 text-center border-b border-slate-200 dark:border-slate-700">No</th>
                <th class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Nama Pegawai</th>
                <th class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Jabatan</th>
                <th class="px-6 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">
                    Kompetensi Dimiliki {{ $tahun === 'semua' ? '(Seluruh Portofolio)' : '(Tahun ' . $tahun . ')' }}
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($pegawai as $index => $item)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 text-center text-slate-500 align-top">{{ $pegawai->firstItem() + $index }}</td>
                    <td class="px-6 py-4 align-top">
                        <h4 class="font-bold text-slate-900 dark:text-white">{{ $item->nama }}</h4>
                        <p class="text-xs text-slate-500">NIP. {{ $item->nip }}</p>
                    </td>
                    <td class="px-6 py-4 align-top">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $item->jabatan->nama_jabatan ?? 'Belum Diatur' }}</span>
                    </td>
                    <td class="px-6 py-4 align-top">
                        @if(count($item->kompetensi_dimiliki) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($item->kompetensi_dimiliki as $komp)
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold shadow-sm">
                                        <span class="material-symbols-outlined text-[10px] mr-1">verified</span>{{ $komp }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">cancel</span> 
                                Belum ada pencapaian {{ $tahun === 'semua' ? 'yang tercatat' : 'di tahun ' . $tahun }}
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">find_in_page</span>
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