<?php

return [
    /*
    | Langues du site. La première est la langue par défaut (et de repli :
    | un texte non traduit en anglais s'affiche en français).
    */
    'locales' => [
        'fr' => 'Français',
        'en' => 'English',
    ],

    /*
    | Préfixes d'URL traduits pour les contenus structurés.
    | Ex. : /fr/realisations/mon-projet  ↔  /en/case-studies/my-project
    */
    'segments' => [
        'solutions' => ['fr' => 'solutions', 'en' => 'solutions'],
        'case_studies' => ['fr' => 'realisations', 'en' => 'case-studies'],
        'posts' => ['fr' => 'insights', 'en' => 'insights'],
        'jobs' => ['fr' => 'carrieres', 'en' => 'careers'],
    ],

    'roles' => [
        'admin' => 'Administrateur',
        'editor' => 'Éditeur',
    ],

    'post_types' => [
        'article' => ['fr' => 'Article', 'en' => 'Article'],
        'newsletter' => ['fr' => 'Newsletter', 'en' => 'Newsletter'],
        'research' => ['fr' => 'Action Research', 'en' => 'Action Research'],
    ],

    'upload_max_kb' => 10240,
];
