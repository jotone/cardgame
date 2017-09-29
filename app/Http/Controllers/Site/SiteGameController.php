<?php
namespace App\Http\Controllers\Site;

use App\Battle;
use App\BattleMembers;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
class SiteGameController extends BaseController
{

	public function createRoom(Request $request){
		SiteFunctionsController::updateConnention();
		$data = $request->all();
		$user = Auth::user();

		//Силиа колоды
		$deck_weight = Crypt::decrypt($data['deck_weight']);
		//Лига
		$league = Crypt::decrypt($data['league']);

		$user_settings = self::battleGetUserSettings($user);

		Battle::where('opponent_id','=',0)->where('creator_id','=',$user->id)->delete();
		$battle = Battle::create([
			'creator_id'		=> $user->id,
			'players_quantity'	=> $data['players'],
			'deck_weight'		=> $deck_weight,
			'league'			=> $league,
			'fight_status'		=> 0,
			'user_id_turn'		=> 0,
			'round_count'		=> 1,
			'round_status'		=> serialize(['p1'=>[], 'p2'=>[]]),
			'battle_field'		=> serialize([
				'p1'=>[
					'0'	=> ['special' => [], 'warrior' => []],
					'1'	=> ['special' => [], 'warrior' => []],
					'2'	=> ['special' => [], 'warrior' => []]
				],
				'p2'=>[
					'0'	=> ['special' => [], 'warrior' => []],
					'1'	=> ['special' => [], 'warrior' => []],
					'2'	=> ['special' => [], 'warrior' => []]
				],
				'mid'=>[]
			]),
			'undead_cards'		=> serialize(['p1'=>[], 'p2'=>[]]),
			'magic_usage'		=> serialize(['p1'=>[], 'p2'=>[]]),
			'disconected_count'	=> 2,
			'pass_count'		=> 0
		]);

		if($battle == false){
			return json_encode(['message' => 'Не удалось создать стол']);
		}

		//Создание данных об участниках битвы
		$battle_members = self::updateBattleMembers(
			$user->id,
			$battle->id,
			$user->user_current_deck,
			$user_settings['deck'],
			$user_settings['magic_effects'],
			$user->user_energy,
			$league
		);

		if($battle_members == false){
			$dropBattle = Battle::find($battle->id);
			$dropBattle -> delete();
			return json_encode(['message' => 'Не удалось создать настройки стола']);
		}

		BattleMembers::where('user_id', '=', $user['id'])->update(['user_ready' => 0]);

		//Отмечаем что пользователь уже играет
		\DB::table('users')->where('id','=',$user->id)->update(['user_busy' => 1]);

		if($battle_members !== false){
			return redirect(route('user-in-game', ['game' => $battle->id]));
		}
	}

	protected static function battleGetUserSettings($user){
		//Карты текущей колоды
		$user_deck = unserialize($user->user_cards_in_deck)[$user->user_current_deck];

		//Активное волшебство пользователя
		$user_magic = [];
		$magic_effects = unserialize($user->user_magic);

		foreach ($magic_effects as $key => $value){
			if($value['active'] == 1){
				$current_magic_effect = \DB::table('tbl_magic_effect')->select('id','fraction')->where('id', '=', $key)->first();
				if($user->user_current_deck == $current_magic_effect->fraction){
					$user_magic[$key] = $value['used_times'];
				}
			}
		}

		return ['deck' => $user_deck, 'magic_effects' => $user_magic];
	}

	//Изменение данных пользовотеля об участии в столах
	protected static function updateBattleMembers($user_id, $battle_id, $user_deck_race, $user_deck, $user_magic, $user_energy, $league){
		//Создание массива всех карт в колоде по отдельности (без указания колличества)
		$real_card_array = [];
		foreach ($user_deck as $card_id => $cards_quantity){
			for($i = 0; $i<$cards_quantity; $i++){
				$card_isset = \DB::table('tbl_cards')->where('id','=',$card_id)->count();
				if($card_isset > 0){
					$real_card_array[] = $card_id;
				}
			}
		}

		$maxHandCardQuantity = \DB::table('tbl_etc_data')
			->select('meta_key','meta_value')
			->where('meta_key','=','maxHandCardQuantity')
			->first();
		//Карты руки пользователя
		$user_hand = [];
		//Количество карт в колоде
		$deck_card_count = count($real_card_array);

		//Создание массива карт руки (случайный выбор)
		while(count($user_hand) != $maxHandCardQuantity -> meta_value){
			$rand_item = mt_rand(0, $deck_card_count-1);		//Случайный индекс карты колоды
			$user_hand[] = $real_card_array[$rand_item];		//Перенос карты в колоду руки
			unset($real_card_array[$rand_item]);				//Убираем данную карту из колоды
			$real_card_array = array_values($real_card_array);	//Пересчет колоды
			$deck_card_count = count($real_card_array);
		}

		$available_to_change = ($user_deck_race == 'highlander')? 4: 2;

		$league_data = \DB::table('tbl_league')
			->select('slug', 'min_lvl')
			->where('slug', '=', '_'.AdminFunctions::str2url($league))
			->first();

		$magic_to_use = [];

		foreach($user_magic as $magic_id => $magic_q) {
			$magic_info = \DB::table('tbl_magic_effect')
				->select('id', 'min_league')
				->where('id', '=', $magic_id)
				->first();

			if($magic_info->min_league == 0){
				$weight = 0;
			}else{
				$magic_in_league = \DB::table('tbl_league')
					->select('id', 'min_lvl')
					->where('id', '=', $magic_info->min_league)
					->first();

				$weight = $magic_in_league->min_lvl;
			}
			if(($weight <= $league_data->min_lvl) && ($magic_q > 0)){
				$magic_to_use[$magic_id] = $magic_q;
			}
		}

		$user_is_battle_member = \DB::table('tbl_battle_members')->select('user_id')->where('user_id','=',$user_id)->count();

		$battle_member_arr = [
			'battle_id'			=> $battle_id,
			'user_deck_race'	=> $user_deck_race,
			'available_to_change'=> $available_to_change,
			'user_deck'			=> serialize($real_card_array),
			'user_hand'			=> serialize($user_hand),
			'user_discard'		=> 'a:0:{}',
			'magic_effects'		=> serialize($magic_to_use),
			'user_energy'		=> $user_energy,
			'user_ready'		=> 0,
			'round_passed'		=> 0,
			'player_source'		=> '',
			'card_source'		=> 'hand',
			'card_to_play'		=> 'a:0:{}',
			'addition_data'		=> 'a:0:{}',
			'round_passed'		=> 0
		];

		//Если пользователя не сучествует в табице tbl_battle_members
		if($user_is_battle_member){
			$result = BattleMembers::where('user_id','=',$user_id)->update($battle_member_arr);
		}else{
			$battle_member_arr['user_id'] = $user_id;
			$result = BattleMembers::create($battle_member_arr);
		}
		return $result;
	}


	public function userConnectToRoom(Request $request){
		SiteFunctionsController::updateConnention();
		$data = $request->all();

		$user = Auth::user();
		//Данные о столе
		$battle_data = Battle::find($data['id']);
		if(empty($battle_data)){
			return json_encode(['message' => 'Данный стол уже занят.']);
		}

		if($battle_data->creator_id == $user['id']){
			return json_encode(['message' => 'success']);
		}

		$battle_data->opponent_id = $user['id'];
		$battle_data->save();

		//Если стол уже занят
		$users_count_in_battle = BattleMembers::where('battle_id', '=', $battle_data->id)->count();
		if($users_count_in_battle >= $battle_data->players_quantity) {
			return json_encode(['message' => 'success']);
		}

		$user_settings = self::battleGetUserSettings($user);

		$battle_members = self::updateBattleMembers(
			$user->id,
			$battle_data->id,
			$user->user_current_deck,
			$user_settings['deck'],
			$user_settings['magic_effects'],
			$user->user_energy,
			$battle_data->league
		);

		if ($battle_members === false) {
			return json_encode(['message' => 'Не удалось подключится к столу.']);
		}

		//Отмечаем что пользователь уже играет
		\DB::table('users')->where('id','=',$user->id)->update(['user_busy' => 1]);

		return json_encode(['message' => 'success']);
	}

	public function startGame(Request $request){
		$data = $request->all();

		$user = Auth::user(); //Данные текущего пользователя

		$battle_members = BattleMembers::where('battle_id', '=', $data['battle_id'])->get(); //Данные текущей битвы

		$users_result_data = [];

		foreach($battle_members as $key => $value){
			$user_in_battle = \DB::table('users')
				->select('id','login','img_url','user_current_deck')
				->where('id', '=', $value -> user_id)
				->first();// Пользователи участвующие в битве

			$current_user_deck_race = \DB::table('tbl_fraction')
				->select('title', 'slug', 'short_description','card_img')
				->where('slug','=', $value -> user_deck_race)
				->first(); //Название колоды

			$user_current_deck = unserialize($value -> user_deck); //Карты колоды пользователя
			$user_current_hand = unserialize($value -> user_hand); //Карты руки пользователя

			//Если участник битвы - противник
			$deck = $hand = [];
			if($user->id != $user_in_battle->id){
				$deck_card_count = count($user_current_deck); //Колличелство карт колоды
			}else{
				$deck = self::buildCardDeck($user_current_deck); //Создание массива карт колоды
				$hand = self::buildCardDeck($user_current_hand); //Создание массива карт руки
				$deck_card_count = count($deck);//Колличелство карт колоды
			}

			//Магические эффекты пользователя (волшебство)
			$user_magic_effect_data = [];
			$magic_effects = unserialize($value->magic_effects);

			foreach($magic_effects as $id => $actions){
				$magic_effect_data = \DB::table('tbl_magic_effect')
					->select('id', 'title', 'slug', 'img_url', 'description', 'energy_cost')
					->where('id', '=', $id)
					->first();

				if(!empty($magic_effect_data)){
					$user_magic_effect_data[] = [
						'id'			=> Crypt::encrypt($id),
						'title'			=> $magic_effect_data->title,
						'slug'			=> $magic_effect_data->slug,
						'img_url'		=> $magic_effect_data->img_url,
						'energy_cost'	=> $magic_effect_data->energy_cost,
					];
				}
			}

			$users_result_data[$user_in_battle->login] = [
				'img_url'		=> $user_in_battle->img_url,
				'deck_slug'		=> $value->user_deck_race,
				'deck_title'	=> $current_user_deck_race->title,
				'deck_descr'	=> $current_user_deck_race->short_description,
				'deck'			=> $deck,
				'deck_count'	=> $deck_card_count,
				'hand'			=> $hand,
				'magic'			=> $user_magic_effect_data,
				'energy'		=> $value->user_energy,
				'ready'			=> $value->user_ready,
				'can_change_cards'=> $value->available_to_change,
				'current_deck'	=> $user_in_battle->user_current_deck,
				'deck_img'		=> $current_user_deck_race->card_img
			];
		}

		return json_encode([
			'message'	=> 'success',
			'userData'	=> $users_result_data,
		]);
	}


	protected static function buildCardDeck($deck){
		$result_array = [];
		foreach($deck as $i => $card){
			$result_array[$i] = BattleFieldController::cardData($card);
		}
		$result_array = array_values($result_array);

		usort($result_array, function($a, $b){
			$r = ($b['strength'] - $a['strength']);
			if($r !== 0) return $r;
			return strnatcasecmp($a['title'], $b['title']);
		});
		return $result_array;
	}

	protected function userReady(Request $request){
		//$data = $request->all();

		$user = Auth::user();

		$user_battle = \DB::table('tbl_battle_members')
			->select('id', 'user_id', 'battle_id', 'user_deck', 'user_hand')
			->where('user_id', '=', $user->id)
			->first(); //Данные текущей битвы пользователя

		$user_deck = unserialize($user_battle->user_deck);
		$user_hand = unserialize($user_battle->user_hand);

		$user_hand = self::buildCardDeck($user_hand);
		$user_deck = self::buildCardDeck($user_deck);
		$timing_settings = self::getTimingSettings();
		$expire_time = $timing_settings['step_time'];// + $data['time'];

		$users_result_data[$user->login] = [
			'deck_count'=> count($user_deck),
			'deck'		=> $user_deck,
			'hand'		=> $user_hand
		];

		\DB::table('tbl_battle_members')->where('user_id', '=', $user['id'])->update([
			'user_ready'	=> 1,
			'turn_expire'	=> $expire_time
		]);

		return json_encode($users_result_data);
	}

	public function socketSettings(){
		$user = Auth::user();
		$battle_member = \DB::table('tbl_battle_members')->select('battle_id','user_id')->where('user_id', '=', $user->id)->first();

		$turn_expire_time = \DB::table('tbl_etc_data')->select('meta_value')
			->where('label_data','=','timing')
			->where('meta_key','=','step_time')
			->first();

		return json_encode([
			'battle'	=> $battle_member->battle_id,
			'user'		=> $user->id,
			'hash'		=> md5(getenv('SECRET_MD5_KEY').$user->id),
			'dom'		=> getenv('APP_DOMEN_NAME'),
			'timeOut'	=> $turn_expire_time->meta_value,
		]);
	}

	public static function getTimingSettings(){
		$timing_settings = \DB::table('tbl_etc_data')
			->select('label_data', 'meta_key', 'meta_value')
			->where('label_data', '=', 'timing')
			->get();
		$result = [];
		foreach($timing_settings as $timing){
			$result[$timing->meta_key] = $timing->meta_value;
		}
		return $result;
	}
}