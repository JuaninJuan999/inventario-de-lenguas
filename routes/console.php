<?php

use App\Services\ModulosMatrizSynchronizer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('modulos:sync-matriz', function (): void {
    $out = app(ModulosMatrizSynchronizer::class)->sync();
    $this->info('Catálogo de módulos actualizado. Filas en role_modulo: '.$out['pivot_assignments'].'.');
})->purpose('Sincroniza tablas modulos y role_modulo desde config/modulos_por_rol.php');

$scheduleMinutes = max(0, (int) config('ingresos_lenguas.inventario_schedule_minutes', 10));
if ($scheduleMinutes > 0) {
    Schedule::command('inventario:sync-lenguas')
        ->cron('*/'.min(59, max(1, $scheduleMinutes)).' * * * *')
        ->withoutOverlapping(15);
}
