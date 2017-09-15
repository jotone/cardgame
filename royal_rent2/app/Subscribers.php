<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Subscribers extends Model{
    protected $table = 'tbl_subscribe';
    protected $fillable = [
        'email','etc_data'
    ];
}