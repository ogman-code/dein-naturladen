<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_overrides', function (Blueprint $table) {
            $table->unsignedInteger('weight_grams')->nullable()->after('stock');
            $table->text('image_url')->nullable()->after('ingredients');
        });
    }

    public function down(): void
    {
        Schema::table('product_overrides', function (Blueprint $table) {
            $table->dropColumn(['weight_grams', 'image_url']);
        });
    }
};
