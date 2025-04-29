<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use App\Models\Bus;
use App\Models\Domain;
use App\Models\Destination;
use App\Models\Trip;
use App\Models\Path;
use App\Models\PathStop;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user only if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'), // Change in production
            ]
        );

        // Create bus companies (users)
        $busCompanies = User::factory()->count(3)->create();
        $allCompanies = $busCompanies->push($admin); // Include admin

        // Create domains
        $domains = Domain::factory()->count(5)->create();

        // Create destinations per domain (5-10 per domain)
        $allDestinations = collect();
        foreach ($domains as $domain) {
            // Create a mix of regular destinations and terminals
            $terminals = Destination::factory()
                ->count(3)
                ->terminal() // Use the terminal state we defined
                ->forDomain($domain)
                ->create();

            $regularDestinations = Destination::factory()
                ->count(rand(2, 7))
                ->forDomain($domain)
                ->create();

            $domainDestinations = $terminals->concat($regularDestinations);
            $allDestinations = $allDestinations->concat($domainDestinations);

            // Update destination count in domain
            $domain->destination_count = $domainDestinations->count();
            $domain->save();
        }

        // Create drivers for each company (3-8 drivers per company)
        $allDrivers = collect();
        foreach ($allCompanies as $company) {
            $drivers = Driver::factory()
                ->count(rand(3, 8))
                ->create([
                    'user_id' => $company->id,
                ]);

            $allDrivers = $allDrivers->concat($drivers);
        }

        // Create buses for each company (2-5 buses per company)
        $allBuses = collect();
        foreach ($allCompanies as $company) {
            // Get this company's drivers
            $companyDrivers = $allDrivers->where('user_id', $company->id);

            $buses = Bus::factory()
                ->count(rand(2, 5))
                ->create([
                    'user_id' => $company->id,
                    'driver_id' => $companyDrivers->random()->id,
                ]);

            $allBuses = $allBuses->concat($buses);
        }

        // Create paths first (before trips)
        $allPaths = collect();
        foreach ($domains as $domain) {
            // Get destinations for this domain
            $domainDestinations = $allDestinations->where('domain_id', $domain->id);

            // Create 3-6 paths per domain
            for ($i = 0; $i < rand(3, 6); $i++) {
                // Choose two random destinations from the domain for start and end
                $startDestination = $domainDestinations->random();
                $endDestination = $domainDestinations->whereNotIn('id', [$startDestination->id])->random();

                $path = Path::factory()
                    ->betweenDestinations($startDestination, $endDestination)
                    ->create();

                $allPaths = $allPaths->push($path);

                // Add potential stops to the path
                $potentialStops = $domainDestinations
                    ->whereNotIn('id', [$startDestination->id, $endDestination->id]);

                // Skip creating stops if we don't have enough destinations
                $stopCount = min(rand(0, 3), $potentialStops->count());
                if ($stopCount <= 0) {
                    continue;
                }

                // Select random stops
                $stopDestinations = $potentialStops->random($stopCount);

                // Create stops in order
                foreach ($stopDestinations as $index => $destination) {
                    $stopOrder = $index + 1;

                    PathStop::factory()
                        ->forPath($path)
                        ->atDestination($destination)
                        ->withOrder($stopOrder)
                        ->create();
                }

                // Update path with actual number of stops
                $path->number_of_stops = $stopCount;
                $path->save();
            }
        }

        // Create trips for each bus (2-8 trips per bus), each associated with a path
        $allTrips = collect();
        foreach ($allBuses as $bus) {
            $tripCount = rand(2, 8);

            for ($i = 0; $i < $tripCount; $i++) {
                // Select a random path for this trip
                $path = $allPaths->random();

                // Create the trip with the selected path
                $trip = Trip::factory()
                    ->forBus($bus)
                    ->forPath($path) // Use the new forPath method
                    ->create();

                $allTrips = $allTrips->push($trip);
            }
        }
    }
}
