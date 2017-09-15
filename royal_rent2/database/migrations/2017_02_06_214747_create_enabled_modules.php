<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnabledModules extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_enabled_modules', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->boolean('unique_slug');
			$table->integer('type')->unsigned();
			$table->text('description');
			$table->text('disabled_fields');
			$table->text('custom_fields');
			$table->smallInteger('position')->unsigned();
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
		Schema::drop('tbl_enabled_modules');
	}
}
