<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaPredeterminada extends Model
{
    protected $table = 'metas_predeterminadas';

    protected $fillable = [
        'description',
    ];
}
