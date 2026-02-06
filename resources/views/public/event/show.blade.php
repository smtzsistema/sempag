<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />
@if($event->banner_path)
    <div class="mx-auto max-w-[1200px]">
        <div class="w-full h-[200px] flex items-center justify-center rounded-2xl bg-zinc-950">
            <img
                src="{{ asset('storage/'.$event->banner_path) }}"
                alt="Banner"
                class="max-w-full max-h-full object-contain"
            >
        </div>
    </div>
@endif
<div class="max-w-4xl mx-auto p-6">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6">
        <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
        @if($event->description)
            <p class="text-zinc-300 mt-2 whitespace-pre-line">{{ $event->description }}</p>
        @endif
    </div>
    <a class="text-zinc-400 hover:text-zinc-200 text-sm" href="{{ route('public.event.landing', $event) }}">← voltar</a>

    <div class="mt-6">
        <h2 class="text-xl font-semibold mb-3">Escolha sua categoria</h2>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($event->categories as $cat)
                <a href="{{ route('public.registration.create', [$event, $cat]) }}"
                   class="block rounded-2xl bg-zinc-900 border border-zinc-800 p-5 hover:border-zinc-600 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-lg font-semibold">{{ $cat->name }}</div>
                            @if($cat->description)
                                <div class="text-sm text-zinc-300 mt-1">{{ $cat->description }}</div>
                            @endif
                        </div>
                        @if($cat->requires_approval)
                            <span
                                class="text-xs px-2 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    Sujeito à aprovação
                                </span>
                        @else
                            <span
                                class="text-xs px-2 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    Instantâneo
                                </span>
                        @endif
                    </div>
                    <div class="text-xs text-zinc-500 mt-3">Clique para iniciar a inscrição nessa categoria</div>
                </a>
            @empty
                <div class="text-zinc-400">Nenhuma categoria ativa.</div>
            @endforelse
        </div>
    </div>
</div>
</body>
</html>
