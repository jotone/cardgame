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

		$result = Battle::create([
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
			'disconected_count'	=> 0,
			'pass_count'		=> 0
		]);

		if($result === false){
			return json_encode(['message' => 'Не удалось создать стол']);
		}

		//Создание данных об участниках битвы
		$battle_members = self::updateBattleMembers(
			$user->id,
			$result->id,
			$user->user_current_deck,
			$user_settings['deck'],
			$user_settings['magic_effects'],
			$user->user_energy,
			$league
		);

		if($battle_members === false){
			$dropBattle = Battle::find($result->id);
			$dropBattle -> delete();
			return json_encode(['message' => 'Не удалось создать настройки стола']);
		}

		BattleMembers::where('user_id', '=', $user['id'])->update(['user_ready' => 0]);

		//Отмечаем что пользователь уже играет
		\DB::table('users')->where('id','=',$user->id)->update(['user_busy' => 1]);

		if($battle_members !== false){
			return redirect(route('user-in-game', ['game' => $result->id]));
		}
	}

	public function userConnectToRoom(Request $request){
		SiteFunctionsController::updateConnention();
		$data = $request->all();

		$user = Auth::user();
		//Данные о столе
		$battle_data = Battle::find($data['id']);

		if($battle_data->creator_id == $user['id']){
			return json_encode(['message' => 'success']);
		}

		$battle_data->opponent_id = $user['id'];
		$battle_data->save();

		//Если стол не пользовательский

		$users_count_in_battle = BattleMembers::where('battle_id', '=', $battle_data->id)->count();

		//Если стол уже занят
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

	/*
	 * $user_id - ID текущего пользователя
	 * $user_deck - текущая колода пользователя
	 * $players_quantity - максимальное количествоигроков за столом
	 * $deck_weight - сила колоды
	 * $league - лига
	 * */
	protected static function battleGetUserSettings($user){
		//Карты текущей колоды
		$user_deck = unserialize($user->user_cards_in_deck)[$user->user_current_deck];

		//Активное волшебство пользователя
		$user_magic = [];
		$magic_effects = unserialize($user->user_magic);

		foreach ($magic_effects as $key => $value){
			if($value['active'] == 1){
				$current_magic_effect = \DB::table('tbl_magic_effect')->select('id','fraction')->where('id', '=', $key)->get();
				if($user->user_current_deck == $current_magic_effect[0]->fraction){
					$user_magic[$key] = $value['used_times'];
				}
			}
		}

		return ['deck' => $user_deck, 'magic_effects' => $user_magic];
	}

	protected static function buildCardDeck($deck){
		$result_array = [];
		foreach($deck as $key => $card_income){
			if(strlen($card_income['id']) > 11){
				$card_income['id'] = $card_income['id'];
			}
			if(count(array_keys($card_income) < 5)){
				$card_data = \DB::table('tbl_cards')
					->select('id','title','slug','card_type', 'card_race', 'is_leader', 'card_strong','img_url','short_description', 'allowed_rows', 'card_actions', 'card_groups')
					->where('id', '=', $card_income['id'])
					->get();

				if(isset($card_data[0])){
					$action_rows = SiteFunctionsController::createActionRowsArray($card_data[0]->allowed_rows);
					$actions = SiteFunctionsController::createActionsArray(unserialize($card_data[0]->card_actions));

					$fraction = ($card_data[0]->card_type == 'race')? $card_data[0]->card_race: $card_data[0]->card_type;

					$result_array[] = [
						'id'		=> $card_data[0]->id,
						'title'		=> $card_data[0]->title,
						'is_leader'	=> $card_data[0]->is_leader,
						'type'		=> $card_data[0]->card_type,
						'strength'	=> $card_data[0]->card_strong,
						'img_url'	=> $card_data[0]->img_url,
						'descript'	=> $card_data[0]->short_description,
						'fraction'	=> $fraction,
						'groups'	=> unserialize($card_data[0]->card_groups),
						'action_row'=> unserialize($card_data[0]->allowed_rows),
						'actions'	=> unserialize($card_data[0]->card_actions),
						'action_txt'=> $actions,
						'row_txt'	=> $action_rows,
					];
				}
			}
		}

		usort($result_array, function($a, $b){return ($b['strength'] - $a['strength']);});
		return $result_array;
	}

	public function startGame(Request $request){
		$data = $request->all();

		$user = Auth::user(); //Данные текущего пользователя

		$battle_members = BattleMembers::where('battle_id', '=', $data['battle_id'])->get(); //Данные текущей битвы

		$users_result_data = [];

		$time_shift = time() - $data['time'];
		\DB::table('tbl_battle_members')
			->where('battle_id', '=', $data['battle_id'])
			->where('user_id', '=', $user['id'])
			->update(['time_shift' => $time_shift]);

		foreach($battle_members as $key => $value){

			$user_in_battle = \DB::table('users')
				->select('id','login','img_url','user_current_deck')
				->where('id', '=', $value -> user_id)
				->get();// Пользователи участвующие в битве

			$current_user_deck_race = \DB::table('tbl_fraction')
				->select('title', 'slug', 'short_description','card_img')
				->where('slug','=', $value -> user_deck_race)
				->get(); //Название колоды

			$user_current_deck = unserialize($value -> user_deck); //Карты колоды пользователя
			$user_current_hand = unserialize($value -> user_hand); //Карты руки пользователя

			$hand = [];

			//Если участник битвы - противник
			if($user->id != $user_in_battle[0]->id){
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
					->get();

				if(isset($magic_effect_data[0])){
					$user_magic_effect_data[] = [
						'id'			=> $id,
						'title'			=> $magic_effect_data[0]->title,
						'slug'			=> $magic_effect_data[0]->slug,
						'img_url'		=> $magic_effect_data[0]->img_url,
						'description'	=> $magic_effect_data[0]->description,
						'energy_cost'	=> $magic_effect_data[0]->energy_cost,
					];
				}
			}

			$users_result_data[$user_in_battle[0]->login] = [
				'img_url'	=> $user_in_battle[0]->img_url,
				'deck_slug'	=> $value -> user_deck_race,
				'deck_title'=> $current_user_deck_race[0]->title,
				'deck_descr'=> $current_user_deck_race[0]->short_description,
				'deck'		=> [],
				'deck_count'=> $deck_card_count,
				'hand'		=> $hand,
				'magic'		=> $user_magic_effect_data,
				'energy'	=> $value -> user_energy,
				'ready'		=> $value -> user_ready,
				'can_change_cards'	=> $value->available_to_change,
				'current_deck'		=> $user_in_battle[0]->user_current_deck,
				'deck_img'	=> $current_user_deck_race[0]->card_img
			];
		}

		return json_encode([
			'message'	=> 'success',
			'userData'	=> $users_result_data
		]);
	}

	protected function userReady(Request $request){
		$data = $request->all();

		$user = Auth::user();

		$user_battle = \DB::table('tbl_battle_members')
			->select('id', 'user_id', 'battle_id', 'user_deck', 'user_hand')
			->where('user_id', '=', $user->id)
			->get(); //Данные текущей битвы пользователя

		$user_deck = unserialize($user_battle[0]->user_deck);
		$user_hand = unserialize($user_battle[0]->user_hand);

		$user_hand = self::buildCardDeck($user_hand);
		$user_deck = self::buildCardDeck($user_deck);
		$users_result_data[$user->login] = [
			'deck_count'=> count($user_deck),
			'deck'		=> $user_deck,
			'hand'		=> $user_hand
		];

		$timing_settings = SiteGameController::getTimingSettings();
		$expire_time = $data['time'] + $timing_settings['first_step_r1'];

		\DB::table('tbl_battle_members')->where('user_id', '=', $user->id)->update([
			'user_ready' => 1,
			'turn_expire' => $expire_time
		]);

		return json_encode($users_result_data);
	}


	public function socketSettings(){
		$user = Auth::user();
		$battle_member = \DB::table('tbl_battle_members')->select('battle_id','user_id')->where('user_id', '=', $user->id)->get();

		$turn_expire_time = \DB::table('tbl_etc_data')
			->select('label_data','meta_key','meta_value')
			->where('label_data','=','timing')
			->where('meta_key','=','max_step_time')->get();
		return json_encode([
			'battle'	=> $battle_member[0]->battle_id,
			'user'		=> $user->id,
			'hash'		=> md5(getenv('SECRET_MD5_KEY').$user->id),
			'dom'		=> getenv('APP_DOMEN_NAME'),
			'timeOut'	=> $turn_expire_time[0]->meta_value,
		]);
	}

	//Изменение данных пользовотеля об участии в столах
	protected static function updateBattleMembers($user_id, $battle_id, $user_deck_race, $user_deck, $user_magic, $user_energy, $league){
		//Создание массива всех карт в колоде по отдельности (без указания колличества)
		$real_card_array = [];
		foreach ($user_deck as $card_id => $cards_quantity){
			for($i = 0; $i<$cards_quantity; $i++){
				$card_isset = \DB::table('tbl_cards')->where('id','=',$card_id)->count();
				if($card_isset > 0){
					$real_card_array[] = ['id' => $card_id];
				}
			}
		}

		//Карты руки пользователя
		$user_hand = [];
		//Количество карт в колоде
		$deck_card_count = count($real_card_array);

		$maxHandCardQuantity = \DB::table('tbl_etc_data')
			->select('meta_key','meta_value')
			->where('meta_key','=','maxHandCardQuantity')
			->get();

		//Создание массива карт руки (случайный выбор)
		while(count($user_hand) != $maxHandCardQuantity[0] -> meta_value){
			$rand_item = mt_rand(0, $deck_card_count-1);   //Случайный индекс карты колоды
			$user_hand[] = $real_card_array[$rand_item];      //Перенос карты в колоду руки
			unset($real_card_array[$rand_item]);              //Убираем данную карту из колоды
			$real_card_array = array_values($real_card_array);      //Пересчет колоды
			$deck_card_count = count($real_card_array);
		}

		$available_to_change =($user_deck_race == 'highlander')? 4: 2;

		$league_data = \DB::table('tbl_league')
			->select('slug', 'min_lvl')
			->where('slug', '=', '_'.AdminFunctions::str2url($league))
			->get();

		$magic_to_use = [];

		foreach($user_magic as $magic_id => $magic_q) {
			$magic_info = \DB::table('tbl_magic_effect')
				->select('id', 'min_league')
				->where('id', '=', $magic_id)
				->get();

			if ($magic_info[0]->min_league == 0) {
				$weight = 0;
			} else {
				$magic_in_league = \DB::table('tbl_league')
					->select('id', 'min_lvl')
					->where('id', '=', $magic_info[0]->min_league)
					->get();

				$weight = $magic_in_league[0]->min_lvl;
			}
			if(($weight <= $league_data[0]->min_lvl) && ($magic_q > 0)){
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

	//Внутриигровые методы
	public function getCardDataByRequest(Request $request){
		$data = $request->all();
		return self::getCardData($data['card']);
	}

	public static function getCardData($id){
		if($id<=0) return '';
		$card_data = \DB::table('tbl_cards')
			->select('id','title','slug','card_type','card_race','is_leader','card_strong','card_groups','img_url','short_description','allowed_rows','card_actions')
			->where('id', '=', $id)
			->get();
		if(!$card_data) return '';
		$action_rows = SiteFunctionsController::createActionRowsArray($card_data[0]->allowed_rows);
		$actions = SiteFunctionsController::createActionsArray(unserialize($card_data[0]->card_actions));

		$fraction = ($card_data[0]->card_type == 'race')? $card_data[0]->card_race: $card_data[0]->card_type;

		return json_encode([
			'id'		=> $id,
			'title'		=> $card_data[0]->title,
			'type'		=> $card_data[0]->card_type,
			'fraction'	=> $fraction,
			'strength'	=> $card_data[0]->card_strong,
			'img_url'	=> $card_data[0]->img_url,
			'is_leader'	=> $card_data[0]->is_leader,
			'descript'	=> $card_data[0]->short_description,
			'action_row'=> unserialize($card_data[0]->allowed_rows),
			'actions'	=> unserialize($card_data[0]->card_actions),
			'action_txt'=> $actions,
			'row_txt'	=> $action_rows,
			'groups'	=> unserialize($card_data[0]->card_groups)
		]);
	}


	public function getMagicDataByRequest(Request $request){
		$data = $request->all();
		return self::getMagicData($data['card']);
	}

	public static function getMagicData($id){
		if($id<=0) return '';

		$magic = \DB::table('tbl_magic_effect')
			->select('id','title','img_url','description','energy_cost','effect_actions')
			->where('id','=',$id)
			->get();

		if(!$magic) return '';

		return json_encode([
			'id'		=> $magic[0]->id,
			'title'		=> $magic[0]->title,
			'img_url'	=> $magic[0]->img_url,
			'descript'	=> $magic[0]->description,
			'energy_cost' => $magic[0]->energy_cost,
			'actions'	=> unserialize($magic[0]->effect_actions)
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