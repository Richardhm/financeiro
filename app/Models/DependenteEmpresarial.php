<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DependenteEmpresarial extends Model
{
    protected $table = 'dependentes_empresariais';

    protected $fillable = [
        'contrato_empresarial_id',
        'cpf',
        'nome',
        'tipo',
        'data_nascimento',
        'valor',
    ];

    public function contrato()
    {
        return $this->belongsTo(ContratoEmpresarial::class, 'contrato_empresarial_id');
    }
}
