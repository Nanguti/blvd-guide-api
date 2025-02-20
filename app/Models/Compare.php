<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compare extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id'
    ];

    /**
     * Get the user who added the property to compare.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property being compared.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
