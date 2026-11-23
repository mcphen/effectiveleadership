<?php

namespace App\Cms;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Nettoyage du HTML saisi dans l'éditeur de texte riche : seules les balises
 * et attributs de mise en forme sont conservés (pas de script, style libre, iframe…).
 */
class Html
{
    /** balise => attributs autorisés */
    protected const ALLOWED = [
        'p' => ['style'], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'a' => ['href', 'target', 'rel'], 'ul' => [], 'ol' => ['start'], 'li' => [],
        'h2' => ['style'], 'h3' => ['style'], 'h4' => ['style'], 'blockquote' => [], 'hr' => [], 'code' => [], 'mark' => [],
    ];

    public static function clean(string $html): string
    {
        $html = trim($html);

        if ($html === '' || $html === '<p></p>') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('__root') ?? $doc->documentElement;
        static::walk($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    protected static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'], true)) {
                $node->removeChild($child);

                continue;
            }

            static::walk($child);

            if (! array_key_exists($tag, self::ALLOWED)) {
                // Balise non autorisée : on garde son contenu.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                $value = $attr->value;

                $keep = in_array($name, self::ALLOWED[$tag], true) && match ($name) {
                    'href' => (bool) preg_match('#^(https?://|mailto:|tel:|/|\#|(page|solution|category|case|post|job):\d+$)#i', trim($value)),
                    'target' => $value === '_blank',
                    'style' => (bool) preg_match('/^\s*text-align:\s*(left|center|right|justify);?\s*$/i', $value),
                    default => true,
                };

                if (! $keep) {
                    $child->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener');
            }
        }
    }

    /** Remplace les liens internes « page:12 » par leur URL réelle dans la langue courante. */
    public static function render(?string $html): string
    {
        if (! $html) {
            return '';
        }

        return preg_replace_callback(
            '/href="((?:page|solution|category|case|post|job):\d+)"/',
            fn ($m) => 'href="'.e(resolve_link($m[1])).'"',
            $html,
        );
    }
}
