<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MagicEffect extends Model{
    protected $table = 'tbl_magic_effect';
    protected $fillable = ['title', 'slug', 'img_url', 'description', 'energy_cost', 'price_gold', 'price_silver', 'usage_count', 'effect_actions', 'min_league', 'fraction'];
}