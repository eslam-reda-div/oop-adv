<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_destination_id',
        'end_destination_id',
        'name',
        'total_distance',
        'total_duration',
        'number_of_stops',
        'route_description',
        'route_map_url',
        'directions_json',
        'path_code',
        'is_circular',
        'notes'
    ];

    protected $casts = [
        'total_distance' => 'float',
        'total_duration' => 'integer',
        'number_of_stops' => 'integer',
        'directions_json' => 'array', // Will automatically JSON encode/decode this field
        'is_circular' => 'boolean',
    ];

    // A path can have many trips
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    // The starting destination of the path
    public function startDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'start_destination_id');
    }

    // The ending destination of the path
    public function endDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'end_destination_id');
    }

    // A path has many stops (destinations) via the pivot table.
    public function stops(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'path_stops')
                    ->withPivot('stop_order', 'estimated_arrival_time', 'estimated_departure_time',
                                'stop_duration', 'distance_from_previous', 'time_from_previous',
                                'stop_notes', 'is_pickup_point', 'is_dropoff_point')
                    ->orderBy('path_stops.stop_order');
    }

    // Get only the pickup points
    public function pickupPoints()
    {
        return $this->stops()->wherePivot('is_pickup_point', true);
    }

    // Get only the dropoff points
    public function dropoffPoints()
    {
        return $this->stops()->wherePivot('is_dropoff_point', true);
    }

    // Format the total duration as hours and minutes
    public function getFormattedDurationAttribute()
    {
        if (!$this->total_duration) return null;

        $hours = floor($this->total_duration / 60);
        $minutes = $this->total_duration % 60;

        return $hours . 'h ' . $minutes . 'm';
    }

    // Get the route name (from start to end)
    public function getRouteNameAttribute()
    {
        $start = $this->startDestination?->name ?? 'Unknown';
        $end = $this->endDestination?->name ?? 'Unknown';

        return "{$start} to {$end}";
    }

    // Calculate and update the number of stops
    public function updateNumberOfStops()
    {
        $this->number_of_stops = $this->stops()->count();
        $this->save();

        return $this;
    }

    // Calculate and update the total distance and duration based on stops
    public function calculatePathMetrics()
    {
        $stops = $this->stops()->orderBy('path_stops.stop_order')->get();

        if ($stops->count() < 2) {
            return $this;
        }

        $totalDistance = 0;
        $totalDuration = 0;

        foreach ($stops as $index => $stop) {
            if ($index > 0) {
                $distanceFromPrevious = $stop->pivot->distance_from_previous ?? 0;
                $timeFromPrevious = $stop->pivot->time_from_previous ?? 0;

                $totalDistance += $distanceFromPrevious;
                $totalDuration += $timeFromPrevious;

                // Add any stop duration
                $totalDuration += $stop->pivot->stop_duration ?? 0;
            }
        }

        $this->total_distance = $totalDistance;
        $this->total_duration = $totalDuration;
        $this->save();

        return $this;
    }
}
