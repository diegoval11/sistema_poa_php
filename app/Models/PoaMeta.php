<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoaMeta extends Model
{
    use HasFactory;

    protected $table = 'poa_metas';

    protected $fillable = ['poa_proyecto_id', 'descripcion'];

    protected $touches = ['proyecto'];
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(PoaProyecto::class, 'poa_proyecto_id');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(PoaActividad::class, 'poa_meta_id');
    }
}
