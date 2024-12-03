<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_add_items', function (Blueprint $table) {
            $table->dateTime('purchse_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_add_items', function (Blueprint $table) {
            $table->dropColumn('purchse_date');
            $table->dropColumn('delivered_date');
        });
    }
};
