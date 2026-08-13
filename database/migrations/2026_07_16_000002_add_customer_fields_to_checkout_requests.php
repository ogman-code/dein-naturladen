<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('street')->nullable()->after('phone');
            $table->string('postal_code', 20)->nullable()->after('street');
            $table->string('city')->nullable()->after('postal_code');
            $table->text('notes')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'phone', 'street', 'postal_code', 'city', 'notes']);
        });
    }
};
