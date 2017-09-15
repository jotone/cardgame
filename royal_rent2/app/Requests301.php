<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Requests301 extends Model{
	protected $table = 'tbl_requests';
	protected $fillable = [
		'link_from','link_to'
	];
}