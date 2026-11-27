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

    'footer' => [
        // Attribution text required by IGDB's terms; keep it verbatim.
        'igdb_attribution' => 'Game data provided by :link',
    ],
];
