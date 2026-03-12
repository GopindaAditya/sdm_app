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
                Verifikasi Sertifikat Masuk
            </h3>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Program Diklat / Pengembangan</th>
                        <th class="px-6 py-4 font-semibold text-center">Tgl Kegiatan</th>
                        <th class="px-6 py-4 font-semibold text-center">Bukti</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ $r->status == 'pending' ? 'bg-amber-50/30 dark:bg-amber-900/10' : '' }}">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                {{ $r->pengembangan->nama_pengembangan }}
                            </td>
                            <td class="px-6 py-4 text-center text-slate-500">
                                {{ \Carbon\Carbon::parse($r->tanggal_kegiatan)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($r->sertifikat)
                                    <a href="{{ asset('storage/sertifikat/' . str_replace('sertifikat/', '', $r->sertifikat)) }}" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline font-bold">
                                        <span class="material-symbols-outlined text-sm">visibility</span> Lihat File
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada file</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center" id="status-sertifikat-{{ $r->id }}">
                                @if($r->status == 'pending')
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold">Menunggu Review</span>
                                @elseif($r->status == 'approved')
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold">Disetujui</span>
                                @else
                                    <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right" id="action-sertifikat-{{ $r->id }}">
                                @if($r->status == 'pending')
                                    <button onclick="bukaModalReview({{ $r->id }}, '{{ addslashes($r->pengembangan->nama_pengembangan) }}')" class="px-4 py-2 bg-primary text-white hover:bg-blue-600 rounded-xl text-xs font-bold transition-all shadow-sm">
                                        Review & Verifikasi
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">Pegawai belum memiliki riwayat sertifikat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">radar</span>
                Status Kompetensi Jabatan
            </h3> 
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="searchKompPegawai" class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-white" placeholder="Cari kompetensi..."/>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm" id="tableKompetensiPegawai">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-12 text-center">No</th>
                        <th class="px-6 py-4 font-semibold">Nama Kompetensi</th>
                        <th class="px-6 py-4 font-semibold">Kategori</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($standarKompetensi as $index => $komp)
                        @php 
                            $terpenuhi = array_key_exists($komp->id, $kompetensiTerpenuhi);
                        @endphp
                        <tr id="row-komp-{{ $komp->id }}" class="row-kompetensi hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ !$terpenuhi ? 'bg-red-50/5 dark:bg-red-900/5' : '' }}">
                            <td class="px-6 py-4 text-center text-slate-500 index-number">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <span class="nama-komp font-medium {{ !$terpenuhi ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ $komp->nama_kompetensi }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ $komp->kategori }}</td>
                            <td class="px-6 py-4 text-center status-badge">
                                @if($terpenuhi)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-bold text-xs">
                                        <span class="material-symbols-outlined text-sm">verified</span> Terpenuhi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-red-500 font-bold text-xs italic">
                                        <span class="material-symbols-outlined text-sm">pending</span> Belum Ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Jabatan belum memiliki standar kompetensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalReviewSertifikat" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]">
        
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Review Sertifikat Pegawai</h3>
                <p id="review_nama_pengembangan" class="text-xs text-primary font-medium mt-0.5"></p>
            </div>
            <button type="button" onclick="tutupModalReview()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto grow space-y-6">
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-white mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">fact_check</span>
                    Kompetensi yang Akan Diperbarui:
                </label>
                <div id="loading-review" class="py-10 text-center hidden">
                    <span class="material-symbols-outlined animate-spin text-primary">autorenew</span>
                    <p class="text-xs text-slate-500 mt-2 font-medium">Memuat data...</p>
                </div>
                <div id="container-kompetensi-review" class="space-y-2">
                    </div>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/10 p-4 rounded-2xl border border-amber-200 dark:border-amber-800">
                <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
                    <strong>Catatan Admin:</strong> Anda dapat menambah atau mengurangi daftar kompetensi di atas sesuai dengan isi sertifikat yang diunggah pegawai sebelum melakukan persetujuan.
                </p>
            </div>
        </div>
        
        <div class="px-6 py-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap gap-3 justify-between items-center">
            <button type="button" onclick="prosesVerifikasiFinal('rejected')" class="px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all border border-red-200 dark:border-red-900">
                Tolak Sertifikat
            </button>
            <div class="flex gap-2">
                <button type="button" onclick="tutupModalReview()" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                <button type="button" onclick="prosesVerifikasiFinal('approved')" class="px-6 py-2 text-sm font-bold bg-primary text-white hover:bg-blue-600 rounded-xl transition-all shadow-md shadow-primary/20">
                    Setujui & Update
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentRiwayatId = null;
    
    $('#searchKompPegawai').on('keyup', function() {
        let val = $(this).val().toLowerCase();
        $('.row-kompetensi').each(function() {
            let namaKomp = $(this).find('.nama-komp').text().toLowerCase();
            $(this).toggle(namaKomp.includes(val));
        });
        
        let counter = 1;
        $('.row-kompetensi:visible').each(function() {
            $(this).find('.index-number').text(counter++);
        });
    });

    function bukaModalReview(id, namaPengembangan) {
        currentRiwayatId = id;
        $('#review_nama_pengembangan').text(namaPengembangan);
        $('#container-kompetensi-review').empty();
        $('#loading-review').removeClass('hidden');
        $('#modalReviewSertifikat').removeClass('hidden').addClass('flex');

        $.ajax({
            url: `{{ url('data-pegawai/riwayat-sertifikat') }}/${id}/detail-review`,
            type: 'GET',
            success: function(response) {
                $('#loading-review').addClass('hidden');
                if (response.data.length > 0) {
                    let html = '';
                    let selectedIds = response.selected_ids || [];
                    
                    let groupedData = response.data.reduce((acc, obj) => {
                        let key = obj.kategori || 'Lainnya';
                        if (!acc[key]) acc[key] = [];
                        acc[key].push(obj);
                        return acc;
                    }, {});

                    for (let kategori in groupedData) {
                        html += `<div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 mt-4 first:mt-0">${kategori}</div>`;
                        
                        groupedData[kategori].sort((a,b) => (selectedIds.includes(a.id) === selectedIds.includes(b.id)) ? 0 : selectedIds.includes(a.id) ? -1 : 1)
                        .forEach(function(komp) {
                            let isOwned = komp.is_owned;
                            let isSelectedByPegawai = selectedIds.includes(komp.id);
                            let isChecked = (isSelectedByPegawai || isOwned) ? 'checked' : '';
                            let isDisabled = isOwned ? 'disabled' : '';
                            
                            let labelHtml = '';
                            if (isOwned) {
                                labelHtml = `<span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1 mt-1">
                                                <span class="material-symbols-outlined text-[12px]">check_circle</span> Sudah Dimiliki (Terkunci)
                                            </span>`;
                            } else if (komp.is_default) {
                                labelHtml = `<span class="text-[10px] text-primary font-bold flex items-center gap-1 mt-1">
                                                <span class="material-symbols-outlined text-[12px]">verified</span> Rekomendasi Sertifikat
                                            </span>`;
                            } else if (isSelectedByPegawai) {
                                labelHtml = `<span class="text-[10px] text-blue-600 font-bold flex items-center gap-1 mt-1">
                                                <span class="material-symbols-outlined text-[12px]">person</span> Dipilih Pegawai
                                            </span>`;
                            }

                            let bgClass = isOwned ? 'opacity-60 bg-slate-50 border-slate-200' : 'bg-white hover:border-primary/50 cursor-pointer shadow-sm';

                            html += `
                            <label class="flex items-start gap-3 p-3 border rounded-2xl transition-all group ${bgClass}">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input type="checkbox" name="komp_review[]" value="${komp.id}" ${isChecked} ${isDisabled} class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary">
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">${komp.nama_kompetensi}</span>
                                    ${labelHtml}
                                </div>
                            </label>`;
                        });
                    }
                    $('#container-kompetensi-review').html(html);
                } else {
                    $('#container-kompetensi-review').html('<p class="text-xs text-center text-slate-500 py-4 italic">Tidak ada standar kompetensi jabatan.</p>');
                }
            },
            error: function() {
                tutupModalReview();
                Swal.fire('Error', 'Gagal memuat detail sertifikat.', 'error');
            }
        });
    }

    function tutupModalReview() {
        $('#modalReviewSertifikat').addClass('hidden').removeClass('flex');
        currentRiwayatId = null;
    }

    function prosesVerifikasiFinal(status) {
        let selectedKomp = [];
        
        $('input[name="komp_review[]"]:checked:not(:disabled)').each(function() {
            selectedKomp.push($(this).val());
        });

        if (status === 'approved' && selectedKomp.length === 0) {
            Swal.fire('Peringatan', 'Minimal pilih satu kompetensi (selain yang sudah dimiliki) jika ingin menyetujui.', 'warning');
            return;
        }

        Swal.fire({
            title: status === 'approved' ? 'Setujui Sertifikat?' : 'Tolak Sertifikat?',
            text: "Pastikan data sudah sesuai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status === 'approved' ? '#1773cf' : '#ef4444',
            confirmButtonText: 'Ya, Proses!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/data-pegawai/sertifikat') }}/${currentRiwayatId}/status`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { 
                        status: status,
                        kompetensi_admin: selectedKomp
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false });
                            location.reload(); 
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal memproses verifikasi.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush