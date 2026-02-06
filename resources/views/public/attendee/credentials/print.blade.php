<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imprimir credencial - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100">

<x-theme-toggle />

<div class="max-w-5xl mx-auto p-6 no-print">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 flex items-start justify-between gap-4">
        <div>
            <div class="text-xl font-bold">{{ $credential->cre_nome }}</div>
            <div class="text-zinc-400 text-sm mt-1">{{ $event->name }}</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('public.attendee.area', $event) }}"
               class="rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-4 py-2 font-semibold">
                Voltar
            </a>
            <button onclick="window.print()"
                    class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-zinc-950 font-semibold px-4 py-2">
                Imprimir
            </button>
        </div>
    </div>
</div>

{{-- A4 --}}
<div class="flex justify-center pb-10">
    <div id="a4Wrap"
         class="bg-white"
         style="width:210mm;height:297mm;position:relative;overflow:hidden;">
        @if($bgUrl)
            <img src="{{ $bgUrl }}"
                 alt=""
                 style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
        @endif

        {{-- camada em PX (config), escalada pra caber no A4 mm --}}
        <div id="layer"
             style="position:absolute;left:0;top:0;width:{{ $pageW }}px;height:{{ $pageH }}px;transform-origin:top left;">
            @php
                $half = (int) floor($pageW / 2);
                $els = is_array($resolved ?? null) ? $resolved : [];
            @endphp

            @foreach($els as $el)
                @php
                    if (!is_array($el)) continue;

                    $id = $el['id'] ?? null;
                    $type = $el['type'] ?? 'text';

                    $x = (int) ($el['x'] ?? 0);
                    $y = (int) ($el['y'] ?? 0);
                    $w = (int) ($el['w'] ?? 200);
                    $h = (int) ($el['h'] ?? 40);

                    $value = (string) ($el['value'] ?? '');

                    $fontSize = (int) ($el['fontSize'] ?? 24);
                    $fontWeight = (string) ($el['fontWeight'] ?? '700');
                    $fontFamily = (string) ($el['fontFamily'] ?? 'Arial');
                    $color = (string) ($el['color'] ?? '#000000');
                    $align = (string) ($el['align'] ?? 'left');

                    $showLabel = !empty($el['showLabel']);
                    $barcodeFormat = (string) ($el['barcodeFormat'] ?? 'CODE128');

                    $copies = $mirror ? [0, $half] : [0];
                @endphp

                @foreach($copies as $dx)
                    <div
                        style="
                            position:absolute;
                            left: {{ $x + $dx }}px;
                            top: {{ $y }}px;
                            width: {{ $w }}px;
                            height: {{ $h }}px;
                        "
                    >
                        @if($type === 'text')
                            <div
                                style="
                                    width:100%;
                                    height:100%;
                                    display:flex;
                                    align-items:center;
                                    justify-content: {{ $align === 'center' ? 'center' : ($align === 'right' ? 'flex-end' : 'flex-start') }};
                                    color: {{ $color }};
                                    font-family: {{ $fontFamily }};
                                    font-weight: {{ $fontWeight }};
                                    @php
                                      $fontSizeFinal = (int) ($el['effectiveFontSize'] ?? $fontSize);
                                    @endphp

                                    font-size: {{ $fontSizeFinal }}px;
                                    line-height: 1.1;
                                    white-space: pre-wrap;
                                "
                            >{{ $value }}</div>

                        @elseif($type === 'qrcode')
                            <div class="qrcode"
                                 data-value="{{ $value }}"
                                 style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                            </div>
                            @if($showLabel)
                                <div
                                    style="font-size:11px;color:#111;margin-top:4px;word-break:break-word;">{{ $value }}</div>
                            @endif

                        @elseif($type === 'barcode')
                            <svg class="barcode"
                                 data-value="{{ $value }}"
                                 data-format="{{ $barcodeFormat }}"
                                 data-w="{{ $w }}"
                                 data-h="{{ $h }}"
                                 style="width:100%;height:100%;"></svg>
                            @if($showLabel)
                                <div
                                    style="font-size:11px;color:#111;margin-top:4px;word-break:break-word;">{{ $value }}</div>
                            @endif
                        @elseif($type === 'photo')
                            @php
                                // form_foto: só permite foto se a ficha do inscrito estiver com foto habilitada
                                $allowPhoto = (($registration->form?->form_foto ?? 'N') === 'S');
                            @endphp

                            @if($allowPhoto && $value)
                                <img src="{{ $value }}"
                                     alt=""
                                     style="width:100%;height:100%;object-fit: {{ (($el['fit'] ?? 'cover') === 'contain') ? 'contain' : 'cover' }};">
                            @endif
                        @endif
                    </div>
                @endforeach
            @endforeach

        </div>
    </div>
</div>

{{-- QR / Barcode via CDN (sem composer) --}}
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
    (function () {
        const wrap = document.getElementById('a4Wrap');
        const layer = document.getElementById('layer');

        function applyScale() {
            const pageW = {{ (int)$pageW }};
            const scale = wrap.clientWidth / pageW;
            layer.style.transform = `scale(${scale})`;
        }

        window.addEventListener('resize', applyScale);
        applyScale();

        // QR
        document.querySelectorAll('.qrcode').forEach(el => {
            const v = (el.dataset.value || '').trim();
            el.innerHTML = '';
            const size = Math.min(el.clientWidth, el.clientHeight);
            if (!v) return;

            new QRCode(el, {
                text: v,
                width: size,
                height: size,
                correctLevel: QRCode.CorrectLevel.M
            });
        });

        // Barcode
        document.querySelectorAll('.barcode').forEach(svg => {
            const v = (svg.dataset.value || '').trim();
            const fmt = (svg.dataset.format || 'CODE128').trim();
            const targetW = parseInt(svg.dataset.w || '0', 10) || svg.clientWidth || 300;
            const targetH = parseInt(svg.dataset.h || '0', 10) || svg.clientHeight || 120;

            if (!v) return;

            // limpa
            while (svg.firstChild) svg.removeChild(svg.firstChild);

            // gera
            const opts = {
                format: fmt,
                displayValue: false,
                margin: 0,
                height: targetH, // altura “real” das barras
            };

            try {
                JsBarcode(svg, v, opts);
            } catch (e) {
                try {
                    JsBarcode(svg, v, {...opts, format: 'CODE128'});
                } catch (e2) {
                    return;
                }
            }

            // deixa responsivo: pega tamanho gerado e cria viewBox
            const wAttr = parseFloat(svg.getAttribute('width') || svg.getBBox().width || targetW);
            const hAttr = parseFloat(svg.getAttribute('height') || svg.getBBox().height || targetH);

            svg.setAttribute('viewBox', `0 0 ${wAttr} ${hAttr}`);
            svg.setAttribute('preserveAspectRatio', 'none');
            svg.setAttribute('width', '100%');
            svg.setAttribute('height', '100%');
            svg.style.width = '100%';
            svg.style.height = '100%';
        });
    })();
</script>

</body>
</html>
