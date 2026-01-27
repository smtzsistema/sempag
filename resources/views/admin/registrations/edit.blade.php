@extends('admin.layouts.app')

@section('title', 'Editar inscrição')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Editar inscrição')

@section('top_actions')
    <a href="{{ route('admin.registrations.show', [$event, $registration]) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>
@endsection

@section('content')

    <form method="POST" action="{{ route('admin.registrations.update', [$event, $registration]) }}" class="space-y-6">
        @csrf

        {{-- Card: Status/Motivo/Email --}}
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Dados principais</h2>
                    <div class="text-sm text-zinc-400 mt-1">
                        Token: <span class="font-mono">{{ $registration->ins_token }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid md:grid-cols-2 gap-4">
                @can('registrations.approve')
                    @php
                        $statusOptions = [
                            'E' => 'Em análise',
                            'S' => 'Aprovado',
                            'R' => 'Reprovado',
                        ];

                        if(auth()->user()?->can('registrations.delete')) {
                            $statusOptions['N'] = 'Excluído';
                        }

                        $currentStatus = old('ins_aprovado', $registration->ins_aprovado);
                    @endphp

                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Status</label>

                        <select name="ins_aprovado"
                                class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                            @foreach($statusOptions as $val => $label)
                                <option value="{{ $val }}" @selected((string)$currentStatus === (string)$val)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Motivo (se Reprovado)</label>
                        <input type="text" name="ins_motivo"
                               value="{{ old('ins_motivo', $registration->ins_motivo) }}"
                               class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2"
                               placeholder="Ex: Dados incompletos">
                    </div>
                @endcan

                @can('registrations.delete')
                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            formmethod="POST"
                            formaction="{{ route('admin.registrations.destroy', ['event' => $event, 'registration' => $registration->ins_token]) }}"
                            onclick="return confirm('Tem certeza que deseja excluir esta inscrição?');"
                            class="w-full rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold py-2.5 transition"
                        >
                            Excluir
                        </button>
                    </div>
                @endcan

                <div>
                    <label class="block text-xs text-zinc-400 mb-1">E-mail</label>
                    <input type="email" name="ins_email"
                           value="{{ old('ins_email', $registration->ins_email) }}"
                           class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
            <h2 class="text-lg font-semibold">Respostas do formulário</h2>
            <p class="text-sm text-zinc-400 mt-1">Edite os campos abaixo. Campos múltiplos continuam múltiplos
                (array).</p>

            <div class="mt-4 grid lg:grid-cols-2 gap-4">
                @foreach($fields as $field)
                    @php
                        $fid = $field->fic_id;
                        $name = "f[{$fid}]";
                        $oldKey = "f.{$fid}";

                        // valor atual salvo (controller geralmente manda $answersByFieldId keyBy('fic_id'))
                        $val = $valuesByFieldId[$fid] ?? null;

                        // metadados do campo (compatível com tua estrutura atual)
                        $label = $field->fic_label ?? $field->label ?? ('#'.$fid);
                        $key   = $field->fic_nome  ?? $field->key ?? null;
                        $type  = $field->fic_tipo  ?? $field->type ?? 'text';
                        $required = (string)($field->fic_obrigatorio ?? $field->required ?? 'N') === 'S' || (bool)($field->fic_obrigatorio ?? $field->required ?? false);

                        $placeholder = $field->fic_placeholder ?? $field->placeholder ?? '';
                        $helpText    = $field->fic_help_text ?? $field->help_text ?? null;

                        // options (pode vir castado, json, ou nulo)
                        $opts = $field->options ?? $field->fic_opcoes ?? null;
                        if (is_string($opts)) {
                            $decoded = json_decode($opts, true);
                            $opts = is_array($decoded) ? $decoded : null;
                        }
                        if (!is_array($opts)) $opts = [];
                    @endphp

                    <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold truncate">
                                    {{ $label }}
                                    @if($required)
                                        <span class="text-rose-400">*</span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500">
                                    key: <span class="text-zinc-300">{{ $key }}</span> • type: <span
                                        class="text-zinc-300">{{ $type }}</span>
                                </div>
                            </div>
                            @if($required)
                                <span
                                    class="text-xs rounded-full border border-zinc-700 bg-zinc-900 px-2 py-0.5 text-zinc-200">Obrigatório</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            @if($type === 'textarea')
                                <textarea name="{{ $name }}" rows="4"
                                          class="w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm"
                                          placeholder="{{ $placeholder }}">{{ old($oldKey, is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)($val ?? '')) }}</textarea>

                            @elseif($type === 'select' && !empty($opts))
                                <select name="{{ $name }}"
                                        class="w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach($opts as $op)
                                        <option
                                            value="{{ $op }}" @selected((string)old($oldKey, (string)$val) === (string)$op)>{{ $op }}</option>
                                    @endforeach
                                </select>

                            @elseif($type === 'checkbox')
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="{{ $name }}" value="1" class="rounded"
                                        @checked(old($oldKey, $val) ? true : false)>
                                    <span class="text-sm text-zinc-200">Marcado</span>
                                </label>

                            @elseif(($type === 'multiselect' || $type === 'checkbox_group') && !empty($opts))
                                @php
                                    $arr = is_array($val) ? $val : (is_string($val) && $val !== '' ? [$val] : []);
                                    $arrOld = old($oldKey, $arr);
                                    if (!is_array($arrOld)) $arrOld = [$arrOld];
                                @endphp
                                <select name="{{ $name }}[]" multiple
                                        class="w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm min-h-32">
                                    @foreach($opts as $op)
                                        <option
                                            value="{{ $op }}" @selected(in_array((string)$op, array_map('strval', $arrOld), true))>{{ $op }}</option>
                                    @endforeach
                                </select>

                            @else
                                <input type="{{ $type === 'email' ? 'email' : 'text' }}"
                                       name="{{ $name }}"
                                       value="{{ old($oldKey, is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)($val ?? '')) }}"
                                       class="w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm"
                                       placeholder="{{ $placeholder }}">
                            @endif

                            @if($helpText)
                                <div class="mt-2 text-xs text-zinc-500">{{ $helpText }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button
                class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2.5 transition">
                Salvar alterações
            </button>

            <a href="{{ route('admin.registrations.show', [$event, $registration]) }}"
               class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-5 py-2.5">
                Cancelar
            </a>
        </div>
    </form>

@endsection
