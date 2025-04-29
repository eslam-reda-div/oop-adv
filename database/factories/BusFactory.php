<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'maintenance', 'out_of_service']);
        $fuelTypes = ['diesel', 'petrol', 'electric', 'hybrid', 'cng', 'lpg'];
        
        return [
            'bus_number' => $this->faker->unique()->bothify('BUS-####??'),
            'capacity' => $this->faker->numberBetween(20, 60),
            'model' => $this->faker->randomElement(['Mercedes Benz', 'Volvo', 'Scania', 'MAN', 'Isuzu', 'Tata', 'Hino']),
            'manufacturer' => $this->faker->company(),
            'year_of_manufacture' => $this->faker->numberBetween(2010, 2025),
            'license_plate' => $this->faker->regexify('[A-Z]{2}[0-9]{4}[A-Z]{2}'),
            'registration_expiry' => $this->faker->dateTimeBetween('+1 month', '+3 years'),
            'last_maintenance_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'next_maintenance_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'status' => $status,
            'fuel_type' => $this->faker->randomElement($fuelTypes),
            'fuel_efficiency' => $this->faker->randomFloat(2, 5, 15),
            'features' => $this->faker->randomElements(['WiFi', 'AC', 'Entertainment System', 'USB Charging', 'Restroom', 'Reclining Seats'], $this->faker->numberBetween(1, 6)),
            'notes' => $status === 'maintenance' || $status === 'out_of_service' ? $this->faker->paragraph() : null,
            'user_id' => User::factory(),
            'driver_id' => Driver::factory(),
        ];
    }
    
    /**
     * Indicate that the bus is under maintenance.
     */
    public function underMaintenance(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'maintenance',
                'notes' => $this->faker->paragraph(1, true),
                'next_maintenance_date' => $this->faker->dateTimeBetween('+1 day', '+2 weeks'),
            ];
        });
    }
    
    /**
     * Indicate that the bus is active.
     */
    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }
    
    /**
     * Indicate that the bus is out of service.
     */
    public function outOfService(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'out_of_service',
                'notes' => $this->faker->paragraph(1, true),
            ];
        });
    }
    
    /**
     * Indicate that the bus is electric.
     */
    public function electric(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'fuel_type' => 'electric',
                'fuel_efficiency' => null,
            ];
        });
    }
}