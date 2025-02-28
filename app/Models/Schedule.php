<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Schedule",
 *     required={"property_id", "user_id", "date", "time"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="property_id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="date", type="string", format="date"),
 *     @OA\Property(property="time", type="string", format="date-time"),
 *     @OA\Property(property="message", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected", "completed", "cancelled"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(
 *         property="property",
 *         ref="#/components/schemas/Property"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User"
 *     )
 * )
 */
class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'user_id',
        'date',
        'time',
        'message',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the property being scheduled for viewing.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user who scheduled the viewing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
