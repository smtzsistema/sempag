@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.dashboard', $event) }}" class="text-sm text-zinc-400 hover:text-white">← voltar</a>

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
                                        @if($field->required) <span class="text-rose-400">*</span> @endif
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

                                    <form method="POST" action="{{ route('admin.system.forms.fields.destroy', [$event, $form, $field]) }}"
                                          onsubmit="return confirm('Remover este campo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-rose-300 hover:bg-zinc-800">
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

                @if(empty($presets) || count($presets) === 0)
                    <div class="mt-3 text-sm text-zinc-400">
                        Você ainda não criou os presets.
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-zinc-400">
                                <tr class="border-b border-zinc-800">
                                    <th class="py-2 text-left font-medium">Campo</th>
                                    <th class="py-2 text-left font-medium">Grupo</th>
                                    <th class="py-2 text-left font-medium">Tipo</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="text-zinc-200">
                                @foreach($presets as $p)
                                    <tr class="border-b border-zinc-800">
                                        <td class="py-2">
                                            <div class="font-medium text-white">{{ $p->label }}</div>
                                            <div class="text-xs text-zinc-400">key: {{ $p->key }}</div>
                                        </td>
                                        <td class="py-2">{{ $p->group }}</td>
                                        <td class="py-2">{{ $p->type }}</td>
                                        <td class="py-2 text-right">
                                            <form method="POST" action="{{ route('admin.system.forms.fields.addPreset', [$event, $form, $p]) }}">
                                                @csrf
                                                <button class="rounded-lg bg-zinc-800 px-3 py-1.5 text-sm text-white hover:bg-zinc-700">
                                                    Adicionar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
            body: JSON.stringify({ order })
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
