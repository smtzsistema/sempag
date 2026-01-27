<div class="mt-4 space-y-3">

    {{-- FIXOS (não entram no sortable) --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
            <label class="block text-xs font-medium text-zinc-300 mb-1">E-mail</label>
            <input type="email" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white"
                   placeholder="seuemail@dominio.com" disabled>
        </div>
        <div>
            <label class="block text-xs font-medium text-zinc-300 mb-1">Confirme seu e-mail</label>
            <input type="email" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white"
                   placeholder="repita o e-mail" disabled>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
            <label class="block text-xs font-medium text-zinc-300 mb-1">Senha</label>
            <input type="password" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white"
                   placeholder="••••••••" disabled>
        </div>
        <div>
            <label class="block text-xs font-medium text-zinc-300 mb-1">Confirme sua senha</label>
            <input type="password" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white"
                   placeholder="••••••••" disabled>
        </div>
    </div>

    {{-- DINÂMICOS (só aqui pode arrastar) --}}
    <div class="border-t border-zinc-800 pt-4 space-y-3" id="preview-sortable">
        @foreach($form->fields->sortBy('sort_order') as $field)
            <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-3"
                 data-field-id="{{ $field->getKey() }}">

                <div class="flex items-start gap-3">
                    <button type="button"
                            class="drag-handle mt-1 select-none text-zinc-500 hover:text-zinc-200"
                            title="Arrastar">
                        ☰
                    </button>

                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-zinc-200 mb-1">
                            {{ $field->label }} @if($field->required)<span class="text-rose-400">*</span>@endif
                        </label>

                        @if($field->type === 'textarea')
                            <textarea class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white"
                                      rows="3" disabled></textarea>
                        @elseif($field->type === 'select')
                            <select class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white" disabled>
                                <option>Selecione…</option>
                            </select>
                        @else
                            <input class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white"
                                   type="text" disabled />
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($form->fields->isEmpty())
            <div class="text-sm text-zinc-400">Nenhum campo na ficha ainda.</div>
        @endif
    </div>

    <button class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white opacity-70" disabled>
        Enviar inscrição
    </button>
</div>
