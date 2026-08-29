<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Config::get('justsubs.tables.subscriptions', 'justsubs_subscriptions'), function (Blueprint $table) {
            $table->id();
            
            $table->string('subscriber_type');
            $table->string('subscriber_id');
            $table->index(['subscriber_type', 'subscriber_id']);

            $table->foreignId('plan_id')->constrained(Config::get('justsubs.tables.plans', 'justsubs_plans'))->cascadeOnDelete();
            
            $table->string('status');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('cancelled_at')->nullable();
            
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('justsubs.tables.subscriptions', 'justsubs_subscriptions'));
    }
};
