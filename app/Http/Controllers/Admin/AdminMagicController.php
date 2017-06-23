<?php

namespace App\Http\Controllers\Admin;

use \App\MagicEffect;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminMagicController extends BaseController
{
    public function addMagic(Request $request){
        $data = $request->all();

        if($data['title'] == ''){
            return 'Не указано название карты.';
        }
        //создание ссылки для магического еффекта
        $slug = AdminFunctions::str2url($data['title']);

        $img_file  = AdminFunctions::createImg($data['img_url'], 'card_images');

        $magic_actions = serialize(json_decode($data['magic_actions'])); //Массив Действий волшебства

        $result = MagicEffect::create([
            'title'         => $data['title'],
            'slug'          => $slug,
            'img_url'       => $img_file,
            'description'   => $data['description'],
            'min_league'    => $data['min_league'],
            'energy_cost'   => $data['energyCost'],
            'price_gold'    => $data['price_gold'],
            'price_silver'  => $data['price_silver'],
            'usage_count'   => $data['usage_count'],
            'effect_actions'=> $magic_actions,
            'fraction'      => $data['race']
        ]);

        if($result !== false){
            return 'success';
        }
    }

    public function editMagic(Request $request){
        $data = $request->all();

        if($data['title'] == ''){
            return 'Не указано название карты.';
        }

        $magic = MagicEffect::find($data['id']);

        //создание ссылки для магического еффекта
        $slug = AdminFunctions::str2url($data['title']);

        $img_file  = AdminFunctions::createImg($data['img_url'], 'card_images');
        if(empty($img_file)) $img_file = $magic->img_url;

        $magic_actions = serialize(json_decode($data['magic_actions'])); //Массив Действий волшебства

        $magic->title          = $data['title'];
        $magic->slug           = $slug;
        $magic->img_url        = $img_file;
        $magic->description    = $data['description'];
        $magic->min_league     = $data['min_league'];
        $magic->energy_cost    = $data['energyCost'];
        $magic->price_gold     = $data['price_gold'];
        $magic->price_silver   = $data['price_silver'];
        $magic->usage_count    = $data['usage_count'];
        $magic->fraction       = $data['race'];
        $magic->effect_actions = $magic_actions;

        //Применяем изменения
        $result = $magic -> save();

        if($result !== false){
            return 'success';
        }
    }

    public function dropMagic(Request $request){
        $dropMagicEffect = MagicEffect::find($request->input('effect_id'));
        $result = $dropMagicEffect -> delete();
        if($result !== false){
            return redirect(route('admin-magic'));
        }
    }
}