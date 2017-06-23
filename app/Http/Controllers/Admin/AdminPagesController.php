<?php

namespace App\Http\Controllers\Admin;

use App\Action;
use App\Card;
use App\CardGroups;
use App\EtcData;
use App\Fraction;
use App\League;
use App\MagicEffect;
use App\Page;
use App\Payment;
use App\Rubric;
use App\User;
use App\Http\Controllers\Admin\AdminViews;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class AdminPagesController extends BaseController
{
	//Фракции
	public function index(){
		$fractions = Fraction::orderBy('position','asc')->get();

		$output = [];
		foreach($fractions as $key => $fraction){
			$output[$key]['id']		= $fraction->id;
			$output[$key]['title']	= $fraction->title;
			$output[$key]['slug']	= $fraction -> slug;
			$output[$key]['type']	= AdminViews::fractionTypeToRus($fraction -> type);
			$output[$key]['img_url']= $fraction -> img_url;
			$output[$key]['created']= date('Y/m/d  H:i', strtotime($fraction->created_at));
			$output[$key]['updated']= date('Y/m/d  H:i', strtotime($fraction->updated_at));
		}
		return view('admin.main', ['fractions' => $output]);
	}


	public function fractionAddPage(){
		return view('admin.layouts.add.fraction');
	}

	public function fractionEditPage($id){
		$fraction = Fraction::find($id);

		return view('admin.layouts.edit.fraction', ['fraction' => $fraction]);
	}
	//END OF Фракции

	//Лиги
	public function leaguePage(){
		$leagues = League::orderBy('min_lvl')->get();
		return view('admin.leagues', ['leagues' => $leagues]);
	}
	//END OF Лиги

	//Базовые Карты
	public function baseDecksPage(){
		$fractions = \DB::table('tbl_fraction')
			->select('title','slug','type','cards','position')
			->where('type', '=', 'race')
			->orderBy('position','asc')
			->get();

		$output = [];

		foreach($fractions as $key => $fraction){
			$output[$key]['title'] = $fraction->title;
			$output[$key]['slug'] = $fraction->slug;

			$fraction_deck = unserialize($fraction->cards);
			$deck_view = '';

			foreach($fraction_deck as $deck){
				$card_weigth = \DB::table('tbl_cards')->select('id','card_value')->where('id','=',$deck['id'])->get();
				$card_weigth = $card_weigth[0]->card_value * $deck['q'];

				$deck_view .= '<tr>
					<td><a href="#" class="drop"></a></td>
					<td>'.AdminViews::getAllCardsSelectorView($deck['id']).'</td>
					<td><input type="number" name="currentQuantity" value="'.$deck['q'].'"></td>
					<td>'.$card_weigth.'</td>
				</tr>';
			}

			$output[$key]['deck'] = $deck_view;
		}

		return view('admin.base_decks', ['fractions' => $output]);
	}
	//END OF Базовые Карты

	//Соотношение обменов
	public function exchangesPage(){
		$exchange = EtcData::where('label_data', '=', 'exchange_options')->get();
		return view('admin.exchanges', ['exchange' => $exchange]);
	}
	//END OF Соотношение обменов

	//Покупка Премиума
	public function premiumPage(){
		$premium_options = EtcData::where('label_data', '=', 'premium_buing')->get();
		return view('admin.premium', ['premium_options' => $premium_options]);
	}
	//END OF Покупка Премиума

	//Настройки колод
	public function deckOptionsPage(){
		$deck_options = EtcData::where('label_data', '=', 'deck_options')->get();
		return view('admin.deck_options', ['deck_options' => $deck_options]);
	}
	//END OF Настройки колод

	//Базовые поля пользователей
	public function userBasicFieldsPage(){
		$base_user_fields = EtcData::where('label_data', '=', 'base_user_fields')->get();
		return view('admin.basic_fields',['base_user_fields' => $base_user_fields]);
	}
	//END OF Базовые поля пользователей

	//Тайминг боя
	public function battleTiming(){
		$timing_options = EtcData::where('label_data', '=', 'timing')->orderBY('id','asc')->get();
		return view('admin.timing', ['timing_options' => $timing_options]);
	}
	//END OF Тайминг боя

	//Карты
	public function cardsPage(Request $request){
		if( !empty($request -> all()) ){
			if(isset($request -> all()['race'])){
				$fraction_slug = $request -> all()['race'];
			}else{
				$fraction_slug = 'knight';
			}
		}else{
			$fraction_slug = 'knight';
		}
		if( ( $fraction_slug == 'special') || ($fraction_slug == 'neutrall') ){
			$field = 'card_type';
		}else{
			$field = 'card_race';
		}
		$fractions = Fraction::orderBy('position', 'asc')->get();
		$cards = Card::where($field, '=', $fraction_slug)
			->orderBy('title', 'asc')
			->orderBy('price_gold','asc')
			->orderBy('price_silver','asc')
			->get();

		$output = [];
		foreach($cards as $key => $card){

			$allowed_rows = AdminViews::getActionRows($card->allowed_rows);
			$in_group = AdminViews::getCardGroups($card->card_groups);
			$actions = AdminViews::getCardActions($card->card_actions);

			$output[$key]['id']			= $card->id;
			$output[$key]['title']		= $card->title;
			$output[$key]['img_url']	= $card->img_url;
			$output[$key]['card_strong']= $card->card_strong;
			$output[$key]['card_value']	= $card->card_value;
			$output[$key]['is_leader']	= $card->is_leader;
			$output[$key]['allowed_rows']= $allowed_rows;
			$output[$key]['groups']		= $in_group;
			$output[$key]['actions']	= $actions;
			$output[$key]['price_gold']	= $card->price_gold;
			$output[$key]['price_silver']= $card->price_silver;
			$output[$key]['created']	= date('Y/m/d  H:i', strtotime($card->created_at));
			$output[$key]['updated']	= date('Y/m/d  H:i', strtotime($card->updated_at));

		}
		return view('admin.cards', [
			'cards' => $output,
			'fractions' => $fractions,
			'fraction_slug' => $fraction_slug
		]);
	}

	public function cardAddPage(){
		$fractions = \DB::table('tbl_fraction')
			->select('title','slug', 'type', 'position')
			->where('type', '=', 'race')
			->orderBy('position','asc')
			->get();

		$card_actions = Action::orderBy('title','asc')->get();
		$card_groups = AdminViews::cardsViewGroupsSelector();
		return view('admin.layouts.add.cards', [
			'card_actions'	=> $card_actions,
			'card_groups'	=> $card_groups,
			'fractions'		=> $fractions
		]);
	}

	public function cardEditPage($id){
		$card = Card::find($id);
		$fractions = \DB::table('tbl_fraction')
			->select('title','slug', 'type', 'position')
			->where('type', '=', 'race')
			->orderBy('position','asc')
			->get();

		$card_current_actions = AdminViews::cardsViewGetCardActions($card->card_actions);
		$card_actions = Action::orderBy('title', 'asc')->get();

		$card_groups = AdminViews::cardsViewGroupsSelector();
		$card_current_groups = AdminViews::cardsViewGetCardGroups($card->card_groups, $type='table');

		return view('admin.layouts.edit.cards', [
			'card'					=> $card,
			'card_actions'			=> $card_actions,
			'card_current_actions'	=> $card_current_actions,
			'card_groups'			=> $card_groups,
			'card_current_groups'	=> $card_current_groups,
			'fractions'				=> $fractions
		]);
	}
	//END OF Карты

	//Группы
	public function cardGroupsPage(){
		$card_groups = CardGroups::orderBy('title', 'asc')->get();

		$output = [];
		foreach($card_groups as $key => $group){
			$output[$key]['id']		= $group->id;
			$output[$key]['title']	= $group->title;
			$output[$key]['slug']	= $group->slug;
			$output[$key]['cards']	= AdminViews::cardsViewCardsList($group->id, 'link');
			$output[$key]['created']= date('Y/m/d  H:i', strtotime($group->created_at));
			$output[$key]['updated']= date('Y/m/d  H:i', strtotime($group->updated_at));
		}
		return view('admin.card_groups', ['card_groups' => $output]);
	}

	public function cardGroupsAddPage(){
		$cards = AdminViews::getAllCardsSelectorView();
		return view('admin.layouts.add.card_groups', ['cards' => $cards]);
	}

	public function cardGroupsEditPage($id){
		$group = CardGroups::find($id);
		$cards = AdminViews::getAllCardsSelectorView();
		$current_cards = AdminViews::cardsViewCardsList($group->id, 'table');
		return view('admin.layouts.edit.card_groups', [
			'group' => $group,
			'cards' => $cards,
			'current_cards' => $current_cards
		]);
	}
	//END OF Группы

	//Магия
	public function magicPage(){
		$magic = MagicEffect::orderBy('fraction','asc')->orderBy('energy_cost','asc')->get();
		$output=[];
		foreach($magic as $key => $value){
			$fraction = \DB::table('tbl_fraction')->select('title','slug')->where('slug','=',$value->fraction)->get();
			$actions = AdminViews::getCardActions($value->effect_actions);
			$league = AdminViews::getLeagueById($value->min_league);

			$output[$key]['id']			= $value->id;
			$output[$key]['title']		= $value->title;
			$output[$key]['img_url']	= $value->img_url;
			$output[$key]['fraction']	= $fraction[0]->title;
			$output[$key]['description']= $value->description;
			$output[$key]['actions']	= $actions;
			$output[$key]['price_gold']	= $value->price_gold;
			$output[$key]['price_silver']= $value->price_silver;
			$output[$key]['energy_cost']= $value->energy_cost;
			$output[$key]['league']		= $league;
			$output[$key]['created']	= date('Y/m/d  H:i', strtotime($value->created_at));
			$output[$key]['updated']	= date('Y/m/d  H:i', strtotime($value->updated_at));
		}
		return view('admin.magic', ['magic' => $output]);
	}

	public function magicAddPage(){
		$fractions = \DB::table('tbl_fraction')
			->select('title','slug','type','position')
			->where('type','=','race')
			->orderBy('position','asc')
			->get();

		$actions = Action::orderBy('title','asc')->get();
		$leagues = \DB::table('tbl_league')->select('id','title','min_lvl','max_lvl')->orderBy('min_lvl')->get();
		return view('admin.layouts.add.magic', [
			'fractions'	=> $fractions,
			'actions'	=> $actions,
			'leagues'	=> $leagues
		]);
	}

	public function magicEditPage($id){
		$magic = MagicEffect::find($id);
		$fractions = \DB::table('tbl_fraction')
			->select('title','slug','type','position')
			->where('type','=','race')
			->orderBy('position','asc')
			->get();

		$actions = Action::orderBy('title','asc')->get();
		$current_actions = AdminViews::cardsViewGetCardActions($magic->effect_actions, 'magic');
		$leagues = \DB::table('tbl_league')->select('id','title','min_lvl','max_lvl')->orderBy('min_lvl')->get();
		return view('admin.layouts.edit.magic', [
			'magic'		=> $magic,
			'fractions'	=> $fractions,
			'actions'	=> $actions,
			'current_actions' => $current_actions,
			'leagues'	=> $leagues
		]);
	}
	//END OF Магия

	//Действия
	public function actionsPage(){
		$actions = Action::orderBy('title', 'asc')->get();
		$output = [];
		foreach($actions as $key => $action) {
			$html_options = unserialize($action->html_options);
			$html = '';
			for($i = 0; $i < count($html_options); $i++){
				$html .='<p>'.$html_options[$i][0].'</p>'.$html_options[$i][1].'<hr>';
			}

			$output[$key]['id']			= $action->id;
			$output[$key]['title']		= $action->title;
			$output[$key]['slug']		= $action->slug;
			$output[$key]['description']= $action->description;
			$output[$key]['created']	= date('Y/m/d  H:i', strtotime($action->created_at));
			$output[$key]['updated']	= date('Y/m/d  H:i', strtotime($action->updated_at));
			$output[$key]['html_options']= $html;
		}
		return view('admin.actions', ['actions' => $output]);
	}

	public function actionAddPage(){
		return view('admin.layouts.add.actions');
	}

	public function actionEditPage($id){
		$action = Action::find($id);
		return view('admin.layouts.edit.actions', ['action' => $action]);
	}
	//END OF Действия

	//Пользователи
	public function usersPage(){
		$users = User::orderBy('login','asc')->get();
		$output = [];
		foreach($users as $key => $user){
			$img = ($user -> img_url == '')
				? 'Изображение отсутствует'
				: '<img src="/img/user_images/'.$user -> img_url.'" alt="" style="max-width: 50px; max-height: 50px;">';
			$ban = ($user -> is_banned == 0)
				? '<a data-type="banUser" id="'.$user->id.'" href="#">Забанить</a>'
				: '<a data-type="unbanUser" id="'.$user->id.'" href="#">Снять бан</a>';
			$admin_status = ($user->user_role == 0)
				? 'Простой смертный'
				: 'Администратор';

			$output[$key]['id']		= $user -> id;
			$output[$key]['login']	= $user -> login;
			$output[$key]['email']	= $user -> email;
			$output[$key]['name']	= $user -> name;
			$output[$key]['img_url']= $img;
			$output[$key]['gold']	= $user -> user_gold;
			$output[$key]['silver']	= $user -> user_silver;
			$output[$key]['energy']	= $user -> user_energy;
			$output[$key]['created']= date('Y/m/d  H:i', strtotime($user->created_at));
			$output[$key]['updated']= date('Y/m/d  H:i', strtotime($user->updated_at));
			$output[$key]['ban']	= $ban;
			$output[$key]['admin_status'] = $admin_status;
		}
		return view('admin.users', ['users' => $output]);
	}

	public function editUser($id){
		$user = User::find($id);
		$payments = Payment::where('user_id','=',$user['id'])->orderBy('updated_at','desc')->get();
		return view('admin.layouts.edit.user', ['user' => $user, 'payments'=>$payments]);
	}
	//END OF Пользователи

	//Страницы
	public function viewPages(){
		$pages = Page::orderBy('title','asc')->get();

		$first_editable = (isset($pages[0]))? $pages[0]: ['title'=>'', 'slug'=>[], 'text'=>''];

		return view('admin.pages', ['pages'=>$pages, 'first_editable'=>$first_editable]);
	}

	public function viewEditablePage(Request $request){
		$data = $request->all();
		$pages = Page::where('slug','=',$data['slug'])->get();
		return json_encode([
			'title'	=> $pages[0]->title,
			'slug'	=> $pages[0]->slug,
			'text'	=> $pages[0]->text
		]);
	}

	public function editPage(Request $request){
		$data = $request->all();
		$result = Page::where('slug','=',$data['slug'])->update([
			'title'	=> $data['title'],
			'text'	=> $data['text']
		]);
		return 'success';
	}

	public function supportPage(){
		$rubrics = Rubric::orderBy('position','asc')->orderBy('title','asc')->get();
		$emails_list = EtcData::select('label_data', 'meta_key', 'meta_key_title')
			->where('label_data','=','support')
			->where('meta_key','=','emails')
			->get();
		return view('admin.support', [
			'rubrics' => $rubrics,
			'emails_list' => unserialize($emails_list[0]->meta_key_title)
		]);
	}
	public function supportAddRubric(Request $request){
		$data = $request->all();
		$slug = AdminFunctions::str2url($data['title']).'_'.uniqid();
		$result = Rubric::create([
			'title'	=> $data['title'],
			'slug'	=> $slug
		]);
		if($result){
			return json_encode(['message'=>'success', 'id'=>$result->id]);
		}
	}

	public function supportEditRubric(Request $request){
		$data = $request->all();
		$result = Rubric::find($data['id']);
		$result -> title = $data['title'];
		$result -> save();
		if($result){
			return 'success';
		}
	}

	public function supportDropRubric(Request $request){
		$data = $request->all();
		$result = Rubric::find($data['id']);
		$result -> delete();
		if($result){
			return 'success';
		}
	}

	public function supportAddEmail(Request $request){
		$data = $request->all();
		$emails = EtcData::select('label_data', 'meta_key', 'meta_key_title')
			->where('label_data','=','support')
			->where('meta_key','=','emails')
			->get();
		$emails = unserialize($emails[0]->meta_key_title);
		$emails[] = $data['email'];
		$emails = serialize(array_values(array_unique($emails)));
		$result = EtcData::where('label_data','=','support')
			->where('meta_key','=','emails')
			->update(['meta_key_title'=>$emails]);
		if($result){
			return 'success';
		}
	}

	public function supportEditEmail(Request $request){
		$data = $request->all();
		$emails = EtcData::select('label_data', 'meta_key', 'meta_key_title')
			->where('label_data','=','support')
			->where('meta_key','=','emails')
			->get();
		$emails = unserialize($emails[0]->meta_key_title);
		$emails[$data['iter']] = $data['email'];
		$emails = serialize(array_values(array_unique($emails)));
		$result = EtcData::where('label_data','=','support')
			->where('meta_key','=','emails')
			->update(['meta_key_title'=>$emails]);
		if($result){
			return 'success';
		}
	}

	public function supportDropEmail(Request $request){
		$data = $request->all();
		$emails = EtcData::select('label_data', 'meta_key', 'meta_key_title')
			->where('label_data','=','support')
			->where('meta_key','=','emails')
			->get();
		$emails = unserialize($emails[0]->meta_key_title);
		unset($emails[$data['iter']]);
		$emails = serialize(array_values(array_unique($emails)));
		$result = EtcData::where('label_data','=','support')
			->where('meta_key','=','emails')
			->update(['meta_key_title'=>$emails]);
		if($result){
			return 'success';
		}
	}

	public function supportChangeRubricPosition(Request $request){
		$data = $request->all();
		foreach($data['rubrics'] as $position => $id){
			$result = Rubric::select('id','position')->find($id);
			$result ->position = $position;
			$result ->save();
		}
		return 'success';
	}

	public function usersPayment(){
		$payments = Payment::where('pay_status','=',1)->orderBy('updated_at','desc')->get();
		return view('admin.payments', ['payments' => $payments]);
	}
	//END OF Страницы
}