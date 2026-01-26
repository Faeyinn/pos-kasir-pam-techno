<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - Pam Techno</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('head-scripts')
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="flex h-screen">
        <div class="sidebar no-print w-64 bg-slate-900 text-white flex flex-col transition-all duration-300">
            <div class="p-6 border-b border-slate-800">
                <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Pam Admin</h1>
            </div>
            
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="{{ route('admin.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.products') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    <span class="font-medium">Products</span>
                </a>
                <a href="{{ route('admin.discounts') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.discounts') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i data-lucide="percent" class="w-5 h-5"></i>
                    <span class="font-medium">Diskon</span>
                </a>
                <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.reports') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="font-medium">Laporan</span>
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.users') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="font-medium">Users</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800 space-y-2">
                @if(auth()->user()->role === 'master')
                <div class="bg-slate-800 rounded-xl p-3 mb-2">
                    <p class="text-xs text-slate-400 mb-2 uppercase font-semibold">Switch View</p>
                    <form action="{{ route('role.set') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="kasir">
                        <button type="submit" class="w-full flex items-center justify-between px-3 py-2 bg-indigo-900/50 hover:bg-indigo-900 text-indigo-300 rounded-lg text-sm transition-colors border border-indigo-500/20">
                            <span>To Kasir</span>
                            <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
                @endif
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-500 transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
           <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 no-print">
               <h2 class="text-lg font-semibold text-slate-800">@yield('header', 'Admin Area')</h2>
               <div class="flex items-center gap-4">
                   <div class="text-right">
                       <p class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                       <p class="text-xs text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                   </div>
                   <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                       {{ substr(auth()->user()->name, 0, 1) }}
                   </div>
               </div>
           </header>
           <main class="flex-1 overflow-auto p-8">
               @yield('content')
           </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('alpine:initialized', () => setTimeout(() => lucide.createIcons(), 100));
    </script>
    
    @stack('scripts')
</body>
</html>
