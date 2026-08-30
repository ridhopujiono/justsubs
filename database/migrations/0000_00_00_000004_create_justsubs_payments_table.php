<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    public function up(): void
    {
        $paymentsTable = Config::get('justsubs.tables.payments', 'justsubs_payments');
        $invoicesTable = Config::get('justsubs.tables.invoices', 'justsubs_invoices');

        Schema::create($paymentsTable, function (Blueprint $table) use ($invoicesTable) {
            $table->id();
            $table->foreignId('invoice_id')->constrained($invoicesTable)->cascadeOnDelete();
            
            $table->string('provider');
            $table->string('provider_reference')->nullable();
            
            $table->bigInteger('amount'); // Smallest unit
            $table->string('currency', 3)->default('IDR');
            
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('justsubs.tables.payments', 'justsubs_payments'));
    }
};
