<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentitemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sentitems', function (Blueprint $table) {
            $table->bigInteger('ID', 0, 1);
            $table->timestamp('UpdatedInDB')->nullable();
            $table->timestamp('InsertIntoDB')->nullable();
            $table->timestamp('SendingDateTime')->index()->nullable();
            $table->timestamp('DeliveryDateTime')->index()->nullable();
            $table->enum('Status', [
                    'SendingOK',
                    'SendingOKNoReport',
                    'SendingError',
                    'DeliveryOK',
                    'DeliveryFailed',
                    'DeliveryPending',
                    'DeliveryUnknown',
                    'Error',
                ])
                ->default('SendingOK');
            $table->integer('StatusError');
            $table->string('DestinationNumber', 20)->index();
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
            $table->string('SenderID')->index();
            $table->integer('SequencePosition')->default(1);
            $table->integer('TPMR')->default(-1)->index();
            $table->integer('RelativeValidity')->default(-1);
            $table->string('CreatorID');

            $table->primary(['ID', 'SequencePosition', 'TPMR']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('sentitems');
    }
}
