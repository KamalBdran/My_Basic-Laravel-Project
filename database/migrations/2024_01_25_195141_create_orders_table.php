<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('t_name');
            $table->integer('quantity');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('status', ['preparing', 'sended', 'deliverd'])->default('preparing');
            $table->timestamps();
            $table->string('payment')->default('unpaid');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};