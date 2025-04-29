<?php

namespace Database\Factories;

use App\Models\PathStop;
use App\Models\Path;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

class PathStopFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PathStop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate random time strings for arrival and departure
        $arrivalHour = $this->faker->numberBetween(0, 23);
        $arrivalMinute = $this->faker->numberBetween(0, 59);
        $estimatedArrivalTime = sprintf('%02d:%02d', $arrivalHour, $arrivalMinute);
        
        $departureMinDiff = $this->faker->numberBetween(10, 45); // 10 to 45 min difference
        $departureTimestamp = mktime($arrivalHour, $arrivalMinute) + ($departureMinDiff * 60);
        $estimatedDepartureTime = date('H:i', $departureTimestamp);
        
        return [
            'path_id' => Path::factory(),
            'destination_id' => Destination::factory(),
            'stop_order' => $this->faker->numberBetween(1, 10),
            'estimated_arrival_time' => $estimatedArrivalTime,
            'estimated_departure_time' => $estimatedDepartureTime,
            'stop_duration' => $departureMinDiff,
            'distance_from_previous' => $this->faker->numberBetween(5, 100),
            'time_from_previous' => $this->faker->numberBetween(10, 120),
            'stop_notes' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
            'is_pickup_point' => $this->faker->boolean(80), // 80% chance to be pickup point
            'is_dropoff_point' => $this->faker->boolean(80), // 80% chance to be dropoff point
        ];
    }
    
    /**
     * Create stop for a specific path.
     */
    public function forPath(Path $path): self
    {
        return $this->state(function (array $attributes) use ($path) {
            return [
                'path_id' => $path->id,
            ];
        });
    }
    
    /**
     * Create stop with a specific destination.
     */
    public function atDestination(Destination $destination): self
    {
        return $this->state(function (array $attributes) use ($destination) {
            return [
                'destination_id' => $destination->id,
            ];
        });
    }
    
    /**
     * Create stop with a specific order.
     */
    public function withOrder(int $order): self
    {
        return $this->state(function (array $attributes) use ($order) {
            return [
                'stop_order' => $order,
            ];
        });
    }
    
    /**
     * Create stop as pickup and dropoff point.
     */
    public function asPickupAndDropoff(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_pickup_point' => true,
                'is_dropoff_point' => true,
            ];
        });
    }
    
    /**
     * Create stop as pickup point only.
     */
    public function asPickupOnly(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_pickup_point' => true,
                'is_dropoff_point' => false,
            ];
        });
    }
    
    /**
     * Create stop as dropoff point only.
     */
    public function asDropoffOnly(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_pickup_point' => false,
                'is_dropoff_point' => true,
            ];
        });
    }
}