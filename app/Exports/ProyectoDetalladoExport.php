<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProyectoDetalladoExport implements WithMultipleSheets
{
    protected $proyecto;
    protected $fechaGeneracion;

    public function __construct($proyecto, $fechaGeneracion)
    {
        $this->proyecto = $proyecto;
        $this->fechaGeneracion = $fechaGeneracion;
    }

    public function sheets(): array
    {
        return [
            new ResumenProyectoSheet($this->proyecto, $this->fechaGeneracion),
            new MetasActividadesSheet($this->proyecto),
        ];
    }
}
