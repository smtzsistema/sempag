{{-- resources/views/admin/system/credentials/a4.blade.php --}}
@extends('admin.layouts.app')

@php
    $isEdit = !empty($credential);
    $pageW = (int) (($initialConfig['page']['w'] ?? 794));
    $pageH = (int) (($initialConfig['page']['h'] ?? 1123));
@endphp

@section('title', $isEdit ? 'Editar credencial A4' : 'Nova credencial A4')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', $isEdit ? 'Editar credencial (A4)' : 'Nova credencial (A4)')

@section('top_actions')
    <a href="{{ route('admin.system.credentials.index', $event) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>

    @if($isEdit)
        <form method="POST" action="{{ route('admin.system.credentials.destroy', [$event, $credential]) }}"
              class="inline">
            @csrf
            <button type="submit"
                    onclick="return confirm('Excluir esta credencial?')"
                    class="rounded-xl bg-red-600 hover:bg-red-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
                Excluir
            </button>
        </form>
    @endif
@endsection

@section('content')

    @php
        $action = $isEdit
            ? route('admin.system.credentials.update', [$event, $credential])
            : route('admin.system.credentials.storeA4', $event);

        $existingBg = $isEdit && !empty($credential->cre_fundo)
            ? asset('storage/' . $credential->cre_fundo)
            : null;

        $selected = old('category_ids', $selectedCategories ?? []);
        $mirrorOld = old('cre_espelhar', ($isEdit && ($credential->cre_espelhar ?? 'N') === 'S') ? 1 : 0);
    @endphp

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" onsubmit="__credBuilderBeforeSubmit()">
        @csrf

        <div class="grid lg:grid-cols-12 gap-4">
            {{-- Left: preview --}}
            <div class="lg:col-span-7 lg:sticky lg:top-6 lg:self-start">
                <div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
                    <div class="p-5 border-b border-zinc-800 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-lg font-semibold">Preview</div>
                            <div class="text-xs text-zinc-400 mt-0.5">
                                A4 em pé ({{ $pageW }}×{{ $pageH }}). Arrasta os itens com o mouse.
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="rounded-xl border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-3 py-2 text-sm transition"
                                    onclick="CredBuilder.addElement('text')">
                                + Texto
                            </button>

                            <button type="button"
                                    class="rounded-xl border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-3 py-2 text-sm transition"
                                    onclick="CredBuilder.addElement('qrcode')">
                                + QRCode
                            </button>

                            <button type="button"
                                    class="rounded-xl border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-3 py-2 text-sm transition"
                                    onclick="CredBuilder.addElement('barcode')">
                                + Barcode
                            </button>

                            <button type="button"
                                    class="rounded-xl border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-3 py-2 text-sm transition"
                                    onclick="CredBuilder.addElement('photo')">
                                + Foto
                            </button>
                        </div>
                    </div>

                    <div class="p-5">
                        {{-- preview stage --}}
                        <div class="w-full">
                            <div class="w-full overflow-auto">
                                <div id="cb-stageWrap"
                                     class="relative mx-auto rounded-2xl border border-zinc-800 bg-zinc-950/40 shadow-2xl select-none"
                                     style="width: min(100%, 780px);">
                                    {{-- scaled A4 --}}
                                    <div id="cb-stage"
                                         class="relative origin-top-left"
                                         style="width: {{ $pageW }}px; height: {{ $pageH }}px;">
                                        {{-- background --}}
                                        <img id="cb-bg"
                                             src="{{ $existingBg ?? '' }}"
                                             alt=""
                                             class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                                             style="{{ $existingBg ? '' : 'display:none;' }}"/>

                                        {{-- divider (when mirror on) --}}
                                        <div id="cb-divider"
                                             class="absolute top-0 bottom-0 w-px bg-zinc-300/30 pointer-events-none"
                                             style="left: {{ intval($pageW/2) }}px; display:none;"></div>

                                        {{-- elements container --}}
                                        <div id="cb-elements" class="absolute inset-0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-zinc-400">
                            Dica: com “Espelhar” ligado, você desenha só na metade esquerda e o lado direito é replicado
                            tudo.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: settings + inspector --}}
            <div class="lg:col-span-5">
                <div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
                    <div class="p-5 border-b border-zinc-800">
                        <div class="text-lg font-semibold">Configuração</div>
                        <div class="text-xs text-zinc-400 mt-0.5">Tudo que você mexer aqui vai pro JSON da credencial.
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- name --}}
                        <div>
                            <label class="text-sm text-zinc-300">Nome</label>
                            <input type="text"
                                   name="cre_nome"
                                   value="{{ old('cre_nome', $isEdit ? $credential->cre_nome : '') }}"
                                   class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm outline-none focus:border-emerald-600"
                                   placeholder="Ex.: Credencial Visitante A4">
                            @error('cre_nome')
                            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- categories --}}
                        <div>
                            <label class="text-sm text-zinc-300">Categorias vinculadas</label>
                            <select name="category_ids[]"
                                    multiple
                                    class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm outline-none focus:border-emerald-600 min-h-[120px]">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->cat_id }}"
                                        @selected(in_array($cat->cat_id, $selected, true))>
                                        {{ $cat->cat_nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_ids')
                            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                            @error('category_ids.*')
                            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-xs text-zinc-500 mt-1">Segura CTRL pra selecionar várias.</div>
                        </div>

                        {{-- bg upload --}}
                        <div>
                            <label class="text-sm text-zinc-300">Imagem de fundo (A4 em pé)</label>
                            <input type="file"
                                   name="cre_fundo"
                                   accept="image/png,image/jpeg"
                                   class="mt-1 block w-full text-sm text-zinc-300
                                      file:mr-3 file:rounded-xl file:border-0
                                      file:bg-zinc-950/40 file:px-4 file:py-2 file:text-sm
                                      file:text-zinc-200 hover:file:bg-zinc-900
                                      border border-zinc-800 rounded-xl bg-zinc-950/40"
                                   onchange="CredBuilder.onBackgroundChange(this)">
                            @error('cre_fundo')
                            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                            @if($existingBg)
                                <div class="text-xs text-zinc-500 mt-1">Já existe um fundo salvo. Substitui ao enviar
                                    outro.
                                </div>
                            @endif
                        </div>

                        {{-- mirror --}}
                        <div
                            class="flex items-center justify-between gap-3 rounded-xl border border-zinc-800 bg-zinc-950/40 p-3">
                            <div>
                                <div class="text-sm text-zinc-200 font-semibold">Espelhar</div>
                                <div class="text-xs text-zinc-500">Replica tudo da esquerda pra direita</div>
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                       name="cre_espelhar"
                                       value="1"
                                       @checked((bool)$mirrorOld)
                                       onchange="CredBuilder.setMirror(this.checked)"
                                       class="rounded border-zinc-700 bg-zinc-950/40 text-emerald-600 focus:ring-0">
                                <span class="text-sm text-zinc-300">Ativo</span>
                            </label>
                        </div>

                        {{-- elements list --}}
                        <div class="rounded-2xl border border-zinc-800 bg-zinc-950/30 overflow-hidden">
                            <div class="px-4 py-3 border-b border-zinc-800 flex items-center justify-between">
                                <div class="text-sm font-semibold text-zinc-200">Elementos</div>
                                <button type="button"
                                        class="text-xs rounded-lg border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-2 py-1 transition"
                                        onclick="CredBuilder.clearSelection()">
                                    Limpar seleção
                                </button>
                            </div>
                            <div id="cb-list" class="max-h-[180px] overflow-auto divide-y divide-zinc-800"></div>
                        </div>

                        {{-- inspector --}}
                        <div class="rounded-2xl border border-zinc-800 bg-zinc-950/30 overflow-hidden">
                            <div class="px-4 py-3 border-b border-zinc-800">
                                <div class="text-sm font-semibold text-zinc-200">Propriedades</div>
                                <div class="text-xs text-zinc-500">Selecione um elemento no preview.</div>
                            </div>

                            <div class="p-4 space-y-3" id="cb-inspector">
                                <div class="text-xs text-zinc-500">
                                    Nada selecionado ainda.
                                </div>
                            </div>
                        </div>

                        {{-- hidden config --}}
                        <input type="hidden" name="cre_config" id="cb-config"
                               value="{{ old('cre_config', json_encode($initialConfig ?? [], JSON_UNESCAPED_UNICODE)) }}">

                        @error('cre_config')
                        <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                        @enderror

                        {{-- save --}}
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-3 text-sm transition">
                                {{ $isEdit ? 'Salvar alterações' : 'Criar credencial' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Data for JS --}}
    @php
        // Garante coleção e converte pra array simples (pra não quebrar o @json)
        $ff = collect($formFields ?? [])->map(function($f){
            return [
                'fic_id'    => $f->fic_id,
                'fic_label' => $f->fic_label,
                'fic_nome'  => $f->fic_nome,
                'form_id'   => $f->form_id,
            ];
        })->values()->all();
    @endphp

    <script>
        window.__CB_BASE_FIELDS = @json($baseFields ?? [], JSON_UNESCAPED_UNICODE);
        window.__CB_FORM_FIELDS = @json($ff ?? [], JSON_UNESCAPED_UNICODE);
        window.__CB_INITIAL = @json($initialConfig ?? [], JSON_UNESCAPED_UNICODE);
        window.__CB_EXISTING_BG = @json($existingBg, JSON_UNESCAPED_UNICODE);
        window.__CB_MIRROR = @json((bool)$mirrorOld, JSON_UNESCAPED_UNICODE);
    </script>


    <script>
        (function () {
            const $ = (sel, root = document) => root.querySelector(sel);
            const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

            const stageWrap = $('#cb-stageWrap');
            const stage = $('#cb-stage');
            const elContainer = $('#cb-elements');
            const list = $('#cb-list');
            const inspector = $('#cb-inspector');
            const bgImg = $('#cb-bg');
            const divider = $('#cb-divider');
            const hiddenCfg = $('#cb-config');

            // scale stage to fixed wrap width
            const PAGE_W = (window.__CB_INITIAL?.page?.w ?? 794) | 0;
            const PAGE_H = (window.__CB_INITIAL?.page?.h ?? 1123) | 0;

            function computeScale() {
                const wrapW = stageWrap.getBoundingClientRect().width;
                const s = wrapW / PAGE_W;
                stage.style.transform = `scale(${s})`;
                stageWrap.style.height = `${Math.round(PAGE_H * s)}px`;
                return s;
            }

            let SCALE = 1;
            window.addEventListener('resize', () => {
                SCALE = computeScale();
            });
            setTimeout(() => {
                SCALE = computeScale();
            }, 50);

            // state
            const state = {
                page: {w: PAGE_W, h: PAGE_H},
                mirror: !!window.__CB_MIRROR,
                elements: [],
                selectedId: null,
                drag: null, // {id, startX, startY, origX, origY}
            };

            // init bg
            if (window.__CB_EXISTING_BG) {
                bgImg.src = window.__CB_EXISTING_BG;
                bgImg.style.display = '';
            }

            // init from hidden or initial
            function safeParseJSON(v) {
                try {
                    return JSON.parse(v);
                } catch (e) {
                    return null;
                }
            }

            const fromHidden = safeParseJSON(hiddenCfg.value);
            const initCfg = fromHidden && typeof fromHidden === 'object' ? fromHidden : (window.__CB_INITIAL || {});
            if (initCfg?.elements && Array.isArray(initCfg.elements)) {
                state.elements = initCfg.elements.map(e => normalizeElement(e));
            }
            if (initCfg?.page?.w && initCfg?.page?.h) {
                state.page.w = initCfg.page.w | 0;
                state.page.h = initCfg.page.h | 0;
            }

            function uid() {
                return 'e' + Math.random().toString(16).slice(2) + Date.now().toString(16);
            }

            function normalizeElement(e) {
                const type = (e?.type || 'text');

                return {
                    id: e?.id || uid(),
                    type,

                    x: clampInt(e?.x ?? 40, 0, state.page.w - 1),
                    y: clampInt(e?.y ?? 40, 0, state.page.h - 1),

                    // tamanho padrão por tipo (inclui foto)
                    w: clampInt(
                        e?.w ?? (type === 'photo' ? 140 : (type === 'barcode' ? 260 : 320)),
                        10,
                        state.page.w
                    ),
                    h: clampInt(
                        e?.h ?? (type === 'photo' ? 180 : (type === 'qrcode' ? 160 : 44)),
                        10,
                        state.page.h
                    ),

                    // source padrão por tipo (inclui foto)
                    source: e?.source || (
                        type === 'photo'
                            ? 'reg:ins_foto'
                            : (type === 'text' ? 'reg:ins_nomecracha' : 'reg:ins_token')
                    ),

                    // text options
                    fontSize: clampInt(e?.fontSize ?? 24, 6, 120),
                    fontWeight: (e?.fontWeight ?? '700').toString(),
                    fontFamily: (e?.fontFamily ?? 'Arial').toString(),
                    color: (e?.color ?? '#ffffff').toString(),
                    align: (e?.align ?? 'left').toString(),

                    // formatting / validations
                    formatMode: (e?.formatMode ?? 'none').toString(),         // none | title | upper
                    validationMode: (e?.validationMode ?? 'none').toString(), // none | first_last | limit | limit_font
                    limit: parseInt(e?.limit ?? 0, 10) || 0,
                    fontWhenOver: parseInt(e?.fontWhenOver ?? 0, 10) || 0,

                    // barcode/qrcode options
                    showLabel: !!(e?.showLabel ?? false),
                    barcodeFormat: (e?.barcodeFormat ?? 'CODE39').toString(),

                    // photo options
                    fit: (e?.fit ?? 'cover').toString(), // cover | contain
                };
            }


            function clampInt(n, min, max) {
                n = parseInt(n, 10);
                if (Number.isNaN(n)) n = min;
                return Math.max(min, Math.min(max, n));
            }

            function isSelected(id) {
                return state.selectedId === id;
            }

            function leftHalfMaxX(elemW) {
                return Math.max(0, Math.floor(state.page.w / 2) - elemW);
            }

            function applyMirrorClamp(el) {
                if (!state.mirror) return;
                el.x = clampInt(el.x, 0, leftHalfMaxX(el.w));
            }

            function setMirrorUI() {
                divider.style.display = state.mirror ? '' : 'none';
            }

            function buildSourceOptionsHTML(selected) {
                const base = window.__CB_BASE_FIELDS || [];
                const ff = window.__CB_FORM_FIELDS || [];

                let html = '';
                html += `<optgroup label="Campos base (tbl_inscricao)">`;
                base.forEach(b => {
                    const val = `reg:${b.key}`;
                    const sel = (selected === val) ? 'selected' : '';
                    html += `<option value="${escapeHtml(val)}" ${sel}>${escapeHtml(b.label)}</option>`;
                });
                html += `</optgroup>`;

                if (ff.length) {
                    html += `<optgroup label="Campos da ficha (respostas)">`;
                    ff.forEach(f => {
                        const val = `ans:${f.fic_id}`;
                        const lbl = `#${f.form_id} • ${f.fic_label || f.fic_nome || ('Campo ' + f.fic_id)}`;
                        const sel = (selected === val) ? 'selected' : '';
                        html += `<option value="${escapeHtml(val)}" ${sel}>${escapeHtml(lbl)}</option>`;
                    });
                    html += `</optgroup>`;
                }
                return html;
            }

            function buildPhotoSourceOptionsHTML(selected) {
                const val = 'reg:ins_foto';
                const sel = (selected === val) ? 'selected' : '';
                return `<option value="${val}" ${sel}>Foto (ins_foto)</option>`;
            }

            function escapeHtml(s) {
                return String(s ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function elementLabel(el) {
                if (el.type === 'text') return 'Texto';
                if (el.type === 'qrcode') return 'QRCode';
                if (el.type === 'barcode') return 'Barcode';
                if (el.type === 'photo') return 'Foto';
                return el.type;
            }

            function sourceLabel(src) {
                if (!src) return '(sem)';
                if (src.startsWith('reg:')) {
                    const key = src.slice(4);
                    const f = (window.__CB_BASE_FIELDS || []).find(x => x.key === key);
                    return f ? f.label : src;
                }
                if (src.startsWith('ans:')) {
                    const id = parseInt(src.slice(4), 10);
                    const f = (window.__CB_FORM_FIELDS || []).find(x => x.fic_id === id);
                    if (f) return `Resposta • ${f.fic_label || f.fic_nome || ('Campo ' + id)}`;
                    return src;
                }
                return src;
            }

            function render() {
                setMirrorUI();

                // elements in DOM
                elContainer.innerHTML = '';
                const frag = document.createDocumentFragment();

                state.elements.forEach(el => {
                    applyMirrorClamp(el);
                    frag.appendChild(renderOne(el, false));

                    if (state.mirror) {
                        frag.appendChild(renderOne(el, true));
                    }
                });

                elContainer.appendChild(frag);

                renderList();
                renderInspector();
                syncHidden();
            }

            function renderOne(el, mirrored) {
                const div = document.createElement('div');

                const half = Math.floor(state.page.w / 2);
                const x = mirrored ? (el.x + half) : el.x;

                div.className = 'absolute';
                div.dataset.id = el.id;
                div.dataset.mirrored = mirrored ? '1' : '0';

                const selected = isSelected(el.id) && !mirrored;
                div.style.left = x + 'px';
                div.style.top = el.y + 'px';
                div.style.width = el.w + 'px';
                div.style.height = el.h + 'px';
                div.style.cursor = mirrored ? 'default' : 'move';
                div.style.boxSizing = 'border-box';
                div.style.border = selected ? '2px solid rgba(16,185,129,0.9)' : '1px dashed rgba(255,255,255,0.25)';
                div.style.borderRadius = '10px';
                div.style.background = 'rgba(0,0,0,0.18)';

                // content
                const inner = document.createElement('div');
                inner.className = 'w-full h-full flex items-center';
                inner.style.padding = '6px 10px';
                inner.style.boxSizing = 'border-box';

                if (el.type === 'text') {
                    inner.style.justifyContent = el.align === 'center' ? 'center' : (el.align === 'right' ? 'flex-end' : 'flex-start');
                    inner.style.color = el.color || '#fff';
                    inner.style.fontFamily = el.fontFamily || 'Arial';
                    inner.style.fontWeight = el.fontWeight || '700';
                    inner.style.fontSize = (el.fontSize || 24) + 'px';
                    inner.style.lineHeight = '1.1';

                    inner.textContent = sourceLabel(el.source);
                }

                if (el.type === 'qrcode') {
                    inner.style.alignItems = 'center';
                    inner.style.justifyContent = 'center';
                    inner.style.flexDirection = 'column';
                    inner.style.gap = '6px';

                    const box = document.createElement('div');
                    box.style.width = '80%';
                    box.style.height = '80%';
                    box.style.border = '2px solid rgba(255,255,255,0.35)';
                    box.style.borderRadius = '10px';
                    box.style.display = 'flex';
                    box.style.alignItems = 'center';
                    box.style.justifyContent = 'center';
                    box.style.color = 'rgba(255,255,255,0.7)';
                    box.style.fontSize = '12px';
                    box.textContent = 'QR';

                    const lab = document.createElement('div');
                    lab.style.color = 'rgba(255,255,255,0.7)';
                    lab.style.fontSize = '11px';
                    lab.textContent = sourceLabel(el.source);

                    inner.appendChild(box);
                    if (el.showLabel) inner.appendChild(lab);
                }

                if (el.type === 'barcode') {
                    inner.style.alignItems = 'center';
                    inner.style.justifyContent = 'center';
                    inner.style.flexDirection = 'column';
                    inner.style.gap = '6px';

                    const box = document.createElement('div');
                    box.style.width = '90%';
                    box.style.height = '65%';
                    box.style.border = '2px solid rgba(255,255,255,0.35)';
                    box.style.borderRadius = '10px';
                    box.style.display = 'flex';
                    box.style.alignItems = 'center';
                    box.style.justifyContent = 'center';
                    box.style.color = 'rgba(255,255,255,0.7)';
                    box.style.fontSize = '12px';
                    box.textContent = el.barcodeFormat || 'CODE39';

                    const lab = document.createElement('div');
                    lab.style.color = 'rgba(255,255,255,0.7)';
                    lab.style.fontSize = '11px';
                    lab.textContent = sourceLabel(el.source);

                    inner.appendChild(box);
                    if (el.showLabel) inner.appendChild(lab);
                }

                if (el.type === 'photo') {
                    inner.style.alignItems = 'center';
                    inner.style.justifyContent = 'center';
                    inner.style.flexDirection = 'column';
                    inner.style.gap = '6px';

                    const box = document.createElement('div');
                    box.style.width = '90%';
                    box.style.height = '90%';
                    box.style.border = '2px solid rgba(255,255,255,0.35)';
                    box.style.borderRadius = '10px';
                    box.style.display = 'flex';
                    box.style.alignItems = 'center';
                    box.style.justifyContent = 'center';
                    box.style.color = 'rgba(255,255,255,0.7)';
                    box.style.fontSize = '12px';
                    box.textContent = 'FOTO';

                    const lab = document.createElement('div');
                    lab.style.color = 'rgba(255,255,255,0.7)';
                    lab.style.fontSize = '11px';
                    lab.textContent = (el.fit === 'contain' ? 'contain' : 'cover');

                    inner.appendChild(box);
                    inner.appendChild(lab);
                }


                div.appendChild(inner);

                // click/select + drag only if not mirrored
                if (!mirrored) {
                    div.addEventListener('mousedown', (ev) => {
                        // ignore right button
                        if (ev.button !== 0) return;
                        ev.preventDefault();

                        state.selectedId = el.id;
                        render();

                        state.drag = {
                            id: el.id,
                            startX: ev.clientX,
                            startY: ev.clientY,
                            origX: el.x,
                            origY: el.y,
                        };
                    });

                    div.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        state.selectedId = el.id;
                        render();
                    });
                } else {
                    div.style.opacity = '0.85';
                }

                return div;
            }

            function renderList() {
                list.innerHTML = '';

                if (!state.elements.length) {
                    list.innerHTML = `<div class="p-4 text-xs text-zinc-500">Nenhum elemento ainda. Usa os botões “+” lá em cima.</div>`;
                    return;
                }

                state.elements.forEach(el => {
                    const row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'w-full text-left px-4 py-3 hover:bg-zinc-950/40 transition flex items-start justify-between gap-3';
                    row.onclick = () => {
                        state.selectedId = el.id;
                        render();
                    };

                    const left = document.createElement('div');
                    left.innerHTML = `
                <div class="text-sm font-semibold ${isSelected(el.id) ? 'text-emerald-300' : 'text-zinc-200'}">${escapeHtml(elementLabel(el))}</div>
                <div class="text-xs text-zinc-500 mt-0.5">${escapeHtml(sourceLabel(el.source))}</div>
                <div class="text-[11px] text-zinc-600 mt-1">x:${el.x} y:${el.y} w:${el.w} h:${el.h}</div>
            `;

                    const right = document.createElement('div');
                    right.className = 'flex items-center gap-2';
                    right.innerHTML = `
                <button type="button"
                        class="text-xs rounded-lg border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-2 py-1 transition"
                        data-act="dup">Duplicar</button>
                <button type="button"
                        class="text-xs rounded-lg border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-2 py-1 transition"
                        data-act="del">Excluir</button>
            `;

                    right.querySelector('[data-act="dup"]').onclick = (ev) => {
                        ev.stopPropagation();
                        CredBuilder.duplicate(el.id);
                    };
                    right.querySelector('[data-act="del"]').onclick = (ev) => {
                        ev.stopPropagation();
                        CredBuilder.remove(el.id);
                    };

                    row.appendChild(left);
                    row.appendChild(right);
                    list.appendChild(row);
                });
            }

            function renderInspector() {
                const el = state.elements.find(x => x.id === state.selectedId);
                if (!el) {
                    inspector.innerHTML = `<div class="text-xs text-zinc-500">Nada selecionado ainda.</div>`;
                    return;
                }

                const rules = `
              <div class="mt-3 grid grid-cols-2 gap-3">
                <div class="col-span-2">
                  <label class="text-xs text-zinc-400">Formatação</label>
                  <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                          onchange="CredBuilder.updateSelected({formatMode:this.value})">
                    <option value="none" ${el.formatMode === 'none' ? 'selected' : ''}>Nenhum</option>
                    <option value="title" ${el.formatMode === 'title' ? 'selected' : ''}>Primeira letra</option>
                    <option value="upper" ${el.formatMode === 'upper' ? 'selected' : ''}>Uppercase</option>
                  </select>
                </div>

                <div class="col-span-2">
                  <label class="text-xs text-zinc-400">Validações</label>
                  <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                          onchange="CredBuilder.updateSelected({validationMode:this.value})">
                    <option value="none" ${el.validationMode === 'none' ? 'selected' : ''}>Nenhum</option>
                    <option value="first_last" ${el.validationMode === 'first_last' ? 'selected' : ''}>Primeira e Última</option>
                    <option value="limit" ${el.validationMode === 'limit' ? 'selected' : ''}>Limite</option>
                    <option value="limit_font" ${el.validationMode === 'limit_font' ? 'selected' : ''}>Tamanho</option>
                  </select>
                </div>

                    <div class="${(el.validationMode === 'first_last' || el.validationMode === 'limit' || el.validationMode === 'limit_font') ? '' : 'hidden'} col-span-2" data-vblock="limit">
                      <label class="text-xs text-zinc-400">Limite</label>
                      <input type="number" min="0"
                             ${(el.validationMode === 'first_last' || el.validationMode === 'limit' || el.validationMode === 'limit_font') ? '' : 'disabled'}
                             class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                             value="${el.limit || 0}"
                             oninput="CredBuilder.updateSelected({limit:this.value})">
                      <div class="text-[11px] text-zinc-600 mt-1">Se for 0, ignora.</div>
                    </div>


                <div class="${(el.validationMode === 'limit_font') ? '' : 'hidden'} col-span-2" data-vblock="fontWhenOver">
                  <label class="text-xs text-zinc-400">Tamanho da fonte quando passar do limite</label>
                  <input type="number" min="0" max="120"
                         ${(el.validationMode === 'limit_font') ? '' : 'disabled'}
                         class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                         value="${el.fontWhenOver || 0}"
                         oninput="CredBuilder.updateSelected({fontWhenOver:this.value})">
                  <div class="text-[11px] text-zinc-600 mt-1">Se 0, mantém o padrão.</div>
                </div>

              </div>
            `;


                const common = `
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-zinc-400">X</label>
                    <input type="number" class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                           value="${el.x}" oninput="CredBuilder.updateSelected({x:this.value})">
                </div>
                <div>
                    <label class="text-xs text-zinc-400">Y</label>
                    <input type="number" class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                           value="${el.y}" oninput="CredBuilder.updateSelected({y:this.value})">
                </div>
                <div>
                    <label class="text-xs text-zinc-400">Largura</label>
                    <input type="number" class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                           value="${el.w}" oninput="CredBuilder.updateSelected({w:this.value})">
                </div>
                <div>
                    <label class="text-xs text-zinc-400">Altura</label>
                    <input type="number" class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                           value="${el.h}" oninput="CredBuilder.updateSelected({h:this.value})">
                </div>
            </div>

            <div class="mt-3">
              <label class="text-xs text-zinc-400">Fonte do dado</label>

              ${el.type === 'photo'
                    ? `
                    <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm opacity-70"
                            disabled>
                        ${buildPhotoSourceOptionsHTML(el.source)}
                    </select>
                    <div class="text-[11px] text-zinc-600 mt-1">
                        A foto sempre vem do módulo de fotos do inscrito.
                    </div>
                    `
                    : `
                    <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                            onchange="CredBuilder.updateSelected({source:this.value})">
                        ${buildSourceOptionsHTML(el.source)}
                    </select>
                    `
                }
            </div>
        `;

                let specific = '';

                if (el.type === 'text') {
                    specific = `
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="text-xs text-zinc-400">Tamanho</label>
                        <input type="number" class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                               value="${el.fontSize}" oninput="CredBuilder.updateSelected({fontSize:this.value})">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-400">Peso</label>
                        <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                                onchange="CredBuilder.updateSelected({fontWeight:this.value})">
                            ${['300', '400', '500', '600', '700', '800', '900'].map(w => `<option value="${w}" ${String(el.fontWeight) === w ? 'selected' : ''}>${w}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-zinc-400">Fonte</label>
                        <input type="text" class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                               value="${escapeHtml(el.fontFamily)}" oninput="CredBuilder.updateSelected({fontFamily:this.value})" placeholder="Arial, Inter, etc">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-400">Cor</label>
                        <input type="color" class="mt-1 w-full h-[40px] rounded-xl border border-zinc-800 bg-zinc-950/40 px-2 py-2"
                               value="${escapeHtml(el.color)}" oninput="CredBuilder.updateSelected({color:this.value})">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-400">Alinhamento</label>
                        <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                                onchange="CredBuilder.updateSelected({align:this.value})">
                            <option value="left" ${el.align === 'left' ? 'selected' : ''}>Esquerda</option>
                            <option value="center" ${el.align === 'center' ? 'selected' : ''}>Centro</option>
                            <option value="right" ${el.align === 'right' ? 'selected' : ''}>Direita</option>
                        </select>
                    </div>
                </div>
            `;
                }

                if (el.type === 'qrcode' || el.type === 'barcode') {
                    specific += `
                <div class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-zinc-800 bg-zinc-950/40 p-3">
                    <div>
                        <div class="text-sm text-zinc-200 font-semibold">Mostrar label</div>
                        <div class="text-xs text-zinc-500">Mostra o texto do campo embaixo</div>
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="rounded border-zinc-700 bg-zinc-950/40 text-emerald-600 focus:ring-0"
                               ${el.showLabel ? 'checked' : ''}
                               onchange="CredBuilder.updateSelected({showLabel:this.checked})">
                        <span class="text-sm text-zinc-300">Ativo</span>
                    </label>
                </div>
            `;
                }

                if (el.type === 'barcode') {
                    specific += `
                <div class="mt-3">
                    <label class="text-xs text-zinc-400">Formato do barcode</label>
                    <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                            onchange="CredBuilder.updateSelected({barcodeFormat:this.value})">
                        ${['CODE39', 'EAN13', 'EAN8', 'CODE128', 'ITF', 'UPC'].map(f => `<option value="${f}" ${el.barcodeFormat === f ? 'selected' : ''}>${f}</option>`).join('')}
                    </select>
                </div>
            `;
                }
                if (el.type === 'photo') {
                    specific += `
        <div class="mt-3">
            <label class="text-xs text-zinc-400">Ajuste da foto</label>
            <select class="mt-1 w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                    onchange="CredBuilder.updateSelected({fit:this.value})">
                <option value="cover" ${el.fit === 'cover' ? 'selected' : ''}>Preencher (cover)</option>
                <option value="contain" ${el.fit === 'contain' ? 'selected' : ''}>Conter (contain)</option>
            </select>
            <div class="text-[11px] text-zinc-600 mt-1">Cover corta as bordas; Contain não corta.</div>
        </div>
    `;
                }

                inspector.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-zinc-200">${escapeHtml(elementLabel(el))}</div>
                    <div class="text-xs text-zinc-500">ID: ${escapeHtml(el.id)}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                            class="text-xs rounded-lg border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-2 py-1 transition"
                            onclick="CredBuilder.duplicate('${el.id}')">Duplicar</button>
                    <button type="button"
                            class="text-xs rounded-lg border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-2 py-1 transition"
                            onclick="CredBuilder.remove('${el.id}')">Excluir</button>
                </div>
            </div>
            <div class="mt-3">${common}</div>
            ${rules}
            ${specific}
        `;
            }

            function syncHidden() {
                const payload = {
                    page: {w: state.page.w, h: state.page.h},
                    elements: state.elements,
                };
                hiddenCfg.value = JSON.stringify(payload);
            }

            // mouse move for drag
            document.addEventListener('mousemove', (ev) => {
                if (!state.drag) return;

                const el = state.elements.find(x => x.id === state.drag.id);
                if (!el) return;

                const dx = (ev.clientX - state.drag.startX) / SCALE;
                const dy = (ev.clientY - state.drag.startY) / SCALE;

                el.x = clampInt(state.drag.origX + dx, 0, state.page.w - el.w);
                el.y = clampInt(state.drag.origY + dy, 0, state.page.h - el.h);

                applyMirrorClamp(el);
                render();
            });

            document.addEventListener('mouseup', () => {
                state.drag = null;
            });

            // click outside clears selection
            stageWrap.addEventListener('mousedown', (ev) => {
                // if clicked empty area (not an element)
                if (!ev.target.closest('[data-id]')) {
                    state.selectedId = null;
                    render();
                }
            });

            // public API
            window.CredBuilder = {
                addElement(type) {
                    const base = normalizeElement({type});
                    if (type === 'qrcode') {
                        base.w = 180;
                        base.h = 180;
                        base.source = 'reg:ins_token';
                        base.showLabel = false;
                    }
                    if (type === 'barcode') {
                        base.w = 320;
                        base.h = 140;
                        base.source = 'reg:ins_token';
                        base.showLabel = true;
                        base.barcodeFormat = 'CODE39';
                    }
                    if (type === 'photo') {
                        base.w = 140;
                        base.h = 180;
                        base.source = 'reg:ins_foto';
                        base.fit = 'cover';
                    }

                    // place on left
                    base.x = 40;
                    base.y = 40 + (state.elements.length * 12);
                    applyMirrorClamp(base);

                    state.elements.push(base);
                    state.selectedId = base.id;
                    render();
                },

                remove(id) {
                    state.elements = state.elements.filter(x => x.id !== id);
                    if (state.selectedId === id) state.selectedId = null;
                    render();
                },

                duplicate(id) {
                    const el = state.elements.find(x => x.id === id);
                    if (!el) return;
                    const copy = normalizeElement({...el, id: uid(), x: (el.x + 20), y: (el.y + 20)});
                    applyMirrorClamp(copy);
                    state.elements.push(copy);
                    state.selectedId = copy.id;
                    render();
                },

                clearSelection() {
                    state.selectedId = null;
                    render();
                },

                updateSelected(patch) {
                    const el = state.elements.find(x => x.id === state.selectedId);
                    if (!el) return;

                    const p = {...patch};

                    if (p.x !== undefined) el.x = clampInt(p.x, 0, state.page.w - el.w);
                    if (p.y !== undefined) el.y = clampInt(p.y, 0, state.page.h - el.h);
                    if (p.w !== undefined) el.w = clampInt(p.w, 10, state.page.w);
                    if (p.h !== undefined) el.h = clampInt(p.h, 10, state.page.h);

                    if (p.source !== undefined) el.source = String(p.source);
                    if (p.fontSize !== undefined) el.fontSize = clampInt(p.fontSize, 6, 120);
                    if (p.fontWeight !== undefined) el.fontWeight = String(p.fontWeight);
                    if (p.fontFamily !== undefined) el.fontFamily = String(p.fontFamily);
                    if (p.color !== undefined) el.color = String(p.color);
                    if (p.align !== undefined) el.align = String(p.align);

                    if (p.showLabel !== undefined) el.showLabel = !!p.showLabel;
                    if (p.barcodeFormat !== undefined) el.barcodeFormat = String(p.barcodeFormat);

                    if (p.formatMode !== undefined) el.formatMode = String(p.formatMode);
                    if (p.validationMode !== undefined) el.validationMode = String(p.validationMode);
                    if (p.limit !== undefined) el.limit = parseInt(p.limit, 10) || 0;
                    if (p.fontWhenOver !== undefined) el.fontWhenOver = parseInt(p.fontWhenOver, 10) || 0;
                    if (p.fit !== undefined) el.fit = String(p.fit);


                    // clamp after size change
                    el.x = clampInt(el.x, 0, state.page.w - el.w);
                    el.y = clampInt(el.y, 0, state.page.h - el.h);
                    applyMirrorClamp(el);

                    render();
                },

                setMirror(on) {
                    state.mirror = !!on;

                    // when enabling mirror, clamp all to left
                    if (state.mirror) {
                        state.elements.forEach(applyMirrorClamp);
                    }
                    render();
                },

                onBackgroundChange(input) {
                    const file = input.files && input.files[0] ? input.files[0] : null;
                    if (!file) return;

                    const url = URL.createObjectURL(file);
                    bgImg.src = url;
                    bgImg.style.display = '';
                },
            };

            window.__credBuilderBeforeSubmit = function () {
                // just ensure hidden JSON is synced
                syncHidden();
            };

            // initial render
            state.mirror = !!window.__CB_MIRROR;
            render();
        })();
    </script>

@endsection
