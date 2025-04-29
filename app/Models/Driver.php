<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_number',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'license_expiry_date',
        'status',
        'years_of_experience',
        'notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'image_path',
        'user_id'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'license_expiry_date' => 'date',
        'years_of_experience' => 'integer',
    ];

    // Each driver belongs to a company
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // A driver might be assigned to multiple buses over time
    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    // Get all trips associated with this driver through their buses
    public function trips()
    {
        return $this->hasManyThrough(Trip::class, Bus::class);
    }

    // Check if a driver's license is expired
    public function isLicenseExpired()
    {
        return $this->license_expiry_date && now()->greaterThan($this->license_expiry_date);
    }

    // Get the full image URL
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return url('storage/' . $this->image_path);
        }
        return null;
    }

    // Get the driver's age
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? now()->diffInYears($this->date_of_birth) : null;
    }
}
