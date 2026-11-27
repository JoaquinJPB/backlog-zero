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

    'providers' => [
        'google' => 'Google',
    ],

    'footer' => [
        // Attribution text required by IGDB's terms; keep it verbatim.
        'igdb_attribution' => 'Game data provided by :link',
    ],
];
