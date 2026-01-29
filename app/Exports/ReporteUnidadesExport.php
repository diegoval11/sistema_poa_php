<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteUnidadesExport implements WithMultipleSheets
{
    protected $unidades;
    protected $fechaGeneracion;
    protected $resumen;

    public function __construct($unidades, $fechaGeneracion, $resumen)
    {
        $this->unidades = $unidades;
        $this->fechaGeneracion = $fechaGeneracion;
        $this->resumen = $resumen;
    }

    public function sheets(): array
    {
        return [
            new ResumenUnidadesSheet($this->fechaGeneracion, $this->resumen),
            new DetalleUnidadesSheet($this->unidades),
        ];
    }
}
