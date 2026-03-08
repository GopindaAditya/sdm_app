<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
            <tr>
                <th class="px-6 py-4 font-semibold w-16 text-center">No</th>
                <th class="px-6 py-4 font-semibold">Nama Program Pengembangan</th>
                <th class="px-6 py-4 font-semibold text-center">Output Kompetensi</th>
                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($pengembangan as $index => $item)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 text-center text-slate-500">{{ $pengembangan->firstItem() + $index }}</td>
                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">{{ $item->nama_pengembangan }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($item->jumlah_kompetensi > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-bps-green/10 text-bps-green border border-bps-green/20">
                                {{ $item->jumlah_kompetensi }} Kompetensi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-bps-orange/10 text-bps-orange border border-bps-orange/20">
                                Belum Dipetakan
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('pengembangan.kompetensi', $item->id) }}" class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors tooltip" title="Atur Output Kompetensi">
                                <span class="material-symbols-outlined text-xl">account_tree</span>
                            </a>
                            
                            <button onclick="openModal({{ $item->id }}, '{{ addslashes($item->nama_pengembangan) }}')" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors tooltip" title="Edit Nama">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </button>
                            <button onclick="deleteData({{ $item->id }})" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors tooltip" title="Hapus">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">model_training</span>
                            <p>Belum ada data master pengembangan di sistem.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pengembangan->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 pagination-wrapper">
        {{ $pengembangan->links() }}
    </div>
@endif