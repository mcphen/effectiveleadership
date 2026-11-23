<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Media extends Model
{
    use HasTranslations;

    protected $fillable = ['disk', 'path', 'filename', 'mime', 'size', 'width', 'height', 'alt'];

    public array $translatable = ['alt'];

    protected $appends = ['url', 'is_image'];

    public function getUrlAttribute(): string
    {
        return media_url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    protected static function booted(): void
    {
        static::deleting(fn (Media $media) => Storage::disk($media->disk)->delete($media->path));
    }
}
