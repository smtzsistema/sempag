<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-100 p-4">
    <div class="text-lg font-semibold mb-4">Exportar relatório (CSV)</div>

    <div class="text-sm text-zinc-300 mb-2">Categorias carregadas: {{ $categories->count() }}</div>

    <form method="GET" action="{{ route('admin.registrations.index', $event) }}" class="space-y-3">
        <label class="block text-sm text-zinc-300">Categoria</label>
        <select name="category_id" class="w-full rounded-xl bg-zinc-900 border border-zinc-800 p-2">
            <option value="">Todas</option>
            @foreach($categories as $c)
                <option value="{{ $c->cat_id }}">{{ $c->cat_nome }}</option>
            @endforeach
        </select>

        <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2">
            Continuar
        </button>
    </form>
</body>
</html>
