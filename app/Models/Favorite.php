<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Favorite",
 *     required={"user_id", "property_id"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="property_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="property",
 *         ref="#/components/schemas/Property"
 *     )
 * )
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id'
    ];

    /**
     * Get the user who favorited the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
