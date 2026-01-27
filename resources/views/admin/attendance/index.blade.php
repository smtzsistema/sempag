@extends('admin.layouts.app')

@section('title', 'Presença por sala')
@section('breadcrumb', 'Admin • Inscrições')
@section('page_title', 'Lista por sala (presença)')

@section('content')

<div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-lg font-semibold">Salas encontradas</div>
            <div class="text-xs text-zinc-400 mt-1">Agrupado por <span class="text-zinc-200">eve_id</span> + <span class="text-zinc-200">pre_local</span>.</div>
        </div>

        <form method="get" class="w-full md:w-auto">
            <div class="flex gap-2">
                <input
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar sala..."
                    class="w-full md:w-72 rounded-xl bg-zinc-950/40 border border-zinc-800 px-3 py-2 text-sm outline-none focus:border-zinc-600"
                />
                <button class="rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-4 py-2 text-sm">
                    Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mt-4 rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
    <div class="p-5 border-b border-zinc-800">
        <div class="text-sm font-semibold">Export</div>
        <div class="text-xs text-zinc-400 mt-1">
            <b>Com duplicidade</b>: todas as passagens. • <b>Sem duplicidade</b>: só a primeira por inscrição.
            <br>CSV usa <b>labels da ficha</b> + labels da presença.
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-zinc-950/40 text-zinc-300">
                <tr>
                    <th class="text-left font-semibold px-4 py-3">Sala</th>
                    <th class="text-left font-semibold px-4 py-3">Passagens</th>
                    <th class="text-left font-semibold px-4 py-3">Únicos</th>
                    <th class="text-right font-semibold px-4 py-3">Exportar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($rooms as $room)
                    <tr class="hover:bg-zinc-950/30">
                        <td class="px-4 py-3">
                            <div class="font-medium text-zinc-100">{{ $room->pre_local ?: 'Sem nome' }}</div>
                        </td>
                        <td class="px-4 py-3 text-zinc-200">{{ (int) $room->total_rows }}</td>
                        <td class="px-4 py-3 text-zinc-200">{{ (int) $room->unique_ins }}</td>
                        <td class="px-4 py-3 text-right flex gap-2 justify-end">
                            <a
                                class="inline-flex items-center rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-3 py-2 text-xs"
                                href="{{ route('admin.attendance.export', [$event, 'local' => $room->pre_local, 'mode' => 'all']) }}"
                            >
                                Com duplicidade
                            </a>

                            <a
                                class="inline-flex items-center rounded-xl bg-sky-600 hover:bg-sky-500 text-zinc-950 font-semibold px-3 py-2 text-xs"
                                href="{{ route('admin.attendance.export', [$event, 'local' => $room->pre_local, 'mode' => 'unique']) }}"
                            >
                                Sem duplicidade
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-zinc-400">
                            Nenhuma presença encontrada ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
