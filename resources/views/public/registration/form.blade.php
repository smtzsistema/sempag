<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->name }} - {{ $category->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />
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

    <form method="POST" enctype="multipart/form-data" action="{{ route('public.registration.store', [$event, $category]) }}"
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

        {{-- SENHA + CONFIRMA SENHA --}}
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



        @if(method_exists($form, 'photoEnabled') && $form->photoEnabled())
            <div>
                <label class="block text-sm font-medium mb-2">Foto <span class="text-red-300">*</span></label>
                <input type="file" name="photo" required accept="image/png,image/jpeg"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                <p class="text-xs text-zinc-400 mt-2">
                    Envie uma foto (JPG/PNG). Ela aparece na sua área, no admin e pode ser usada na credencial.
                </p>
            </div>
        @endif
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
                    $opts = array_values($opts);
                }
                $opts = array_values(array_filter(array_map(fn($v) => trim((string)$v), (array)$opts), fn($v) => $v !== ''));

                // Máscaras / autofill
                $mask = null;
                $autofill = null;

                if ($field->type === 'cpf')  $mask = 'cpf';
                if ($field->type === 'cnpj') { $mask = 'cnpj'; $autofill = 'cnpj'; }
                if ($field->type === 'cep')  { $mask = 'cep';  $autofill = 'cep';  }


                if (in_array($field->key, ['ins_tel_celular', 'ins_celular', 'ins_whatsapp', 'ins_mobile'], true)) {
                    $mask = 'mobile_int';
                }

                if (in_array($field->key, ['ins_tel_comercial', 'ins_telefone', 'ins_fone', 'ins_phone'], true)) {
                    $mask = 'phone_int';
                }

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
                                <option value="{{ $opt }}" @selected((string)$oldVal === (string)$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('multiselect')
                        @php
                            $selected = is_array($oldVal) ? $oldVal : (strlen((string)$oldVal) ? [$oldVal] : []);
                            $selected = array_map('strval', $selected);
                        @endphp

                        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4 space-y-3">
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
                                            @if($mask) data-mask="{{ $mask }}" @endif
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
                        <input
                            id="f_{{ $fid }}"
                            type="text"
                            name="f[{{ $fid }}]"
                            value="{{ $oldVal }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            data-key="{{ $field->key }}"
                            @if($mask) data-mask="{{ $mask }}" @endif
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

@include('public.partials.masks-and-autofill')
</body>
</html>
