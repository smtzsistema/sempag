<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar dados - {{ $event->name }}</title>
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
<div class="max-w-4xl mx-auto p-6">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Editar dados</h1>
                <p class="text-zinc-400 text-sm mt-1">{{ $event->name }}</p>
            </div>

            <a href="{{ route('public.attendee.area', $event) }}"
               class="rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-4 py-2 font-semibold">
                Voltar
            </a>
        </div>

        @if(session('ok'))
            <div class="mt-4 p-3 rounded-lg bg-emerald-900/25 border border-emerald-800 text-emerald-200">
                {{ session('ok') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 p-3 rounded-lg bg-red-900/25 border border-red-800 text-red-200">
                <div class="font-semibold mb-2">Corrija os campos:</div>
                <ul class="list-disc ml-5 text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-6 space-y-4" method="POST" action="{{ route('public.attendee.update', $event) }}">
            @csrf

            @if($fields->isEmpty())
                <div class="rounded-xl bg-zinc-950 border border-zinc-800 p-4 text-zinc-300">
                    Nenhum campo está liberado para edição.
                </div>
            @endif

            @foreach($fields as $field)
                @php
                    $name = "f[{$field->id}]";
                    $val = $current[$field->id] ?? null;
                    $opts = is_array($field->options) ? $field->options : [];
                @endphp

                <div class="rounded-xl bg-zinc-950 border border-zinc-800 p-4">
                    <label class="block font-semibold mb-2">
                        {{ $field->label }}
                        @if($field->required) <span class="text-red-400">*</span> @endif
                    </label>

                    @if($field->type === 'textarea')
                        <textarea name="{{ $name }}"
                                  rows="4"
                                  class="w-full rounded-lg bg-zinc-900 border border-zinc-700 px-3 py-2 text-zinc-100"
                                  placeholder="{{ $field->placeholder ?? '' }}">{{ $val }}</textarea>

                    @elseif($field->type === 'select')
                        <select name="{{ $name }}"
                                class="w-full rounded-lg bg-zinc-900 border border-zinc-700 px-3 py-2 text-zinc-100">
                            <option value="">Selecione...</option>
                            @foreach($opts as $op)
                                <option value="{{ $op }}" @selected((string)$val === (string)$op)>{{ $op }}</option>
                            @endforeach
                        </select>

                    @elseif($field->type === 'checkbox' && !empty($opts))
                        @php
                            $arr = is_array($val) ? $val : (empty($val) ? [] : [$val]);
                        @endphp

                        <div class="space-y-2">
                            @foreach($opts as $op)
                                <label class="flex items-center gap-2 text-zinc-200">
                                    <input type="checkbox"
                                           name="{{ $name }}[]"
                                           value="{{ $op }}"
                                           class="rounded bg-zinc-900 border border-zinc-700"
                                           @checked(in_array($op, $arr))>
                                    <span>{{ $op }}</span>
                                </label>
                            @endforeach
                        </div>

                    @else
                        @php
                            $type = in_array($field->type, ['email','number','date','password']) ? $field->type : 'text';
                        @endphp
                        <input type="{{ $type }}"
                               name="{{ $name }}"
                               value="{{ is_array($val) ? '' : $val }}"
                               placeholder="{{ $field->placeholder ?? '' }}"
                               class="w-full rounded-lg bg-zinc-900 border border-zinc-700 px-3 py-2 text-zinc-100">
                    @endif

                    @if($field->help_text)
                        <p class="text-zinc-400 text-sm mt-2">{{ $field->help_text }}</p>
                    @endif
                </div>
            @endforeach

            @if(!$fields->isEmpty())
                <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-3 transition">
                    Salvar alterações
                </button>
            @endif
        </form>
    </div>
</div>
</body>
</html>
