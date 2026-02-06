<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minha área - {{ $event->name }}</title>
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
                <h1 class="text-2xl font-bold">Minha área</h1>
                <p class="text-zinc-400 text-sm mt-1">{{ $event->name }}</p>
            </div>

            <form method="POST" action="{{ route('public.attendee.logout', $event) }}">
                @csrf
                <button class="rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-4 py-2 font-semibold">
                    Sair
                </button>
            </form>
        </div>

        @if(session('ok'))
            <div class="mt-4 p-3 rounded-lg bg-emerald-900/25 border border-emerald-800 text-emerald-200">
                {{ session('ok') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mt-4 p-3 rounded-lg bg-red-900/25 border border-red-800 text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-6 rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
            <div class="text-sm text-zinc-400">Inscrição</div>
            <div class="mt-1 font-semibold">
                {{ $registration->full_name ?? '—' }}
            </div>
            <div class="text-zinc-400 text-sm mt-1">
                {{ $registration->email ?? '—' }}
                @if($registration->cpf) • {{ $registration->cpf }} @endif
            </div>
            <div class="text-zinc-400 text-sm mt-1">
                Status: <span class="text-zinc-200">{{ $registration->status }}</span>
            </div>
        </div>

        @if(($registration->form?->form_foto ?? 'N') === 'S')
            <div class="mt-6 rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-zinc-400">Minha foto</div>
                        <div class="text-xs text-zinc-500 mt-1">Essa foto pode ser usada na credencial e é visível no admin.</div>
                    </div>
                    <a href="{{ route('public.attendee.photo', $event) }}"
                       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
                        Editar foto
                    </a>
                </div>

                <div class="mt-4">
                    @if(!empty($registration->photo_url))
                        <img src="{{ $registration->photo_url }}" alt="Foto" class="w-40 h-40 rounded-2xl object-cover border border-zinc-800">
                    @else
                        <div class="w-40 h-40 rounded-2xl bg-zinc-900/40 border border-zinc-800 flex items-center justify-center text-zinc-500 text-sm">
                            Sem foto
                        </div>
                    @endif
                </div>
            </div>
        @endif


        <div class="mt-6 grid md:grid-cols-3 gap-4">
            <a href="{{ route('public.attendee.letter', $event) }}"
               class="block rounded-2xl bg-zinc-950 border border-zinc-800 p-5 hover:border-zinc-700 transition">
                <div class="text-lg font-semibold">Carta de confirmação</div>
                <div class="text-zinc-400 text-sm mt-1">Visualizar o e-mail de confirmação</div>
            </a>

            <a href="{{ route('public.attendee.edit', $event) }}"
               class="block rounded-2xl bg-zinc-950 border border-zinc-800 p-5 hover:border-zinc-700 transition">
                <div class="text-lg font-semibold">Editar dados</div>
                <div class="text-zinc-400 text-sm mt-1">Alterar somente campos permitidos</div>
            </a>

            <a href="{{ $credUi['href'] ?? '#' }}"
               class="block rounded-2xl border p-5 {{ $credUi['class'] ?? 'bg-zinc-950 border-zinc-800' }}"
               @if(empty($credUi['enabled'])) onclick="return false;" @endif
            >
                <div class="text-lg font-semibold">Credencial</div>
                <div class="text-zinc-400 text-sm mt-1">
                    {{ $credUi['desc'] ?? 'Abrir credencial' }}
                </div>
            </a>
        </div>

        <div class="mt-6">
            <a href="{{ route('public.event.landing', $event) }}"
               class="text-sm text-zinc-400 hover:text-zinc-200">
                ← Voltar ao início
            </a>
        </div>
    </div>
</div>
</body>
</html>
