<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Vendedores (cargo corretor) so acessam a area deles: Meus Clientes,
 * Programa Estrela e sair. Qualquer outra rota redireciona para Meus Clientes.
 * Backoffice, diretores e demais cargos passam direto.
 */
class AcessoVendedor
{
    private const CARGO_CORRETOR = 2;

    private const ROTAS_PERMITIDAS = [
        'vendedor.clientes',
        'vendedor.clientes.parcelas',
        'estrela.index',
        'logout',
        'profile.edit',
        'profile.update',
        'password.update',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && (int) $user->cargo_id === self::CARGO_CORRETOR) {
            $rota = optional($request->route())->getName();
            if (!in_array($rota, self::ROTAS_PERMITIDAS, true)) {
                return redirect()->route('vendedor.clientes');
            }
        }

        return $next($request);
    }
}
