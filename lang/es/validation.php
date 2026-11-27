<?php

// Spanish messages for the rules this app uses. Anything missing falls back
// to the framework's English file (fallback_locale = en).

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'unique' => 'Ese :attribute ya está en uso.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
    ],
    'date_format' => 'El campo :attribute debe tener el formato :format.',
    'before_or_equal' => 'El campo :attribute no puede ser posterior a :date.',
    'after_or_equal' => 'El campo :attribute no puede ser anterior a :date.',
    'exists' => 'El valor de :attribute no es válido.',
    'enum' => 'El valor de :attribute no es válido.',
    'prohibited' => 'El campo :attribute no está permitido.',

    'library' => [
        'finish_date_not_allowed' => 'Solo los juegos terminados o abandonados pueden tener fecha de fin.',
    ],

    'username' => [
        'length' => 'El nombre de usuario debe tener entre :min y :max caracteres.',
        'format' => 'Solo se permiten letras minúsculas sin tildes, números y guiones bajos.',
        'reserved' => 'Ese nombre de usuario está reservado.',
    ],

    'attributes' => [
        'name' => 'nombre',
        'username' => 'nombre de usuario',
        'status' => 'estado',
        'rating' => 'nota',
        'platform_id' => 'plataforma',
        'started_on' => 'fecha de inicio',
        'finished_on' => 'fecha de fin',
    ],

    // Clearer than the generic messages with ":date" = "today" / a field name.
    'custom' => [
        'started_on' => [
            'before_or_equal' => 'La fecha de inicio no puede ser futura.',
        ],
        'finished_on' => [
            'before_or_equal' => 'La fecha de fin no puede ser futura.',
            'after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ],
    ],
];
