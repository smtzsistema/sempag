<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <title>@yield('title', 'Admin') • {{ $event->name ?? 'Evento' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">

    {{-- Alpine (accordion) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />

@php
    $navItem = function (string $label, string $href, bool $active = false, ?string $badge = null) {
        $base = 'flex items-center justify-between gap-3 rounded-xl px-3 py-2 text-sm border transition';
        $cls  = $active
            ? $base.' bg-zinc-900 border-zinc-700 text-zinc-50'
            : $base.' bg-zinc-950/40 border-zinc-800 text-zinc-300 hover:bg-zinc-900/60 hover:border-zinc-700';

        return [
            'label' => $label,
            'href'  => $href,
            'cls'   => $cls,
            'badge' => $badge,
        ];
    };

    $is = fn($pattern) => request()->routeIs($pattern);

    // Qual grupo abre por padrão (pela rota atual)
    $openGroup = 'general';

if ($is('admin.system.*')) {
    $openGroup = 'system';
} elseif ($is('admin.registrations.*') || $is('admin.attendance.*')) {
    $openGroup = 'registrations';
} elseif ($is('admin.stats.*')) {
    $openGroup = 'stats';
} elseif ($is('admin.users.*')) {
    $openGroup = 'users';
} elseif ($is('admin.sync.*')) {
    $openGroup = 'sync';
}

@endphp

<!-- Mobile overlay -->
<div id="adminMobileOverlay" class="fixed inset-0 z-40 hidden bg-black/60 md:hidden"
     onclick="adminCloseSidebar()"></div>

<div class="min-h-screen md:flex">

    <!-- Sidebar -->
    <aside id="adminSidebar"
           class="fixed inset-y-0 left-0 z-50 hidden w-80 shrink-0 border-r border-zinc-800 bg-zinc-950 md:static md:block">
        <div class="p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-zinc-400">Admin</div>
                    <div class="text-lg font-semibold leading-tight">{{ $event->name }}</div>
                </div>
                <button class="md:hidden rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-sm"
                        onclick="adminCloseSidebar()">
                    Fechar
                </button>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('public.event.landing', $event) }}"
                   class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-3 py-2 text-sm transition">
                    Nova inscrição
                </a>
                <a href="{{ route('admin.dashboard', $event) }}"
                   class="rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-zinc-800 px-3 py-2 text-sm transition">
                    Dashboard
                </a>
            </div>

            <div class="mt-6 space-y-6"
                 x-data="{ open: @js($openGroup) }"
                 x-cloak
            >

                @php
                    $groupBtn = function (string $label, string $key) {
                        return [
                            'label' => $label,
                            'key' => $key,
                            'cls' => 'w-full flex items-center justify-between gap-3 text-left rounded-xl px-3 py-2 text-xs font-semibold tracking-wide border transition
                                     bg-zinc-950/40 border-zinc-800 text-zinc-400 hover:bg-zinc-900/60 hover:border-zinc-700',
                        ];
                    };
                @endphp

                <!-- Geral (accordion) -->
                <div>
                    @php($g = $groupBtn('GERAL', 'general'))
                    <button type="button"
                            class="{{ $g['cls'] }}"
                            @click="open = (open === '{{ $g['key'] }}' ? null : '{{ $g['key'] }}')"
                            :aria-expanded="open === '{{ $g['key'] }}'">
                        <span>{{ $g['label'] }}</span>
                        <svg class="h-4 w-4 text-zinc-500 transition-transform"
                             :class="open === '{{ $g['key'] }}' ? 'rotate-180' : ''"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div class="mt-2 space-y-2" x-show="open === '{{ $g['key'] }}'" x-collapse>
                        @can('dashboard.view')
                            @php($i = $navItem('Dashboard', route('admin.dashboard', $event), $is('admin.dashboard')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}">
                                <span>{{ $i['label'] }}</span>
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Configurações (accordion) -->
                @can('system.manage')
                    <div>
                        @php($g = $groupBtn('CONFIGURAÇÕES DE SISTEMA', 'system'))
                        <button type="button"
                                class="{{ $g['cls'] }}"
                                @click="open = (open === '{{ $g['key'] }}' ? null : '{{ $g['key'] }}')"
                                :aria-expanded="open === '{{ $g['key'] }}'">
                            <span>{{ $g['label'] }}</span>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform"
                                 :class="open === '{{ $g['key'] }}' ? 'rotate-180' : ''"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="mt-2 space-y-2" x-show="open === '{{ $g['key'] }}'" x-collapse>
                            @php($i = $navItem('Configuração do Evento', route('admin.system.event.index', $event), $is('admin.system.event.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>

                            @php($i = $navItem('Configuração de categorias', route('admin.system.categories.index', $event), $is('admin.system.categories.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>

                            @php($i = $navItem('Configuração de fichas', route('admin.system.forms.index', $event), $is('admin.system.forms.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>

                            @php($i = $navItem('Cartas de confirmação', route('admin.system.letters.index', $event), $is('admin.system.letters.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>

                            @php($i = $navItem('Grupos de Permissões', route('admin.system.roles.index', $event), $is('admin.system.roles.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>

                            @php($i = $navItem('Configurações de Credenciais', route('admin.system.credentials.index', $event), $is('admin.system.credentials.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>
                        </div>
                    </div>
                @endcan

                <!-- Inscrições (accordion) -->
                <div>
                    @php($g = $groupBtn('INSCRIÇÕES', 'registrations'))
                    <button type="button"
                            class="{{ $g['cls'] }}"
                            @click="open = (open === '{{ $g['key'] }}' ? null : '{{ $g['key'] }}')"
                            :aria-expanded="open === '{{ $g['key'] }}'">
                        <span>{{ $g['label'] }}</span>
                        <svg class="h-4 w-4 text-zinc-500 transition-transform"
                             :class="open === '{{ $g['key'] }}' ? 'rotate-180' : ''"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div class="mt-2 space-y-2" x-show="open === '{{ $g['key'] }}'" x-collapse>
                        @can('registrations.view')
                            @php($i = $navItem('Lista de inscritos', route('admin.registrations.index', $event), $is('admin.registrations.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}"><span>{{ $i['label'] }}</span></a>
                        @endcan

                        @can('registrations.export')
                            @php($i = $navItem('Exportar relatório (CSV)', '#', false))
                            <button type="button" onclick="adminOpenGlobalExport()"
                                    class="{{ $i['cls'] }} w-full text-left">
                                <span>{{ $i['label'] }}</span>
                            </button>
                        @endcan

                        @can('registrations.salas')
                            @php($i = $navItem('Lista por sala (presença)', route('admin.attendance.index', $event), $is('admin.attendance.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}">
                                <span>{{ $i['label'] }}</span>
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Estatísticas (accordion) -->
                @can('stats.view')
                    <div>
                        @php($g = $groupBtn('ESTATÍSTICAS', 'stats'))
                        <button type="button"
                                class="{{ $g['cls'] }}"
                                @click="open = (open === '{{ $g['key'] }}' ? null : '{{ $g['key'] }}')"
                                :aria-expanded="open === '{{ $g['key'] }}'">
                            <span>{{ $g['label'] }}</span>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform"
                                 :class="open === '{{ $g['key'] }}' ? 'rotate-180' : ''"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="mt-2 space-y-2" x-show="open === '{{ $g['key'] }}'" x-collapse>
                            @php($i = $navItem('Painel de estatísticas', route('admin.stats.index', $event), $is('admin.stats.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}">
                                <span>{{ $i['label'] }}</span>
                            </a>
                        </div>
                    </div>
                @endcan

                <!-- Usuários (accordion) -->
                @can('users.manage')
                    <div>
                        @php($g = $groupBtn('USUÁRIOS', 'users'))
                        <button type="button"
                                class="{{ $g['cls'] }}"
                                @click="open = (open === '{{ $g['key'] }}' ? null : '{{ $g['key'] }}')"
                                :aria-expanded="open === '{{ $g['key'] }}'">
                            <span>{{ $g['label'] }}</span>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform"
                                 :class="open === '{{ $g['key'] }}' ? 'rotate-180' : ''"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="mt-2 space-y-2" x-show="open === '{{ $g['key'] }}'" x-collapse>
                            @php($i = $navItem('Novo usuário', route('admin.users.create', $event), $is('admin.users.create')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}">
                                <span>{{ $i['label'] }}</span>
                            </a>

                            @php($i = $navItem('Lista de usuários', route('admin.users.index', $event), $is('admin.users.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}">
                                <span>{{ $i['label'] }}</span>
                            </a>
                        </div>
                    </div>
                @endcan

                <!-- Exportar/Importar (accordion) -->
                @can('sync.manage')
                    <div>
                        @php($g = $groupBtn('EXPORTAR / IMPORTAR', 'sync'))
                        <button type="button"
                                class="{{ $g['cls'] }}"
                                @click="open = (open === '{{ $g['key'] }}' ? null : '{{ $g['key'] }}')"
                                :aria-expanded="open === '{{ $g['key'] }}'">
                            <span>{{ $g['label'] }}</span>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform"
                                 :class="open === '{{ $g['key'] }}' ? 'rotate-180' : ''"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="mt-2 space-y-2" x-show="open === '{{ $g['key'] }}'" x-collapse>
                            @php($i = $navItem('Sincronização', route('admin.sync.index', $event), $is('admin.sync.*')))
                            <a href="{{ $i['href'] }}" class="{{ $i['cls'] }}">
                                <span>{{ $i['label'] }}</span>
                            </a>
                        </div>
                    </div>
                @endcan

                <div class="pt-2 border-t border-zinc-800">
                    <form method="POST" action="{{ route('admin.logout', $event) }}">
                        @csrf
                        <button
                            class="w-full rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 text-sm">
                            Sair
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 md:ml-0">

        <!-- Topbar -->
        <div class="sticky top-0 z-30 border-b border-zinc-800 bg-zinc-950/70 backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button class="md:hidden rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2 text-sm"
                            onclick="adminOpenSidebar()">
                        Menu
                    </button>

                    <div>
                        <div class="text-xs text-zinc-400">@yield('breadcrumb', 'Admin')</div>
                        <div class="text-lg font-semibold leading-tight">@yield('page_title', 'Painel')</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @yield('top_actions')
                </div>
            </div>
        </div>

        <main class="mx-auto max-w-7xl px-4 sm:px-6 py-6">
            @include('admin.partials.flash')
            @yield('content')
        </main>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.flatpickr) {
            flatpickr('.js-date', {
                dateFormat: 'Y-m-d',
                locale: 'pt',
                allowInput: true
            });
        }
    });
</script>

{{-- Modal GLOBAL export CSV (categoria + status) --}}

<div id="adminGlobalExportModal" class="fixed inset-0 z-[999] hidden">
    <div class="absolute inset-0 bg-black/60" onclick="adminCloseGlobalExport()"></div>

    <div class="relative mx-auto mt-16 w-[95%] max-w-2xl rounded-2xl bg-zinc-900 border border-zinc-800 shadow-2xl">
        <div class="p-5 border-b border-zinc-800 flex items-start justify-between gap-3">
            <div>
                <div class="text-lg font-semibold">Exportar relatório (CSV)</div>
                <div class="text-xs text-zinc-400 mt-1">
                    Filtre por categoria e/ou status. Se não selecionar nada, exporta tudo daquele filtro.
                </div>
            </div>
            <button type="button"
                    class="rounded-xl border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm"
                    onclick="adminCloseGlobalExport()">
                Fechar
            </button>
        </div>

        <div id="admin_modal_all">
            <div class="p-5">
                <form method="GET"
                      action="{{ route('admin.registrations.exports.filtered', $event) }}"
                      class="grid md:grid-cols-12 gap-4">
                    <div class="md:col-span-7">
                        <div class="flex items-center justify-between gap-3">
                            <label class="block text-xs text-zinc-400">Categorias (multi)</label>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-xs text-emerald-300 hover:underline"
                                        onclick="adminSelAll('admin_modal_cat_ids')">Selecionar todas
                                </button>
                                <span class="text-zinc-700">•</span>
                                <button type="button" class="text-xs text-zinc-300 hover:underline"
                                        onclick="adminSelNone('admin_modal_cat_ids')">Limpar
                                </button>
                            </div>
                        </div>

                        <div id="admin_modal_cat_ids"
                             class="mt-2 w-full rounded-2xl bg-zinc-950 border border-zinc-800 p-2 max-h-64 overflow-auto">
                            @foreach(($categories ?? \App\Models\Category::where('eve_id', $event->id)->orderBy('cat_nome')->get()) as $c)
                                <label
                                    class="flex items-center gap-2 rounded-xl px-2 py-2 hover:bg-zinc-900/60 cursor-pointer">
                                    <input type="checkbox" name="cat_ids[]" value="{{ $c->cat_id }}"
                                           class="h-4 w-4 accent-emerald-500">
                                    <span class="text-sm text-zinc-200">{{ $c->cat_nome }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="md:col-span-5">
                        <div class="flex items-center justify-between gap-3">
                            <label class="block text-xs text-zinc-400">Status (multi)</label>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-xs text-emerald-300 hover:underline"
                                        onclick="adminSelAll('admin_modal_statuses')">Selecionar todos
                                </button>
                                <span class="text-zinc-700">•</span>
                                <button type="button" class="text-xs text-zinc-300 hover:underline"
                                        onclick="adminSelNone('admin_modal_statuses')">Limpar
                                </button>
                            </div>
                        </div>

                        <div id="admin_modal_statuses"
                             class="mt-2 w-full rounded-2xl bg-zinc-950 border border-zinc-800 p-2 max-h-64 overflow-auto">
                            @foreach((['S'=>'Aprovados','E'=>'Em análise','R'=>'Reprovados','N'=>'Excluídos']) as $k => $label)
                                <label
                                    class="flex items-center gap-2 rounded-xl px-2 py-2 hover:bg-zinc-900/60 cursor-pointer">
                                    <input type="checkbox" name="statuses[]" value="{{ $k }}"
                                           @checked(in_array($k, ['S','E','R'], true)) class="h-4 w-4 accent-emerald-500">
                                    <span class="text-sm text-zinc-200">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="md:col-span-12 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
                        <div class="text-xs text-zinc-500">Caso queira imprimir todas as inscrições sem filtros basta
                            clicar em
                            <button type="button" class="text-xs text-emerald-300 hover:underline"
                                    onclick="adminSelAll('admin_modal_all')">Selecionar todos
                            </button>
                            em ambos os filtros.
                        </div>
                        <button
                            class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-5 py-2 transition">
                            Baixar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function adminOpenGlobalExport() {
        const m = document.getElementById('adminGlobalExportModal');
        if (!m) return;

        // se estiver no mobile com sidebar aberta, fecha pra não virar bagunça
        try {
            adminCloseSidebar();
        } catch (e) {
        }

        m.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function adminCloseGlobalExport() {
        const m = document.getElementById('adminGlobalExportModal');
        if (!m) return;
        m.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function adminSelAll(id) {
        const el = document.getElementById(id);
        if (!el) return;

        if (el.tagName === 'SELECT') {
            Array.from(el.options).forEach(o => o.selected = true);
            return;
        }

        el.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
    }

    function adminSelNone(id) {
        const el = document.getElementById(id);
        if (!el) return;

        if (el.tagName === 'SELECT') {
            Array.from(el.options).forEach(o => o.selected = false);
            return;
        }

        el.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') adminCloseGlobalExport();
    });

    // opcional: abrir via querystring ?export=1 em qualquer página do admin
    @if(request()->boolean('export'))
    document.addEventListener('DOMContentLoaded', () => adminOpenGlobalExport());
    @endif
</script>

</body>
</html>
