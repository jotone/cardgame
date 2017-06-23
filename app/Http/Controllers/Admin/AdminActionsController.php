<?php
namespace App\Http\Controllers\Admin;

use \App\Action;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminActionsController extends BaseController
{
    public function addAction(Request $request){
        $data = $request->all();
        //функция транслитеризации см. app\http\AdminFunctions.php
        $slug = AdminFunctions::str2url($data['title']);

        //массив характеристик действий карты
        $charac = array();

        /*
        * если существует входящий массив характеристик приводим его к виду:
        * array(
        *  [порядковый номер характеристики]=>[описание][html]
        * )
        */
        if(isset($data['characteristics'])){
            $characteristics = json_decode($data['characteristics']);
            for($i=0; $i<count($characteristics); $i++){
                if($i%2 == 1){
                    $charac[] = array($characteristics[$i-1], $characteristics[$i]);
                }
            }
        }

        //превращаем массив в строку
        $charac = serialize($charac);

        //заносим в БД
        $result = Action::create([
            'title'         => $data['title'],
            'slug'          => $slug,
            'description'   => $data['description'],
            'html_options'  => $charac
        ]);

        //если действие занесено в БД, передаем в AJAX запрос success
        if($result !== false){
            return 'success';
        }
    }

    public function editAction(Request $request){
        $data = $request->all();

        //Находим в БД редактируемое действие карты
        $editedAction = Action::find($data['id']);
        if(!empty($editedAction)){

            //функция транслитеризации см. app\http\AdminFunctions.php
            $slug = AdminFunctions::str2url($data['title']);

            //массив характеристик действий карты
            $charac = array();

            /*
            * если существует входящий массив характеристик приводим его к виду:
            * array(
            *  [порядковый номер характеристики]=>[описание][html]
            * )
            */

            if(isset($data['characteristics'])){
                $characteristics = json_decode($data['characteristics']);
                for($i=0; $i<count($characteristics); $i++){
                    if($i%2 == 1){
                        $charac[] = array($characteristics[$i-1], $characteristics[$i]);
                    }
                }
            }

            $charac = serialize($charac);

            //Изменение данных
            $editedAction->title        = $data['title'];
            $editedAction->slug         = $slug;
            $editedAction->description  = $data['description'];
            $editedAction->html_options = $charac;

            //Сохранение в БД
            $result = $editedAction->save();
            if($result !== false){
                return 'success';
            }
        }
    }

    public function dropAction(Request $request){
        $dropAction = Action::find($request->input('adm_id'));
        $result = $dropAction -> delete();
        if($result !== false){
            return redirect(route('admin-actions'));
        }
    }
}