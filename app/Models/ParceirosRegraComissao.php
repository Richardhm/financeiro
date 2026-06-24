<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParceirosRegraComissao extends Model
{
    protected $table = 'parceiros_regras_comissao';

    protected $fillable = [
        'corretora_id',
        'parceiro_id',
        'plano_id',
        'parcela_1_pct',
        'parcela_2_pct',
        'parcela_3_pct',
        'parcela_4_pct',
        'parcela_5_pct',
        'parcela_6_pct',
    ];

    protected $casts = [
        'parcela_1_pct' => 'decimal:2',
        'parcela_2_pct' => 'decimal:2',
        'parcela_3_pct' => 'decimal:2',
        'parcela_4_pct' => 'decimal:2',
        'parcela_5_pct' => 'decimal:2',
        'parcela_6_pct' => 'decimal:2',
    ];

    public function parceiro()
    {
        return $this->belongsTo(User::class, 'parceiro_id');
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
