@extends('layouts.student')

@section('header', 'Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    
    {{-- disabled dark mode
    <!-- Appearance Settings Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h4 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-palette text-indigo-600"></i>
                Appearance
            </h4>
        </div>
        
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Customize how the application looks and feels.</p>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-moon text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h5 class="font-semibold text-gray-900 mb-1">Dark Mode</h5>
                        <p class="text-sm text-gray-600">Switch between light and dark themes</p>
                    </div>
                </div>
                
                <button id="theme-toggle" class="relative inline-flex h-8 w-14 items-center rounded-full bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span id="theme-toggle-circle" class="inline-block h-6 w-6 transform rounded-full bg-white transition-transform translate-x-1 shadow-sm"></span>
                </button>
            </div>
        </div>
    </div>
    --}}

    <!-- Password Change Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h4 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-lock text-indigo-600"></i>
                Change Password
            </h4>
        </div>
        
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Update your password to keep your account secure.</p>
            
            @if(session('status') === 'password-updated')
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3 shadow-sm">
                    <i class="fas fa-check-circle text-lg"></i>
                    <p class="font-medium">Password updated successfully!</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="max-w-xl space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="current_password" :value="__('Current Password')" />
                    <x-text-input 
                        id="current_password" 
                        name="current_password" 
                        type="password" 
                        class="mt-1 block w-full" 
                        autocomplete="current-password" 
                    />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('New Password')" />
                    <x-text-input 
                        id="password" 
                        name="password" 
                        type="password" 
                        class="mt-1 block w-full" 
                        autocomplete="new-password" 
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="mt-1 block w-full" 
                        autocomplete="new-password" 
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>
                        {{ __('Update Password') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Information Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white text-3xl font-bold border-2 border-white/30">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="text-white">
                    <h3 class="text-2xl font-bold mb-1">{{ $user->name }}</h3>
                    <p class="text-indigo-100 flex items-center gap-2">
                        <i class="fas fa-envelope"></i>
                        {{ $user->email }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-indigo-600"></i>
                Account Information
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Full Name</p>
                    <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email Address</p>
                    <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Account Type</p>
                    <p class="text-gray-900 font-medium capitalize">{{ $user->role ?? 'Student' }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Member Since</p>
                    <p class="text-gray-900 font-medium">{{ $user->created_at->format('F j, Y') }}</p>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-red-200 bg-red-50">
            <h4 class="text-lg font-bold text-red-900 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                Danger Zone
            </h4>
        </div>
        
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
            
            <button onclick="event.preventDefault(); document.getElementById('delete-user-form').submit();" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition shadow-sm">
                <i class="fas fa-trash-alt"></i>
                Delete Account
            </button>
            
            <form id="delete-user-form" action="{{ route('profile.destroy') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

{{-- disabled dark mode
@push('scripts')
<script>
    const themeToggle = document.getElementById('theme-toggle');
    const themeToggleCircle = document.getElementById('theme-toggle-circle');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Apply theme to document and toggle UI
    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggle.classList.remove('bg-gray-200');
            themeToggle.classList.add('bg-indigo-600');
            themeToggleCircle.classList.remove('translate-x-1');
            themeToggleCircle.classList.add('translate-x-7');
        } else {
            document.documentElement.removeAttribute('data-theme');
            themeToggle.classList.add('bg-gray-200');
            themeToggle.classList.remove('bg-indigo-600');
            themeToggleCircle.classList.add('translate-x-1');
            themeToggleCircle.classList.remove('translate-x-7');
        }
    };
    
    // Save theme to the database via AJAX
    const saveTheme = (theme) => {
        fetch('{{ route("theme.update") }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ theme }),
        });
    };
    
    // Toggle theme
    const toggleTheme = () => {
        const currentTheme = localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
        saveTheme(newTheme);
    };
    
    // Initialize theme on page load
    const initTheme = () => {
        const storedTheme = localStorage.getItem('theme') || 'light';
        applyTheme(storedTheme);
    };
    
    // Check for system preference if no stored theme
    const checkSystemPreference = () => {
        if (!localStorage.getItem('theme')) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = prefersDark ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
            applyTheme(theme);
        }
    };
    
    // Event listeners
    themeToggle.addEventListener('click', toggleTheme);
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        checkSystemPreference();
        initTheme();
    });
</script>
@endpush
--}}
@endsection