<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar foto - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-100">
@php
    $bannerPath = !empty($category?->banner_path) ? $category->banner_path : ($event->banner_path ?? null);
@endphp
@if($bannerPath)
    <div class="mx-auto max-w-[1200px]">
        <div class="w-full h-[200px] flex items-center justify-center rounded-2xl bg-zinc-950">
            <img src="{{ asset('storage/'.$bannerPath) }}" alt="Banner" class="max-w-full max-h-full object-contain">
        </div>
    </div>
@endif

<div class="max-w-3xl mx-auto p-6">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Editar foto</h1>
                <p class="text-zinc-400 text-sm mt-1">{{ $event->name }}</p>
            </div>

            <a href="{{ route('public.attendee.area', $event) }}"
               class="rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-4 py-2 font-semibold">
                Voltar
            </a>
        </div>

        @if(session('ok'))
            <div class="mt-4 p-3 rounded-lg bg-emerald-900/25 border border-emerald-800 text-emerald-200">
                {{ session('ok') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 p-3 rounded-lg bg-red-900/25 border border-red-800 text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-6 grid md:grid-cols-2 gap-6 items-start">
            <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                <div class="text-sm text-zinc-400">Foto atual</div>
                <div class="mt-3">
                    @if(!empty($registration->photo_url))
                        <img src="{{ $registration->photo_url }}" alt="Foto" class="w-full max-w-[320px] aspect-square rounded-2xl object-cover border border-zinc-800">
                    @else
                        <div class="w-full max-w-[320px] aspect-square rounded-2xl bg-zinc-900/40 border border-zinc-800 flex items-center justify-center text-zinc-500 text-sm">
                            Sem foto
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <form method="POST" action="{{ route('public.attendee.photo.destroy', $event) }}"
                          onsubmit="return confirm('Remover a foto atual?');">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-red-600 hover:bg-red-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
                            Remover foto
                        </button>
                    </form>
                    <p class="text-xs text-zinc-500 mt-2">Ao remover, ela some do sistema (a gente só desativa no banco).</p>
                </div>
            </div>

            <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                <div class="text-sm font-semibold">Enviar nova foto</div>
                <p class="text-xs text-zinc-400 mt-1">Obrigatório: JPG/PNG. Máx 10MB.</p>

                <form method="POST" enctype="multipart/form-data" action="{{ route('public.attendee.photo.update', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="file" name="photo" required accept="image/png,image/jpeg"
                           class="w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2">

                    <button type="submit"
                            class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
                        Salvar foto
                    </button>
                </form>

                <div class="mt-4 text-xs text-zinc-500">
                    Dica: use uma foto quadrada (tipo 1:1) pra ficar certinho na credencial.
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
