@once
    <style>
        .dev-credit {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            margin: 0;
            padding: 0.45rem 1rem calc(0.45rem + env(safe-area-inset-bottom, 0px));
            text-align: center;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.02em;
            line-height: 1.3;
            color: color-mix(in srgb, #f4fffa 72%, transparent);
            background: color-mix(in srgb, #050807 88%, transparent);
            border-top: 1px solid color-mix(in srgb, #7ce8ad 18%, transparent);
            pointer-events: none;
            backdrop-filter: blur(6px);
        }
        .dev-credit__brand {
            font-weight: 750;
            letter-spacing: 0.03em;
        }
        .dev-credit__col {
            color: #2ecc71;
        }
        .dev-credit__beef {
            color: #e74c3c;
        }
        .dev-credit__reg {
            font-size: 0.65em;
            font-weight: 700;
            margin-left: 0.12em;
            color: color-mix(in srgb, #f4fffa 80%, transparent);
            vertical-align: super;
        }
        .dev-credit__name {
            font-weight: 650;
            color: color-mix(in srgb, #f4fffa 92%, transparent);
        }
        body {
            padding-bottom: 2.75rem !important;
        }
    </style>
@endonce
<p class="dev-credit" role="contentinfo">
    <span class="dev-credit__brand"><span class="dev-credit__col">Col</span><span class="dev-credit__beef">beef</span><sup class="dev-credit__reg" aria-label="marca registrada">®</sup></span>
    2026 — Desarrollado por <span class="dev-credit__name">Juan Carreño</span> — Auxiliar TIC
</p>
