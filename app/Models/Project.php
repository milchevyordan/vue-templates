<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Warehouse;
use App\Traits\HasCreator;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasCreator;
    use HasChangeLogs;
    use SoftDeletes;
    use HasImages;


    /**
     * Profile image section from all images related to the user.
     *
     * @var string
     */
    public string $profileImageSection = 'profileImages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'warehouse',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'warehouse' => Warehouse::class,
    ];

    /**
     * Relation to the products added to the project.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_projects')->withPivot('quantity', 'creator_id')->withTimestamps();
    }
}
