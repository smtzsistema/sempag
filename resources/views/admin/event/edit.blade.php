<x-admin-layout>
    <div class="p-6 max-w-3xl">
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-white">Editar evento #{{ $event->id }}</h1>
                <a href="{{ route('event.index') }}" class="text-sm text-zinc-300 hover:text-white">Voltar</a>
            </div>

            @if(session('success'))
                <div class="mt-4 rounded-xl border border-green-900 bg-green-950/40 p-3 text-green-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-4 rounded-xl border border-red-900 bg-red-950/40 p-3 text-red-200 text-sm">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-6 space-y-4" method="POST" action="{{ route('event.update', $event) }}">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm text-zinc-300 mb-1">Nome</label>
                    <input
                        name="name"
                        value="{{ old('name', $event->name) }}"
                        class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm text-zinc-300 mb-1">Descrição</label>
                    <textarea
                        name="description"
                        rows="6"
                        class="w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 text-white"
                    >{{ old('description', $event->description) }}</textarea>
                    <p class="mt-1 text-xs text-zinc-500">
                        Dica: quebras de linha ficam salvas. Para exibir com quebra, use <code>nl2br(e())</code>.
                    </p>
                </div>

                <div class="pt-2 flex gap-2">
                    <button class="rounded-xl bg-white text-black px-4 py-2 text-sm font-semibold hover:bg-zinc-200">
                        Salvar
                    </button>

                    <a href="{{ route('event.index') }}"
                       class="rounded-xl bg-zinc-800 hover:bg-zinc-700 px-4 py-2 text-sm text-white">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
