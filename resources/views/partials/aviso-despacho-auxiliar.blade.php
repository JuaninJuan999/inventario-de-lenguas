{{-- Toast informativo + sound.mp3 al aparecer (auxiliar/user/super_admin). --}}
@php
    $rolAviso = (string) (auth()->user()?->role ?? '');
    $activarAvisoDespachoAuxiliar = in_array($rolAviso, ['auxiliar', 'user', 'super_admin'], true);
@endphp
@once
@if ($activarAvisoDespachoAuxiliar)
    <style>
        .aviso-despacho-toast {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 9999;
            max-width: min(26rem, calc(100vw - 2rem));
            display: none;
            padding: 0.85rem 1rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(124, 232, 173, 0.55);
            background: rgba(6, 40, 28, 0.95);
            color: #f4fffa;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            opacity: 0;
            transform: translateY(0.6rem);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        .aviso-despacho-toast.is-on {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .aviso-despacho-toast.is-hiding {
            opacity: 0;
            transform: translateY(0.4rem);
        }
        .aviso-despacho-toast a {
            color: #7ce8ad;
            font-weight: 700;
        }
    </style>

    <audio id="aviso-despacho-tono" preload="auto" playsinline src="{{ asset('song/sound.mp3') }}"></audio>
    <div class="aviso-despacho-toast" id="aviso-despacho-nuevo" role="status" aria-live="polite" hidden></div>

    <script>
        (function () {
            var avisosUrl = @json(route('entrega.conformidad.avisos', [], false));
            var tonoUrl = @json(asset('song/sound.mp3'));
            var storageKey = 'inv_aviso_despacho_after_id';
            var toast = document.getElementById('aviso-despacho-nuevo');
            var tonoEl = document.getElementById('aviso-despacho-tono');
            var afterId = 0;
            try {
                afterId = Number(localStorage.getItem(storageKey) || 0) || 0;
            } catch (eLs) {
                afterId = 0;
            }
            var pollMs = 2000;
            var toastMs = 7000;
            var hideTimer = null;
            var vistos = {};
            var audioCtx = null;
            var audioBuffer = null;
            var audioListo = false;
            var cargandoBuffer = false;

            function guardarCursor(id) {
                afterId = Number(id) || afterId;
                try {
                    localStorage.setItem(storageKey, String(afterId));
                } catch (eSave) {}
            }

            function obtenerAudioContext() {
                var AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) {
                    return null;
                }
                if (!audioCtx) {
                    audioCtx = new AC();
                }
                return audioCtx;
            }

            function cargarBuffer() {
                var ctx = obtenerAudioContext();
                if (!ctx || audioBuffer || cargandoBuffer) {
                    return Promise.resolve(audioBuffer);
                }
                cargandoBuffer = true;
                return fetch(tonoUrl, { credentials: 'same-origin' })
                    .then(function (res) {
                        if (!res.ok) {
                            throw new Error('HTTP audio');
                        }
                        return res.arrayBuffer();
                    })
                    .then(function (raw) {
                        return ctx.decodeAudioData(raw.slice(0));
                    })
                    .then(function (buf) {
                        audioBuffer = buf;
                        cargandoBuffer = false;
                        return buf;
                    })
                    .catch(function () {
                        cargandoBuffer = false;
                        return null;
                    });
            }

            function desbloquearAudio() {
                var ctx = obtenerAudioContext();
                if (ctx && ctx.state === 'suspended') {
                    ctx.resume().catch(function () {});
                }
                cargarBuffer();
                // También desbloquea el <audio> HTML5 (volumen 0, sin dejar muted).
                if (tonoEl) {
                    try {
                        var prev = tonoEl.volume;
                        tonoEl.volume = 0.001;
                        var p = tonoEl.play();
                        if (p && typeof p.then === 'function') {
                            p.then(function () {
                                tonoEl.pause();
                                tonoEl.currentTime = 0;
                                tonoEl.volume = prev || 1;
                                audioListo = true;
                            }).catch(function () {
                                tonoEl.volume = prev || 1;
                            });
                        } else {
                            tonoEl.pause();
                            tonoEl.currentTime = 0;
                            tonoEl.volume = prev || 1;
                            audioListo = true;
                        }
                    } catch (e) {}
                }
            }

            function reproducirConAudioContext() {
                var ctx = obtenerAudioContext();
                if (!ctx || !audioBuffer) {
                    return false;
                }
                try {
                    if (ctx.state === 'suspended') {
                        ctx.resume();
                    }
                    var src = ctx.createBufferSource();
                    var gain = ctx.createGain();
                    gain.gain.value = 1;
                    src.buffer = audioBuffer;
                    src.connect(gain);
                    gain.connect(ctx.destination);
                    src.start(0);
                    audioListo = true;
                    return true;
                } catch (e) {
                    return false;
                }
            }

            function reproducirConHtmlAudio() {
                try {
                    // Nueva instancia: más fiable que reutilizar el mismo elemento.
                    var a = new Audio(tonoUrl);
                    a.preload = 'auto';
                    a.volume = 1;
                    a.playsInline = true;
                    var p = a.play();
                    if (p && typeof p.then === 'function') {
                        p.then(function () {
                            audioListo = true;
                        }).catch(function () {
                            if (tonoEl) {
                                tonoEl.volume = 1;
                                tonoEl.currentTime = 0;
                                var p2 = tonoEl.play();
                                if (p2 && typeof p2.catch === 'function') {
                                    p2.catch(function () {});
                                }
                            }
                        });
                    }
                    return true;
                } catch (e) {
                    return false;
                }
            }

            function reproducirSonidoDespachoOk() {
                desbloquearAudio();
                if (reproducirConAudioContext()) {
                    return;
                }
                // Si el buffer aún no cargó, cárgalo y reproduce al terminar.
                cargarBuffer().then(function (buf) {
                    if (buf && reproducirConAudioContext()) {
                        return;
                    }
                    reproducirConHtmlAudio();
                });
                // Fallback inmediato por si decode tarda.
                window.setTimeout(function () {
                    if (!audioListo) {
                        reproducirConHtmlAudio();
                    }
                }, 120);
            }

            function ocultarToast() {
                if (!toast) {
                    return;
                }
                toast.classList.add('is-hiding');
                window.setTimeout(function () {
                    toast.classList.remove('is-on', 'is-hiding');
                    toast.hidden = true;
                    toast.style.display = 'none';
                }, 260);
            }

            function mostrarToast(aviso) {
                if (!toast || !aviso) {
                    return;
                }
                var nro = Number(aviso.numero || 0) > 0 ? Number(aviso.numero) : Number(aviso.id || 0);
                var placa = aviso.placa ? ' · placa ' + aviso.placa : '';
                var hora = aviso.realizado_at ? ' a las ' + aviso.realizado_at : '';
                toast.innerHTML =
                    '<strong>Despacho #' +
                    nro +
                    ' listo' +
                    hora +
                    placa +
                    '.</strong><br>Saque la Entrega de conformidad.' +
                    (aviso.pdf_url
                        ? ' <a href="' +
                          aviso.pdf_url +
                          '" target="_blank" rel="noopener">Abrir PDF</a>'
                        : '');
                toast.hidden = false;
                toast.style.display = 'block';
                toast.classList.remove('is-hiding');
                void toast.offsetWidth;
                toast.classList.add('is-on');

                if (hideTimer) {
                    window.clearTimeout(hideTimer);
                }
                hideTimer = window.setTimeout(ocultarToast, toastMs);
            }

            function avisarEmergente(aviso) {
                if (!aviso) {
                    return;
                }
                var id = Number(aviso.id || 0);
                if (id > 0 && vistos[id]) {
                    return;
                }
                if (id > 0) {
                    vistos[id] = true;
                }
                // Sonido en el mismo momento del anuncio.
                reproducirSonidoDespachoOk();
                mostrarToast(aviso);
            }

            function consultarAvisos() {
                var url = avisosUrl + (avisosUrl.indexOf('?') >= 0 ? '&' : '?') + 'after_id=' + afterId;
                fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (res) {
                        if (!res.ok) {
                            throw new Error('HTTP');
                        }
                        return res.json();
                    })
                    .then(function (json) {
                        if (!json || !json.enabled) {
                            return;
                        }
                        var lista = Array.isArray(json.avisos) ? json.avisos : [];
                        if (afterId <= 0 && lista.length === 0) {
                            guardarCursor(json.latest_id || 0);
                            return;
                        }
                        if (lista.length) {
                            avisarEmergente(lista[lista.length - 1]);
                            guardarCursor(
                                Math.max(
                                    Number(json.latest_id || 0),
                                    Number(lista[lista.length - 1].id || 0),
                                    afterId,
                                ),
                            );
                        } else if (json.latest_id) {
                            guardarCursor(Math.max(afterId, Number(json.latest_id)));
                        }
                    })
                    .catch(function () {});
            }

            // Cualquier gesto desbloquea audio para el resto de la sesión.
            ['pointerdown', 'keydown', 'touchstart', 'click', 'mousemove'].forEach(function (evName) {
                document.addEventListener(evName, desbloquearAudio, { capture: true, once: true });
            });
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    desbloquearAudio();
                }
            });

            desbloquearAudio();
            cargarBuffer();
            consultarAvisos();
            window.setInterval(consultarAvisos, pollMs);
        })();
    </script>
@endif
@endonce
