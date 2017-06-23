<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model{
    protected $table = 'tbl_cards';
    protected $fillable = [
        'title', 'slug', 'card_type', 'card_race', 'forbidden_races', 'allowed_rows',
        'card_strong', 'card_value', 'is_leader', 'img_url', 'card_actions',
        'card_groups', 'max_quant_in_deck', 'short_description', 'full_description',
        'price_gold', 'price_silver'
    ];
}