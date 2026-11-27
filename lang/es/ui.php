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

    'games' => [
        'no_cover' => 'Sin portada',
        'cover_alt' => 'Portada de :name',
        'released' => 'Lanzamiento',
        'release_unknown' => 'Fecha de lanzamiento desconocida',
        'platforms' => 'Plataformas',
        'genres' => 'Géneros',
        'no_summary' => 'Todavía no hay descripción para este juego.',
        'view_on_igdb' => 'Ver en IGDB',
        'search' => [
            'nav' => 'Buscar juegos',
            'title' => 'Buscar juegos',
            'label' => 'Nombre del juego',
            'placeholder' => 'Zelda, Hollow Knight, Final Fantasy…',
            'hint' => 'Escribe al menos :min caracteres.',
            'searching' => 'Buscando…',
            'empty' => 'No hemos encontrado juegos para «:query».',
            'failed' => 'La búsqueda no está disponible ahora mismo. Inténtalo de nuevo en un rato.',
        ],
    ],

    'journal' => [
        'nav' => 'Journal',
        'title' => 'Journal :year',
        'year_label' => 'Año',
        'platform_label' => 'Plataforma',
        'all_platforms' => 'Todas',
        'include_abandoned' => 'Incluir abandonados',
        'finished_count' => '{0} Ningún juego terminado|{1} 1 juego terminado|[2,*] :count juegos terminados',
        'abandoned_count' => '{1} 1 abandonado|[2,*] :count abandonados',
        'average_rating' => 'Nota media: :stars',
        'month_count' => '{1} 1 juego|[2,*] :count juegos',
        'finished_on' => 'Terminado el :date',
        'abandoned_on' => 'Abandonado el :date',
        'took_days' => '{0} en el mismo día|{1} en 1 día|[2,*] en :count días',
        'empty' => 'No hay juegos terminados en :year con estos filtros.',
        'empty_all' => 'Aún no has terminado ningún juego. Cuando marques uno como terminado con su fecha, aparecerá aquí.',
    ],

    'library' => [
        'nav' => 'Mi biblioteca',
        'title' => 'Mi biblioteca',
        'all' => 'Todos',
        'empty' => 'Todavía no hay juegos aquí. Busca uno y añádelo desde su página.',
        'started' => 'Empezado: :date',
        'finished' => 'Terminado: :date',
        'abandoned' => 'Abandonado: :date',
        'previous_page' => 'Anterior',
        'next_page' => 'Siguiente',
        'page_of' => 'Página :current de :last',
        'section_title' => 'En tu biblioteca',
        'add' => 'Añadir a mi biblioteca',
        'save' => 'Guardar',
        'remove' => 'Quitar de mi biblioteca',
        'remove_confirm' => '¿Seguro que quieres quitar este juego de tu biblioteca? Se perderán su nota y fechas.',
        'added' => 'Juego añadido a tu biblioteca.',
        'saved' => 'Cambios guardados.',
        'removed' => 'Juego quitado de tu biblioteca.',
        'already_added' => 'Este juego ya estaba en tu biblioteca.',
        'status_label' => 'Estado',
        'rating_label' => 'Nota',
        'rating_none' => 'Sin nota',
        'rating_clear' => 'Quitar nota',
        'rating_value' => ':stars de 5 estrellas',
        'platform_label' => 'Plataforma',
        'platform_none' => 'Sin especificar',
        'started_on_label' => 'Empezado el',
        'finished_on_label' => 'Terminado el',
        'abandoned_on_label' => 'Abandonado el',
        'statuses' => [
            'playing' => 'Jugando',
            'finished' => 'Terminado',
            'abandoned' => 'Abandonado',
            'backlog' => 'Pendiente',
        ],
    ],

    'profile' => [
        'edit' => 'Editar perfil',
        'private_note' => 'Por ahora solo tú puedes ver esta página.',
        'counts' => [
            'library' => 'En la biblioteca',
            'finished' => 'Terminados',
            'finished_this_year' => 'Terminados en :year',
            'playing' => 'Jugando',
            'backlog' => 'Pendientes',
            'abandoned' => 'Abandonados',
        ],
        'average_rating' => 'Nota media',
        'no_ratings' => 'Sin notas',
        'ratings_title' => 'Notas',
        'rated_count' => '{0} Ningún juego puntuado|{1} 1 juego puntuado|[2,*] :count juegos puntuados',
        'rating_bar' => ':stars estrellas: :count',
        'per_year_title' => 'Terminados por año',
        'top_platforms' => 'Plataformas más jugadas',
        'top_genres' => 'Géneros más jugados',
        'game_count' => '{1} 1 juego|[2,*] :count juegos',
        'nothing_yet' => 'Todavía no hay datos.',
        'games_title' => 'Juegos jugados',
        'games_empty' => 'Aún no hay juegos jugados. Los que marques como jugando, terminados o abandonados aparecerán aquí.',
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
