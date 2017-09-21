<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SummaryLeague extends Model
{
	protected $table = 'tbl_summary';
	protected $fillable = [
		'league','knight','forest','highlander','cursed','undead','monsters'
	];
}