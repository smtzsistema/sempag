@extends('admin.layouts.app')

@section('title', $mode === 'create' ? 'Novo Usuário' : 'Editar Usuário')
@section('breadcrumb', 'Admin • Usuários')
@section('page_title', $mode === 'create' ? 'Novo Usuário' : 'Editar Usuário')

@section('top_actions')
    <a href="{{ route('admin.users.index', $event) }}"
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

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-700 bg-rose-900/30 p-3 text-sm">
            <div class="font-semibold mb-2">Corrige aí:</div>
            <ul class="list-disc ml-5 space-y-1 text-zinc-200">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $mode === 'create' ? route('admin.users.store', $event) : route('admin.users.update', [$event, $user]) }}"
          class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-zinc-300 mb-2">Nome</label>
            <input type="text" name="usu_nome"
                   value="{{ old('usu_nome', $user->usu_nome ?? '') }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm outline-none focus:border-emerald-600">
        </div>

        <div>
            <label class="block text-sm text-zinc-300 mb-2">Email</label>
            <input type="email" name="usu_email"
                   value="{{ old('usu_email', $user->usu_email ?? '') }}"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm outline-none focus:border-emerald-600">
        </div>

        @if($mode === 'create')
            <div>
                <label class="block text-sm text-zinc-300 mb-2">Senha</label>
                <input type="password" name="usu_password"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm outline-none focus:border-emerald-600">
            </div>
        @endif

        <div>
            <label class="block text-sm text-zinc-300 mb-2">Grupo (Role)</label>
            <select name="role"
                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm outline-none focus:border-emerald-600">
                <option value="">Sem grupo</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}"
                        @selected(old('role', $currentRole ?? '') === $r->name)>
                        {{ $r->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-3 text-sm transition">
                Salvar
            </button>
        </div>
    </form>

    @if($mode === 'edit')
        <div class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 space-y-3">
            <div class="font-semibold">Reset de senha</div>
            <div class="text-xs text-zinc-400">
            <b>Vazio gera uma senha aleatória.</b>
            </div>

            <form method="POST" action="{{ route('admin.users.reset_password', [$event, $user]) }}" class="flex gap-2 items-center">
                @csrf
                <input type="password" name="password" placeholder="Nova senha (opcional)"
                       class="flex-1 rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm outline-none focus:border-emerald-600">
                <button type="submit"
                        class="rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-3 text-sm hover:bg-zinc-800 transition">
                    Redefinir
                </button>
            </form>
        </div>
    @endif
@endsection
