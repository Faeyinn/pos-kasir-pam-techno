<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pam Techno POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>


</head>
<body class="bg-slate-50 flex flex-col items-center justify-center min-h-screen p-4 md:p-8">

    <div class="fixed top-0 left-0 w-full h-full -z-10 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[60%] h-[60%] bg-blue-200/40 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[60%] h-[60%] bg-indigo-200/40 rounded-full blur-[100px] animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-md mx-auto">
        <div class="glass-effect bg-white/90 border border-white/20 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] p-8 sm:p-10 transition-all duration-300">

            <div class="flex flex-col items-center mb-8">
                <div class="mb-6 flex items-center justify-center w-full h-32">
                    <img src="{{ asset('assets/logo.png') }}" alt="Pam Techno Logo" class="h-full object-contain filter drop-shadow-md">
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Selamat Datang</h2>
                <p class="text-gray-500 mt-2 text-center text-sm sm:text-base">Masuk ke sistem kasir Pam Techno</p>
            </div>

            <form 
                action="{{ route('login.post') }}" 
                method="POST" 
                class="space-y-5"
                x-data="{ showPassword: false }"
            >
                @csrf

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Email</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="pamtechno@gmail.com" 
                            class="w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium @error('email') border-red-500 @enderror text-sm sm:text-base" 
                            required autofocus>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 ml-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-semibold text-gray-700 ml-1">Password</label>
                    </div>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            placeholder="••••••••" 
                            class="w-full pl-11 pr-14 py-4 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium text-sm sm:text-base" 
                            required
                        >
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-blue-500 transition-colors"
                        >
                            <i x-show="!showPassword" data-lucide="eye" class="w-5 h-5"></i>
                            <i x-show="showPassword" data-lucide="eye-off" class="w-5 h-5" x-cloak></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between px-1 py-1">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500/20 transition-all cursor-pointer">
                        <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-[0_10px_20px_rgba(37,99,235,0.2)] hover:shadow-[0_15px_25px_rgba(37,99,235,0.3)] transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 group">
                    <span>Masuk ke Kasir</span>
                    <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>

        <p class="text-center mt-8 text-gray-500 text-sm">
            Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar sekarang</a>
        </p>
    </div>


</body>
</html>
