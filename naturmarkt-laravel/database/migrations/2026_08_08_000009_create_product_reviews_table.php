<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);
            $table->string('product', 160);
            $table->string('name', 80);
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->timestamps();

            $table->index(['category', 'product']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
