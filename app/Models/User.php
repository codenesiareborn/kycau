<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'package_id',
        'package_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'package_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Determine if the user can access the Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // All users can access panel, permissions are handled by Shield
    }

    /**
     * Get the user's package
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the user's customers
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the user's products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the user's sales
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the user's file uploads
     */
    public function fileUploads(): HasMany
    {
        return $this->hasMany(FileUpload::class);
    }

    /**
     * Check if the user has an active package (not expired).
     */
    public function hasActivePackage(): bool
    {
        if (!$this->package_id) {
            return false;
        }

        // Lifetime packages never expire
        if ($this->package?->isLifetime()) {
            return true;
        }

        // Check if package has expired
        if ($this->package_expires_at && $this->package_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the user's package has expired.
     */
    public function isPackageExpired(): bool
    {
        if (!$this->package_id) {
            return true;
        }

        if ($this->package?->isLifetime()) {
            return false;
        }

        return $this->package_expires_at && $this->package_expires_at->isPast();
    }

    /**
     * Get the package expiration status label.
     */
    public function getPackageStatusAttribute(): string
    {
        if (!$this->package_id) {
            return 'Tidak ada paket';
        }

        if ($this->package?->isLifetime()) {
            return 'Lifetime';
        }

        if ($this->isPackageExpired()) {
            return 'Kadaluarsa';
        }

        $daysLeft = now()->diffInDays($this->package_expires_at, false);
        if ($daysLeft <= 0) {
            return 'Kadaluarsa hari ini';
        }

        return $daysLeft . ' hari tersisa';
    }
}

