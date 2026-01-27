@extends('admin.layouts.app')

@section('title', 'Nova carta')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Nova carta de confirmação')

@section('top_actions')
    <a href="{{ route('admin.system.letters.index', $event) }}"
       class="rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-sm transition">
        Voltar
    </a>
@endsection

@section('content')
    @include('admin.system.letters.partials.form', [
        'mode' => 'create',
        'action' => route('admin.system.letters.store', $event),
        'letter' => null,
        'selectedCategories' => old('category_ids', []),
    ])
@endsection
