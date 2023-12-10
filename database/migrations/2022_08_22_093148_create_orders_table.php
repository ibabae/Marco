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
            $table->increments('id');
            $table->unsignedInteger('UserId');
            $table->index('UserId');
            $table->foreign('UserId')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('Price');
            $table->integer('Profit');
            $table->string('Coupon')->nullable();
            $table->longText('Descriptions');
            $table->integer('Status')->default(0);
            $table->timestamps();
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
