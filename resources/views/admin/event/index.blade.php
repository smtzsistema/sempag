@extends('admin.layouts.app')

@section('title', 'Evento')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Configuração do evento')

@section('content')
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.system.event.update', ['event' => $event]) }}" class="space-y-4" enctype="multipart/form-data">

            @csrf

            <div>
                <label class="block text-sm text-zinc-300 mb-1">Nome</label>
                <input name="eve_nome" value="{{ old('eve_nome', $event->eve_nome) }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white" required>
                @error('eve_nome') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-300 mb-1">Slug</label>
                <input name="eve_slug" value="{{ old('eve_slug', $event->eve_slug) }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white">
                @error('eve_slug') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-zinc-300 mb-1">Data início</label>
                    <input
                        type="text"
                        name="eve_data_inicio"
                        value="{{ old('eve_data_inicio', $event->eve_data_inicio?->format('Y-m-d')) }}"
                        class="js-date w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white"
                        placeholder="YYYY-MM-DD"
                        autocomplete="off"
                    />
                    @error('eve_data_inicio') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-300 mb-1">Data fim</label>
                    <input
                        type="text"
                        name="eve_data_fim"
                        value="{{ old('eve_data_fim', $event->eve_data_fim?->format('Y-m-d')) }}"
                        class="js-date w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white"
                        placeholder="YYYY-MM-DD"
                        autocomplete="off"
                    />
                    @error('eve_data_fim') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-zinc-300 mb-1">Local</label>
                <input name="eve_local" value="{{ old('eve_local', $event->eve_local) }}"
                       class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white">
                @error('eve_local') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
            <label class="block text-sm text-zinc-300 mb-1">Descrição</label>

            <textarea
                name="eve_descricao"
                rows="6"
                class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white"
            >{{ old('eve_descricao', $event->eve_descricao) }}</textarea>

            @error('eve_descricao') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
        </div>


            <div>
                <label class="block text-sm text-zinc-300 mb-1">Banner (png/jpg/jpeg até 5MB)<br><b>É ALTAMENTE RECOMENDADO QUE A IMAGEM TENHA AS PROPORÇÕES 1200X200 CASO CONTRARIO A AQULIDADE OU TAMANHO PODE FICAR INSTAVEL)</b></label>

                @if(!empty($event->eve_banner))
                    <img src="{{ asset('storage/'.$event->eve_banner) }}"
                         class="mb-3 w-full max-w-xl rounded-xl border border-zinc-800"
                         alt="Banner atual">
                @endif

                <input type="file" name="eve_banner" accept="image/png,image/jpeg"
                       class="block w-full text-sm text-zinc-200
                              file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-800 file:px-4 file:py-2 file:text-sm file:text-white hover:file:bg-zinc-700">
                @error('eve_banner') <div class="text-red-300 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="pt-2">
                <button class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
                    Salvar
                </button>
            </div>
        </form>
    </div>
@endsection
