<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('customers',function(Blueprint $t){$t->boolean('is_blocked')->default(false);$t->boolean('newsletter_opt_in')->default(false);});
  Schema::table('checkout_requests',function(Blueprint $t){$t->string('tracking_number')->nullable();$t->string('shipping_carrier')->nullable();$t->timestamp('shipped_at')->nullable();});
  Schema::table('product_overrides',function(Blueprint $t){$t->decimal('sale_price',10,2)->nullable();$t->string('allergens')->nullable();$t->string('base_price_unit')->nullable();$t->boolean('featured')->default(false);});
  Schema::create('custom_products',function(Blueprint $t){$t->id();$t->string('category_key');$t->string('name');$t->string('handle');$t->decimal('price',10,2);$t->decimal('sale_price',10,2)->nullable();$t->unsignedInteger('stock')->default(0);$t->unsignedInteger('weight_grams')->default(500);$t->text('description')->nullable();$t->text('ingredients')->nullable();$t->string('allergens')->nullable();$t->text('image_url')->nullable();$t->boolean('active')->default(true);$t->boolean('featured')->default(false);$t->timestamps();$t->unique(['category_key','handle']);});
  Schema::create('coupons',function(Blueprint $t){$t->id();$t->string('code')->unique();$t->enum('type',['percent','fixed']);$t->decimal('value',10,2);$t->decimal('minimum_order',10,2)->default(0);$t->timestamp('expires_at')->nullable();$t->unsignedInteger('max_uses')->nullable();$t->unsignedInteger('uses')->default(0);$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('coupon_customer',function(Blueprint $t){$t->id();$t->foreignId('coupon_id')->constrained()->cascadeOnDelete();$t->foreignId('customer_id')->constrained()->cascadeOnDelete();$t->timestamps();$t->unique(['coupon_id','customer_id']);});
  Schema::create('wishlists',function(Blueprint $t){$t->id();$t->foreignId('customer_id')->constrained()->cascadeOnDelete();$t->string('category_key');$t->string('product_handle');$t->boolean('notify_back_in_stock')->default(false);$t->timestamps();$t->unique(['customer_id','category_key','product_handle']);});
  Schema::create('product_reviews',function(Blueprint $t){$t->id();$t->foreignId('customer_id')->constrained()->cascadeOnDelete();$t->string('category_key');$t->string('product_handle');$t->unsignedTinyInteger('rating');$t->text('comment')->nullable();$t->enum('status',['pending','approved','rejected'])->default('pending');$t->timestamps();$t->unique(['customer_id','category_key','product_handle']);});
  Schema::create('admin_activity_logs',function(Blueprint $t){$t->id();$t->foreignId('owner_user_id')->nullable()->constrained()->nullOnDelete();$t->string('action');$t->text('details')->nullable();$t->string('ip_address',45)->nullable();$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('admin_activity_logs');Schema::dropIfExists('product_reviews');Schema::dropIfExists('wishlists');Schema::dropIfExists('coupon_customer');Schema::dropIfExists('coupons');Schema::dropIfExists('custom_products');Schema::table('product_overrides',fn(Blueprint $t)=>$t->dropColumn(['sale_price','allergens','base_price_unit','featured']));Schema::table('checkout_requests',fn(Blueprint $t)=>$t->dropColumn(['tracking_number','shipping_carrier','shipped_at']));Schema::table('customers',fn(Blueprint $t)=>$t->dropColumn(['is_blocked','newsletter_opt_in']));}
};
