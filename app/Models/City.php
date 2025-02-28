<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="City",
 *     required={"state_id", "name"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="state_id", type="integer"),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="state",
 *         ref="#/components/schemas/State"
 *     ),
 *     @OA\Property(
 *         property="areas",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Area")
 *     )
 * )
 */
class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name'
    ];

    /**
     * Get the state that owns the city.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the areas/neighborhoods in the city.
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Get the properties in the city.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
