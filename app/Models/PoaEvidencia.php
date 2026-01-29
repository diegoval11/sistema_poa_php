<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaEvidencia extends Model
{
    protected $table = 'poa_evidencias';

    protected $fillable = [
        'poa_actividad_id',
        'tipo',
        'archivo',
        'url',
        'descripcion',
        'mes'
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(PoaActividad::class, 'poa_actividad_id');
    }
}
