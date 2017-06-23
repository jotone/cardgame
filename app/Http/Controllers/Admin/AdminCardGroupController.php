<?php

namespace App\Http\Controllers\Admin;

use \App\CardGroups;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminCardGroupController extends BaseController
{
    public function addCardGroup(Request $request){
        $data = $request->all();
        if(empty($data['title'])){
            return 'Не указано название группы';
        }

        $slug = AdminFunctions::str2url($data['title']);

        $cards = array_unique(json_decode($data['cards']));
        $result = CardGroups::create([
            'title' => $data['title'],
            'slug'  => $slug,
        ]);


        if($result !== false){
            foreach($cards as $i => $card_id){
                $card_data = \DB::table('tbl_cards')->select('id','card_groups')->where('id','=',$card_id)->get();
                $card_groups = unserialize($card_data[0]->card_groups);
                $card_groups[] = $result['id'];
                $card_groups = serialize(array_values(array_unique($card_groups)));
                \DB::table('tbl_cards')->where('id','=',$card_id)->update(['card_groups' => $card_groups]);
            }
            return 'success';
        }else{
            return 'Не удалось записать группу в БД';
        }
    }

    public function editCardGroup(Request $request){
        $data = $request->all();
        if(empty($data['title'])){
            return 'Не указано название группы';
        }

        $slug = AdminFunctions::str2url($data['title']);

        $cards = array_unique(json_decode($data['cards']));

        $group = CardGroups::find($data['id']);
        $group->title = $data['title'];
        $group->slug  = $slug;
        $group->save();
        if($group !== false){
            foreach($cards as $i => $card_id){
                $card_data = \DB::table('tbl_cards')->select('id','card_groups')->where('id','=',$card_id)->get();
                $card_groups = unserialize($card_data[0]->card_groups);
                $card_groups[] = $group->id;
                $card_groups = serialize(array_values(array_unique($card_groups)));
                \DB::table('tbl_cards')->where('id','=',$card_id)->update(['card_groups' => $card_groups]);
            }
            return 'success';
        }else{
            return 'Не удалось записать группу в БД';
        }
    }

    public function dropCardGroup(Request $request){
        //Находим по id
        $dropGroup = CardGroups::find($request->input('group_id'));
        //Удаляем
        $result = $dropGroup -> delete();

        if($result !== false){
            $cards = \DB::table('tbl_cards')->select('id','card_groups')->where('card_groups','!=','a:0:{}')->get();
            foreach($cards as $card_iter => $card_data) {
                $card_groups = unserialize($card_data->card_groups);
                $group_key = array_search($request->input('group_id'), $card_groups);
                unset($card_groups[$group_key]);
                \DB::table('tbl_cards')->where('id','=',$card_data->id)->update(['card_groups' => serialize($card_groups)]);
            }

            return redirect(route('admin-card-groups'));
        }else{
            return 'Не удалосьудалить группу';
        }

    }
}