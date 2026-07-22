@extends('layouts.admin')

@section('header', 'Payment Gateways')

@section('content')
<div class="w-full">
    <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 flex items-start gap-3 shadow-sm">
        <i class="fas fa-info-circle text-lg mt-0.5"></i>
        <p class="text-sm font-medium">Only one gateway can be active at a time. Activating a gateway automatically deactivates the other — the checkout flow always uses whichever one is active here.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($gateways as $key => $gateway)
        <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden" x-data="{ active: {{ $gateway->is_active ? 'true' : 'false' }} }">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="fas {{ $key === 'payglocal' ? 'fa-globe' : 'fa-bolt' }} text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ ucfirst($key) }}</h3>
                        <p class="text-xs text-gray-500" x-text="active ? 'Active' : 'Inactive'" :class="active ? 'text-green-600 font-semibold' : 'text-gray-400'"></p>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $gateway->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-500 border border-gray-200' }}">
                    {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <form action="{{ route('admin.gateways.update', $key) }}" method="POST" class="p-6 space-y-5">
                @csrf

                <div class="flex items-center justify-between">
                    <label for="is_active_{{ $key }}" class="text-sm font-semibold text-gray-700">Enable this gateway</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="is_active_{{ $key }}" name="is_active" value="1" x-model="active" {{ $gateway->is_active ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-600 transition-colors"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Environment</label>
                    <select name="is_production" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="0" {{ !$gateway->is_production ? 'selected' : '' }}>Sandbox / Test</option>
                        <option value="1" {{ $gateway->is_production ? 'selected' : '' }}>Production / Live</option>
                    </select>
                </div>

                <div class="border-t border-gray-100 pt-5 space-y-4">
                    @if($key === 'easebuzz')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Merchant Key</label>
                            <input type="password" name="config[key]" placeholder="{{ $gateway->credential('key') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Salt</label>
                            <input type="password" name="config[salt]" placeholder="{{ $gateway->credential('salt') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current value.</p>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Merchant ID</label>
                            <input type="password" name="config[merchant_id]" placeholder="{{ $gateway->credential('merchant_id') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Private KID</label>
                            <input type="password" name="config[private_kid]" placeholder="{{ $gateway->credential('private_kid') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                            <p class="text-xs text-gray-400 mt-1">Identifies our signing keypair to PayGlocal — sent on every request. Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Public KID <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="password" name="config[public_kid]" placeholder="{{ $gateway->credential('public_kid') ? '••••••••••••' : 'Not set' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                            <p class="text-xs text-gray-400 mt-1">PayGlocal's reference for their public key. Not sent on requests today — kept for reconciliation. Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">RSA Private Key</label>
                            <textarea name="config[private_key]" rows="4" placeholder="{{ $gateway->credential('private_key') ? 'A private key is already set — leave blank to keep it' : '-----BEGIN PRIVATE KEY-----' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Ours — used to sign outgoing requests to PayGlocal. Leave blank to keep the current value.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">PayGlocal Public Key <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea name="config[public_key]" rows="4" placeholder="{{ $gateway->credential('public_key') ? 'A public key is already set — leave blank to keep it' : '-----BEGIN PUBLIC KEY-----' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-all font-mono"></textarea>
                            <p class="text-xs text-gray-400 mt-1">PayGlocal's own key — used to verify their callback signature. Without it, callbacks are still cross-checked against PayGlocal's status API before enrollment.</p>
                        </div>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-save"></i> Save {{ ucfirst($key) }} Settings
                    </button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
