<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Decomisos\InspeccionSirtController;
use App\Http\Controllers\Despacho\VehiculoAsignadoLookupController;
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
    Route::view('/inventario-de-lenguas', 'inventario-lenguas')->name('inventario.lenguas');
    Route::view('/despacho-de-lenguas', 'despacho-lenguas')->name('despacho.lenguas');
    Route::get('/despacho-de-lenguas/vehiculos-asignados', VehiculoAsignadoLookupController::class)
        ->name('despacho.lookup.vehiculos');
    Route::view('/entrega-de-conformidad', 'entrega-conformidad')->name('entrega.conformidad');
    Route::get('/decomisos-de-lenguas', [InspeccionSirtController::class, 'index'])->name('decomisos.lenguas');
});
