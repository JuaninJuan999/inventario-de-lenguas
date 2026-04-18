<?php

namespace Tests\Unit;

use App\Support\DestinoEntregaParser;
use PHPUnit\Framework\TestCase;

class DestinoEntregaParserTest extends TestCase
{
    public function test_cuatro_partes_extrae_tercera_y_resto_como_direccion(): void
    {
        $raw = 'Barranquilla / SUPERTIENDAS Y DROGUERIA OLIMPICA S.A. / OLIMPICA / 6ta Entrada Km 2-701 vía Caracolí-Malambo';
        $out = DestinoEntregaParser::destinoYDireccion($raw);
        $this->assertSame('OLIMPICA', $out['codigo']);
        $this->assertSame('6ta Entrada Km 2-701 vía Caracolí-Malambo', $out['direccion']);
    }

    public function test_direccion_puede_incluir_barras_adicionales(): void
    {
        $raw = 'A / B / C / D / E';
        $out = DestinoEntregaParser::destinoYDireccion($raw);
        $this->assertSame('C', $out['codigo']);
        $this->assertSame('D / E', $out['direccion']);
    }
}
