<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Agent",
 *     required={"user_id", "agency_id", "license_number", "experience_years"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="agency_id", type="integer"),
 *     @OA\Property(property="license_number", type="string"),
 *     @OA\Property(property="experience_years", type="integer"),
 *     @OA\Property(property="specialties", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="bio", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'agency_id',
        'license_number',
        'experience_years',
        'specialties',
        'bio'
    ];

    protected $casts = [
        'specialties' => 'array',
        'experience_years' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
