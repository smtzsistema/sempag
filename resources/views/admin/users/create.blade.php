@extends('admin.layouts.app')

@section('title', 'Novo usuário')
@section('breadcrumb', 'Admin • Usuários')
@section('page_title', 'Novo usuário')

@section('top_actions')
    <a href="{{ route('admin.users.index', $event) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>
@endsection

@section('content')
<div class="max-w-3xl">

    @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-700 bg-emerald-950/40 p-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-2xl border border-rose-700 bg-rose-950/30 p-4 text-sm">
            <div class="font-semibold mb-2">Ajusta isso aqui rapidinho:</div>
            <ul class="list-disc pl-5 space-y-1 text-rose-200">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
        <div class="text-lg font-semibold">Criar usuário</div>
        <div class="text-sm text-zinc-400 mt-1">O usuário vai entrar no admin desse evento conforme o grupo (role) escolhido.</div>

        <form method="POST" action="{{ route('admin.users.store', $event) }}" class="mt-5 space-y-4">
            @csrf

            <div>
                <label class="text-sm text-zinc-300">Nome</label>
                <input type="text" name="usu_nome" value="{{ old('usu_nome') }}"
                       class="mt-1 w-full rounded-xl bg-zinc-950/40 border border-zinc-800 px-4 py-2 text-sm outline-none focus:border-zinc-600" />
            </div>

            <div>
                <label class="text-sm text-zinc-300">E-mail</label>
                <input type="email" name="usu_email" value="{{ old('usu_email') }}"
                       class="mt-1 w-full rounded-xl bg-zinc-950/40 border border-zinc-800 px-4 py-2 text-sm outline-none focus:border-zinc-600" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-zinc-300">Senha</label>
                    <input type="password" name="usu_password"
                           class="mt-1 w-full rounded-xl bg-zinc-950/40 border border-zinc-800 px-4 py-2 text-sm outline-none focus:border-zinc-600" />
                </div>
                <div>
                    <label class="text-sm text-zinc-300">Confirmar senha</label>
                    <input type="password" name="usu_password_confirmation"
                           class="mt-1 w-full rounded-xl bg-zinc-950/40 border border-zinc-800 px-4 py-2 text-sm outline-none focus:border-zinc-600" />
                </div>
            </div>

            <div>
                <label class="text-sm text-zinc-300">Grupo (permissões)</label>
                <select name="role_id"
                        class="mt-1 w-full rounded-xl bg-zinc-950/40 border border-zinc-800 px-4 py-2 text-sm outline-none focus:border-zinc-600">
                    <option value="">Selecione…</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <div class="text-xs text-zinc-500 mt-1">Ex.: <span class="text-zinc-300">Dados</span> (só listas/busca/export/estatísticas) ou <span class="text-zinc-300">Admin</span> (tudo).</div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2 text-sm transition">
                    Criar usuário
                </button>

                <a href="{{ route('admin.users.index', $event) }}"
                   class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-5 py-2 text-sm transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
