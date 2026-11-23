<?php

namespace App\Cms;

/**
 * Réglages globaux, organisés en onglets. Chaque onglet est enregistré
 * dans la table settings sous sa clé (identity, theme, contact…).
 */
class SettingsSchema
{
    public const HEADING_FONTS = [
        'Merriweather' => 'Merriweather (empattements, sobre)',
        'Libre Baskerville' => 'Libre Baskerville (empattements, classique)',
        'Playfair Display' => 'Playfair Display (empattements, élégant)',
        'Lora' => 'Lora (empattements, doux)',
        'Montserrat' => 'Montserrat (sans empattement, moderne)',
        'Poppins' => 'Poppins (sans empattement, arrondi)',
    ];

    public const BODY_FONTS = [
        'Source Sans 3' => 'Source Sans 3',
        'Inter' => 'Inter',
        'Open Sans' => 'Open Sans',
        'Lato' => 'Lato',
        'Nunito Sans' => 'Nunito Sans',
    ];

    public static function tabs(): array
    {
        return [
            'identity' => [
                'label' => 'Identité',
                'icon' => 'badge',
                'fields' => [
                    ['name' => 'site_name', 'type' => 'text', 'label' => 'Nom du site', 'default' => 'Effective Leadership Consulting'],
                    ['name' => 'tagline', 'type' => 'text', 'label' => 'Signature', 't' => true],
                    ['name' => 'logo', 'type' => 'image', 'label' => 'Logo (fond clair)', 'width' => 'half'],
                    ['name' => 'logo_light', 'type' => 'image', 'label' => 'Logo (fond foncé, facultatif)', 'width' => 'half', 'help' => 'Version blanche du logo pour le pied de page.'],
                    ['name' => 'favicon', 'type' => 'image', 'label' => 'Icône d\'onglet (favicon)', 'width' => 'half', 'help' => 'Image carrée, 512×512 px conseillé.'],
                    ['name' => 'logo_height', 'type' => 'range', 'label' => 'Hauteur du logo dans l\'en-tête', 'min' => 32, 'max' => 90, 'step' => 2, 'suffix' => 'px', 'default' => 52, 'width' => 'half'],
                ],
            ],
            'theme' => [
                'label' => 'Couleurs & typographie',
                'icon' => 'palette',
                'fields' => [
                    ['name' => 'primary', 'type' => 'color', 'label' => 'Couleur principale', 'default' => '#156080', 'width' => 'half'],
                    ['name' => 'secondary', 'type' => 'color', 'label' => 'Couleur secondaire', 'default' => '#008080', 'width' => 'half'],
                    ['name' => 'dark', 'type' => 'color', 'label' => 'Couleur foncée (fonds sombres)', 'default' => '#0C2B3A', 'width' => 'half'],
                    ['name' => 'accent', 'type' => 'color', 'label' => 'Couleur d\'accent (détails)', 'default' => '#C9973F', 'width' => 'half'],
                    ['name' => 'heading_font', 'type' => 'select', 'label' => 'Police des titres', 'options' => self::HEADING_FONTS, 'default' => 'Merriweather', 'width' => 'half'],
                    ['name' => 'body_font', 'type' => 'select', 'label' => 'Police du texte', 'options' => self::BODY_FONTS, 'default' => 'Source Sans 3', 'width' => 'half'],
                    ['name' => 'radius', 'type' => 'select', 'label' => 'Arrondi des coins', 'options' => ['0' => 'Aucun', '4' => 'Léger', '10' => 'Moyen', '18' => 'Prononcé'], 'default' => '4', 'width' => 'half'],
                ],
            ],
            'header' => [
                'label' => 'En-tête',
                'icon' => 'panel-top',
                'fields' => [
                    ['name' => 'cta_label', 'type' => 'text', 'label' => 'Bouton de l\'en-tête – texte', 't' => true, 'width' => 'half'],
                    ['name' => 'cta_link', 'type' => 'link', 'label' => 'Bouton de l\'en-tête – lien', 'width' => 'half'],
                    ['name' => 'topbar', 'type' => 'toggle', 'label' => 'Afficher la barre de contact au-dessus du menu', 'default' => true],
                ],
            ],
            'contact' => [
                'label' => 'Coordonnées',
                'icon' => 'map-pin',
                'fields' => [
                    ['name' => 'address', 'type' => 'textarea', 'label' => 'Adresse', 't' => true, 'rows' => 2],
                    ['name' => 'email', 'type' => 'text', 'label' => 'E-mail', 'width' => 'half'],
                    ['name' => 'phones', 'type' => 'repeater', 'label' => 'Téléphones', 'item_label' => 'number', 'fields' => [
                        ['name' => 'number', 'type' => 'text', 'label' => 'Numéro'],
                    ]],
                    ['name' => 'hours', 'type' => 'text', 'label' => 'Horaires', 't' => true],
                    ['name' => 'map_query', 'type' => 'text', 'label' => 'Adresse pour la carte Google Maps', 'help' => 'Ex. : Liberté 6 Extension, Dakar, Sénégal'],
                    ['name' => 'notify_email', 'type' => 'text', 'label' => 'E-mail qui reçoit les messages du site', 'help' => 'Laisser vide pour utiliser l\'e-mail ci-dessus.'],
                ],
            ],
            'socials' => [
                'label' => 'Réseaux sociaux',
                'icon' => 'share-2',
                'fields' => [
                    ['name' => 'linkedin', 'type' => 'text', 'label' => 'LinkedIn (URL)'],
                    ['name' => 'facebook', 'type' => 'text', 'label' => 'Facebook (URL)'],
                    ['name' => 'instagram', 'type' => 'text', 'label' => 'Instagram (URL)'],
                    ['name' => 'youtube', 'type' => 'text', 'label' => 'YouTube (URL)'],
                    ['name' => 'twitter', 'type' => 'text', 'label' => 'X / Twitter (URL)'],
                ],
            ],
            'footer' => [
                'label' => 'Pied de page',
                'icon' => 'panel-bottom',
                'fields' => [
                    ['name' => 'about', 'type' => 'textarea', 'label' => 'Texte de présentation', 't' => true, 'rows' => 3],
                    ['name' => 'newsletter', 'type' => 'toggle', 'label' => 'Afficher l\'inscription à la newsletter', 'default' => true],
                    ['name' => 'newsletter_title', 'type' => 'text', 'label' => 'Titre de la newsletter', 't' => true],
                    ['name' => 'copyright', 'type' => 'text', 'label' => 'Mention de copyright', 't' => true, 'help' => '{year} est remplacé par l\'année en cours.'],
                ],
            ],
            'seo' => [
                'label' => 'Référencement',
                'icon' => 'search',
                'fields' => [
                    ['name' => 'title_suffix', 'type' => 'text', 'label' => 'Suffixe des titres de page', 'default' => ' | ELC'],
                    ['name' => 'description', 'type' => 'textarea', 'label' => 'Description par défaut', 't' => true, 'rows' => 3],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image de partage par défaut (1200×630)'],
                    ['name' => 'analytics', 'type' => 'text', 'label' => 'Identifiant Google Analytics (G-XXXX)'],
                ],
            ],
        ];
    }
}
