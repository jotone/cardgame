<?php
namespace App\Http\Controllers\Admin;

use \App\League;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminLeagueController extends BaseController
{
    //Сохранение в БД данных о лигах
    protected function leagueApply(Request $request){
        $data = $request -> all();

        //Входящий массив лиг
        $leagues = json_decode($data['leagueData']);

        foreach($leagues as $iter => $league_data){
            $data_to_save = [];
            foreach($league_data as $field_iter =>$field_data){
                foreach($field_data as $field_name => $value){
                    if(empty($value)) $value = 0;
                    $data_to_save[$field_name] = $value;
                }
            }
            $data_to_save['slug'] = '_'.AdminFunctions::str2url($data_to_save['title']);

            if(!isset($data_to_save['leagueId'])){
                $result = League::create($data_to_save);
            }else{
                $result = League::find($data_to_save['leagueId']);
                foreach($data_to_save as $field => $value){
                    if($field != 'leagueId'){
                        $result -> $field = $value;
                    }
                    $result -> save();
                }
            }
        }

        if($result != false){
            return 'success';
        }
    }

    //Удалить лигу
    protected function leagueDrop(Request $request){
        $data = $request -> all();
        $result = League::find($data['leagueId']);
        $result -> delete();
        return redirect(route('admin-main'));
    }
}