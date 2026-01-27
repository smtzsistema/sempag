@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-white">Novo campo</h1>
        <p class="mt-1 text-sm text-zinc-400">
            Ficha: {{ $form->name ?? ('#'.$form->id) }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-900 bg-rose-950 p-3 text-sm text-rose-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4">
        <form method="POST" action="{{ route('admin.system.forms.fields.store', [$event, $form]) }}">
            @csrf
            @include('admin.system.form_fields.partials.form', ['event'=>$event, 'form'=>$form, 'field'=>$field])
        </form>
    </div>
</div>
@endsection
