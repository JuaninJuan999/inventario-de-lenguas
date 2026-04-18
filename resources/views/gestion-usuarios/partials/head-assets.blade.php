<link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --welcome-base: #050807;
                --welcome-surface: rgba(6, 18, 14, 0.78);
                --green-deep: #1a5c40;
                --text: #f4fffa;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                min-height: 100dvh;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                background-color: var(--welcome-base);
                background-image:
                    radial-gradient(ellipse 130% 100% at 0% -15%, rgba(124, 232, 173, 0.22), transparent 52%),
                    radial-gradient(ellipse 110% 90% at 100% 15%, rgba(249, 223, 248, 0.18), transparent 48%);
                color: var(--text);
                padding: 1rem 1.25rem 2rem;
            }
            .page-bar {
                max-width: 56rem;
                margin: 0 auto 1rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }
            .page-bar h1 {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 700;
            }
            .page-bar .sub {
                margin: 0.35rem 0 0;
                font-size: 0.82rem;
                line-height: 1.45;
                opacity: 0.88;
                max-width: 40rem;
            }
            .link-menu {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--brand-rose);
                text-decoration: none;
                padding: 0.4rem 0.85rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
                background: color-mix(in srgb, var(--brand-rose) 8%, transparent);
            }
            .sheet {
                max-width: 56rem;
                margin: 0 auto;
                background: var(--welcome-surface);
                padding: 1.25rem 1.35rem 1.5rem;
                border-radius: 1.25rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
            }
            .section-title {
                margin: 0 0 0.75rem;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 90%, #fff);
            }
            .alert {
                margin: 0 0 1rem;
                padding: 0.75rem 0.9rem;
                border-radius: 0.65rem;
                font-size: 0.88rem;
                font-weight: 600;
            }
            .alert--ok {
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 12%, rgba(0, 0, 0, 0.35));
                color: var(--text);
            }
            .alert--err {
                border: 1px solid color-mix(in srgb, #ffb4b4 55%, transparent);
                background: color-mix(in srgb, #5c1a1a 55%, rgba(0, 0, 0, 0.35));
                color: #ffe4e4;
            }
            .muted {
                margin: 0 0 1rem;
                font-size: 0.82rem;
                line-height: 1.45;
                color: color-mix(in srgb, var(--text) 68%, transparent);
            }
            /* Una sola columna y ancho acotado: evita campos y botones Â«VerÂ» muy separados en pantallas anchas. */
            .form-panel {
                max-width: 22.5rem;
                margin: 0 auto 1.25rem;
            }
            .form-grid {
                display: grid;
                gap: 0.65rem;
                grid-template-columns: 1fr;
            }
            .field {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }
            .field--full {
                grid-column: 1 / -1;
            }
            .form-panel .actions {
                margin-top: 0.85rem;
            }
            .field label {
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 90%, #fff);
            }
            .field input,
            .field select {
                padding: 0.5rem 0.65rem;
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.9rem;
                font-family: inherit;
            }
            .field input:focus,
            .field select:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .err {
                margin: 0;
                font-size: 0.75rem;
                font-weight: 600;
                color: #ffb4b4;
            }
            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.65rem;
                align-items: center;
                margin-top: 1rem;
            }
            .btn-submit {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 1.15rem;
                font-size: 0.75rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #04261c;
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, #fff);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 72%, var(--green-deep)));
            }
            .btn-submit:hover {
                filter: brightness(1.06);
            }
            .divider {
                margin: 1.5rem 0 1rem;
                border: 0;
                border-top: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
            }
            .table-wrap {
                overflow-x: auto;
                border-radius: 0.65rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
            }
            table.data {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.8rem;
            }
            table.data th,
            table.data td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.45rem 0.5rem;
                text-align: left;
                vertical-align: top;
            }
            table.data th {
                background: linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--brand-green) 24%, transparent),
                    color-mix(in srgb, var(--brand-green) 10%, transparent)
                );
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                font-size: 0.65rem;
            }
            table.data tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.2);
            }
            .pill {
                display: inline-block;
                padding: 0.15rem 0.45rem;
                border-radius: 9999px;
                font-size: 0.68rem;
                font-weight: 700;
                background: color-mix(in srgb, var(--brand-rose) 18%, transparent);
                color: var(--text);
            }
            .pagination {
                margin-top: 1rem;
                font-size: 0.82rem;
            }
            .pagination a {
                color: var(--brand-rose);
            }
            .row-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                align-items: center;
            }
            .btn-inline {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.28rem 0.55rem;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                border-radius: 5px;
                cursor: pointer;
                font-family: inherit;
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, #1a3d2e);
                background: color-mix(in srgb, var(--brand-green) 14%, rgba(0, 0, 0, 0.35));
                color: var(--text);
            }
            .btn-inline:hover {
                filter: brightness(1.08);
            }
            .btn-inline--danger {
                border-color: color-mix(in srgb, #ffb4b4 45%, transparent);
                background: color-mix(in srgb, #5c1a1a 40%, rgba(0, 0, 0, 0.35));
                color: #ffe4e4;
            }
            .muted-cell {
                font-size: 0.75rem;
                opacity: 0.75;
            }
            .password-wrap {
                position: relative;
            }
            .password-wrap input {
                padding-right: 3.25rem;
            }
            .toggle-password {
                position: absolute;
                right: 0.35rem;
                top: 50%;
                transform: translateY(-50%);
                margin: 0;
                padding: 0.35rem 0.55rem;
                border: none;
                border-radius: 5px;
                background: color-mix(in srgb, var(--brand-green) 22%, transparent);
                color: var(--text);
                font-size: 0.7rem;
                font-weight: 700;
                cursor: pointer;
                font-family: inherit;
            }
            .toggle-password:hover {
                background: color-mix(in srgb, var(--brand-green) 32%, transparent);
            }
            .toggle-password:focus-visible {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .list-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.65rem;
                margin-bottom: 0.75rem;
            }
            .list-toolbar .section-title {
                margin-bottom: 0;
            }
            a.btn-submit {
                text-decoration: none;
                display: inline-flex;
            }
        </style>

