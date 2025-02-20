<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyFloorPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'title',
        'description',
        'image',
        'price',
        'size'
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
