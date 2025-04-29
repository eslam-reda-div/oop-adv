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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('bus_number')->unique();
            $table->integer('capacity')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->year('year_of_manufacture')->nullable();
            $table->string('license_plate')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['active', 'maintenance', 'out_of_service'])->default('active');
            $table->string('fuel_type')->nullable();
            $table->decimal('fuel_efficiency', 8, 2)->nullable(); // km/liter or miles/gallon
            $table->text('features')->nullable(); // Can store JSON or comma-separated list
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // company id
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
