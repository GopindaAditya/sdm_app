<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'SDM Dashboard') - Aplikasi SDM</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#0ea5e9", // Blue
                        secondary: "#84cc16", // Green
                        accent: "#f97316", // Orange
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e293b",
                        "text-light": "#334155",
                        "text-dark": "#cbd5e1",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                    },
                },
            },
        };
    </script>
    
    <style type="text/tailwindcss">
        body {
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark min-h-screen flex relative">

    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out w-64 bg-surface-light dark:bg-surface-dark border-r border-slate-200 dark:border-slate-700 flex flex-col h-screen shrink-0">
        
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-700">
            <a href="{{ route('profil') }}" class="text-xl font-bold text-primary flex items-center gap-3">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Perusahaan" class="h-8 w-auto">
                SDM 
            </a>
            
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px]">bolt</span>
                Dashboard
            </a>
            <a href="{{ route('profil') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('profil') ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px]">person</span>
                Profil
            </a>
            <a href="{{ route('kompetensi') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('kompetensi') ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px]">verified</span>
                Kompetensi
            </a>
            <a href="{{ route('pengembangan') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('pengembangan') ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[22px]">trending_up</span>
                Pengembangan
            </a>
        </nav>
        
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            <form action="/logout" method="POST" class="m-0">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors">
                    <span class="material-symbols-outlined text-[22px]">logout</span>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <header class="h-16 bg-surface-light dark:bg-surface-dark border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-8 shrink-0">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 -ml-2 rounded hover:bg-slate-100 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">@yield('title', 'Dashboard')</h1>
            </div>
            
            <div class="flex items-center gap-6">                        
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                    <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center ring-2 ring-slate-100 dark:ring-slate-800">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                    <div class="hidden lg:block">
                        <p class="text-sm font-semibold text-slate-800 dark:text-white leading-tight">{{ Auth::user()->nama ?? 'Pegawai' }}</p>
                        <p class="text-[11px] text-slate-500 uppercase tracking-wider">{{ Auth::user()->jabatan->nama_jabatan ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900/50 p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </div>
        
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const $sidebar = $('#sidebar');
            const $overlay = $('#mobile-overlay');
            
            $('#mobile-menu-btn').on('click', function() {
                $sidebar.removeClass('-translate-x-full'); 
                $overlay.removeClass('hidden'); 
                $('body').addClass('overflow-hidden md:overflow-auto'); 
            });

            function closeSidebar() {
                $sidebar.addClass('-translate-x-full'); 
                $overlay.addClass('hidden'); 
                $('body').removeClass('overflow-hidden md:overflow-auto'); 
            }

            $('#close-sidebar-btn').on('click', closeSidebar);
            
            $overlay.on('click', closeSidebar);
        });
    </script>
    
    @stack('scripts')
</body>
</html>