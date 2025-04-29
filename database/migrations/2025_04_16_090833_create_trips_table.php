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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->onDelete('cascade');
            $table->foreignId('path_id')->constrained('paths')->onDelete('cascade'); // Added path_id foreign key
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->decimal('price', 8, 2);
            $table->string('trip_code')->unique()->nullable(); // Unique identifier for the trip
            $table->integer('available_seats')->nullable(); // Track available seats
            $table->integer('booked_seats')->default(0); // Track booked seats
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'delayed'])->default('scheduled');
            $table->text('delay_reason')->nullable(); // Reason if trip is delayed
            $table->text('cancellation_reason')->nullable(); // Reason if trip is cancelled
            $table->decimal('distance', 10, 2)->nullable(); // Distance in kilometers
            $table->integer('estimated_duration')->nullable(); // Duration in minutes
            $table->decimal('fuel_consumption', 8, 2)->nullable(); // Estimated fuel consumption
            $table->text('notes')->nullable(); // Any additional notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
