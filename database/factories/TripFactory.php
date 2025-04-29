<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\Bus;
use App\Models\Path;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled', 'delayed'];

        // Generate random departure and arrival times
        $departureTime = $this->faker->dateTimeBetween('-1 week', '+1 month');
        $travelHours = $this->faker->numberBetween(1, 12);
        $arrivalTime = clone $departureTime;
        $arrivalTime->modify("+{$travelHours} hours");

        // Create a unique trip code
        $tripCode = 'TR-' . strtoupper($this->faker->bothify('??####'));

        $status = $this->faker->randomElement($statuses);

        return [
            'trip_code' => $tripCode,
            'bus_id' => Bus::factory(),
            'path_id' => Path::factory(), // Add path_id to the factory
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'price' => $this->faker->randomFloat(2, 20, 200),
            'available_seats' => $this->faker->numberBetween(10, 50),
            'booked_seats' => $this->faker->numberBetween(0, 10),
            'status' => $status,
            'distance' => $this->faker->numberBetween(50, 1000),
            'estimated_duration' => $travelHours * 60, // In minutes
            'fuel_consumption' => $this->faker->randomFloat(2, 10, 100),
            'delay_reason' => $status === 'delayed' ? $this->faker->sentence() : null,
            'cancellation_reason' => $status === 'cancelled' ? $this->faker->sentence() : null,
            'notes' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
        ];
    }

    /**
     * Set bus ID for this trip.
     */
    public function forBus(Bus $bus): self
    {
        return $this->state(function (array $attributes) use ($bus) {
            // Set available seats based on bus capacity
            $availableSeats = $bus->capacity;
            $bookedSeats = min($this->faker->numberBetween(0, $bus->capacity), $availableSeats);

            return [
                'bus_id' => $bus->id,
                'available_seats' => $availableSeats - $bookedSeats,
                'booked_seats' => $bookedSeats,
            ];
        });
    }

    /**
     * Set path ID for this trip.
     */
    public function forPath(Path $path): self
    {
        return $this->state(function (array $attributes) use ($path) {
            // Update distance and duration from path if available
            $distance = $path->total_distance ?? $attributes['distance'];
            $estimatedDuration = $path->total_duration ?? $attributes['estimated_duration'];

            return [
                'path_id' => $path->id,
                'distance' => $distance,
                'estimated_duration' => $estimatedDuration,
            ];
        });
    }

    /**
     * Create a scheduled trip.
     */
    public function scheduled(): self
    {
        return $this->state(function (array $attributes) {
            // Always in the future
            $departureTime = $this->faker->dateTimeBetween('+1 day', '+1 month');
            $travelHours = $this->faker->numberBetween(1, 12);
            $arrivalTime = clone $departureTime;
            $arrivalTime->modify("+{$travelHours} hours");

            return [
                'status' => 'scheduled',
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
                'delay_reason' => null,
                'cancellation_reason' => null,
            ];
        });
    }

    /**
     * Create a completed trip.
     */
    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            // Always in the past
            $departureTime = $this->faker->dateTimeBetween('-1 month', '-1 day');
            $travelHours = $this->faker->numberBetween(1, 12);
            $arrivalTime = clone $departureTime;
            $arrivalTime->modify("+{$travelHours} hours");

            return [
                'status' => 'completed',
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
                'delay_reason' => null,
                'cancellation_reason' => null,
            ];
        });
    }

    /**
     * Create a cancelled trip.
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'cancellation_reason' => $this->faker->sentence(),
                'delay_reason' => null,
            ];
        });
    }

    /**
     * Create a delayed trip.
     */
    public function delayed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'delayed',
                'delay_reason' => $this->faker->sentence(),
                'cancellation_reason' => null,
            ];
        });
    }
}
