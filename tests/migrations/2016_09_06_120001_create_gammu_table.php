<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGammuTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('gammu', function (Blueprint $table) {
            $table->integer('Version')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('gammu');
    }
}
