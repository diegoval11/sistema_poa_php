<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidades';

    protected $fillable = ['nombre', 'activa', 'sin_reporte'];

    protected $casts = [
        'activa' => 'boolean',
        'sin_reporte' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
