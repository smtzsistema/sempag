@extends('admin.layouts.app')

@section('title', 'Inscrições')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Lista de inscritos')

@section('top_actions')
    <button type="button" onclick="__openExportModal()"
            class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Exportar CSV
    </button>
@endsection

@section('content')

@php
    $statusLabel = fn($v) => match($v) {
        'S' => 'Aprovado',
        'E' => 'Em análise',
        'R' => 'Reprovado',
        'N' => 'Excluído',
        default => '—',
    };

    $statusPill = fn($v) => match($v) {
        'S' => 'border-emerald-700/60 bg-emerald-950/30 text-emerald-300',
        'E' => 'border-amber-700/60 bg-amber-950/30 text-amber-300',
        'R' => 'border-rose-700/60 bg-rose-950/30 text-rose-300',
        'N' => 'border-zinc-700 bg-zinc-950 text-zinc-400',
        default => 'border-zinc-700 bg-zinc-950 text-zinc-300',
    };

    $fullName = function($r) {
        $n = trim(($r->ins_nome ?? '').' '.($r->ins_sobrenome ?? ''));
        return $n !== '' ? $n : '—';
    };
@endphp

{{-- Filtros (padrão novo) --}}
<div class="mt-2 rounded-2xl bg-zinc-900 border border-zinc-800 p-4">
    <form method="GET" class="grid md:grid-cols-12 gap-3">
        <div class="md:col-span-3">
            <label class="block text-xs text-zinc-400 mb-1">Status</label>
            <select name="status" class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                <option value="">Todos</option>
                <option value="S" @selected($status==='S')>Aprovado</option>
                <option value="E" @selected($status==='E')>Em análise</option>
                <option value="R" @selected($status==='R')>Reprovado</option>
                @can('registrations.delete')
                    <option value="N" @selected($status==='N')>Excluído</option>
                @endcan
            </select>
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs text-zinc-400 mb-1">Categoria</label>
            <select name="cat_id" class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                <option value="">Todas</option>
                @foreach($categories as $c)
                    <option value="{{ $c->cat_id }}" @selected((string)$catId === (string)$c->cat_id)>
                        {{ $c->cat_nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs text-zinc-400 mb-1">Busca</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Nome, e-mail, CPF, instituição..."
                   class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
        </div>

        <div class="md:col-span-1 flex items-end">
            <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-2 transition">
                Filtrar
            </button>
        </div>
    </form>
</div>

{{-- Lista --}}
<div class="mt-6">

    {{-- MOBILE: cards --}}
    <div class="grid gap-3 md:hidden">
        @forelse($registrations as $r)
            <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-base font-semibold">{{ $fullName($r) }}</div>
                        <div class="text-sm text-zinc-300 break-all">{{ $r->ins_email ?? '—' }}</div>
                    </div>

                </div>

                <div class="mt-3 text-sm text-zinc-300">
                    <div><span class="text-zinc-500">Categoria (cat_id):</span> {{ $r->cat_id ?? '—' }}</div>
                    <div><span class="text-zinc-500">Criado:</span> {{ optional($r->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <a class="text-emerald-300 hover:underline" href="{{ route('admin.registrations.show', [$event, $r]) }}">Ver</a>
                    <a class="text-zinc-200 hover:underline" href="{{ route('admin.registrations.edit', [$event, $r]) }}">Editar</a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 text-zinc-400">
                Nenhum resultado.
            </div>
        @endforelse
    </div>

    {{-- DESKTOP: table moderna --}}
    <div class="hidden md:block rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="text-zinc-400 bg-zinc-950/40">
                <tr>
                    <th class="text-left font-medium p-3">Nome</th>
                    <th class="text-left font-medium p-3">E-mail</th>
                    <th class="text-left font-medium p-3">Categoria</th>
                    <th class="text-left font-medium p-3">Status</th>
                    <th class="text-left font-medium p-3">Criado</th>
                    <th class="text-right font-medium p-3">Ações</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-zinc-800">
                @forelse($registrations as $r)
                    <tr class="hover:bg-zinc-950/30 transition">
                        <td class="p-3 font-medium">{{ $fullName($r) }}</td>
                        <td class="p-3 text-zinc-300">{{ $r->ins_email ?? '—' }}</td>
                        <td class="p-3 text-zinc-300">{{ $r->category?->cat_nome ?? '—' }}</td>
                        <td class="p-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border {{ $statusPill($r->ins_aprovado) }}">
                              {{ $statusLabel($r->ins_aprovado) }}
                            </span>
                        </td>
                        <td class="p-3 text-zinc-300">{{ optional($r->created_at)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            @can('registrations.view')
                            <a class="text-emerald-300 hover:underline" href="{{ route('admin.registrations.show', [$event, $r]) }}">Ver</a>
                            @endcan
                            <span class="text-zinc-700 mx-2">•</span>
                            @can('registrations.edit')
                            <a class="text-zinc-200 hover:underline" href="{{ route('admin.registrations.edit', [$event, $r]) }}">Editar</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-zinc-400">Nenhum resultado.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação mantendo filtros --}}
    <div class="mt-4">
        {{ $registrations->appends(request()->query())->links() }}
    </div>
</div>

{{-- Modal export CSV (categoria + status) --}}
<div id="exportModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60" onclick="__closeExportModal()"></div>

    <div class="relative mx-auto mt-16 w-[95%] max-w-2xl rounded-2xl bg-zinc-900 border border-zinc-800 shadow-2xl">
        <div class="p-5 border-b border-zinc-800 flex items-start justify-between gap-3">
            <div>
                <div class="text-lg font-semibold">Exportar relatório (CSV)</div>
                <div class="text-xs text-zinc-400 mt-1">Filtre por categoria e/ou status. Se não selecionar nada, exporta tudo daquele filtro.</div>
            </div>
            <button type="button" class="rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm" onclick="__closeExportModal()">
                Fechar
            </button>
        </div>

        <div class="p-5">
            @php
                $statusOptions = [
                    'S' => 'Aprovados',
                    'E' => 'Em análise',
                    'R' => 'Reprovados',
                    'N' => 'Excluídos',
                ];
            @endphp

            <form method="GET" action="{{ route('admin.registrations.exports.filtered', $event) }}" class="grid md:grid-cols-12 gap-4">

                <div class="md:col-span-7">
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-xs text-zinc-400">Categorias (multi)</label>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-xs text-emerald-300 hover:underline" onclick="__selAll('modal_cat_ids')">Selecionar todas</button>
                            <span class="text-zinc-700">•</span>
                            <button type="button" class="text-xs text-zinc-300 hover:underline" onclick="__selNone('modal_cat_ids')">Limpar</button>
                        </div>
                    </div>

                    <select id="modal_cat_ids" name="cat_ids[]" multiple size="8" class="mt-2 w-full rounded-2xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                        @foreach($categories as $c)
                            <option value="{{ $c->cat_id }}">{{ $c->cat_nome }} ({{ $c->cat_id }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-5">
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-xs text-zinc-400">Status (multi)</label>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-xs text-emerald-300 hover:underline" onclick="__selAll('modal_statuses')">Selecionar todos</button>
                            <span class="text-zinc-700">•</span>
                            <button type="button" class="text-xs text-zinc-300 hover:underline" onclick="__selNone('modal_statuses')">Limpar</button>
                        </div>
                    </div>

                    <select id="modal_statuses" name="statuses[]" multiple size="8" class="mt-2 w-full rounded-2xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                        @foreach($statusOptions as $k => $label)
                            <option value="{{ $k }}" @selected(in_array($k, ['S','E','R'], true))>{{ $label }} ({{ $k }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-12 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
                    <div class="text-xs text-zinc-500">CSV em UTF-8 com separador ";" (Excel PT-BR)</div>
                    <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2 transition">
                        Baixar CSV
                    </button>
                </div>
            </form>

            <div class="mt-4 text-xs text-zinc-500">
                Precisa de mais opções? Você também pode ir em <a class="text-emerald-300 hover:underline" href="{{ route('admin.registrations.exports.index', $event) }}">Exportar relatório (CSV)</a>.
            </div>
        </div>
    </div>
</div>

<script>
    function __openExportModal(){
        const m = document.getElementById('exportModal');
        if(!m) return;
        m.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function __closeExportModal(){
        const m = document.getElementById('exportModal');
        if(!m) return;
        m.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
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
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') __closeExportModal();
    });

    @if(request()->boolean('export'))
    document.addEventListener('DOMContentLoaded', () => {
        __openExportModal();
    });
    @endif
</script>

@endsection
