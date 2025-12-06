<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Mencegah duplikasi (User tidak bisa like hewan yang sama 2x)
            $table->unique(['user_id', 'pet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};