<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->name }} - {{ $category->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-100">
@php
    $bannerPath = !empty($category->banner_path) ? $category->banner_path : ($event->banner_path ?? null);
@endphp
@if($bannerPath)
    <div class="mx-auto max-w-[1200px]">
        <div class="w-full h-[200px] flex items-center justify-center rounded-2xl bg-zinc-950">
            <img
                src="{{ asset('storage/'.$bannerPath) }}"
                alt="Banner"
                class="max-w-full max-h-full object-contain"
            >
        </div>
    </div>
@endif
<div class="max-w-3xl mx-auto p-6">
    <div class="mb-6">
        <a class="text-zinc-400 hover:text-zinc-200 text-sm" href="{{ route('public.event.show', $event) }}">←
            voltar</a>
        <h1 class="text-2xl font-bold mt-2">{{ $category->name }}</h1>
        <p class="text-zinc-400">{{ $event->name }}</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 mb-5">
            <div class="font-semibold text-red-200 mb-2">Corrija os campos:</div>
            <ul class="text-sm text-red-200/80 list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            @if(session('already_registered'))
                <a class="..." href="{{ route('public.attendee.login', $event) }}">
                    Ir para login
                </a>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('public.registration.store', [$event, $category]) }}"
          class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 space-y-5">
        @csrf

        {{-- EMAIL + CONFIRMA EMAIL --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">E-mail <span class="text-red-300">*</span></label>
                <input type="email" name="ins_email" required value="{{ old('ins_email') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Confirmar e-mail <span class="text-red-300">*</span></label>
                <input type="email" name="email_confirmation" required value="{{ old('email_confirmation') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
            </div>
        </div>

        {{-- SENHA + CONFIRMA SENHA (subiu pra cima) --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Crie uma senha <span class="text-red-300">*</span></label>
                <input type="password" required name="password"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Confirmar senha <span class="text-red-300">*</span></label>
                <input type="password" required name="password_confirmation"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
            </div>
        </div>

        <p class="text-xs text-zinc-400">
            Use esse e-mail e senha para acessar sua área futuramente.
        </p>

        {{-- CAMPOS DINÂMICOS --}}
        @foreach($form->fields as $field)
            @php
                $fid = $field->getKey();
                $oldVal = old("f.$fid");

                // options sempre como lista (array de strings)
                $opts = $field->options ?? [];
                if (is_string($opts)) {
                    $decoded = json_decode($opts, true);
                    $opts = is_array($decoded) ? $decoded : [];
                }
                if (is_array($opts) && !array_is_list($opts)) {
                    // se vier objeto/associativo, usa só os valores
                    $opts = array_values($opts);
                }
                $opts = array_values(array_filter(array_map(fn($v) => trim((string)$v), (array)$opts), fn($v) => $v !== ''));

                // Máscaras / autofill
                $mask = null;
                $autofill = null;

                if ($field->type === 'cpf')  $mask = 'cpf';
                if ($field->type === 'cnpj') { $mask = 'cnpj'; $autofill = 'cnpj'; }
                if ($field->type === 'cep')  { $mask = 'cep';  $autofill = 'cep';  }

                // ViaCEP targets por KEY
                $cepTarget = match ($field->key) {
                    'ins_endereco'    => 'logradouro',
                    'ins_bairro'      => 'bairro',
                    'ins_cidade'      => 'localidade',
                    'ins_estado'      => 'uf',
                    'ins_complemento' => 'complemento',
                    default           => null,
                };

                $requiredAttr = $field->required ? 'required' : null;

                // pra multiselect, old pode vir string (legado) ou array
                $oldArr = is_array($oldVal) ? $oldVal : (strlen((string)$oldVal) ? [$oldVal] : []);
            @endphp

            <div>
                <label class="block text-sm font-medium mb-2">
                    {{ $field->label }}
                    @if($field->required)
                        <span class="text-red-300">*</span>
                    @endif
                </label>

                @switch($field->type)

                    @case('textarea')
                        <textarea
                            id="f_{{ $fid }}"
                            name="f[{{ $fid }}]"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($cepTarget) data-cep-target="{{ $cepTarget }}" @endif
                            @if($requiredAttr) required @endif
                            rows="4"
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        >{{ $oldVal }}</textarea>
                        @break

                    @case('select')
                        <select
                            id="f_{{ $fid }}"
                            name="f[{{ $fid }}]"
                            data-key="{{ $field->key }}"
                            @if($requiredAttr) required @endif
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        >
                            <option value="">Selecione...</option>
                            @foreach($opts as $opt)
                                <option
                                    value="{{ $opt }}" @selected((string)$oldVal === (string)$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('multiselect')
                        @php
                            // old pode vir array (certo) ou string (legado)
                            $selected = is_array($oldVal) ? $oldVal : (strlen((string)$oldVal) ? [$oldVal] : []);
                            $selected = array_map('strval', $selected);
                        @endphp

                        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4 space-y-3">
                            {{-- se quiser permitir "limpar tudo" no backend com empty array, não manda hidden --}}
                            {{-- mas se preferir garantir que sempre chegue algo, manda esse hidden: --}}
                            <input type="hidden" name="f[{{ $fid }}]" value="">

                            <div class="grid grid-cols-1 sm:grid-cols-1 gap-1">
                                @forelse($opts as $i => $opt)
                                    @php
                                        $oid = "f_{$fid}_{$i}";
                                        $optStr = (string)$opt;
                                    @endphp

                                    <label for="{{ $oid }}"
                                           class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-900/40 px-3 py-2 hover:bg-zinc-900">
                                        <input
                                            id="{{ $oid }}"
                                            type="checkbox"
                                            name="f[{{ $fid }}][]"
                                            value="{{ $optStr }}"
                                            data-key="{{ $field->key }}"
                                            @checked(in_array($optStr, $selected, true))
                                            class="h-4 w-4 rounded border-zinc-700 bg-zinc-950"
                                        >
                                        <span class="text-sm text-zinc-200">{{ $optStr }}</span>
                                    </label>
                                @empty
                                    <div class="text-sm text-zinc-400">
                                        Nenhuma opção cadastrada pra esse campo.
                                    </div>
                                @endforelse
                            </div>

                            @if($field->required)
                                <div class="text-xs text-zinc-400">
                                    Selecione pelo menos uma opção.
                                </div>
                            @endif
                        </div>
                        @break

                    @case('email')
                        <input
                            id="f_{{ $fid }}"
                            type="email"
                            name="f[{{ $fid }}]"
                            value="{{ $oldVal }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($requiredAttr) required @endif
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        />
                        @break

                    @case('number')
                        <input
                            id="f_{{ $fid }}"
                            type="number"
                            name="f[{{ $fid }}]"
                            value="{{ $oldVal }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($requiredAttr) required @endif
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        />
                        @break

                    @case('password')
                        <input
                            id="f_{{ $fid }}"
                            type="password"
                            name="f[{{ $fid }}]"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($requiredAttr) required @endif
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        />
                        @break

                    @case('cpf')
                    @case('cnpj')
                    @case('cep')
                        <input
                            id="f_{{ $fid }}"
                            type="text"
                            name="f[{{ $fid }}]"
                            value="{{ $oldVal }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($mask) data-mask="{{ $mask }}" @endif
                            @if($autofill) data-autofill="{{ $autofill }}" @endif
                            @if($cepTarget) data-cep-target="{{ $cepTarget }}" @endif
                            @if($requiredAttr) required @endif
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        />
                        @break

                    @default
                        {{-- text (e qualquer coisa desconhecida cai aqui) --}}
                        <input
                            id="f_{{ $fid }}"
                            type="text"
                            name="f[{{ $fid }}]"
                            value="{{ $oldVal }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($cepTarget) data-cep-target="{{ $cepTarget }}" @endif
                            @if($requiredAttr) required @endif
                            class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 focus:outline-none focus:border-zinc-600"
                        />
                @endswitch

                @if($field->help_text)
                    <div class="text-xs text-zinc-400 mt-2">{{ $field->help_text }}</div>
                @endif
            </div>
        @endforeach

        <button type="submit"
                class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-3 transition">
            Enviar inscrição
        </button>
    </form>
</div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        function onlyDigits(v) {
            return (v || '').replace(/\D+/g, '');
        }

        // ---------
        // Máscaras
        // ---------
        function maskCPF(v) {
            v = onlyDigits(v).slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return v;
        }

        function maskCNPJ(v) {
            v = onlyDigits(v).slice(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            return v;
        }

        function maskCEP(v) {
            v = onlyDigits(v).slice(0, 8);
            v = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
            return v;
        }

        // ----------
        // UI erro inline
        // ----------
        function setFieldError(el, msg) {
            el.classList.add('border-red-500');
            el.classList.remove('border-zinc-800');

            let help = el.parentElement.querySelector('.js-field-error');
            if (!help) {
                help = document.createElement('div');
                help.className = 'js-field-error text-xs text-red-300 mt-2';
                el.parentElement.appendChild(help);
            }
            help.textContent = msg;
            el.setCustomValidity(msg || '');
        }

        function clearFieldError(el) {
            el.classList.remove('border-red-500');
            el.classList.add('border-zinc-800');

            const help = el.parentElement.querySelector('.js-field-error');
            if (help) help.remove();

            el.setCustomValidity('');
        }

        // -------------------------
        // Validadores (CPF / CNPJ)
        // -------------------------
        function isValidCPF(cpf) {
            cpf = onlyDigits(cpf);
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false;

            let sum = 0;
            for (let i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i);
            let d1 = (sum * 10) % 11;
            if (d1 === 10) d1 = 0;
            if (d1 !== parseInt(cpf[9])) return false;

            sum = 0;
            for (let i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i);
            let d2 = (sum * 10) % 11;
            if (d2 === 10) d2 = 0;
            return d2 === parseInt(cpf[10]);
        }

        function isValidCNPJ(cnpj) {
            cnpj = onlyDigits(cnpj);
            if (cnpj.length !== 14) return false;
            if (/^(\d)\1{13}$/.test(cnpj)) return false;

            const calc = (base) => {
                let size = base.length;
                let pos = size - 7;
                let sum = 0;

                for (let i = size; i >= 1; i--) {
                    sum += parseInt(base[size - i]) * pos--;
                    if (pos < 2) pos = 9;
                }
                const res = sum % 11;
                return (res < 2) ? 0 : 11 - res;
            };

            const d1 = calc(cnpj.slice(0, 12));
            const d2 = calc(cnpj.slice(0, 12) + d1);
            return cnpj === (cnpj.slice(0, 12) + String(d1) + String(d2));
        }

        // ----------
        // Helpers pra preencher por key
        // ----------
        function setByKey(key, value) {
            if (value == null) return;

            const el =
                document.querySelector(`[data-key="${key}"]`) ||
                document.querySelector(`[name="${key}"]`) ||
                document.querySelector(`[name="f[${key}]"]`);

            if (!el) return;

            if (el.value === '' || el.value == null) {
                el.value = value;
                el.dispatchEvent(new Event('input', {bubbles: true}));
            }
        }

        // ----------
        // Delegação de máscara
        // ----------
        document.addEventListener('input', (e) => {
            const el = e.target;
            if (!el || !el.matches || !el.matches('[data-mask]')) return;

            const type = el.getAttribute('data-mask');
            const cur = el.value;

            if (type === 'cpf') el.value = maskCPF(cur);
            if (type === 'cnpj') el.value = maskCNPJ(cur);
            if (type === 'cep') el.value = maskCEP(cur);

            // se tá digitando, remove erro pra não ficar “grudado”
            if (type === 'cpf' || type === 'cnpj') clearFieldError(el);
        });

        // ----------
        // Validação CPF/CNPJ no blur (sem alert)
        // ----------
        document.addEventListener('blur', (e) => {
            const el = e.target;
            if (!el || !el.matches) return;

            const mask = el.getAttribute('data-mask');
            if (mask !== 'cpf' && mask !== 'cnpj') return;

            const digits = onlyDigits(el.value);

            if (mask === 'cpf') {
                if (digits.length === 0) return clearFieldError(el);
                if (digits.length < 11) return setFieldError(el, 'CPF incompleto.');
                if (!isValidCPF(digits)) return setFieldError(el, 'CPF inválido.');
                return clearFieldError(el);
            }

            if (mask === 'cnpj') {
                if (digits.length === 0) return clearFieldError(el);
                if (digits.length < 14) return setFieldError(el, 'CNPJ incompleto.');
                if (!isValidCNPJ(digits)) return setFieldError(el, 'CNPJ inválido.');
                return clearFieldError(el);
            }
        }, true);

        // ----------
        // ViaCEP (quando sai do CEP)
        // ----------
        async function viaCepLookup(cep) {
            cep = onlyDigits(cep);
            if (cep.length !== 8) return null;
            const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const j = await r.json();
            if (j.erro) return null;
            return j;
        }

        document.addEventListener('blur', async (e) => {
            const el = e.target;
            if (!el || !el.matches || !el.matches('[data-autofill="cep"]')) return;

            const data = await viaCepLookup(el.value);
            if (!data) return;

            document.querySelectorAll('[data-cep-target]').forEach(target => {
                const k = target.getAttribute('data-cep-target'); // logradouro, bairro, etc
                if (!k) return;

                const val = data[k];
                if (val == null) return;

                if (target.value === '' || target.value == null) {
                    target.value = val;
                    target.dispatchEvent(new Event('input', {bubbles: true}));
                }
            });
        }, true);

        // ----------
        // API CNPJ (quando sai do CNPJ)
        // ----------
        document.addEventListener('blur', async (e) => {
            const el = e.target;
            if (!el || !el.matches || !el.matches('[data-autofill="cnpj"]')) return;

            const cnpj = onlyDigits(el.value);
            if (cnpj.length !== 14) return;

            const r = await fetch(`/api/cnpj?cnpj=${cnpj}`);
            const j = await r.json();
            if (!r.ok || j.code !== 'success') return;

            // ajuste conforme seu form
            setByKey('ins_instituicao', j.data.razao_social);
            setByKey('ins_endereco', j.data.logradouro);
            setByKey('ins_numero', j.data.numero);
            setByKey('ins_bairro', j.data.bairro);
            setByKey('ins_cidade', j.data.cidade);
            setByKey('ins_estado', j.data.estado);
            setByKey('ins_cep', j.data.cep);
            setByKey('ins_pais', j.data.pais || 'Brasil');
        }, true);

    });
</script>

</html>
