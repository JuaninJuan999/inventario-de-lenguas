<?php

namespace App\Http\Controllers\Ingresos;

use App\Http\Controllers\Controller;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class IngresosLenguasController extends Controller
{
    public function __construct(
        protected IngresosLenguasConsultaSirt $consultaSirt
    ) {}

    protected function dbQueryUserMessage(Throwable $e): string
    {
        $base = 'No se pudo consultar la base de datos. Revise red/VPN, credenciales y los esquemas trazabilidad_proceso / organizaciones.';
        if (config('app.debug')) {
            return $base.' Detalle técnico: '.$e->getMessage();
        }

        return $base;
    }

    public function index(Request $request): View
    {
        $hoy = $this->consultaSirt->fechaHoyOperacion();
        $formularioEnviado = $request->filled('_enviado');
        $primeraCarga = ! $formularioEnviado;

        $fechaDesdeRaw = (string) $request->input('fecha_desde', '');
        $fechaHastaRaw = (string) $request->input('fecha_hasta', '');
        $fechaDesdeTrim = trim($fechaDesdeRaw);
        $fechaHastaTrim = trim($fechaHastaRaw);

        if ($primeraCarga) {
            $fechaDesdeDisplay = '';
            $fechaHastaDisplay = '';
        } else {
            $fechaDesdeDisplay = $fechaDesdeRaw;
            $fechaHastaDisplay = $fechaHastaRaw;
        }

        $filters = [
            'fecha_desde' => $fechaDesdeDisplay,
            'fecha_hasta' => $fechaHastaDisplay,
            'id_producto' => substr($request->string('id_producto')->trim()->toString(), 0, 80),
            'propietario' => substr($request->string('propietario')->trim()->toString(), 0, 200),
        ];

        $baseView = [
            'filters' => $filters,
        ];

        $dataValidacion = [
            'id_producto' => $filters['id_producto'],
            'propietario' => $filters['propietario'],
        ];
        $reglasValidacion = [
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
        ];

        if (! $primeraCarga) {
            $dataValidacion['fecha_desde'] = $fechaDesdeTrim === '' ? null : $fechaDesdeTrim;
            $dataValidacion['fecha_hasta'] = $fechaHastaTrim === '' ? null : $fechaHastaTrim;
            $reglasValidacion['fecha_desde'] = ['nullable', 'date'];
            $reglasValidacion['fecha_hasta'] = ['nullable', 'date'];
        }

        $validator = Validator::make($dataValidacion, $reglasValidacion);

        $validator->after(function ($v) use ($primeraCarga, $fechaDesdeTrim, $fechaHastaTrim): void {
            if ($primeraCarga) {
                return;
            }
            $d1Present = $fechaDesdeTrim !== '';
            $d2Present = $fechaHastaTrim !== '';
            if ($d1Present xor $d2Present) {
                $v->errors()->add('fecha_hasta', 'Indique ambas fechas de turno o déjelas vacías.');

                return;
            }
            if ($d1Present && $d2Present && $fechaDesdeTrim > $fechaHastaTrim) {
                $v->errors()->add('fecha_hasta', 'La fecha hasta debe ser mayor o igual que la fecha desde.');
            }
        });

        if ($validator->fails()) {
            return view('ingresos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => null,
            ]))->withErrors($validator);
        }

        if (! $this->consultaSirt->externalPostgresEnabled()) {
            return view('ingresos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'Configure la conexión en .env (POSTGRES_HOST, POSTGRES_DB, POSTGRES_USER, POSTGRES_PASSWORD).',
            ]));
        }

        $connection = (string) config('ingresos_lenguas.connection');
        if ($connection === '') {
            return view('ingresos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => 'Conexión no configurada (ingresos_lenguas.connection).',
            ]));
        }

        $result = $this->consultaSirt->consultar(
            $connection,
            $hoy,
            $primeraCarga,
            $fechaDesdeTrim,
            $fechaHastaTrim,
            $filters,
            false,
        );

        if ($result['exception'] instanceof Throwable) {
            return view('ingresos-lenguas', array_merge($baseView, [
                'rows' => [],
                'error' => $this->dbQueryUserMessage($result['exception']),
            ]));
        }

        return view('ingresos-lenguas', array_merge($baseView, [
            'rows' => $result['rows'],
            'error' => null,
        ]));
    }
}
