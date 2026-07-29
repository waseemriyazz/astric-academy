@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ink-100 focus:border-brand-500 focus:ring-brand-500 rounded-2xl shadow-sm']) }}>
