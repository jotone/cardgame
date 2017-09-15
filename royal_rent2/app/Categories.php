<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Categories extends Model{
	protected $table = 'tbl_categories';
	protected $fillable = [
		'title','slug','img_url','description','text','custom_fields',
		'position','refer_to','module_id','author','enabled'
	];
}