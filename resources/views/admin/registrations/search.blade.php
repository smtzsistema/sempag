@extends('admin.layouts.app')

@section('title', 'Busca de inscrito')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Busca de inscrito')

@section('content')
<div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
    <h2 class="text-lg font-semibold">Em breve</h2>
    <p class="text-sm text-zinc-400 mt-1">
        A ideia aqui é ter uma busca rápida (nome, e-mail, CPF, token) com atalho para abrir a inscrição.
    </p>
    <div class="mt-4">
        <a href="{{ route('admin.registrations.index', $event) }}" class="text-sm text-emerald-300 hover:underline">→ Usar a lista com filtros</a>
    </div>
</div>
@endsection
