<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-new.jpeg') }}" alt="Skill Stryx" class="h-12 object-contain mix-blend-multiply">
            </div>
            <div class="bg-white rounded-4xl p-6 sm:p-10 shadow-card border border-ink-100/60">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope-open-text text-xl"></i>
                    </div>
                    <h2 class="font-display text-2xl font-bold text-ink-900 mb-1">Verify your email</h2>
                    <p class="text-ink-400 text-sm">{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 font-medium text-sm text-emerald-700 text-center">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div class="flex flex-col gap-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-primary-button class="w-full justify-center">
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center text-sm font-semibold text-ink-500 hover:text-brand-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
