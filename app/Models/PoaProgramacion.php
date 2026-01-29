<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaProgramacion extends Model
{
    protected $table = 'poa_programaciones';

    protected $fillable = [
        'poa_actividad_id',
        'mes',
        'anio',
        'cantidad_programada',
        'cantidad_ejecutada',
        'causal_desvio',
        'es_extraordinaria'
    ];

    // Casts para asegurar tipos correctos
    protected $casts = [
        'cantidad_programada' => 'integer',
        'cantidad_ejecutada' => 'integer',
        'es_extraordinaria' => 'boolean',
        'mes' => 'integer',
        'anio' => 'integer',
    ];

    protected $touches = ['actividad'];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(PoaActividad::class, 'poa_actividad_id');
    }
}
