@php
    $theme = request()->cookie('theme', 'dark');
    $isDark = $theme === 'dark';
@endphp

<div class="fixed right-4 top-4 z-[9999]">
    <form method="POST" action="{{ route('theme.toggle') }}">
        @csrf
        <button type="submit"
                class="rounded-full border border-zinc-700 bg-zinc-900/80 px-4 py-2 text-xs font-semibold text-zinc-100 shadow-lg backdrop-blur hover:bg-zinc-800">
            {{ $isDark ? 'Tema claro' : 'Tema escuro' }}
        </button>
    </form>
</div>
