<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRoles extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_user_roles', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('pseudonim');
			$table->boolean('editable');
			$table->text('access_pages');
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
		Schema::drop('tbl_user_roles');
	}
}
