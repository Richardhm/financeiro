<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParceirosConfigPagamento extends Model
{
    protected $table = 'parceiros_config_pagamento';

    protected $fillable = [
        'user_id',
        'frequencia',
        'dias_pagamento',
        'ativo',
    ];

    protected $casts = [
        'dias_pagamento' => 'array',
        'ativo'          => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
