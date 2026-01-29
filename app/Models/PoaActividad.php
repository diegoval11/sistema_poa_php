<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoaActividad extends Model
{
    use HasFactory;

    protected $table = 'poa_actividades';

    protected $fillable = [
        'poa_meta_id',
        'descripcion',
        'unidad_medida',
        'es_cuantificable',
        'cantidad_programada_total',
        'medio_verificacion',
        'recursos',
        'costo_estimado',
        'es_no_planificada',
        'estado_aprobacion'
    ];

    // DEFINICIÓN DE CASTS (ACTUALIZADO)
    protected $casts = [
        'es_cuantificable' => 'boolean',
        'es_no_planificada' => 'boolean',
        'cantidad_programada_total' => 'integer', // CORRECCIÓN: Ahora es integer en BD
        'costo_estimado' => 'decimal:2', // Asegura precisión monetaria
    ];

    protected $touches = ['meta'];
    public function meta(): BelongsTo
    {
        return $this->belongsTo(PoaMeta::class, 'poa_meta_id');
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(PoaProgramacion::class, 'poa_actividad_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(PoaEvidencia::class, 'poa_actividad_id');
    }

    protected static function booted()
    {
        static::created(function ($actividad) {
            $anio = $actividad->meta->proyecto->anio ?? date('Y');
            $batch = [];
            for ($i = 1; $i <= 12; $i++) {
                $batch[] = [
                    'poa_actividad_id' => $actividad->id,
                    'mes' => $i,
                    'anio' => $anio,
                    'cantidad_programada' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            PoaProgramacion::insert($batch);
        });
    }
}
