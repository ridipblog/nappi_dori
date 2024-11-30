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
        Schema::create('user_add_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price_at_purchase',10,2)->nullable();
            $table->integer('total_item');
            $table->integer('cart_status')->default(0)->comment('0 still in cart , 1 update to purchase,2 remove form cart');
            $table->integer('purchase_status')->default(0)->comment('0 still in not purchase,1 purchase item');
            $table->integer('delivery_status')->default(0)->comment('no process form purchase,1 for delivery done,2 for reject delivery');
            $table->string('rejection_reason')->nullable()->comment('if any rejection ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_add_items');
    }
};
