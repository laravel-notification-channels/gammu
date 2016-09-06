<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutboxMultipartTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('outbox_multipart', function (Blueprint $table) {
            $table->bigInteger('ID')->unsigned();
            $table->enum('Coding', [
                    'Default_No_Compression',
                    'Unicode_No_Compression',
                    '8bit',
                    'Default_Compression',
                    'Unicode_Compression',
                ])
                ->default('Default_No_Compression');
            $table->string('UDH', 12);
            $table->integer('Class')->default(-1);
            $table->text('Text');
            $table->text('TextDecoded');
            $table->integer('SequencePosition')->default(2);
            $table->primary(['ID', 'SequencePosition', 'UDH']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('outbox_multipart');
    }
}
