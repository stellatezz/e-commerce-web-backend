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
        Schema::create('orders', function (Blueprint $table) {
            //
            $table->id('uuid');

            $table->string('digest');
            $table->double('total_price', 15, 8);
            $table->string('currency');
            $table->string('email');
            $table->string('salt');
            $table->text('products');
            $table->text('prices');

            $table->string('username');
            $table->integer('uid');

            $table->string('txn_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
