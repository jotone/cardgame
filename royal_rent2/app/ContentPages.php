<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class ContentPages extends Model{
	protected $table = 'tbl_site_pages';
	protected $fillable = [
		'title','slug','img_url','description','text','custom_fields',
		'meta_title','meta_description','meta_keywords',
		'module_id','author','views','enabled','published_at'
	];
}