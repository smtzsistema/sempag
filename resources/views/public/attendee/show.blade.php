<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minha inscrição</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-100">
    <div class="max-w-3xl mx-auto p-6">
        <a class="text-zinc-400 hover:text-zinc-200 text-sm" href="{{ route('public.event.landing', $event) }}">← voltar</a>

        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 mt-4">
            <h1 class="text-2xl font-bold">Minha inscrição</h1>
            <p class="text-zinc-400 mt-1">{{ $event->name }}</p>

            <div class="mt-5 text-sm text-zinc-300 space-y-2">
                <div><span class="text-zinc-500">Status:</span> {{ $registration->status }}</div>
                <div><span class="text-zinc-500">Nome:</span> {{ $registration->full_name }}</div>
                <div><span class="text-zinc-500">E-mail:</span> {{ $registration->email }}</div>
                <div><span class="text-zinc-500">Token:</span> {{ $registration->token }}</div>
            </div>

            <div class="mt-6 text-xs text-zinc-500">
                Próximo passo: mostrar os campos/answers e habilitar edição só onde o admin permitir.
            </div>
        </div>
    </div>
</body>
</html>
