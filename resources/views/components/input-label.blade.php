@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-ink-700']) }}>
    {{ $value ?? $slot }}
</label>
