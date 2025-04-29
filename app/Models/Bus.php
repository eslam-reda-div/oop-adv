<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_number',
        'capacity',
        'model',
        'manufacturer',
        'year_of_manufacture',
        'license_plate',
        'registration_expiry',
        'last_maintenance_date',
        'next_maintenance_date',
        'status',
        'fuel_type',
        'fuel_efficiency',
        'features',
        'notes',
        'image_path',
        'user_id',
        'driver_id'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'year_of_manufacture' => 'integer',
        'registration_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'fuel_efficiency' => 'float',
        'features' => 'array', // Will automatically JSON encode/decode this field
    ];

    // Each bus belongs to a company
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Each bus has a driver
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    // A bus can have many trips
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    // Get the full image URL
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return url('storage/' . $this->image_path);
        }
        return null;
    }

    // Check if the bus is due for maintenance
    public function isDueForMaintenance()
    {
        return $this->next_maintenance_date && now()->greaterThanOrEqualTo($this->next_maintenance_date);
    }

    // Check if registration is expired
    public function isRegistrationExpired()
    {
        return $this->registration_expiry && now()->greaterThan($this->registration_expiry);
    }

    // Get the age of the bus
    public function getAgeAttribute()
    {
        return $this->year_of_manufacture ? now()->year - $this->year_of_manufacture : null;
    }

    // Get upcoming trips for this bus
    public function upcomingTrips()
    {
        return $this->trips()->where('departure_time', '>', now())->orderBy('departure_time');
    }
}
