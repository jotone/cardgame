<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Articles extends Model{
	protected $table = 'tbl_articles';
	protected $fillable = [
		'title','slug','img_url','description','text_caption','text','custom_fields',
		'meta_title','meta_description','meta_keywords',
		'module_id','author','views','enabled','published_at'
	];
}