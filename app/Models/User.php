<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use OpenApi\Annotations as OA;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;

/**
 * @OA\Schema(
 *     schema="User",
 *     required={"name", "email", "password", "role"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="role", type="string", enum={"user", "agent", "admin"}),
 *     @OA\Property(property="profile_image", type="string", format="url", nullable=true),
 *     @OA\Property(property="bio", type="string", nullable=true),
 *     @OA\Property(property="social_media_links", type="object", nullable=true),
 *     @OA\Property(property="agency_id", type="integer", nullable=true),
 *     @OA\Property(property="license_number", type="string", nullable=true),
 *     @OA\Property(property="experience_years", type="integer", nullable=true),
 *     @OA\Property(property="specialties", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRelationships;

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'profile_image',
        'bio',
        'social_media_links',
        'agency_id',
        'license_number',
        'experience_years',
        'specialties'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'social_media_links' => 'array',
        'specialties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the properties associated with the user.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the agency associated with the user.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the favorites associated with the user.
     */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'favorites')
            ->withTimestamps();
    }

    /**
     * Get the compares associated with the user.
     */
    public function compares(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'compares')
            ->withTimestamps();
    }

    /**
     * Get the reviews created by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope a query to only include agents.
     */
    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    /**
     * Get the subscriptions associated with the user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the subscription plan associated with the user.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
