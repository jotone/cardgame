<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class League extends Model{
    protected $table = 'tbl_league';
    protected $fillable = [
        'title', 'slug', 'min_lvl', 'max_lvl', 'gold_per_win', 'gold_per_loose',
        'silver_per_win', 'silver_per_loose', 'rating_per_win', 'rating_per_loose',
        'prem_gold_per_win', 'prem_gold_per_loose',
        'prem_silver_per_win', 'prem_silver_per_loose', 'min_amount'
    ];
}