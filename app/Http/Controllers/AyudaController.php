<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AyudaController extends Controller
{
    /**
     * Help Mode: ficha de receta de un producto. Con una petición AJAX devuelve sólo el fragmento
     * para abrirlo sobre el KDS sin perder la orden que se está preparando.
     */
    public function show(Request $request, Producto $producto): View
    {
        $producto->load(['estacion:id_estacion,nombre', 'recetaActiva.detalles.ingrediente:id_ingrediente,nombre,unidad_medida']);

        $vista = $request->ajax() || $request->wantsJson() ? 'ayuda._ficha' : 'ayuda.show';

        return view($vista, ['producto' => $producto, 'receta' => $producto->recetaActiva]);
    }
}
