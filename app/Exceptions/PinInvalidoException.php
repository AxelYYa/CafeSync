<?php

namespace App\Exceptions;

/**
 * El PIN no corresponde a ningún empleado activo; cuenta como intento fallido.
 */
class PinInvalidoException extends ReglaNegocioException
{
    public function __construct()
    {
        parent::__construct('PIN no válido.');
    }
}
