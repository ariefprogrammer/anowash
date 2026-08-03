<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'business_name',
        'owner_name',
        'email',
        'phone',
        'address',
        'logo_path',
        'status',
        'referred_by_marketing_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Owner $owner) {
            if ($owner->isForceDeleting()) {
                $owner->users()->delete();
            } else {
                $owner->users()->update(['is_active' => false]);
            }
        });

        static::restoring(function (Owner $owner) {
            $owner->users()->update(['is_active' => true]);
        });
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionInvoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function vehicleCategories(): HasMany
    {
        return $this->hasMany(VehicleCategory::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(CommissionRule::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function referredByMarketing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Marketing::class, 'referred_by_marketing_id');
    }
}