<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VidasMesVendedor extends Model
{
    protected $table    = 'vidas_mes_vendedor';
    protected $fillable = [
        'user_id',
        'mes',
        'quantidade_individual',
        'quantidade_coletivo',
        'quantidade_empresarial',
        'quantidade_comissao',
        'quantidade_total',
    ];
}
