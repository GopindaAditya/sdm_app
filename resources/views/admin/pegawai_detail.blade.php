@extends('layouts.admin')

@section('title', 'Detail Data Pegawai')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col gap-8 pb-12">

    <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6 relative z-10">
            <div class="h-24 w-24 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-4xl shrink-0 border-4 border-white dark:border-slate-800 shadow-md overflow-hidden">
                @if(!empty($pegawai->foto_profil))
                    <img src="{{ asset('storage/' . $pegawai->foto_profil) }}" alt="Foto {{ $pegawai->nama }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($pegawai->nama, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $pegawai->nama }}</h2>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full text-xs font-bold border border-blue-200 dark:border-blue-800">
                        {{ $pegawai->nama_jabatan ?? 'Jabatan Belum Diatur' }}
                    </span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">badge</span> NIP. {{ $pegawai->nip }}
                    <span class="mx-2 text-slate-300">|</span>                    
                </p>
            </div>
            <a href="{{ route('data_pegawai') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 rounded-xl font-medium transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali
            </a>
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500">workspace_premium</span>
                Riwayat Sertifikat & Verifikasi
            </h3>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Program Diklat</th>
                        <th class="px-6 py-4 font-semibold text-center">Tgl Kegiatan</th>
                        <th class="px-6 py-4 font-semibold text-center">Bukti/Sertifikat</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-right w-48">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ $r->status == 'pending' ? 'bg-amber-50/30 dark:bg-amber-900/10' : '' }}">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">{{ $r->nama_pengembangan }}</td>
                            <td class="px-6 py-4 text-center">{{ \Carbon\Carbon::parse($r->tanggal_kegiatan)->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($r->sertifikat)
                                    <a href="{{ asset('storage/sertifikat/' . str_replace('sertifikat/', '', $r->sertifikat)) }}" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline font-medium">
                                        <span class="material-symbols-outlined text-sm">description</span> Lihat File
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada file</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center" id="status-sertifikat-{{ $r->id }}">
                                @if($r->status == 'pending')
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold">Menunggu</span>
                                @elseif($r->status == 'approved')
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold">Disetujui</span>
                                @else
                                    <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold tooltip" >Ditolak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right" id="action-sertifikat-{{ $r->id }}">
                                @if($r->status == 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="verifikasiSertifikat({{ $r->id }}, 'approved')" class="px-3 py-1.5 bg-primary text-white hover:bg-blue-600 rounded-lg text-xs font-bold transition-colors">Setujui</button>
                                        <button onclick="verifikasiSertifikat({{ $r->id }}, 'rejected')" class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded-lg text-xs font-bold transition-colors">Tolak</button>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Telah diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Pegawai belum pernah mengunggah sertifikat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">radar</span>
                    Kompetensi Pegawai
                </h3>                
            </div>
            
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="searchKompPegawai" class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white shadow-sm transition-all" placeholder="Cari kompetensi..."/>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm" id="tableKompetensiPegawai">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-12 text-center">No</th>
                        <th class="px-6 py-4 font-semibold">Standar Kompetensi Jabatan</th>
                        <th class="px-6 py-4 font-semibold">Kategori</th>
                        <th class="px-6 py-4 font-semibold text-center">Status Pemenuhan</th>
                        <th class="px-6 py-4 font-semibold text-right">Tindakan Khusus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($standarKompetensi as $index => $komp)
                        @php
                            $terpenuhi = in_array($komp->id, $kompetensiTerpenuhiIds);
                        @endphp
                        
                        <tr id="row-komp-{{ $komp->id }}" class="row-kompetensi hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ !$terpenuhi ? 'bg-red-50/30 dark:bg-red-900/10' : '' }}">
                            <td class="px-6 py-4 text-center text-slate-500 index-number">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <span class="nama-komp font-medium {{ !$terpenuhi ? 'text-red-700 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ $komp->nama_kompetensi }}
                                </span>
                                <div class="ket-verifikasi-langsung mt-1">
                                    @if(in_array($komp->id, $kompBisaDiklat))
                                        <span class="w-max text-[10px] px-2 py-0.5 bg-blue-50 text-blue-600 rounded border border-blue-200">Via Sertifikat</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ $komp->kategori }}</td>
                            <td class="px-6 py-4 text-center status-badge">
                                @if($terpenuhi)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold">
                                        <span class="material-symbols-outlined text-sm">check_circle</span> Terpenuhi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold">
                                        <span class="material-symbols-outlined text-sm">cancel</span> Belum Terpenuhi
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right action-btn">
                                @if(!$terpenuhi)
                                    <button onclick="bukaModalManual({{ $komp->id }}, '{{ addslashes($komp->nama_kompetensi) }}')" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 hover:border-primary hover:text-primary rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1 ml-auto">
                                        <span class="material-symbols-outlined text-[14px]">how_to_reg</span>
                                        Verifikasi Langsung
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Jabatan ini belum memiliki standar kompetensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalManual" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4 hidden flex">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
        <form id="formManual" onsubmit="simpanManual(event)">
            @csrf
            <input type="hidden" name="id_kompetensi" id="id_komp_manual">
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-blue-50 dark:bg-blue-900/20">
                <h3 class="text-lg font-bold text-blue-800 dark:text-blue-400 flex items-center gap-2">
                    <span class="material-symbols-outlined">how_to_reg</span> Verifikasi Langsung
                </h3>
                <button type="button" onclick="$('#modalManual').addClass('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">Anda akan memverifikasi pemenuhan kompetensi <strong id="nama_komp_display" class="text-slate-900 dark:text-white"></strong> untuk pegawai ini secara langsung.</p>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                <button type="button" onclick="$('#modalManual').addClass('hidden')" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 rounded-xl">Batal</button>
                <button type="submit" id="btnManual" class="px-4 py-2 text-sm font-bold bg-primary text-white rounded-xl hover:bg-blue-600 transition-colors shadow-sm">
                    Tandai Terpenuhi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // REVISI 8: Fitur Live Search untuk Tabel Kompetensi Pegawai
    $('#searchKompPegawai').on('keyup', function() {
        let val = $(this).val().toLowerCase();
        $('.row-kompetensi').each(function() {
            let namaKomp = $(this).find('.nama-komp').text().toLowerCase();
            if(namaKomp.includes(val)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Memperbaiki nomor urut saat filter aktif
        let counter = 1;
        $('.row-kompetensi:visible').each(function() {
            $(this).find('.index-number').text(counter++);
        });
    });

    // FUNGSI 1: VERIFIKASI SERTIFIKAT (Dengan Efek Blur Konsisten)
    function verifikasiSertifikat(id, status) {
        let title = status === 'approved' ? 'Setujui Sertifikat?' : 'Tolak Sertifikat?';
        let textMsg = status === 'approved' ? 'Sertifikat akan disetujui dan kompetensi otomatis diperbarui.' : 'Sertifikat ini akan ditolak secara permanen.';
        let color = status === 'approved' ? '#1773cf' : '#ef4444'; 
        
        Swal.fire({
            title: title, text: textMsg, icon: 'question',
            showCancelButton: true, confirmButtonColor: color,
            confirmButtonText: status === 'approved' ? 'Ya, Setujui' : 'Ya, Tolak',
            cancelButtonText: 'Batal', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                
                // 1. Tangkap elemen baris tabel
                let $rowSertifikat = $(`#status-sertifikat-${id}`).closest('tr');
                let $actionContainer = $(`#action-sertifikat-${id}`);
                
                // 2. Berikan efek BLUR / Opacity dan matikan tombol (KONSISTENSI UI)
                $rowSertifikat.css('opacity', '0.5').css('pointer-events', 'none');
                $actionContainer.find('button').prop('disabled', true);

                $.ajax({
                    url: `{{ url('/data-pegawai/sertifikat') }}/${id}/status`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: status },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false });

                            // --- DOM MAGIC 1: UPDATE TABEL SERTIFIKAT ---
                            // Kembalikan Opacity ke normal
                            $rowSertifikat.css('opacity', '1').css('pointer-events', 'auto');
                            $rowSertifikat.removeClass('bg-amber-50/30 dark:bg-amber-900/10'); 
                            
                            if (status === 'approved') {
                                $(`#status-sertifikat-${id}`).html('<span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold">Disetujui</span>');
                            } else {
                                $(`#status-sertifikat-${id}`).html('<span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold">Ditolak</span>');
                            }
                            $actionContainer.html('<span class="text-xs text-slate-400 italic">Telah diverifikasi</span>');

                            // --- DOM MAGIC 2: UPDATE TABEL KOMPETENSI PEGAWAI ---
                            if (status === 'approved' && response.kompetensi_baru && response.kompetensi_baru.length > 0) {
                                response.kompetensi_baru.forEach(function(idKomp) {
                                    let $rowKomp = $(`#row-komp-${idKomp}`);
                                    if ($rowKomp.length > 0) {
                                        $rowKomp.removeClass('bg-red-50/30 dark:bg-red-900/10');
                                        $rowKomp.find('.nama-komp').removeClass('text-red-700 dark:text-red-400').addClass('text-slate-900 dark:text-white');
                                        $rowKomp.find('.status-badge').html('<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold"><span class="material-symbols-outlined text-sm">check_circle</span> Terpenuhi</span>');
                                        $rowKomp.find('.action-btn').html('<span class="text-xs text-slate-400 italic">Selesai</span>');
                                    }
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        // Jika gagal, kembalikan opacity agar bisa diklik lagi
                        $rowSertifikat.css('opacity', '1').css('pointer-events', 'auto');
                        $actionContainer.find('button').prop('disabled', false);
                        
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    }
    // FUNGSI 2: VERIFIKASI LANGSUNG (Tanpa Refresh Halaman)
    function bukaModalManual(idKomp, namaKomp) {
        $('#id_komp_manual').val(idKomp);
        $('#nama_komp_display').text(namaKomp);
        $('#modalManual').removeClass('hidden');
    }

    function simpanManual(e) {
        e.preventDefault();
        let $btn = $('#btnManual');
        $btn.prop('disabled', true).text('Menyimpan...');

        // REVISI 6: Mengubah DOM setelah berhasil tanpa me-reload halaman
        $.ajax({
            url: `{{ url('/data-pegawai') }}/{{ $pegawai->nip }}/kompetensi-manual`,
            type: 'POST',
            data: $('#formManual').serialize(),
            success: function(response) {
                if(response.success) {
                    $('#modalManual').addClass('hidden');
                    Swal.fire({ icon: 'success', title: 'Terpenuhi!', text: response.message, timer: 1500, showConfirmButton: false });
                    
                    // -- DOM Manipulation Ajaib (Tanpa Refresh) --
                    let idKomp = $('#id_komp_manual').val();
                    let $row = $(`#row-komp-${idKomp}`);
                    
                    // 1. Ubah warna background baris (hapus warna merah)
                    $row.removeClass('bg-red-50/30 dark:bg-red-900/10');
                    // 2. Ubah warna teks nama kompetensi
                    $row.find('.nama-komp').removeClass('text-red-700 dark:text-red-400').addClass('text-slate-900 dark:text-white');
                    // 3. Tambahkan keterangan kecil di bawah namanya
                    $row.find('.ket-verifikasi-langsung').append('<p class="text-[10px] text-primary flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[12px]">how_to_reg</span> Diverifikasi Langsung Admin</p>');
                    // 4. Ubah badge status menjadi hijau
                    $row.find('.status-badge').html('<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold"><span class="material-symbols-outlined text-sm">check_circle</span> Terpenuhi</span>');
                    // 5. Ubah tombol action menjadi teks Selesai
                    $row.find('.action-btn').html('<span class="text-xs text-slate-400 italic">Selesai</span>');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Gagal menyimpan', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Tandai Terpenuhi');
            }
        });
    }
</script>
@endpush