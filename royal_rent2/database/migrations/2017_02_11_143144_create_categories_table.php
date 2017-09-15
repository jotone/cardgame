<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->text('img_url');
            $table->text('description');
            $table->text('text');
            $table->text('custom_fields');

            $table->smallInteger('position')->unsigned();
            $table->integer('refer_to')->unsigned();
            $table->integer('module_id')->unsigned();
            $table->string('author');

            $table->boolean('enabled');
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
        Schema::drop('tbl_categories');
    }
}
