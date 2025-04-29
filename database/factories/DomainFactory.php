<?php

namespace Database\Factories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regions = [
            'North America' => ['United States', 'Canada', 'Mexico'],
            'Europe' => ['United Kingdom', 'France', 'Germany', 'Spain', 'Italy'],
            'Asia' => ['Japan', 'China', 'India', 'Singapore', 'Thailand'],
            'Middle East' => ['UAE', 'Saudi Arabia', 'Qatar', 'Egypt'],
            'Africa' => ['South Africa', 'Kenya', 'Morocco', 'Nigeria'],
            'Oceania' => ['Australia', 'New Zealand']
        ];
        
        $region = $this->faker->randomElement(array_keys($regions));
        $country = $this->faker->randomElement($regions[$region]);
        
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->paragraph(),
            'region' => $region,
            'country' => $country,
            'color_code' => $this->faker->hexColor(),
            'icon' => $this->faker->randomElement(['globe', 'map', 'mountain', 'beach', 'city', 'forest', 'landmark']),
            'is_active' => $this->faker->boolean(80), // 80% chance to be active
            'contact_person' => $this->faker->name(),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'notes' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
        ];
    }
    
    /**
     * Indicate that the domain is active.
     */
    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }
    
    /**
     * Indicate that the domain is inactive.
     */
    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
    
    /**
     * Set a specific region and country.
     */
    public function location(string $region, string $country): self
    {
        return $this->state(function (array $attributes) use ($region, $country) {
            return [
                'region' => $region,
                'country' => $country,
            ];
        });
    }
}