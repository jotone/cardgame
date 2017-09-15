<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class UserRoles extends Model{
	protected $table = 'tbl_user_roles';
	protected $fillable = [
		'title','pseudonim','editable','access_pages'
	];
}