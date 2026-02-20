<!doctype html>
<html lang="pt-br" class="{{ request()->cookie('theme','dark') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imprimir Certificado - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">

    <style>
        /* =========
           CANVAS REAL DO CERTIFICADO (PX)
           ========= */
        :root{
            --pageW: {{ (int)$pageW }}px;
            --pageH: {{ (int)$pageH }}px;
        }

        html, body {
            height: 100%;
        }

        /* evita “barras” e overflow fantasma */
        body {
            margin: 0;
            overflow-x: hidden;
        }

        /* Área de visualização (tela) */
        .viewer {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 40px;
        }

        /* Caixa que limita altura pra caber na tela sem scroll */
        .stage {
            width: 100%;
            height: min(calc(100vh - 220px), 760px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }

        /* Moldura (sem scroll!) */
        #certViewport {
            width: 100%;
            height: 100%;
            border-radius: 16px;
            overflow: hidden; /* <- mata scrollbar */
            background: white; /* <- nada de fundo preto ao “sobrar” */
            position: relative;
        }

        /* O “papel” real em px, escalado via transform */
        #certWrap {
            width: var(--pageW);
            height: var(--pageH);
            position: absolute;
            left: 0;
            top: 0;
            transform-origin: top left;
            overflow: hidden;
            background: white;
        }

        /* fundo ocupa tudo */
        #certBg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* layer de elementos */
        #layer {
            position: absolute;
            left: 0;
            top: 0;
            width: var(--pageW);
            height: var(--pageH);
        }

        /* =========
           PRINT
           ========= */
        @page {
            /* força landscape e sem margem */
            size: {{ (int)$pageW }}px {{ (int)$pageH }}px;
            margin: 0;
        }

        @media print {
            /* some com tudo fora do certificado */
            .no-print, .theme-toggle, .theme-toggle * {
                display: none !important;
            }

            html, body {
                width: var(--pageW);
                height: var(--pageH);
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden !important;
            }

            /* o certificado vira a página inteira */
            #certViewport {
                width: var(--pageW) !important;
                height: var(--pageH) !important;
                border-radius: 0 !important;
                overflow: hidden !important;
                background: #fff !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
            }

            #certWrap {
                transform: none !important; /* nada de scale no print */
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: var(--pageW) !important;
                height: var(--pageH) !important;
            }

            /* garante 1 página (sem quebra) */
            #certViewport, #certWrap {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>

<body class="bg-zinc-950 text-zinc-100">

{{-- Theme toggle NÃO pode aparecer no print --}}
<div class="no-print">
    <x-theme-toggle />
</div>

<div class="max-w-5xl mx-auto p-6 no-print">
    <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-6 flex items-start justify-between gap-4">
        <div>
            <div class="text-xl font-bold">{{ $certificate->cer_nome }}</div>
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

<div class="viewer">
    <div class="stage">
        <div id="certViewport" class="border border-zinc-800/60 shadow-2xl">
            <div id="certWrap">
                @if($bgUrl)
                    <img id="certBg" src="{{ $bgUrl }}" alt="">
                @endif

                <div id="layer">
                    @php
                        $els = is_array($resolved ?? null) ? $resolved : [];
                    @endphp

                    @foreach($els as $el)
                        @php
                            if (!is_array($el)) continue;

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

                            $fontSizeFinal = (int) ($el['effectiveFontSize'] ?? $fontSize);
                        @endphp

                        <div style="
                            position:absolute;
                            left: {{ $x }}px;
                            top: {{ $y }}px;
                            width: {{ $w }}px;
                            height: {{ $h }}px;
                        ">
                            @if($type === 'text')
                                <div style="
                                    width:100%;
                                    height:100%;
                                    display:flex;
                                    align-items:center;
                                    justify-content: {{ $align === 'center' ? 'center' : ($align === 'right' ? 'flex-end' : 'flex-start') }};
                                    color: {{ $color }};
                                    font-family: {{ $fontFamily }};
                                    font-weight: {{ $fontWeight }};
                                    font-size: {{ $fontSizeFinal }}px;
                                    line-height: 1.1;
                                    white-space: pre-wrap;
                                ">{{ $value }}</div>

                            @elseif($type === 'qrcode')
                                <div class="qrcode"
                                     data-value="{{ $value }}"
                                     style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                </div>
                                @if($showLabel)
                                    <div style="font-size:11px;color:#111;margin-top:4px;word-break:break-word;">{{ $value }}</div>
                                @endif

                            @elseif($type === 'barcode')
                                <svg class="barcode"
                                     data-value="{{ $value }}"
                                     data-format="{{ $barcodeFormat }}"
                                     data-w="{{ $w }}"
                                     data-h="{{ $h }}"
                                     style="width:100%;height:100%;"></svg>
                                @if($showLabel)
                                    <div style="font-size:11px;color:#111;margin-top:4px;word-break:break-word;">{{ $value }}</div>
                                @endif

                            @elseif($type === 'photo')
                                @php
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
                </div>
            </div>
        </div>
    </div>
</div>

{{-- QR / Barcode via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
(function () {
    const viewport = document.getElementById('certViewport');
    const wrap = document.getElementById('certWrap');

    const pageW = {{ (int)$pageW }};
    const pageH = {{ (int)$pageH }};

    function applyScale() {
        // escala pra caber sem scroll (mantém 16:9)
        const vw = viewport.clientWidth;
        const vh = viewport.clientHeight;

        const scale = Math.min(vw / pageW, vh / pageH);

        wrap.style.transform = `scale(${scale})`;

        // centraliza dentro do viewport
        const scaledW = pageW * scale;
        const scaledH = pageH * scale;

        wrap.style.left = ((vw - scaledW) / 2) + 'px';
        wrap.style.top  = ((vh - scaledH) / 2) + 'px';
        wrap.style.position = 'absolute';
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
        const targetH = parseInt(svg.dataset.h || '0', 10) || svg.clientHeight || 120;

        if (!v) return;

        while (svg.firstChild) svg.removeChild(svg.firstChild);

        const opts = {
            format: fmt,
            displayValue: false,
            margin: 0,
            height: targetH,
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

        const wAttr = parseFloat(svg.getAttribute('width') || svg.getBBox().width || 300);
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
