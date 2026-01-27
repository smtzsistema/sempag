@if(session('ok'))
    <div class="mb-4 rounded-xl border border-emerald-900/40 bg-emerald-950/30 p-3 text-sm text-emerald-200">
        {{ session('ok') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-xl border border-rose-900/40 bg-rose-950/30 p-3 text-sm text-rose-200">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-rose-900/40 bg-rose-950/30 p-3 text-sm text-rose-200">
        <div class="font-semibold">Corrija os campos abaixo:</div>
        <ul class="list-disc pl-5 mt-2 space-y-1">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
