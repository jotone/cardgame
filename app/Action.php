<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model{
    protected $table = 'tbl_actions';
    protected $fillable = [
        'title', 'slug', 'description', 'html_options'
    ];
}