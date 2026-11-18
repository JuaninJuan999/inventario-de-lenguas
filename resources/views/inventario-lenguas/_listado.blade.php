@if (! empty($importMessage))
    <p class="alert alert--ok" role="status">{{ $importMessage }}</p>
@endif
@if (! empty($importError))
    <p class="alert alert--err" role="alert">{{ $importError }}</p>
@endif

@if ($rows->isEmpty())
    @if (($listadoEmptyHint ?? 'global') === 'validation')
        <p class="muted">Ajuste los criterios de consulta para obtener un resultado conforme a las reglas del módulo.</p>
    @elseif (($listadoEmptyHint ?? 'global') === 'filtered')
        <p class="muted">
            No hay stock disponible que coincida con los filtros (tope de presentación: 2.000 registros).
        </p>
    @else
        <p class="muted">
            No hay lenguas disponibles en la réplica local. El sistema sincroniza el día de operación desde el origen
            corporativo automáticamente; si el listado permanece vacío, verifique conectividad e integración.
        </p>
    @endif
@else
    <p class="muted" style="margin: 0 0 0.75rem">
        Stock disponible: <strong>{{ $rows->count() }}</strong> registro(s)
        @if (! empty($filtrosActivos))
            (con filtros activos)
        @endif
        · tope de visualización: 2.000.
    </p>
    <div class="table-wrap">
        <table class="data">
            <colgroup>
                <col class="col-id-producto-col" />
                <col span="8" />
            </colgroup>
            <thead>
                <tr>
                    <th class="col-id-producto">Id producto</th>
                    <th>Fecha registro</th>
                    <th>Hora registro</th>
                    <th>Propietario</th>
                    <th>Destino</th>
                    <th>Peso</th>
                    <th>Ref. turno</th>
                    <th>Fecha de vencimiento</th>
                    <th>Vida útil</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php
                        $diasVida = \App\Support\VencimientoLengua::diasHastaVencimiento($row->fecha_registro);
                        $esVencida = $diasVida !== null && $diasVida < 0;
                        $esPorVencer = $diasVida !== null && $diasVida >= 0 && $diasVida <= 1;
                        $celAlerta = $esVencida ? 'cel-vencida' : ($esPorVencer ? 'cel-por-vencer' : '');
                    @endphp
                    <tr @class(['row-vencida' => $esVencida, 'row-por-vencer' => $esPorVencer])>
                        <td @class(['col-id-producto', $celAlerta => $celAlerta !== ''])>
                            {{ $row->id_producto }}
                            @if (! empty($row->es_adicional))
                                <span class="chip-adicional">Adicional</span>
                            @endif
                        </td>
                        <td>{{ $row->fecha_registro?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $row->hora_registro ?? '—' }}</td>
                        <td>{{ $row->propietario ?? '—' }}</td>
                        <td>{{ $row->destino !== null && $row->destino !== '' ? $row->destino : '—' }}</td>
                        <td>{{ \App\Support\PesoFormatter::mostrar($row->peso) }}</td>
                        <td>{{ $row->fecha_turno_referencia?->format('Y-m-d') ?? '—' }}</td>
                        <td @class([$celAlerta => $celAlerta !== ''])>{{ \App\Support\VencimientoLengua::fechaVencimientoTexto($row->fecha_registro) }}</td>
                        <td @class([$celAlerta => $celAlerta !== ''])>{{ \App\Support\VencimientoLengua::diasHastaVencimientoTexto($row->fecha_registro) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
