@php
    $total = (int) ($totalCoincidentes ?? $rows->count());
    $mostrando = $rows->count();
    $limite = (int) ($listadoLimit ?? 2000);
    $desdeTxt = trim((string) ($filters['fecha_desde'] ?? ''));
    $hastaTxt = trim((string) ($filters['fecha_hasta'] ?? ''));
    $rangoTxt = '';
    if ($desdeTxt !== '' && $hastaTxt !== '') {
        $rangoTxt = $desdeTxt === $hastaTxt ? $desdeTxt : ($desdeTxt.' → '.$hastaTxt);
    } elseif ($desdeTxt !== '') {
        $rangoTxt = 'desde '.$desdeTxt;
    } elseif ($hastaTxt !== '') {
        $rangoTxt = 'hasta '.$hastaTxt;
    }
    $huboSync = ! empty($importOk) || (isset($importadosSync) && $importadosSync !== null);
@endphp

@if (! empty($importError))
    <p class="alert alert--err" role="alert">{{ $importError }}</p>
@elseif ($huboSync || ! $rows->isEmpty() || ($listadoEmptyHint ?? '') === 'filtered')
    <p
        @class(['alert alert--ok' => $huboSync, 'muted' => ! $huboSync])
        role="status"
        @if (! $huboSync) style="margin: 0 0 0.75rem" @endif
    >
        @if ($huboSync)
            Inventario actualizado ·
        @endif
        @if ($rows->isEmpty())
            total disponible: <strong>0</strong>
            @if ($rangoTxt !== '')
                ({{ $rangoTxt }})
            @endif
            @if (($listadoEmptyHint ?? '') === 'filtered')
                · ninguna coincide con el filtro actual
            @endif
        @else
            total disponible: <strong>{{ $total }}</strong>
            @if ($rangoTxt !== '')
                ({{ $rangoTxt }})
            @endif
            @if ($mostrando < $total)
                · en pantalla {{ $mostrando }} de {{ $total }} (tope {{ $limite }})
            @endif
        @endif
        .
    </p>
@endif

@if ($rows->isEmpty())
    @if (($listadoEmptyHint ?? 'global') === 'validation')
        <p class="muted">Ajuste los criterios de consulta para obtener un resultado conforme a las reglas del módulo.</p>
    @elseif (($listadoEmptyHint ?? 'global') === 'filtered')
        {{-- El resumen de arriba ya explica el 0 --}}
    @elseif (! $huboSync)
        <p class="muted">
            No hay lenguas disponibles en el inventario. Si el listado permanece vacío, verifique la actualización automática o recargue la página.
        </p>
    @endif
@else
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
                            @if (! empty($row->es_salida))
                                <span class="chip-salida">Salida</span>
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
