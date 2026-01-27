@extends('admin.layouts.app')

@section('title', 'Nova categoria')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Nova categoria')

@section('content')

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.system.categories.store', $event) }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Nome</label>
                <input name="cat_nome" value="{{ old('cat_nome', $category->cat_nome) }}" class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white" >
@error('cat_nome') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-end gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="cat_ativo" value="1" class="rounded"
                           @checked(old('cat_ativo', $category->cat_ativo) ? true : false)>
                    <span class="text-sm">Ativa</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="cat_aprova" value="1" class="rounded"
                           @checked(old('cat_aprova', $category->cat_aprova) ? true : false)>
                    <span class="text-sm">Requer aprovação</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-zinc-400 mb-1">Descrição</label>
                <textarea name="cat_descricao" rows="4"
                          class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">{{ old('cat_descricao', $category->cat_descricao) }}</textarea>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="text-sm font-semibold mb-4">Visibilidade agendada</div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Liberar em (opcional)</label>
                <input type="datetime-local" name="cat_date_start"
                       value="{{ old('cat_date_start') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                <div class="text-xs text-zinc-500 mt-1">Se vazio, a categoria segue apenas o status “Ativa”.</div>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Ocultar em (opcional)</label>
                <input type="datetime-local" name="cat_date_end"
                       value="{{ old('cat_date_end') }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
                <div class="text-xs text-zinc-500 mt-1">Se preenchido, a categoria some após essa data/hora.</div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="text-sm font-semibold mb-4">Banner da categoria (opcional)</div>
        <div>
            <label class="block text-xs text-zinc-400 mb-1">Enviar banner</label>
            <input type="file" name="cat_banner_path" accept="image/png,image/jpeg"
                   class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2">
            <div class="text-xs text-zinc-500 mt-1">Se você não enviar um banner, o sistema pode usar o banner do evento.</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2.5 transition">
            Criar
        </button>
        <a href="{{ route('admin.system.categories.index', $event) }}"
           class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-5 py-2.5">
            Cancelar
        </a>
    </div>

</form>

@endsection
