<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

class DestinationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Destination::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create plausible coordinates
        $latitude = $this->faker->latitude();
        $longitude = $this->faker->longitude();
        
        return [
            'name' => $this->faker->unique()->city(),
            'description' => $this->faker->paragraph(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->postcode(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->safeEmail(),
            'opening_hours' => $this->faker->randomElement([
                'Mon-Fri: 8am-5pm, Sat-Sun: 10am-4pm',
                'Daily: 9am-6pm',
                'Mon-Thu: 7am-9pm, Fri-Sun: 9am-11pm',
                '24/7',
                'Mon-Fri: 6am-10pm, Sat: 8am-8pm, Sun: Closed'
            ]),
            'facilities' => $this->faker->randomElements([
                'Restrooms', 'Food Court', 'WiFi', 'Waiting Area', 
                'Parking', 'Luggage Storage', 'ATM', 'Ticket Office',
                'Information Desk', 'Shops', 'Disabled Access', 'Baby Changing'
            ], $this->faker->numberBetween(2, 6)),
            'notes' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
            'domain_id' => Domain::factory(),
        ];
    }
    
    /**
     * Set the domain ID for this destination.
     */
    public function forDomain(Domain $domain): self
    {
        return $this->state(function (array $attributes) use ($domain) {
            // Match destination country to domain country if available
            $country = $domain->country ?? $this->faker->country();
            
            return [
                'domain_id' => $domain->id,
                'country' => $country,
            ];
        });
    }
    
    /**
     * Create a terminal/station destination.
     */
    public function terminal(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->randomElement([
                    $this->faker->city() . ' Bus Terminal',
                    $this->faker->city() . ' Central Station',
                    $this->faker->city() . ' Transit Center',
                    $this->faker->lastName() . ' Bus Terminal',
                    $this->faker->city() . ' Bus Station'
                ]),
                'facilities' => $this->faker->randomElements([
                    'Restrooms', 'Food Court', 'WiFi', 'Waiting Area', 
                    'Parking', 'Luggage Storage', 'ATM', 'Ticket Office',
                    'Information Desk', 'Shops', 'Disabled Access', 'Baby Changing',
                    'Security', 'First Aid', 'Restaurant', 'Charging Stations'
                ], $this->faker->numberBetween(5, 10)),
            ];
        });
    }
    
    /**
     * Create a tourist attraction destination.
     */
    public function touristAttraction(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->randomElement([
                    $this->faker->city() . ' Museum',
                    $this->faker->lastName() . ' Park',
                    'The ' . $this->faker->word() . ' Gardens',
                    $this->faker->city() . ' Monument',
                    $this->faker->word() . ' Beach',
                    $this->faker->lastName() . ' Castle',
                    $this->faker->city() . ' Zoo'
                ]),
                'description' => $this->faker->paragraph(3),
            ];
        });
    }
}