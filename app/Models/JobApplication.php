<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = ['job_offer_id', 'name', 'email', 'phone', 'message', 'cv_path', 'locale', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }
}
