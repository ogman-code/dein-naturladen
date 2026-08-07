<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_overrides', function (Blueprint $table) {
            $table->text('description')->nullable()->after('stock');
            $table->text('ingredients')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('product_overrides', function (Blueprint $table) {
            $table->dropColumn(['description', 'ingredients']);
        });
    }
};
