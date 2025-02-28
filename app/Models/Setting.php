<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Setting",
 *     required={"site_name", "site_title", "company_name", "contact_email", "contact_phone", "office_address"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="site_name", type="string", example="Houzez Real Estate"),
 *     @OA\Property(property="site_title", type="string", example="Houzez - Real Estate Platform"),
 *     @OA\Property(property="site_description", type="string", nullable=true),
 *     @OA\Property(property="site_logo", type="string", format="url", nullable=true),
 *     @OA\Property(property="site_favicon", type="string", format="url", nullable=true),
 *     @OA\Property(property="company_name", type="string"),
 *     @OA\Property(property="contact_email", type="string", format="email"),
 *     @OA\Property(property="contact_phone", type="string"),
 *     @OA\Property(property="office_address", type="string"),
 *     @OA\Property(property="facebook_url", type="string", format="url", nullable=true),
 *     @OA\Property(property="twitter_url", type="string", format="url", nullable=true),
 *     @OA\Property(property="instagram_url", type="string", format="url", nullable=true),
 *     @OA\Property(property="linkedin_url", type="string", format="url", nullable=true),
 *     @OA\Property(property="currency_symbol", type="string", example="$"),
 *     @OA\Property(property="measurement_unit", type="string", example="sq ft"),
 *     @OA\Property(property="properties_per_page", type="integer", example=12),
 *     @OA\Property(property="show_featured_properties", type="boolean"),
 *     @OA\Property(property="enable_map", type="boolean"),
 *     @OA\Property(property="default_latitude", type="string", nullable=true),
 *     @OA\Property(property="default_longitude", type="string", nullable=true),
 *     @OA\Property(property="google_analytics_id", type="string", nullable=true),
 *     @OA\Property(property="meta_keywords", type="string", nullable=true),
 *     @OA\Property(property="meta_description", type="string", nullable=true),
 *     @OA\Property(property="smtp_host", type="string", nullable=true),
 *     @OA\Property(property="smtp_port", type="string", nullable=true),
 *     @OA\Property(property="smtp_username", type="string", nullable=true),
 *     @OA\Property(property="mail_encryption", type="string", nullable=true),
 *     @OA\Property(property="mail_from_address", type="string", format="email", nullable=true),
 *     @OA\Property(property="mail_from_name", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 */
class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        // Site Information
        'site_name',
        'site_title',
        'site_description',
        'site_logo',
        'site_favicon',

        // Contact Information
        'company_name',
        'contact_email',
        'contact_phone',
        'office_address',

        // Social Media
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',

        // Real Estate Specific
        'currency_symbol',
        'measurement_unit',
        'properties_per_page',
        'show_featured_properties',
        'enable_map',
        'default_latitude',
        'default_longitude',

        // SEO Settings
        'google_analytics_id',
        'meta_keywords',
        'meta_description',

        // Email Settings
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_featured_properties' => 'boolean',
            'enable_map' => 'boolean',
            'properties_per_page' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the settings as a key-value array
     *
     * @return array<string, mixed>
     */
    public static function getSettings(): array
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'site_name' => 'Houzez Real Estate',
                'site_title' => 'Houzez - Real Estate Platform',
                'company_name' => 'Houzez Real Estate',
                'contact_email' => 'info@example.com',
                'contact_phone' => '+1 234 567 8900',
                'office_address' => '123 Real Estate Street',
                'currency_symbol' => '$',
                'measurement_unit' => 'sq ft',
                'properties_per_page' => 12,
                'show_featured_properties' => true,
                'enable_map' => true,
            ]);
        }

        return $settings->toArray();
    }
}
