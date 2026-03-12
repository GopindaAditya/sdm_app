@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col gap-6">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard Admin</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ringkasan data SDM dan Kompetensi BPS Provinsi Bali.</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 shadow-sm">
            <span class="material-symbols-outlined text-[18px] text-primary">calendar_today</span>
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">groups</span>
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Total Pegawai</p>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $totalPegawai }} <span class="text-xs font-normal text-slate-400">Orang</span></h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">work</span>
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Standar Jabatan</p>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $totalJabatan }} <span class="text-xs font-normal text-slate-400">Posisi</span></h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">verified</span>
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Sertifikat Disetujui</p>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $sertifikatApproved }} <span class="text-xs font-normal text-slate-400">Berkas</span></h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4 relative overflow-hidden">
            @if($sertifikatPending > 0)
                <div class="absolute top-0 right-0 w-1.5 h-full bg-amber-500"></div>
            @endif
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Butuh Verifikasi</p>
                <h3 class="text-xl font-bold text-amber-600 dark:text-amber-500">{{ $sertifikatPending }} <span class="text-xs font-normal text-amber-600/70">Tertunda</span></h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
            <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">bar_chart</span>
                Distribusi Jabatan (Top 5)
            </h3>
            
            @if(count($dataJabatan) > 0)
                <div class="relative flex-1 w-full min-h-[250px]">
                    <canvas id="jabatanChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">analytics</span>
                    <p class="text-sm">Belum ada data jabatan</p>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">notifications_active</span>
                    Antrean Verifikasi Terbaru
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 border-y border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Pegawai</th>
                            <th class="px-4 py-3 font-semibold">Diklat / Sertifikat</th>
                            <th class="px-4 py-3 font-semibold text-center">Tanggal Kirim</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($recentPending as $rp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $rp->pegawai->nama }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300 truncate max-w-[200px]" title="{{ $rp->pengembangan->nama_pengembangan }}">{{ $rp->pengembangan->nama_pengembangan }}</td>
                                <td class="px-4 py-3 text-center text-slate-500 text-xs">{{ \Carbon\Carbon::parse($rp->created_at)->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('data_pegawai.detail', $rp->nip) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-primary hover:text-white rounded-lg text-xs font-bold transition-colors">
                                        Cek <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-2xl">done_all</span>
                                        </div>
                                        <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada antrean verifikasi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($sertifikatPending > 5)
                <div class="mt-4 text-center border-t border-slate-100 pt-4">
                    <a href="{{ route('data_pegawai') }}" class="text-sm text-primary hover:underline font-medium">Lihat Semua Pegawai</a>
                </div>
            @endif
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    @if(count($dataJabatan) > 0)
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('jabatanChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labelsJabatan) !!},
                    datasets: [{
                        label: 'Jumlah Pegawai',
                        data: {!! json_encode($dataJabatan) !!},
                        backgroundColor: '#3b82f6',  
                        borderRadius: 6,
                        barThickness: 24
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 10,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: '#f1f5f9', drawBorder: false }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                callback: function(value) {
                                    let label = this.getLabelForValue(value);
                                    return label.length > 10 ? label.substr(0, 10) + '...' : label;
                                }
                            }
                        }
                    }
                }
            });
        });
    @endif
</script>
@endpush