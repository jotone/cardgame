<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Fraction extends Model{
    protected $table = 'tbl_fraction';
    protected $fillable = [
        'title', 'slug', 'img_url', 'bg_img', 'card_img', 'type', 'description', 'short_description', 'position', 'cards',
        'descr_shop','descr_magic'
    ];
}