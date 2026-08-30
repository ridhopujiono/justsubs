<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $invoicesTable = Config::get('justsubs.tables.invoices', 'justsubs_invoices');
        $subscriptionsTable = Config::get('justsubs.tables.subscriptions', 'justsubs_subscriptions');

        Schema::create($invoicesTable, function (Blueprint $table) use ($subscriptionsTable) {
            $table->id();
            $table->string('invoice_number')->unique()->nullable();

            // Polymorphic subscriber
            $table->string('subscriber_type');
            $table->string('subscriber_id');
            $table->index(['subscriber_type', 'subscriber_id'], 'subscriber_idx');

            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained($subscriptionsTable)
                ->nullOnDelete();

            $table->bigInteger('amount'); // Smallest unit, e.g. cents
            $table->string('currency', 3)->default('IDR');

            $table->string('status')->default('draft');

            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('justsubs.tables.invoices', 'justsubs_invoices'));
    }
};
