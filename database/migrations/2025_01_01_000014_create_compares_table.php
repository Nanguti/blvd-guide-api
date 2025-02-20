<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can't add the same property to compare multiple times
            $table->unique(['user_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compares');
    }
};
