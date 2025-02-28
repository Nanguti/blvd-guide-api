<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Site Information
            $table->string('site_name');
            $table->string('site_title');
            $table->text('site_description')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('site_favicon')->nullable();

            // Contact Information
            $table->string('company_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('office_address');

            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();

            // Real Estate Specific
            $table->string('currency_symbol')->default('$');
            $table->string('measurement_unit')->default('sq ft');
            $table->integer('properties_per_page')->default(12);
            $table->boolean('show_featured_properties')->default(true);
            $table->boolean('enable_map')->default(true);
            $table->string('default_latitude')->nullable();
            $table->string('default_longitude')->nullable();

            // SEO Settings
            $table->string('google_analytics_id')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            // Email Settings
            $table->string('smtp_host')->nullable();
            $table->string('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
