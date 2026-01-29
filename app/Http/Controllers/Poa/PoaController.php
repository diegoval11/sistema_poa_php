<?php

namespace App\Http\Controllers\Poa;

use App\Http\Controllers\Controller;
use App\Models\PoaProyecto;
use Illuminate\Support\Facades\Auth;

class PoaController extends Controller
{
    public function index()
    {
        $proyectos = PoaProyecto::where('user_id', Auth::id())
                        ->orderBy('anio', 'desc')
                        ->get();

        return view('poa.index', compact('proyectos'));
    }
}
