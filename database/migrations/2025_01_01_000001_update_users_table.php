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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->enum('role', ['admin', 'agent', 'user'])->default('user');
            $table->string('profile_image')->nullable();
            $table->text('bio')->nullable();
            $table->json('social_media_links')->nullable();
            $table->foreignId('agency_id')->nullable()->constrained()->onDelete('set null');
            $table->string('license_number')->nullable();
            $table->integer('experience_years')->nullable();
            $table->json('specialties')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone',
                'role',
                'profile_image',
                'bio',
                'social_media_links',
                'agency_id',
                'license_number',
                'experience_years',
                'specialties'
            ]);
        });
    }
};
