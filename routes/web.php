<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Decomisos\InspeccionSirtController;
use App\Http\Controllers\Despacho\DespachoFinalizarInventarioController;
use App\Http\Controllers\Despacho\LenguaDestinoInventarioController;
use App\Http\Controllers\Despacho\VehiculoAsignadoLookupController;
use App\Http\Controllers\Ingresos\IngresosLenguasController;
use App\Http\Controllers\Inventario\InventarioLenguasController;
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
    Route::view('/menu', 'menu')->name('menu');
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/inventario-de-lenguas', [InventarioLenguasController::class, 'index'])->name('inventario.lenguas');
    Route::post('/inventario-de-lenguas/importar-hoy', [InventarioLenguasController::class, 'importarDesdeHoy'])
        ->name('inventario.lenguas.importar_hoy');
    Route::get('/ingresos-de-lenguas', [IngresosLenguasController::class, 'index'])->name('ingresos.lenguas');
    Route::view('/despacho-de-lenguas', 'despacho-lenguas')->name('despacho.lenguas');
    Route::get('/despacho-de-lenguas/vehiculos-asignados', VehiculoAsignadoLookupController::class)
        ->name('despacho.lookup.vehiculos');
    Route::get('/despacho-de-lenguas/lengua-destino-inventario', LenguaDestinoInventarioController::class)
        ->name('despacho.lookup.lengua_destino');
    Route::post('/despacho-de-lenguas/finalizar-inventario', DespachoFinalizarInventarioController::class)
        ->name('despacho.finalizar.inventario');
    Route::view('/entrega-de-conformidad', 'entrega-conformidad')->name('entrega.conformidad');
    Route::get('/decomisos-de-lenguas', [InspeccionSirtController::class, 'index'])->name('decomisos.lenguas');
});
