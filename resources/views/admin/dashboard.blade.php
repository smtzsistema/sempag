@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Admin • Dashboard')
@section('page_title')
    {{ $event->name }}
@endsection

@section('content')

<div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-4">
        <div class="text-sm text-zinc-400">Total</div>
        <div class="text-3xl font-bold mt-1">{{ $counts['total'] }}</div>
    </div>
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-4">
        <div class="text-sm text-zinc-400">Pendentes</div>
        <div class="text-3xl font-bold mt-1">{{ $counts['pending'] }}</div>
    </div>
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-4">
        <div class="text-sm text-zinc-400">Aprovadas</div>
        <div class="text-3xl font-bold mt-1">{{ $counts['approved'] }}</div>
    </div>
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-4">
        <div class="text-sm text-zinc-400">Reprovadas</div>
        <div class="text-3xl font-bold mt-1">{{ $counts['rejected'] }}</div>
    </div>
</div>

<div class="mt-8 space-y-6">
    @forelse($countsByCategory as $row)
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800">
            <div class="p-4 border-b border-zinc-800 flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm text-zinc-400">Categoria</div>
                    <div class="text-lg font-semibold">
                        {{ $row->category?->name ?? 'Sem categoria' }}
                    </div>
                </div>
                @can('registrations.view')
                <a class="text-sm text-emerald-300 hover:underline"
                   href="{{ route('admin.registrations.index', $event) }}?category_id={{ $row->category_id }}">
                    Ver inscrições
                </a>
                @endcan
            </div>

            <div class="p-4">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                        <div class="text-sm text-zinc-400">Total</div>
                        <div class="text-3xl font-bold mt-1">{{ (int) $row->total }}</div>
                    </div>

                    <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                        <div class="text-sm text-zinc-400">Pendentes</div>
                        <div class="text-3xl font-bold mt-1">{{ (int) $row->pending }}</div>
                    </div>

                    <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                        <div class="text-sm text-zinc-400">Aprovadas</div>
                        <div class="text-3xl font-bold mt-1">{{ (int) $row->approved }}</div>
                    </div>

                    <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                        <div class="text-sm text-zinc-400">Reprovadas</div>
                        <div class="text-3xl font-bold mt-1">{{ (int) $row->rejected }}</div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-zinc-400">
            Sem inscrições ainda.
        </div>
    @endforelse
</div>

<div class="mt-8 rounded-2xl bg-zinc-900 border border-zinc-800">
    <div class="p-4 border-b border-zinc-800 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Últimas inscrições</h2>
        @can('registations.view')
        <a href="{{ route('admin.registrations.index', $event) }}" class="text-sm text-emerald-300 hover:underline">
            Abrir lista
        </a>
        @endcan
    </div>

    <div class="overflow-auto">
        <table class="min-w-full text-sm">
            <thead class="text-zinc-400">
            <tr>
                <th class="text-left font-medium p-3">Nome</th>
                <th class="text-left font-medium p-3">E-mail</th>
                <th class="text-left font-medium p-3">Categoria</th>
                <th class="text-left font-medium p-3">Status</th>
                <th class="text-right font-medium p-3">Ações</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
            @forelse($latest as $r)
                <tr>
                    <td class="p-3">{{ $r->full_name ?? '—' }}</td>
                    <td class="p-3 text-zinc-300">{{ $r->email ?? '—' }}</td>
                    <td class="p-3 text-zinc-300">{{ $r->category?->name ?? '—' }}</td>
                    <td class="p-3">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-zinc-700 bg-zinc-950">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="p-3 text-right">
                        @can('registration.view')
                        <a class="text-emerald-300 hover:underline"
                           href="{{ route('admin.registrations.show', [$event, $r]) }}">
                            Ver
                        </a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-zinc-400">Sem inscrições ainda.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
