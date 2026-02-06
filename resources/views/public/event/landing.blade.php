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

        <div class="mt-6 grid md:grid-cols-2 gap-4">
            <a href="{{ route('public.event.show', $event) }}"
               class="block text-center rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-3 transition">
                Nova inscrição
            </a>

            <a href="{{ route('public.attendee.login', $event) }}"
               class="block text-center rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-100 font-semibold py-3 transition border border-zinc-700">
                Já sou inscrito
            </a>

        </div>
    </div>
</body>
</html>
