<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Products extends Model{
	protected $table = 'tbl_products';
	protected $fillable = [
		'title','slug','img_url','description','text','price','color','custom_fields',
		'meta_title','meta_description','meta_keywords',
		'module_id','author','views','enabled','published_at'
	];
}