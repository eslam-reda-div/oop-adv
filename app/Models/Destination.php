<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'description',
        'facilities',
        'contact_phone',
        'contact_email',
        'opening_hours',
        'notes',
        'image_path',
        'domain_id'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'facilities' => 'array', // Will automatically JSON encode/decode this field
    ];

    // Each destination belongs to a domain
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    // A destination can be part of many paths as a stop
    public function pathsAsStop(): BelongsToMany
    {
        return $this->belongsToMany(Path::class, 'path_stops')
                    ->withPivot('stop_order', 'estimated_arrival_time', 'estimated_departure_time',
                               'stop_duration', 'distance_from_previous', 'time_from_previous',
                               'stop_notes', 'is_pickup_point', 'is_dropoff_point')
                    ->withTimestamps();
    }

    // A destination can be the starting point of many paths
    public function pathsAsStart()
    {
        return $this->hasMany(Path::class, 'start_destination_id');
    }

    // A destination can be the ending point of many paths
    public function pathsAsEnd()
    {
        return $this->hasMany(Path::class, 'end_destination_id');
    }

    // Get the full image URL
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return url('storage/' . $this->image_path);
        }
        return null;
    }

    // Get formatted address
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->country,
            $this->postal_code
        ]);

        return implode(', ', $parts);
    }

    // Scope to find destinations by city
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    // Scope to find destinations by country
    public function scopeInCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    // Scope to order by name
    public function scopeOrderByName($query)
    {
        return $query->orderBy('name');
    }

    // Scope to get destinations with coordinates
    public function scopeHasCoordinates($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    // Calculate distance to another destination
    public function distanceTo(Destination $destination)
    {
        if (!$this->latitude || !$this->longitude || !$destination->latitude || !$destination->longitude) {
            return null;
        }

        // Simple distance calculation using Haversine formula
        $earthRadius = 6371; // km

        $latDiff = deg2rad($destination->latitude - $this->latitude);
        $lonDiff = deg2rad($destination->longitude - $this->longitude);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($destination->latitude)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
