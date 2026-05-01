<?php

namespace App\Http\Controllers\Despacho\Concerns;

use Illuminate\Http\Request;

trait ResuelveUsuarioVehiculoDespacho
{
    /** Username efectivo para filtrar va.user_name cuando DESPACHO_PPV_FILTRAR_USER_NAME=true. */
    protected function usernameVehiculoEfectivo(Request $request): string
    {
        if (config('despacho.vehiculo_registra_use_auth_username') === true) {
            $u = trim((string) ($request->user()?->username ?? ''));
            if ($u !== '') {
                return $u;
            }
        }

        return (string) config('despacho.vehiculo_registra_username', 'porteria52');
    }
}
