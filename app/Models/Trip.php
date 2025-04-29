<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'path_id', // Added path_id
        'departure_time',
        'arrival_time',
        'price',
        'trip_code',
        'available_seats',
        'booked_seats',
        'status',
        'delay_reason',
        'cancellation_reason',
        'distance',
        'estimated_duration',
        'fuel_consumption',
        'notes'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'price' => 'float',
        'available_seats' => 'integer',
        'booked_seats' => 'integer',
        'distance' => 'float',
        'estimated_duration' => 'integer',
        'fuel_consumption' => 'float',
    ];

    // Each trip belongs to a bus
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    // Each trip belongs to a path (changed from hasOne to belongsTo)
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    // Get the driver of this trip through the bus
    public function driver()
    {
        return $this->bus->driver;
    }

    // Get the company of this trip through the bus
    public function company()
    {
        return $this->bus->company;
    }

    // Get start and end destinations through the path
    public function getStartDestinationAttribute()
    {
        return $this->path->startDestination;
    }

    public function getEndDestinationAttribute()
    {
        return $this->path->endDestination;
    }

    // Calculate the remaining seats
    public function getRemainingSeatsAttribute()
    {
        return $this->available_seats - $this->booked_seats;
    }

    // Check if the trip is fully booked
    public function isFullyBooked()
    {
        return $this->booked_seats >= $this->available_seats;
    }

    // Calculate the trip duration in minutes
    public function getDurationInMinutesAttribute()
    {
        return $this->departure_time ? $this->arrival_time->diffInMinutes($this->departure_time) : $this->estimated_duration;
    }

    // Format the duration as hours and minutes
    public function getFormattedDurationAttribute()
    {
        $minutes = $this->getDurationInMinutesAttribute();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    // Check if the trip is delayed
    public function isDelayed()
    {
        return $this->status === 'delayed';
    }

    // Check if the trip is complete
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // Check if the trip is cancelled
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // Check if the trip is in progress
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    // Scope for upcoming trips
    public function scopeUpcoming($query)
    {
        return $query->where('departure_time', '>', now())
                     ->where('status', '!=', 'cancelled')
                     ->orderBy('departure_time');
    }

    // Scope for completed trips
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')->orderBy('arrival_time', 'desc');
    }
}
