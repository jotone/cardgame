<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CardGroups extends Model{
    protected $table = 'tbl_card_groups';
    protected $fillable = ['title', 'slug'];
}
