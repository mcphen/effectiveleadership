<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model
{
    use HasTranslations, Publishable;

    protected $fillable = ['author', 'role', 'company', 'quote', 'photo', 'is_published', 'sort'];

    public array $translatable = ['role', 'quote'];

    protected $casts = ['is_published' => 'boolean'];
}
