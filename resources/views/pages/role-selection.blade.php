<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - Pam Techno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
        <div class="p-8 text-center bg-blue-600 text-white">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold mb-2">Welcome Master</h1>
            <p class="text-indigo-100">Please select your view mode</p>
        </div>
        
        <div class="p-8 space-y-4">
            <form action="{{ route('role.set') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="admin">
                <button type="submit" class="w-full group relative flex items-center p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-300">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mr-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="font-bold text-slate-800 group-hover:text-indigo-700">Admin View</h3>
                        <p class="text-sm text-slate-500">Access dashboard & management</p>
                    </div>
                    <div class="absolute right-4 text-slate-300 group-hover:text-indigo-500 transition-colors">
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </div>
                </button>
            </form>

            <form action="{{ route('role.set') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="kasir">
                <button type="submit" class="w-full group relative flex items-center p-4 rounded-2xl border-2 border-slate-100 hover:border-emerald-500 hover:bg-emerald-50 transition-all duration-300">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mr-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="font-bold text-slate-800 group-hover:text-emerald-700">Kasir View</h3>
                        <p class="text-sm text-slate-500">Access Point of Sale interface</p>
                    </div>
                    <div class="absolute right-4 text-slate-300 group-hover:text-emerald-500 transition-colors">
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </div>
                </button>
            </form>
            
             <form action="{{ route('logout') }}" method="POST" class="mt-6 pt-6 border-t border-slate-100">
                @csrf
                <button type="submit" class="w-full text-center text-slate-400 hover:text-red-500 text-sm font-medium transition-colors">
                    Log Out
                </button>
            </form>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
