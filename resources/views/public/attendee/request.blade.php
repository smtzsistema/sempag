<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->name }} - Já sou inscrito</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />
    <div class="max-w-xl mx-auto p-6">
        <a class="text-zinc-400 hover:text-zinc-200 text-sm" href="{{ route('public.event.landing', $event) }}">← voltar</a>

        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 mt-4">
            <h1 class="text-2xl font-bold">Já sou inscrito</h1>
            <p class="text-zinc-400 mt-1">Informe seu e-mail para receber um link de acesso à sua inscrição.</p>

            @if(session('ok'))
                <div class="mt-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-emerald-200 text-sm">
                    {{ session('ok') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-red-200 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('public.attendee.send_link', $event) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-2">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        placeholder="seu@email.com">
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 py-3 font-semibold">
                    Enviar link
                </button>
            </form>
        </div>
    </div>
</body>
</html>
