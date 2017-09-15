<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class PageContent extends Model{
	protected $table = 'tbl_page_content';
	protected $fillable = [
		'title','caption','type','content',
		'refer_to','module_id'
	];
}