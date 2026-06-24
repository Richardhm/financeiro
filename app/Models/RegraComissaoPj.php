<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegraComissaoPj extends Model
{
    protected $table = 'regras_comissao_pj';

    protected $fillable = [
        'corretora_id',
        'nome',
        'vidas_min',
        'vidas_max',
        'parcela_2_pct',
        'parcela_3_pct',
        'parcela_4_pct',
    ];

    protected $casts = [
        'vidas_min'     => 'integer',
        'vidas_max'     => 'integer',
        'parcela_2_pct' => 'decimal:2',
        'parcela_3_pct' => 'decimal:2',
        'parcela_4_pct' => 'decimal:2',
    ];
}
