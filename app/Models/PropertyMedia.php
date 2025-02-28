<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PropertyMedia",
 *     required={"property_id", "type", "url"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="property_id", type="integer"),
 *     @OA\Property(property="type", type="string", enum={"image", "video", "virtual-tour"}),
 *     @OA\Property(property="url", type="string", format="url"),
 *     @OA\Property(property="sort_order", type="integer", nullable=true),
 *     @OA\Property(property="is_featured", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PropertyMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'type',
        'url',
        'sort_order',
        'is_featured'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the property that owns the media.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
