@extends('admin.layouts.app')

@section('title', 'Nova ficha')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Nova ficha')

@section('content')

<form method="POST" action="{{ route('admin.system.forms.store', $event) }}" class="space-y-6">
    @csrf

    @if(!empty($cloneForm))
        <input type="hidden" name="clone_form_id" value="{{ $cloneForm->form_id }}">
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
            <div class="text-sm">
                <div class="font-semibold text-zinc-100">Clonando ficha</div>
                <div class="text-zinc-400 mt-1">
                    Os campos serão copiados de <span class="text-zinc-200 font-medium">{{ $cloneForm->name }}</span>
                    (ID: {{ $cloneForm->id }})
                    @if($cloneForm->category)
                        • Categoria: <span class="text-zinc-200">{{ $cloneForm->category->name }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Categoria</label>
                <select name="cat_id" class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                    @foreach($categories as $c)
                        <option value="{{ $c->cat_id }}" @selected((string)old('cat_id') === (string)$c->cat_id)>
                            {{ $c->cat_nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-zinc-400 mb-1">Nome da ficha</label>
                <input
                    type="text"
                    name="form_nome"
                    value="{{ old('form_nome') }}"
                    class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2"
                    placeholder="Ex: Ficha Padrão">
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="form_ativo" value="1" class="rounded" @checked(old('form_ativo', 1))>
                    <span class="text-sm">Ativa</span>
                </label>
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="form_foto" value="1" class="rounded" @checked(old('form_foto', 0))>
                    <span class="text-sm">Módulo de foto (obrigatório)</span>
                </label>
            </div>
        </div>

        <p class="mt-4 text-sm text-zinc-400">
            A versão (<span class="text-zinc-200">form_versao</span>) é incrementada automaticamente por categoria.
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2.5 transition">
            Criar
        </button>
        <a href="{{ route('admin.system.forms.index', $event) }}" class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-5 py-2.5">
            Cancelar
        </a>
    </div>
</form>

@endsection
