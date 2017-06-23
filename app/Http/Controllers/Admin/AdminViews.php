<?php
namespace App\Http\Controllers\Admin;

use App\Action;
use App\Card;
use App\CardGroups;
use Illuminate\Routing\Controller as BaseController;

class AdminViews extends BaseController
{

    public static function cardsViewGroupsList(){
        //Выборка из БД групп по названию; сортировка алфавитная
        $groups_data = CardGroups::orderBy('title','asc')->get();

        return json_encode($groups_data->all());
    }

    public static function cardsViewGroupsSelector(){
        $groups = \DB::table('tbl_card_groups')->select('id','title')->orderBy('title','asc')->get();
        $result = '';
        foreach($groups as $group_data){
            $result .= '<option value="'.$group_data->id.'">'.$group_data->title.'</option>';
        }
        return $result;
    }

    protected static function createCardSelectOptions($cards_array, $id = ''){
        $result = '';
        foreach($cards_array as $card){
            if($id == $card->id){
                $selected = 'selected="selected"';
            }else{
                $selected = '';
            }
            $result .= '<option value="'.$card->id.'" '.$selected.'>'.$card->title.' (сила '.$card->card_strong.'; вес '.$card->card_value.')</option>';
        }

        return $result;
    }

    protected  static function createViewFraction($fractions){
        $result = '';
        foreach($fractions as $key => $value){
            $fraction = \DB::table('tbl_fraction')->select('slug','title')->where('slug', '=', $value)->get();
            $result .= $fraction[0]->title.', ';
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Функция вывода карт в группе (для admin/crads/groups)  ($card_id - id карты, $type - тип вывода)
    public static function cardsViewCardsList($group_id, $type ='link'){

        $result = '';

        $cards = \DB::table('tbl_cards')->select('id', 'title', 'card_groups')->where('card_groups','!=','a:0:{}')->get();

        foreach($cards as $card_iter => $card_data){

            $card_group = unserialize($card_data->card_groups);
            if(in_array($group_id, $card_group)){
                if($type == 'link'){
                    $result .= '<a href="/admin/card/edit/'.$card_data->id.'">'.$card_data->title.'</a> ';
                }

                if($type == 'table'){
                    $result .= '
                    <tr>
                        <td><a class="drop" href="#"></a></td>
                        <td>'.$card_data->title.'</td>
                        <td style="display: none;">'.$card_data->id.'</td>
                    </tr>
                    ';
                }
            }
        }

        return $result;
    }

    //Функция вывода списка групп карты ($card_groups - группы карты, $type - тип вывода)
    public static function cardsViewGetCardGroups($card_groups, $type='link'){
        if(!is_array($card_groups)) $card_groups = unserialize($card_groups);

        $result = '';
        foreach($card_groups as $group_id){
            $group = CardGroups::find($group_id);
            if($group !== false){
                if($type == 'link'){
                    $result .= '<a href="/admin/cards/groups/edit/'.$group->id.'">'.$group->title.'</a> ';
                }

                if($type == 'table'){
                    $result .= '
                    <tr>
                        <td>
                            <a class="drop" href="#"></a>
                        </td>
                        <td>'.$group->title.'</td>
                        <td style="display: none;">'.$group->id.'</td>
                    </tr>
                    ';
                }
            }
        }
        return $result;
    }

    public static function fractionTypeToRus($fraction){
        switch($fraction){
            case 'race':    return 'Рассовые карты'; break;
            case 'special': return 'Специальные карты'; break;
            case 'neutrall':return 'Нейтральные карты'; break;
            default: return 'Неизвестно';
        }
    }

    //Функция возвращает селектор всех карт
    public static function getAllCardsSelectorView($id=''){
        $out = '<select name="currentCard">';
        $cards_type = \DB::table('tbl_cards')->select('card_type')->groupBy('card_type')->get();

        foreach($cards_type as $type){
            switch($type->card_type){
                case 'race':
                    $current_card_type = 'Рассовые';
                    $result = '';

                    $cards_race = Card::where('card_type', '=', 'race')->groupBy('card_race')->get();
                    foreach($cards_race as $card_race){
                        $fraction = \DB::table('tbl_fraction')
                            ->select('slug', 'title')
                            ->where('slug', '=', $card_race->card_race)
                            ->get();

                        $result .= '<optgroup label="'.$fraction[0]->title.'">';
                        $cards_by_races = \DB::table('tbl_cards')
                            ->select('id','title','card_type','card_race','card_strong','card_value')
                            ->where('card_type', '=', 'race')
                            ->where('card_race', '=', $card_race->card_race)
                            ->orderBy('title','asc')
                            ->get();

                        $result .= self::createCardSelectOptions($cards_by_races, $id);
                    }
                    break;

                case 'neutrall':
                    $current_card_type = 'Нейтральные';
                    $result = '';

                    $cards_to_view = \DB::table('tbl_cards')
                        ->select('id','title','card_type','card_race','card_strong','card_value')
                        ->where('card_type','=',$type->card_type)
                        ->orderBy('title','asc')
                        ->get();

                    $result .= self::createCardSelectOptions($cards_to_view, $id);
                    break;

                case 'special':
                    $current_card_type = 'Специальные';
                    $result = '';

                    $cards_to_view = \DB::table('tbl_cards')
                        ->select('id','title','card_type','card_race','card_strong','card_value')
                        ->where('card_type','=',$type->card_type)
                        ->orderBy('title','asc')
                        ->get();

                    $result .= self::createCardSelectOptions($cards_to_view, $id);
                    break;

            }
            $out .= '<optgroup label="'.$current_card_type.'">'.$result.'</optgroup>';

        }
        $out .= '</select>';
        return $out;
    }

    public static function getAllCardsSelector(){
        return '
            <tr>
                <td><a href="#" class="drop"></a></td>
                <td>'.self::getAllCardsSelectorView().'</td>
                <td><input name="currentQuantity" type="number" value="1" min="1"></td>
                <td></td>
            </tr>
        ';
    }


    public static function getActionRows($action_rows){
        $action_rows = unserialize($action_rows);
        $result = '';
        foreach($action_rows as $row){
            switch($row){
                case '0': $result .= 'Ближний. '; break;
                case '1': $result .= 'Дальний. '; break;
                case '2': $result .= 'Сверхдальний. '; break;
            }
        }
        return $result;
    }

    public static function getCardActions($actions){
        $actions = unserialize($actions);
        $result = '';

        foreach ($actions as $key => $value) {
            $action = \DB::table('tbl_actions')->select('id','title')->where('id', '=', $value->action)->get();
            $result .= $action[0]->title.'; ';
        }

        return substr($result, 0, -2);
    }

    public static function getCardGroups($groups){
        $groups = unserialize($groups);
        $result = '';
        foreach($groups as $group){
            $group_data = \DB::table('tbl_card_groups')->select('id','title')->where('id','=',$group)->get();
            $result .= '<p><a href="/admin/card/groups/edit/'.$group_data[0]->id.'">'.$group_data[0]->title.'</a></p>';
        }
        return $result;
    }



    //Функция возвращает список действий карты
    public static function cardsViewGetCardActions($actions, $type=''){
        $actions = unserialize($actions);

        $result = '';

        foreach($actions as $action){

            $current_action = Action::where('id', '=', $action->action)->get();
            $result .= '
            <tr>
                <td><a class="drop" href="#"></a></td>
                <td>
                    <ins>'.$current_action[0]['title'].'</ins>:<br>
            '; // Вывод названия действия


            //Бессмертный
            if(isset($action -> deadless_backToDeck)){
                $result .= (0 == $action -> deadless_backToDeck) ? ' - Возвращается На поле;<br>': ' - Возвращается В руку;<br>';
            }

            //Боевое Братство
            if(isset($action -> brotherhood_actionToGroupOrSame)){
                $result .= (0 == $action -> brotherhood_actionToGroupOrSame) ? ' - Дейстует на одинаковые;<br>' : ' - Действует на группу: '. self::createActionGroups($action -> brotherhood_actionToGroupOrSame);
                $result .= ' - Умножает силу в '.$action->brotherhood_strenghtMult.' раз;<br>';
            }

            //Воодушевление
            if(isset($action -> inspiration_ActionRow)){
                $result .= ' - Дальность: '. self::createActionsRowRange($action -> inspiration_ActionRow);
                $result .= ' - Умножает силу в: '.$action -> inspiration_multValue.'<br>';
                $result .= ' - Игнорирует полный иммунитет: ';
                $result .= (0 == $action -> inspiration_ignoreImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //Иммунитет
            if(isset($action -> immumity_type)){
                $result .= ' - Тип иммунитета: ';
                $result .= (0 == $action -> immumity_type) ? 'Простой' : 'Полный';
            }

            //Лекарь
            if(isset($action -> healer_typeOfCard)){
                $result .= ' - Тип карты: ';
                switch($action->healer_typeOfCard){
                    case '0':
                        $result .= 'Определенные карты<br> - Карты: ';
                        foreach($action->healer_type_singleCard as $i => $card_id){
                            $card_title = \DB::table('tbl_cards')->select('id','title')->where('id','=',$card_id)->get();
                            $result .= $card_title[0]->title.', ';
                        }
                        $result .= '<br>';
                        break;
                    case '1':
                        $result .= 'Карта относится к ряду: ';
                        $result .= self::createActionsRowRange($action->healer_type_actionRow);
                        break;
                    case '2':
                        $result .= (0 == $action->healer_type_cardType) ? 'Специальная карта' : 'Карта воина';
                        $result .= '<br>';
                        break;
                    case '3':
                        $result .= '- Группы: '.self::createActionGroups($action -> healer_type_group);
                        break;
                    default: $result .= 'Любая<br>';
                }
                $result .= ' - Способ выбора: ';
                $result .= (0 == $action->healer_cardChoise) ? 'Вручную<br>': 'Случайно<br>';
                $result .= ' - Играть карту из колоды: ';
                $result .= (0 == $action->healer_deckChoise) ? 'Своей<br>': 'Противника<br>';
                $result .= ' - Игнорирует полный иммунитет: ';
                $result .= (0 == $action -> healer_ignoreImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //Неистовство
            if(isset($action -> fury_enemyRace)){
                $result .= ' - Если противник имеет рассу: '. self::createViewFraction($action -> fury_enemyRace);

                if(!empty($action -> fury_group)){
                    $result .= ' - Противник имеет карту из группы: '. self::createActionGroups($action -> fury_group);
                }

                if(0 != $action -> fury_enemyWarriorsCount){
                    $result .= ' - Противник имеет воинов в количестве: '.$action -> fury_enemyWarriorsCount.' в ряду: '.self::createActionsRowRange($action -> fury_ActionRow);
                }

                if(isset($action -> fury_strenghtVal)){
                    $result .= ' - Повышает силу на: '.$action -> fury_strenghtVal.' единиц<br>';
                }
                if(isset($action -> fury_abilityCastEnemy)){
                    $result .= ' - Противник использовал способность: ';
                    if($action -> fury_abilityCastEnemy == 0) {
                        $result .= 'Нет';
                    }else{
                        $result .= 'Да';
                    }
                }
            }

            //Одурманивание
            if(isset($action -> obscure_ActionRow)){
                $result .= ' - Действует на ряд: '.self::createActionsRowRange($action -> obscure_ActionRow);
                $result .= ' - Количество перетягиваемых карт: '.$action -> obscure_quantityOfCardToObscure.'<br>';
                $result .= ' - Максимальная сила карты которую можно перетянуть: '.$action -> obscure_maxCardStrength.'<br>';
                $result .= ' - Сила перетягиваемой карты: ';
                switch($action -> obscure_strenghtOfCard){
                    case '0': $result .= 'Слабую<br>'; break;
                    case '1': $result .= 'Сильную<br>'; break;
                    case '2': $result .= 'Случайно<br>'; break;
                }
                $result .= ' - Игнорирует полный иммунитет: ';
                $result .= (0 == $action -> obscure_ignoreImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //перегруппировка
            if(isset($action->regroup_ignoreImmunity)){
                $result .= ' - Игнорирует полный иммунитет: ';
                $result .= (0 == $action -> regroup_ignoreImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //Печаль
            if(isset($action -> sorrow_ActionRow)){
                $result .= ' - Действует на ряд: '.self::createActionsRowRange($action -> sorrow_ActionRow);

                $result .= ' - Действует на своих: ';
                $result .= (0 == $action -> sorrow_actionTeamate) ? 'Нет<br>': 'Да<br>';
            }

            //Повелитель
            if(isset($action -> master_group)){
                $result .= ' - Группа карт, которые будут призываться: '.self::createActionGroups($action -> master_group);

                $result .= ' - Карты берутся из: ';
                foreach($action -> master_cardSource as $source){
                    switch($source){
                        case 'hand':    $result .= 'Рука, '; break;
                        case 'discard': $result .= 'Отбой, '; break;
                        case 'deck':    $result .= 'Колода, '; break;
                    }
                }
                $result = substr($result, 0, -2).'<br>';

                $result .= ' - Призывать карту: ';
                switch($action -> master_summonByModificator){
                    case '0': $result .= 'Слабую<br>'; break;
                    case '1': $result .= 'Сильную<br>'; break;
                    case '2': $result .= 'Случайно<br>'; break;
                }
                $result .= ' - Макс. количество карт, которое призывается: '. $action -> master_maxCardsSummon.'<br>';
                $result .= ' - Макс. значение силы карт, которые призываются: '. $action -> master_maxCardsStrenght;
            }

            //Поддержка
            if(isset($action -> support_ActionRow)){
                $result .= ' - Повысить силу в ряду: '. self::createActionsRowRange($action -> support_ActionRow);
                $result .= (0 == $action -> support_actionToGroupOrAll)? ' - Дейстует на всех<br>': ' - Действует на группу: '. self::createActionGroups($action -> support_actionToGroupOrAll);
                $result .= ' - Повышение силы действует на себя: ';
                $result .= (0 == $action -> support_selfCast) ? 'Нет<br>': 'Да<br>';
                $result .= ' - Значение повышения силы: '. $action -> support_strenghtValue.' единиц';
            }

            //Подсмотреть карты
            if(isset($action -> overview_cardCount)){
                $result .= '- Количество карт: '.$action -> overview_cardCount;
            }

            //Призыв
            if(isset($action -> summon_typeOfCard)){
                $result .= ' - Тип карты: ';
                switch($action->summon_typeOfCard){
                    case '0':
                        $result .= 'Определенные карты<br> - Карты: ';
                        foreach($action->summon_type_singleCard as $i => $card_id){
                            $card_title = \DB::table('tbl_cards')->select('id','title')->where('id','=',$card_id)->get();
                            $result .= $card_title[0]->title.', ';
                        }
                        $result .= '<br>';
                        break;
                    case '1':
                        $result .= 'Карта относится к ряду: ';
                        $result .= self::createActionsRowRange($action->summon_type_actionRow);
                        break;
                    case '2':
                        $result .= (0 == $action->summon_type_cardType) ? 'Специальная карта' : 'Карта воина';
                        $result .= '<br>';
                        break;
                    case '3':
                        $result .= '- Группы: '.self::createActionGroups($action -> summon_type_group);
                        break;
                    default: $result .= 'Любая<br>';
                }
                $result .= ' - Способ выбора: ';
                $result .= (0 == $action->summon_cardChoise) ? 'Вручную<br>': 'Случайно<br>';
                $result .= ' - Играть карту из колоды: ';
                $result .= (0 == $action->summon_deckChoise) ? 'Своей<br>': 'Противника<br>';
                $result .= ' - Игнорирует полный иммунитет: ';
                $result .= (0 == $action -> summon_ignoreImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //Сброс карт и поднятие из колоды
            if(isset($action->dropAndPick_dropCount)){
                $result .= ' - Сколько карт сбросить: '.$action->dropAndPick_dropCount.'<br>';
                $result .= ' - Сколько карт поднять: '.$action->dropAndPick_pickCount.'<br>';
                $result .= ' - Способ выбора карты: ';
                $result .= (0 == $action->dropAndPick_cardChoise) ? 'Вручную<br>': 'Случайно<br>';
                $result .= ' - Игнорирует полный иммунитет: ';
                $result .= (0 == $action -> dropAndPick_ignoreImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //Сброс карт противника в отбой\
            if(isset($action->enemyDropHand_cardCount)){
                $result .= '- Количество карт: '.$action -> enemyDropHand_cardCount;
            }

            //Страшный
            if(isset($action -> fear_enemyRace)){
                $result .= ' - Не действует на рассу: '. self::createViewFraction($action -> fear_enemyRace);
                $result .= (0 == $action->fear_actionToGroupOrAll)?' - Действует на всех<br>':' - Действует на группу: '.self::createActionGroups($action -> fear_actionToGroupOrAll);
                $result .= ' - Ряд действия: '. self::createActionsRowRange($action -> fear_ActionRow);

                $result .= ' - Действует на своих: ';
                $result .= (0 == $action -> fear_actionTeamate)? 'Нет<br>': 'Да<br>';

                $result .= ' - Значение понижения силы: '. $action -> fear_strenghtValue;
            }

            //Убийца
            if(isset($action -> killer_ActionRow)){
                $result .= ' - Ряд действия: '.self::createActionsRowRange($action -> killer_ActionRow);
                $result .= ' - Действует на своих: ';
                $result .= (0 == $action -> killer_atackTeamate)?'Нет<br>':'Да<br>';
                $result .= 'Выбрать для убийства карту: ';
                switch($action->killer_killedQuality_Selector){
                    case '0': $result .= 'Самая слабая<br>'; break;
                    case '1': $result .= 'Самая сильная<br>'; break;
                    case '2': $result .= 'Случайно<br>'; break;
                }
                if(0 != $action ->killer_recomendedTeamateForceAmount_OnOff){
                    $result .= ' - Количество силы необходимое для совершения убийства воинов: '. $action -> killer_recomendedTeamateForceAmount_OnOff;
                    $result .= ' -> Ряд подсчета: '.self::createActionsRowRange($action -> killer_recomendedTeamateForceAmount_ActionRow);
                    switch($action -> killer_recomendedTeamateForceAmount_Selector){
                        case '0': $result .= ' (Больше указанного значения)<br>'; break;
                        case '1': $result .= ' (Меньше указанного значения)<br>'; break;
                        case '2': $result .= ' (Равно указанному значению)<br>'; break;
                    }
                }
                $result .= ' - Порог силы воинов противника для совершения убийства: '.$action -> killer_enemyStrenghtLimitToKill.'<br>';
                $result .= ' - На кого действует карта: ';
                $result .= (0 == $action ->killer_killAllOrSingle)?'Одиночная<br>':'Всех<br>';
                $result .= ' - Действует на группу: '.self::createActionGroups($action -> killer_group);
                $result .= ' - Игнорирует иммунитет: ';
                $result .= (0 == $action -> killer_ignoreKillImmunity) ? 'Нет<br>': 'Да<br>';
            }

            //Шпион
            if(isset($action -> spy_getCardsCount)){
                $result .= ' - Поле игрока: ';
                $result .= (0 == $action -> spy_fieldChoise) ? 'Своё<br>': 'Противника<br>';
                $result .= ' - Плучить из колоды '.$action -> spy_getCardsCount.' карт';
            }

            $result .='
                </td>
                <td style="display: none;">'.json_encode($action).'</td>
            </tr>
            ';
        }

        return $result;
    }

    //Создание списка групп для cardsViewGetCardActions
    protected static function createActionGroups($action){
        $groups_data = CardGroups::orderBy('title', 'asc')->get();

        $result = '';

        foreach($action as $action_group){
            foreach ($groups_data as $group) {
                if($action_group == $group['id']){
                    $result .= $group['title'].', ';
                }
            }
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Создание списка дальности действия карты/действия для cardsViewGetCardActions
    protected static function createActionsRowRange($action){
        $result = '';
        foreach ($action as $range) {
            switch($range){
                case '0': $result.= 'Ближний; '; break;
                case '1': $result.= 'Дальний; '; break;
                case '2': $result.= 'Сверхдальний; '; break;
            }
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Функция создает список действий карты для admin/cards
    public static function cardsViewCurrentCardActions($actions){

        $actions = unserialize($actions);
        $result = '';

        foreach ($actions as $key => $value) {
            $current_action = \DB::table('tbl_actions')->select('id','title')->find($value->action);
            $result .= $current_action->title.', ';
        }

        return substr($result, 0, -2);
    }

    public static function getLeagueById($id){
        if($id != 0){
            $league = \DB::table('tbl_league')->select('id', 'title')->where('id', '=', $id)->get();
            if($league){
                return $league[0]->title;
            }else{
                return 'error';
            }
        }else{
            return 'Все';
        }
    }
}