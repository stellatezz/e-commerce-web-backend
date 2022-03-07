<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('pid');
            $table->timestamps();
            $table->integer('catid')->unsigned();
            $table->foreign('catid')->references('catid')->on('categories');
            $table->string('name');
            $table->double('price', 15, 8);
            $table->text('description');
            $table->integer('stock');
            $table->text('image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
