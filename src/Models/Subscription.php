<?php

namespace Ridho\JustSubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;
use Ridho\JustSubs\Database\Factories\SubscriptionFactory;
use Ridho\JustSubs\Enums\SubscriptionStatus;
use Ridho\JustSubs\JustSubs;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function getTable()
    {
        return Config::get('justsubs.tables.subscriptions', 'justsubs_subscriptions');
    }

    protected static function newFactory()
    {
        return SubscriptionFactory::new();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getSubscriberNameAttribute(): string
    {
        return JustSubs::getSubscriberName($this->subscriber);
    }

    public function active(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && $this->starts_at->lte(now())
            && $this->ends_at->gte(now());
    }

    public function expired(): bool
    {
        return $this->status === SubscriptionStatus::Expired
            || $this->ends_at->lt(now());
    }

    public function cancelled(): bool
    {
        return $this->status === SubscriptionStatus::Cancelled || ! is_null($this->cancelled_at);
    }
}
