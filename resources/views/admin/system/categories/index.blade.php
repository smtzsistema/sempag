@extends('admin.layouts.app')

@section('title', 'Categorias')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Configuração de categorias')

@section('top_actions')
    <a href="{{ route('admin.system.categories.create', $event) }}"
       class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
        Nova categoria
    </a>
@endsection

@section('content')

<div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-auto">
    <table class="min-w-full text-sm">
        <thead class="text-zinc-400">
        <tr>
            <th class="text-left font-medium p-3">Nome</th>
            <th class="text-left font-medium p-3">Ativa</th>
            <th class="text-left font-medium p-3">Aprovação</th>
            <th class="text-right font-medium p-3">Ações</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800">
        @forelse($categories as $c)
            <tr>
                <td class="p-3">
                    <div class="font-semibold">{{ $c->name }}</div>
                    @if($c->description)
                        <div class="text-xs text-zinc-500 mt-1">{{ $c->description }}</div>
                    @endif
                </td>
                <td class="p-3">{{ $c->active ? 'Sim' : 'Não' }}</td>
                <td class="p-3">{{ $c->requires_approval ? 'Sim' : 'Não' }}</td>
                <td class="p-3 text-right">
                    <a class="text-emerald-300 hover:underline" href="{{ route('admin.system.categories.edit', [$event, $c]) }}">Editar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="p-4 text-zinc-400">Nenhuma categoria cadastrada.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
