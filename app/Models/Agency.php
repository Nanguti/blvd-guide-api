<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Agency",
 *     required={"name", "address", "phone", "email"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="logo", type="string", format="url", nullable=true),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true),
 *     @OA\Property(property="social_media_links", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
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
