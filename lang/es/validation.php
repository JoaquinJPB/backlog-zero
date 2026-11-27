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

    'username' => [
        'length' => 'El nombre de usuario debe tener entre :min y :max caracteres.',
        'format' => 'Solo se permiten letras minúsculas sin tildes, números y guiones bajos.',
        'reserved' => 'Ese nombre de usuario está reservado.',
    ],

    'attributes' => [
        'name' => 'nombre',
        'username' => 'nombre de usuario',
    ],
];
