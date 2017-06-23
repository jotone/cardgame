<?php
namespace App\Http\Controllers\Admin;

use \App\EtcData;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminEtcDataController extends BaseController
{
    protected function saveToDB($data, $label_data){
        $result = array();
        //Для каждого входящего элемента обновляем значение в БД
        foreach($data as $key => $value) {
            if (($key != '_token') and ($key != '_method')) {
                $result[] = EtcData::where('label_data','=',$label_data)
                    ->where('meta_key', '=', $key)
                    ->update(['meta_value' => $value]);
            }
        }
        return $result;
    }

    //Соотношения обменов
    public function editExchanges(Request $request){
        $data = $request->all();
        $result = self::saveToDB($data, 'exchange_options');
        if(!in_array(false, $result, true)){
            return redirect(route('admin-exchanges'));
        }
    }

    //Настройки покупки премиума
    public function editPremium(Request $request){
        $data = $request->all();
        $result = self::saveToDB($data, 'premium_buing');
        if(!in_array(false, $result, true)){
            return redirect(route('admin-premium'));
        }
    }

    //Настройки колоды
    public function editDeckOptions(Request $request){
        $data = $request -> all();
        $result = self::saveToDB($data, 'deck_options');
        if(!in_array(false, $result, true)){
            return redirect(route('admin-deck-options'));
        }
    }

    //Базовые поля пользователей
    public function editBasicFieldsOptions(Request $request){
        $data = $request -> all();
        $result = self::saveToDB($data, 'base_user_fields');
        if(!in_array(false, $result, true)){
            return redirect(route('admin-user-fields'));
        }
    }

    //Тайминг боя
    public function editBattleTiming(Request $request){
        $data = $request -> all();
        $result = self::saveToDB($data, 'timing');
        if(!in_array(false, $result, true)){
            return redirect(route('admin-timing'));
        }
    }
}