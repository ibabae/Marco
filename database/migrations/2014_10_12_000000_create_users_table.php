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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname')->nullable();
            $table->string('email')->unique()->nullable();
            $table->bigInteger('phone')->unique();
            $table->string('password');
            $table->string('cname')->nullable();
            $table->integer('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->text('address2')->nullable();
            $table->bigInteger('zipcode')->nullable();
            $table->integer('status')->default(1);
            $table->integer('role')->default(0);
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
        Schema::dropIfExists('users');
    }
};
