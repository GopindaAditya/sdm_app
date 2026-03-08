<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <th class="px-6 py-4 font-medium">Nama Pengembangan</th>
                    <th class="px-6 py-4 font-medium">Tanggal Diikuti</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($pengembangan as $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $item->nama_pengembangan }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                            {{ $item->tanggal_kegiatan ? \Carbon\Carbon::parse($item->tanggal_kegiatan)->format('d M Y') : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($item->status_pengembangan == 'approved')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                    <span class="material-symbols-outlined text-[14px]">check</span> Selesai
                                </span>
                            @elseif($item->status_pengembangan == 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                    <span class="material-symbols-outlined text-[14px]">hourglass_empty</span> Menunggu Review
                                </span>
                            @elseif($item->status_pengembangan == 'rejected')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-red-500/10 text-red-600 border border-red-500/20">
                                    <span class="material-symbols-outlined text-[14px]">close</span> Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Belum Unggah
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($item->status_pengembangan == 'approved')
                                <a href="{{ asset('storage/sertifikat/' . $item->sertifikat) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span> Lihat
                                </a>

                            @elseif(in_array($item->status_pengembangan, ['pending', 'rejected']))
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ asset('storage/sertifikat/' . $item->sertifikat) }}" target="_blank" class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Lihat File">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    
                                    <button type="button" 
                                        class="btn-trigger-modal p-2 bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                                        data-id="{{ $item->id }}"
                                        data-nama="{{ $item->nama_pengembangan }}"
                                        data-tanggal="{{ $item->tanggal_kegiatan ? \Carbon\Carbon::parse($item->tanggal_kegiatan)->format('Y-m-d') : '' }}"
                                        title="Edit Data">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>

                                    <button type="button" 
                                        class="btn-delete p-2 bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors" 
                                        data-url="{{ route('pengembangan.hapus', $item->id) }}" 
                                        title="Hapus Sertifikat">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>

                            @else
                                <button type="button" 
                                    class="btn-trigger-modal inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors shadow-sm"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama_pengembangan }}">
                                    <span class="material-symbols-outlined text-[18px]">upload</span> Unggah Sertifikat
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 italic">
                            Belum ada daftar pengembangan kompetensi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($pengembangan->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30 pagination-wrapper">
            {{ $pengembangan->links() }}
        </div>
    @endif
</div>