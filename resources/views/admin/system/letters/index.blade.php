@extends('admin.layouts.app')

@section('title', 'Cartas de confirmação')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Cartas de confirmação')

@section('top_actions')
    <a href="{{ route('admin.system.letters.create', $event) }}"
       class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
        Nova carta
    </a>
@endsection

@section('content')

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
        <div class="p-5 border-b border-zinc-800">
            <div class="text-sm text-zinc-400">
                Aqui você cadastra os modelos de carta (HTML) e vincula por categoria(s) e status da inscrição.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-950/40 text-zinc-300">
                <tr>
                    <th class="text-left font-semibold px-4 py-3">Descrição</th>
                    <th class="text-left font-semibold px-4 py-3">Categorias</th>
                    <th class="text-left font-semibold px-4 py-3">Status</th>
                    <th class="text-left font-semibold px-4 py-3">Idioma</th>
                    <th class="text-left font-semibold px-4 py-3">Atualizado</th>
                    <th class="text-right font-semibold px-4 py-3">Ações</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                @forelse($letters as $letter)
                    @php
                        // cat_id agora é JSON array
                        $catIds = is_array($letter->cat_id)
                            ? $letter->cat_id
                            : (json_decode($letter->cat_id ?? '[]', true) ?: []);

                        $catIds = array_values(array_unique(array_map('intval', $catIds)));

                        // pega categorias do evento (carrega no controller com $event->load('categories'))
                        $eventCats = $event->categories ?? collect();

                        $catNames = collect($catIds)
                            ->map(function ($id) use ($eventCats) {
                                $cat = $eventCats->firstWhere('cat_id', $id);
                                return $cat->cat_nome ?? $cat->name ?? null;
                            })
                            ->filter()
                            ->values();

                        $statusLabel = [
                            'S' => 'Aprovado',
                            'E' => 'Em análise',
                            'R' => 'Reprovado',
                            'N' => 'Excluído',
                        ][$letter->car_tipo] ?? null;

                        $langLabel = [
                            'pt' => 'Português',
                            'en' => 'English',
                            'es' => 'Español',
                        ][$letter->car_trad] ?? null;
                    @endphp

                    <tr class="hover:bg-zinc-950/30">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-zinc-100">{{ $letter->car_descricao }}</div>
                            <div class="text-xs text-zinc-400 mt-0.5">{{ $letter->car_assunto }}</div>
                        </td>

                        <td class="px-4 py-3">
                            @if($catNames->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($catNames as $nm)
                                        <span
                                            class="text-xs rounded-full border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-zinc-300">
                            {{ $nm }}
                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-zinc-500">(nenhuma)</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
            <span class="text-xs rounded-full border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-zinc-300">
                {{ $statusLabel ?: 'Qualquer' }}
            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-xs text-zinc-300">{{ $langLabel ?: '-' }}</span>
                        </td>

                        <td class="px-4 py-3 text-xs text-zinc-400">
                            {{ optional($letter->updated_at)->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.system.letters.edit', [$event, $letter]) }}"
                               class="inline-flex items-center rounded-xl border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-3 py-2 text-sm transition">
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-zinc-400">
                            Nenhuma carta cadastrada ainda.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($letters, 'links'))
            <div class="p-5 border-t border-zinc-800">
                {{ $letters->links() }}
            </div>
        @endif
    </div>

@endsection
