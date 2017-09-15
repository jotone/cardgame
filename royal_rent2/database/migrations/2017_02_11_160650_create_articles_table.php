<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->text('img_url');
            $table->text('description');
            $table->string('text_caption');
            $table->text('text');
            $table->text('custom_fields');

            $table->string('meta_title');
            $table->string('meta_description');
            $table->string('meta_keywords');

            $table->integer('module_id')->unsigned();
            $table->string('author');
            $table->integer('views')->unsigned();
            $table->boolean('enabled');
            $table->timestamp('published_at');
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
        Schema::drop('tbl_articles');
    }
}
