<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->string('country_code', 2)->default('DE')->after('city');
            $table->decimal('subtotal', 10, 2)->default(0)->after('cart');
            $table->decimal('shipping', 10, 2)->default(0)->after('subtotal');
            $table->decimal('discount', 10, 2)->default(0)->after('shipping');
            $table->string('coupon_code')->nullable()->after('discount');
            $table->string('payment_status')->default('Offen')->after('payment_method');
        });

        Schema::create('product_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('category_key');
            $table->string('product_handle');
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['category_key', 'product_handle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_overrides');

        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->dropColumn([
                'country_code', 'subtotal', 'shipping', 'discount',
                'coupon_code', 'payment_status',
            ]);
        });
    }
};
