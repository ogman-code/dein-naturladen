<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 80)->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('country_code', 2)->default('DE');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->foreignId('customer_user_id')
                ->nullable()
                ->after('id')
                ->constrained('customer_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_user_id');
        });

        Schema::dropIfExists('customer_users');
    }
};
