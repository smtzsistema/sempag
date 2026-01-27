@extends('admin.layouts.app')

@section('title', 'Usuários')
@section('breadcrumb', 'Admin • Usuários')
@section('page_title', 'Usuários do evento')

@section('top_actions')
    <a href="{{ route('admin.users.create', $event) }}"
       class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
        Novo usuário
    </a>
@endsection

@section('content')
    <div class="max-w-5xl">
        @if(session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-700 bg-emerald-950/40 p-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="p-5 border-b border-zinc-800 flex items-center justify-between gap-3">
                <div>
                    <div class="text-lg font-semibold">Lista</div>
                    <div class="text-sm text-zinc-400 mt-1">Aqui estão os usuários que têm acesso ao admin desse
                        evento.
                    </div>
                </div>
                <a href="{{ route('admin.users.create', $event) }}"
                   class="rounded-xl bg-zinc-950/40 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
                    + Adicionar
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-950/40 text-zinc-300">
                    <tr class="text-left">
                        <th class="px-5 py-3">Nome</th>
                        <th class="px-5 py-3">E-mail</th>
                        <th class="px-5 py-3">Grupo</th>
                        <th class="px-5 py-3">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                    @forelse($users as $u)
                        <tr class="hover:bg-zinc-950/30">
                            <td class="px-5 py-3">
                                <div class="font-semibold">{{ $u->usu_nome }}</div>
                                <div class="text-xs text-zinc-500">#{{ $u->usu_id }}</div>
                            </td>
                            <td class="px-5 py-3 text-zinc-200">{{ $u->usu_email }}</td>
                            <td class="px-5 py-3">
                                @php($roles = $u->roles?->pluck('name') ?? collect())
                                @if($roles->count())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($roles as $r)
                                            <span
                                                class="text-xs rounded-full border border-zinc-700 bg-zinc-950/40 px-2 py-1">
                                                {{ $r }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-500">Sem grupo</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.users.edit', [$event, $u]) }}"
                                   class="rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-xs hover:bg-zinc-800 transition">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-6 text-center text-zinc-400">Nenhum usuário vinculado
                                ainda.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
