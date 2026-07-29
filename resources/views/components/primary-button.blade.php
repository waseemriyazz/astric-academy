<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-brand-600 to-brand-400 border border-transparent rounded-full font-semibold text-sm text-white shadow-glow hover:from-brand-700 hover:to-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 active:scale-[0.98] transition-all duration-150']) }}>
    {{ $slot }}
</button>
