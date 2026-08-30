<?php

namespace Ridho\JustSubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Ridho\JustSubs\Database\Factories\InvoiceFactory;
use Ridho\JustSubs\Enums\InvoiceStatus;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'status' => InvoiceStatus::class,
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function getTable()
    {
        return Config::get('justsubs.tables.invoices', 'justsubs_invoices');
    }

    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }

    protected static function booted()
    {
        // Automatically generate a collision-safe invoice number if not set.
        // Format: INV-YYYYMM-{ID}
        static::created(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . now()->format('Ym') . '-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT);
                $invoice->saveQuietly(); // Use saveQuietly to prevent re-triggering events
            }
        });
    }

    public function subscriber(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
