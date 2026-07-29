<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-new.jpeg') }}" alt="Skill Stryx" class="h-12 object-contain mix-blend-multiply">
            </div>
            <div class="bg-white rounded-4xl p-6 sm:p-10 shadow-card border border-ink-100/60">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-halved text-xl"></i>
                    </div>
                    <h2 class="font-display text-2xl font-bold text-ink-900 mb-1">Confirm your password</h2>
                    <p class="text-ink-400 text-sm">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
                </div>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
                    @csrf

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <div class="relative mt-1">
                            <x-text-input id="password" class="block w-full pr-10"
                                            type="password"
                                            name="password"
                                            required autocomplete="current-password" />
                            <button type="button" onclick="const p = document.getElementById('password'); const i = this.querySelector('i'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); } else { p.type = 'password'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-ink-300 hover:text-ink-600 focus:outline-none">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        {{ __('Confirm') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
