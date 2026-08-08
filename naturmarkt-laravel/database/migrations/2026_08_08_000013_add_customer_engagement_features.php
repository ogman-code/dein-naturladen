<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_user_id')->constrained('customer_users')->cascadeOnDelete();
            $table->string('category', 100); $table->string('product', 160); $table->timestamps();
            $table->unique(['customer_user_id', 'category', 'product']);
        });
        Schema::create('availability_subscriptions', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_user_id')->nullable()->constrained('customer_users')->nullOnDelete();
            $table->string('email'); $table->string('category', 100); $table->string('product', 160); $table->string('token', 64)->unique();
            $table->boolean('active')->default(true); $table->timestamp('notified_at')->nullable(); $table->timestamps();
            $table->unique(['email', 'category', 'product']);
        });
        Schema::create('coupons', function (Blueprint $table) {
            $table->id(); $table->string('code', 40)->unique(); $table->string('type', 10); $table->decimal('value', 10, 2);
            $table->decimal('minimum_order', 10, 2)->default(0); $table->unsignedInteger('maximum_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0); $table->timestamp('starts_at')->nullable(); $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('cart_reminders', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_user_id')->nullable()->constrained('customer_users')->nullOnDelete();
            $table->string('email'); $table->json('cart'); $table->string('token', 64)->unique();
            $table->timestamp('remind_after'); $table->timestamp('sent_at')->nullable(); $table->timestamp('unsubscribed_at')->nullable(); $table->timestamps();
        });
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('status'); $table->string('tracking_url', 1000)->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('tracking_url'); $table->foreignId('coupon_id')->nullable()->after('coupon_code')->constrained('coupons')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) { $table->dropConstrainedForeignId('coupon_id'); $table->dropColumn(['tracking_number', 'tracking_url', 'shipped_at']); });
        Schema::dropIfExists('cart_reminders'); Schema::dropIfExists('coupons'); Schema::dropIfExists('availability_subscriptions'); Schema::dropIfExists('wishlists');
    }
};
