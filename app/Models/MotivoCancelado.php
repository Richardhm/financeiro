<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivoCancelado extends Model
{
    protected $table = 'motivos_cancelados';

    protected $fillable = [
        'motivo',
        'descricao',
    ];
}
