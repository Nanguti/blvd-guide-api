<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Site Information
            'site_name' => $this->site_name,
            'site_title' => $this->site_title,
            'site_description' => $this->site_description,
            'site_logo' => $this->site_logo,
            'site_favicon' => $this->site_favicon,

            // Contact Information
            'company_name' => $this->company_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'office_address' => $this->office_address,

            // Social Media
            'facebook_url' => $this->facebook_url,
            'twitter_url' => $this->twitter_url,
            'instagram_url' => $this->instagram_url,
            'linkedin_url' => $this->linkedin_url,

            // Real Estate Specific
            'currency_symbol' => $this->currency_symbol,
            'measurement_unit' => $this->measurement_unit,
            'properties_per_page' => $this->properties_per_page,
            'show_featured_properties' => $this->show_featured_properties,
            'enable_map' => $this->enable_map,
            'default_latitude' => $this->default_latitude,
            'default_longitude' => $this->default_longitude,

            // SEO Settings
            'google_analytics_id' => $this->google_analytics_id,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,

            // Email Settings
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_username' => $this->smtp_username,
            'mail_encryption' => $this->mail_encryption,
            'mail_from_address' => $this->mail_from_address,
            'mail_from_name' => $this->mail_from_name,

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
