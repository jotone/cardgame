<?php

namespace App\Http\Controllers\Admin;

use \App\Card;
use \App\CardGroups;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminCardsController extends BaseController
{
    public function addCard(Request $request){
        $data = $request->all();
        if($data['title'] == ''){
            return 'Не указано название карты.';
        }
        $slug = AdminFunctions::str2url($data['title']);

        $img_file  = AdminFunctions::createImg($data['img_url'], 'card_images');

        $card_actions = serialize(json_decode($data['card_actions'])); //Массив Действий карты

        //Если карта "специальная" - создаем список рас которым запрещено пользоваться данной картой
        $card_type_forbidden_race_deck = serialize(json_decode($data['card_type_forbidden_race_deck']));

        //Если карта "рассовая" - указываем к какой расе она принадлежит
        $card_race = ('race' == $data['card_type'])? $data['card_race']: '';

        //Карта действует на ряд
        $card_action_row = serialize(json_decode($data['card_action_row']));

        $card_refer_to_group = json_decode($data['card_refer_to_group']);

        //Карта-лидер
        if($data['card_is_leader'] == 'false'){
            $card_is_leader = 0;
        }else{
            $card_is_leader = 1;
        }

        //Заносим карту в БД
        $result = Card::create([
            'title'             => $data['title'],
            'slug'              => $slug,
            'card_type'         => $data['card_type'],
            'card_race'         => $card_race,
            'forbidden_races'   => $card_type_forbidden_race_deck,
            'allowed_rows'      => $card_action_row,
            'card_strong'       => $data['card_strenght'],
            'card_value'        => $data['card_weight'],
            'is_leader'         => $card_is_leader,
            'img_url'           => $img_file,
            'card_actions'      => $card_actions,
            'card_groups'       => serialize($card_refer_to_group),
            'max_quant_in_deck' => $data['card_max_num_in_deck'],
            'short_description' => $data['short_descr'],
            'full_description'  => $data['full_descr'],
            'price_gold'        => $data['card_gold_price'],
            'price_silver'      => $data['card_silver_price'],
        ]);

        //Если карта успешно записана в БД
        if($result !== false){
            return 'success';
        }else{
            return 'Не удалось записать карту в базу.';
        }
    }

    public function editCard(Request $request){
        $data = $request->all();

        $card = Card::find($data['id']);
        if($data['title'] == ''){
            return 'Не указано название карты.';
        }
        $slug = AdminFunctions::str2url($data['title']);

        $img_file  = AdminFunctions::createImg($data['img_url'], 'card_images');
        if(empty($img_file)) $img_file = $card->img_url;
        $card_actions = serialize(json_decode($data['card_actions'])); //Массив Действий карты

        //Если карта "специальная" - создаем список рас которым запрещено пользоваться данной картой
        $card_type_forbidden_race_deck = serialize(json_decode($data['card_type_forbidden_race_deck']));

        //Если карта "рассовая" - указываем к какой расе она принадлежит
        $card_race = ('race' == $data['card_type'])? $data['card_race']: '';

        //Карта действует на ряд
        $card_action_row = serialize(json_decode($data['card_action_row']));

        $card_refer_to_group = json_decode($data['card_refer_to_group']);

        //Карта-лидер
        if($data['card_is_leader'] == 'false'){
            $card_is_leader = 0;
        }else{
            $card_is_leader = 1;
        }

        $card->title            = $data['title'];
        $card->slug             = $slug;
        $card->card_type        = $data['card_type'];
        $card->card_race        = $card_race;
        $card->forbidden_races  = $card_type_forbidden_race_deck;
        $card->allowed_rows     = $card_action_row;
        $card->card_strong      = $data['card_strenght'];
        $card->card_value       = $data['card_weight'];
        $card->is_leader        = $card_is_leader;
        $card->img_url          = $img_file;
        $card->card_actions     = $card_actions;
        $card->card_groups      = serialize($card_refer_to_group);
        $card->max_quant_in_deck= $data['card_max_num_in_deck'];
        $card->short_description= $data['short_descr'];
        $card->full_description = $data['full_descr'];
        $card->price_gold       = $data['card_gold_price'];
        $card->price_silver     = $data['card_silver_price'];
        $card->save();

        //Если карта успешно записана в БД
        if($card !== false){
            return 'success';
        }else{
            return 'Не удалось записать карту в базу.';
        }
    }

    public function dropCard(Request $request){
        //Находим по id
        $dropCard = Card::find($request->input('card_id'));
        //Удаляем
        $result = $dropCard -> delete();

        if($result !== false){
            return redirect(route('admin-cards'));
        }else{
            return 'Не удалось удалить карту из базы.';
        }
    }
}