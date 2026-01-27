@extends('admin.layouts.app')

@section('title', 'Exportar inscrições')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Exportar relatórios')

@section('content')

<div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
    <h2 class="text-lg font-semibold">Exportações disponíveis (CSV)</h2>
    <p class="text-sm text-zinc-400 mt-1">Os arquivos são gerados em UTF-8 com separador ";" (Excel PT-BR).</p>

    <div class="mt-5 grid md:grid-cols-4 gap-4">
        <a href="{{ route('admin.registrations.exports.filteredForm', $event) }}"
           class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4 hover:bg-zinc-900/60 transition">
            <div class="text-sm text-zinc-400">Relatório</div>
            <div class="text-lg font-semibold mt-1">Com filtros (categoria + status)</div>
            <div class="text-xs text-zinc-500 mt-2">Escolha um ou mais antes de exportar</div>
        </a>
        <a href="{{ route('admin.registrations.exports.all', $event) }}"
           class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4 hover:bg-zinc-900/60 transition">
            <div class="text-sm text-zinc-400">Relatório</div>
            <div class="text-lg font-semibold mt-1">Todas as inscrições</div>
            <div class="text-xs text-zinc-500 mt-2">Download direto</div>
        </a>

        <a href="{{ route('admin.registrations.exports.byCategoryForm', $event) }}"
           class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4 hover:bg-zinc-900/60 transition">
            <div class="text-sm text-zinc-400">Relatório</div>
            <div class="text-lg font-semibold mt-1">Por categoria</div>
            <div class="text-xs text-zinc-500 mt-2">Escolha a categoria antes de exportar</div>
        </a>

        <a href="{{ route('admin.registrations.exports.byStatusForm', $event) }}"
           class="rounded-2xl bg-zinc-950 border border-zinc-800 p-4 hover:bg-zinc-900/60 transition">
            <div class="text-sm text-zinc-400">Relatório</div>
            <div class="text-lg font-semibold mt-1">Por aprovação/status</div>
            <div class="text-xs text-zinc-500 mt-2">Escolha o status antes de exportar</div>
        </a>
    </div>
</div>

@endsection
