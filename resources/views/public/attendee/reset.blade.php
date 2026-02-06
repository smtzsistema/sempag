<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redefinir senha - {{ $event->name }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />
<div class="min-h-screen flex items-center justify-center p-6">
  <div class="w-full max-w-md">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 shadow-lg">
      <h1 class="text-2xl font-bold">Redefinir senha</h1>
      <p class="text-zinc-400 mt-1 text-sm">{{ $event->name }}</p>

      <form class="mt-6 space-y-4" method="POST" action="{{ url()->full() }}">
        @csrf

        <div>
          <label class="text-sm text-zinc-300">Nova senha</label>
          <input name="password" type="password" required
                 class="mt-1 w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-600">
          @error('password') <p class="text-red-300 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="text-sm text-zinc-300">Confirmar nova senha</label>
          <input name="password_confirmation" type="password" required
                 class="mt-1 w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-600">
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-3 transition">
          Salvar nova senha
        </button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
