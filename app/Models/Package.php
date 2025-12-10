<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration_days',
        'is_active',
        'is_trial',
        'features',
        'description',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_trial' => 'boolean',
            'features' => 'array',
        ];
    }

    /**
     * Get the users that have this package.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope a query to only include active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include trial packages.
     */
    public function scopeTrial($query)
    {
        return $query->where('is_trial', true);
    }

    /**
     * Scope a query to only include paid packages.
     */
    public function scopePaid($query)
    {
        return $query->where('is_trial', false)->where('price', '>', 0);
    }

    /**
     * Check if this is a lifetime package (no expiration).
     */
    public function isLifetime(): bool
    {
        return $this->duration_days === null && !$this->is_trial;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) {
            return 'Gratis';
        }
        return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Get duration label.
     */
    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_days === null) {
            return 'Selamanya';
        }
        return $this->duration_days . ' hari';
    }
}
