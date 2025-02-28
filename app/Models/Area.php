<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Area",
 *     required={"city_id", "name"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="city_id", type="integer"),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="city",
 *         ref="#/components/schemas/City"
 *     ),
 *     @OA\Property(
 *         property="properties",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Property")
 *     )
 * )
 */
class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'description'
    ];

    /**
     * Get the city that owns the area.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the properties in this area.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
