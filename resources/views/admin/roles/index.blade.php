@extends('admin.layouts.app')

@section('title', 'Grupos de Permissões')
@section('breadcrumb', 'Admin • Sistema • Grupos')
@section('page_title', 'Grupos de Permissões')

@section('top_actions')
    <a href="{{ route('admin.system.roles.create', $event) }}"
       class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
        Novo grupo
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

    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-zinc-950/50">
            <tr class="text-left">
                <th class="p-4">Grupo</th>
                <th class="p-4">Usuários</th>
                <th class="p-4 w-56">Ações</th>
            </tr>
            </thead>
            <tbody>
            @foreach($roles as $role)
                <tr class="border-t border-zinc-800">
                    <td class="p-4 font-semibold">{{ $role->name }}</td>
                    <td class="p-4 text-zinc-300">{{ $role->users_count }}</td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.system.roles.edit', [$event, $role]) }}"
                           class="rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-xs hover:bg-zinc-800 transition">
                            Editar
                        </a>

                        <form method="POST" action="{{ route('admin.system.roles.destroy', [$event, $role]) }}"
                              onsubmit="return confirm('Excluir este grupo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-xl border border-rose-800 bg-rose-950/40 px-3 py-2 text-xs hover:bg-rose-900/40 transition">
                                Excluir
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            @if($roles->isEmpty())
                <tr class="border-t border-zinc-800">
                    <td class="p-4 text-zinc-400" colspan="3">Nenhum grupo criado.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endsection
