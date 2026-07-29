<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-new.jpeg') }}" alt="Skill Stryx" class="h-12 object-contain mix-blend-multiply">
            </div>
            <div class="bg-white rounded-4xl p-6 sm:p-10 shadow-card border border-ink-100/60">
                <div class="text-center mb-6">
                    <h2 class="font-display text-2xl font-bold text-ink-900 mb-1">Create your account</h2>
                    <p class="text-ink-400 text-sm">Join Skill Stryx and start learning today.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <div class="relative mt-1">
                            <x-text-input id="password" class="block w-full pr-10"
                                            type="password"
                                            name="password"
                                            required autocomplete="new-password" />
                            <button type="button" onclick="const p = document.getElementById('password'); const i = this.querySelector('i'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); } else { p.type = 'password'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-ink-300 hover:text-ink-600 focus:outline-none">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                        <div class="relative mt-1">
                            <x-text-input id="password_confirmation" class="block w-full pr-10"
                                            type="password"
                                            name="password_confirmation" required autocomplete="new-password" />
                            <button type="button" onclick="const p = document.getElementById('password_confirmation'); const i = this.querySelector('i'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); } else { p.type = 'password'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-ink-300 hover:text-ink-600 focus:outline-none">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a class="text-sm font-semibold text-ink-500 hover:text-brand-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400" href="{{ route('login') }}">
                            {{ __('Already registered?') }}
                        </a>

                        <x-primary-button>
                            {{ __('Register') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
