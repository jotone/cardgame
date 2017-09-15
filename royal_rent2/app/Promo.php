<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Promo extends Model{
	protected $table = 'tbl_promo';
	protected $fillable = [
		'title','slug','img_url','description','text','discount','date_start','date_finish','custom_fields',
		'meta_title','meta_description','meta_keywords',
		'module_id','author','views','enabled','published_at'
	];
}