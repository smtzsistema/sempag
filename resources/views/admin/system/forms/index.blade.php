@extends('admin.layouts.app')

@section('title', 'Fichas')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Configuração de fichas')

@section('top_actions')
    <a href="{{ route('admin.system.forms.create', $event) }}"
       class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
        Nova ficha
    </a>
@endsection

@section('content')

<div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-auto">
    <table class="min-w-full text-sm">
        <thead class="text-zinc-400">
        <tr>
            <th class="text-left font-medium p-3">Ficha</th>
            <th class="text-left font-medium p-3">Categoria</th>
            <th class="text-left font-medium p-3">Versão</th>
            <th class="text-left font-medium p-3">Ativa</th>
            <th class="text-right font-medium p-3">Ações</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800">
        @forelse($forms as $f)
            <tr>
                <td class="p-3">
                    <div class="font-semibold">{{ $f->name }}</div>
                    <div class="text-xs text-zinc-500">ID: {{ $f->id }}</div>
                </td>
                <td class="p-3">{{ $f->category?->name ?? '—' }}</td>
                <td class="p-3">{{ $f->version }}</td>
                <td class="p-3">{{ $f->active ? 'Sim' : 'Não' }}</td>
                <td class="p-3 text-right">
                    <a class="text-emerald-300 hover:underline" href="{{ route('admin.system.forms.edit', [$event, $f]) }}">Editar</a>
                    <span class="text-zinc-700">•</span>
                    <a class="text-zinc-200 hover:underline" href="{{ route('admin.system.forms.fields.index', [$event, $f]) }}">Campos</a>
                    <span class="text-zinc-700">•</span>
                    <a class="text-sky-200 hover:underline" href="{{ route('admin.system.forms.create', [$event, 'clone_form_id' => $f->form_id]) }}">Clonar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="p-4 text-zinc-400">Nenhuma ficha cadastrada.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
