<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *     schema="Property",
 *     required={"title", "description", "price", "area", "address", "city_id", "property_type_id", "property_status_id"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="title", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="property_type_id", type="integer"),
 *     @OA\Property(property="property_status_id", type="integer"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="area", type="number", format="float"),
 *     @OA\Property(property="bedrooms", type="integer", nullable=true),
 *     @OA\Property(property="bathrooms", type="integer", nullable=true),
 *     @OA\Property(property="garages", type="integer", nullable=true),
 *     @OA\Property(property="year_built", type="integer", nullable=true),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true),
 *     @OA\Property(property="published_status", type="string", enum={"draft", "published"}),
 *     @OA\Property(property="city_id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="featured_image", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="amenities",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Amenity")
 *     ),
 *     @OA\Property(
 *         property="features",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Feature")
 *     ),
 *     @OA\Property(property="property_type", ref="#/components/schemas/PropertyType"),
 *     @OA\Property(property="property_status", ref="#/components/schemas/PropertyStatus"),
 *     @OA\Property(property="city", ref="#/components/schemas/City"),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PropertyMedia")
 *     ),
 *     @OA\Property(
 *         property="floor_plans",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PropertyFloorPlan")
 *     )
 * )
 */
class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'property_type_id',
        'property_status_id',
        'price',
        'area',
        'bedrooms',
        'bathrooms',
        'garages',
        'year_built',
        'address',
        'latitude',
        'longitude',
        'features',
        'published_status',
        'user_id',
        'city_id',
        'featured_image'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function propertyStatus(): BelongsTo
    {
        return $this->belongsTo(PropertyStatus::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class);
    }

    public function floorPlans(): HasMany
    {
        return $this->hasMany(PropertyFloorPlan::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class)->with('user');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class)->with('user');
    }
}
