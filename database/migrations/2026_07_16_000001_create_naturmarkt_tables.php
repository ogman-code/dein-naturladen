<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('checkout_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('payment_method')->default('PayPal');
            $table->json('cart')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_requests');
        Schema::dropIfExists('newsletter_requests');
    }
};
