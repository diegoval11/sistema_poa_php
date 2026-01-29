<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjetivoEspecificoPredeterminado extends Model
{
    protected $table = 'objetivos_especificos_predeterminados';

    protected $fillable = [
        'description',
    ];
}
