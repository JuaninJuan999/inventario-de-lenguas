<?php

use App\Services\ModulosMatrizSynchronizer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('modulos:sync-matriz', function (): void {
    $out = app(ModulosMatrizSynchronizer::class)->sync();
    $this->info('Catálogo de módulos actualizado. Filas en role_modulo: '.$out['pivot_assignments'].'.');
})->purpose('Sincroniza tablas modulos y role_modulo desde config/modulos_por_rol.php');
