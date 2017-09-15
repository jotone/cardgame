<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Modules extends Model{
	protected $table = 'tbl_modules';
	protected $fillable = [
		'title','slug','description','options'
	];
}