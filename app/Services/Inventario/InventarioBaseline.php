<?php

namespace App\Services\Inventario;

use App\Support\DespachoCodigoBarras;
use App\Support\DespachoInventarioMatch;
use Illuminate\Support\Facades\Storage;

/**
 * Baseline de inventario tras reinicio desde Excel.
 *
 * Mientras exista el archivo, la sync solo crea filas si:
 * - el código está en la lista del Excel, o
 * - la fecha de turno es >= fecha de corte (p. ej. desde REF. TURNO 2026-11-19)
 *   — producción nueva “hacia adelante”, sin reabrir histórico excluido.
 *
 * @see docs/inventario/reiniciar-desde-excel.md
 */
class InventarioBaseline
{
    public const STORAGE_PATH = 'inventario/baseline.json';

    /** @var array{fecha_corte: string, codigos_norm: list<string>, archivo?: string, creado_at?: string}|null */
    private ?array $cached = null;

    /** @var array<string, true>|null */
    private ?array $allowKeys = null;

    public function active(): bool
    {
        $data = $this->data();

        return $data !== null && trim((string) ($data['fecha_corte'] ?? '')) !== '';
    }

    public function fechaCorte(): ?string
    {
        $data = $this->data();
        if ($data === null) {
            return null;
        }
        $f = trim((string) ($data['fecha_corte'] ?? ''));

        return $f !== '' ? $f : null;
    }

    /**
     * @param  list<string>  $codigosNorm  IDs normalizados únicos del Excel
     */
    public function save(array $codigosNorm, string $fechaCorte, string $archivo = ''): void
    {
        $norm = array_values(array_unique(array_filter(array_map(
            static function (string $c): string {
                return DespachoCodigoBarras::normalizarIdProducto(
                    DespachoInventarioMatch::normalizarGuionesIdProducto($c)
                );
            },
            $codigosNorm
        ))));

        $payload = [
            'fecha_corte' => $fechaCorte,
            'codigos_norm' => $norm,
            'archivo' => $archivo,
            'creado_at' => now()->toIso8601String(),
            'total' => count($norm),
        ];

        Storage::disk('local')->put(self::STORAGE_PATH, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->cached = $payload;
        $this->allowKeys = null;
    }

    public function clear(): void
    {
        if (Storage::disk('local')->exists(self::STORAGE_PATH)) {
            Storage::disk('local')->delete(self::STORAGE_PATH);
        }
        $this->cached = null;
        $this->allowKeys = null;
    }

    /**
     * ¿Se puede crear una fila nueva en la réplica local?
     */
    public function permiteAlta(string $idProducto, ?string $fechaTurno): bool
    {
        if (! $this->active()) {
            return true;
        }

        if ($this->codigoEnLista($idProducto)) {
            return true;
        }

        $corte = $this->fechaCorte();
        $turno = trim((string) ($fechaTurno ?? ''));
        if ($corte !== null && $turno !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $turno) === 1 && $turno >= $corte) {
            return true;
        }

        return false;
    }

    public function codigoEnLista(string $idProducto): bool
    {
        $allow = $this->allowKeys();
        if ($allow === []) {
            return false;
        }
        foreach (DespachoInventarioMatch::matchKeysForProductId($idProducto) as $key) {
            if (isset($allow[$key]) || isset($allow[mb_strtolower($key)])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{fecha_corte: string, codigos_norm: list<string>, archivo?: string, creado_at?: string, total?: int}|null
     */
    public function data(): ?array
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return null;
        }

        try {
            $raw = Storage::disk('local')->get(self::STORAGE_PATH);
            $decoded = json_decode((string) $raw, true);
            if (! is_array($decoded) || trim((string) ($decoded['fecha_corte'] ?? '')) === '') {
                return null;
            }
            $this->cached = $decoded;

            return $this->cached;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, true> */
    private function allowKeys(): array
    {
        if ($this->allowKeys !== null) {
            return $this->allowKeys;
        }

        $data = $this->data();
        $allow = [];
        foreach (($data['codigos_norm'] ?? []) as $codigo) {
            foreach (DespachoInventarioMatch::matchKeysForProductId((string) $codigo) as $key) {
                $allow[$key] = true;
                $allow[mb_strtolower($key)] = true;
            }
        }
        $this->allowKeys = $allow;

        return $this->allowKeys;
    }
}
