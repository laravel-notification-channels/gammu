<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePbkTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pbk', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('GroupID')
                ->default(-1);
            $table->string('Name'); //->text('Name');
            $table->string('Number', 20); //->text('Number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('pbk');
    }
}
