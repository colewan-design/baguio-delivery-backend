<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('osm_id');
            $table->string('osm_type', 10);
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamps();

            $table->unique(['osm_type', 'osm_id']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
