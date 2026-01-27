@extends('admin.layouts.app')

@section('title', 'Editar carta')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Editar carta de confirmação')

@section('top_actions')
    <a href="{{ route('admin.system.letters.index', $event) }}"
       class="rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>
@endsection

@section('content')
    @include('admin.system.letters.partials.form', [
        'mode' => 'edit',
        'action' => route('admin.system.letters.update', [$event, $letter]),
        'letter' => $letter,
        'selectedCategories' => $selectedCategories ?? [],
    ])

    <form id="deleteLetterForm" method="POST" action="{{ route('admin.system.letters.destroy', [$event, $letter]) }}" class="hidden">
        @csrf
    </form>
@endsection
