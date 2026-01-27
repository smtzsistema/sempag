<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-100">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-md rounded-2xl bg-zinc-900 border border-zinc-800 p-6">
            <div class="mb-6">
                <p class="text-xs text-zinc-400">Admin • Evento</p>
                <h1 class="text-2xl font-bold">{{ $event->name }}</h1>
                <p class="text-sm text-zinc-400 mt-1">Acesso restrito à organizadora</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-900/50 bg-red-950/40 p-3 text-sm text-red-200">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post', $event) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm text-zinc-300 mb-1">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-600"
                           placeholder="admin@demo.com" required>
                </div>

                <div>
                    <label class="block text-sm text-zinc-300 mb-1">Senha</label>
                    <input type="password" name="password"
                           class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-600"
                           placeholder="••••••••" required>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-2.5 transition">
                    Entrar
                </button>

                <p class="text-xs text-zinc-500">
                    Dica demo: <span class="text-zinc-300">admin@demo.com</span> / <span class="text-zinc-300">123456</span>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
