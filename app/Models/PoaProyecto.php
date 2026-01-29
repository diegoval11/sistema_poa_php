<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoaProyecto extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'poa_proyectos';

    // Asignación masiva controlada
    protected $fillable = [
        'user_id', 'nombre', 'anio', 'objetivo_unidad', 'estado', 'aprobado_por', 'fecha_aprobacion', 'motivo_rechazo'
    ];

    protected $casts = [
        'anio' => 'integer',
        'fecha_aprobacion' => 'datetime',
    ];

    public function metas(): HasMany
    {
        return $this->hasMany(PoaMeta::class, 'poa_proyecto_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
