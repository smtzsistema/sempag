<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Escolher credencial - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />

@php
    $bannerPath = !empty($category->banner_path) ? $category->banner_path : ($event->banner_path ?? null);
@endphp
@if($bannerPath)
    <div class="mx-auto max-w-[1200px]">
        <div class="w-full h-[200px] flex items-center justify-center rounded-2xl bg-zinc-950">
            <img
                src="{{ asset('storage/'.$bannerPath) }}"
                alt="Banner"
                class="max-w-full max-h-full object-contain"
            >
        </div>
    </div>
@endif
<div class="max-w-4xl mx-auto p-6">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Escolher credencial</h1>
                <p class="text-zinc-400 text-sm mt-1">{{ $event->name }}</p>
            </div>

            <a href="{{ route('public.attendee.area', $event) }}"
               class="rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-4 py-2 font-semibold">
                Voltar
            </a>
        </div>

        <div class="mt-6 grid md:grid-cols-2 gap-4">
            @foreach($credentials as $cred)
                <a href="{{ route('public.attendee.credentials.print', [$event, $cred->cre_id]) }}"
                   class="block rounded-2xl bg-zinc-950 border border-zinc-800 p-5 hover:border-zinc-700 transition">
                    <div class="text-lg font-semibold">{{ $cred->cre_nome }}</div>
                    <div class="text-zinc-400 text-sm mt-1">
                        Clique pra abrir a impressão
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
</body>
</html>
