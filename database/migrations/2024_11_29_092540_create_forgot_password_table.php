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
        Schema::create('forgot_password', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->integer('otp');
            $table->dateTime('expire_time');
            $table->integer('is_used')->default(1)->comment('1 for no used , 2 for already used');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forgot_password');
    }
};
