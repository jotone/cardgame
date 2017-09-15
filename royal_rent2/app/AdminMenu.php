<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class AdminMenu extends Model{
	protected $table = 'tbl_admin_menu';
	protected $fillable = [
		'title','slug','position','refer_to','module_id','enabled'
	];
}