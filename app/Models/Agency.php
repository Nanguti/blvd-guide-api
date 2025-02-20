<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'social_media_links'
    ];

    protected $casts = [
        'social_media_links' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the agents associated with the agency.
     */
    public function agents(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'agent');
    }
}
