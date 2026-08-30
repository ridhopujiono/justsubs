<?php

namespace Ridho\JustSubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Ridho\JustSubs\Enums\PaymentStatus;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function getTable()
    {
        return Config::get('justsubs.tables.payments', 'justsubs_payments');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
