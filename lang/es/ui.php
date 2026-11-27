<?php

// User-facing strings shared with the React frontend (see HandleInertiaRequests).
// Keys are read in TS with t('group.key').

return [
    'app' => [
        'tagline' => 'Tu diario de videojuegos: a qué juegas, qué terminas y qué te pareció.',
    ],

    'welcome' => [
        'title' => 'Inicio',
        'heading' => 'Deja el backlog a cero',
        'intro' => 'Apunta los juegos que empiezas, los que terminas y los que abandonas. Ponles nota, fechas y plataforma, y repasa tu año en listas.',
    ],

    'auth' => [
        'login_with' => 'Entrar con :provider',
        'logout' => 'Cerrar sesión',
        'failed' => 'No se pudo iniciar sesión. Inténtalo de nuevo.',
        'missing_email' => 'Tu cuenta no nos ha facilitado un email, así que no podemos registrarte.',
        'email_taken' => 'Ya existe una cuenta con ese email, creada con otro método de acceso. Entra con el método que usaste la primera vez.',
    ],

    'onboarding' => [
        'title' => 'Elige tu nombre de usuario',
        'intro' => 'Será tu dirección pública en Backlog Zero, así que elige uno que te guste: :url',
        'label' => 'Nombre de usuario',
        'hint' => 'Entre :min y :max caracteres: letras minúsculas, números y guiones bajos.',
        'submit' => 'Continuar',
        'done' => '¡Listo, @:username! Ya puedes empezar a apuntar juegos.',
    ],

    'settings' => [
        'title' => 'Tu perfil',
        'name_label' => 'Nombre visible',
        'name_hint' => 'Así te verán tus amigos. Máximo :max caracteres.',
        'email_label' => 'Email',
        'email_hint' => 'Viene de tu cuenta de acceso y no se puede cambiar aquí.',
        'username_warning' => 'Si cambias tu nombre de usuario, cambiará tu dirección pública y los enlaces antiguos dejarán de funcionar.',
        'submit' => 'Guardar cambios',
        'saved' => 'Perfil actualizado.',
    ],

    'providers' => [
        'google' => 'Google',
    ],

    'footer' => [
        // Attribution text required by IGDB's terms; keep it verbatim.
        'igdb_attribution' => 'Game data provided by :link',
    ],
];
