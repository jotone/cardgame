<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_page_content', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('caption');
            $table->string('type');
            $table->text('content');
            $table->integer('refer_to')->unsigned();
            $table->integer('module_id')->unsigned();
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
        Schema::drop('tbl_page_content');
    }
}
