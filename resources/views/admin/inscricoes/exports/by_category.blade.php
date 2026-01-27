@extends('admin.layouts.app')

@section('title', 'Exportar por categoria')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Exportar por categoria')

@section('content')

<div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
    <h2 class="text-lg font-semibold">Selecione a categoria</h2>
    <form method="GET" action="{{ route('admin.registrations.exports.byCategory', $event) }}" class="mt-4 grid md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
            <label class="block text-xs text-zinc-400 mb-1">Categoria</label>
            <select name="category_id" class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-2 transition">Baixar CSV</button>
        </div>
    </form>

    <div class="mt-4 text-sm text-zinc-400">
        <a href="{{ route('admin.registrations.exports.index', $event) }}" class="text-emerald-300 hover:underline">← Voltar</a>
    </div>
</div>

@endsection
