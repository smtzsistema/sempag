@php
    $isEdit = isset($field) && $field->exists;
    $currentKey = old('key', $field->key ?? ($field->fic_nome ?? ''));
    $currentEditable = old('editable_by_attendee', ($field->editable ?? false) ? 1 : 0);

    // Options atuais (sempre trabalhamos com ARRAY de strings no sistema)
    $optsRaw = old('options_text', null);
    if ($optsRaw === null) {
        $opts = $field->options ?? ($field->fic_opcoes ?? null);
        if (is_string($opts)) {
            $decoded = json_decode($opts, true);
            $opts = is_array($decoded) ? $decoded : null;
        }
        $optsRaw = is_array($opts) ? json_encode(array_values($opts), JSON_UNESCAPED_UNICODE) : '';
    }
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-xs font-semibold text-zinc-200 mb-1">Label</label>
            <input name="label" value="{{ old('label', $field->label ?? '') }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white"
                   required>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-xs font-semibold text-zinc-200 mb-1">Tipo</label>
            <select name="type"
                    id="ff_type"
                    class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white">
                @foreach(['text','email','number','textarea','select','multiselect','checkbox','cpf','cnpj','cep','password'] as $t)
                    <option value="{{ $t }}" @selected(old('type', $field->type ?? 'text') === $t)>
                        {{ $t }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-zinc-200 mb-1">Ordem</label>
            <input type="number" name="order" value="{{ old('order', $field->order ?? 0) }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white">
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="required" value="1"
                       class="rounded border-zinc-700 bg-zinc-900"
                       @checked(old('required', $field->required ?? false))>
                <span class="text-sm text-white">Obrigatório</span>
            </label>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-xs font-semibold text-zinc-200 mb-1">Editável via Área do inscrito</label>
            <select name="editable_by_attendee"
                    class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white">
                <option value="0" @selected((string)$currentEditable === '0')>Não</option>
                <option value="1" @selected((string)$currentEditable === '1')>Sim</option>
            </select>
        </div>

        <div class="hidden sm:block"></div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-xs font-semibold text-zinc-200 mb-1">Placeholder</label>
            <input name="placeholder" value="{{ old('placeholder', $field->placeholder ?? '') }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white">
        </div>

        <div>
            <label class="block text-xs font-semibold text-zinc-200 mb-1">Help text</label>
            <input name="help_text" value="{{ old('help_text', $field->help_text ?? '') }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white">
        </div>
    </div>

    <div id="ff_options_wrap" class="hidden">
        <label class="block text-xs font-semibold text-zinc-200 mb-1">Opções (select / multiselect)</label>

        {{-- Campo real enviado pro backend (string). A UI monta um JSON array aqui dentro. --}}
        <textarea name="options_text" id="ff_options_text" class="hidden">{{ $optsRaw }}</textarea>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-3">
            <div class="flex items-center justify-between gap-2">
                <div class="text-xs text-zinc-400">Adicione as opções abaixo.</div>
                <button type="button" id="ff_add_opt"
                        class="rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-xs text-white hover:bg-zinc-800">
                    + Adicionar
                </button>
            </div>

            <div id="ff_opt_rows" class="mt-3 space-y-2"></div>
        </div>
    </div>

    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.system.forms.fields.index', [$event, $form]) }}"
           class="text-sm text-zinc-400 hover:text-white">
            ← Voltar
        </a>

        <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
            Salvar
        </button>
    </div>
</div>

{{-- Options builder (sem libs) --}}
<script>
(() => {
    const typeEl = document.getElementById('ff_type');
    const wrap = document.getElementById('ff_options_wrap');
    const rows = document.getElementById('ff_opt_rows');
    const addBtn = document.getElementById('ff_add_opt');
    const hidden = document.getElementById('ff_options_text');

    if (!typeEl || !wrap || !rows || !addBtn || !hidden) return;

    const supportsOptions = (t) => ['select', 'multiselect'].includes(String(t || '').toLowerCase());

    const parseInitial = () => {
        const raw = (hidden.value || '').trim();
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(v => String(v)).filter(v => v.trim() !== '');
            }
            // Se vier objeto (legado), usa só os valores.
            if (parsed && typeof parsed === 'object') {
                return Object.values(parsed).map(v => String(v)).filter(v => v.trim() !== '');
            }
        } catch (e) {
            // fallback legado: quebra por linhas
            return raw.split(/\r\n|\r|\n/).map(v => String(v).trim()).filter(v => v !== '');
        }
        return [];
    };

    const syncHidden = () => {
        const values = Array.from(rows.querySelectorAll('input[data-opt]'))
            .map(i => String(i.value || '').trim())
            .filter(v => v !== '');
        hidden.value = values.length ? JSON.stringify(values) : '';
    };

    const addRow = (val = '') => {
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';

        const inp = document.createElement('input');
        inp.type = 'text';
        inp.value = val;
        inp.setAttribute('data-opt', '1');
        inp.placeholder = 'Ex: Sim, Não, Estudante, etc...';
        inp.className = 'w-full rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-white text-sm';
        inp.addEventListener('input', syncHidden);

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'shrink-0 rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-xs text-white hover:bg-zinc-800';
        del.textContent = 'Remover';
        del.addEventListener('click', () => {
            row.remove();
            syncHidden();
        });

        row.appendChild(inp);
        row.appendChild(del);
        rows.appendChild(row);
    };

    const refreshVisibility = () => {
        const show = supportsOptions(typeEl.value);
        wrap.classList.toggle('hidden', !show);
        if (!show) {
            // não apaga os dados; só não mostra.
            return;
        }
        // se ainda não tem linhas, popula
        if (rows.childElementCount === 0) {
            const initial = parseInitial();
            if (initial.length) {
                initial.forEach(v => addRow(v));
            } else {
                addRow('');
            }
            syncHidden();
        }
    };

    addBtn.addEventListener('click', () => {
        addRow('');
        syncHidden();
    });

    typeEl.addEventListener('change', refreshVisibility);
    refreshVisibility();
})();
</script>
