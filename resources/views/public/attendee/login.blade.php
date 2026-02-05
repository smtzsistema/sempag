<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Já sou inscrito - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-100">
@php
    $bannerPath = !empty($category->banner_path) ? $category->banner_path : ($event->banner_path ?? null);
@endphp
@if($bannerPath)
    <div class="mx-auto max-w-[1200px]">
        <div class="w-full h-[200px] flex items-center justify-center rounded-2xl bg-zinc-950">
            <img
                src="{{ asset('storage/'.$bannerPath) }}"
                alt="Banner"
                class="max-w-full max-h-full object-contain"
            >
        </div>
    </div>
@endif
<div class="min-h-[50vh] flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 shadow-lg">
            <h1 class="text-2xl font-bold">Já sou inscrito</h1>
            <p class="text-zinc-400 mt-1 text-sm">{{ $event->name }}</p>

            @if(session('ok'))
                <div class="mt-4 p-3 rounded-lg bg-emerald-900/25 border border-emerald-800 text-emerald-200">
                    {{ session('ok') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mt-4 p-3 rounded-lg bg-red-900/25 border border-red-800 text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $prefill = old('login')
                    ?? session('prefill_login')
                    ?? request()->query('login')
                    ?? '';

                $prefillType = old('login_type')
                    ?? session('prefill_login_type')
                    ?? request()->query('login_type')
                    ?? 'email';
            @endphp

            <form class="mt-6 space-y-4" method="POST" action="{{ route('public.attendee.login.post', $event) }}">
                @csrf

                <input type="hidden" name="login_type" id="login_type" value="{{ $prefillType }}">

                <div>
                    <label class="text-sm text-zinc-300">Entrar com</label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <button type="button" id="btnEmail"
                                class="rounded-xl border border-zinc-700 py-2 font-semibold transition">
                            E-mail
                        </button>
                        <button type="button" id="btnCpf"
                                class="rounded-xl border border-zinc-700 py-2 font-semibold transition">
                            CPF
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-sm text-zinc-300" id="login_label">E-mail</label>
                    <input id="login"
                           name="login"
                           type="text"
                           value="{{ old('login', $prefill ?? session('login_prefill')) }}"
                           required
                           autocomplete="username"
                           class="mt-1 w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-600">
                    @error('login') <p class="text-red-300 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-zinc-300">Senha</label>
                    <input name="password"
                           type="password"
                           required
                           autocomplete="current-password"
                           class="mt-1 w-full rounded-xl bg-zinc-950 border border-zinc-800 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-600">
                    @error('password') <p class="text-red-300 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold py-3 transition">
                    Entrar
                </button>

                <div class="flex items-center justify-between text-sm">
                    <a class="text-zinc-400 hover:text-zinc-200"
                       href="{{ route('public.attendee.forgot', $event) }}">
                        Esqueci minha senha
                    </a>

                    <a class="text-zinc-400 hover:text-zinc-200"
                       href="{{ route('public.event.landing', $event) }}">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function onlyDigits(v){ return (v||'').replace(/\D+/g,''); }
function maskCPF(v){
  v = onlyDigits(v).slice(0,11);
  v = v.replace(/(\d{3})(\d)/, '$1.$2');
  v = v.replace(/(\d{3})(\d)/, '$1.$2');
  v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  return v;
}

const btnEmail = document.getElementById('btnEmail');
const btnCpf = document.getElementById('btnCpf');
const loginType = document.getElementById('login_type');
const login = document.getElementById('login');
const label = document.getElementById('login_label');

function setMode(mode){
  loginType.value = mode;

  // estilos
  const active = 'bg-emerald-600 text-zinc-950 border-emerald-500';
  const idle = 'bg-transparent text-zinc-200 border-zinc-700 hover:bg-zinc-800';

  if(mode === 'cpf'){
    btnCpf.className = `rounded-xl border py-2 font-semibold transition ${active}`;
    btnEmail.className = `rounded-xl border py-2 font-semibold transition ${idle}`;
    label.textContent = 'CPF';
    login.placeholder = '000.000.000-00';
    login.inputMode = 'numeric';
    // se já tem algo, mascara
    if (login.value) login.value = maskCPF(login.value);
  } else {
    btnEmail.className = `rounded-xl border py-2 font-semibold transition ${active}`;
    btnCpf.className = `rounded-xl border py-2 font-semibold transition ${idle}`;
    label.textContent = 'E-mail';
    login.placeholder = 'email@exemplo.com';
    login.inputMode = 'email';
  }
}

btnEmail?.addEventListener('click', () => setMode('email'));
btnCpf?.addEventListener('click', () => setMode('cpf'));

login?.addEventListener('input', () => {
  if(loginType.value === 'cpf'){
    login.value = maskCPF(login.value);
  }
});

// inicia no tipo vindo do backend (old/session/query) ou email
setMode(loginType.value || 'email');
</script>
</body>
</html>
