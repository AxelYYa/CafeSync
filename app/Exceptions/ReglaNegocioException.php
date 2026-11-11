<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Regla de negocio incumplida; el mensaje está redactado para mostrarse al usuario.
 */
class ReglaNegocioException extends RuntimeException {}
