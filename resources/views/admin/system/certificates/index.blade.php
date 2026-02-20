@extends('admin.layouts.app')

@section('title', 'Certificados')
@section('breadcrumb', 'Admin • Configurações de Sistema')
@section('page_title', 'Certificados')

@section('top_actions')
    <a href="{{ route('admin.system.certificates.create', $event) }}"
       class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2 text-sm transition">
        Novo certificado
    </a>
@endsection

@section('content')

    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 overflow-hidden">
        <div class="p-5 border-b border-zinc-800">
            <div class="text-sm text-zinc-400">
                Aqui você configura modelos de certificado e vincula por categoria(s). Por enquanto, só A4.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-950/40 text-zinc-300">
                <tr>
                    <th class="text-left font-semibold px-4 py-3">Nome</th>
                    <th class="text-left font-semibold px-4 py-3">Categorias</th>
                    <th class="text-left font-semibold px-4 py-3">Tipo</th>
                    <th class="text-left font-semibold px-4 py-3">Espelhar</th>
                    <th class="text-left font-semibold px-4 py-3">Atualizado</th>
                    <th class="text-right font-semibold px-4 py-3">Ações</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                @forelse($certificates as $cert)
                    @php
                        $catNames = collect($cert->category_names ?? []);
                        $tipo = strtoupper($cert->cer_tipo ?? 'A4');
                        $espelhar = ($cert->cer_espelhar ?? 'N') === 'S';
                    @endphp
                    <tr class="hover:bg-zinc-950/30">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-zinc-100">{{ $cert->cer_nome }}</div>
                            @if(!empty($cert->cer_fundo))
                                <div class="text-xs text-zinc-400 mt-0.5">Com fundo</div>
                            @else
                                <div class="text-xs text-zinc-500 mt-0.5">Sem fundo</div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if($catNames->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($catNames as $nm)
                                        <span class="text-xs rounded-full border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-zinc-300">
                                            {{ $nm }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-zinc-500">(nenhuma)</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-xs rounded-full border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-zinc-300">
                                {{ $tipo }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-xs rounded-full border border-zinc-700 bg-zinc-950 px-2 py-0.5 {{ $espelhar ? 'text-emerald-300' : 'text-zinc-300' }}">
                                {{ $espelhar ? 'Sim' : 'Não' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-xs text-zinc-400">
                            {{ optional($cert->updated_at)->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.system.certificates.edit', [$event, $cert]) }}"
                               class="inline-flex items-center rounded-xl border border-zinc-800 bg-zinc-950/40 hover:bg-zinc-900 px-3 py-2 text-sm transition">
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-zinc-400">
                            Nenhum certificado cadastrado ainda.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($certificates, 'links'))
            <div class="p-5 border-t border-zinc-800">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>

@endsection
