<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PropertyFloorPlan",
 *     required={"property_id", "title", "image"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="property_id", type="integer"),
 *     @OA\Property(property="title", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="image", type="string", format="url"),
 *     @OA\Property(property="price", type="number", format="float", nullable=true),
 *     @OA\Property(property="size", type="number", format="float", nullable=true),
 *     @OA\Property(property="bathrooms", type="integer", nullable=true),
 *     @OA\Property(property="bedrooms", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PropertyFloorPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'title',
        'description',
        'image',
        'price',
        'size',
        'bathrooms',
        'bedrooms',


    ];

    protected $casts = [
        'price' => 'decimal:2',
        'size' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the property that owns the floor plan.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
