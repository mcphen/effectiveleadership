<?php

namespace App\Cms;

/**
 * Catalogue des blocs disponibles dans le constructeur de pages.
 *
 * Chaque bloc est décrit une seule fois ici :
 *  - l'admin (Vue) génère automatiquement son formulaire à partir de « fields » ;
 *  - le site public l'affiche avec resources/views/blocks/{type}.blade.php.
 *
 * Un bloc enregistré a la forme :
 *   { "id": "b_x1y2", "type": "hero", "hidden": false, "data": {...}, "settings": {...} }
 *
 * Types de champs : voir App\Cms\Fields.
 * « t » => true  : champ traduisible, stocké sous la forme {"fr": "...", "en": "..."}.
 */
class Blocks
{
    public const GROUPS = [
        'layout' => 'En-têtes & appels à l\'action',
        'content' => 'Contenu',
        'dynamic' => 'Contenus du site',
        'forms' => 'Formulaires',
    ];

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        $button = [
            ['name' => 'label', 'type' => 'text', 'label' => 'Texte du bouton', 't' => true, 'width' => 'half'],
            ['name' => 'url', 'type' => 'link', 'label' => 'Lien', 'width' => 'half'],
            ['name' => 'style', 'type' => 'select', 'label' => 'Style', 'width' => 'half', 'options' => [
                'primary' => 'Plein', 'secondary' => 'Couleur secondaire', 'outline' => 'Contour', 'link' => 'Lien texte',
            ], 'default' => 'primary'],
        ];

        $heading = [
            ['name' => 'eyebrow', 'type' => 'text', 'label' => 'Sur-titre', 't' => true, 'help' => 'Petit texte au-dessus du titre (facultatif).'],
            ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true],
            ['name' => 'intro', 'type' => 'textarea', 'label' => 'Introduction', 't' => true, 'rows' => 3],
        ];

        return [
            // ───────────── En-têtes & appels à l'action ─────────────
            'hero' => [
                'label' => 'Bannière',
                'description' => 'Grand en-tête avec image de fond, titre et boutons.',
                'group' => 'layout',
                'icon' => 'image',
                'fields' => [
                    ['name' => 'eyebrow', 'type' => 'text', 'label' => 'Sur-titre', 't' => true],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Texte', 't' => true, 'rows' => 3],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image de fond'],
                    ['name' => 'overlay', 'type' => 'range', 'label' => 'Assombrissement de l\'image', 'min' => 0, 'max' => 90, 'step' => 5, 'suffix' => '%', 'default' => 55, 'width' => 'half'],
                    ['name' => 'height', 'type' => 'select', 'label' => 'Hauteur', 'width' => 'half', 'options' => [
                        'small' => 'Compacte', 'medium' => 'Moyenne', 'large' => 'Grande', 'full' => 'Plein écran',
                    ], 'default' => 'large'],
                    ['name' => 'align', 'type' => 'select', 'label' => 'Alignement', 'width' => 'half', 'options' => [
                        'left' => 'À gauche', 'center' => 'Centré',
                    ], 'default' => 'left'],
                    ['name' => 'buttons', 'type' => 'repeater', 'label' => 'Boutons', 'item_label' => 'label', 'max' => 3, 'fields' => $button],
                ],
            ],

            'page_header' => [
                'label' => 'En-tête de page',
                'description' => 'Titre de page sobre avec fil d\'Ariane, sur fond coloré ou image.',
                'group' => 'layout',
                'icon' => 'heading',
                'fields' => [
                    ['name' => 'eyebrow', 'type' => 'text', 'label' => 'Sur-titre', 't' => true],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true, 'help' => 'Laisser vide pour reprendre le titre de la page.'],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Texte', 't' => true, 'rows' => 2],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image de fond (facultative)'],
                    ['name' => 'overlay', 'type' => 'range', 'label' => 'Assombrissement de l\'image', 'min' => 0, 'max' => 90, 'step' => 5, 'suffix' => '%', 'default' => 60],
                ],
            ],

            'cta' => [
                'label' => 'Appel à l\'action',
                'description' => 'Bandeau incitatif avec un ou deux boutons.',
                'group' => 'layout',
                'icon' => 'megaphone',
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Texte', 't' => true, 'rows' => 2],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image de fond (facultative)'],
                    ['name' => 'buttons', 'type' => 'repeater', 'label' => 'Boutons', 'item_label' => 'label', 'max' => 2, 'fields' => $button],
                ],
                'settings' => ['background' => 'primary'],
            ],

            // ───────────── Contenu ─────────────
            'text' => [
                'label' => 'Texte',
                'description' => 'Titre et texte mis en forme (gras, listes, liens…).',
                'group' => 'content',
                'icon' => 'align-left',
                'fields' => [
                    ...$heading,
                    ['name' => 'content', 'type' => 'richtext', 'label' => 'Contenu', 't' => true],
                    ['name' => 'width', 'type' => 'select', 'label' => 'Largeur', 'width' => 'half', 'options' => [
                        'narrow' => 'Étroite (lecture)', 'normal' => 'Normale', 'wide' => 'Large',
                    ], 'default' => 'narrow'],
                    ['name' => 'align', 'type' => 'select', 'label' => 'Alignement', 'width' => 'half', 'options' => [
                        'left' => 'À gauche', 'center' => 'Centré',
                    ], 'default' => 'left'],
                    ['name' => 'buttons', 'type' => 'repeater', 'label' => 'Boutons', 'item_label' => 'label', 'max' => 2, 'fields' => $button],
                ],
            ],

            'text_image' => [
                'label' => 'Texte + image',
                'description' => 'Texte à côté d\'une image, à gauche ou à droite.',
                'group' => 'content',
                'icon' => 'layout-panel-left',
                'fields' => [
                    ['name' => 'eyebrow', 'type' => 'text', 'label' => 'Sur-titre', 't' => true],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true],
                    ['name' => 'content', 'type' => 'richtext', 'label' => 'Contenu', 't' => true],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ['name' => 'image_position', 'type' => 'select', 'label' => 'Position de l\'image', 'width' => 'half', 'options' => [
                        'right' => 'À droite', 'left' => 'À gauche',
                    ], 'default' => 'right'],
                    ['name' => 'image_style', 'type' => 'select', 'label' => 'Style de l\'image', 'width' => 'half', 'options' => [
                        'rounded' => 'Coins arrondis', 'frame' => 'Avec cadre décalé', 'plain' => 'Simple',
                    ], 'default' => 'frame'],
                    ['name' => 'buttons', 'type' => 'repeater', 'label' => 'Boutons', 'item_label' => 'label', 'max' => 2, 'fields' => $button],
                ],
            ],

            'cards' => [
                'label' => 'Cartes / vitrines',
                'description' => 'Grille de cartes avec icône ou image, texte et lien.',
                'group' => 'content',
                'icon' => 'layout-grid',
                'fields' => [
                    ...$heading,
                    ['name' => 'columns', 'type' => 'select', 'label' => 'Colonnes', 'width' => 'half', 'options' => ['2' => '2', '3' => '3', '4' => '4'], 'default' => '3'],
                    ['name' => 'style', 'type' => 'select', 'label' => 'Style des cartes', 'width' => 'half', 'options' => [
                        'elevated' => 'Carte blanche avec ombre', 'bordered' => 'Bordure fine', 'accent' => 'Bandeau de couleur', 'numbered' => 'Numérotées',
                    ], 'default' => 'elevated'],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Cartes', 'item_label' => 'title', 'fields' => [
                        ['name' => 'icon', 'type' => 'icon', 'label' => 'Icône', 'width' => 'half'],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image (remplace l\'icône)', 'width' => 'half'],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Texte', 't' => true, 'rows' => 3],
                        ['name' => 'link_label', 'type' => 'text', 'label' => 'Texte du lien', 't' => true, 'width' => 'half'],
                        ['name' => 'url', 'type' => 'link', 'label' => 'Lien', 'width' => 'half'],
                    ]],
                ],
            ],

            'steps' => [
                'label' => 'Étapes / démarche',
                'description' => 'Méthodologie en étapes numérotées.',
                'group' => 'content',
                'icon' => 'list-ordered',
                'fields' => [
                    ...$heading,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Disposition', 'options' => [
                        'timeline' => 'Frise verticale', 'columns' => 'Colonnes',
                    ], 'default' => 'columns'],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Étapes', 'item_label' => 'title', 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Texte', 't' => true, 'rows' => 3],
                    ]],
                ],
            ],

            'checklist' => [
                'label' => 'Liste à puces',
                'description' => 'Bénéfices, résultats attendus, publics cibles…',
                'group' => 'content',
                'icon' => 'list-checks',
                'fields' => [
                    ...$heading,
                    ['name' => 'columns', 'type' => 'select', 'label' => 'Colonnes', 'width' => 'half', 'options' => ['1' => '1', '2' => '2', '3' => '3'], 'default' => '2'],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Éléments', 'item_label' => 'text', 'fields' => [
                        ['name' => 'text', 'type' => 'text', 'label' => 'Texte', 't' => true],
                    ]],
                ],
            ],

            'stats' => [
                'label' => 'Chiffres clés',
                'description' => 'Quelques chiffres mis en valeur.',
                'group' => 'content',
                'icon' => 'bar-chart-3',
                'fields' => [
                    ...$heading,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Chiffres', 'item_label' => 'label', 'max' => 6, 'fields' => [
                        ['name' => 'value', 'type' => 'text', 'label' => 'Valeur', 'width' => 'half', 'help' => 'Ex. : 15+, 250, 98 %'],
                        ['name' => 'label', 'type' => 'text', 'label' => 'Libellé', 't' => true, 'width' => 'half'],
                    ]],
                ],
                'settings' => ['background' => 'dark'],
            ],

            'quote' => [
                'label' => 'Citation / signature',
                'description' => 'Phrase forte mise en avant (ex. « Révéler. Aligner. Transformer. »).',
                'group' => 'content',
                'icon' => 'quote',
                'fields' => [
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Citation', 't' => true, 'rows' => 3, 'required' => true],
                    ['name' => 'author', 'type' => 'text', 'label' => 'Auteur (facultatif)', 't' => true],
                ],
                'settings' => ['background' => 'light'],
            ],

            'faq' => [
                'label' => 'Questions fréquentes',
                'description' => 'Questions / réponses dépliables.',
                'group' => 'content',
                'icon' => 'help-circle',
                'fields' => [
                    ...$heading,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Questions', 'item_label' => 'question', 'fields' => [
                        ['name' => 'question', 'type' => 'text', 'label' => 'Question', 't' => true],
                        ['name' => 'answer', 'type' => 'richtext', 'label' => 'Réponse', 't' => true, 'compact' => true],
                    ]],
                ],
            ],

            'gallery' => [
                'label' => 'Galerie d\'images',
                'description' => 'Grille de photos avec légendes.',
                'group' => 'content',
                'icon' => 'images',
                'fields' => [
                    ...$heading,
                    ['name' => 'columns', 'type' => 'select', 'label' => 'Colonnes', 'options' => ['2' => '2', '3' => '3', '4' => '4'], 'default' => '3'],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Images', 'item_label' => 'caption', 'fields' => [
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                        ['name' => 'caption', 'type' => 'text', 'label' => 'Légende', 't' => true],
                    ]],
                ],
            ],

            'video' => [
                'label' => 'Vidéo',
                'description' => 'Vidéo YouTube ou Vimeo intégrée.',
                'group' => 'content',
                'icon' => 'play-circle',
                'fields' => [
                    ...$heading,
                    ['name' => 'url', 'type' => 'text', 'label' => 'Adresse de la vidéo (YouTube / Vimeo)', 'required' => true],
                ],
            ],

            // ───────────── Contenus du site (alimentés automatiquement) ─────────────
            'solutions' => [
                'label' => 'Nos solutions',
                'description' => 'Pôles, catégories ou offres, mis à jour automatiquement.',
                'group' => 'dynamic',
                'icon' => 'briefcase',
                'fields' => [
                    ...$heading,
                    ['name' => 'source', 'type' => 'select', 'label' => 'Afficher', 'options' => [
                        'poles' => 'Les pôles (ELC Advisory, ELC Institute)',
                        'categories' => 'Les catégories d\'un pôle',
                        'solutions' => 'Les offres d\'une catégorie ou d\'un pôle',
                        'featured' => 'Les offres mises en avant',
                    ], 'default' => 'poles'],
                    ['name' => 'category_id', 'type' => 'select', 'label' => 'Pôle / catégorie', 'options_source' => 'solution_categories', 'help' => 'Utilisé pour « catégories d\'un pôle » et « offres d\'une catégorie ».'],
                    ['name' => 'columns', 'type' => 'select', 'label' => 'Colonnes', 'width' => 'half', 'options' => ['2' => '2', '3' => '3', '4' => '4'], 'default' => '3'],
                    ['name' => 'link_label', 'type' => 'text', 'label' => 'Texte du lien des cartes', 't' => true, 'width' => 'half', 'default' => ['fr' => 'En savoir plus', 'en' => 'Learn more']],
                ],
            ],

            'case_studies' => [
                'label' => 'Nos réalisations',
                'description' => 'Les dernières réalisations publiées.',
                'group' => 'dynamic',
                'icon' => 'award',
                'fields' => [
                    ...$heading,
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Nombre maximum', 'width' => 'half', 'default' => 6, 'help' => '0 = toutes'],
                    ['name' => 'button', 'type' => 'repeater', 'label' => 'Bouton « voir tout » (facultatif)', 'item_label' => 'label', 'max' => 1, 'fields' => $button],
                ],
            ],

            'posts' => [
                'label' => 'Publications (Insights)',
                'description' => 'Articles, newsletters et Action Research.',
                'group' => 'dynamic',
                'icon' => 'newspaper',
                'fields' => [
                    ...$heading,
                    ['name' => 'type', 'type' => 'select', 'label' => 'Type', 'width' => 'half', 'options' => [
                        '' => 'Tous', 'article' => 'Articles', 'newsletter' => 'Newsletters', 'research' => 'Action Research',
                    ], 'default' => ''],
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Nombre par page', 'width' => 'half', 'default' => 3],
                    ['name' => 'paginate', 'type' => 'toggle', 'label' => 'Afficher la pagination', 'default' => false],
                    ['name' => 'button', 'type' => 'repeater', 'label' => 'Bouton « voir tout » (facultatif)', 'item_label' => 'label', 'max' => 1, 'fields' => $button],
                ],
            ],

            'testimonials' => [
                'label' => 'Témoignages',
                'description' => 'Témoignages clients publiés.',
                'group' => 'dynamic',
                'icon' => 'message-square-quote',
                'fields' => [
                    ...$heading,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Disposition', 'width' => 'half', 'options' => [
                        'slider' => 'Carrousel', 'grid' => 'Grille',
                    ], 'default' => 'slider'],
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Nombre maximum', 'width' => 'half', 'default' => 0, 'help' => '0 = tous'],
                ],
                'settings' => ['background' => 'light'],
            ],

            'references' => [
                'label' => 'Références clients',
                'description' => 'Logos des clients.',
                'group' => 'dynamic',
                'icon' => 'building-2',
                'fields' => [
                    ...$heading,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Disposition', 'width' => 'half', 'options' => [
                        'grid' => 'Grille', 'marquee' => 'Défilement continu',
                    ], 'default' => 'grid'],
                    ['name' => 'grayscale', 'type' => 'toggle', 'label' => 'Logos en gris (couleur au survol)', 'width' => 'half', 'default' => true],
                ],
            ],

            'jobs' => [
                'label' => 'Offres d\'emploi',
                'description' => 'Postes ouverts et candidature spontanée.',
                'group' => 'dynamic',
                'icon' => 'user-plus',
                'fields' => [
                    ...$heading,
                    ['name' => 'empty_text', 'type' => 'textarea', 'label' => 'Texte si aucune offre', 't' => true, 'rows' => 2,
                        'default' => ['fr' => 'Aucun poste n\'est ouvert pour le moment. Vous pouvez nous adresser une candidature spontanée.', 'en' => 'There are no open positions at the moment. You are welcome to send us an unsolicited application.']],
                    ['name' => 'spontaneous', 'type' => 'toggle', 'label' => 'Proposer une candidature spontanée', 'default' => true],
                ],
            ],

            // ───────────── Formulaires ─────────────
            'contact' => [
                'label' => 'Formulaire de contact',
                'description' => 'Formulaire + coordonnées et carte.',
                'group' => 'forms',
                'icon' => 'mail',
                'fields' => [
                    ...$heading,
                    ['name' => 'show_info', 'type' => 'toggle', 'label' => 'Afficher les coordonnées', 'width' => 'half', 'default' => true],
                    ['name' => 'show_map', 'type' => 'toggle', 'label' => 'Afficher la carte', 'width' => 'half', 'default' => true],
                    ['name' => 'subjects', 'type' => 'repeater', 'label' => 'Objets proposés (liste déroulante)', 'item_label' => 'label', 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Objet', 't' => true],
                    ]],
                ],
            ],

            'newsletter' => [
                'label' => 'Inscription newsletter',
                'description' => 'Champ e-mail pour recevoir les publications.',
                'group' => 'forms',
                'icon' => 'send',
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Texte', 't' => true, 'rows' => 2],
                ],
                'settings' => ['background' => 'primary'],
            ],
        ];
    }

    /** Réglages communs à tous les blocs (onglet « Apparence »). */
    public static function settingsFields(): array
    {
        return [
            ['name' => 'background', 'type' => 'select', 'label' => 'Fond', 'width' => 'half', 'options' => [
                'white' => 'Blanc', 'light' => 'Gris clair', 'tint' => 'Teinte de la couleur principale', 'primary' => 'Couleur principale', 'dark' => 'Foncé',
            ], 'default' => 'white'],
            ['name' => 'spacing', 'type' => 'select', 'label' => 'Espacement vertical', 'width' => 'half', 'options' => [
                'none' => 'Aucun', 'small' => 'Petit', 'normal' => 'Normal', 'large' => 'Grand',
            ], 'default' => 'normal'],
            ['name' => 'anchor', 'type' => 'text', 'label' => 'Ancre (pour un lien #ancre)', 'width' => 'half', 'help' => 'Ex. : contact → lien « #contact »'],
        ];
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, static::all());
    }

    /** Définition envoyée à l'admin, avec les options dynamiques résolues. */
    public static function forAdmin(): array
    {
        return collect(static::all())->map(function (array $block, string $type) {
            $block['type'] = $type;
            $block['fields'] = Fields::resolveOptions($block['fields']);
            $block['settings'] = array_merge(
                collect(static::settingsFields())->mapWithKeys(fn ($f) => [$f['name'] => $f['default'] ?? null])->all(),
                $block['settings'] ?? [],
            );

            return $block;
        })->values()->all();
    }
}
