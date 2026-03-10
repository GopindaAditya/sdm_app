@if ($kompetensi->total() > 10)
<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-between items-center">
    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 font-medium">
        <span>Tampilkan</span>
        <select id="perPage" class="py-1 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white transition-all cursor-pointer">
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
        Total <span class="font-bold text-slate-700 dark:text-slate-300">{{ $kompetensi->total() }}</span> kompetensi
    </div> --}}
</div>
@endif

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
            <tr>
                <th class="px-6 py-4 font-semibold w-16 text-center">No</th>
                <th class="px-6 py-4 font-semibold">Nama Kompetensi</th>
                <th class="px-6 py-4 font-semibold">Kategori</th>
                <th class="px-6 py-4 font-semibold text-right w-24">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($kompetensi as $index => $item)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 text-center text-slate-500">{{ $kompetensi->firstItem() + $index }}</td>
                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">{{ $item->nama_kompetensi }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                            {{ $item->kategori }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="openModal({{ $item->id }}, '{{ addslashes($item->nama_kompetensi) }}', '{{ $item->kategori }}')" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors tooltip" title="Edit">
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
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">psychology</span>
                            <p>Belum ada data kompetensi yang sesuai.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($kompetensi->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 pagination-wrapper">
        {{ $kompetensi->links() }}
    </div>
@endif