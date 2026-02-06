@extends('admin.layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard', $event) }}" class="text-sm text-zinc-400 hover:text-white">←
                    voltar</a>

                <h1 class="mt-2 text-2xl font-semibold text-white">
                    Campos da ficha: <span class="text-zinc-300">{{ $form->name ?? ('#'.$form->id) }}</span>
                </h1>
                <p class="mt-1 text-sm text-zinc-400">
                    Aqui você edita os campos da ficha e vê uma prévia em tempo real.
                </p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.system.forms.fields.create', [$event, $form]) }}"
                   class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    + Novo campo
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Preview --}}
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <div class="text-sm font-semibold text-white">Prévia da ficha</div>
                    <div class="text-xs text-zinc-400">Somente visual</div>
                </div>

                @include('admin.system.form_fields.partials.preview', ['form' => $form])
            </div>

            {{-- Config / Lista + Presets --}}
            <div class="space-y-4">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
                    <div class="text-sm font-semibold text-white">Campos cadastrados</div>

                    @if($form->fields->isEmpty())
                        <div class="mt-3 text-sm text-zinc-400">
                            Nenhum campo ainda. Clique em <b>Novo campo</b> ou adicione um campo padrão abaixo.
                        </div>
                    @else
                        <div class="mt-3 divide-y divide-zinc-800">
                            @foreach($form->fields->sortBy('sort_order') as $field)
                                <div class="py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-white font-medium truncate">
                                            {{ $field->label }}
                                            @if($field->required)
                                                <span class="text-rose-400">*</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-zinc-400">
                                            key: <span class="text-zinc-300">{{ $field->key }}</span>
                                            · tipo: <span class="text-zinc-300">{{ $field->type }}</span>
                                            @if(!empty($field->maps_to_column))
                                                · coluna: <span class="text-zinc-300">{{ $field->maps_to_column }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex gap-2 shrink-0">
                                        <a class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-white hover:bg-zinc-800"
                                           href="{{ route('admin.system.forms.fields.edit', [$event, $form, $field]) }}">
                                            Editar
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.system.forms.fields.destroy', [$event, $form, $field]) }}"
                                              onsubmit="return confirm('Remover este campo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-rose-300 hover:bg-zinc-800">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
                    <div class="text-sm font-semibold text-white">Campos padrões (presets)</div>
                    <p class="mt-1 text-xs text-zinc-400">
                        Selecione um campo padrão e ele já entra na ficha com key e tipo corretos.
                    </p>

                    @php
                        $presetsList = $presets ?? collect();

                        // Agrupa por "group" (fallback pra "Outros")
                        $groupedPresets = $presetsList instanceof \Illuminate\Support\Collection
                            ? $presetsList->groupBy(fn($p) => trim((string)($p->group ?? '')) !== '' ? (string)$p->group : 'Outros')
                            : collect($presetsList)->groupBy(fn($p) => trim((string)($p->group ?? '')) !== '' ? (string)$p->group : 'Outros');

                        /**
                         * LÓGICA "DOENTE" CERTA:
                         * - se apagou ins_adicional26, o próximo volta a ser 26
                         * - acha o menor número disponível de 1..30
                         */
                        $usedAdditional = [];
                        foreach(($form->fields ?? []) as $f) {
                            $k = (string)($f->key ?? '');
                            if (preg_match('/^ins_adicional(\d+)$/', $k, $m)) {
                                $usedAdditional[(int)$m[1]] = true;
                            }
                        }

                        $nextAdditional = null;
                        for ($i = 1; $i <= 30; $i++) {
                            if (!isset($usedAdditional[$i])) {
                                $nextAdditional = $i;
                                break;
                            }
                        }
                    @endphp

                    @if($presetsList->isEmpty())
                        <div class="mt-3 text-sm text-zinc-400">
                            Você ainda não criou os presets.
                        </div>
                    @else
                        <div class="mt-4 space-y-4">
                            @foreach($groupedPresets as $groupName => $items)
                                @php
                                    // pega todos presets do tipo ins_adicional1..30
                                    $additionalPresets = collect($items)->filter(fn($it) => preg_match('/^ins_adicional(\d+)$/', (string)$it->key));

                                    // remove eles da listagem normal
                                    $normalItems = collect($items)->reject(fn($it) => preg_match('/^ins_adicional(\d+)$/', (string)$it->key));

                                    // acha o preset exato do próximo disponível (ex: ins_adicional26)
                                    $nextAdditionalPreset = $nextAdditional
                                        ? $additionalPresets->first(fn($p) => (string)$p->key === ('ins_adicional' . $nextAdditional))
                                        : null;
                                @endphp

                                <div class="rounded-xl border border-zinc-800 bg-zinc-950/30">
                                    <div class="px-3 py-2 border-b border-zinc-800 flex items-center justify-between">
                                        <div class="text-xs font-semibold text-zinc-200 uppercase tracking-wide">
                                            {{ $groupName }}
                                        </div>

                                        <div class="text-[11px] text-zinc-500">
                                            {{ $normalItems->count() + ($additionalPresets->isNotEmpty() ? 1 : 0) }} itens
                                        </div>
                                    </div>

                                    <div class="divide-y divide-zinc-800">
                                        {{-- Lista normal --}}
                                        @foreach($normalItems as $p)
                                            <div class="p-3 flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-white truncate">
                                                        {{ $p->label }}
                                                    </div>
                                                    <div class="mt-0.5 text-xs text-zinc-400">
                                                        key: <span class="text-zinc-300">{{ $p->key }}</span>
                                                        · tipo: <span class="text-zinc-300">{{ $p->type }}</span>
                                                    </div>
                                                </div>

                                                <form method="POST"
                                                      action="{{ route('admin.system.forms.fields.addPreset', [$event, $form, $p]) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="shrink-0 rounded-lg bg-zinc-800 px-3 py-1.5 text-sm text-white hover:bg-zinc-700">
                                                        Adicionar
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach

                                        {{-- Linha especial: Extras (1 botão só) --}}
                                        @if($additionalPresets->isNotEmpty())
                                            <div class="p-3 flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-white truncate">
                                                        Extras
                                                        <span class="text-xs text-zinc-400 font-normal">
                                                            @if($nextAdditional)
                                                                (vaga: ins_adicional{{ $nextAdditional }})
                                                            @else
                                                                (limite atingido 1–30)
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="mt-0.5 text-xs text-zinc-400">
                                                        @if($nextAdditionalPreset)
                                                            vai adicionar: <span class="text-zinc-300">{{ $nextAdditionalPreset->label }}</span>
                                                            · tipo: <span class="text-zinc-300">{{ $nextAdditionalPreset->type }}</span>
                                                        @else
                                                            @if($nextAdditional === null)
                                                                sem vagas disponíveis (30/30)
                                                            @else
                                                                preset do ins_adicional{{ $nextAdditional }} não encontrado neste grupo
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($nextAdditionalPreset)
                                                    <form method="POST"
                                                          action="{{ route('admin.system.forms.fields.addPreset', [$event, $form, $nextAdditionalPreset]) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="shrink-0 rounded-lg bg-zinc-800 px-3 py-1.5 text-sm text-white hover:bg-zinc-700">
                                                            Adicionar +1
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button"
                                                            class="shrink-0 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm text-zinc-500 border border-zinc-800 cursor-not-allowed"
                                                            disabled>
                                                        Adicionar +1
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        @if($normalItems->isEmpty() && $additionalPresets->isEmpty())
                                            <div class="p-3 text-sm text-zinc-400">
                                                Nenhum preset neste grupo.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        (function () {
            const el = document.getElementById('preview-sortable');
            if (!el) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const saveOrder = async () => {
                const order = Array.from(el.querySelectorAll('[data-field-id]'))
                    .map(x => Number(x.getAttribute('data-field-id')))
                    .filter(Boolean);

                const res = await fetch(@json(route('admin.system.forms.fields.reorder', [$event, $form])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf || ''
                    },
                    body: JSON.stringify({order})
                });

                if (!res.ok) {
                    console.error('Falha ao salvar ordem', await res.text());
                }
            };

            new Sortable(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'opacity-40',
                onEnd: function () {
                    saveOrder();
                }
            });
        })();
    </script>
@endsection
