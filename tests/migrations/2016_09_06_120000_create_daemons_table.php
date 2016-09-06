<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDaemonsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('daemons', function (Blueprint $table) {
            $table->text('Start');
            $table->text('Info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('daemons');
    }
}
