<?php

namespace Database\Factories;

use App\Models\Path;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

class PathFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Path::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate path code
        $pathCode = 'PTH-' . strtoupper($this->faker->bothify('??####'));

        // Random distance and duration
        $totalDistance = $this->faker->numberBetween(50, 1000);
        $totalDuration = $this->faker->numberBetween(30, 1200); // 30 min to 20 hours

        return [
            'path_code' => $pathCode,
            'name' => $this->faker->sentence(3), // Added name for the path
            'start_destination_id' => Destination::factory(),
            'end_destination_id' => Destination::factory(),
            'total_distance' => $totalDistance,
            'total_duration' => $totalDuration,
            'number_of_stops' => $this->faker->numberBetween(0, 5),
            'is_circular' => $this->faker->boolean(10), // 10% chance to be circular
            'route_map_url' => $this->faker->boolean(70) ? $this->faker->url() : null,
            'route_description' => $this->faker->boolean(80) ? $this->faker->paragraph() : null,
            'notes' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
            'directions_json' => $this->faker->boolean(50) ? json_encode([
                'points' => [
                    ['lat' => $this->faker->latitude(), 'lng' => $this->faker->longitude()],
                    ['lat' => $this->faker->latitude(), 'lng' => $this->faker->longitude()],
                    ['lat' => $this->faker->latitude(), 'lng' => $this->faker->longitude()],
                ]
            ]) : null,
        ];
    }

    /**
     * Set specific start and end destinations.
     */
    public function betweenDestinations(Destination $start, Destination $end): self
    {
        return $this->state(function (array $attributes) use ($start, $end) {
            // Calculate an approximate distance if we have coordinates
            $totalDistance = $attributes['total_distance'];

            if ($start->latitude && $start->longitude && $end->latitude && $end->longitude) {
                // Simple distance calculation (not perfect, just for demo)
                $latDiff = abs($start->latitude - $end->latitude);
                $lonDiff = abs($start->longitude - $end->longitude);
                $totalDistance = round(sqrt(($latDiff * $latDiff) + ($lonDiff * $lonDiff)) * 111.32 * 1000); // Rough km estimate
            }

            // Generate a name based on destinations
            $name = "{$start->name} to {$end->name}";

            return [
                'start_destination_id' => $start->id,
                'end_destination_id' => $end->id,
                'total_distance' => $totalDistance,
                'is_circular' => $start->id === $end->id,
                'name' => $name,
            ];
        });
    }

    /**
     * Path with no stops.
     */
    public function withoutStops(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'number_of_stops' => 0,
            ];
        });
    }

    /**
     * Path with a specific number of stops.
     */
    public function withStops(int $count): self
    {
        return $this->state(function (array $attributes) use ($count) {
            return [
                'number_of_stops' => $count,
            ];
        });
    }
}
