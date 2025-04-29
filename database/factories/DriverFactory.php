<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'inactive', 'on_leave', 'terminated']);
        
        return [
            'name' => $this->faker->name(),
            'license_number' => $this->faker->unique()->numerify('DL-#####-########'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-20 years'),
            'license_expiry_date' => $this->faker->dateTimeBetween('+1 month', '+5 years'),
            'status' => $status,
            'years_of_experience' => $this->faker->numberBetween(1, 20),
            'notes' => $status === 'terminated' || $status === 'on_leave' ? $this->faker->paragraph() : null,
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'user_id' => User::factory(), // This will create a new user if not provided
        ];
    }
    
    /**
     * Indicate that the driver's license is expired.
     */
    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'license_expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            ];
        });
    }
    
    /**
     * Indicate that the driver is active.
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
     * Indicate that the driver is on leave.
     */
    public function onLeave(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'on_leave',
                'notes' => $this->faker->paragraph(),
            ];
        });
    }
}