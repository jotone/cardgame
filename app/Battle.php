<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Battle extends Model{
    protected $table = 'tbl_battles';
    protected $fillable = [
        'creator_id', 'opponent_id', 'players_quantity', 'deck_weight', 'league', 'fight_status', 'user_id_turn',
        'first_turn_user_id', 'round_count', 'round_status', 'battle_field', 'undead_cards', 'magic_usage', 'turn_expire',
        'disconected_count', 'pass_count'
    ];
}