<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
            <tr>
                <th class="px-6 py-4 font-semibold w-16 text-center">No</th>
                <th class="px-6 py-4 font-semibold">Profil Pegawai</th>
                <th class="px-6 py-4 font-semibold">Jabatan</th>
                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($pegawai as $index => $item)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 text-center text-slate-500">{{ $pegawai->firstItem() + $index }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg shrink-0 overflow-hidden ring-1 ring-primary/20">
                                @if(!empty($item->foto_profil))
                                    <img src="{{ asset('storage/' . $item->foto_profil) }}" alt="{{ $item->nama }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($item->nama, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">{{ $item->nama }}</h4>
                                <p class="text-xs text-slate-500 font-medium">NIP. {{ $item->nip }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($item->nama_jabatan)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                {{ $item->nama_jabatan }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 italic">Belum diatur</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('data_pegawai.detail', $item->nip) }}" class="p-2 text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors tooltip" title="Detail & Analisis GAP">
                                <span class="material-symbols-outlined text-xl">visibility</span>
                            </a>
                            
                            <button onclick="openModal('{{ $item->nip }}', '{{ addslashes($item->nama) }}', '{{ $item->id_jabatan }}')" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors tooltip" title="Edit Akun">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </button>
                            
                            <button onclick="deleteData('{{ $item->nip }}')" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors tooltip" title="Hapus Akun">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">group_off</span>
                            <p>Belum ada data pegawai.</p>
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