<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'format',
        'content',
        'image_path',
        'target_url',
        'target_countries',
        'countdown_seconds',
        'is_active',
    ];

    protected $casts = [
        'target_countries' => 'array',
        'is_active' => 'boolean',
        'countdown_seconds' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBanner($query)
    {
        return $query->where('format', 'banner');
    }

    public function scopeInterstitial($query)
    {
        return $query->where('format', 'interstitial');
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where(function ($q) use ($countryCode) {
            $q->whereNull('target_countries')
              ->orWhereJsonContains('target_countries', $countryCode);
        });
    }
}
