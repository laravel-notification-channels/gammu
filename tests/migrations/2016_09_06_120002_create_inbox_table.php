<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInboxTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('inbox', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->timestamp('UpdatedInDB')->nullable();
            $table->timestamp('ReceivingDateTime')->nullable();
            $table->string('SenderNumber', 20);
            $table->enum('Coding', [
                    'Default_No_Compression',
                    'Unicode_No_Compression',
                    '8bit',
                    'Default_Compression',
                    'Unicode_Compression',
                ])
                ->default('Default_No_Compression');
            $table->string('UDH', 12);
            $table->string('SMSCNumber', 20);
            $table->integer('Class')->default(-1);
            $table->text('Text');
            $table->text('TextDecoded');
            $table->string('RecipientID');
            $table->enum('Processed', [
                    'true', 'false',
                ])
                ->default('false');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('inbox');
    }
}
