<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Menu de navigation. « items » est un arbre JSON :
 * [{ "label": {"fr","en"}, "link": "page:3" | "https://…", "new_tab": false, "children": [...] }]
 */
class Menu extends Model
{
    protected $fillable = ['location', 'name', 'items'];

    protected $casts = ['items' => 'array'];

    public const LOCATIONS = [
        'header' => 'Menu principal',
        'footer' => 'Pied de page – liens',
        'footer_legal' => 'Pied de page – mentions',
    ];

    public static function at(string $location): array
    {
        return static::query()->where('location', $location)->first()?->items ?? [];
    }
}
