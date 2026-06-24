<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaixaComissaoClt extends Model
{
    protected $table = 'faixas_comissao_clt';

    protected $fillable = [
        'corretora_id',
        'nome',
        'vidas_min',
        'vidas_max',
        'producao_min',
        'producao_max',
        'percentual',
        'producao_bonus',
        'percentual_bonus',
    ];

    protected $casts = [
        'vidas_min'        => 'integer',
        'vidas_max'        => 'integer',
        'producao_min'     => 'decimal:2',
        'producao_max'     => 'decimal:2',
        'percentual'       => 'decimal:2',
        'producao_bonus'   => 'decimal:2',
        'percentual_bonus' => 'decimal:2',
    ];
}
