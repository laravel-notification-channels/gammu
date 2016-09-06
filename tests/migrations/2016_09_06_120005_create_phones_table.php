<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhonesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->string('ID')->index();
            $table->timestamp('UpdatedInDB')->nullable();
            $table->timestamp('InsertIntoDB')->nullable();
            $table->timestamp('TimeOut')->nullable();
            $table->enum('Send', ['yes', 'no'])->default('yes');
            $table->enum('Receive', ['yes', 'no'])->default('yes');
            $table->string('IMEI', 35)->primary();
            $table->text('Client');
            $table->integer('Battery')->default(-1);
            $table->integer('Signal')->default(-1);
            $table->integer('Sent')->default(0);
            $table->integer('Received')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('phones');
    }
}
