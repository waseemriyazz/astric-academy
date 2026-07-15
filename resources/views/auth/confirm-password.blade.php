<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" onclick="const p = document.getElementById('password'); const i = this.querySelector('i'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); } else { p.type = 'password'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
