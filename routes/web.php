<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForcedPasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Decomisos\InspeccionSirtController;
use App\Http\Controllers\Despacho\DespachoFinalizarInventarioController;
use App\Http\Controllers\Despacho\DespachoLenguasController;
use App\Http\Controllers\Despacho\DespachoOperadorPlacaStoreController;
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
use App\Http\Controllers\ObservatorioController;
use App\Http\Controllers\Usuarios\GestionUsuariosController;
use App\Http\Controllers\Usuarios\PerfilController;
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
    Route::get('/cambiar-contrasena', [ForcedPasswordChangeController::class, 'edit'])->name('password.change');
    Route::post('/cambiar-contrasena', [ForcedPasswordChangeController::class, 'update'])->name('password.change.update');

    Route::get('/menu', MenuController::class)->name('menu');

    Route::get('/mi-perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/mi-perfil', [PerfilController::class, 'update'])->name('perfil.update');

    /*
     | Gestión de usuarios (solo super_admin activo).
     | Incluye editar, restablecer contraseña (= username + must_change_password) e inactivar.
     | Tras desplegar: migrate (must_change_password, avatar_path) y ver README → Despliegue.
     */
    Route::middleware('can.manage.users')->group(function () {
        Route::get('/gestion-de-usuarios/crear', [GestionUsuariosController::class, 'create'])->name('gestion.usuarios.create');
        Route::get('/gestion-de-usuarios', [GestionUsuariosController::class, 'index'])->name('gestion.usuarios.index');
        Route::post('/gestion-de-usuarios', [GestionUsuariosController::class, 'store'])->name('gestion.usuarios.store');
        Route::get('/gestion-de-usuarios/{user}/editar', [GestionUsuariosController::class, 'edit'])->name('gestion.usuarios.edit');
        Route::put('/gestion-de-usuarios/{user}', [GestionUsuariosController::class, 'update'])->name('gestion.usuarios.update');
        Route::post('/gestion-de-usuarios/{user}/restablecer-contrasena', [GestionUsuariosController::class, 'resetPassword'])
            ->name('gestion.usuarios.reset_password');
        Route::post('/gestion-de-usuarios/{user}/inactivar', [GestionUsuariosController::class, 'inactivar'])
            ->name('gestion.usuarios.inactivar');
        Route::post('/gestion-de-usuarios/{user}/activar', [GestionUsuariosController::class, 'activar'])
            ->name('gestion.usuarios.activar');
        Route::delete('/gestion-de-usuarios/{user}', [GestionUsuariosController::class, 'destroy'])
            ->name('gestion.usuarios.destroy');
    });

    Route::middleware('module:dashboard')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });

    Route::middleware('module:observatorio')->group(function () {
        Route::get('/observatorio', ObservatorioController::class)->name('observatorio');
    });

    Route::middleware('module:inventario')->group(function () {
        Route::get('/inventario-de-lenguas', [InventarioLenguasController::class, 'index'])->name('inventario.lenguas');
        Route::get('/inventario-de-lenguas/excel', [InventarioLenguasController::class, 'exportExcel'])
            ->name('inventario.lenguas.excel');
        Route::post('/inventario-de-lenguas/importar-hoy', [InventarioLenguasController::class, 'importarDesdeHoy'])
            ->name('inventario.lenguas.importar_hoy');
    });

    Route::middleware('module:cambios_destinos')->group(function () {
        Route::get('/cambios-de-destinos', [CambiosDestinosController::class, 'index'])->name('cambios.destinos');
        Route::patch('/cambios-de-destinos/{ingreso}', [CambiosDestinosController::class, 'update'])->name('cambios.destinos.update');
        Route::post('/cambios-de-destinos/masivo', [CambiosDestinosController::class, 'bulkUpdate'])->name('cambios.destinos.bulk');
        Route::post('/cambios-de-destinos/guardar', [CambiosDestinosController::class, 'guardarCambios'])
            ->name('cambios.destinos.guardar');
        Route::post('/cambios-de-destinos/marcar-adicional', [CambiosDestinosController::class, 'marcarAdicional'])
            ->name('cambios.destinos.marcar_adicional');
        Route::post('/cambios-de-destinos/adicionales-masivo', [CambiosDestinosController::class, 'bulkAdicionales'])
            ->name('cambios.destinos.adicionales_bulk');
        Route::post('/cambios-de-destinos/salidas-masivo', [CambiosDestinosController::class, 'bulkSalidas'])
            ->name('cambios.destinos.salidas_bulk');
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
        Route::post('/despacho-de-lenguas/operador-placa', DespachoOperadorPlacaStoreController::class)
            ->name('despacho.operador_placa.store');
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
        Route::get('/entrega-de-conformidad/avisos', [EntregaConformidadController::class, 'avisosNuevos'])
            ->name('entrega.conformidad.avisos');
        Route::get('/entrega-de-conformidad/{despacho}/pdf', [EntregaConformidadController::class, 'pdf'])
            ->name('entrega.conformidad.pdf');
        Route::get('/entrega-de-conformidad/{despacho}/destinos', [EntregaConformidadController::class, 'destinosManifest'])
            ->name('entrega.conformidad.destinos_manifest');
        Route::get('/entrega-de-conformidad/{despacho}/destinos.json', [EntregaConformidadController::class, 'destinosManifestJson'])
            ->name('entrega.conformidad.destinos_manifest_json');
    });

    Route::middleware('module:decomisos')->group(function () {
        Route::get('/decomisos-de-lenguas', [InspeccionSirtController::class, 'index'])->name('decomisos.lenguas');
        Route::get('/decomisos-de-lenguas/excel', [InspeccionSirtController::class, 'exportExcel'])->name('decomisos.lenguas.excel');
        Route::get('/decomisos-de-lenguas/sugerencias-producto', [InspeccionSirtController::class, 'sugerenciasProducto'])
            ->name('decomisos.lenguas.sugerencias_producto');
        Route::post('/decomisos-de-lenguas/manual', [InspeccionSirtController::class, 'storeManual'])->name('decomisos.lenguas.manual');
    });
});
