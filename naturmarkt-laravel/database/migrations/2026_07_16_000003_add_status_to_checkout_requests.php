<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->string('status')->default('Neu')->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
