@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Novo Grupo' : 'Editar Grupo')
@section('breadcrumb', 'Admin • Sistema • Grupos')
@section('page_title', $mode === 'create' ? 'Novo Grupo' : 'Editar Grupo')

@section('top_actions')
    <a href="{{ route('admin.system.roles.index', $event) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-700 bg-emerald-900/30 p-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-rose-700 bg-rose-900/30 p-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-700 bg-rose-900/30 p-3 text-sm">
            <div class="font-semibold mb-2">Corrige aí:</div>
            <ul class="list-disc ml-5 space-y-1 text-zinc-200">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $mode === 'create'
                ? route('admin.system.roles.store', $event)
                : route('admin.system.roles.update', [$event, $role]) }}"
          class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-zinc-300 mb-2">Nome do grupo</label>
            <input type="text" name="name"
                   value="{{ old('name', $role->name ?? '') }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm outline-none focus:border-emerald-600">
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm text-zinc-300">Permissões</label>
                <div class="text-xs text-zinc-500">Marca o que esse grupo pode acessar</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @php
                    $grouped = $permissions->groupBy(fn($p) => $p->perm_group ?: 'Outros');
                @endphp

                @foreach($grouped as $groupName => $items)
                    <div class="mt-4">
                        <div class="text-xs font-semibold text-zinc-400 tracking-wide mb-2">
                            {{ strtoupper($groupName) }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($items as $perm)
                                @php
                                    $checked = in_array($perm->name, old('permissions', $selected ?? []), true);
                                    $label = $perm->perm_label ?: $perm->name;
                                @endphp

                                <label
                                    class="flex items-start gap-3 rounded-xl border border-zinc-800 bg-zinc-950/30 p-3 text-sm hover:bg-zinc-900/40">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                           @checked($checked)
                                           class="mt-1 accent-emerald-600">

                                    <div class="leading-tight">
                                        <div class="text-zinc-100 font-medium">{{ $label }}</div>
                                        @if(!empty($perm->perm_desc))
                                            <div class="text-xs text-zinc-400 mt-0.5">{{ $perm->perm_desc }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button type="submit"
                    class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-3 text-sm transition">
                Salvar
            </button>
        </div>
    </form>

    @if($mode === 'edit')
        <div class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold">Excluir grupo</div>
                    <div class="text-xs text-zinc-400 mt-1">Cuidado: isso remove o grupo. Usuários ficam sem esse
                        grupo.
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('admin.system.roles.destroy', [$event, $role]) }}"
                      onsubmit="return confirm('Excluir este grupo?');">
                    @csrf
                    <button type="submit"
                            class="rounded-xl border border-rose-800 bg-rose-950/40 px-4 py-2 text-sm hover:bg-rose-900/40 transition">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection
