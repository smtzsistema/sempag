@extends('admin.layouts.app')

@section('title', 'Editar ficha')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Editar ficha')

@section('top_actions')
    <a href="{{ route('admin.system.forms.fields.index', [$event, $form]) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Configurar campos
    </a>
@endsection

@section('content')

<form method="POST" action="{{ route('admin.system.forms.update', [$event, $form]) }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <div class="text-xs text-zinc-400">Categoria</div>
                <div class="text-lg font-semibold">{{ $form->category?->cat_nome ?? '—' }}</div>
            </div>

            <div>
                <div class="text-xs text-zinc-400">Versão</div>
                <div class="text-lg font-semibold">{{ $form->form_versao }}</div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-zinc-400 mb-1">Nome da ficha</label>
                <input
                    type="text"
                    name="form_nome"
                    value="{{ old('form_nome', $form->form_nome) }}"
                    class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2"
                >
                @error('form_nome') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2 flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="form_ativo"
                        value="1"
                        class="rounded"
                        @checked(old('form_ativo', (bool) $form->form_ativo))
                    >
                    <span class="text-sm">Ativa</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2.5 transition">
            Salvar
        </button>
        <a href="{{ route('admin.system.forms.index', $event) }}"
           class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-5 py-2.5">
            Voltar
        </a>
    </div>

</form>

<div class="mt-8 rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Campos</h2>
        <a class="text-emerald-300 hover:underline" href="{{ route('admin.system.forms.fields.index', [$event, $form]) }}">
            Gerenciar
        </a>
    </div>

    <div class="mt-3 text-sm text-zinc-400">
        Total: {{ $form->fields->count() }}
    </div>

    <div class="mt-4 space-y-2">
        @forelse($form->fields as $field)
            <div class="rounded-xl bg-zinc-950 border border-zinc-800 p-3 flex items-start justify-between gap-3">
                <div>
                    <div class="font-semibold">{{ $field->fic_label }}</div>
                    <div class="text-xs text-zinc-500">
                        key: {{ $field->fic_nome }} • type: {{ $field->fic_tipo }} • ordem: {{ $field->fic_ordem }}
                    </div>
                </div>
                <a class="text-zinc-200 hover:underline"
                   href="{{ route('admin.system.forms.fields.edit', [$event, $form, $field]) }}">
                    Editar
                </a>
            </div>
        @empty
            <div class="text-zinc-400">Sem campos ainda.</div>
        @endforelse
    </div>
</div>

@endsection
