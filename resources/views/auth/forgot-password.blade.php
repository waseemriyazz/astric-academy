<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-new.jpeg') }}" alt="Skill Stryx" class="h-12 object-contain mix-blend-multiply">
            </div>
            <div class="bg-white rounded-4xl p-6 sm:p-10 shadow-card border border-ink-100/60">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-key text-xl"></i>
                    </div>
                    <h2 class="font-display text-2xl font-bold text-ink-900 mb-1">Forgot password?</h2>
                    <p class="text-ink-400 text-sm">{{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        {{ __('Email Password Reset Link') }}
                    </x-primary-button>
                </form>

                <p class="text-center text-sm text-ink-400 mt-6">
                    <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">
                        <i class="fas fa-arrow-left text-xs mr-1"></i> Back to login
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
