<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Widerrufserklaerungen, die ueber die elektronische Widerrufsfunktion (§ 356a BGB) eingehen. */
    public function up(): void
    {
        Schema::create('contract_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contract_reference', 500);
            $table->string('email');
            $table->foreignId('checkout_request_id')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->index();
            $table->timestamp('received_at');
            $table->timestamp('receipt_sent_at')->nullable();
            $table->string('status')->default('Eingegangen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_withdrawals');
    }
};
