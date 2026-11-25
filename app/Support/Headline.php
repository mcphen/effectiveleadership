<?php

namespace App\Support;

/**
 * Majuscule à chaque mot important d'un titre, à la manière des titres anglais :
 * « Un cabinet de conseil en leadership et management » → « Un Cabinet de Conseil en Leadership et Management ».
 *
 * Les petits mots (articles, prépositions, conjonctions) restent en minuscule, sauf en début de titre
 * ou après « : », « — », « – ». Les mots déjà en majuscules (sigles, marques) ne sont jamais modifiés.
 */
class Headline
{
    protected const SMALL_WORDS = [
        'fr' => ['à', 'au', 'aux', 'avec', 'chez', 'd', 'dans', 'de', 'des', 'du', 'en', 'et', 'l', 'la', 'le', 'les', 'ou', 'par', 'pour', 'sur', 'un', 'une', 'vers'],
        'en' => ['a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'from', 'in', 'into', 'nor', 'of', 'on', 'or', 'the', 'to', 'vs', 'with'],
    ];

    /** Mots élidés français suivis d'une apostrophe (d’, l’, qu’…). */
    protected const ELISIONS = ['c', 'd', 'j', 'l', 'm', 'n', 's', 't', 'qu', 'jusqu', 'lorsqu', 'puisqu'];

    public static function title(?string $text, string $locale): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        // Les petits mots des deux langues : les titres français contiennent des noms de marque anglais (« AXIOME™ by ELC »).
        $small = array_merge(...array_values(self::SMALL_WORDS));
        $startOfPhrase = true;
        $previousWord = '';
        $separator = '';

        // Découpe en conservant les séparateurs (espaces, retours à la ligne, tirets, apostrophes).
        $parts = preg_split("/([\s\-‑–—:’'«»\"()\/]+)/u", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => $part) {
            if ($i % 2 === 1) {
                // Séparateur : une nouvelle phrase commence après « : », un tiret long ou un retour à la ligne.
                if (preg_match('/[:—–\n]/u', $part)) {
                    $startOfPhrase = true;
                }
                $separator = $part;

                continue;
            }
            if ($part === '') {
                continue;
            }

            // Après une apostrophe, seule l'élision française (d’, l’, qu’…) annonce un nouveau mot :
            // « d’impact » → « d’Impact », mais « Let's » et « l’ONU » restent inchangés.
            $afterApostrophe = in_array($separator, ['’', "'"], true);
            $elision = in_array(mb_strtolower($previousWord), self::ELISIONS, true);
            $previousWord = $part;

            if ($afterApostrophe && ! $elision) {
                continue;
            }

            $lower = mb_strtolower($part);
            $isSmall = in_array($lower, $small, true) && $part === $lower;

            if ($startOfPhrase || ! $isSmall) {
                $parts[$i] = mb_strtoupper(mb_substr($part, 0, 1)).mb_substr($part, 1);
            }

            // Après une phrase terminée par un point, la ligne suivante recommence.
            $startOfPhrase = str_ends_with($part, '.') || str_ends_with($part, '?') || str_ends_with($part, '!');
        }

        return implode('', $parts);
    }

    /** Applique title() à chaque langue d'une valeur traduisible {"en": "...", "fr": "..."}. */
    public static function translations(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $locale => $text) {
            if (is_string($text)) {
                $value[$locale] = self::title($text, (string) $locale);
            }
        }

        return $value;
    }
}
