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
        Schema::create('path_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('paths')->onDelete('cascade');
            $table->foreignId('destination_id')->constrained('destinations')->onDelete('cascade');
            $table->integer('stop_order');
            $table->time('estimated_arrival_time')->nullable();
            $table->time('estimated_departure_time')->nullable();
            $table->integer('stop_duration')->nullable(); // Duration in minutes
            $table->decimal('distance_from_previous', 10, 2)->nullable(); // Distance from previous stop in km
            $table->integer('time_from_previous')->nullable(); // Time from previous stop in minutes
            $table->text('stop_notes')->nullable();
            $table->boolean('is_pickup_point')->default(true);
            $table->boolean('is_dropoff_point')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('path_stops');
    }
};
