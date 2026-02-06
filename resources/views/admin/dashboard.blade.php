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
                           href="{{ route('admin.registrations.index', $event) }}?cat_id={{ $row->cat_id }}">
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

    @can('system.manage')
        <div class="mt-8 rounded-2xl bg-zinc-900 border border-zinc-800">
            <div class="p-4 border-b border-zinc-800 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Mural de avisos</h2>
                    <p class="text-sm text-zinc-400">Aqui fica visivel se falta configurar algo dentro do sistema.</p>
                </div>

                <div class="flex gap-2">
                    @can('system.manage')
                        <a href="{{ route('admin.system.forms.index', $event) }}"
                           class="text-sm rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 hover:border-zinc-700">
                            Fichas
                        </a>
                    @endcan

                    @can('system.manage')
                        <a href="{{ route('admin.system.letters.index', $event) }}"
                           class="text-sm rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 hover:border-zinc-700">
                            Cartas
                        </a>
                    @endcan

                    @can('system.manage')
                        <a href="{{ route('admin.system.credentials.index', $event) }}"
                           class="text-sm rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 hover:border-zinc-700">
                            Credenciais
                        </a>
                    @endcan
                </div>
            </div>

            <div class="overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-zinc-400">
                    <tr>
                        <th class="text-left font-medium p-3">Categoria</th>
                        <th class="text-left font-medium p-3">Ficha</th>
                        <th class="text-left font-medium p-3">Cartas</th>
                        <th class="text-left font-medium p-3">Credencial</th>
                        <th class="text-left font-medium p-3">Avisos</th>
                        <th class="text-right font-medium p-3">Status</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-800">
                    @forelse($noticeBoard as $row)
                        @php
                        $cat = $row['category'];

                        $requiredLetters = $row['letters_required'] ?? [];
                        $missingLetters  = $row['letters_missing'] ?? [];
                            $letterLabel = [
                                'E' => 'Em análise',
                                'S' => 'Aprovado',
                                'R' => 'Reprovado',
                            ];

                            $requiredText = implode(' / ', array_map(fn($s) => $letterLabel[$s] ?? $s, $requiredLetters));
                            $missingText  = implode(', ', array_map(fn($s) => $letterLabel[$s] ?? $s, $missingLetters));
                        @endphp

                        <tr>
                            <td class="p-3">
                                <div class="font-medium text-zinc-100">{{ $cat->name ?? $cat->cat_nome ?? '—' }}</div>
                                <div class="text-xs text-zinc-500">
                                    {{ $cat->requiresApproval ? 'Com aprova/reprova' : 'Sem aprova/reprova' }}
                                </div>
                            </td>

                            <td class="p-3">
                                @if($row['has_form'])
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-emerald-800 bg-emerald-900/20 text-emerald-200">
                                OK
                            </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-amber-800 bg-amber-900/20 text-amber-200">
                                Pendente
                            </span>
                                @endif
                            </td>

                            <td class="p-3">
                                @if(empty($missingLetters))
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-emerald-800 bg-emerald-900/20 text-emerald-200">
                                OK ({{ implode('/', array_map(fn($s)=>$letterLabel[$s] ?? $s, $requiredLetters)) }})
                            </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-amber-800 bg-amber-900/20 text-amber-200">
                                Falta: {{ implode(', ', array_map(fn($s)=>$letterLabel[$s] ?? $s, $missingLetters)) }}
                            </span>
                                @endif
                            </td>

                            <td class="p-3">
                                @if($row['has_credential'])
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-emerald-800 bg-emerald-900/20 text-emerald-200">
                                OK
                            </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-amber-800 bg-amber-900/20 text-amber-200">
                                Pendente
                            </span>
                                @endif
                            </td>

                            <td class="p-3 text-zinc-300">
                                @if($row['ok'])
                                    <span class="text-zinc-400">Tudo certo.</span>
                                @else
                                    <ul class="list-disc pl-4 space-y-1">
                                        @foreach(($row['issues'] ?? []) as $issue)
                                            <li>
                                                @if(!empty($issue['url']))
                                                    <a href="{{ $issue['url'] }}"
                                                       class="text-amber-200 hover:underline">
                                                        {{ $issue['text'] }}
                                                    </a>
                                                    @if(!empty($issue['action']))
                                                        <a href="{{ $issue['url'] }}"
                                                           class="ml-2 text-xs rounded-full px-2 py-0.5 border border-zinc-700 bg-zinc-950 text-zinc-200 hover:border-zinc-600">
                                                            {{ $issue['action'] }}
                                                        </a>
                                                    @endif
                                                @else
                                                    {{ $issue['text'] }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>

                            <td class="p-3 text-right">
                                @if($row['ok'])
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs border border-emerald-800 bg-emerald-900/20 text-emerald-200">
                                ✅ OK
                            </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs border border-amber-800 bg-amber-900/20 text-amber-200">
                                ⚠️ Atenção
                            </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-zinc-400">Nenhuma categoria cadastrada.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcan

    <div class="mt-8 rounded-2xl bg-zinc-900 border border-zinc-800">
        <div class="p-4 border-b border-zinc-800 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Últimas inscrições</h2>
            @can('registrations.view')
                <a href="{{ route('admin.registrations.index', $event) }}"
                   class="text-sm text-emerald-300 hover:underline">
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
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border border-zinc-700 bg-zinc-950">
                            {{ $r->status }}
                        </span>
                        </td>
                        <td class="p-3 text-right">
                            @can('registrations.view')
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
