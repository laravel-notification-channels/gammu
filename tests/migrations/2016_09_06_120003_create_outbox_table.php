<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutboxTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('outbox', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->timestamp('UpdatedInDB')->nullable();
            $table->timestamp('InsertIntoDB')->nullable();
            $table->timestamp('SendingDateTime')->nullable();
            $table->timestamp('SendingTimeOut')->nullable();
            $table->time('SendBefore')->default('23:59:59');
            $table->time('SendAfter')->default('00:00:00');
            $table->string('DestinationNumber', 20);
            $table->enum('Coding', [
                    'Default_No_Compression',
                    'Unicode_No_Compression',
                    '8bit',
                    'Default_Compression',
                    'Unicode_Compression',
                ])
                ->default('Default_No_Compression');
            $table->string('UDH', 12)->nullable();
            $table->integer('Class')->default(-1);
            $table->text('Text')->nullable();
            $table->text('TextDecoded');
            $table->enum('MultiPart', [
                    'true', 'false',
                ])
                ->default('false');
            $table->integer('RelativeValidity')->default(-1);
            $table->string('SenderID')->index();
            $table->enum('DeliveryReport', [
                    'default', 'yes', 'no',
                ])
                ->default('default');
            $table->text('CreatorID');
            $table->integer('Retries')->default(1);
            $table->index(['SendingDateTime', 'SendingTimeOut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('outbox');
    }
}
