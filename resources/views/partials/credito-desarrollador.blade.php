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
            color: color-mix(in srgb, #f4fffa 62%, transparent);
            background: color-mix(in srgb, #050807 88%, transparent);
            border-top: 1px solid color-mix(in srgb, #7ce8ad 18%, transparent);
            pointer-events: none;
            backdrop-filter: blur(6px);
        }
        .dev-credit strong {
            font-weight: 650;
            color: color-mix(in srgb, #f4fffa 88%, transparent);
        }
        body {
            padding-bottom: 2.75rem !important;
        }
    </style>
@endonce
<p class="dev-credit" role="contentinfo">
    Desarrollado por <strong>Juan Pablo Carreño</strong>
</p>
