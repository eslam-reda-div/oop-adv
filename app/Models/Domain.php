<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'region',
        'country',
        'contact_person',
        'contact_email',
        'contact_phone',
        'is_active',
        'destination_count',
        'color_code',
        'icon',
        'image_path',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'destination_count' => 'integer',
    ];

    // A domain groups many destinations
    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    // Get the full image URL
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return url('storage/' . $this->image_path);
        }
        return null;
    }

    // Scope for active domains only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordering by name
    public function scopeOrderByName($query)
    {
        return $query->orderBy('name');
    }

    // Scope for filtering by region
    public function scopeInRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    // Scope for filtering by country
    public function scopeInCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    // Update the destination count
    public function updateDestinationCount()
    {
        $this->destination_count = $this->destinations()->count();
        $this->save();
    }
}
