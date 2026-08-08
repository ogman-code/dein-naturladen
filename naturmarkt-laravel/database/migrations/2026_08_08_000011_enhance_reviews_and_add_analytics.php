<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->foreignId('customer_user_id')->nullable()->after('id')->constrained('customer_users')->nullOnDelete();
            $table->boolean('verified_purchase')->default(false)->after('comment');
            $table->string('image_path')->nullable()->after('verified_purchase');
            $table->unsignedInteger('helpful_count')->default(0)->after('image_path');
            $table->text('owner_reply')->nullable()->after('helpful_count');
            $table->timestamp('replied_at')->nullable()->after('owner_reply');
        });

        Schema::create('review_helpful_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('product_reviews')->cascadeOnDelete();
            $table->string('visitor_hash', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['review_id', 'visitor_hash']);
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 40)->index();
            $table->string('path', 500)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('product', 160)->nullable();
            $table->string('visitor_hash', 64)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('review_helpful_votes');
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_user_id');
            $table->dropColumn(['verified_purchase', 'image_path', 'helpful_count', 'owner_reply', 'replied_at']);
        });
    }
};
