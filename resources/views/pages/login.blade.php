<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pam Techno POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 sm:p-6 lg:p-8 overflow-hidden">
    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-full -z-10 overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-blue-100 rounded-full blur-[120px] opacity-60"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-indigo-100 rounded-full blur-[120px] opacity-60"></div>
    </div>

    <div class="w-full max-w-lg">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col items-center p-8 sm:p-12 transition-all transform hover:scale-[1.01]">
            <div class="mb-10 flex justify-center">
                <img src="{{ asset('assets/logo.png') }}" alt="Pam Techno Logo" class="h-32 object-contain filter drop-shadow-sm">
            </div>
            
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang</h2>
            <p class="text-gray-500 mb-10 text-center">Silakan masuk ke sistem kasir Pam Techno</p>
            
            <form 
                action="{{ route('login.post') }}" 
                method="POST" 
                class="w-full space-y-6"
                x-data="{ showPassword: false }"
            >
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email" class="w-full pl-12 pr-4 py-4 bg-gray-50 border @error('email') border-red-500 @else border-gray-200 @enderror rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" required autofocus>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-2 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-gray-700">Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            placeholder="Masukkan password" 
                            class="w-full pl-12 pr-14 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" 
                            required
                        >
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-blue-600 transition-colors"
                        >
                            <i x-show="!showPassword" data-lucide="eye" class="w-5 h-5"></i>
                            <i x-show="showPassword" data-lucide="eye-off" class="w-5 h-5" x-cloak></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition-all">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya untuk 30 hari</label>
                </div>

                <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all transform hover:translate-y-[-2px] active:scale-95">
                    Masuk ke Kasir
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-widest font-bold">Pam Techno POS v1.0</p>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
