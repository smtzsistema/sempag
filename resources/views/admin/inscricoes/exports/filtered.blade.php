@extends('admin.layouts.app')

@section('title', 'Exportar com filtros')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Exportar com filtros')

@section('content')

@php
    $statusOptions = [
        'S' => 'Aprovados',
        'E' => 'Em análise',
        'R' => 'Reprovados',
        'N' => 'Excluídos',
    ];

    $selectedCatIds = $selectedCatIds ?? [];
    $selectedStatuses = $selectedStatuses ?? ['S','E','R'];
@endphp

<div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
    <h2 class="text-lg font-semibold">Escolha os filtros</h2>
    <p class="text-sm text-zinc-400 mt-1">
        Dica: se você <span class="text-zinc-200 font-medium">não selecionar nada</span> em um filtro, ele exporta <span class="text-zinc-200 font-medium">tudo</span> daquele filtro.
    </p>

    <form method="GET" action="{{ route('admin.registrations.exports.filtered', $event) }}" class="mt-5 grid md:grid-cols-12 gap-4">

        <div class="md:col-span-7">
            <div class="flex items-center justify-between gap-3">
                <label class="block text-xs text-zinc-400">Categorias (multi)</label>
                <div class="flex items-center gap-2">
                    <button type="button" class="text-xs text-emerald-300 hover:underline" onclick="__selAll('cat_ids')">Selecionar todas</button>
                    <span class="text-zinc-700">•</span>
                    <button type="button" class="text-xs text-zinc-300 hover:underline" onclick="__selNone('cat_ids')">Limpar</button>
                </div>
            </div>

            <select id="cat_ids" name="cat_ids[]" multiple size="10" class="mt-2 w-full rounded-2xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                @foreach($categories as $c)
                    <option value="{{ $c->cat_id }}" @selected(in_array((int)$c->cat_id, array_map('intval', (array)$selectedCatIds), true))>
                        {{ $c->cat_nome }} ({{ $c->cat_id }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-5">
            <div class="flex items-center justify-between gap-3">
                <label class="block text-xs text-zinc-400">Status (multi)</label>
                <div class="flex items-center gap-2">
                    <button type="button" class="text-xs text-emerald-300 hover:underline" onclick="__selAll('statuses')">Selecionar todos</button>
                    <span class="text-zinc-700">•</span>
                    <button type="button" class="text-xs text-zinc-300 hover:underline" onclick="__selNone('statuses')">Limpar</button>
                </div>
            </div>

            <select id="statuses" name="statuses[]" multiple size="10" class="mt-2 w-full rounded-2xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                @foreach($statusOptions as $k => $label)
                    <option value="{{ $k }}" @selected(in_array($k, (array)$selectedStatuses, true))>
                        {{ $label }} ({{ $k }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-12 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
            <div class="text-xs text-zinc-500">
                CSV em UTF-8 com separador ";" (Excel PT-BR)
            </div>
            <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2 transition">
                Baixar CSV
            </button>
        </div>

    </form>

    <div class="mt-4 text-sm text-zinc-400">
        <a href="{{ route('admin.registrations.exports.index', $event) }}" class="text-emerald-300 hover:underline">← Voltar</a>
    </div>
</div>

<script>
    function __selAll(id){
        const el = document.getElementById(id);
        if(!el) return;
        Array.from(el.options).forEach(o => o.selected = true);
    }
    function __selNone(id){
        const el = document.getElementById(id);
        if(!el) return;
        Array.from(el.options).forEach(o => o.selected = false);
    }
</script>

@endsection
