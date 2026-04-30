<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $menuModulos = Modulo::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->filter(static fn (Modulo $m): bool => Route::has($m->route_name))
            ->filter(static fn (Modulo $m): bool => $user->canSeeMenuModule($m))
            ->values();

        return view('menu', [
            'menuModulos' => $menuModulos,
        ]);
    }
}
