<?php
namespace App\Classes\Socket;

use App\Battle;
use App\BattleMembers;
use App\Http\Controllers\Site\BattleFieldController;
use App\League;
use App\SummaryLeague;
use App\User;
use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Site\SiteGameController;
use Ratchet\ConnectionInterface;

use Illuminate\Support\Facades\Crypt;

class GwentSocket extends BaseSocket
{
	protected $clients;//Соединения клиентов
	protected $battles;
	protected $battle_id;
	protected $users_data;
	protected $magic_usage;
	public $step_status;

	public function __construct(){
		$this->clients = new \SplObjectStorage;
	}

	//Socket actions
	public function onError(ConnectionInterface $conn, \Exception $e){
		echo 'An error has occured: '.$e->getMessage()."\n";
		$conn -> close();
	}

	public function onOpen(ConnectionInterface $conn){
		//Пользователь присоединяется к сессии
		$this->clients->attach($conn); //Добавление клиента
		echo 'New connection ('.$conn->resourceId.')'."\n\r";
	}

	public function onClose(ConnectionInterface $conn){
		$battle = Battle::find($this->battle_id);
		$battle->disconected_count++;
		$battle->save();

		if( ($battle->fight_status < 3) && ($battle->disconected_count > 1) ){
			$battle->fight_status = 3;
			$battle->save();
			$battle_info = BattleFieldController::battleInfo($battle, unserialize($battle->battle_field), $this->users_data, $this->magic_usage, $this->step_status);

			//Подсчет результатов раунда по очкам
			$total_score = self::calcStrByPlayers($battle_info['field_status']);
			$user_score = $total_score[$this->users_data['user']['player']];
			$opponent_score = $total_score[$this->users_data['opponent']['player']];

			if($user_score > $opponent_score){
				self::saveGameResults($this->users_data['p1']['id'], $battle, 'win');
				self::saveGameResults($this->users_data['p2']['id'], $battle, 'loose');
			}

			if($user_score < $opponent_score){
				self::saveGameResults($this->users_data['p2']['id'], $battle, 'win');
				self::saveGameResults($this->users_data['p1']['id'], $battle, 'loose');
			}

			if($user_score == $opponent_score){
				if( ( ($this->users_data['user']['current_deck'] == 'undead') || ($this->users_data['opponent']['current_deck'] == 'undead') ) && ($this->users_data['user']['current_deck'] != $this->users_data['opponent']['current_deck']) ){
					if($this->users_data['user']['current_deck'] == 'undead'){
						self::saveGameResults($this->users_data['user']['id'], $battle, 'win');
						self::saveGameResults($this->users_data['opponent']['id'], $battle, 'loose');
					}else{
						self::saveGameResults($this->users_data['opponent']['id'], $battle, 'win');
						self::saveGameResults($this->users_data['user']['id'], $battle, 'loose');
					}
				}else{
					self::saveGameResults($this->users_data['user']['id'], $battle, 'draw');
					self::saveGameResults($this->users_data['opponent']['id'], $battle, 'draw');
				}
			}

			\DB::table('users')->where('id','=',$this->users_data['user']['id'])->update(['user_busy' => 0]);
			\DB::table('users')->where('id','=',$this->users_data['opponent']['id'])->update(['user_busy' => 0]);
		}

		$this->clients->detach($conn);//delete on finish
		echo 'Connection '.$conn->resourceId.' has disconnected'."\n";//delete on finish
	}

	protected static function sendMessageToOthers($from, $result, $battles){
		foreach($battles as $client){
			if($client->resourceId != $from->resourceId){
				$client->send(json_encode($result));
			}
		}
	}

	protected static function sendMessageToSelf($from, $message){
		$from->send(json_encode($message));
	}
	//Socket actions end

	//Обработчик каждого сообщения
	public function onMessage(ConnectionInterface $from, $msg){
		$msg = json_decode($msg); // сообщение от пользователя arr[action, ident[battleId, UserId, Hash]]
		var_dump(date('Y-m-d H:i:s'));
		var_dump($msg);
		if(!isset($this->battles[$msg->ident->battleId])){
			$this->battles[$msg->ident->battleId] = new \SplObjectStorage;
		}

		$timing_settings = SiteGameController::getTimingSettings();

		if(!$this->battles[$msg->ident->battleId]->contains($from)){
			$this->battles[$msg->ident->battleId]->attach($from);
		}
		$SplBattleObj = $this->battles;

		$battle = Battle::find($msg->ident->battleId); //Даные битвы
		$this->battle_id = $msg->ident->battleId;

		$battle_members = BattleMembers::where('battle_id', '=', $msg->ident->battleId)->get(); //Данные о участвующих в битве

		\DB::table('users')->where('id', '=', $msg->ident->userId)->update([
			'updated_at'	=> date('Y-m-d H:i:s'),
			'user_online'	=> '1'
		]);

		//Создание массивов пользовательских данных
		foreach($battle_members as $key => $value){
			$user = User::find($value->user_id);
			$user_identificator = ($value->user_id == $battle->creator_id)? 'p1' : 'p2';
			$card_background = \DB::table('tbl_fraction')->select('card_img')->where('slug','=',$user->user_current_deck)->first();
			if($value->user_id == $msg->ident->userId){
				$this->users_data['user'] = [
					'id'			=> $value->user_id,
					'login'			=> $user->login,
					'player'		=> $user_identificator,					//Идентификатор поля пользователя
					'magic_effects'	=> unserialize($value->magic_effects),	//Список активных маг. эффектов
					'energy'		=> $user->user_energy,					//Колличество энергии пользователя
					'deck'			=> unserialize($value->user_deck),		//Колода пользователя
					'hand'			=> unserialize($value->user_hand),		//Рука пользователя
					'discard'		=> unserialize($value->user_discard),	//Отбой пользователя
					'current_deck'	=> $user->user_current_deck,			//Название фракции текущей колоды пользоватля
					'card_source'	=> $value->card_source,					//Источник карт (рука/колода/отбой) текущего хода
					'player_source'	=> $value->player_source,				//Источник карт игрока (свои/противника) текущего хода
					'cards_to_play'	=> unserialize($value->card_to_play),	//Массив определенных условиями действия карт при отыгрыше из колоды или отбое
					'round_passed'	=> $value->round_passed,				//Маркер паса
					'addition_data'	=> $value->addition_data,
					'battle_member_id'=> $value->id,						//ID текущей битвы
					'turn_expire'	=> $value->turn_expire,
					'time_shift'	=> $value->time_shift,
					'pseudonim'		=> 'user',
					'user_magic'	=> unserialize($user->user_magic),
					'card_images'	=> [
						'back'			=> $card_background->card_img,
						'flag'			=> BattleFieldController::getFractionFlag($user->user_current_deck)
					]
				];
				$this->users_data[$user_identificator] = &$this->users_data['user'];
				$this->users_data[$value->user_id] = &$this->users_data['user'];
			}else{
				$this->users_data['opponent'] = [
					'id'			=> $value->user_id,
					'login'			=> $user->login,
					'player'		=> $user_identificator,
					'magic_effects'	=> unserialize($value->magic_effects),
					'energy'		=> $user->user_energy,
					'deck'			=> unserialize($value->user_deck),
					'hand'			=> unserialize($value->user_hand),
					'discard'		=> unserialize($value->user_discard),
					'current_deck'	=> $user->user_current_deck,
					'card_source'	=> $value->card_source,
					'player_source'	=> $value->player_source,
					'cards_to_play'	=> unserialize($value->card_to_play),
					'round_passed'	=> $value->round_passed,
					'addition_data'	=> $value->addition_data,
					'battle_member_id'=> $value->id,
					'turn_expire'	=> $value->turn_expire,
					'time_shift'	=> $value->time_shift,
					'pseudonim'		=> 'opponent',
					'user_magic'	=> unserialize($user->user_magic),
					'card_images'	=> [
						'back'			=> $card_background->card_img,
						'flag'			=> BattleFieldController::getFractionFlag($user->user_current_deck)
					]
				];
				$this->users_data[$user_identificator] = &$this->users_data['opponent'];
				$this->users_data[$value->user_id] = &$this->users_data['opponent'];
			}
		}

		$this->step_status = [
			'added_cards'	=> [],
			'dropped_cards'	=> [],
			'played_card'	=> [
				'card'			=> [],
				'move_to'		=> [
						'player'	=> '',
						'row'		=> '',
						'user'		=> ''
					],
				'self_drop'		=> 0,
				'strength'		=> ''
			],
			'played_magic'	=> [],
			'actions'		=> [
				'appear'		=> [],
				'disappear'		=> [],
				'cards'			=> []
			],
			'counts'		=> [],
			'round_status'	=> [
				'round'			=> 0,
				'current_player'=> '',
				'card_source'	=> [],
				'activate_popup'=> '',
				'cards_to_play'	=> [],
				'status'		=> [],
			],
			'magic_usage'	=> [],
			'users_energy'	=> [],
			'timing'		=> '',
			'images'		=> []
		];
		$this->magic_usage = unserialize($battle->magic_usage);//Данные о использовании магии

		switch($msg->action){
			case 'userJoinedToRoom':
				if($battle->disconected_count > 0){
					$battle->disconected_count--;
					$battle->save();
				}
				$user_turn = ($battle->user_id_turn != 0)? $this->users_data[$battle->user_id_turn]['login']: '';

				if($battle->fight_status <= 1){
					if(2 == count($battle_members)){
						if(0 == $battle->fight_status){
							$battle->turn_expire = $timing_settings['card_change'] + time();
							$battle->fight_status = 1; // Подключилось нужное количество пользователей
							$battle->save();
						}

						$this->step_status['round_status']['current_player'] = $user_turn;
						$this->step_status['timing'] = $timing_settings['card_change'];

						$result = $this->step_status;
						$result['message'] = 'usersAreJoined';
						$result['joined_user'] = $this->users_data['user']['login'];
						$result['battleInfo'] = $msg->ident->battleId;

						self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
						self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
					}
				}

				if(2 == $battle->fight_status){
					$battle->save();
					$player_source = (empty($this->users_data['user']['player_source']))
						? $this->users_data['user']['player']
						: $this->users_data['user']['player_source'];

					$this->step_status['counts'] = self::getDecksCounts($this->users_data);
					$this->step_status['round_status']['round'] = $battle->round_count;
					$this->step_status['round_status']['current_player'] = $user_turn;
					$this->step_status['round_status']['card_source'] = [$player_source => $this->users_data['user']['card_source']];
					$this->step_status['round_status']['activate_popup'] = $this->users_data['user']['addition_data'];
					$cards_to_play = [];
					if(!empty($this->users_data['user']['cards_to_play'])){
						foreach($this->users_data['user']['cards_to_play'] as $card_data){
							$cards_to_play[] = BattleFieldController::cardData($card_data);
						}
					}
					$this->step_status['round_status']['cards_to_play'] = $cards_to_play;
					$this->step_status['users_energy'] = [
						$this->users_data['user']['login']	=> $this->users_data['user']['energy'],
						$this->users_data['opponent']['login']=> $this->users_data['opponent']['energy']
					];

					$this->step_status['timing'] = $timing_settings['card_change'];
					$this->step_status['images'] = [
						$this->users_data['user']['login'] => $this->users_data['user']['card_images'],
						$this->users_data['opponent']['login'] => $this->users_data['opponent']['card_images'],
					];

					$result = $this->step_status;
					$result['message'] = 'allUsersAreReady';
					$result['battleInfo'] = $msg->ident->battleId;

					self::sendMessageToSelf($from, $result);
				}
			break;

			case 'userReady':
				if(1 == $battle->fight_status){
					$ready_players_count = 0;//Количество игроков за столом готовых к игре
					foreach($battle_members as $key => $value){
						if(0 != $value->user_ready){
							$ready_players_count++;
						}
					}

					if(2 == $ready_players_count){
						$cursed_players = [];
						$player = 'p1';
						if($this->users_data['p1']['current_deck'] == 'cursed'){
							$cursed_players[] = $this->users_data['user']['player'];
							$player = 'p1';
						}
						if($this->users_data['p2']['current_deck'] == 'cursed'){
							$cursed_players[] = $this->users_data['opponent']['player'];
							$player = 'p2';
						}

						if($battle->user_id_turn < 1){
							if((1 == count($cursed_players)) && ($msg->ident->userId == $this->users_data[$player]['id'])){
								if(isset($msg->turn)){
									$players_turn = (($this->users_data['user']['login'] == $msg->turn) || ($msg->turn == ''))
										? $this->users_data['user']['id']
										: $this->users_data['opponent']['id'];
								}else{
									$rand = mt_rand(0, 1);
									$players_turn = ($rand == 0)? $this->users_data['p1']['id']: $this->users_data['p2']['id'];
								}
							}else{
								$rand = mt_rand(0, 1);
								$players_turn = ($rand == 0)? $this->users_data['p1']['id']: $this->users_data['p2']['id'];
							}
							$battle->user_id_turn = $players_turn;
							$battle->first_turn_user_id = $players_turn;
							$battle->save();
						}

						$user_timing = \DB::table('tbl_battle_members')->select('turn_expire')->where('user_id','=',$battle->user_id_turn)->first();
						$battle->turn_expire = $user_timing->turn_expire + time();

						$player_source = (empty($this->users_data['opponent']['player_source']))
							? $this->users_data['opponent']['player']
							: $this->users_data['opponent']['player_source'];

						$this->step_status['counts'] = self::getDecksCounts($this->users_data);
						$this->step_status['round_status']['round']			= $battle->round_count;
						$this->step_status['round_status']['current_player']= $this->users_data[$battle->user_id_turn]['login'];
						$this->step_status['round_status']['card_source']	= [$player_source => $this->users_data['opponent']['card_source']];
						$this->step_status['round_status']['activate_popup']= $this->users_data['user']['addition_data'];
						$this->step_status['round_status']['cards_to_play']	= $this->users_data['opponent']['cards_to_play'];
						$this->step_status['users_energy'] = [
							$this->users_data['user']['login']	=> $this->users_data['user']['energy'],
							$this->users_data['opponent']['login']=> $this->users_data['opponent']['energy']
						];
						$this->step_status['timing'] = $user_timing->turn_expire;
						$this->step_status['images'] = [
							$this->users_data['user']['login'] => $this->users_data['user']['card_images'],
							$this->users_data['opponent']['login'] => $this->users_data['opponent']['card_images'],
						];

						$result = $this->step_status;
						$result['message'] = 'allUsersAreReady';
						$result['battleInfo'] = $msg->ident->battleId;

						if($battle->fight_status <= 1){
							self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
						}
						$battle->fight_status = 2;
						$battle->save();

						$player_source = (empty($this->users_data['user']['player_source']))
							? $this->users_data['user']['player']
							: $this->users_data['user']['player_source'];

						$result['round_status']['card_source'] = [$player_source => $this->users_data['user']['card_source']];
						$result['round_status']['cards_to_play'] = $this->users_data['user']['cards_to_play'];

						self::sendMessageToSelf($from, $result);
					}else{
						$cursed_players = [];
						$player = 'p1';
						if($this->users_data['p1']['current_deck'] == 'cursed'){
							$cursed_players[] = $this->users_data['user']['player'];
							$player = 'p1';
						}
						if($this->users_data['p2']['current_deck'] == 'cursed'){
							$cursed_players[] = $this->users_data['opponent']['player'];
							$player = 'p2';
						}

						if((1 == count($cursed_players)) && ($msg->ident->userId == $this->users_data[$player]['id'])){
							if(isset($msg->turn)){
								$players_turn = (($this->users_data['p1']['login'] == $msg->turn) || ($msg->turn == ''))
									? $this->users_data['p1']['id']
									: $players_turn = $this->users_data['p2']['id'];
							}else{
								$players_turn = $this->users_data['user']['id'];
							}
							$battle->user_id_turn = $players_turn;
							$battle->save();
						}
					}
				}
			break;

			case 'userMadeAction':
				if($battle->fight_status == 2){

					//Данные о текущем пользователе
					$battle_field = unserialize($battle->battle_field);//Данные о поле битвы
					//Установка источника хода по умолчанию
					$this->users_data['user']['cards_to_play'] = [];
					$this->users_data['user']['player_source'] = $this->users_data['user']['player'];
					$this->users_data['user']['card_source'] = 'hand';

					$this->step_status['round_status']['cards_to_play'] = [];
					$this->step_status['round_status']['card_source'] = [$this->users_data['user']['player'] => 'hand'];
					$this->step_status['round_status']['activate_popup'] = '';
					$this->step_status['round_status']['round'] = $battle->round_count;

					//Ход следующего игрока
					if($this->users_data['opponent']['round_passed'] == 1){
						$this->step_status['round_status']['current_player'] = $this->users_data['user']['login'];
						$user_turn_id = $this->users_data['user']['id'];
					}else{
						$this->step_status['round_status']['current_player'] = $this->users_data['opponent']['login'];
						$user_turn_id = $this->users_data['opponent']['id'];
					}


					$self_drop = 0;
					if($msg->magic != ''){
						$disable_magic = false;
						$magic_id = Crypt::decrypt($msg->magic);
						$magic = BattleFieldController::magicData($magic_id);
						if(($this->users_data['user']['user_magic'][$magic_id]['used_times'] > 0) && ($this->users_data['user']['energy'] >= $magic['energy_cost'])){
							$this->users_data['user']['user_magic'][$magic_id]['used_times'] = $this->users_data['user']['user_magic'][$magic_id]['used_times'] - 1;
							$this->users_data['user']['energy'] = $this->users_data['user']['energy'] - $magic['energy_cost'];

							if(!isset($this->magic_usage[$this->users_data['user']['player']][$battle->round_count])){
								$this->magic_usage[$this->users_data['user']['player']][$battle->round_count] = [
									'id'	=> Crypt::decrypt($msg->magic),
									'allow'	=> '1'
								];
								$current_actions = $magic['actions'];
								$this->step_status['played_magic'][$this->users_data['user']['player']] = $magic;
							}else{
								$disable_magic = true;
							}
						}else{
							$disable_magic = true;
						}

						if($disable_magic){
							$current_actions = [];
						}

						\DB::table('users')->where('id', '=', $this->users_data['user']['id'])->update([
							'user_energy'	=> $this->users_data['user']['energy'],
							'user_magic'	=> serialize($this->users_data['user']['user_magic'])
						]);
					}


					if($msg->card != ''){
						$current_card_id = Crypt::decrypt($msg->card);
						$current_card = BattleFieldController::cardData($current_card_id);
						$current_card_row = (isset($msg->BFData->row))? self::strRowToInt($msg->BFData->row): 3;

						$current_card_field = (isset($msg->BFData->field))? $msg->BFData->field: 'mid';

						if($current_card['fraction'] == 'special'){
							if($current_card_row == 3){
								$battle_field['mid'][] = [
									'id'		=> $current_card_id,
									'caption'	=> $current_card['caption'],
									'login'		=> $this->users_data['user']['login']
								];
							}else{
								//Если логика карт предусматривает сразу уходить в отбой
								foreach($current_card['actions'] as $i => $action){
									switch($action['caption']){
										case 'cure':
										case 'heal':
										case 'regroup':
										case 'sorrow':
										case 'call':
										case 'killer':
											$this->users_data['user']['discard'][] = $current_card_id;
											$this->step_status['added_cards'][$this->users_data['user']['player']]['discard'][] = $current_card;
											$self_drop = 1;
										break;
										default:
											if(!empty($battle_field[$current_card_field][$current_card_row]['special'])){//Еcли в ряду уже есть спец карта
												$this->users_data[$current_card_field]['discard'][] = $battle_field[$current_card_field][$current_card_row]['special']['id'];
												$this->step_status['added_cards'][$this->users_data['user']['player']]['discard'][] = BattleFieldController::cardData($battle_field[$current_card_field][$current_card_row]['special']['id']);
											}
											$battle_field[$current_card_field][$current_card_row]['special'] = [
												'id'		=> $current_card_id,
												'caption'	=> $current_card['caption'],
												'login'		=> $this->users_data['user']['login']
											];
									}
								}
							}
						}else{
							$battle_field[$current_card_field][$current_card_row]['warrior'][] = [
								'id'		=> $current_card_id,
								'caption'	=> $current_card['caption'],
								'strength'	=> $current_card['strength'],
								'login'		=> $this->users_data['user']['login']
							];
						}

						$this->step_status['played_card'] = [
							'card'		=> $current_card,
							'move_to'	=> [
								'player'	=> $current_card_field,
								'row'		=> $current_card_row,
								'user'		=> $this->users_data['user']['login']
							],
							'self_drop'	=> $self_drop,
							'strength'	=> $current_card['strength']
						];

						//Если был задействован МЭ "Марионетка"
						if(
							(isset($this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['id']))
							&& ($this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['id'] == '19')
							&& ($this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['allow'] == 1)
						){
							$this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['allow'] = '0';
							$user_type = 'opponent';
						}else{
							$user_type = 'user';
						}

						//Убираем карту из текущй колоды
						$source = (isset($msg->source->p1))? $msg->source->p1: $msg->source->p2;
						$this->users_data[$user_type][$source] = self::dropCardFromDeck($this->users_data[$user_type][$source], $current_card['id']);
						if( ($source == 'deck') || ($source == 'discard') ){
							$this->step_status['dropped_cards'][$this->users_data[$msg->ident->userId]['player']][$source][] = $this->step_status['played_card']['card']['caption'];
						}
						$current_actions = $current_card['actions'];
					}

					//Применение действий
					$add_time = true;
					$view_cards_strength = false;
					foreach($current_actions as $action_iter => $action){
						$action_result = self::actionProcessing($action, $battle_field, $this->users_data, $this->step_status, $user_turn_id, $msg, $this->magic_usage, $battle);
						$this->step_status	= $action_result['step_status'];
						$this->users_data	= $action_result['users_data'];
						$battle_field		= $action_result['battle_field'];
						$user_turn_id		= $action_result['user_turn_id'];

						$this->magic_usage	= $action_result['magic_usage'];

						switch($action['caption']){
							case 'call':
							case 'heal':
							case 'peep_card':
								$add_time = false;
							break;

							case 'cure':
							case 'killer':
							case 'obscure':
							case 'regroup':
							case 'sorrow':
								$view_cards_strength = true;
							break;
						}
					}

					//Сортировка колод
					$this->users_data = self::sortDecksByStrength($this->users_data);

					$battle_info = BattleFieldController::battleInfo($battle, $battle_field, $this->users_data, $this->magic_usage, $this->step_status);

					$this->step_status = $battle_info['step_status'];
					$battle_field = $battle_info['battle_field'];
					$round_passed_summ = $this->users_data['user']['round_passed'] + $this->users_data['opponent']['round_passed'];

					if($view_cards_strength){
						$this->step_status['actions']['cards_strength'] = $battle_info['cards_strength'];
					}

					if($round_passed_summ < 1){
						if($add_time === true){
							$turn_expire = $timing_settings['step_time'];
							$showTimerOfUser = 'opponent';
						}else{
							$turn_expire = $msg->timing;
							$showTimerOfUser = 'user';
						}
					}else{
						$turn_expire = $timing_settings['step_time'];
						$showTimerOfUser = $this->users_data[$msg->ident->userId]['pseudonim'];
					}

					$this->step_status['timing'] = $turn_expire;//$this->users_data[$showTimerOfUser]['turn_expire'];

					\DB::table('tbl_battle_members')->where('id', '=', $this->users_data['user']['battle_member_id'])->update([
						'user_deck'		=> serialize($this->users_data['user']['deck']),
						'user_hand'		=> serialize($this->users_data['user']['hand']),
						'user_discard'	=> serialize($this->users_data['user']['discard']),
						'card_source'	=> $this->users_data['user']['card_source'],
						'player_source'	=> $this->users_data['user']['player_source'],
						'card_to_play'	=> serialize($this->users_data['user']['cards_to_play']),
						'round_passed'	=> '0',
						'addition_data'	=> $this->step_status['round_status']['activate_popup'],
						'turn_expire'	=> $turn_expire
					]);
					\DB::table('tbl_battle_members')->where('id', '=', $this->users_data['opponent']['battle_member_id'])->update([
						'user_deck'	=> serialize($this->users_data['opponent']['deck']),
						'user_hand'	=> serialize($this->users_data['opponent']['hand']),
						'user_discard'=> serialize($this->users_data['opponent']['discard'])
					]);

					$this->step_status['magic_usage'] = $this->magic_usage;
					//Сохраняем поле битвы
					$battle->battle_field	= serialize($battle_field);
					$battle->magic_usage	= serialize($this->magic_usage);
					$battle->user_id_turn	= $user_turn_id;
					$battle->turn_expire	= $this->users_data[$showTimerOfUser]['turn_expire'] + time();
					$battle->save();

					self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
				}
			break;

			case 'userPassed':
				$battle_field = unserialize($battle->battle_field);

				$enemy = ($msg->user == 'p1')? 'p2': 'p1';

				\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$msg->user]['battle_member_id'])->update([
					'round_passed' => 1,
					'turn_expire' => $msg->timing
				]);

				$users_passed_count = $this->users_data[$enemy]['round_passed'] + 1;

				//Если только один пасанувший
				if($users_passed_count == 1){
					$this->step_status['round_status']['current_player'] = $this->users_data[$enemy]['login'];
					$this->step_status['round_status']['status'] = ['passed_user' => $this->users_data[$msg->user]['login']];
					$this->step_status['round_status']['card_source'] = [$this->users_data[$enemy]['player'] => 'hand'];
					$this->step_status['round_status']['cards_to_play'] = [];
					$this->step_status['round_status']['round'] = $battle->round_count;
					$this->step_status['timing'] = $this->users_data[$enemy]['turn_expire'];

					$battle->user_id_turn = $this->users_data[$enemy]['id'];
					$battle->pass_count++;
					$battle->turn_expire = $this->users_data[$enemy]['turn_expire'] + time();
					$battle->save();

					self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
				}

				//Если спасовало 2 пользователя
				if($users_passed_count == 2){
					$battle_info = BattleFieldController::battleInfo($battle, $battle_field, $this->users_data, $this->magic_usage, $this->step_status);

					$this->step_status = $battle_info['step_status'];
					$field_status = $battle_info['field_status'];

					//Подсчет результатов раунда по очкам
					$total_score = self::calcStrByPlayers($field_status);
					$user_score = $total_score[$this->users_data['user']['player']];
					$opponent_score = $total_score[$this->users_data['opponent']['player']];

					//Статус битвы (очки раундов)
					$round_status = unserialize($battle->round_status);

					$gain_cards_count = ['user' => 1, 'opponent' => 1];//Количество дополнительных карт
					//Определение выигравшего
					switch(true){
						case $user_score > $opponent_score:
							$round_status[$this->users_data['user']['player']][] = 1;
							$round_result = 'Выграл '.$this->users_data['user']['login'];
							if($this->users_data['opponent']['current_deck'] == 'knight') $gain_cards_count['opponent'] = 2;
						break;
						case $user_score < $opponent_score:
							$round_status[$this->users_data['opponent']['player']][] = 1;
							$round_result = 'Выграл '.$this->users_data['opponent']['login'];
							if($this->users_data['user']['current_deck'] == 'knight') $gain_cards_count['user'] = 2;
						break;
						case $user_score == $opponent_score:
							//Если колода пользователя - нечисть и противник не играет нечистью
							if(
								( ($this->users_data['user']['current_deck'] == 'undead') || ($this->users_data['opponent']['current_deck'] == 'undead') )
								&& ($this->users_data['user']['current_deck'] != $this->users_data['opponent']['current_deck'])
							){
								if($this->users_data['user']['current_deck'] == 'undead'){
									$round_status[$this->users_data['user']['player']][] = 1;
									$round_result = 'Выграл '.$this->users_data['user']['login'];
									if($this->users_data['opponent']['current_deck'] == 'knight') $gain_cards_count['opponent'] = 2;
								}else{
									$round_status[$this->users_data['opponent']['player']][] = 1;
									$round_result = 'Выграл '.$this->users_data['opponent']['login'];
									if($this->users_data['user']['current_deck'] == 'knight') $gain_cards_count['user'] = 2;
								}
							}else{
								$round_status[$this->users_data['user']['player']][] = 1;
								$round_status[$this->users_data['opponent']['player']][] = 1;
								$round_result = 'Ничья';
							}
						break;
					}

					$wins_count = [
						$this->users_data['p1']['login'] => $round_status['p1'],
						$this->users_data['p2']['login'] => $round_status['p2']
					];

					$battle->round_count	= $battle->round_count +1;
					$battle->round_status	= serialize($round_status);
					$battle->magic_usage	= serialize($this->magic_usage);

					$clear_result	= self::clearBattleField($battle, $battle_field, $this->users_data, $this->magic_usage, $gain_cards_count, $this->step_status);
					$battle_field	= $clear_result['battle_field'];

					$this->users_data	= $clear_result['users_data'];
					$this->step_status	= $clear_result['step_status'];
					$this->step_status['actions']['cards_strength'] = $clear_result['cards_strength'];

					$battle->battle_field	= serialize($battle_field);

					$battle->undead_cards	= serialize($clear_result['deadless_cards']);
					$battle->pass_count		= 0;
					$battle->save();

					//Отправка результатов пользователям
					if((count($round_status['p1']) < 2) && (count($round_status['p2']) < 2)){
						$this->users_data = self::sortDecksByStrength($this->users_data);
						$this->step_status['counts'] = self::getDecksCounts($this->users_data);
						$this->step_status['round_status']['status'] = [
							'result'=> $round_result,
							'score'	=> $wins_count
						];
						$this->step_status['actions']['appear'] = self::getAppearActions($battle_field);
						foreach($this->step_status['added_cards'] as $player => $decks){
							if(isset($decks['hand'])){
								unset($this->step_status['added_cards'][$player]['hand']);
							}
						}

						$this->step_status['users_energy'] = [
							$this->users_data['user']['login']	=> $this->users_data['user']['energy'],
							$this->users_data['opponent']['login']=> $this->users_data['opponent']['energy']
						];

						$cursed_players = [];
						//timing and cursed player
						foreach($this->users_data as $type => $user_data){
							if(($type == 'user') || ($type == 'opponent')){
								$timing = $timing_settings['step_time'];
								if($this->users_data[$type]['current_deck'] == 'cursed'){
									$cursed_players[] = $this->users_data[$type]['player'];
								}
								\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$type]['battle_member_id'])->update(['turn_expire' => $timing]);
							}
						}

						if(count($cursed_players) == 1){
							$this->step_status['round_status']['activate_popup'] = 'activate_turn_choise';
							$user_turn_id = $this->users_data[$cursed_players[0]]['id'];

							\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$cursed_players[0]]['battle_member_id'])->update([
								'addition_data' => 'activate_turn_choise'
							]);
						}else{
							$type = ($this->users_data['user']['id'] == $battle->first_turn_user_id)? 'opponent': 'user';
							$user_turn_id = $this->users_data[$type]['id'];
						}

						$this->step_status['images'] = [
							$this->users_data['user']['login'] => $this->users_data['user']['card_images'],
							$this->users_data['opponent']['login'] => $this->users_data['opponent']['card_images'],
						];

						$this->step_status['round_status']['current_player'] = $this->users_data[$user_turn_id]['login'];
						$this->step_status['round_status']['card_source'] = [$this->users_data[$user_turn_id]['player'] => 'hand'];
						$this->step_status['magic_usage'] = $this->magic_usage;

						foreach($this->users_data as $user_type => $user){
							if(($user_type == 'user') || ($user_type == 'opponent')){
								$battle_data = BattleMembers::find($this->users_data[$user_type]['battle_member_id']);
								$battle_data['user_deck']		= serialize($this->users_data[$user_type]['deck']);
								$battle_data['user_hand']		= serialize($this->users_data[$user_type]['hand']);
								$battle_data['user_discard']	= serialize($this->users_data[$user_type]['discard']);
								$battle_data['card_source']		= 'hand';
								$battle_data['round_passed']	= '0';
								$battle_data['card_to_play']	= 'a:0:{}';
								$battle_data['player_source']	= $this->users_data[$user_type]['player'];
								$battle_data->save();
							}
						}

						$this->step_status['timing'] = $this->users_data[$user_turn_id]['turn_expire'];

						$battle->first_turn_user_id = $user_turn_id;
						$battle->user_id_turn = $user_turn_id;
						$battle->turn_expire = $this->step_status['timing'] + time();
						$battle->save();

						$result = $this->step_status;
						$result['message'] = 'roundEnds';
						$result['battleInfo'] = $msg->ident->battleId;
						$result['user_hand'] = self::getDeckCards($this->users_data['user']['hand']);
						$result['deck_slug'] = $this->users_data['user']['current_deck'];

						self::sendMessageToSelf($from, $result);
						$result['user_hand'] = self::getDeckCards($this->users_data['opponent']['hand']);
						$result['deck_slug'] = $this->users_data['opponent']['current_deck'];
						self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
					}else{
						$battle->fight_status = 3;
						$battle->save();

						if(count($round_status['p1']) > count($round_status['p2'])){
							$game_result	= 'Игру выграл '.$this->users_data['p1']['login'];
							$winner			= $this->users_data['p1']['id'];
							$to_self		= self::saveGameResults($this->users_data['p1']['id'], $battle, 'win');
							$to_enemy		= self::saveGameResults($this->users_data['p2']['id'], $battle, 'loose');
						}

						if(count($round_status['p1']) < count($round_status['p2'])){
							$game_result	= 'Игру выграл '.$this->users_data['p2']['login'];
							$winner			= $this->users_data['p2']['id'];
							$to_self		= self::saveGameResults($this->users_data['p2']['id'], $battle, 'win');
							$to_enemy		= self::saveGameResults($this->users_data['p1']['id'], $battle, 'loose');
						}

						if(count($round_status['p1']) == count($round_status['p2'])){
							if( ( ($this->users_data['user']['current_deck'] == 'undead') || ($this->users_data['opponent']['current_deck'] == 'undead') ) && ($this->users_data['user']['current_deck'] != $this->users_data['opponent']['current_deck']) ){
								if($this->users_data['user']['current_deck'] == 'undead'){
									$game_result= 'Игру выграл '.$this->users_data['user']['login'];
									$winner		= $this->users_data['user']['id'];
									$to_self	= self::saveGameResults($this->users_data['user']['id'], $battle, 'win');
									$to_enemy	= self::saveGameResults($this->users_data['opponent']['id'], $battle, 'loose');
								}else{
									$game_result= 'Игру выграл '.$this->users_data['opponent']['login'];
									$winner		= $this->users_data['opponent']['id'];
									$to_self	= self::saveGameResults($this->users_data['opponent']['id'], $battle, 'win');
									$to_enemy	= self::saveGameResults($this->users_data['user']['id'], $battle, 'loose');
								}
							}else{
								$game_result	= 'Игра сыграна в ничью';
								$winner			= '';
								$to_self		= self::saveGameResults($this->users_data['user']['id'], $battle, 'draw');
								$to_enemy		= self::saveGameResults($this->users_data['opponent']['id'], $battle, 'draw');
							}
						}

						\DB::table('users')->where('id','=',$this->users_data['user']['id'])->update(['user_busy' => 0]);
						\DB::table('users')->where('id','=',$this->users_data['opponent']['id'])->update(['user_busy' => 0]);

						$result = ['message' => 'gameEnds', 'gameResult' => $game_result, 'battleInfo' => $msg->ident->battleId];

						if(($winner == '') || ($winner == $msg->ident->userId)){
							$result['resources'] = $to_self;
							self::sendMessageToSelf($from, $result);
							$result['resources'] = $to_enemy;
							self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
						}else{
							$result['resources'] = $to_enemy;
							self::sendMessageToSelf($from, $result);
							$result['resources'] = $to_self;
							self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
						}
					}
				}
			break;

			case 'changeCardInHand':
				$card_id = Crypt::decrypt($msg->card);
				$users_battle_data = \DB::table('tbl_battle_members')->select('available_to_change')->find($this->users_data['user']['battle_member_id']);

				if($users_battle_data->available_to_change > 0){
					$rand = mt_rand(0, count($this->users_data['user']['deck']) - 1);
					$card_to_add = $this->users_data['user']['deck'][$rand];

					$this->step_status['dropped_cards'][$this->users_data['user']['player']]['deck'][] = BattleFieldController::getCardNaturalSetting($card_id);

					unset($this->users_data['user']['deck'][$rand]);
					$this->users_data['user']['deck'] = array_values($this->users_data['user']['deck']);

					foreach($this->users_data['user']['hand'] as $hand_iter => $hand_card_data){
						if($hand_card_data == $card_id){
							$this->step_status['added_cards'] = BattleFieldController::cardData($card_to_add);

							$this->users_data['user']['deck'][] = $this->users_data['user']['hand'][$hand_iter];
							unset($this->users_data['user']['hand'][$hand_iter]);
							$this->users_data['user']['hand'][] = $card_to_add;
							break;
						}
					}

					$this->users_data['user']['hand'] = array_values($this->users_data['user']['hand']);

					$users_battle_data->available_to_change--;

					\DB::table('tbl_battle_members')->where('id','=',$this->users_data['user']['battle_member_id'])
						->update([
							'user_deck'			=> serialize($this->users_data['user']['deck']),
							'user_hand'			=> serialize($this->users_data['user']['hand']),
							'available_to_change'=> $users_battle_data->available_to_change
						]);

					$this->users_data = self::sortDecksByStrength($this->users_data);

					$result = $this->step_status;

					$result['message'] = 'changeCardInHand';
					$result['can_change_cards'] = $users_battle_data->available_to_change;

					self::sendMessageToSelf($from, $result);
				}
			break;

			case 'getActiveRow':
				$id = Crypt::decrypt($msg->card);
				if($msg->type == 'card'){
					$card = \DB::table('tbl_cards')->select('card_type','card_race','allowed_rows','card_actions')->find($id);
					$actions_list = [];
					$actions = unserialize($card->card_actions);
					foreach($actions as $i => $action){
						$action = get_object_vars($action);
						$action_data = \DB::table('tbl_actions')->select('type')->find($action['action']);
						$actions_list[$i] = $action;
						$actions_list[$i]['caption'] = $action_data->type;
					}
					$result = [
						'message'	=> 'cardData',
						'fraction'	=> ($card->card_type == 'race')? $card->card_race: $card->card_type,
						'rows'		=> unserialize($card->allowed_rows),
						'actions'	=> $actions_list,
						'type'		=> $msg->type
					];
				}else{
					$magic = \DB::table('tbl_magic_effect')->select('effect_actions')->find($id);
					$actions_list = [];
					$actions = unserialize($magic->effect_actions);
					foreach($actions as $i => $action){
						$action = get_object_vars($action);
						$action_data = \DB::table('tbl_actions')->select('type')->find($action['action']);
						$actions_list[$i] = $action;
						$actions_list[$i]['caption'] = $action_data->type;
					}
					$result = [
						'message'	=> 'cardData',
						'actions'	=> $actions_list,
						'type'		=> $msg->type
					];
				}
				self::sendMessageToSelf($from, $result);
			break;

			case 'cartDescription':
				$data = ( (isset($msg->type)) && (!empty($msg->type)) )
					? BattleFieldController::getMagicDescription(Crypt::decrypt($msg->card))
					: BattleFieldController::getCardDescription(Crypt::decrypt($msg->card));

				$result = [
					'message'	=> 'cartDescription',
					'data'		=> $data
				];
				self::sendMessageToSelf($from, $result);
			break;

			case 'cursedWantToChangeTurn':
				$player = ($this->users_data['p1']['login'] == $msg->user)? 'p1': 'p2';

				$this->step_status['round_status']['current_player'] = $this->users_data[$player]['login'];
				$this->step_status['round_status']['card_source'] = [$player => 'hand'];

				$turn_expire = $msg->time;
				if($turn_expire > $timing_settings['step_time']){
					$turn_expire = $timing_settings['step_time'];
				}

				$battle->user_id_turn = $this->users_data[$player]['id'];;
				$battle->turn_expire = $turn_expire + time();
				$battle->save();

				\DB::table('tbl_battle_members')
					->where('id', '=', $this->users_data[$msg->ident->userId]['battle_member_id'])
					->update([
						'addition_data'	=> '',
						'round_passed'	=> '0',
						'turn_expire'	=> $turn_expire
					]);
				$showTimerOfUser = $this->users_data[$player]['pseudonim'];

				$this->step_status['timing'] = $this->users_data[$showTimerOfUser]['turn_expire'];
				self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
			break;

			case 'userGivesUp':
				$battle->fight_status = 3;
				$battle->save();
				$game_result = 'Игру выграл '.$this->users_data['opponent']['login'];
				$winner = $this->users_data['opponent']['id'];
				$to_self = self::saveGameResults($this->users_data['opponent']['id'], $battle, 'win', 'leave');
				$to_enemy = self::saveGameResults($this->users_data['user']['id'], $battle, 'loose', 'leave');

				\DB::table('users')->where('id','=',$this->users_data['user']['id'])->update(['user_busy' => 0]);
				\DB::table('users')->where('id','=',$this->users_data['opponent']['id'])->update(['user_busy' => 0]);

				$result = ['message' => 'gameEnds', 'gameResult' => $game_result, 'battleInfo' => $msg->ident->battleId];

				if( ($winner == '') || ($winner == $msg->ident->userId) ){
					$result['resources'] = $to_self;
					self::sendMessageToSelf($from, $result);
					$result['resources'] = $to_enemy;
					self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
				}else{
					$result['resources'] = $to_enemy;
					self::sendMessageToSelf($from, $result);
					$result['resources'] = $to_self;
					self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
				}
			break;

			case 'dropCard':
				if($msg->player != $this->users_data['user']['player']){
					$position = -1;
					foreach ($this->users_data[$msg->player][$msg->deck] as $card_iter => $card_data) {
						if ($card_data == Crypt::decrypt($msg->card)) {
							$position = $card_iter;
							break;
						}
					}
					if ($position >= 0) {
						$card = BattleFieldController::cardData($this->users_data[$msg->player][$msg->deck][$position]);
						$this->step_status['dropped_cards'][$msg->player][$msg->deck][] = $card['caption'];

						unset($this->users_data[$msg->player][$msg->deck][$position]);
						$this->users_data[$msg->player][$msg->deck] = array_values($this->users_data[$msg->player][$msg->deck]);

						\DB::table('tbl_battle_members')->where('id', '=', $this->users_data[$msg->player]['battle_member_id'])->update([
							'user_' . $msg->deck => serialize($this->users_data[$msg->player][$msg->deck])
						]);
						$result = $this->step_status;
						$result['message'] = 'dropCard';
						self::sendMessageToSelf($from, $result);
						self::sendMessageToOthers($from, $result, $SplBattleObj[$msg->ident->battleId]);
					}
				}
			break;

			case 'returnCardToHand':
				$battle_field = unserialize($battle->battle_field);
				$this->magic_usage = unserialize($battle->magic_usage);

				$player = $this->users_data[$msg->ident->userId]['player'];
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);

				$turn_expire = $timing_settings['step_time'];
				\DB::table('tbl_battle_members')
					->where('id','=',$this->users_data[$msg->ident->userId]['battle_member_id'])
					->update(['turn_expire' => $turn_expire]);

				foreach($battle_field[$player] as $row => $row_data){
					foreach($row_data['warrior'] as $card_iter => $card_data){
						if($card_data['id'] == Crypt::decrypt($msg->card)){
							$card = BattleFieldController::cardData($card_data['id']);
							$this->users_data[$player]['hand'][] = $card_data['id'];
							$this->step_status['added_cards'][$player]['hand'][] = $card;
							$this->step_status['dropped_cards'][$player][$row][$card_iter] = $card['caption'];
							$this->step_status['actions']['regroup_card'] = $card;
							$this->step_status['actions']['appear'][$player][$row][$card_iter] = 'regroup';
							unset($battle_field[$player][$row]['warrior'][$card_iter]);
							$battle_field[$player][$row]['warrior'] = array_values($battle_field[$player][$row]['warrior']);
							break 2;
						}
					}
				}

				$battle_info = BattleFieldController::battleInfo($battle, $battle_field, $this->users_data, $this->magic_usage, $this->step_status);

				$this->step_status['actions']['cards_strength'] = $battle_info['cards_strength'];

				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				foreach($field_buffs as $field => $rows){
					if(!isset($new_field_buffs[$field])){
						$this->step_status['actions']['disappear'][$field] = $field_buffs[$field];
					}else{
						foreach($rows as $row => $row_data){
							if(isset($new_field_buffs[$field][$row])){
								$this->step_status['actions']['disappear'][$field][$row] = array_diff($field_buffs[$field][$row], $new_field_buffs[$field][$row]);
							}else{
								$this->step_status['actions']['disappear'][$field][$row] = $field_buffs[$field][$row];
							}
						}
					}
				}
				if(isset($this->step_status['actions']['disappear'])){
					foreach($this->step_status['actions']['disappear'] as $action_player => $rows){
						foreach($rows as $row => $data){
							if(empty($this->step_status['actions']['disappear'][$action_player][$row])){
								unset($this->step_status['actions']['disappear'][$action_player][$row]);
							}
						}
						if(empty($this->step_status['actions']['disappear'][$action_player])){
							unset($this->step_status['actions']['disappear'][$action_player]);
						}
					}
				}

				$this->users_data[$player]['addition_data'] = [];
				$this->users_data = self::sortDecksByStrength($this->users_data);
				\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$msg->ident->userId]['battle_member_id'])
					->update([
						'user_hand'		=> serialize($this->users_data[$player]['hand']),
						'addition_data'	=> '',
						'card_to_play'	=> 'a:0:{}',
						'card_source'	=> 'hand'
					]);

				$user_type = (0 != $this->users_data['opponent']['round_passed'])? 'user': 'opponent';

				$this->step_status['timing'] = $turn_expire;
				$battle->battle_field	= serialize($battle_field);
				$battle->user_id_turn	= $this->users_data[$user_type]['id'];
				$battle->turn_expire	= $this->step_status['timing'] + time();
				$battle->save();

				$card_image = ($msg->type == 'card')
					? \DB::table('tbl_cards')->select('img_url')->where('slug','=','peregruppirovka')->first()
					: \DB::table('tbl_magic_effect')->select('img_url')->where('slug','=','taktika')->first();

				$this->step_status['actions']['regroup_img'] = '/img/card_images/'.$card_image->img_url;

				$this->step_status['round_status']['activate_popup'] = '';
				$this->step_status['round_status']['card_source'] = [$this->users_data[$user_type]['player'] => 'hand'];
				$this->step_status['round_status']['cards_to_play'] = [];
				$this->step_status['round_status']['current_player'] = $this->users_data[$user_type]['login'];
				$this->step_status['round_status']['round'] = $battle->round_count;
				$this->step_status['actions']['type'] = $msg->type;

				self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
			break;
		}
	}

	//Service functions
	protected static function sendUserMadeAction($users_data, $step_status, $msg, $SplBattleObj, $from){
		$step_status['counts'] = self::getDecksCounts($users_data);

		$step_status['images'] = [
			$users_data['user']['login'] => $users_data['user']['card_images'],
			$users_data['opponent']['login'] => $users_data['opponent']['card_images'],
		];

		$step_status['users_energy'] = [
			$users_data['user']['login']	=> $users_data['user']['energy'],
			$users_data['opponent']['login']=> $users_data['opponent']['energy']
		];

		$result = $step_status;
		$result['message'] = 'userMadeAction';
		$result['battleInfo'] = $msg->ident->battleId;
		$result['passed_user'] = '';
		$result['deck_slug'] = $users_data['user']['current_deck'];

		if(($users_data['opponent']['round_passed'] + $users_data['user']['round_passed']) == 1){
			$result['passed_user'] = ($users_data['opponent']['round_passed'] > 0)? $users_data['opponent']['login']: $users_data['user']['login'];
		}

		self::sendMessageToSelf($from, $result); //Отправляем результат отправителю

		foreach($result['added_cards'] as $player => $decks){
			unset($result['added_cards'][$player]['hand']);
		}
		foreach($result['dropped_cards'] as $player => $decks){
			unset($result['dropped_cards'][$player]['deck']);
		}

		if( ($users_data['opponent']['login'] != $result['round_status']['current_player']) || ($step_status['actions']['appear'] == 'peep_card') ){
			$result['round_status']['cards_to_play'] = [];
			$result['round_status']['activate_popup'] = '';
		}
		$result['deck_slug'] = $users_data['opponent']['current_deck'];
		self::sendMessageToOthers($from, $result, $SplBattleObj[$msg->ident->battleId]);
	}

	protected static function getDecksCounts($users_data, $player = 'both'){
		if($player == 'both'){
			$result = [
				'p1' => [
					'deck'		=> count($users_data['p1']['deck']),
					'discard'	=> count($users_data['p1']['discard']),
					'hand'		=> count($users_data['p1']['hand']),
				],
				'p2' => [
					'deck'		=> count($users_data['p2']['deck']),
					'discard'	=> count($users_data['p2']['discard']),
					'hand'		=> count($users_data['p2']['hand']),
				]
			];
		}else{
			$result = [
				$player => [
					'deck'		=> count($users_data[$player]['deck']),
					'discard'	=> count($users_data[$player]['discard']),
					'hand'		=> count($users_data[$player]['hand']),
				]
			];
		}
		return $result;
	}

	protected static function sortDecksByStrength($users_data){
		$users_data['user']['deck'] = BattleFieldController::recontentDecks($users_data['user']['deck']);
		$users_data['user']['discard'] = BattleFieldController::recontentDecks($users_data['user']['discard']);
		$users_data['user']['hand'] = BattleFieldController::recontentDecks($users_data['user']['hand']);
		$users_data['opponent']['hand'] = BattleFieldController::recontentDecks($users_data['opponent']['hand']);
		$users_data['opponent']['discard'] = BattleFieldController::recontentDecks($users_data['opponent']['discard']);
		$users_data['opponent']['deck'] = BattleFieldController::recontentDecks($users_data['opponent']['deck']);

		BattleFieldController::sortingDeck($users_data['user']['deck']);
		BattleFieldController::sortingDeck($users_data['user']['discard']);
		BattleFieldController::sortingDeck($users_data['user']['hand']);
		BattleFieldController::sortingDeck($users_data['opponent']['deck']);
		BattleFieldController::sortingDeck($users_data['opponent']['discard']);
		BattleFieldController::sortingDeck($users_data['opponent']['hand']);

		$users_data['user']['deck'] = self::refillDeckWithIds($users_data['user']['deck']);
		$users_data['user']['discard'] = self::refillDeckWithIds($users_data['user']['discard']);
		$users_data['user']['hand'] = self::refillDeckWithIds($users_data['user']['hand']);
		$users_data['opponent']['hand'] = self::refillDeckWithIds($users_data['opponent']['hand']);
		$users_data['opponent']['discard'] = self::refillDeckWithIds($users_data['opponent']['discard']);
		$users_data['opponent']['deck'] = self::refillDeckWithIds($users_data['opponent']['deck']);
		return $users_data;
	}

	protected static function refillDeckWithIds($deck){
		$result = [];
		foreach($deck as $i => $card){
			$result[$i] = $card['id'];
		}
		return $result;
	}

	protected static function strRowToInt($field){
		switch($field){ //Порядковый номер поля
			case 'meele':		$field_row = 0; break;
			case 'range':		$field_row = 1; break;
			case 'superRange':	$field_row = 2; break;
			case 'sortable-cards-field-more':$field_row = 3; break;
		}
		return $field_row;
	}

	protected static function dropCardFromDeck($deck, $card_id){
		if(strlen($card_id) > 11){
			$card_id = Crypt::decrypt($card_id);
		}
		$deck = array_values($deck);
		//Количество карт в входящей колоде
		foreach($deck as $card_iter => $card){
			if($card == $card_id){
				unset($deck[$card_iter]);
				break;
			}
		}
		$deck = array_values($deck);
		return $deck;
	}

	protected static function actionProcessing($action, $battle_field, $users_data, $step_status, $user_turn_id, $msg, $magic_usage, $battle){
		switch($action['caption']){
			case 'block_magic'://БЛОКИРОВКА МАГИИ
				$magic_usage[$users_data['opponent']['player']][0] = ['id' => Crypt::decrypt($msg->magic), 'allow'=>'0'];
				$magic_usage[$users_data['opponent']['player']][1] = ['id' => Crypt::decrypt($msg->magic), 'allow'=>'0'];
				$magic_usage[$users_data['opponent']['player']][2] = ['id' => Crypt::decrypt($msg->magic), 'allow'=>'0'];
				$step_status['actions']['appear'] = $action['caption'];
			break;

			case 'call'://ПРИЗЫВ
				$users_data['user']['card_source'] = 'deck';
				$action_data = [
					'deckChoise'	=> $action['summon_deckChoise'],
					'typeOfCard'	=> $action['summon_typeOfCard'],
					'cardChoise'	=> $action['summon_cardChoise'],
					'ignoreImmunity'=> $action['summon_ignoreImmunity']
				];
				if(isset($action['summon_type_singleCard']))$action_data['type_singleCard'] = $action['summon_type_singleCard'];
				if(isset($action['summon_type_actionRow']))	$action_data['type_actionRow'] = $action['summon_type_actionRow'];
				if(isset($action['summon_type_cardType']))	$action_data['type_cardType'] = $action['summon_type_cardType'];
				if(isset($action['summon_type_group']))		$action_data['type_group'] = $action['summon_type_group'];

				$summon_result = self::makeHealOrSummon($users_data, $step_status, $action_data);
				//card activates after user action
				$users_data		= $summon_result['users_data'];
				$user_turn_id	= $summon_result['user_turn_id'];
				$step_status	= $summon_result['step_status'];

				$step_status['actions'][] = $action['caption'];
			break;

			case 'cure'://ИСЦЕЛЕНИЕ
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				foreach($battle_field['mid'] as $card_data){
					$user_type = ($users_data['user']['login'] == $card_data['login'])? 'user': 'opponent';
					$users_data[$user_type]['discard'][] = $card_data['id'];
					$card = BattleFieldController::cardData($card_data['id']);

					$step_status['added_cards'][$users_data[$user_type]['player']]['discard'][] = $card;
					$step_status['dropped_cards'][$users_data[$user_type]['player']]['mid'][] = $card['caption'];
				}
				$battle_field['mid'] = [];
				$step_status['actions']['appear'] = $action['caption'];

				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				foreach($field_buffs as $field => $rows){
					if(!isset($new_field_buffs[$field])){
						$step_status['actions']['disappear'][$field] = $field_buffs[$field];
					}else{
						foreach($rows as $row => $row_data){
							if(isset($new_field_buffs[$field][$row])){
								$step_status['actions']['disappear'][$field][$row] = array_values(array_diff($field_buffs[$field][$row], $new_field_buffs[$field][$row]));
							}else{
								$step_status['actions']['disappear'][$field][$row] = $field_buffs[$field][$row];
							}
						}
					}
				}
			break;

			case 'drop_card'://CБРОС КАРТ ПРОТИВНИКА В ОТБОЙ
				if(!empty($users_data['opponent']['hand'])){
					for($i=0; $i < $action['enemyDropHand_cardCount']; $i++){
						$rand = mt_rand(0, count($users_data['opponent']['hand'])-1);
						$users_data['opponent']['discard'][] = $users_data['opponent']['hand'][$rand];
						$card = BattleFieldController::getCardNaturalSetting($users_data['opponent']['hand'][$rand]);

						$step_status['dropped_cards'][$users_data['opponent']['player']]['hand'][$rand] = $card['caption'];
						$step_status['added_cards'][$users_data['opponent']['player']]['discard'][] = $card;

						unset($users_data['opponent']['hand'][$rand]);
						$users_data['opponent']['hand'] = array_values($users_data['opponent']['hand']);
					}
					$step_status['actions']['appear'] = $action['caption'];
				}
			break;

			case 'heal'://ЛЕКАРЬ
				$users_data['user']['card_source'] = 'discard';
				$action_data = [
					'deckChoise'	=> $action['healer_deckChoise'],
					'typeOfCard'	=> $action['healer_typeOfCard'],
					'cardChoise'	=> $action['healer_cardChoise'],
					'ignoreImmunity'=> $action['healer_ignoreImmunity']
				];
				if(isset($action['healer_type_singleCard']))$action_data['type_singleCard'] = $action['healer_type_singleCard'];
				if(isset($action['healer_type_actionRow']))	$action_data['type_actionRow'] = $action['healer_type_actionRow'];
				if(isset($action['healer_type_cardType']))	$action_data['type_cardType'] = $action['healer_type_cardType'];
				if(isset($action['healer_type_group']))		$action_data['type_group'] = $action['healer_type_group'];

				$heal_result = self::makeHealOrSummon($users_data, $step_status, $action_data);
				//card activates after user action
				$user_turn_id	= $heal_result['user_turn_id'];
				$users_data		= $heal_result['users_data'];
				$step_status	= $heal_result['step_status'];
			break;

			case 'killer'://УБИЙЦА
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				//Может ли бить своих
				$players = ( (isset($action['killer_atackTeamate'])) && ($action['killer_atackTeamate']== 1) )? $players = ['p1', 'p2'] : [$users_data['opponent']['player']];
				//наносит удат по группе
				$groups = $action['killer_group'];

				$strength_limit_to_kill = ($action['killer_enemyStrenghtLimitToKill'] < 1) ? 999: $action['killer_enemyStrenghtLimitToKill'];

				$rows_strength = [];//Сумарная сила выбраных рядов
				$max_strength = 0;  // максимальная сила карты
				$min_strength = 999;// минимальная сила карты
				$card_strength_set = [];//набор силы карты для выбора случйного значения силы

				$cards_to_destroy = [];
				foreach($players as $player){
					foreach($action['killer_ActionRow'] as $row){
						foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
							if(isset($rows_strength[$player][$row])){
								$rows_strength[$player][$row] += $card_data['strength'];
							}else{
								$rows_strength[$player][$row] = $card_data['strength'];
							}
							if(!empty($groups)){
								$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
								foreach($card['group'] as $group_id){
									if(in_array($group_id, $groups)){

										if($card_data['strength'] < $strength_limit_to_kill){
											if($max_strength < $card_data['strength']){
												$max_strength = $card_data['strength'];// максимальная сила карты
											}
											if($min_strength > $card_data['strength']){
												$min_strength = $card_data['strength'];// минимальная сила карты
											}
											$card_strength_set[] = $card_data['strength'];
											$cards_to_destroy[$player][$row][$card_iter] = [
												'id'		=> $card_data['id'],
												'strength'	=> $card_data['strength']
											];
										}
									}
								}
							}else{
								$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
								$allow_by_immune = BattleFieldController::checkForSimpleImmune($action['killer_ignoreKillImmunity'], $card['actions']);
								if($allow_by_immune){

									if($card_data['strength'] < $strength_limit_to_kill){
										if($max_strength < $card_data['strength']){
											$max_strength = $card_data['strength'];// максимальная сила карты
										}
										if($min_strength > $card_data['strength']){
											$min_strength = $card_data['strength'];// минимальная сила карты
										}
										$cards_to_destroy[$player][$row][$card_iter] = [
											'id'		=> $card_data['id'],
											'strength'	=> $card_data['strength']
										];
									}
								}
							}
						}
					}
				}

				switch($action['killer_killedQuality_Selector']){
					case '0':	$card_strength_to_kill = $min_strength; break;//Самую слабую
					case '1':	$card_strength_to_kill = $max_strength; break;//Самую сильную
					case '2':	//Самую Случайную
						$random = mt_rand(0, count($card_strength_set)-1);
						$card_strength_to_kill = $card_strength_set[$random];
					break;
				}

				$card_to_kill = [];
				foreach($cards_to_destroy as $player => $rows){
					foreach($rows as $row => $cards){
						foreach($cards as $card_iter => $card_data){
							$allow_to_kill_by_force_amount = true;
							if($action['killer_recomendedTeamateForceAmount_OnOff'] > 0){
								$row_summ = 0;
								foreach($action['killer_recomendedTeamateForceAmount_ActionRow'] as $row_to_calculate){
									if(isset($rows_strength[$player][$row_to_calculate])){
										$row_summ += $rows_strength[$player][$row_to_calculate];
									}
								}
								switch($action['killer_recomendedTeamateForceAmount_Selector']){
									case '0':	//Больше указаного значения
										$allow_to_kill_by_force_amount = ($action['killer_recomendedTeamateForceAmount_OnOff'] <= $row_summ) ? true : false; break;
									case '1':	//Меньше указанного значения
										$allow_to_kill_by_force_amount = ($action['killer_recomendedTeamateForceAmount_OnOff'] >= $row_summ) ? true : false; break;
									case '2':	//Равно указанному значению
										$allow_to_kill_by_force_amount = ($action['killer_recomendedTeamateForceAmount_OnOff'] == $row_summ) ? true : false; break;
								}
							}
							switch($action['killer_killedQuality_Selector']){
								case '0':
									if(($card_data['strength'] <= $card_strength_to_kill) && ($allow_to_kill_by_force_amount) ){
										$card_to_kill[$player][$row][$card_iter] = $card_data;
									}
								break;
								case '1':
									if(($card_data['strength'] >= $card_strength_to_kill) && ($allow_to_kill_by_force_amount) ){
										$card_to_kill[$player][$row][$card_iter] = $card_data;
									}
								break;
								case '2':
									if(($card_data['strength'] == $card_strength_to_kill) && ($allow_to_kill_by_force_amount) ){
										$card_to_kill[$player][$row][$card_iter] = $card_data;
									}
								break;
							}
						}
					}
				}

				if($action['killer_killAllOrSingle'] == 0){
					$temp = [];
					foreach($card_to_kill as $player => $row_data){
						foreach($row_data as $row => $cards_to_kill){
							foreach($cards_to_kill as $card_iter => $card){
								$temp[$player][$row][$card_iter] = $card;
								break 3;
							}
						}
					}
					$card_to_kill = $temp;
				}

				foreach($card_to_kill as $player => $row_data){
					foreach($row_data as $row => $cards_to_kill){
						foreach($cards_to_kill as $card_iter => $card_to_kill){
							$users_data[$player]['discard'][] = $card_to_kill['id'];

							$card = BattleFieldController::cardData($card_to_kill['id']);

							$step_status['actions']['appear'][$player][$row][$card_iter] = 'killer';

							$step_status['added_cards'][$player]['discard'][] = $card;
							unset($battle_field[$player][$row]['warrior'][$card_iter]);
						}
						$battle_field[$player][$row]['warrior'] = array_values($battle_field[$player][$row]['warrior']);
					}
				}

				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				foreach($field_buffs as $field => $rows){
					if(!isset($new_field_buffs[$field])){
						$step_status['actions']['disappear'][$field] = $field_buffs[$field];
					}else{
						foreach($rows as $row => $row_data){
							if(isset($new_field_buffs[$field][$row])){
								$step_status['actions']['disappear'][$field][$row] = array_diff($field_buffs[$field][$row], $new_field_buffs[$field][$row]);
							}else{
								$step_status['actions']['disappear'][$field][$row] = $field_buffs[$field][$row];
							}
						}
					}
				}
			break;

			case 'master'://ПОВЕЛИТЕЛЬ
				$cards_can_be_added = [];
				foreach($action['master_cardSource'] as $destination){
					foreach($users_data['user'][$destination] as $card_data){
						$card = BattleFieldController::cardData($card_data);
						if(!empty($card['group'])){
							if(!empty(array_intersect($action['master_group'], $card['group']))){
								if($card['strength'] <= $action['master_maxCardsStrenght']){
									$cards_can_be_added[] = [
										'id'		=> $card_data,
										'strength'	=> $card['strength'],
										'source_deck'=>$destination
									];
								}
							}
						}
					}
				}
				switch($action['master_summonByModificator']){
					case '0': usort($cards_can_be_added, function($a, $b){return ($a['strength'] - $b['strength']);}); break;
					case '1': usort($cards_can_be_added, function($a, $b){return ($b['strength'] - $a['strength']);});break;
					case '2':
						$cards_shuffle_keys = array_keys($cards_can_be_added);
						shuffle($cards_shuffle_keys);
						array_merge(array_flip($cards_shuffle_keys), $cards_can_be_added);
					break;
				}

				$cards_to_add = ['hand'=> [], 'deck'=>[], 'discard'=>[]];
				$n = (count($cards_can_be_added) < $action['master_maxCardsSummon'])? count($cards_can_be_added): $action['master_maxCardsSummon'];
				for($i=0; $i<$n; $i++){
					$cards_to_add[$cards_can_be_added[$i]['source_deck']][] = $cards_can_be_added[$i]['id'];
				}

				if($n > 0){
					$cards_count = 0;
					foreach($cards_to_add as $destination => $cards){
						if(!empty($cards)){
							foreach($users_data['user'][$destination] as $card_to_summon_iter => $card_to_summon){
								$card = BattleFieldController::cardData($card_to_summon);
								if(in_array($card_to_summon, $cards)){
									if(count($card['allowed_rows']) > 1){
										$rand = mt_rand(0, count($card['allowed_rows'])-1);
										$action_row = $card['allowed_rows'][$rand];
									}else{
										$action_row = $card['allowed_rows'][0];
									}
									//Move card to battle_field
									$battle_field[$users_data['user']['player']][$action_row]['warrior'][] = [
										'id'		=> $card_to_summon,
										'strength'	=> $card['strength'],
										'login'		=> $users_data['user']['login']
									];
									$step_status['added_cards'][$users_data['user']['player']][$action_row][] = $card;
									$step_status['actions']['appear'][$users_data['user']['player']][$action_row][$card_to_summon_iter] = 'master';

									$step_status['dropped_cards'][$users_data['user']['player']][$destination][$card_to_summon_iter] = $card['caption'];
									unset($users_data['user'][$destination][$card_to_summon_iter]);
									$cards_count++;
								}
								if($cards_count >= $action['master_maxCardsSummon']){
									$users_data['user'][$destination] = array_values($users_data['user'][$destination]);
									break 2;
								}
							}
							$users_data['user'][$destination] = array_values($users_data['user'][$destination]);
						}
					}
				}
			break;

			case 'obscure'://ОДУРМАНИВАНИЕ
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				$cards_can_be_obscured = [];
				$min_strength = 999;
				$max_strength = 0;

				foreach($action['obscure_ActionRow'] as $row_iter => $row){
					foreach($battle_field[$users_data['opponent']['player']][$row]['warrior'] as $card_data){
						$card = BattleFieldController::cardData($card_data['id']);
						if($card_data['strength'] <= $action['obscure_maxCardStrength']){
							$allow_obscure = BattleFieldController::checkForSimpleImmune($action['obscure_ignoreImmunity'], $card['actions']);

							if($allow_obscure){
								$max_strength = ($card_data['strength'] > $max_strength)
									? $card_data['strength']
									: $max_strength;
								$min_strength = ($card_data['strength'] < $min_strength)
									? $card_data['strength']
									: $min_strength;

								$cards_can_be_obscured[] = [
									'id'		=> $card['id'],
									'caption'	=> $card_data['caption'],
									'strength'	=> $card_data['strength'],
									'row'		=> $row
								];
							}
						}
					}
				}

				if($min_strength < 1) $min_strength = 1;

				if(!empty($cards_can_be_obscured)){
					switch($action['obscure_strenghtOfCard']){
						case '0': $card_strength_to_obscure = $min_strength; break;//Самую слабую
						case '1': $card_strength_to_obscure = $max_strength; break;//Самую сильную
						case '2':
							$random = mt_rand(0, count($cards_can_be_obscured)-1);
							$card_strength_to_obscure = $cards_can_be_obscured[$random]['strength'];
						break;
					}
				}

				$cards_to_obscure = [];
				if(!empty($cards_can_be_obscured)){
					for($i=0; $i < $action['obscure_quantityOfCardToObscure']; $i++){
						for($j=0; $j<count($cards_can_be_obscured); $j++){
							if($card_strength_to_obscure == $cards_can_be_obscured[$j]['strength']){
								$cards_to_obscure[] = $cards_can_be_obscured[$j];
								break;
							}
						}
					}
				}
				for($i=0; $i<count($cards_to_obscure); $i++){
					foreach($battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior'] as $j => $card_data){
						if(Crypt::decrypt($cards_to_obscure[$i]['id']) == $card_data['id']){
							$battle_field[$users_data['user']['player']][$cards_to_obscure[$i]['row']]['warrior'][] = [
								'id'		=> $card_data['id'],
								'caption'	=> $card_data['caption'],
								'strength'	=> $card_data['strength'],
								'login'		=> $users_data['user']['login']
							];
							$card_obscured = BattleFieldController::cardData($card_data['id']);
							$step_status['added_cards'][$users_data['user']['player']][$cards_to_obscure[$i]['row']][] = $card_obscured;
							$step_status['dropped_cards'][$users_data['opponent']['player']][$cards_to_obscure[$i]['row']][$j] = $card_obscured['caption'];
							$step_status['actions']['appear'][$users_data['opponent']['player']][$cards_to_obscure[$i]['row']][$j] = 'obscure';
							unset($battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior'][$j]);
							$battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior'] = array_values($battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior']);
							break;
						}
					}
				}

				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				foreach($field_buffs as $field => $rows){
					if(!isset($new_field_buffs[$field])){
						$step_status['actions']['disappear'][$field] = $field_buffs[$field];
					}else{
						foreach($rows as $row => $row_data){
							if(isset($new_field_buffs[$field][$row])){
								$step_status['actions']['disappear'][$field][$row] = array_diff($field_buffs[$field][$row], $new_field_buffs[$field][$row]);
							}else{
								$step_status['actions']['disappear'][$field][$row] = $field_buffs[$field][$row];
							}
						}
					}
				}
			break;

			case 'peep_card'://ПРОСМОТР КАРТ ПРОТИВНИКА
				$temp_hand = $users_data['opponent']['hand'];
				$n = (count($users_data['opponent']['hand']) < $action['overview_cardCount'])? count($users_data['opponent']['hand']): $action['overview_cardCount'];
				while(count($users_data['user']['cards_to_play']) < $n){
					$rand = mt_rand(0, count($temp_hand)-1);
					$temp_card = $temp_hand[$rand];
					$users_data['user']['cards_to_play'][] = $temp_card;
					$step_status['round_status']['cards_to_play'][] = BattleFieldController::cardData($temp_card);
					unset($temp_hand[$rand]);
					$temp_hand = array_values($temp_hand);
				}
				$step_status['round_status']['activate_popup'] = 'activate_view';
				if(count($users_data['user']['cards_to_play']) > 0){
					$step_status['actions']['appear'] = $action['caption'];
				}
			break;

			case 'regroup'://ПЕРЕГРУППИРОВКА
				foreach($battle_field[$users_data['user']['player']] as $row => $row_data){
					foreach($row_data['warrior'] as $card_data){
						$card = BattleFieldController::cardData($card_data['id']);
						$allow_to_regroup = true;
						if($action['regroup_ignoreImmunity'] == 0){
							$allow_to_regroup = BattleFieldController::checkForFullImmune($action['regroup_ignoreImmunity'], $card['actions']);
						}
						if($allow_to_regroup){
							$users_data['user']['cards_to_play'][] = $card_data['id'];
							$step_status['round_status']['cards_to_play'][] = $card;
						}
					}
				}
				//card activates after user action
				if(count($users_data['user']['cards_to_play']) > 0){
					$user_turn_id	= $users_data['user']['id'];
					$step_status['round_status']['current_player'] = $users_data['user']['login'];
					$step_status['round_status']['activate_popup'] = (!empty($step_status['played_magic']))? 'activate_magic_regroup': 'activate_regroup';
					$step_status['actions']['it_is_regroup'] = 'YARRR';
				}
			break;

			case 'sorrow'://ПЕЧАЛЬ
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				$players = ($action['sorrow_actionTeamate'] == 0)? [$users_data['opponent']['player']]: ['p1', 'p2'];
				$row = self::strRowToInt($msg->BFData->row);

				//MAGIC USED
				foreach($players as $player){
					foreach($magic_usage[$player] as $activated_in_round => $magic_id){
						if($magic_id != '0'){
							$magic = BattleFieldController::magicData($magic_id['id']);//Данные о МЭ
							foreach($magic['actions'] as $action_data){
								if($action_data['caption'] == 'inspiration'){
									$magic_usage[$player][$activated_in_round]['allow'] = 0;
									$step_status['actions']['appear'][$player][$row][] = $action['caption'];
								}
							}
						}
					}
				}

				foreach($players as $player){
					if(!empty($battle_field[$player][$row]['special'])){
						$users_data[$player]['discard'][] = $battle_field[$player][$row]['special']['id'];
						$card = BattleFieldController::cardData($battle_field[$player][$row]['special']['id']);
						$step_status['added_cards'][$player]['discard'][] = $card;
						$step_status['dropped_cards'][$player][$row]['special'] = $card['caption'];
						$battle_field[$player][$row]['special'] = '';
						$step_status['actions']['appear'][$player][$row][] = $action['caption'];
					}
				}

				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				foreach($field_buffs as $field => $rows){
					if(!isset($new_field_buffs[$field])){
						$step_status['actions']['disappear'][$field] = $field_buffs[$field];
					}else{
						foreach($rows as $row => $row_data){
							if(isset($new_field_buffs[$field][$row])){
								$step_status['actions']['disappear'][$field][$row] = array_diff($field_buffs[$field][$row], $new_field_buffs[$field][$row]);
							}else{
								$step_status['actions']['disappear'][$field][$row] = $field_buffs[$field][$row];
							}
						}
					}
				}
			break;

			case 'support'://Поддержка
				if(!empty($step_status['played_card']['card'])){
					foreach($action['support_ActionRow'] as $row){
						$step_status['actions']['appear'][$step_status['played_card']['move_to']['player']][$row][] = $action['caption'];
					}
				}
				if(!empty($step_status['played_magic'])){
					foreach($action['support_ActionRow'] as $row){
						$step_status['actions']['appear'][$msg->BFData->field][$row][] = $action['caption'];
					}
				}
			break;

			case 'terrify':
				if(!empty($step_status['played_card']['card'])){
					if($action['fear_actionTeamate'] == 1){
						$players = ['p1','p2'];
					}else{
						$players = ($step_status['played_card']['move_to']['player'] == 'p1')? ['p2']: ['p1'];
					}
					foreach($players as $player){
						foreach($action['fear_ActionRow'] as $row){
							$step_status['actions']['appear'][$player][$row][] = $action['caption'];
						}
					}
				}
				if(!empty($step_status['played_magic'])){
					if($action['fear_actionTeamate'] == 1){
						$players = ['p1','p2'];
					}else{
						$players = [$users_data['opponent']['player']];
					}
					foreach($players as $player){
						foreach($action['fear_ActionRow'] as $row){
							$step_status['actions']['appear'][$player][$row][] = $action['caption'];
						}
					}
				}
			break;

			case 'brotherhood':
				if(!empty($step_status['played_card']['card'])){
					$step_status['actions']['appear'][$step_status['played_card']['move_to']['player']][$step_status['played_card']['move_to']['row']][] = $action['caption'];
				}
			break;

			case 'inspiration':
				if(!empty($step_status['played_card']['card'])){
					$step_status['actions']['appear'][$step_status['played_card']['move_to']['player']][$step_status['played_card']['move_to']['row']][] = $action['caption'];
				}
				if(!empty($step_status['played_magic'])){
					foreach($action['inspiration_ActionRow'] as $row){
						$step_status['actions']['appear'][$msg->BFData->field][$row][] = $action['caption'];
					}
				}
			break;

			case 'spy'://ШПИЙОН
				$deck_card_count = count($users_data['user']['deck']);
				$step_status['played_card']['move_to']['player'] = ($action['spy_fieldChoise'] == 1)? $users_data['opponent']['player']: $users_data['user']['player'];
				$n = ($deck_card_count >= $action['spy_getCardsCount']) ? $action['spy_getCardsCount'] : $deck_card_count;
				for($i=0; $i<$n; $i++){
					$rand_item = mt_rand(0, $deck_card_count-1);
					$random_card = $users_data['user']['deck'][$rand_item];

					$users_data['user']['hand'][] = $random_card;

					$card = BattleFieldController::cardData($random_card);
					$step_status['added_cards'][$users_data['user']['player']]['hand'][] = $card;
					$step_status['dropped_cards'][$users_data['user']['player']]['deck'][] = $card['caption'];

					unset($users_data['user']['deck'][$rand_item]);

					$users_data['user']['deck'] = array_values($users_data['user']['deck']);
					$deck_card_count = count($users_data['user']['deck']);
				}
			break;
		}

		if(isset($step_status['actions']['disappear'])){
			foreach($step_status['actions']['disappear'] as $player => $rows){
				foreach($rows as $row => $data){
					if(empty($step_status['actions']['disappear'][$player][$row])){
						unset($step_status['actions']['disappear'][$player][$row]);
					}
				}
				if(empty($step_status['actions']['disappear'][$player])){
					unset($step_status['actions']['disappear'][$player]);
				}
			}
		}

		return [
			'user_turn_id'	=> $user_turn_id,
			'magic_usage'	=> $magic_usage,
			'battle_field'	=> $battle_field,
			'users_data'	=> $users_data,
			'step_status'	=> $step_status
		];
	}

	protected static function makeHealOrSummon($users_data, $step_status, $input_action){
		$deck = $users_data['user']['card_source'];
		if($input_action['deckChoise'] == 1){
			$users_data['user']['player_source'] = $users_data['opponent']['player'];
			$user = 'opponent';
		}else{
			$user = 'user';
		}
		$step_status['round_status']['activate_popup'] = 'activate_choise';

		$cards_to_play = [];
		switch($input_action['typeOfCard']){
			case '0':
				foreach($users_data[$user][$deck] as $card_data){
					$card = BattleFieldController::getCardNaturalSetting($card_data);
					if(in_array($card_data, $input_action['type_singleCard'])){
						$allow_to_summon = ($user == 'user')
							? BattleFieldController::checkForFullImmune($input_action['ignoreImmunity'], $card['actions'])
							: BattleFieldController::checkForSimpleImmune($input_action['ignoreImmunity'], $card['actions']);

						if($allow_to_summon){
							$cards_to_play[] = $card_data;
						}
					}
				}
			break;
			case '1':
				foreach($users_data[$user][$deck] as $card_data){
					$card = BattleFieldController::getCardNaturalSetting($card_data);
					foreach($card['allowed_rows'] as $row_iter => $card_row){
						if( (in_array($card_row, $input_action['type_actionRow'])) && ($card['fraction'] != 'special') ){
							$allow_to_summon = ($user == 'user')
								? BattleFieldController::checkForFullImmune($input_action['ignoreImmunity'], $card['actions'])
								: BattleFieldController::checkForSimpleImmune($input_action['ignoreImmunity'], $card['actions']);

							if($allow_to_summon){
								$cards_to_play[] = $card_data;
							}
						}
					}
				}
			break;
			case '2':
				foreach($users_data[$user][$deck] as $card_data){
					$card = BattleFieldController::getCardNaturalSetting($card_data);

					if($card['fraction'] != 'special'){
						$allow_to_summon = ($user == 'user')
							? BattleFieldController::checkForFullImmune($input_action['ignoreImmunity'], $card['actions'])
							: BattleFieldController::checkForSimpleImmune($input_action['ignoreImmunity'], $card['actions']);

						if($allow_to_summon){
							$cards_to_play[] = $card_data;
						}
					}
				}
			break;
			case '3':
				foreach($users_data[$user][$deck] as $card_data){
					$card = BattleFieldController::getCardNaturalSetting($card_data);
					foreach($card['group'] as $group_id){
						$allow_by_group = false;
						if(in_array($group_id, $input_action['type_group'])){
							$allow_by_group = true;
						}
						$allow_to_summon = ($user == 'user')
							? BattleFieldController::checkForFullImmune($input_action['ignoreImmunity'], $card['actions'])
							: BattleFieldController::checkForSimpleImmune($input_action['ignoreImmunity'], $card['actions']);

						if( ($allow_to_summon) && ($allow_by_group) ){
							$cards_to_play[] = $card_data;
						}
					}
				}
			break;
			case '4':
				foreach($users_data[$user][$deck] as $card_data){
					$card = BattleFieldController::getCardNaturalSetting($card_data);
					$allow_to_summon = ($user == 'user')
						? BattleFieldController::checkForFullImmune($input_action['ignoreImmunity'], $card['actions'])
						: BattleFieldController::checkForSimpleImmune($input_action['ignoreImmunity'], $card['actions']);
					if($allow_to_summon){
						$cards_to_play[] = $card_data;
					}
				}
			break;
		}
		$cards_to_play = array_values($cards_to_play);

		if($input_action['cardChoise'] == 1){
			$rand = mt_rand(0, count($cards_to_play)-1);
			$random_card = $cards_to_play[$rand];
			$cards_to_play = [];
			$cards_to_play[] = $random_card;
		}

		foreach($users_data[$user][$deck] as $card_data){
			if(in_array($card_data, $cards_to_play)){
				$users_data['user']['cards_to_play'][] = $card_data;//Карты приходят в попап выбора карт
				$step_status['round_status']['cards_to_play'][] = BattleFieldController::cardData($card_data);
			}
		}

		if(count($users_data['user']['cards_to_play']) > 0){
			$user_turn_id = $users_data['user']['id'];
			$step_status['round_status']['current_player'] = $users_data['user']['login'];
			$step_status['round_status']['card_source'] = [$users_data['user']['player_source'] => $deck];
		}else{
			$users_data['user']['card_source'] = $deck;
			$users_data['user']['player_source'] = $users_data['user']['player'];
			$step_status['round_status']['activate_popup'] = '';
			if($users_data['opponent']['round_passed'] > 0){
				$user_turn_id = $users_data['user']['id'];
				$step_status['round_status']['current_player'] = $users_data['user']['login'];
			}else{
				$user_turn_id = $users_data['opponent']['id'];
				$step_status['round_status']['current_player'] = $users_data['opponent']['login'];
			}
			$step_status['round_status']['card_source'] = [$users_data['user']['player_source'] => 'hand'];
		}

		return [
			'users_data'	=> $users_data,
			'user_turn_id'	=> $user_turn_id,
			'step_status'	=> $step_status
		];
	}

	protected static function calcStrByPlayers($field_status){
		$result = [];
		foreach($field_status as $player => $rows){
			if($player != 'mid'){
				$total = 0;

				foreach($rows as $row_data){
					foreach($row_data['warrior'] as $card){
						$total += $card['strengthModified'];
					}
				}
				$result[$player] = $total;
			}
		}
		return $result;
	}

	protected static function clearBattleField($battle, $battle_field, $users_data, $magic_usage, $gain_cards_count, $step_status){
		$deadless_cards = unserialize($battle->undead_cards);

		//Добавление карт из колоды каждому игроку
		$gain_cards_data = self::userGainCards($users_data['user'], $gain_cards_count['user'], $step_status);
		$users_data['user'] = $gain_cards_data['array'];
		$step_status = $gain_cards_data['step_status'];

		$gain_cards_data = self::userGainCards($users_data['opponent'], $gain_cards_count['opponent'], $step_status);
		$users_data['opponent'] = $gain_cards_data['array'];
		$step_status = $gain_cards_data['step_status'];

		//Очищение поля битвы от карт
		foreach($battle_field as $player => $rows){
			if($player != 'mid'){
				$card_to_stay = [];
				//Просчет рассовой способности монстров
				if($users_data[$player]['current_deck'] == 'monsters'){
					$card_to_stay = self::cardsToStay($battle_field, $player);
				}

				foreach($rows as $row => $cards){
					if(!empty($battle_field[$player][$row]['special'])){
						$users_data[$player]['discard'][] = $battle_field[$player][$row]['special']['id'];

						$card = BattleFieldController::cardData($battle_field[$player][$row]['special']['id']);
						$step_status['dropped_cards'][$player][$row]['special'] = $battle_field[$player][$row]['special']['caption'];
						$step_status['added_cards'][$player]['discard'][] = $card;
						$battle_field[$player][$row]['special'] = [];
					}

					//Заносим карты воинов в отбой
					foreach($cards['warrior'] as $card_iter => $card_data){
						$card = BattleFieldController::cardData($card_data['id']);

						$card_is_deadless = false;
						foreach($card['actions'] as $action_iter => $action){
							if($action['caption'] == 'deadless'){
								$card_is_deadless = true;
							}
						}

						if($card_is_deadless){
							//Если действие "Бессмертный" была использована в прошлом раунде
							if( (isset($deadless_cards[$player][$battle->round_count -1])) && (in_array($card_data['id'], $deadless_cards[$player][$battle->round_count -1])) ){
								$users_data[$player]['discard'][] = $card_data['id'];
								$step_status['dropped_cards'][$player][$row]['warrior'][] = $card['caption'];
								$step_status['added_cards'][$player]['discard'][] = $card;
								unset($battle_field[$player][$row]['warrior'][$card_iter]);
							}else{
								$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $card['strength'];
								$deadless_cards[$player][$battle->round_count][] = $card_data['id'];
							}
						}else{
							$users_data[$player]['discard'][] = $card_data['id'];
							$step_status['added_cards'][$player]['discard'][] = $card;
							$step_status['dropped_cards'][$player][$row]['warrior'][] = $card['caption'];
							unset($battle_field[$player][$row]['warrior'][$card_iter]);
						}
					}
					$battle_field[$player][$row]['warrior'] = array_values($battle_field[$player][$row]['warrior']);
				}

				if(!empty($card_to_stay)){
					foreach($card_to_stay as $key => $value){
						$destination = explode('_',$key);
						$battle_field[$destination[0]][$destination[1]]['warrior'][] = $value;
						foreach($step_status['dropped_cards'][$destination[0]][$destination[1]]['warrior'] as $card_iter => $card_caption){
							if($card_caption == $value['caption']){
								unset($step_status['dropped_cards'][$destination[0]][$destination[1]]['warrior'][$card_iter]);
								break;
							}
						}
						foreach($users_data[$destination[0]]['discard'] as $discard_iter => $discard_card){
							if($discard_card == $value['id']){
								unset($users_data[$destination[0]]['discard'][$discard_iter]);
								$users_data[$destination[0]]['discard'] = array_values($users_data[$destination[0]]['discard']);
								break;
							}
						}
						break;
					}
				}
			}else{
				foreach($battle_field[$player] as $card_iter => $card_data){
					$type = ($card_data['login'] == $users_data['user']['login'])? 'user': 'opponent';
					$player_type = $users_data[$type]['player'];
					$users_data[$type]['discard'][] = $card_data['id'];
					$step_status['added_cards'][$player_type]['discard'][] = BattleFieldController::cardData($card_data['id']);
				}
			}
		}

		$battle_field['mid'] = [];

		$temp = BattleFieldController::battleInfo($battle, $battle_field, $users_data, $magic_usage, $step_status);

		return [
			'battle_field'	=> $temp['battle_field'],
			'cards_strength'=> $temp['cards_strength'],
			'users_data'	=> $users_data,
			'deadless_cards'=> $deadless_cards,
			'step_status'	=> $step_status
		];
	}

	protected static function getAppearActions($battle_field){
		$appear = [];
		foreach($battle_field as $player => $rows){
			if($player != 'mid'){
				foreach($rows as $row => $row_data){
					foreach($row_data['warrior'] as $card_iter => $card_data){
						$card = BattleFieldController::cardData($card_data['id']);
						foreach($card['actions'] as $action_iter => $action){
							switch($action['caption']){
								case 'support'://Поддержка
									foreach($action['support_ActionRow'] as $action_row){
										$appear[$player][$action_row][] = $action['caption'];
									}
								break;

								case 'terrify':
									if($action['fear_actionTeamate'] == 1){
										$players = ['p1','p2'];
									}else{
										$players = ($player == 'p1')? ['p2']: ['p1'];
									}
									foreach($players as $action_player){
										foreach($action['fear_ActionRow'] as $action_row){
											$appear[$action_player][$action_row][] = $action['caption'];
										}
									}
								break;
							}
						}
					}
				}
			}
		}
		return $appear;
	}

	protected static function userGainCards($array, $card_to_gain=1, $step_status){
		if(count($array['deck']) < $card_to_gain) $card_to_gain = count($array['deck']);
		for($i=0; $i<$card_to_gain; $i++) {
			if (!empty($array['deck'])) {
				$rand = mt_rand(0, count($array['deck']) - 1);
				$array['hand'][] = $array['deck'][$rand];
				$card = BattleFieldController::getCardNaturalSetting($array['deck'][$rand]);
				$step_status['dropped_cards'][$array['player']]['deck'][] = $card['caption'];
				unset($array['deck'][$rand]);
				$array['deck'] = array_values($array['deck']);
			}
		}
		return [
			'array' => $array,
			'step_status' => $step_status
		];
	}

	protected static function cardsToStay($battle_field, $player){
		$rows_to_stay_card = [];
		$cards_to_stay = [];
		foreach($battle_field[$player] as $row => $row_data){
			if(!empty($row_data['warrior'])){
				$rows_to_stay_card[] = $row;
			}
		}
		foreach($rows_to_stay_card as $row){
			foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
				$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
				$allow_to_count = true;
				foreach($card['actions'] as $action_iter => $action){
					if($action['caption'] == 'deadless'){
						$allow_to_count = false;
					}
				}
				if($allow_to_count){
					$cards_to_stay[] = [$player.'_'.$row => $card_data];
				}
			}
		}

		$cards_to_stay = (!empty($cards_to_stay))? $cards_to_stay[mt_rand(0, count($cards_to_stay)-1)]: [];

		return $cards_to_stay;
	}

	protected static function getDeckCards($deck){
		$result = [];
		foreach($deck as $card_id){
			$card = BattleFieldController::cardData($card_id);
			$result[] = $card;
		}
		return $result;
	}
	// /Service functions

	protected static function saveGameResults($user_id, $battle, $game_result, $type = 'fair'){
		$user = \DB::table('users')
			->select('id', 'login', 'premium_activated', 'premium_expire_date', 'user_gold', 'user_silver', 'user_rating')
			->where('id', '=', $user_id)
			->first();

		$league = League::where('title', '=', $battle->league)->first();

		$user_rating = unserialize($user->user_rating);

		$games_count = $user_rating[$league['slug']]['games_count'] + 1;

		$gold = $user->user_gold;
		$silver = $user->user_silver;

		$win_count = $user_rating[$league['slug']]['win_count'];

		$expire_date = strtotime(substr($user->premium_expire_date, 0, -9));
		$current_date = strtotime(date('Y-m-d'));

		if((($expire_date - $current_date) > 0) && ($user->premium_activated > 0)){//if user is premium
			$resources = [
				'gold_per_win'	=> $league->prem_gold_per_win,
				'gold_per_loose'=> $league->prem_gold_per_loose,
				'silver_per_win'=> $league->prem_silver_per_win,
				'silver_per_loose'=> $league->prem_silver_per_loose,
			];
		}else{
			$resources = [
				'gold_per_win'	=> $league->gold_per_win,
				'gold_per_loose'=> $league->gold_per_loose,
				'silver_per_win'=> $league->silver_per_win,
				'silver_per_loose'=> $league->silver_per_loose,
			];
		}

		$result = [
			'gold' => 0,
			'silver' => 0,
			'user_rating' => 0,
			'gameResult' => $game_result
		];

		$user_deck = BattleMembers::select('user_deck_race')->where('user_id','=',$user_id)->first();
		$deck = $user_deck->user_deck_race;
		$summary = SummaryLeague::select('id',$deck)
			->where('league','=',$league['slug'])
			->first();

		$statistic = unserialize($summary->$deck);
		$statistic[$type]++;

		switch($game_result){
			case 'win':
				$gold = $user->user_gold + $resources['gold_per_win'];
				$silver = $user->user_silver + $resources['silver_per_win'];
				$rating = $user_rating[$league['slug']]['user_rating'] + $league->rating_per_win;
				$win_count = $user_rating[$league['slug']]['win_count'] + 1;
				$result['gold'] = $resources['gold_per_win'];
				$result['silver'] = $resources['silver_per_win'];
				$result['user_rating'] = $league->rating_per_win;
				$statistic['win']++;
			break;
			case 'loose':
				$gold = $user->user_gold + $resources['gold_per_loose'];
				$silver = $user->user_silver + $resources['silver_per_loose'];
				$rating = $user_rating[$league['slug']]['user_rating'] + $league->rating_per_loose;
				if($gold < 0) $gold = 0;
				if($silver < 0) $silver = $league->min_amount;
				$result['gold'] = $resources['gold_per_loose'];
				$result['silver'] = $resources['silver_per_loose'];
				$result['user_rating'] = abs($league->rating_per_loose);
				$statistic['win']--;
			break;
			case 'draw':
				$rating = $user_rating[$league['slug']]['user_rating'];
			break;
		}
		SummaryLeague::where('id','=',$summary->id)->update([
			$deck => serialize($statistic)
		]);

		$user_rating[$league['slug']] = [
			'user_rating'	=> $rating,
			'win_count'		=> $win_count,
			'games_count'	=> $games_count
		];

		\DB::table('users')->where('id', '=', $user->id)->update([
			'user_gold'		=> $gold,
			'user_silver'	=> $silver,
			'user_rating'	=> serialize($user_rating)
		]);

		return $result;
	}
}