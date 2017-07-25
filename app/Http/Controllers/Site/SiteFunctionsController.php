<?php
namespace App\Http\Controllers\Site;

use App\Card;
use App\EtcData;
use App\Fraction;
use App\MagicEffect;
use App\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteFunctionsController extends BaseController
{

	/*
	 * Пользователь
	*/

	//Получить данные о пользователе
	public function getUserData(){
		$user = Auth::user();

		//Достаем из БД дополнительные данные пользователя
		$etc_data = EtcData::where('label_data', '=', 'deck_options')->get();

		$leagues = \DB::table('tbl_league')->select('title', 'min_lvl')->orderBy('min_lvl', 'asc')->get();
		$exchanges = EtcData::where('label_data', '=', 'exchange_options')->get();

		$users = User::select('id','user_rating','login')->get();
		foreach($users as $user_iter => $user_to_rate_data){
			$users_rates[] = SiteFunctionsController::calcUserRating('all', $user_to_rate_data);
		}

		usort($users_rates, function($a, $b){return ($b['rating'] - $a['rating']);});
		foreach($users_rates as $pos => $user_data){
			if($user['login'] == $user_data['login']){
				$user_rating = [
					'position' => $pos+1,
					'rating' => $user_data['rating']
				];
			}
		}

		if($user){
			$result = [
				'avatar'	=> $user->img_url,
				'gold'		=> $user->user_gold,
				'silver'	=> $user->user_silver,
				'energy'	=> $user->user_energy,
				'rating'	=> $user_rating
			];
		}

		foreach ($etc_data as $key => $value) {
			$result[$value->meta_key] = $value->meta_value;
		}

		$result['leagues'] = [];
		foreach ($leagues as $key => $value) {
			$result['leagues'][] = [
				'title' => $value->title,
				'min_lvl' => $value->min_lvl
			];
		}

		$result['exchanges'] = [];
		foreach ($exchanges as $key => $value) {
			$result['exchanges'][$value->meta_key] = $value->meta_value;
		}

		return json_encode($result);
	}
	/*
	 * END OF Пользователь
	*/



	/*
	 * Покупочки
	*/
	//Пользователь покупает энергию
	public function userBuyingEnergy(Request $request){
		self::updateConnention();

		$user = Auth::user();

		$exchange = EtcData::where('label_data', '=', 'exchange_options')->get();

		$exchange_array = [];
		foreach($exchange as $key => $value){
			$exchange_array[$value -> meta_key] = $value -> meta_value;
		}

		switch($request -> input('pay_type')){
			case 'gold_to_100_energy':
				if($user->user_gold >= $exchange_array['gold_to_100_energy']){
					$user->user_gold -= $exchange_array['gold_to_100_energy'];
					$user->user_energy += 100;
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			case 'silver_to_100_energy':
				if($user->user_silver >= $exchange_array['silver_to_100_energy']){
					$user->user_silver -= $exchange_array['silver_to_100_energy'];
					$user->user_energy += 100;
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			case 'gold_to_200_energy':
				if($user->user_gold >= $exchange_array['gold_to_200_energy']){
					$user->user_gold -= $exchange_array['gold_to_200_energy'];
					$user->user_energy += 200;
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			case 'silver_to_200_energy':
				if($user->user_silver >= $exchange_array['silver_to_200_energy']){
					$user->user_silver -= $exchange_array['silver_to_200_energy'];
					$user->user_energy += 200;
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			default:
				return json_encode(['message' => 'Неизвестный тип операции.']);
		}

		$result = User::where('login', '=', $user->login)->update([
			'user_gold'		=> $user->user_gold,
			'user_silver'	=> $user->user_silver,
			'user_energy'	=> $user->user_energy
		]);
		if($result !== false){
			return json_encode(['message' => 'success', 'gold' => $user->user_gold, 'silver' => $user->user_silver, 'energy' => $user->user_energy]);
		}else{
			return json_encode(['message' => 'Произошел сбой.']);
		}
	}

	//Пользователь покупает серебро
	public function userBuyingSilver(Request $request){
		self::updateConnention();
		$data = $request->all();

		$user = Auth::user();

		$gold_to_silver = \DB::table('tbl_etc_data')->select('meta_value')->where('meta_key','=','gold_to_silver')->first();

		if(empty($gold_to_silver)){
			return json_encode(['message'=>'Error in DB query. Returns ']);
		}

		if($user->user_gold >= $data['gold']){
			$user->user_gold -= $data['gold'];
			$user->user_silver = $user->user_silver + ($data['gold'] * $gold_to_silver->meta_value);

			$result = User::where('login', '=', $user->login)->update([
				'user_gold'		=> $user->user_gold,
				'user_silver'	=> $user->user_silver
			]);

			if($result != false){
				return json_encode(['message' => 'success', 'gold' => $user->user_gold, 'silver' => $user->user_silver]);
			}
		}else{
			return json_encode(['message' => 'Недостаточно золота для операции.']);
		}
	}

	//Пользователь покупает премиум
	public function userBuyingPremium(Request $request){
		self::updateConnention();

		$data = $request->all();

		$user = Auth::user();

		$price = \DB::table('tbl_etc_data')->select('meta_key','meta_value')->where('meta_key','=',$data['premiumType'])->get();

		if($user->user_gold >= $price[0]->meta_value) {

			$user->user_gold -= $price[0]->meta_value;
			switch($data['premiumType']){
				case 'gold_per_month':	$days = 30; break;
				case 'gold_per_week':	$days = 7; break;
				case 'gold_per_day':	$days = 1; break;
				default: $days = 0;
			}

			if($user->premium_activated != 0){
				$expire_date = date('Y-m-d H:i:s', strtotime($user -> premium_expire_date.' +'.$days.' day'));
			}else{
				$expire_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +'.$days.' day'));
			}

			$result = User::where('login', '=', $user['login'])->update([
				'user_gold'			=> $user->user_gold,
				'premium_activated'	=> 1,
				'premium_expire_date' => $expire_date
			]);

			if($result != false){
				return json_encode(['message' => 'success']);
			}
		}else{
			return json_encode(['message' => 'Недостаточно золота для активации премиум-аккаунта']);
		}
	}

	/*
	 * END OF Покупочки
	*/

	/*
	 * Рейтинг
	*/
	public static function calcUserRating($league, $user_to_rate_data){
		$rates = unserialize($user_to_rate_data -> user_rating);

		$rating = 0;
		$games = 0;
		$wins = 0;

		if($league == 'all'){
			foreach($rates as $league_id => $rate_data){
				$rating = $rating + $rate_data['user_rating'];
				$games = $games + $rate_data['games_count'];
				$wins = $wins + $rate_data['win_count'];
			}
		}else{
			$rating = $rating + $rates[$league]['user_rating'];
			$games = $games + $rates[$league]['games_count'];
			$wins = $wins + $rates[$league]['win_count'];
		}

		$wins_percent = ($games != 0)? (100 * ($wins / $games)): 0;

		return [
			'rating'	=> $rating,
			'games'		=> $games,
			'login'		=> $user_to_rate_data->login,
			'position'	=> 0,
			'wins_percent' => round($wins_percent, 2)
		];
	}

	public function getUserRating(Request $request){
		$data = $request->all();
		$current_user = Auth::user();
		$user = User::select('login','user_rating')->where('login', '=', $current_user['login'])->get();

		if(!empty($user[0])){
			$users_rates = [];

			$users = (isset($data['login']) && !empty($data['login']))? User::where('login','LIKE','%'.$data['login'].'%')->get(): User::get();

			foreach($users as $user_iter => $user_to_rate_data){
				$users_rates[] = self::calcUserRating($data['league'], $user_to_rate_data);
			}

			usort($users_rates, function($a, $b){return ($b['rating'] - $a['rating']);});
			$indexes = [];

			$user_current_index = 0;
			$user_rates_count = count($users_rates);
			for($i = 0; $i < $user_rates_count; $i++){
				if($i<3) $indexes[] = $i;//Первые 20 пользователей
				if($user[0]['login'] == $users_rates[$i]['login']) $user_current_index = $i;
				$users_rates[$i]['position'] = $i+1;
			}

			if($user_current_index - 7 >= 0){
				$top = $user_current_index - 7;
				$bottom_adds = 0;
			}else{
				$top = 0;
				$bottom_adds = abs($user_current_index - 7);
			}

			if($user_current_index + 7 <= $user_rates_count){
				$bottom = $user_current_index + 7 + $bottom_adds;
			}else{
				$bottom = $user_rates_count-1;
				$top -= abs($user_current_index + 8 - $user_rates_count);
			}

			for($i=$top; $i<=$bottom; $i++) $indexes[] = $i;
			$indexes = array_values(array_unique($indexes));
			$users_out = [];
			foreach($indexes as $i => $index){
				if(isset($users_rates[$index])){
					if($users_rates[$index]['login'] == $user[0]['login']){
						$users_rates[$index]['is_active'] = 1;
					}
					$users_out[] = $users_rates[$index];
				}
			}
			return json_encode([
				'message'	=> 'success',
				'users'		=> $users_out
			]);
		}else{
			return json_encode(['message' => 'Данного пользователя не существует']);
		}
	}

	public function getUsersRatingByScroll(Request $request){
		$data = $request -> all();

		$current_user = Auth::user();
		$user = (isset($data['user_login']))? User::where('login', '=', $data['user_login'])->get(): User::where('login', '=', $current_user['login'])->get();

		if(!empty($user[0])){
			$users_rates = [];
			$users = User::select('login','user_rating')->get();

			foreach($users as $user_to_rate_data){
				$users_rates[] = self::calcUserRating($data['league'], $user_to_rate_data);
			}

			usort($users_rates, function($a, $b){return ($b['rating'] - $a['rating']);});

			$user_rates_count = count($users_rates);
			for($i = 0; $i < $user_rates_count; $i++){
				$users_rates[$i]['position'] = $i+1;
			}

			$users_out = [];
			if($data['direction'] == 1){
				$n = ($data['position'] +10 <= $user_rates_count)? $data['position'] +10: $user_rates_count;
				for($i = $data['position']; $i < $n; $i++){
					$users_out[] = $users_rates[$i];
				}
			}else{
				$n = ($data['position'] -10 >= 3)? $data['position'] -10: 3;
				for($i = $n; $i < $data['position']-1 ; $i++){
					$users_out[] = $users_rates[$i];
				}
			}

			return json_encode([
				'message'	=> 'success',
				'users'		=> $users_out
			]);
		}else{
			return json_encode(['message' => 'Данного пользователя не существует']);
		}
	}
	/*
	 * END OF Рейтинг
	*/

	/*
	 * Системные методы
	*/

	//Если пользователь начал проявлять активность - делаем его активным
	public static function updateConnention(){
		$user = Auth::user();
		//Если пользователь не в бою
		User::where('login', '=', $user['login'])->update([
			'updated_at' => date('Y-m-d H:i:s'),
			'user_online' => '1'
		]);
	}

	//Получить статус занятости игрока
	public function getUserPlayingStatus(){
		$user = Auth::user();
		$status = \DB::table('users')->select('login','user_busy')->where('login','=', $user->login)->get();
		if($status[0]->user_busy > 0){
			return json_encode(['message' => 'Операция невозможна. Вы находитесь в битве.']);
		}else{
			return $status[0]->user_busy;
		}
	}

	//Получить кол-во активных пользователей
	public function getUsersQuantity(){
		return \DB::table('users')->select('user_online')->where('user_online', '=', 1)->count();
	}

	public static function createActionsArray($card_actions){
		$actions = [];
		foreach($card_actions as $action_iter => $action){
			$action_data = \DB::table('tbl_actions')->select('id','title')->where('id','=',$action->action)->get();
			$title = $action_data[0]->title;

			switch($action->action){
				case '2': $img_url = 'deadless.png'; break; //Бессмертный
				case '3': $img_url = 'brotherhood.png'; break; //Боевое братство
				case '4': $img_url = 'inspiration.png'; break; //Воодушевление
				case '5': //Иммунитет
					if($action->immumity_type == 0){
						$title = 'Иммунитет к негативным воздействиям';
						$img_url = 'imun_simple.png';
					}else{
						$title = 'Иммунитет полный';
						$img_url = 'imun_full.png';
					}
					break;
				case '6': $img_url = 'istselenie.png'; break; //Исцеление
				case '7': $img_url = 'heal.png'; break; //Лекарь
				case '8': $img_url = 'neishtovoschch.png'; break; //Неистовство
				case '9': $img_url = 'odurmanivanie.png'; break; //Одурманивание
				case '10': $img_url = 'regroup.png'; break; //перегруппировка
				case '11': $img_url = 'pechal.png'; break; //Печаль
				case '12': $img_url = 'master.png'; break; //Повелитель
				case '13': $img_url = 'wsparcie.png'; break; //Поддержка
				case '15': $img_url = 'priziv.png'; break; //Призыв
				case '18': $img_url = 'strah.png'; break; //Страшный
				case '19': $img_url = 'killer.png'; break; //Убийца
				case '20':                  //Шпион
					if($action->spy_fieldChoise == 0){
						$title = 'Разведчик';
						$img_url = 'spy.png';
					}else{
						$title = 'Шпион';
						$img_url = 'razved.png';
					}
					break;
			}
			$actions[] = ['title' => $title, 'img' => '/images/'.$img_url];
		}
		return $actions;
	}

	public static function createActionRowsArray($action_rows){
		$action_rows = unserialize($action_rows);
		$row_img = '_';
		foreach($action_rows as $i => $row) $row_img .= $row;
		switch($row_img){
			case '_0':  $rows_to_action[] = ['title' => 'Ближний', 'image' => '/images/card_action_row'.$row_img.'.png']; break;
			case '_1':  $rows_to_action[] = ['title' => 'Дальний', 'image' => '/images/card_action_row'.$row_img.'.png']; break;
			case '_2':  $rows_to_action[] = ['title' => 'Сверхдальний', 'image' => '/images/card_action_row'.$row_img.'.png']; break;
			case '_01': $rows_to_action[] = ['title' => 'Ближний, Дальний', 'image' => '/images/card_action_row'.$row_img.'.png']; break;
			case '_12': $rows_to_action[] = ['title' => 'Дальний, Сверхдальний', 'image' => '/images/card_action_row'.$row_img.'.png']; break;
			case '_012':$rows_to_action[] = ['title' => 'Ближний, Дальний, Сверхдальний', 'image' => '/images/card_action_row'.$row_img.'.png']; break;
			default:    $rows_to_action[] = ['title' => 'Средний', 'image' => '/images/card_action_row_mid.png'];
		}
		return $rows_to_action;
	}

	public function validateDeck(Request $request){
		self::updateConnention();
		$data = $request->all();
		$user = Auth::user();

		$current_deck = unserialize($user->user_cards_in_deck);

		$deck_options = EtcData::where('label_data', '=', 'deck_options')->get();
		$deck_rules = [];
		foreach($deck_options as $key => $value){
			$deck_rules[$value->meta_key] = $value->meta_value;
		}

		if(!empty($current_deck[$data['fraction']])){
			$error = '';
			$leader_card_quantity = 0;
			$warrior_card_quantity = 0;
			$special_card_quantity = 0;

			foreach($current_deck[$data['fraction']] as $key => $value){
				$card = \DB::table('tbl_cards')
					->select('id','title','forbidden_races','max_quant_in_deck','is_leader','card_type')
					->where('id','=',$key)
					->get();

				if(isset($card[0])){
					//Проверяем достапна ли карта для данной колоды
					$card_forbidden_race = unserialize($card[0]->forbidden_races);
					if(!empty($card_forbidden_race)){
						$is_forbidden = 0;
						foreach($card_forbidden_race as $i => $fraction){
							if($data['fraction'] == $fraction) $is_forbidden = 1;
						}
						if($is_forbidden != 0){
							$error .= '<p>Карта "'.$card[0]->title.'" недоступна для данной колоды.</p>';
						}
					}

					//Проверяем максимальное колличество карт каждого типа
					if($value > $card[0]->max_quant_in_deck){
						$error .= '<p>В колоде находится слишком много карт "'.$card[0]->title.'" (Максимальное колличество - '.$card[0]->max_quant_in_deck.').</p>';
					}

					if(0 != $card[0]->is_leader) $leader_card_quantity += $value; //Количество карт-лидеров

					if($card[0]->card_type == 'special'){
						$special_card_quantity += $value; //Количество спец. карт
					}else{
						$warrior_card_quantity += $value; //Количество карт-воинов
					}
				}
			}

			if($warrior_card_quantity < $deck_rules['minWarriorQuantity']) {
				$error .= '<p>Количество карт воинов в  колоде должно быть не меньше '.$deck_rules['minWarriorQuantity'].' штук</p>';
			}

			if($special_card_quantity > $deck_rules['specialQuantity']){
				$error .= '<p>Количество спец. карт в колоде должно быть не больше '.$deck_rules['specialQuantity'].' штук</p>';
			}

			if($leader_card_quantity > $deck_rules['leaderQuantity']){
				$error .= '<p>Количество карт лидеров в колоде должно быть не больше '.$deck_rules['leaderQuantity'].' штук</p>';
			}

			//Если есть ошибки валидации
			if($error != ''){
				return json_encode(['message' => $error]);
			}else{

				$user_member = \DB::table('tbl_battle_members')
					->select('user_id','battle_id')
					->where('user_id','=', $user->id)
					->get();

				if($user_member != false){
					$battle = \DB::table('tbl_battles')
						->select('id','opponent_id','fight_status')
						->where('fight_status','<',3)
						->where('opponent_id','!=',0)
						->get();

					if($battle != false){
						$battle_members = \DB::table('tbl_battle_members')
							->select('battle_id')
							->where('battle_id','=',$battle[0]->id)
							->count();

						if($battle_members == 2){
							return json_encode(['message' => 'in_battle', 'room'=>$battle[0]->id]);
						}
					}
				}

				setcookie('current_deck', $data['fraction'], time()+36000, '/');
				return json_encode(['message' => 'success']);
			}

		}else{
			return json_encode(['message' => 'Пустая колда']);
		}
	}
	/*
	 * END OF Системные методы
	*/


	/*
	 * Колоды
	*/
	//Перенос карт из колоды в колоду
	protected static function addCardToDeck($deck_from, $deck_to, $card_id){
		foreach ($deck_from as $key => $val) {
			//если такая карта действительно существует
			if($key == $card_id){
				//Уменьшаем количество перетягиваемой карты на 1
				$deck_from[$key]--;

				//Если такая карта существует в пользовательской колоде
				if(isset($deck_to[$key])){
					//увеличиваем её количество на 1
					$deck_to[$key]++;
				}else{
					//если нету, создаем её
					$deck_to[$key] = 1;
				}

				//Если карт одного вида в колоде доступных нету- удаляем её
				if(0 >= $deck_from[$key]){
					unset($deck_from[$key]);
				}
			}
		}

		return [
			'deck_from'	=> $deck_from,
			'deck_to'	=> $deck_to
		];
	}

	public function getUserDecks(Request $request){
		self::updateConnention();
		$data = $request->all();

		//Текущая колода
		$deck = htmlspecialchars(strip_tags(trim($data['deck'])));

		$fraction_options = \DB::table('tbl_fraction')->select('slug', 'img_url')->where('slug', '=', $deck)->get();

		$user = Auth::user();
		User::where('login','=',$user->login)->update(['last_user_deck' => $deck]);

		//Все доступные карты пользователя, что не находятся в колодах
		$user_available_cards = unserialize($user->user_available_deck);

		$result_array= ['in_deck' => [], 'available' => [], 'race_img'=>$fraction_options[0]->img_url];

		//формирование масива доступных карт
		foreach ($user_available_cards as $key => $value) {
			$card = Card::where('id', '=', $key)->get();
			if(isset($card[0])){
				$card_actions = unserialize($card[0]->card_actions);
				$actions = self::createActionsArray($card_actions);

				$action_rows = self::createActionRowsArray($card[0]->allowed_rows);

				if(($card[0]->card_race == $deck)or($card[0]->card_race == '')) {
					$result_array['available'][$key] = [
						'id'		=> $key,
						'title'		=> $card[0]->title,
						'type'		=> $card[0]->card_type,
						'race'		=> $card[0]->card_race,
						'strength'	=> $card[0]->card_strong,
						'weight'	=> $card[0]->card_value,
						'is_leader'	=> $card[0]->is_leader,
						'img_url'	=> $card[0]->img_url,
						'descr'		=> $card[0]->full_description,
						'max_quant'	=> $card[0]->max_quant_in_deck,
						'quantity'	=> $value,
						'allowed_rows' => $action_rows,
						'actions'	=> $actions
					];
				}
			}
		}

		//карты пользовательских колод
		$user_deck = unserialize($user->user_cards_in_deck);

		//формирование масива карт пользовательских колод
		foreach($user_deck[$deck] as $key => $value){
			$card = Card::where('id', '=', $key)->get();
			if(isset($card[0])){
				$card_actions = unserialize($card[0]->card_actions);
				$actions = self::createActionsArray($card_actions);

				$action_rows = self::createActionRowsArray($card[0]->allowed_rows);

				if(($card[0]->card_race == $deck)or($card[0]->card_race == '')) {
					$result_array['in_deck'][$key] = [
						'id'		=> $key,
						'title'		=> $card[0]->title,
						'type'		=> $card[0]->card_type,
						'race'		=> $card[0]->card_race,
						'strength'	=> $card[0]->card_strong,
						'weight'	=> $card[0]->card_value,
						'is_leader'	=> $card[0]->is_leader,
						'img_url'	=> $card[0]->img_url,
						'descr'		=> $card[0]->full_description,
						'max_quant'	=> $card[0]->max_quant_in_deck,
						'quantity'	=> $value,
						'allowed_rows' => $action_rows,
						'actions'	=> $actions
					];
				}
			}
		}

		return json_encode($result_array);
	}

	public function userClearDeck(Request $request){
		//Обновление активности пользователя
		self::updateConnention();
		$data = $request->all();
		$user = Auth::user();
		//Текущая колода
		$deck = htmlspecialchars(strip_tags(trim($data['deck'])));

		$fraction_options = \DB::table('tbl_fraction')->select('slug', 'img_url')->where('slug', '=', $deck)->get();

		$user_available_cards = unserialize($user->user_available_deck);
		$user_deck = unserialize($user->user_cards_in_deck);

		foreach($user_deck[$deck] as $key => $value){
			if(isset($user_available_cards[$key])){
				$user_available_cards[$key] += $value;
			}else{
				$user_available_cards[$key] = $value;
			}
			unset($user_deck[$deck][$key]);
		}

		$result_array= ['available' => [], 'race_img'=>$fraction_options[0]->img_url];

		//формирование масива доступных карт
		foreach ($user_available_cards as $key => $value) {
			$card = Card::where('id', '=', $key)->get();
			if(isset($card[0])){
				$card_actions = unserialize($card[0]->card_actions);
				$actions = self::createActionsArray($card_actions);

				$action_rows = self::createActionRowsArray($card[0]->allowed_rows);

				if(($card[0]->card_race == $deck)or($card[0]->card_race == '')) {
					$result_array['available'][$key] = [
						'id'		=> $key,
						'title'		=> $card[0]->title,
						'type'		=> $card[0]->card_type,
						'race'		=> $card[0]->card_race,
						'strength'	=> $card[0]->card_strong,
						'weight'	=> $card[0]->card_value,
						'is_leader'	=> $card[0]->is_leader,
						'img_url'	=> $card[0]->img_url,
						'descr'		=> $card[0]->full_description,
						'max_quant'	 => $card[0]->max_quant_in_deck,
						'quantity'	=> $value,
						'allowed_rows' => $action_rows,
						'actions'	=> $actions
					];
				}
			}
		}

		$result = User::where('login','=',$user->login)->update([
			'user_available_deck'	=> serialize($user_available_cards),
			'user_cards_in_deck'	=> serialize($user_deck)
		]);

		if($result !== false){
			return json_encode($result_array);
		}
	}

	public function userPullOverCard(Request $request){
		self::updateConnention();
		$data = $request->all();
		$user = Auth::user();

		//Доступные карт
		$available_deck = unserialize($user->user_available_deck);

		//ПОльзовательские колоды карт
		$card_deck = unserialize($user->user_cards_in_deck);

		//Перетягивание из доступных в колоду расы
		if($data['source'] == 'available'){
			$decks = self::addCardToDeck($available_deck, $card_deck[$data['deck']], $data['id']);
			$card_deck[$data['deck']] = $decks['deck_to'];
			$available_deck = serialize($decks['deck_from']);
		}

		//Перетягивание из колоды расы в доступные
		if($data['source'] == 'user_deck'){
			$decks = self::addCardToDeck($card_deck[$data['deck']], $available_deck, $data['id']);
			$card_deck[$data['deck']] = $decks['deck_from'];
			$available_deck = serialize($decks['deck_to']);
		}
		$card_deck = serialize($card_deck);
		//Сохраняем колоды в БД
		User::where('login','=',$user->login)->update([
			'user_available_deck'	=> $available_deck,
			'user_cards_in_deck'	=> $card_deck,
		]);
	}

	/*
	 * Колоды
	*/


	/*
	 * Магазин
	*/
	public function cardsByFraction(Request $request){
		self::updateConnention();
		$user = Auth::user();
		//Если колода относится к определенной расе
		$field = ( ($request->input('fraction') == 'special') || ($request->input('fraction') == 'neutrall') )? 'card_type': 'card_race';

		$fraction = \DB::table('tbl_fraction')
			->select('slug','img_url')
			->where('slug', '=', $request->input('fraction'))
			->get();

		$result['race_img'] = $fraction[0]->img_url;

		//Карты пользователя
		$user_all_cards_array = ['knight'=>[], 'forest'=>[], 'cursed'=>[], 'undead'=>[], 'highlander'=>[], 'monsters'=>[], 'neutrall'=>[], 'special'=>[]];

		$user_cards_in_deck = unserialize($user->user_cards_in_deck);
		$user_available_deck = unserialize($user->user_available_deck);
		foreach($user_cards_in_deck as $deck => $cards){
			foreach($cards as $card_id => $card_quant){
				$card_data = Card::find($card_id);
				if($card_data){
					$card_deck = ($card_data['card_type'] != 'race')? $card_data['card_type']: $deck;

					for($i=0; $i<$card_quant; $i++){
						if(!isset($user_all_cards_array[$card_deck][$card_id])){
							$user_all_cards_array[$card_deck][$card_id] = 1;
						}else{
							$user_all_cards_array[$card_deck][$card_id]++;
						}
					}
				}
			}
		}
		foreach($user_available_deck as $card_id => $card_quant){
			$card_data = Card::find($card_id);
			if($card_data){
				$card_deck = ($card_data['card_type'] != 'race')? $card_data['card_type']: $card_data['card_race'];
				for($i=0; $i<$card_quant; $i++){
					if(!isset($user_all_cards_array[$card_deck][$card_id])){
						$user_all_cards_array[$card_deck][$card_id] = 1;
					}else{
						$user_all_cards_array[$card_deck][$card_id]++;
					}
				}
			}
		}
		$cards = Card::where($field, '=', $request->input('fraction'))->orderBy('card_strong','desc') -> get();
		foreach($cards as $key => $value){
			$quantity = ( isset($user_all_cards_array[$request->input('fraction')][$value['id']]) )
				? $user_all_cards_array[$request->input('fraction')][$value['id']]
				: 0;

			$card_actions = unserialize($value->card_actions);
			$actions = self::createActionsArray($card_actions);

			$action_rows = self::createActionRowsArray($value->allowed_rows);

			$result['cards'][] = [
				'id'		=> $value['id'],
				'title'		=> $value['title'],
				'type'		=> $value['card_type'],
				'race'		=> $value['card_race'],
				'strength'	=> $value['card_strong'],
				'value'		=> $value['card_value'],
				'is_leader'	=> $value['is_leader'],
				'img_url'	=> $value['img_url'],
				'quantity'	=> $quantity,
				'max_quant'	=> $value['max_quant_in_deck'],
				'descr'		=> $value['short_description'],
				'gold'		=> $value['price_gold'],
				'silver'	=> $value['price_silver'],
				'allowed_rows' => $action_rows,
				'actions'	=> $actions
			];
		}

		return json_encode($result);
	}

	public function getUserRequestToBuyCard(Request $request){
		self::updateConnention();
		$data = $request->all();
		$user = Auth::user();
		$card = \DB::table('tbl_cards')->select('id','title','price_gold','price_silver')->where('id', '=', $data['card_id'])->get();

		$result = [
			'title'			=> $card[0]->title,
			'user_gold'		=> $user->user_gold,
			'user_silver'	=> $user->user_silver,
			'message'		=> 'success'
		];
		switch($data['buy_type']){
			case 'simpleBuy':
				$result['price_gold']   = $card[0]->price_gold;
				$result['price_silver'] = $card[0]->price_silver;
				break;
			default:
				$result['message'] = 'Неверная операция.';
		}
		return json_encode($result);
	}

	public function userBuyCard(Request $request){
		self::updateConnention();
		$data = $request->all();
		$user = Auth::user();

		//Данные карты
		$card = Card::find($data['card_id']);

		$gold_price = $card->price_gold * $data['quant'];
		$silver_price = $card->price_silver * $data['quant'];

		if(($user->user_gold >= $gold_price) && ($user->user_silver >= $silver_price)){

			$user->user_gold -= $gold_price;
			$user->user_silver -= $silver_price;

			$user_available_deck = unserialize($user->user_available_deck);//Доступные карты пользователя

			for($i=0; $i<$data['quant']; $i++){
				//Если карта существуетв колоде - увеличиваем её количество на 1
				if( isset($user_available_deck[$data['card_id']]) ){
					$user_available_deck[$data['card_id']]++;
				}else{
					//Если нету - создаем её
					$user_available_deck[$data['card_id']] = 1;
				}
			}

			//Сохраняем колоду доступных карт
			$result = User::where('login','=',$user->login)->update([
				'user_gold'		=> $user->user_gold,
				'user_silver'	=> $user->user_silver,
				'user_available_deck' => serialize($user_available_deck)
			]);

			if($result !== false){
				return json_encode(['message' => 'success', 'gold' => $user->user_gold, 'silver' => $user->user_silver, 'title' => $card->title]);
			}else{
				return $result;
			}
		}else{
			return json_encode(['message' => 'Недостачно денег для покупки.']);
		}
	}
	/*
	* END OF Магазин
	*/


	/*
	*  Волшебство
	*/
	public function getMagicByFraction(Request $request){
		self::updateConnention();

		$user = Auth::user();
		//Данные пользователя

		//магические еффекты
		$magic_effects = MagicEffect::orderBy('price_gold','asc')->orderBy('price_silver','asc')->get();

		//Изображение текущей расы
		$fraction = \DB::table('tbl_fraction')
			->select('slug','img_url')
			->where('slug', '=', $request -> input('fraction'))
			->get();

		$result['race_img'] = $fraction[0]->img_url;

		//Текущие магические эффекты пользоввтеля
		$user_magic = unserialize($user->user_magic);

		foreach($magic_effects as $key => $value){
			//Если текущая раса в массиве доступных рас
			if( $request->input('fraction') == $value->fraction ){
				//если пользователь имеет текущий магический еффект
				if( isset($user_magic[$value->id]) ){
					//если магический еффект полностью израсходован
					if( 0 == $user_magic[$value->id]['used_times'] ){
						$status = 'disabled';		//статус "отсутствует"
						$used_times = '';			//Осталось использований
					}else{
						$status = ( 0 == $user_magic[$value->id]['active'] )? '': $status = 'active';		//статус "активен"
						$used_times = '<p>'.$user_magic[$value->id]['used_times'].'</p>';
					}

				}else{
					$status = 'disabled';
					$used_times = '';
				}

				//если цена в золоте == 0
				$gold = ( 0 == $value->price_gold)? '&mdash;': $value->price_gold;
				//если цена в серебре == 0
				$silver = ( 0 == $value->price_silver)? '&mdash;': $value->price_silver;

				$result['effects'][] = [
					'id'		=> $value->id,
					'title'		=> $value->title,
					'img_url'	=> $value->img_url,
					'descr'		=> $value->description,
					'energy'	=> $value->energy_cost,
					'gold'		=> $gold,
					'silver'	=> $silver,
					'status'	=> $status,
					'used_times'=> $used_times,
				];
			}
		}
		\DB::table('users')->where('login','=',$user->login)->update(['last_user_deck' => $request -> input('fraction')]);
		return json_encode($result);
	}

	public function getUserRequestToBuyMagic(Request $request){
		self::updateConnention();
		$user = Auth::user();
		$magic = \DB::table('tbl_magic_effect')
			->select('id', 'title', 'price_gold', 'price_silver')
			->where('id', '=', $request->input('magic_id'))
			->get();

		$user_money = \DB::table('users')->select('login','user_gold','user_silver')->where('login', '=', $user->login)->get();

		return json_encode([
			'title'			=> $magic[0]->title,
			'price_gold'	=> $magic[0]->price_gold,
			'price_silver'	=> $magic[0]->price_silver,
			'user_gold'		=> $user_money[0]->user_gold,
			'user_silver'	=> $user_money[0]->user_silver,
		]);
	}

	//Пользователь покупает магию
	public function userBuyingMagic(Request $request){
		self::updateConnention();

		$user = Auth::user();
		$magic = \DB::table('tbl_magic_effect')
			->select('id','title','price_gold','price_silver','usage_count')
			->where('id','=',$request->input('magic_id'))
			->get();

		if(($user->user_gold >= $magic[0]->price_gold) && ($user->user_silver >= $magic[0]->price_silver)){
			$user_magic = unserialize($user->user_magic);//Текущие магиские эффекты пользователя
			//Если существует текущий маг. эффект
			if( isset($user_magic[$request->input('magic_id')]) ){
				$user_magic[$request->input('magic_id')]['used_times'] += $magic[0]->usage_count;
			}else{
				$user_magic[$request->input('magic_id')] = ['used_times' => $magic[0]->usage_count, 'active' => 0];
			}

			$user->user_gold -= $magic[0]->price_gold;
			$user->user_silver -= $magic[0]->price_silver;

			$result = \DB::table('users')->where('login','=',$user->login)->update([
				'user_gold'		=> $user->user_gold,
				'user_silver'	=> $user->user_silver,
				'user_magic'	=> serialize($user_magic)
			]);

			if($result !== false){
				return json_encode([
					'message'	=> 'success',
					'date'		=> '<p>'.$user_magic[$request->input('magic_id')]['used_times'].'</p>',
					'gold'		=> $user->user_gold,
					'silver'	=> $user->user_silver,
					'title'		=> $magic[0]->title
				]);
			}else{
				return json_encode(['message' => 'Что-то пошло не так.']);
			}
		}else{
			return json_encode(['message' => 'Недостачно денег для покупки.']);
		}
	}

	//Пользователь активирует маг. еффект в магазине
	public function userChangesMagicStatus(Request $request){
		self::updateConnention();
		$data = $request->all();
		$user = Auth::user();
		$user_magic = unserialize($user->user_magic);

		if($data['is_active'] == 'false') {
			//Активные еффекты
			$active_effects = [];

			$user_magic_effects_ids = array_keys($user_magic);
			$magic = \DB::table('tbl_magic_effect')
				->select('id','fraction','min_league')
				->where('id', '=', $data['status_id'])
				->get();

			$magic_effect_by_current_race = \DB::table('tbl_magic_effect')
				->select('id', 'fraction')
				->where('fraction', '=', $magic[0]->fraction)
				->get();

			foreach($magic_effect_by_current_race as $key => $value){
				if(in_array($value->id, $user_magic_effects_ids)){
					if( 0 != $user_magic[$value->id]['active'] ){
						$active_effects[] = $value->id;
					}
				}
			}

			//проверка на вхождение магии в лигу
			$user_deck = unserialize($user->user_cards_in_deck);
			$user_deck = $user_deck[$magic[0]->fraction];

			if($magic[0]->min_league > 0){
				$league = \DB::table('tbl_league')->select('id','min_lvl')->where('id','=',$magic[0]->min_league)->get();
				$weight_to_need = $league[0]->min_lvl;
			}else{
				$weight_to_need = 0;
			}

			$deck_weight = 0;
			foreach ($user_deck as $card_id => $card_quantity){
				$card = \DB::table('tbl_cards')->select('id', 'card_value')->where('id', '=', $card_id)->get();
				$deck_weight += $card[0]->card_value * $card_quantity;
			}
			if($deck_weight < $weight_to_need){
				return json_encode(['deny_by_league']);
			}
			//END OF проверка на вхождение магии в лигу

			$maximum_active_magic = \DB::table('tbl_etc_data')
				->select('meta_key', 'meta_value')
				->where('meta_key', '=', 'base_max_magic')
				->get();

			if ($maximum_active_magic[0]->meta_value > count($active_effects)) {
				$user_magic[$data['status_id']]['active'] = 1;
			}else{
				return json_encode(['too_much', 0]);
			}

		}else{
			$user_magic[$data['status_id']]['active'] = 0;
		}

		User::where('login', '=', $user->login)->update(['user_magic' => serialize($user_magic)]);

		return json_encode(['success', $user_magic[$data['status_id']]['active']]);
	}

	/*
	*  END OF Волшебство
	*/

	public function getFractionDescription(Request $request){
		$data = $request->all();
		$fraction = Fraction::select('title','img_url','descr_shop','descr_magic')->where('slug','=',$data['fraction'])->first();
		if(!empty($fraction)){
			return json_encode([
				'title'		=> $fraction->title,
				'img_url'	=> '/img/fractions_images/'.$fraction->img_url,
				'shop_text'	=> $fraction->descr_shop,
				'magic_text'=> $fraction->descr_magic,
			]);
		}
	}
}