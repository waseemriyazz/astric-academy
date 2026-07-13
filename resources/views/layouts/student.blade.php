<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @auth data-theme="{{ auth()->user()->theme }}" @endauth>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Student Portal - Astryx Academy</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
    </script>
</head>
<body class="font-sans antialiased bg-[#F8FAFC] flex h-screen overflow-hidden text-sm">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-white text-gray-600 flex flex-col h-full shadow-[2px_0_10px_rgba(0,0,0,0.03)] border-r border-gray-100 z-20 transition-all duration-300">
        
        <!-- Logo Area -->
        <div class="flex items-center justify-center h-[72px] px-6 border-b border-gray-100">
            <img src="{{ asset('images/logo-1.jpeg') }}" alt="Astryx Academy" class="h-10 object-contain mix-blend-multiply">
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 space-y-1.5 px-4 scrollbar-hide">
            <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'hover:bg-gray-50 hover:text-gray-900 text-gray-500' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200">
                <i class="fas fa-home w-5 text-center text-lg {{ request()->routeIs('student.dashboard') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                <span class="font-semibold">Dashboard</span>
            </a>
            
            <a href="#" class="hover:bg-gray-50 hover:text-gray-900 text-gray-500 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200">
                <i class="fas fa-certificate w-5 text-center text-lg text-gray-400"></i>
                <span class="font-semibold">Certificates</span>
            </a>
            
            <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'hover:bg-gray-50 hover:text-gray-900 text-gray-500' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200">
                <i class="fas fa-user-circle w-5 text-center text-lg {{ request()->routeIs('profile.edit') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                <span class="font-semibold">Profile</span>
            </a>
            
            <a href="{{ route('student.settings') }}" class="{{ request()->routeIs('student.settings') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'hover:bg-gray-50 hover:text-gray-900 text-gray-500' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 mt-4 border border-transparent">
                <i class="fas fa-cog w-5 text-center text-lg {{ request()->routeIs('student.settings') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                <span class="font-semibold">Settings</span>
            </a>
        </nav>
        
        <!-- Logout -->
        <div class="p-4 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-3 w-full text-left hover:bg-red-50 hover:text-red-700 text-gray-500 rounded-xl transition-all duration-200">
                    <i class="fas fa-sign-out-alt w-5 text-center text-lg text-gray-400 group-hover:text-red-500"></i>
                    <span class="font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full relative z-10 overflow-hidden bg-[#F8FAFC]">
        
        <!-- Top Header -->
        <header class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 lg:px-8 shadow-sm shrink-0">
            <!-- Left Header Area (Hamburger for mobile) -->
            <div class="flex items-center gap-4 flex-1">
                <button class="text-gray-400 hover:text-gray-600 transition md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center justify-end gap-5 sm:gap-8 flex-1">
                
                <!-- Search Box -->
                <div class="relative hidden lg:block w-72">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder-gray-400 focus:bg-white" placeholder="Search my courses...">
                </div>

                <!-- Notifications -->
                <button class="relative text-gray-500 hover:text-indigo-600 transition">
                    <i class="far fa-bell text-xl"></i>
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 border-2 border-white box-content transform translate-x-1 -translate-y-1"></span>
                </button>

                <!-- Profile -->
                <div class="flex items-center gap-3 pl-2 sm:pl-4 border-l border-gray-200">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-semibold text-gray-800 leading-none mb-1">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 leading-none">Student</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold shadow-sm border border-indigo-200">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content Scroll Area -->
        <main class="flex-1 overflow-y-auto">
            <!-- Page Title Area -->
            <div class="px-6 lg:px-8 py-6 max-w-7xl mx-auto w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            @yield('header')
                        </h2>
                    </div>
                    
                    @hasSection('header_actions')
                        <div>
                            @yield('header_actions')
                        </div>
                    @endif
                </div>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3 shadow-sm">
                        <i class="fas fa-check-circle text-lg"></i>
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-3 shadow-sm">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Dynamic Page Content -->
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
