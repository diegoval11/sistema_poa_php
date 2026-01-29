<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetaPredeterminada;
use App\Models\ObjetivoEspecificoPredeterminado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * Display catalog management view
     */
    public function index()
    {
        $metas = MetaPredeterminada::orderBy('description')->get();
        $objetivos = ObjetivoEspecificoPredeterminado::orderBy('description')->get();

        return view('admin.panel.catalogos', [
            'metas' => $metas,
            'objetivos' => $objetivos,
        ]);
    }

    /**
     * Store a new meta predeterminada
     */
    public function storeMeta(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            MetaPredeterminada::create([
                'description' => $request->description,
            ]);
        });

        return redirect()->route('admin.panel.catalogos')
            ->with('success', 'Meta predeterminada creada exitosamente');
    }

    /**
     * Delete a meta predeterminada
     */
    public function destroyMeta($id)
    {
        $meta = MetaPredeterminada::findOrFail($id);

        DB::transaction(function () use ($meta) {
            $meta->delete();
        });

        return redirect()->route('admin.panel.catalogos')
            ->with('success', 'Meta predeterminada eliminada exitosamente');
    }

    /**
     * Store a new objetivo específico predeterminado
     */
    public function storeObjetivo(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            ObjetivoEspecificoPredeterminado::create([
                'description' => $request->description,
            ]);
        });

        return redirect()->route('admin.panel.catalogos')
            ->with('success', 'Objetivo específico predeterminado creado exitosamente');
    }

    /**
     * Delete an objetivo específico predeterminado
     */
    public function destroyObjetivo($id)
    {
        $objetivo = ObjetivoEspecificoPredeterminado::findOrFail($id);

        DB::transaction(function () use ($objetivo) {
            $objetivo->delete();
        });

        return redirect()->route('admin.panel.catalogos')
            ->with('success', 'Objetivo específico predeterminado eliminado exitosamente');
    }
}
