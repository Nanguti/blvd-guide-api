<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->hasMany(PropertyInquiry::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
