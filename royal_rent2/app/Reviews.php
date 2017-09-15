<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Reviews extends Model{
	protected $table = 'tbl_reviews';
	protected $fillable = [
		'user_id','name','text','custom_fields','rating','refer_to','module_id','associate_with'
	];
}