<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Administradora;
use App\Models\Plano;
use App\Models\User;

class ComissoesCorretoresConfiguracoes extends Model
{

    protected $table = 'comissoes_corretores_configuracoes';

    protected $fillable = [
        'plano_id',
        'user_id',
        'administradora_id',
        'tabela_origens_id',
        'valor',
        'parcela',
        'corretora_id'
    ];

    protected $casts = [
        'valor'   => 'decimal:2',
        'parcela' => 'integer',
    ];

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function administradora()
    {
        return $this->belongsTo(Administradora::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
