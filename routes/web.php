<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Decomisos\InspeccionSirtController;
use App\Http\Controllers\Despacho\DespachoFinalizarInventarioController;
use App\Http\Controllers\Despacho\DespachoLenguasController;
use App\Http\Controllers\Despacho\DespachoSeleccionPlacasController;
use App\Http\Controllers\Despacho\HistoriaDespachoLenguasController;
use App\Http\Controllers\Despacho\LenguaDestinoInventarioController;
use App\Http\Controllers\Despacho\VehiculoAsignadoLookupController;
use App\Http\Controllers\Desposte\DesposteFinalizarInventarioController;
use App\Http\Controllers\EntregaConformidadController;
use App\Http\Controllers\Ingresos\IngresosLenguasController;
use App\Http\Controllers\Inventario\CambiosDestinosController;
use App\Http\Controllers\Inventario\InventarioLenguasController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Usuarios\GestionUsuariosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/menu', MenuController::class)->name('menu');

    Route::middleware('can.manage.users')->group(function () {
        Route::get('/gestion-de-usuarios/crear', [GestionUsuariosController::class, 'create'])->name('gestion.usuarios.create');
        Route::get('/gestion-de-usuarios', [GestionUsuariosController::class, 'index'])->name('gestion.usuarios.index');
        Route::post('/gestion-de-usuarios', [GestionUsuariosController::class, 'store'])->name('gestion.usuarios.store');
        Route::post('/gestion-de-usuarios/{user}/inactivar', [GestionUsuariosController::class, 'inactivar'])
            ->name('gestion.usuarios.inactivar');
        Route::post('/gestion-de-usuarios/{user}/activar', [GestionUsuariosController::class, 'activar'])
            ->name('gestion.usuarios.activar');
        Route::delete('/gestion-de-usuarios/{user}', [GestionUsuariosController::class, 'destroy'])
            ->name('gestion.usuarios.destroy');
    });

    Route::middleware('module:dashboard')->group(function () {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
    });

    Route::middleware('module:inventario')->group(function () {
        Route::get('/inventario-de-lenguas', [InventarioLenguasController::class, 'index'])->name('inventario.lenguas');
        Route::post('/inventario-de-lenguas/importar-hoy', [InventarioLenguasController::class, 'importarDesdeHoy'])
            ->name('inventario.lenguas.importar_hoy');
    });

    Route::middleware('module:cambios_destinos')->group(function () {
        Route::get('/cambios-de-destinos', [CambiosDestinosController::class, 'index'])->name('cambios.destinos');
        Route::patch('/cambios-de-destinos/{ingreso}', [CambiosDestinosController::class, 'update'])->name('cambios.destinos.update');
    });

    Route::middleware('module:ingresos')->group(function () {
        Route::get('/ingresos-de-lenguas', [IngresosLenguasController::class, 'index'])->name('ingresos.lenguas');
    });

    Route::middleware('module:despacho')->group(function () {
        Route::get('/despacho-de-lenguas', DespachoSeleccionPlacasController::class)->name('despacho.lenguas');
        Route::get('/despacho-de-lenguas/manual', DespachoLenguasController::class)->name('despacho.lenguas.manual');
        Route::get('/despacho-de-lenguas/checklist/{id_vehiculo_asignado}', [DespachoLenguasController::class, 'checklist'])
            ->whereNumber('id_vehiculo_asignado')
            ->name('despacho.lenguas.checklist');
        Route::get('/despacho-de-lenguas/vehiculos-asignados', VehiculoAsignadoLookupController::class)
            ->name('despacho.lookup.vehiculos');
        Route::get('/despacho-de-lenguas/lengua-destino-inventario', LenguaDestinoInventarioController::class)
            ->name('despacho.lookup.lengua_destino');
        Route::post('/despacho-de-lenguas/finalizar-inventario', DespachoFinalizarInventarioController::class)
            ->name('despacho.finalizar.inventario');
    });

    Route::middleware('module:desposte')->group(function () {
        Route::view('/lenguas-desposte', 'lenguas-desposte')->name('desposte.lenguas');
        Route::post('/lenguas-desposte/finalizar-inventario', DesposteFinalizarInventarioController::class)
            ->name('desposte.finalizar.inventario');
    });

    Route::middleware('module:historial')->group(function () {
        Route::get('/historia-despacho-lenguas', [HistoriaDespachoLenguasController::class, 'index'])
            ->name('historia.despacho.lenguas');
        Route::get('/historia-despacho-lenguas/desposte/{desposte}/detalle', [HistoriaDespachoLenguasController::class, 'detalleDesposte'])
            ->name('historia.desposte.lenguas.detalle');
        Route::get('/historia-despacho-lenguas/{despacho}/detalle', [HistoriaDespachoLenguasController::class, 'detalle'])
            ->name('historia.despacho.lenguas.detalle');
    });

    Route::middleware('module:entrega')->group(function () {
        Route::get('/entrega-de-conformidad', [EntregaConformidadController::class, 'index'])->name('entrega.conformidad');
        Route::get('/entrega-de-conformidad/{despacho}/pdf', [EntregaConformidadController::class, 'pdf'])
            ->name('entrega.conformidad.pdf');
        Route::get('/entrega-de-conformidad/{despacho}/destinos', [EntregaConformidadController::class, 'destinosManifest'])
            ->name('entrega.conformidad.destinos_manifest');
        Route::get('/entrega-de-conformidad/{despacho}/destinos.json', [EntregaConformidadController::class, 'destinosManifestJson'])
            ->name('entrega.conformidad.destinos_manifest_json');
    });

    Route::middleware('module:decomisos')->group(function () {
        Route::get('/decomisos-de-lenguas', [InspeccionSirtController::class, 'index'])->name('decomisos.lenguas');
    });
});
