@php
    $mode = $mode ?? 'create';
    $letter = $letter ?? null;
    $selectedCategories = $selectedCategories ?? [];
    $action = $action ?? 'admin.system.letters.store';

    $input = function(string $name, $default = null) use ($letter) {
        return old($name, $letter?->{$name} ?? $default);
    };

    $selectedCategories = array_map('intval', $selectedCategories);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="grid gap-4 md:grid-cols-12">
            <div class="md:col-span-5">
                <label class="block text-xs text-zinc-400 mb-1">Descrição (uso interno)</label>
                <input name="car_descricao" value="{{ $input('car_descricao') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                @error('car_descricao')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-5">
                <label class="block text-xs text-zinc-400 mb-1">Assunto</label>
                <input name="car_assunto" value="{{ $input('car_assunto') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                @error('car_assunto')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-zinc-400 mb-1">Idioma</label>

                <select name="car_trad"
                        class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                    <option value="" {{ $input('car_trad') === '' ? 'selected' : '' }}>Selecione</option>
                    <option value="pt" {{ $input('car_trad') === 'pt' ? 'selected' : '' }}>Português</option>
                    <option value="en" {{ $input('car_trad') === 'en' ? 'selected' : '' }}>English</option>
                    <option value="es" {{ $input('car_trad') === 'es' ? 'selected' : '' }}>Español</option>
                </select>

                @error('car_trad')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-6">
                <label class="block text-xs text-zinc-400 mb-1">Categorias</label>

                {{-- Chips selecionados --}}
                <div id="catChips"
                     class="flex flex-wrap gap-2 rounded-xl bg-zinc-950 border border-zinc-800 p-2 min-h-[44px]">
                    @foreach($categories as $c)
                        @php $cid = (int) $c->cat_id; @endphp
                        @if(in_array($cid, $selectedCategories, true))
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-zinc-900 border border-zinc-700 px-3 py-1 text-sm"
                                data-chip="{{ $cid }}">
                    {{ $c->cat_nome ?? $c->name }}
                    <button type="button"
                            class="text-zinc-400 hover:text-zinc-100"
                            onclick="removeCategory({{ $cid }})"
                            aria-label="Remover">
                        ✕
                    </button>
                    <input type="hidden" name="category_ids[]" value="{{ $cid }}" data-hidden="{{ $cid }}">
                </span>
                        @endif
                    @endforeach
                </div>

                {{-- Busca --}}
                <div class="mt-2">
                    <input id="catSearch" type="text"
                           placeholder="Buscar categoria"
                           class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                </div>

                {{-- Lista clicável --}}
                <div id="catList"
                     class="mt-2 max-h-56 overflow-auto rounded-xl bg-zinc-950 border border-zinc-800">
                    @foreach($categories as $c)
                        @php $cid = (int) $c->cat_id; $label = $c->cat_nome ?? $c->name; @endphp
                        <button type="button"
                                class="w-full text-left px-3 py-2 hover:bg-zinc-900 border-b border-zinc-900 last:border-b-0"
                                data-id="{{ $cid }}"
                                data-label="{{ e($label) }}"
                                onclick="addCategory({{ $cid }}, @js($label))">
                            <span class="text-sm">{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="text-xs text-zinc-500 mt-1"><b>Clique nas categorias pra adicionar. Pra remover, aperta o ✕</b>
                </div>
                @error('category_ids')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <script>
                function isSelected(id) {
                    return !!document.querySelector(`[data-hidden="${id}"]`);
                }

                function addCategory(id, label) {
                    if (isSelected(id)) return;

                    const chips = document.getElementById('catChips');

                    const span = document.createElement('span');
                    span.className = 'inline-flex items-center gap-2 rounded-full bg-zinc-900 border border-zinc-700 px-3 py-1 text-sm';
                    span.setAttribute('data-chip', id);

                    span.innerHTML = `
            ${escapeHtml(label)}
            <button type="button" class="text-zinc-400 hover:text-zinc-100" aria-label="Remover">✕</button>
            <input type="hidden" name="category_ids[]" value="${id}" data-hidden="${id}">
        `;

                    span.querySelector('button').addEventListener('click', () => removeCategory(id));

                    chips.appendChild(span);
                }

                function removeCategory(id) {
                    const chip = document.querySelector(`[data-chip="${id}"]`);
                    if (chip) chip.remove();
                }

                function escapeHtml(str) {
                    return String(str)
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                }

                // filtro de busca
                document.addEventListener('DOMContentLoaded', () => {
                    const search = document.getElementById('catSearch');
                    const list = document.getElementById('catList');

                    search.addEventListener('input', () => {
                        const q = search.value.trim().toLowerCase();
                        list.querySelectorAll('button[data-id]').forEach(btn => {
                            const label = (btn.getAttribute('data-label') || '').toLowerCase();
                            btn.style.display = label.includes(q) ? '' : 'none';
                        });
                    });
                });
            </script>


            <div class="md:col-span-3">
                <label class="block text-xs text-zinc-400 mb-1">Status (opcional)</label>

                <select name="car_tipo" class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                    <option value="" @selected($input('car_tipo') === '')>Qualquer</option>
                    <option value="S" @selected($input('car_tipo') === 'S')>Aprovado</option>
                    <option value="E" @selected($input('car_tipo') === 'E')>Em análise</option>
                    <option value="R" @selected($input('car_tipo') === 'R')>Reprovado</option>
                    <option value="N" @selected($input('car_tipo') === 'N')>Excluído</option>
                </select>

                @error('car_tipo')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs text-zinc-400 mb-1">Reply-To (opcional)</label>
                <input name="car_responderto" value="{{ $input('car_responderto') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2"
                       placeholder="email@dominio.com">
                @error('car_responderto')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-6">
                <label class="block text-xs text-zinc-400 mb-1">Cópia (TO adicional, opcional)</label>
                <input name="car_copia" value="{{ $input('car_copia') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2"
                       placeholder="email1@... , email2@...">
                @error('car_copia')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-6">
                <label class="block text-xs text-zinc-400 mb-1">Cópia (CC, opcional)</label>
                <input name="car_copiac" value="{{ $input('car_copiac') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2"
                       placeholder="email1@... , email2@...">
                @error('car_copiac')
                <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-semibold">Texto (HTML)</div>
                <div class="text-xs text-zinc-400 mt-1">Editor rico: dá pra colocar imagem por URL, link, tabela, etc.
                </div>
            </div>
        </div>

        <div class="mt-4">
            <textarea id="car_texto" name="car_texto" rows="14"
                      class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">{{ $input('car_texto') }}</textarea>
            @error('car_texto')
            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-3 items-center">
        <button
            class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2.5 transition">
            Salvar
        </button>

        <a href="{{ route('admin.system.letters.index', $event) }}"
           class="rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-zinc-800 px-5 py-2.5 transition">
            Cancelar
        </a>

        @if($mode === 'edit' && $letter)
            <button type="button"
                    onclick="if(confirm('Excluir esta carta?')) document.getElementById('deleteLetterForm')?.submit();"
                    class="ml-auto rounded-xl border border-red-900/60 bg-red-950/40 hover:bg-red-950 px-5 py-2.5 text-red-200 transition">
                Excluir
            </button>
        @endif
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.tinymce) return;

        tinymce.init({
            selector: '#car_texto',
            height: 520,
            menubar: false,
            plugins: 'link image lists table code autoresize',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
            branding: false,
            convert_urls: false,
        });
    });
</script>
