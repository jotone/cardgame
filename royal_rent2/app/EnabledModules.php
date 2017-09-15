<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class EnabledModules extends Model{
	protected $table = 'tbl_enabled_modules';
	protected $fillable = [
		'title','slug','unique_slug','type','description',
        'disabled_fields','custom_fields','position','enabled'
	];
}