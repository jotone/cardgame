<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Rubric extends Model{
    protected $table = 'tbl_support';
    protected $fillable = [
        'title', 'slug', 'emails'
    ];
}