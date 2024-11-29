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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->nullable();
            $table->text('product_desc')->nullable();
            $table->decimal('price',10,2)->nullable();
            $table->decimal('discount_price',10,2)->nullable();
            $table->integer('total_stock')->nullable();
            $table->integer('out_of_stock')->default(1)->comment('1 in stock and 0 out of stock');
            $table->integer('category_id')->default(0);
            $table->string('item_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
