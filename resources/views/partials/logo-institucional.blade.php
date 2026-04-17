@once
    <style>
        .inst-brand {
            width: 100%;
            margin: 0 auto 0.65rem;
            padding: 0 1.25rem;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }
        .inst-brand picture,
        .inst-brand img {
            display: block;
        }
        .inst-brand__img {
            max-height: 52px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        @media (max-width: 480px) {
            .inst-brand__img {
                max-height: 40px;
            }
        }
    </style>
@endonce
<div class="inst-brand">
    <picture>
        <source srcset="{{ asset('images/fond.png') }}" type="image/png" />
        <img
            class="inst-brand__img"
            src="{{ asset('images/colbeef.ico') }}"
            alt="Colbeef"
            decoding="async"
        />
    </picture>
</div>
