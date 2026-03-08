<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'HR Admin Dashboard') - BPS Management</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1773cf",
                        "bps-green": "#078838",
                        "bps-orange": "#e73908",
                        "background-light": "#f6f7f8",
                        "background-dark": "#111921",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e293b",
                        "border-light": "#e2e8f0",
                        "border-dark": "#334155"
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px"},
                },
            },
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display min-h-screen">
    
    <div class="flex h-screen overflow-hidden">
        
        <aside class="w-64 bg-surface-light dark:bg-surface-dark border-r border-border-light dark:border-border-dark flex flex-col hidden md:flex shrink-0">
            <div class="h-16 px-6 flex items-center gap-3 border-b border-border-light dark:border-border-dark">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo BPS" class="h-8 w-auto">
                
                <div class="flex flex-col">
                    <h1 class="text-base font-bold leading-tight text-primary">HR Admin</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider">BPS Management</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                
                <div class="pt-2 pb-1">
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Menu Utama</p>
                </div>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('dashboard') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('dashboard') }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-sm">Dashboard</span>
                </a>
                
                <div class="pt-5 pb-1">
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Manajemen Data</p>
                </div>

                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('pegawai*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('data_pegawai') }}">
                    <span class="material-symbols-outlined">group</span>
                    <span class="text-sm">Data Pegawai</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('jabatan*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('jabatan') }}">
                    <span class="material-symbols-outlined">work</span>
                    <span class="text-sm">Jabatan</span>
                </a>

                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('kompetensi*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('kompetensi') }}">
                    <span class="material-symbols-outlined">psychology</span>
                    <span class="text-sm">Kompetensi</span>
                </a>

                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('pengembangan*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('pengembangan') }}">
                    <span class="material-symbols-outlined">model_training</span>
                    <span class="text-sm">Pengembangan</span>
                </a>

                <div class="pt-5 pb-1">
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Laporan & Rekap</p>
                </div>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('rekap-kompetensi*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('rekap_kompetensi') }}">
                    <span class="material-symbols-outlined">book</span>
                    <span class="text-sm">Rekap Kompetensi</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('rekap-gap*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('rekap_gap') }}">
                    <span class="material-symbols-outlined">monitoring</span>
                    <span class="text-sm">Rekap GAP</span>
                </a>
                
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('analisis-diklat*') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}" href="{{ route('analisis_diklat') }}">
                    <span class="material-symbols-outlined">school</span>
                    <span class="text-sm">Analisis Diklat</span>
                </a>
            </nav>

            <div class="p-4 border-t border-border-light dark:border-border-dark">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="text-sm">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-screen overflow-hidden">
{{--             
            <header class="h-16 bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4">
                    <button class="md:hidden text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h2 class="text-xl font-bold">@yield('title', 'Dashboard')</h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ Auth::user()->nama ?? 'Administrator' }}</span>
                        <span class="text-xs text-primary font-medium">BPS Admin</span>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shadow-sm">
                        <span class="material-symbols-outlined">shield_person</span>
                    </div>
                </div>
            </header> --}}

            <div class="flex-1 overflow-y-auto p-6 bg-background-light dark:bg-background-dark">
                @yield('content')
            </div>
            
        </main>
    </div>

    @stack('scripts')
</body>
</html>