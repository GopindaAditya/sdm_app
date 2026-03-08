<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - HRIS BPS Provinsi Bali</title>
    
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

<body class="bg-slate-50 dark:bg-slate-900 font-sans antialiased min-h-screen flex items-center justify-center relative overflow-hidden selection:bg-primary selection:text-white">

    <div class="absolute top-0 left-0 w-full h-96 bg-primary/5 dark:bg-primary/10 -skew-y-6 transform origin-top-left -z-10"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500/5 dark:bg-blue-500/10 rounded-full blur-3xl -z-10"></div>

    <div class="w-full max-w-md px-6 py-12 z-10">
        
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-center mx-auto mb-4 text-primary">
                <span class="material-symbols-outlined text-4xl">admin_panel_settings</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Portal SDM & Kompetensi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium">BPS Provinsi Bali</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] border border-slate-100 dark:border-slate-700">
            
            <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Masuk ke Akun Anda</h2>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-xl">
                    <div class="flex items-center gap-2 text-red-700 dark:text-red-400">
                        <span class="material-symbols-outlined text-sm">error</span>
                        <p class="text-sm font-semibold">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="username_or_nip" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">NIP Pegawai</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">badge</span>
                        <input type="text" name="username_or_nip" id="username_or_nip" value="{{ old('username_or_nip') }}" required autofocus
                            class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all" 
                            placeholder="Masukkan NIP Anda">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Kata Sandi</label>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">lock</span>
                        <input type="password" name="password" id="password" required
                            class="w-full pl-11 pr-12 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-slate-900 dark:text-white transition-all" 
                            placeholder="••••••••">
                        
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]" id="eye-icon">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium group-hover:text-slate-900 transition-colors">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-primary text-white rounded-xl text-sm font-bold hover:bg-blue-600 focus:ring-4 focus:ring-primary/30 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 mt-4">
                    Masuk Sekarang
                    <span class="material-symbols-outlined text-[18px]">login</span>
                </button>
            </form>
            
        </div>

        <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-8 font-medium">
            &copy; {{ date('Y') }} Badan Pusat Statistik Provinsi Bali.<br>Hak Cipta Dilindungi.
        </p>

    </div>

    <script>
        // Fitur sederhana untuk memperlihatkan/menyembunyikan password
        function togglePassword() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.textContent = 'visibility';
            } else {
                pwdInput.type = 'password';
                eyeIcon.textContent = 'visibility_off';
            }
        }
    </script>
</body>
</html>