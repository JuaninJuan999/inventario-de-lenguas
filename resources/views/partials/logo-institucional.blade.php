@once
    <style>
        .inst-brand {
            width: 100%;
            margin: 0 auto 0.75rem;
            padding: 0 1.25rem;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }
        .inst-brand__inner {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.45rem 0.85rem 0.45rem 0.5rem;
            border-radius: 9999px;
            border: 1px solid color-mix(in srgb, #7ce8ad 32%, transparent);
            background:
                linear-gradient(
                    135deg,
                    color-mix(in srgb, #7ce8ad 12%, rgba(6, 18, 14, 0.72)),
                    color-mix(in srgb, #f9dff8 8%, rgba(5, 8, 7, 0.78))
                );
            box-shadow:
                0 0 0 1px color-mix(in srgb, #f9dff8 8%, transparent) inset,
                0 10px 28px rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(8px);
        }
        .inst-brand__mark {
            flex: 0 0 auto;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid color-mix(in srgb, #7ce8ad 55%, #fff);
            background: #0a0a0a;
            box-shadow:
                0 0 0 3px color-mix(in srgb, #7ce8ad 14%, transparent),
                0 4px 14px rgba(0, 0, 0, 0.35);
        }
        .inst-brand__mark img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .inst-brand__divider {
            width: 1px;
            align-self: stretch;
            min-height: 1.75rem;
            background: linear-gradient(
                180deg,
                transparent,
                color-mix(in srgb, #7ce8ad 45%, transparent),
                transparent
            );
        }
        .inst-brand__logo {
            display: block;
            max-height: 36px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
        }
        .inst-brand__text {
            display: flex;
            flex-direction: column;
            gap: 0.05rem;
            line-height: 1.15;
            min-width: 0;
        }
        .inst-brand__app {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #f4fffa;
            white-space: nowrap;
        }
        .inst-brand__sub {
            margin: 0;
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: color-mix(in srgb, #7ce8ad 80%, #f4fffa);
        }
        @media (max-width: 480px) {
            .inst-brand {
                padding: 0 0.75rem;
            }
            .inst-brand__inner {
                gap: 0.55rem;
                padding: 0.4rem 0.7rem 0.4rem 0.4rem;
            }
            .inst-brand__mark {
                width: 38px;
                height: 38px;
            }
            .inst-brand__logo {
                max-height: 30px;
            }
            .inst-brand__app {
                font-size: 0.72rem;
            }
            .inst-brand__sub {
                font-size: 0.58rem;
            }
        }
    </style>
@endonce
<div class="inst-brand">
    <div class="inst-brand__inner" title="{{ config('app.name') }}">
        <span class="inst-brand__mark" aria-hidden="true">
            <img
                src="{{ asset('images/lengua.png') }}"
                alt=""
                width="44"
                height="44"
                decoding="async"
            >
        </span>
        <span class="inst-brand__divider" aria-hidden="true"></span>
        <img
            class="inst-brand__logo"
            src="{{ asset('images/colbeef.ico') }}"
            alt="Colbeef"
            decoding="async"
        >
        <div class="inst-brand__text">
            <p class="inst-brand__app">Inventario de Lenguas</p>
            <p class="inst-brand__sub">Colbeef</p>
        </div>
    </div>
</div>
@include('partials.credito-desarrollador')
{{-- Aviso toast + sonido de despacho nuevo (solo auxiliar), en todos los módulos. --}}
@include('partials.aviso-despacho-auxiliar')
