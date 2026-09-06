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
        'parcela_1_pct',
        'parcela_2_pct',
        'parcela_3_pct',
        'parcela_4_pct',
        'parcela_5_pct',
        'parcela_6_pct',
    ];

    protected $casts = [
        'vidas_min'     => 'integer',
        'vidas_max'     => 'integer',
        'parcela_1_pct' => 'decimal:2',
        'parcela_2_pct' => 'decimal:2',
        'parcela_3_pct' => 'decimal:2',
        'parcela_4_pct' => 'decimal:2',
        'parcela_5_pct' => 'decimal:2',
        'parcela_6_pct' => 'decimal:2',
    ];
}
