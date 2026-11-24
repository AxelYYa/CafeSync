<?php

/*
| Mensajes de validación en español para las reglas que usa CaféSync.
| Las reglas no listadas caen en el idioma de respaldo (inglés).
*/

return [
    'array' => 'El campo :attribute debe ser una lista.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña actual no es correcta.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'different' => 'El campo :attribute debe ser distinto de :other.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'exists' => 'El valor seleccionado en :attribute no existe.',
    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
    ],
    'in' => 'El valor seleccionado en :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => [
        'letters' => 'La :attribute debe incluir al menos una letra.',
        'mixed' => 'La :attribute debe incluir mayúsculas y minúsculas.',
        'numbers' => 'La :attribute debe incluir al menos un número.',
        'symbols' => 'La :attribute debe incluir al menos un símbolo.',
        'uncompromised' => 'La :attribute apareció en una filtración de datos. Elige otra.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_with' => 'El campo :attribute es obligatorio cuando se indica :values.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El valor de :attribute ya está registrado.',

    'attributes' => [
        'canal' => 'método de verificación',
        'canal_origen' => 'canal de origen',
        'cantidad' => 'cantidad',
        'codigo' => 'código',
        'correo' => 'correo',
        'entrada' => 'código o respuesta',
        'estado' => 'estado',
        'identificador' => 'usuario o correo',
        'items' => 'productos',
        'items.*.cantidad' => 'cantidad',
        'items.*.id_producto' => 'producto',
        'login' => 'usuario o correo',
        'metodo_pago_registrado' => 'método de pago',
        'motivo' => 'motivo',
        'nombre' => 'nombre',
        'nota' => 'nota',
        'password' => 'contraseña',
        'password_actual' => 'contraseña actual',
        'pin' => 'PIN',
        'precio' => 'precio',
        'pregunta_secreta' => 'pregunta secreta',
        'respuesta_secreta' => 'respuesta secreta',
        'telefono' => 'teléfono',
        'tipo' => 'tipo de movimiento',
        'username' => 'nombre de usuario',
    ],
];
