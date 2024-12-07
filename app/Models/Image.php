<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'original_name',
        'unique_name',
        'path',
        'order',
        'section',
        'size',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON serialization.
     *
     * @var string[]
     */
    protected $hidden = [
        'url',
    ];

    /**
     * Get the owning imageable model (e.g., Vehicle).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        parent::boot();
    }
}
