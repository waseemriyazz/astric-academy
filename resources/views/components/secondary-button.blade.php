<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-ink-100 rounded-full font-semibold text-sm text-ink-800 shadow-sm hover:bg-brand-50 hover:border-brand-200 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 disabled:opacity-25 transition-all duration-150']) }}>
    {{ $slot }}
</button>
