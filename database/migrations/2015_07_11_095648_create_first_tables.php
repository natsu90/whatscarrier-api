<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirstTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Device', function ($table) {

            $table->increments('id');
            $table->string('platform');
            $table->string('udid');
            $table->string('country_code');
            $table->string('push_token');
            $table->unsignedInteger('credits')->default(5);
            $table->timestamps();
        });

        Schema::create('Product', function($table) {

            $table->string('id');
            $table->unsignedInteger('credits');
        });

        Schema::create('Receipt', function($table) {

            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->string('product_id');
            $table->string('price');
            $table->string('transaction_id');
            $table->text('transaction');
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
        Schema::drop('Device');
        Schema::drop('Product');
        Schema::drop('Receipt');
    }
}
