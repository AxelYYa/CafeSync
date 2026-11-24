<?php

namespace App\Exceptions;

class StockInsuficienteException extends ReglaNegocioException
{
    /**
     * @param  list<string>  $faltantes  Nombres de los ingredientes que no alcanzan.
     */
    public function __construct(public readonly array $faltantes)
    {
        parent::__construct('Falta '.implode(', ', $faltantes).'. Reabastece el inventario o avisa al gerente.');
    }
}
