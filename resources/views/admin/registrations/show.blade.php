@extends('admin.layouts.app')

@section('title', 'Inscrição')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Inscrição')

@section('top_actions')
    <a href="{{ route('admin.registrations.index', $event) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>

    @can('registrations.edit')
        <a href="{{ route('admin.registrations.edit', [$event, $registration]) }}"
           class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
            Editar
        </a>
    @endcan
@endsection

@section('content')

    @php
        $statusMap = ['S' => 'Aprovada', 'E' => 'Em análise', 'R' => 'Reprovada', 'N' => 'Excluída'];
        $status = $registration->ins_aprovado;
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
            <div class="grid lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2">
                    <div class="text-sm text-zinc-400">ID</div>
                    <div class="font-mono text-sm text-zinc-200">{{ $registration->ins_id }}</div>

                    <div class="mt-4 grid md:grid-cols-2 gap-3">
                        <div>
                            <div class="text-sm text-zinc-400">Status</div>
                            <div class="text-white font-semibold">{{ $statusMap[$status] ?? $status }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-400">Categoria</div>
                            <div class="text-white">{{ $registration->category?->cat_nome }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-400">Nome</div>
                            <div
                                class="text-white">{{ trim(($registration->ins_nome ?? '').' '.($registration->ins_sobrenome ?? '')) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-400">E-mail</div>
                            <div class="text-white">{{ $registration->ins_email }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-400">CPF</div>
                            <div class="text-white">{{ $registration->ins_cpf }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-zinc-400">Instituição</div>
                            <div class="text-white">{{ $registration->ins_instituicao }}</div>
                        </div>
                    </div>

                    @if(!empty($registration->ins_motivo))
                        <div class="mt-4">
                            <div class="text-sm text-zinc-400">Motivo</div>
                            <div class="text-zinc-200 whitespace-pre-line">{{ $registration->ins_motivo }}</div>
                        </div>
                    @endif
                </div>


<div class="lg:col-span-1 space-y-4">
    @can('fotos.view')
    <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
        <div class="text-sm font-semibold text-white">Foto</div>

        @if((string)($registration->form?->form_foto ?? 'N') !== 'S')
            <div class="mt-3 text-xs text-zinc-400">
                Módulo de foto desativado neste formulário.
            </div>
        @else
            <div class="mt-3 flex items-start gap-4">
                <div class="w-32 h-40 rounded-xl overflow-hidden border border-zinc-800 bg-zinc-900">
                    @if($registration->photo_url)
                        <img src="{{ $registration->photo_url }}" alt="Foto"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-xs text-zinc-400">
                            Sem foto
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    @can('fotos.edit')
                    <form method="POST" action="{{ route('admin.registrations.photo.update', [$event, $registration]) }}"
                          enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input type="file" name="photo" accept="image/*" required
                               class="block w-full text-xs text-zinc-200 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-800 file:px-3 file:py-2 file:text-xs file:text-zinc-200 hover:file:bg-zinc-700">
                        <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-2 text-sm transition">
                            Enviar nova foto
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.registrations.photo.destroy', [$event, $registration]) }}"
                          class="mt-2"
                          onsubmit="return confirm('Tem certeza que deseja excluir a foto?');">
                        @csrf
                        <button class="w-full rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 py-2 text-sm transition">
                            Excluir foto
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        @endif
    </div>
    @endcan

    @can('registrations.approve')
        <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                            <div class="text-sm font-semibold text-white">Aprovação</div>

                            @if($status == 'S')
                                <label class="block text-sm text-zinc-300">Inscrição já aprovada</label>
                            @else
                                <form method="POST"
                                      action="{{ route('admin.registrations.approve', [$event, $registration]) }}"
                                      class="mt-3">
                                    @csrf
                                    <button
                                        class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-2.5 transition">
                                        Aprovar
                                    </button>
                                </form>
                            @endif

                            <div class="mt-4 border-t border-zinc-800 pt-4">
                                <form method="POST"
                                      action="{{ route('admin.registrations.reject', [$event, $registration]) }}"
                                      class="space-y-3">
                                    @csrf
                                    <label class="block text-sm text-zinc-300">Motivo da reprovação</label>
                                    <textarea name="ins_motivo" rows="4"
                                              class="w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm text-zinc-200"
                                              placeholder="Ex: Dados incompletos, CPF inválido, Não identificado, não qualificado...">{{ old('ins_motivo', $registration->ins_motivo) }}</textarea>

                                    <button
                                        class="w-full rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 py-2.5 text-sm">
                                        Reprovar
                                    </button>
                                </form>
                            </div>
                        </div>
    @endcan
</div>

            </div>
        </div>
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
            <h2 class="text-lg font-semibold">Campos da ficha</h2>

            @if(($fields ?? collect())->isEmpty())
                <div class="mt-4 text-sm text-zinc-400">
                    Esta inscrição não possui ficha vinculada.
                </div>
            @else
                <div class="mt-4 grid lg:grid-cols-2 gap-4">
                    @foreach($fields as $field)
                        @php
                            $fid = $field->fic_id;
                            $val = $valuesByFieldId[$fid] ?? null;

                            $display = is_array($val)
                                ? implode(', ', array_map('strval', $val))
                                : (string)($val ?? '');

                            $display = trim($display) !== '' ? $display : '—';

                            $label = $field->fic_label ?? ('#'.$fid);
                            $meta  = ($field->fic_nome ?? '').' • '.($field->fic_tipo ?? '');
                        @endphp

                        <div class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4">
                            <label class="block text-sm font-semibold text-white">
                                {{ $label }}
                            </label>

                            {{-- textarea “read only” pra não quebrar layout com textos grandes --}}
                            @if(strlen($display) > 80)
                                <textarea
                                    class="mt-3 w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm text-zinc-200"
                                    rows="3"
                                    readonly
                                    disabled
                                >{{ $display }}</textarea>
                            @else
                                <input
                                    type="text"
                                    class="mt-3 w-full rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm text-zinc-200"
                                    value="{{ $display }}"
                                    readonly
                                    disabled
                                />
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection
