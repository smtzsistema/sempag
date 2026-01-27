@extends('admin.layouts.app')

@section('title', 'Nova credencial')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Nova credencial')

@section('top_actions')
    <a href="{{ route('admin.system.credentials.index', $event) }}"
       class="rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>
@endsection

@section('content')

    <div class="grid md:grid-cols-2 gap-4">

        <a href="#"
           class="group rounded-2xl bg-zinc-900 border border-zinc-800 p-6 hover:bg-zinc-950/30 transition relative overflow-hidden"
           onclick="return alert('Em breve 😅');">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-lg font-semibold">Etiqueta</div>
                    <div class="text-sm text-zinc-400 mt-1">Modelo tipo etiqueta (impressão em rolo/folha).</div>
                </div>
                <span class="text-xs rounded-full border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-zinc-300">Em breve</span>
            </div>
            <div class="mt-4 text-xs text-zinc-500">Clique só pra ver a mensagem mesmo.</div>
        </a>

        <a href="{{ route('admin.system.credentials.createA4', $event) }}"
           class="group rounded-2xl bg-zinc-900 border border-zinc-800 p-6 hover:bg-zinc-950/30 transition relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-lg font-semibold">A4</div>
                    <div class="text-sm text-zinc-400 mt-1">Modelo em folha A4 (em pé), com preview customizável.</div>
                </div>
                <span class="text-xs rounded-full border border-emerald-700 bg-emerald-950/30 px-2 py-0.5 text-emerald-300">Ativo</span>
            </div>
            <div class="mt-4 text-xs text-zinc-500">Vai pro builder.</div>
        </a>

    </div>

@endsection
