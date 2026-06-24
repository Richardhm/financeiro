<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CidadeCodigoVendedor extends Model
{
    protected $table = "cidade_codigo_vendedores";

    public function cidade()
    {
        return $this->belongsTo(TabelaOrigens::class,'tabela_origens_id','id');
    }


}
