<!DOCTYPE html>
{{-- disabled dark mode: data-theme attribute --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" {{-- @auth data-theme="{{ auth()->user()->theme }}" @endauth --}}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Student Portal - Skill Stryx</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=plus-jakarta-sans:500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- disabled dark mode
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
    </script>
    --}}
</head>
<body class="font-sans antialiased bg-[#F5F8FC] flex h-screen overflow-hidden text-sm">

    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-ink-900 to-ink-950 text-ink-200 flex flex-col h-full shadow-2xl z-20 transition-all duration-300">

        <!-- Logo Area -->
        <div class="flex items-center justify-center h-[76px] px-6 border-b border-white/5">
            <img src="{{ asset('images/logo-new.jpeg') }}" alt="Skill Stryx" class="h-9 object-contain rounded-md bg-white/95 px-2 py-1">
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 space-y-1 px-4 scrollbar-hide">
            <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'bg-white/10 text-white shadow-inner ring-1 ring-white/10' : 'hover:bg-white/5 hover:text-white text-ink-300' }} flex items-center gap-3 px-4 py-2.5 rounded-2xl transition-all duration-200">
                <i class="fas fa-home w-5 text-center {{ request()->routeIs('student.dashboard') ? 'text-brand-400' : 'text-ink-400' }}"></i>
                <span class="font-semibold text-[13.5px]">Dashboard</span>
            </a>

            <a href="{{ route('student.certificates.index') }}" class="{{ request()->routeIs('student.certificates.*') ? 'bg-white/10 text-white shadow-inner ring-1 ring-white/10' : 'hover:bg-white/5 hover:text-white text-ink-300' }} flex items-center gap-3 px-4 py-2.5 rounded-2xl transition-all duration-200">
                <i class="fas fa-certificate w-5 text-center {{ request()->routeIs('student.certificates.*') ? 'text-brand-400' : 'text-ink-400' }}"></i>
                <span class="font-semibold text-[13.5px]">Certificates</span>
            </a>

            <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'bg-white/10 text-white shadow-inner ring-1 ring-white/10' : 'hover:bg-white/5 hover:text-white text-ink-300' }} flex items-center gap-3 px-4 py-2.5 rounded-2xl transition-all duration-200">
                <i class="fas fa-user-circle w-5 text-center {{ request()->routeIs('profile.edit') ? 'text-brand-400' : 'text-ink-400' }}"></i>
                <span class="font-semibold text-[13.5px]">Profile</span>
            </a>

            <a href="{{ route('student.settings') }}" class="{{ request()->routeIs('student.settings') ? 'bg-white/10 text-white shadow-inner ring-1 ring-white/10' : 'hover:bg-white/5 hover:text-white text-ink-300' }} flex items-center gap-3 px-4 py-2.5 rounded-2xl transition-all duration-200 mt-4 border border-transparent">
                <i class="fas fa-cog w-5 text-center {{ request()->routeIs('student.settings') ? 'text-brand-400' : 'text-ink-400' }}"></i>
                <span class="font-semibold text-[13.5px]">Settings</span>
            </a>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-white/5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-2.5 w-full text-left hover:bg-rose-500/10 hover:text-rose-300 text-ink-300 rounded-2xl transition-all duration-200">
                    <i class="fas fa-sign-out-alt w-5 text-center text-ink-400"></i>
                    <span class="font-semibold text-[13.5px]">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full relative z-10 overflow-hidden bg-[#F5F8FC]">

        <!-- Top Header -->
        <header class="h-[76px] bg-white/80 backdrop-blur-md border-b border-ink-100 flex items-center justify-between px-6 lg:px-8 shrink-0">
            <!-- Left Header Area (Hamburger for mobile) -->
            <div class="flex items-center gap-4 flex-1">
                <button class="text-ink-400 hover:text-ink-700 transition md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center justify-end gap-5 sm:gap-8 flex-1">

                <!-- Search Box -->
                <div class="relative hidden lg:block w-72">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-ink-300 text-sm"></i>
                    </div>
                    <input type="text" class="w-full pl-10 pr-4 py-2 bg-ink-50 border border-transparent text-ink-700 text-sm rounded-full focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all placeholder-ink-400 focus:bg-white" placeholder="Search my courses...">
                </div>

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative w-10 h-10 flex items-center justify-center rounded-full text-ink-500 hover:bg-ink-50 hover:text-brand-600 transition">
                        <i class="far fa-bell text-lg"></i>
                    </button>
                    <!-- Dropdown -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-card border border-ink-100 overflow-hidden z-50">
                        <div class="p-6 text-center text-ink-500">
                            <i class="far fa-bell text-3xl text-ink-200 mb-3 block"></i>
                            <p class="text-sm font-medium">No new notification</p>
                        </div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="flex items-center gap-3 pl-2 sm:pl-4 border-l border-ink-100">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-semibold text-ink-800 leading-none mb-1">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-ink-400 leading-none">Student</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold shadow-glow">
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
                        <h2 class="text-2xl font-display font-bold text-ink-900">
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
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3 shadow-sm">
                        <i class="fas fa-check-circle text-lg"></i>
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-3 shadow-sm">
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
    @stack('modals')
</body>
</html>
