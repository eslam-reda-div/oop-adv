<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathStop extends Model
{
    use HasFactory;

    protected $table = 'path_stops';

    protected $fillable = [
        'path_id',
        'destination_id',
        'stop_order',
        'estimated_arrival_time',
        'estimated_departure_time',
        'stop_duration',
        'distance_from_previous',
        'time_from_previous',
        'stop_notes',
        'is_pickup_point',
        'is_dropoff_point',
    ];

    protected $casts = [
        'estimated_arrival_time' => 'datetime:H:i',
        'estimated_departure_time' => 'datetime:H:i',
        'is_pickup_point' => 'boolean',
        'is_dropoff_point' => 'boolean',
    ];

    // Relationships
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
