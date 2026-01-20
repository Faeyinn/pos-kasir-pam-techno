<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Pam Techno POS</title>
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
                <div class="mb-6 flex items-center justify-center w-full h-24">
                   <!-- Keep logo consistent -->
                    <img src="{{ asset('assets/logo.png') }}" alt="Pam Techno Logo" class="h-full object-contain filter drop-shadow-md">
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Daftar Akun Baru</h2>
                <p class="text-gray-500 mt-2 text-center text-sm sm:text-base">Mulai menggunakan Pam Techno POS</p>
            </div>

            <form 
                action="{{ route('register.post') }}" 
                method="POST" 
                class="space-y-4"
                x-data="{ showPassword: false, showConfirmPassword: false }"
            >
                @csrf

                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Nama Lengkap</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </span>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Pam Techno" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium @error('name') border-red-500 @enderror text-sm" 
                            required autofocus>
                    </div>
                     @error('name')
                        <p class="text-red-500 text-xs mt-1 ml-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Username</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="at-sign" class="w-5 h-5"></i>
                        </span>
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="pamtechno" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium @error('username') border-red-500 @enderror text-sm" 
                            required>
                    </div>
                    @error('username')
                        <p class="text-red-500 text-xs mt-1 ml-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Email</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="pamtechno@gmail.com" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium @error('email') border-red-500 @enderror text-sm" 
                            required>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 ml-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Password</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            placeholder="••••••••" 
                            class="w-full pl-11 pr-14 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium @error('password') border-red-500 @enderror text-sm" 
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
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 ml-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                 <div class="space-y-1">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Konfirmasi Password</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i data-lucide="lock-check" class="w-5 h-5"></i>
                        </span>
                        <input 
                            :type="showConfirmPassword ? 'text' : 'password'" 
                            name="password_confirmation" 
                            placeholder="••••••••" 
                            class="w-full pl-11 pr-14 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 font-medium text-sm" 
                            required
                        >
                        <button 
                            type="button" 
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-blue-500 transition-colors"
                        >
                            <i x-show="!showConfirmPassword" data-lucide="eye" class="w-5 h-5"></i>
                            <i x-show="showConfirmPassword" data-lucide="eye-off" class="w-5 h-5" x-cloak></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-[0_10px_20px_rgba(37,99,235,0.2)] hover:shadow-[0_15px_25px_rgba(37,99,235,0.3)] transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 group">
                        <span>Daftar Sekarang</span>
                        <i data-lucide="user-plus" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center mt-8 text-gray-500 text-sm">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Masuk disini</a>
        </p>
    </div>


</body>
</html>
