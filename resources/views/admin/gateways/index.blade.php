@extends('layouts.admin')

@section('header', 'Payment Gateways')

@section('content')
<div class="w-full">
    <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 flex items-start gap-3 shadow-sm">
        <i class="fas fa-info-circle text-lg mt-0.5"></i>
        <p class="text-sm font-medium">Only one gateway can be active at a time. Activating a gateway automatically deactivates the other — the checkout flow always uses whichever one is active here.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($gateways as $key => $gateway)
        <div
            class="rounded-2xl overflow-hidden transition-all duration-300"
            :class="active ? 'bg-gradient-to-br from-emerald-50 via-white to-emerald-50/50 border border-emerald-200 shadow-[0_8px_28px_-8px_rgba(16,185,129,0.35)]' : 'bg-white border border-gray-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)]'"
            x-data="{ active: {{ $gateway->is_active ? 'true' : 'false' }} }"
        >
            <div class="px-6 py-5 flex items-center justify-between transition-colors duration-300" :class="active ? 'border-b border-emerald-100' : 'border-b border-gray-100'">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 flex items-center justify-center text-brand-600">
                        <i class="fas {{ $key === 'payglocal' ? 'fa-globe' : 'fa-bolt' }} text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ ucfirst($key) }}</h3>
                        <p class="text-xs text-gray-500" x-text="active ? 'Active' : 'Inactive'" :class="active ? 'text-emerald-600 font-semibold' : 'text-gray-400'"></p>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $gateway->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-50 text-gray-500 border border-gray-200' }}">
                    {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <form action="{{ route('admin.gateways.update', $key) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf

                <label for="is_active_{{ $key }}" class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="radio" id="is_active_{{ $key }}" name="is_active" value="1" onchange="this.form.requestSubmit()" {{ $gateway->is_active ? 'checked' : '' }} class="w-4 h-4 accent-emerald-600 cursor-pointer">
                    <span class="text-sm font-semibold text-gray-700">Set as active gateway</span>
                </label>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Environment</label>
                    <select name="is_production" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="0" {{ !$gateway->is_production ? 'selected' : '' }}>Sandbox / Test</option>
                        <option value="1" {{ $gateway->is_production ? 'selected' : '' }}>Production / Live</option>
                    </select>
                </div>

                <div class="border-t border-gray-100 pt-5 space-y-4">
                    @if($key === 'easebuzz')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Merchant Key</label>
                            <div class="relative" x-data="{ show: false }">
                                <input type="password" x-ref="input" autocomplete="new-password" name="config[key]" placeholder="{{ $gateway->credential('key') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                                <button type="button" @click="if ($refs.input.type === 'password' && !$refs.input.value) { fetch('{{ route('admin.gateways.reveal', [$key, 'key']) }}').then(r => r.json()).then(d => { $refs.input.value = d.value; $refs.input.type = 'text'; show = true; }); } else { $refs.input.type = $refs.input.type === 'password' ? 'text' : 'password'; show = $refs.input.type === 'text'; }" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Salt</label>
                            <div class="relative" x-data="{ show: false }">
                                <input type="password" x-ref="input" autocomplete="new-password" name="config[salt]" placeholder="{{ $gateway->credential('salt') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                                <button type="button" @click="if ($refs.input.type === 'password' && !$refs.input.value) { fetch('{{ route('admin.gateways.reveal', [$key, 'salt']) }}').then(r => r.json()).then(d => { $refs.input.value = d.value; $refs.input.type = 'text'; show = true; }); } else { $refs.input.type = $refs.input.type === 'password' ? 'text' : 'password'; show = $refs.input.type === 'text'; }" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current value.</p>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Merchant ID</label>
                            <div class="relative" x-data="{ show: false }">
                                <input type="password" x-ref="input" autocomplete="new-password" name="config[merchant_id]" placeholder="{{ $gateway->credential('merchant_id') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                                <button type="button" @click="if ($refs.input.type === 'password' && !$refs.input.value) { fetch('{{ route('admin.gateways.reveal', [$key, 'merchant_id']) }}').then(r => r.json()).then(d => { $refs.input.value = d.value; $refs.input.type = 'text'; show = true; }); } else { $refs.input.type = $refs.input.type === 'password' ? 'text' : 'password'; show = $refs.input.type === 'text'; }" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Private KID</label>
                            <div class="relative" x-data="{ show: false }">
                                <input type="password" x-ref="input" autocomplete="new-password" name="config[private_kid]" placeholder="{{ $gateway->credential('private_kid') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                                <button type="button" @click="if ($refs.input.type === 'password' && !$refs.input.value) { fetch('{{ route('admin.gateways.reveal', [$key, 'private_kid']) }}').then(r => r.json()).then(d => { $refs.input.value = d.value; $refs.input.type = 'text'; show = true; }); } else { $refs.input.type = $refs.input.type === 'password' ? 'text' : 'password'; show = $refs.input.type === 'text'; }" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Identifies our signing keypair to PayGlocal — sent on every request. Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Public KID</label>
                            <div class="relative" x-data="{ show: false }">
                                <input type="password" x-ref="input" autocomplete="new-password" name="config[public_kid]" placeholder="{{ $gateway->credential('public_kid') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                                <button type="button" @click="if ($refs.input.type === 'password' && !$refs.input.value) { fetch('{{ route('admin.gateways.reveal', [$key, 'public_kid']) }}').then(r => r.json()).then(d => { $refs.input.value = d.value; $refs.input.type = 'text'; show = true; }); } else { $refs.input.type = $refs.input.type === 'password' ? 'text' : 'password'; show = $refs.input.type === 'text'; }" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Identifies PayGlocal's public key used to encrypt every outgoing request. Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">RSA Private Key</label>
                            <div class="flex items-center gap-2">
                                <input type="file" name="config_files[private_key]" accept=".pem,.key,.txt" class="w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-2xl file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold file:text-xs hover:file:bg-brand-100 bg-gray-50 border border-gray-200 rounded-2xl">
                                @if($gateway->credential('private_key'))
                                    <span class="shrink-0 text-xs font-medium text-green-600 flex items-center gap-1"><i class="fas fa-check-circle"></i> Set</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Ours — used to sign outgoing requests to PayGlocal. Upload the .pem file; leave empty to keep the current key.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">PayGlocal Public Key</label>
                            <div class="flex items-center gap-2">
                                <input type="file" name="config_files[public_key]" accept=".pem,.key,.txt" class="w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-2xl file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold file:text-xs hover:file:bg-brand-100 bg-gray-50 border border-gray-200 rounded-2xl">
                                @if($gateway->credential('public_key'))
                                    <span class="shrink-0 text-xs font-medium text-green-600 flex items-center gap-1"><i class="fas fa-check-circle"></i> Set</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1">PayGlocal's own key — every outgoing request is encrypted with it before being signed with ours. Upload the .pem file; leave empty to keep the current key.</p>
                        </div>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-2xl font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-save"></i> Save {{ ucfirst($key) }} Settings
                    </button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
