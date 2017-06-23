<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleMembers extends Model{
    protected $table = 'tbl_battle_members';
    protected $fillable = [
        'user_id', 'battle_id', 'user_deck_race', 'available_to_change', 'user_deck', 'user_hand', 'magic_effects',
        'user_energy', 'user_ready', 'round_passed', 'player_source', 'card_source', 'user_discard', 'card_to_play',
        'addition_data', 'turn_expire', 'time_shift'
    ];
}