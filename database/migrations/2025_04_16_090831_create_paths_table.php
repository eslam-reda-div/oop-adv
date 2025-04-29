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
        Schema::create('paths', function (Blueprint $table) {
            $table->id();
            // Removed trip_id as paths will now be independent entities
            $table->string('name')->nullable(); // Added name for path identity
            $table->foreignId('start_destination_id')->constrained('destinations')->onDelete('cascade');
            $table->foreignId('end_destination_id')->constrained('destinations')->onDelete('cascade');
            $table->decimal('total_distance', 10, 2)->nullable(); // Total distance in kilometers
            $table->integer('total_duration')->nullable(); // Expected duration in minutes
            $table->integer('number_of_stops')->default(0);
            $table->text('route_description')->nullable();
            $table->string('route_map_url')->nullable(); // URL to static map image
            $table->text('directions_json')->nullable(); // Can store detailed route information in JSON
            $table->string('path_code')->unique()->nullable(); // Unique identifier for the path
            $table->boolean('is_circular')->default(false); // Whether the path returns to starting point
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paths');
    }
};
