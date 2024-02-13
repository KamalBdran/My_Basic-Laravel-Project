<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('t_name');
            $table->string('s_name');
            $table->string('category');
            $table->string('company_name');
            $table->date('exp_date');
            $table->double('quantity');
            $table->string('image');
            $table->decimal('price', 8, 2);      
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
