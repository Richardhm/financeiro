<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComissoesCorretoraConfiguracoes extends Model
{
    protected $table = 'comissoes_corretora_configuracoes';

    protected $fillable = [
        'corretora_id',
        'plano_id',
        'administradora_id',
        'tabela_origens_id',
        'user_id',
        'valor',
        'parcela',
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
