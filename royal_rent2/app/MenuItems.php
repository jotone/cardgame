<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MenuItems extends Model{
	protected $table = 'tbl_menu_items';
	protected $fillable = [
		'title','slug','custom_fields',
		'position','refer_to','module_id','enabled','active'
	];
}