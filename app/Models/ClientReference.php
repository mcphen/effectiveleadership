<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/** Référence client (logo). */
class ClientReference extends Model
{
    use HasTranslations, Publishable;

    protected $fillable = ['name', 'logo', 'url', 'sector', 'is_published', 'sort'];

    public array $translatable = ['sector'];

    protected $casts = ['is_published' => 'boolean'];
}
