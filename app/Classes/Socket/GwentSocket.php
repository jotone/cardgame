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
	protected $clients;		//Соединения клиентов
	protected $battles;		//Текущие активные игры в соккете
	protected $battle_id;	//ID текущей битвы
	protected $users_data;	//Данные пользователей
	protected $magic_usage;	//Использование Маг Эффектов в битве
	public $step_status;	//Данные текущего хода

	/**
	 * GwentSocket constructor.
	 */
	public function __construct(){
		$this->clients = new \SplObjectStorage;
	}

//Socket actions
	/**
	 * Socket error handler
	 * @param ConnectionInterface $conn данные соккет-соединения
	 * @param \Exception $e
	 */
	public function onError(ConnectionInterface $conn, \Exception $e){
		echo 'An error has occured: '.$e->getMessage()."\n";
		$conn -> close();
	}

	/**
	 * Socket connetion open
	 * @param ConnectionInterface $conn данные соккет-соединения
	 */
	public function onOpen(ConnectionInterface $conn){
		//User conects to session
		$this->clients->attach($conn); //[RU]Добавление клиента  [EN]Client add
		echo 'New connection ('.$conn->resourceId.')'."\n\r";
	}

	/**
	 * Socket connection close
	 * @param ConnectionInterface $conn
	 */
	public function onClose(ConnectionInterface $conn){
		//Looking for current battle
		$battle = Battle::find($this->battle_id);
		//Increase disconnected users counter
		$battle->disconected_count++;
		$battle->save();

		//Battle isn't finished but users had leave
		if( ($battle->fight_status < 3) && ($battle->disconected_count > 1) ){
			//Set marker of battle status to "Fight ends"
			$battle->fight_status = 3;
			$battle->save();
			//Calculate cards strength and their abilities
			$battle_info = BattleFieldController::battleInfo($battle, unserialize($battle->battle_field), $this->users_data, $this->magic_usage, $this->step_status);

			//Calculate round result
			$total_score = self::calcStrByPlayers($battle_info['field_status']);
			$user_score = $total_score[$this->users_data['user']['player']];
			$opponent_score = $total_score[$this->users_data['opponent']['player']];
			//The definition of the winner and calculation of the battle results
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
			//Set the busy marker to FALSE
			\DB::table('users')->where('id','=',$this->users_data['user']['id'])->update(['user_busy' => 0]);
			\DB::table('users')->where('id','=',$this->users_data['opponent']['id'])->update(['user_busy' => 0]);
		}

		$this->clients->detach($conn);//delete on finish
		echo 'Connection '.$conn->resourceId.' has disconnected'."\n";//delete on finish
	}

	/**
	 * Send messages to all users
	 * @param ConnectionInterface $from
	 * @param $result
	 * @param $battles
	 */
	protected static function sendMessageToOthers($from, $result, $battles){
		foreach($battles as $client){
			if($client->resourceId != $from->resourceId){
				$client->send(json_encode($result));
			}
		}
	}

	/**
	 * Send message to self
	 * @param ConnectionInterface $from
	 * @param $message
	 */
	protected static function sendMessageToSelf($from, $message){
		$from->send(json_encode($message));
	}
//Socket actions end

	/**
	 * Users actions handler
	 * @param ConnectionInterface $from
	 * @param json string $msg [action, ident[battleId, UserId, Hash]]
	 */
	public function onMessage(ConnectionInterface $from, $msg){
		$msg = json_decode($msg);
		var_dump(date('Y-m-d H:i:s'));
		var_dump($msg);
		if(!isset($this->battles[$msg->ident->battleId])){
			$this->battles[$msg->ident->battleId] = new \SplObjectStorage;
		}

		//Timing settings
		$timing_settings = SiteGameController::getTimingSettings();

		//If user isn't connected to socket
		if(!$this->battles[$msg->ident->battleId]->contains($from)){
			$this->battles[$msg->ident->battleId]->attach($from);
		}
		//Current Battle object
		$SplBattleObj = $this->battles;

		//Looking for current battle in the DB
		$battle = Battle::find($msg->ident->battleId);
		$this->battle_id = $msg->ident->battleId;

		//Data on those participating in the battle
		$battle_members = BattleMembers::where('battle_id', '=', $msg->ident->battleId)->get();

		//Set the busy marker to TRUE
		\DB::table('users')->where('id', '=', $msg->ident->userId)->update([
			'updated_at'	=> date('Y-m-d H:i:s'),
			'user_online'	=> '1'
		]);

		//Creating user data arrays
		foreach($battle_members as $key => $value){
			//Search the current player in the DB
			$user = User::find($value->user_id);
			//Arrangement of players' tags
			$user_identificator = ($value->user_id == $battle->creator_id)? 'p1' : 'p2';
			//Creation of card shirts
			$card_background = \DB::table('tbl_fraction')->select('card_img')->where('slug','=',$user->user_current_deck)->first();
			//Users data arrays fill
			if($value->user_id == $msg->ident->userId){
				$this->users_data['user'] = [
					'id'			=> $value->user_id,
					'login'			=> $user->login,
					'player'		=> $user_identificator,					//Players' tag
					'magic_effects'	=> unserialize($value->magic_effects),	//List of active magic effects
					'energy'		=> $user->user_energy,					//User's energy quantity
					'deck'			=> unserialize($value->user_deck),		//Uses's deck
					'hand'			=> unserialize($value->user_hand),		//User's cards "in hand"
					'discard'		=> unserialize($value->user_discard),	//User's discard
					'current_deck'	=> $user->user_current_deck,			//fraction caption
					'card_source'	=> $value->card_source,					//Source of card playing deck(hand/deck/discard)
					'player_source'	=> $value->player_source,				//Source of player deck(user/opponent)
					'cards_to_play'	=> unserialize($value->card_to_play),	//Cards array that nedd to play on current turn
					'round_passed'	=> $value->round_passed,				//Pass marker
					'addition_data'	=> $value->addition_data,				//Popup activation marker
					'battle_member_id'=> $value->id,						//ID of current battle
					'turn_expire'	=> $value->turn_expire,					//Turn expire timestamp
					'pseudonim'		=> 'user',								//Player pseudonym
					'user_magic'	=> unserialize($user->user_magic),		//Array of used magic effects
					'card_images'	=> [									//Сard shirts
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
					'pseudonim'		=> 'opponent',							//Opponent pseudonym
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
			'added_cards'	=> [],			//Array of added cards
			'dropped_cards'	=> [],			//Array od dropped cards
			'played_card'	=> [
				'card'			=> [],		//Card data
				'move_to'		=> [		//Card position
						'player'	=> '',		//(user/opponent)
						'row'		=> '',		//Field row
						'user'		=> ''		//User login
					],
				'self_drop'		=> 0,		//Card must be deleted to this turn
				'strength'		=> ''		//Card strength value
			],
			'played_magic'	=> [],			//Magic that used on this turn
			'actions'		=> [			//List of actions
				'appear'		=> [],		//Actions that need to appear
				'disappear'		=> [],		//Action that need to disappear
				'cards'			=> []		//List of modified cards
			],
			'counts'		=> [],			//User decks counters
			'round_status'	=> [
				'round'			=> 0,		//Number of current round
				'current_player'=> '',		//Next turn user's login
				'card_source'	=> [],		//Source of card playing deck[played=>[deck]]
				'activate_popup'=> '',		//Activate popup on current turn
				'cards_to_play'	=> [],		//List of cards that need to play
				'status'		=> [],		//pass and round ends statuses
			],
			'magic_usage'	=> [],			//Current turn magic usage
			'users_energy'	=> [],			//Users energy counters
			'timing'		=> '',			//End of next turn timestamp
			'images'		=> []			//Cards shirt images
		];
		$this->magic_usage = unserialize($battle->magic_usage);//magic usage array

		switch($msg->action){
			//User joined to battle
			case 'userJoinedToRoom':
				//If there is other disconnected players in socket connection
				if($battle->disconected_count > 0){
					$battle->disconected_count--;
					$battle->save();
				}
				//Get user login of current turn
				$user_turn = ($battle->user_id_turn != 0)? $this->users_data[$battle->user_id_turn]['login']: '';

				//If the battle doesn't start
				if($battle->fight_status <= 1){
					//if there are two players connected
					if(2 == count($battle_members)){
						//If battle stage is card choose/change
						if(0 == $battle->fight_status){
							//Set card change timestamp
							$battle->turn_expire = $timing_settings['card_change'] + time();
							//Set the battle is started
							$battle->fight_status = 1;
							$battle->save();
						}

						//Set which player should play
						$this->step_status['round_status']['current_player'] = $user_turn;
						$this->step_status['timing'] = $timing_settings['card_change'];

						$result = $this->step_status;
						$result['message'] = 'usersAreJoined';
						$result['joined_user'] = $this->users_data['user']['login'];
						$result['battleInfo'] = $msg->ident->battleId;

						self::sendMessageToSelf($from, $result); //Send data to self
						self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
					}
				}

				//if battle continues(user reloaded page)
				if(2 == $battle->fight_status){
					$battle->save();
					//There was activated card action to choose cards
					$player_source = (empty($this->users_data['user']['player_source']))
						? $this->users_data['user']['player']
						: $this->users_data['user']['player_source'];

					//Get decks counters
					$this->step_status['counts'] = self::getDecksCounts($this->users_data);
					//Get current round number
					$this->step_status['round_status']['round'] = $battle->round_count;
					//Get current user turn
					$this->step_status['round_status']['current_player'] = $user_turn;
					//Get current user card source
					$this->step_status['round_status']['card_source'] = [$player_source => $this->users_data['user']['card_source']];
					//Get current popup activation
					$this->step_status['round_status']['activate_popup'] = $this->users_data['user']['addition_data'];
					//Get cards that need to play
					$cards_to_play = [];
					if(!empty($this->users_data['user']['cards_to_play'])){
						foreach($this->users_data['user']['cards_to_play'] as $card_data){
							$cards_to_play[] = BattleFieldController::cardData($card_data);
						}
					}
					$this->step_status['round_status']['cards_to_play'] = $cards_to_play;
					//Get users energy counters
					$this->step_status['users_energy'] = [
						$this->users_data['user']['login']	=> $this->users_data['user']['energy'],
						$this->users_data['opponent']['login']=> $this->users_data['opponent']['energy']
					];
					//Get turn timestamps
					$this->step_status['timing'] = $timing_settings['card_change'];
					//Get cards shirts
					$this->step_status['images'] = [
						$this->users_data['user']['login'] => $this->users_data['user']['card_images'],
						$this->users_data['opponent']['login'] => $this->users_data['opponent']['card_images'],
					];
					//Send message to self
					$result = $this->step_status;
					$result['message'] = 'allUsersAreReady';
					$result['battleInfo'] = $msg->ident->battleId;

					self::sendMessageToSelf($from, $result);
				}
			break;

			//All users are ready
			case 'userReady':
				//if battle stage is card choose/change
				if(1 == $battle->fight_status){
					//Ready users quantity
					$ready_players_count = 0;
					foreach($battle_members as $key => $value){
						if(0 != $value->user_ready){
							$ready_players_count++;
						}
					}

					//If there are two ready users or user was connected as second player
					if(2 == $ready_players_count){
						$cursed_players = [];
						$player = 'p1';//Default $player value
						//if there are player with fraction "Cursed"
						if($this->users_data['p1']['current_deck'] == 'cursed'){
							$cursed_players[] = $this->users_data['user']['player'];
							$player = 'p1';
						}
						if($this->users_data['p2']['current_deck'] == 'cursed'){
							$cursed_players[] = $this->users_data['opponent']['player'];
							$player = 'p2';
						}

						//if user doesn't makes turn
						if($battle->user_id_turn < 1){
							//If there is ONE player with fraction "Cursed"
							if((1 == count($cursed_players)) && ($msg->ident->userId == $this->users_data[$player]['id'])){
								//If there was turn step
								if(isset($msg->turn)){
									//Switch to "Cursed" player
									$players_turn = (($this->users_data['user']['login'] == $msg->turn) || ($msg->turn == ''))
										? $this->users_data['user']['id']
										: $this->users_data['opponent']['id'];
								}else{
									//Set random player to current turn
									$rand = mt_rand(0, 1);
									$players_turn = ($rand == 0)? $this->users_data['p1']['id']: $this->users_data['p2']['id'];
								}
							}else{
								//Set random player to current turn
								$rand = mt_rand(0, 1);
								$players_turn = ($rand == 0)? $this->users_data['p1']['id']: $this->users_data['p2']['id'];
							}
							//Save battle turn settings
							$battle->user_id_turn = $players_turn;
							$battle->first_turn_user_id = $players_turn;
							$battle->save();
						}

						//Get timing settings
						$user_timing = \DB::table('tbl_battle_members')->select('turn_expire')->where('user_id','=',$battle->user_id_turn)->first();
						$battle->turn_expire = $user_timing->turn_expire + time();

						//Get current player card source
						$player_source = (empty($this->users_data['opponent']['player_source']))
							? $this->users_data['opponent']['player']
							: $this->users_data['opponent']['player_source'];

						//This turn step status fill (look to LINE 317)
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

						//if battle stage is card case/change
						if($battle->fight_status <= 1){
							self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
						}
						//Set the battle starts
						$battle->fight_status = 2;
						$battle->save();

						$player_source = (empty($this->users_data['user']['player_source']))
							? $this->users_data['user']['player']
							: $this->users_data['user']['player_source'];

						$result['round_status']['card_source'] = [$player_source => $this->users_data['user']['card_source']];
						$result['round_status']['cards_to_play'] = $this->users_data['user']['cards_to_play'];

						self::sendMessageToSelf($from, $result);
					}else{//If there is ONE ready player (look for LINE 370)
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

			//User made card or magic effect action
			case 'userMadeAction':
				if($battle->fight_status == 2){
					$battle_field = unserialize($battle->battle_field);//Battle field data
					//Set cards source by defaults
					$this->users_data['user']['cards_to_play'] = [];//Current user cards need to play
					$this->users_data['user']['player_source'] = $this->users_data['user']['player'];//Source of deck player
					$this->users_data['user']['card_source'] = 'hand';//Source of card deck

					$this->step_status['round_status']['cards_to_play'] = [];
					$this->step_status['round_status']['card_source'] = [$this->users_data['user']['player'] => 'hand'];
					$this->step_status['round_status']['activate_popup'] = '';//No need the popup activation
					$this->step_status['round_status']['round'] = $battle->round_count;//Current round number

					$self_drop = 0;//Card self drop marker

					//Next user turn step
					//If opponent had passed
					if($this->users_data['opponent']['round_passed'] == 1){
						$this->step_status['round_status']['current_player'] = $this->users_data['user']['login'];
						$user_turn_id = $this->users_data['user']['id'];
					}else{
						$this->step_status['round_status']['current_player'] = $this->users_data['opponent']['login'];
						$user_turn_id = $this->users_data['opponent']['id'];
					}

					//If there was used magic effect
					if($msg->magic != ''){
						$disable_magic = false;//Enable the magic effect usage status by defaults
						$magic_id = Crypt::decrypt($msg->magic);
						$magic = BattleFieldController::magicData($magic_id);//Get current magic effect data
						/**
						 * Condition:
						 * magic wasn't used && user has sufficient amount of energy points to use magic effect
						 */
						if(($this->users_data['user']['user_magic'][$magic_id]['used_times'] > 0) && ($this->users_data['user']['energy'] >= $magic['energy_cost'])){
							//magic effect counter decrement
							$this->users_data['user']['user_magic'][$magic_id]['used_times'] = $this->users_data['user']['user_magic'][$magic_id]['used_times'] - 1;
							//decrease energy points
							$this->users_data['user']['energy'] = $this->users_data['user']['energy'] - $magic['energy_cost'];

							//If magic was not used by this player in this round
							if(!isset($this->magic_usage[$this->users_data['user']['player']][$battle->round_count])){
								//Set the magic usage hor current player and round
								$this->magic_usage[$this->users_data['user']['player']][$battle->round_count] = [
									'id'	=> Crypt::decrypt($msg->magic),
									'allow'	=> '1'
								];
								//Create actions array
								$current_actions = $magic['actions'];
								//Step status magic
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

						//Save magic usage and user energy data
						\DB::table('users')->where('id', '=', $this->users_data['user']['id'])->update([
							'user_energy'	=> $this->users_data['user']['energy'],
							'user_magic'	=> serialize($this->users_data['user']['user_magic'])
						]);
					}

					//If there was card played
					if($msg->card != ''){
						//Get decrypted card id
						$current_card_id = Crypt::decrypt($msg->card);
						//Get card data
						$current_card = BattleFieldController::cardData($current_card_id);
						//The row in which the card was placed
						$current_card_row = (isset($msg->BFData->row))? self::strRowToInt($msg->BFData->row): 3;
						//The field in which the card was placed
						$current_card_field = (isset($msg->BFData->field))? $msg->BFData->field: 'mid';

						//If card has "special" status
						if($current_card['fraction'] == 'special'){
							//If card placed if middle row
							if($current_card_row == 3){
								$battle_field['mid'][] = [
									'id'		=> $current_card_id,
									'caption'	=> $current_card['caption'],
									'login'		=> $this->users_data['user']['login']
								];
							}else{
								//Looking for card "self-drop" option
								foreach($current_card['actions'] as $i => $action){
									switch($action['caption']){
										case 'cure':
										case 'heal':
										case 'regroup':
										case 'sorrow':
										case 'call':
										case 'killer':
											//Add card to user discard
											$this->users_data['user']['discard'][] = $current_card_id;
											//Set step status -> card was added to user's discard
											$this->step_status['added_cards'][$this->users_data['user']['player']]['discard'][] = $current_card;
											//Set self-drop marker to TRUE
											$self_drop = 1;
										break;
										//If there is no "self-drop" option
										default:
											//If there is "special" card in this row
											if(!empty($battle_field[$current_card_field][$current_card_row]['special'])){
												//Add card to user discard
												$this->users_data[$current_card_field]['discard'][] = $battle_field[$current_card_field][$current_card_row]['special']['id'];
												//Set step status -> card was added to user's discard
												$this->step_status['added_cards'][$this->users_data['user']['player']]['discard'][] = BattleFieldController::cardData($battle_field[$current_card_field][$current_card_row]['special']['id']);
											}
											//Set this card to battle field
											$battle_field[$current_card_field][$current_card_row]['special'] = [
												'id'		=> $current_card_id,
												'caption'	=> $current_card['caption'],
												'login'		=> $this->users_data['user']['login']
											];
									}
								}
							}
						}else{
							//Set card to battle field
							$battle_field[$current_card_field][$current_card_row]['warrior'][] = [
								'id'		=> $current_card_id,
								'caption'	=> $current_card['caption'],
								'strength'	=> $current_card['strength'],
								'login'		=> $this->users_data['user']['login']
							];
						}

						//Fill step status played cacd array
						$this->step_status['played_card'] = [
							'card'		=> $current_card,		//Card data
							'move_to'	=> [
								'player'	=> $current_card_field,		//Field that card moves in
								'row'		=> $current_card_row,		//Row that card moves in
								'user'		=> $this->users_data['user']['login']	//Login of card owner
							],
							'self_drop'	=> $self_drop,			//Self-drop marker
							'strength'	=> $current_card['strength']	//Card strength value
						];

						//If there was used magic effect "marionette"(@"marionetka")
						if(isset($this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['id'])){
							//Get magic effect data
							$magic_effect_data = BattleFieldController::getMagicDescription($this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['id']);
							if(
								($magic_effect_data['slug'] == 'marionetka') && //If it is ME "marionette"
								($this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['allow'] == 1)//If ME is allowed to use
							){
								$this->magic_usage[$this->users_data['user']['player']][$battle->round_count]['allow'] = '0';
								$call_used = true;//Marionette effect usage marker set to TRUE
							}else{
								$call_used = false;//Marionette effect usage marker set to FALSE
							}
						}else{
							$call_used = false;
						}
						//If there was used magic effect "marionette"(@"marionetka") THEN switch to opponent player marker
						$user_type = ($call_used)? 'opponent': 'user';

						//Get card source player
						$source = (isset($msg->source->p1))? $msg->source->p1: $msg->source->p2;
						//Drop card from source deck
						$this->users_data[$user_type][$source] = self::dropCardFromDeck($this->users_data[$user_type][$source], $current_card['id']);
						//Add card to step status dropped cards array
						if( ( ($source == 'deck') || ($source == 'discard') ) && ($call_used === false)){
							$this->step_status['dropped_cards'][$this->users_data[$msg->ident->userId]['player']][$source][] = $this->step_status['played_card']['card']['caption'];
						}
						//Get card actions
						$current_actions = $current_card['actions'];
					}

					//Actions apply
					$add_time = true;//add additional time to user marker
					$view_cards_strength = false;
					//Actions process
					foreach($current_actions as $action_iter => $action){
						//Get action processing result
						/**
						 * [
						 * $action - current action data
						 * $battle_field - battle field data
						 * $this->users_data - users data array
						 * $this->step_status
						 * $user_turn_id - ID of current turn step user
						 * $msg - socket message obj
						 * $this->magic_usage
						 * ]
						 */
						$action_result = self::actionProcessing($action, $battle_field, $this->users_data, $this->step_status, $user_turn_id, $msg, $this->magic_usage);
						$this->step_status	= $action_result['step_status'];	//changed $this->step_status
						$this->users_data	= $action_result['users_data'];		//changed $this->users_data
						$battle_field		= $action_result['battle_field'];	//changed battle field data
						$user_turn_id		= $action_result['user_turn_id'];	//changed ID of current turn step user

						$this->magic_usage	= $action_result['magic_usage'];	//changed magic usage array

						//List of actions which are not influenced by time change and card view strength
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

					//Decks sorting
					$this->users_data = self::sortDecksByStrength($this->users_data);

					//Recalculates strength values of battle field
					$battle_info = BattleFieldController::battleInfo($battle, $battle_field, $this->users_data, $this->magic_usage, $this->step_status);

					$this->step_status = $battle_info['step_status'];
					$battle_field = $battle_info['battle_field'];
					//Count of passed players
					$round_passed_summ = $this->users_data['user']['round_passed'] + $this->users_data['opponent']['round_passed'];

					//Card should view it's strength
					if($view_cards_strength){
						$this->step_status['actions']['cards_strength'] = $battle_info['cards_strength'];
					}

					//if there are no passed users
					if($round_passed_summ < 1){
						//If there was action without popup-activation ability
						if($add_time === true){
							$turn_expire = $timing_settings['step_time'];
							$showTimerOfUser = 'opponent';
						}else{
							$turn_expire = $msg->timing;
							$showTimerOfUser = 'user';
						}
					//If there is ONLY ONE passed used
					}else{
						$turn_expire = $timing_settings['step_time'];
						$showTimerOfUser = $this->users_data[$msg->ident->userId]['pseudonim'];
					}

					//Set timing timestamp
					$this->step_status['timing'] = $turn_expire;

					//Save battle data
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
					//Save data of each user
					\DB::table('tbl_battle_members')->where('id', '=', $this->users_data['opponent']['battle_member_id'])->update([
						'user_deck'	=> serialize($this->users_data['opponent']['deck']),
						'user_hand'	=> serialize($this->users_data['opponent']['hand']),
						'user_discard'=> serialize($this->users_data['opponent']['discard'])
					]);

					$this->step_status['magic_usage'] = $this->magic_usage;
					//Save battle field data
					$battle->battle_field	= serialize($battle_field);
					$battle->magic_usage	= serialize($this->magic_usage);
					$battle->user_id_turn	= $user_turn_id;
					$battle->turn_expire	= $this->users_data[$showTimerOfUser]['turn_expire'] + time();
					$battle->save();
					//Send current turn step data
					self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
				}
			break;

			//User Pass
			case 'userPassed':
				//get battlefield data
				$battle_field = unserialize($battle->battle_field);
				//Get enemy player identificator
				$enemy = ($msg->user == 'p1')? 'p2': 'p1';

				//Update user data
				\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$msg->user]['battle_member_id'])->update([
					'round_passed' => 1,
					'turn_expire' => $msg->timing
				]);

				//Get passed players count
				$users_passed_count = $this->users_data[$enemy]['round_passed'] + 1;

				//If there is ONLY ONE passed player
				if($users_passed_count == 1){
					//Next turn step for opponent user
					$this->step_status['round_status']['current_player'] = $this->users_data[$enemy]['login'];
					//Round passed message
					$this->step_status['round_status']['status'] = ['passed_user' => $this->users_data[$msg->user]['login']];
					//Card source set to "hand"
					$this->step_status['round_status']['card_source'] = [$this->users_data[$enemy]['player'] => 'hand'];
					//No cards need to play
					$this->step_status['round_status']['cards_to_play'] = [];
					//Round number
					$this->step_status['round_status']['round'] = $battle->round_count;
					$this->step_status['timing'] = $this->users_data[$enemy]['turn_expire'];

					//ID of next turn step user
					$battle->user_id_turn = $this->users_data[$enemy]['id'];
					//Passed users count -> save
					$battle->pass_count++;
					$battle->turn_expire = $this->users_data[$enemy]['turn_expire'] + time();
					$battle->save();

					self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
				}

				//If there are two passed users
				if($users_passed_count == 2){
					//Recalculates strength values of battle field
					$battle_info = BattleFieldController::battleInfo($battle, $battle_field, $this->users_data, $this->magic_usage, $this->step_status);

					$this->step_status = $battle_info['step_status'];
					$field_status = $battle_info['field_status'];

					//Calculate users field points
					$total_score = self::calcStrByPlayers($field_status);
					$user_score = $total_score[$this->users_data['user']['player']];
					$opponent_score = $total_score[$this->users_data['opponent']['player']];

					//Score by rounds
					$round_status = unserialize($battle->round_status);

					$gain_cards_count = ['user' => 1, 'opponent' => 1];//Adiitional cards quantity
					//Choose the winner
					switch(true){
						//User win
						case $user_score > $opponent_score:
							//Add win point
							$round_status[$this->users_data['user']['player']][] = 1;
							//Round result message
							$round_result = 'Выграл '.$this->users_data['user']['login'];
							//If opponent's fraction is "Knight" he gets two cards for loose
							if($this->users_data['opponent']['current_deck'] == 'knight') $gain_cards_count['opponent'] = 2;
						break;
						//Opponent win
						case $user_score < $opponent_score:
							$round_status[$this->users_data['opponent']['player']][] = 1;
							$round_result = 'Выграл '.$this->users_data['opponent']['login'];
							if($this->users_data['user']['current_deck'] == 'knight') $gain_cards_count['user'] = 2;
						break;
						//Draw
						case $user_score == $opponent_score:
							//If one of players belongs to fraction "Undead"
							if(
								( ($this->users_data['user']['current_deck'] == 'undead') || ($this->users_data['opponent']['current_deck'] == 'undead') ) &&
								($this->users_data['user']['current_deck'] != $this->users_data['opponent']['current_deck'])
							){
								//If user belongs to "Undead"
								if($this->users_data['user']['current_deck'] == 'undead'){
									$round_status[$this->users_data['user']['player']][] = 1;
									$round_result = 'Выграл '.$this->users_data['user']['login'];
									if($this->users_data['opponent']['current_deck'] == 'knight') $gain_cards_count['opponent'] = 2;
								//If opponent belongs to "Undead"
								}else{
									$round_status[$this->users_data['opponent']['player']][] = 1;
									$round_result = 'Выграл '.$this->users_data['opponent']['login'];
									if($this->users_data['user']['current_deck'] == 'knight') $gain_cards_count['user'] = 2;
								}
							//If it is reallly the draw
							}else{
								$round_status[$this->users_data['user']['player']][] = 1;
								$round_status[$this->users_data['opponent']['player']][] = 1;
								$round_result = 'Ничья';
							}
						break;
					}

					//Round win points
					$wins_count = [
						$this->users_data['p1']['login'] => $round_status['p1'],
						$this->users_data['p2']['login'] => $round_status['p2']
					];

					$battle->round_count	= $battle->round_count +1;//Increase round counter
					$battle->round_status	= serialize($round_status);//Save round status
					$battle->magic_usage	= serialize($this->magic_usage);

					//Drop cards from battle field
					$clear_result	= self::clearBattleField($battle, $battle_field, $this->users_data, $this->magic_usage, $gain_cards_count, $this->step_status);
					$battle_field	= $clear_result['battle_field'];

					$this->users_data	= $clear_result['users_data'];
					$this->step_status	= $clear_result['step_status'];
					$this->step_status['actions']['cards_strength'] = $clear_result['cards_strength'];//Cards strength by rows

					$battle->battle_field	= serialize($battle_field);

					$battle->undead_cards	= serialize($clear_result['deadless_cards']);//cards that left to next round
					$battle->pass_count		= 0;
					$battle->save();

					//Send results to users
					//if each of players has less thar two wins
					if((count($round_status['p1']) < 2) && (count($round_status['p2']) < 2)){
						$this->users_data = self::sortDecksByStrength($this->users_data);//Decks sorting
						$this->step_status['counts'] = self::getDecksCounts($this->users_data);//Get decks counters
						$this->step_status['round_status']['status'] = [
							'result'=> $round_result,
							'score'	=> $wins_count
						];
						//If "deadless" cards has some actions
						$this->step_status['actions']['appear'] = self::getAppearActions($battle_field);
						//Remove cards for hand deck
						foreach($this->step_status['added_cards'] as $player => $decks){
							if(isset($decks['hand'])){
								unset($this->step_status['added_cards'][$player]['hand']);
							}
						}

						$this->step_status['users_energy'] = [
							$this->users_data['user']['login']		=> $this->users_data['user']['energy'],
							$this->users_data['opponent']['login']	=> $this->users_data['opponent']['energy']
						];

						$cursed_players = [];
						//timing and cursed player
						foreach($this->users_data as $type => $user_data){
							if(($type == 'user') || ($type == 'opponent')){
								$timing = $timing_settings['step_time'];
								//Looking for players with fraction "Cursed"
								if($this->users_data[$type]['current_deck'] == 'cursed'){
									$cursed_players[] = $this->users_data[$type]['player'];
								}
								\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$type]['battle_member_id'])->update(['turn_expire' => $timing]);
							}
						}

						//If there is ONLY ONE player with fraction "Cursed"
						if(count($cursed_players) == 1){
							//Activate popup to switch fist-step player
							$this->step_status['round_status']['activate_popup'] = 'activate_turn_choise';
							//Switch to "Cursed"-fraction player
							$user_turn_id = $this->users_data[$cursed_players[0]]['id'];

							\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$cursed_players[0]]['battle_member_id'])->update([
								'addition_data' => 'activate_turn_choise'
							]);
						}else{
							//Switch to player that had no fist-step in last round
							$type = ($this->users_data['user']['id'] == $battle->first_turn_user_id)? 'opponent': 'user';
							$user_turn_id = $this->users_data[$type]['id'];
						}

						//Cards shirts
						$this->step_status['images'] = [
							$this->users_data['user']['login'] => $this->users_data['user']['card_images'],
							$this->users_data['opponent']['login'] => $this->users_data['opponent']['card_images'],
						];

						$this->step_status['round_status']['current_player'] = $this->users_data[$user_turn_id]['login'];
						$this->step_status['round_status']['card_source'] = [$this->users_data[$user_turn_id]['player'] => 'hand'];
						$this->step_status['magic_usage'] = $this->magic_usage;

						//Save users data
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
						//SAve timings and fist-step player
						$battle->first_turn_user_id = $user_turn_id;
						$battle->user_id_turn = $user_turn_id;
						$battle->turn_expire = $this->step_status['timing'] + time();
						$battle->save();

						//Send "Round-end" messages
						$result = $this->step_status;
						$result['message'] = 'roundEnds';
						$result['battleInfo'] = $msg->ident->battleId;
						$result['user_hand'] = self::getDeckCards($this->users_data['user']['hand']);
						$result['deck_slug'] = $this->users_data['user']['current_deck'];

						self::sendMessageToSelf($from, $result);
						$result['user_hand'] = self::getDeckCards($this->users_data['opponent']['hand']);
						$result['deck_slug'] = $this->users_data['opponent']['current_deck'];
						self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
					//If one or both of players has 2 wins
					}else{
						$battle->fight_status = 3;//Set fight status to "battle-finished"(3)
						$battle->save();

						//If player1 has more points
						if(count($round_status['p1']) > count($round_status['p2'])){
							$game_result	= 'Игру выграл '.$this->users_data['p1']['login'];	//Message to users
							$winner			= $this->users_data['p1']['id'];					//Winner player ID
							$to_self		= self::saveGameResults($this->users_data['p1']['id'], $battle, 'win');//save results for player1
							$to_enemy		= self::saveGameResults($this->users_data['p2']['id'], $battle, 'loose');//save results for player2
						}

						//If player2 has more points
						if(count($round_status['p1']) < count($round_status['p2'])){
							$game_result	= 'Игру выграл '.$this->users_data['p2']['login'];
							$winner			= $this->users_data['p2']['id'];
							$to_self		= self::saveGameResults($this->users_data['p2']['id'], $battle, 'win');
							$to_enemy		= self::saveGameResults($this->users_data['p1']['id'], $battle, 'loose');
						}

						//Draw
						if(count($round_status['p1']) == count($round_status['p2'])){
							//If one of players belongs to fraction "Undead"
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
								//If it is really a draw
								$game_result	= 'Игра сыграна в ничью';
								$winner			= '';
								$to_self		= self::saveGameResults($this->users_data['user']['id'], $battle, 'draw');
								$to_enemy		= self::saveGameResults($this->users_data['opponent']['id'], $battle, 'draw');
							}
						}

						//Set the busy marker to FALSE
						\DB::table('users')->where('id','=',$this->users_data['user']['id'])->update(['user_busy' => 0]);
						\DB::table('users')->where('id','=',$this->users_data['opponent']['id'])->update(['user_busy' => 0]);

						$result = ['message' => 'gameEnds', 'gameResult' => $game_result, 'battleInfo' => $msg->ident->battleId];

						//Send final messages to users
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

			//Change/choose cards stage. Both of players change cards
			case 'changeCardInHand':
				$card_id = Crypt::decrypt($msg->card);//Card that is chosen to drop
				//Counter of available cards changing ability(highlanders can change 4 cards; others - only 2)
				$users_battle_data = \DB::table('tbl_battle_members')->select('available_to_change')->find($this->users_data['user']['battle_member_id']);

				//If change-card-counter if greater then 0
				if($users_battle_data->available_to_change > 0){
					//get random card from deck
					$rand = mt_rand(0, count($this->users_data['user']['deck']) - 1);
					//New card that should be added to hand
					$card_to_add = $this->users_data['user']['deck'][$rand];
					//Dropped Cards array is using for animation
					$this->step_status['dropped_cards'][$this->users_data['user']['player']]['deck'][] = BattleFieldController::getCardNaturalSetting($card_id);

					//Drop new card from deck
					unset($this->users_data['user']['deck'][$rand]);
					$this->users_data['user']['deck'] = array_values($this->users_data['user']['deck']);

					//search chosen card in hand
					foreach($this->users_data['user']['hand'] as $hand_iter => $hand_card_data){
						//Chosen card found
						if($hand_card_data == $card_id){
							//New card data
							$this->step_status['added_cards'] = BattleFieldController::cardData($card_to_add);

							//Add chosen card to deck
							$this->users_data['user']['deck'][] = $this->users_data['user']['hand'][$hand_iter];
							//Drop chosen card from hand
							unset($this->users_data['user']['hand'][$hand_iter]);
							//Add to hand new card
							$this->users_data['user']['hand'][] = $card_to_add;
							break;
						}
					}

					$this->users_data['user']['hand'] = array_values($this->users_data['user']['hand']);
					//Decrease counter of available cards change
					$users_battle_data->available_to_change--;

					//Save all of decks
					\DB::table('tbl_battle_members')->where('id','=',$this->users_data['user']['battle_member_id'])
						->update([
							'user_deck'			=> serialize($this->users_data['user']['deck']),
							'user_hand'			=> serialize($this->users_data['user']['hand']),
							'available_to_change'=> $users_battle_data->available_to_change
						]);

					//Decks sorting
					$this->users_data = self::sortDecksByStrength($this->users_data);

					$result = $this->step_status;

					$result['message'] = 'changeCardInHand';
					$result['can_change_cards'] = $users_battle_data->available_to_change;
					//Send change-card-result to self
					self::sendMessageToSelf($from, $result);
				}
			break;

			//Get card/ME action row
			case 'getActiveRow':
				$id = Crypt::decrypt($msg->card);//Card/ME ID
				//If it is card
				if($msg->type == 'card'){
					//Get card data
					$card = \DB::table('tbl_cards')->select('card_type','card_race','allowed_rows','card_actions')->find($id);
					$actions_list = [];
					$actions = unserialize($card->card_actions);
					//Process actions
					foreach($actions as $i => $action){
						$action = get_object_vars($action);
						//Get action data
						$action_data = \DB::table('tbl_actions')->select('type')->find($action['action']);
						$actions_list[$i] = $action;
						$actions_list[$i]['caption'] = $action_data->type;
					}
					//Action result array
					$result = [
						'message'	=> 'cardData',
						'fraction'	=> ($card->card_type == 'race')? $card->card_race: $card->card_type,
						'rows'		=> unserialize($card->allowed_rows),
						'actions'	=> $actions_list,
						'type'		=> $msg->type
					];
				//If it is magic effect
				}else{
					//Get ME data
					$magic = \DB::table('tbl_magic_effect')->select('effect_actions')->find($id);
					$actions_list = [];
					$actions = unserialize($magic->effect_actions);
					//Process actions
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

			//Get card description
			case 'cartDescription':
				//If there is pointer of ME
				$data = ( (isset($msg->type)) && (!empty($msg->type)) )
					? BattleFieldController::getMagicDescription(Crypt::decrypt($msg->card))//Get ME data
					: BattleFieldController::getCardDescription(Crypt::decrypt($msg->card));//Get card data

				$result = [
					'message'	=> 'cartDescription',
					'data'		=> $data
				];
				self::sendMessageToSelf($from, $result);
			break;

			//User with "Cursed" fraction choose first-step player
			case 'cursedWantToChangeTurn':
				//Get player identificator
				$player = ($this->users_data['p1']['login'] == $msg->user)? 'p1': 'p2';

				//Set chosen player for step
				$this->step_status['round_status']['current_player'] = $this->users_data[$player]['login'];
				//Card source set to default - hand
				$this->step_status['round_status']['card_source'] = [$player => 'hand'];

				$turn_expire = $msg->time;
				if($turn_expire > $timing_settings['step_time']){
					$turn_expire = $timing_settings['step_time'];
				}

				//Save player ID for first step
				$battle->user_id_turn = $this->users_data[$player]['id'];
				$battle->turn_expire = $turn_expire + time();
				$battle->save();

				\DB::table('tbl_battle_members')
					->where('id', '=', $this->users_data[$msg->ident->userId]['battle_member_id'])
					->update([
						'addition_data'	=> '',
						'round_passed'	=> '0',
						'turn_expire'	=> $turn_expire
					]);

				//Set timing to timestamp of chosen player
				$this->step_status['timing'] = $this->users_data[$this->users_data[$player]['pseudonim']]['turn_expire'];
				self::sendUserMadeAction($this->users_data, $this->step_status, $msg, $SplBattleObj, $from);
			break;

			//Player gives up
			case 'userGivesUp':
				$battle->fight_status = 3;//Set fight status to "battle-finished"(3)
				$battle->save();
				//Result message
				$game_result = 'Игру выграл '.$this->users_data['opponent']['login'];
				//Set winner as opponent
				$winner = $this->users_data['opponent']['id'];
				//Save game results
				$to_self = self::saveGameResults($this->users_data['opponent']['id'], $battle, 'win', 'leave');
				$to_enemy = self::saveGameResults($this->users_data['user']['id'], $battle, 'loose', 'leave');

				//Set the busy marker to FALSE
				\DB::table('users')->where('id','=',$this->users_data['user']['id'])->update(['user_busy' => 0]);
				\DB::table('users')->where('id','=',$this->users_data['opponent']['id'])->update(['user_busy' => 0]);

				$result = ['message' => 'gameEnds', 'gameResult' => $game_result, 'battleInfo' => $msg->ident->battleId];

				//Send mesages to players
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

			//Drop card from deck/discard
			case 'dropCard':
				//If deck/discard belongs to oppenent
				if($msg->player != $this->users_data['user']['player']){
					$position = -1;//If position == -1 -> card does not isset
					//Get dropped card position
					foreach($this->users_data[$msg->player][$msg->deck] as $card_iter => $card_data){
						if ($card_data == Crypt::decrypt($msg->card)) {
							$position = $card_iter;
							break;
						}
					}
					//If card isset
					if($position >= 0){
						//Get card data
						$card = BattleFieldController::cardData($this->users_data[$msg->player][$msg->deck][$position]);
						//Set card to dropped cards array
						$this->step_status['dropped_cards'][$msg->player][$msg->deck][] = $card['caption'];
						//Delete card from deck
						unset($this->users_data[$msg->player][$msg->deck][$position]);
						$this->users_data[$msg->player][$msg->deck] = array_values($this->users_data[$msg->player][$msg->deck]);

						//Save deck
						\DB::table('tbl_battle_members')->where('id', '=', $this->users_data[$msg->player]['battle_member_id'])->update([
							'user_'.$msg->deck => serialize($this->users_data[$msg->player][$msg->deck])
						]);
						$result = $this->step_status;
						$result['message'] = 'dropCard';
						//Send messages to users
						self::sendMessageToSelf($from, $result);
						self::sendMessageToOthers($from, $result, $SplBattleObj[$msg->ident->battleId]);
					}
				}
			break;

			//Get card from battle field and return it to hand (used in regroup action)
			case 'returnCardToHand':
				//Battle field data
				$battle_field = unserialize($battle->battle_field);
				//magic usage data
				$this->magic_usage = unserialize($battle->magic_usage);
				//Current player identificator
				$player = $this->users_data[$msg->ident->userId]['player'];
				//Get buffs for each row
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				//Turn expire timestamp
				$turn_expire = $timing_settings['step_time'];
				//Save timing
				\DB::table('tbl_battle_members')
					->where('id','=',$this->users_data[$msg->ident->userId]['battle_member_id'])
					->update(['turn_expire' => $turn_expire]);
				//Looking for regrouping card
				foreach($battle_field[$player] as $row => $row_data){
					foreach($row_data['warrior'] as $card_iter => $card_data){
						//Catch card
						if($card_data['id'] == Crypt::decrypt($msg->card)){
							//Get card data
							$card = BattleFieldController::cardData($card_data['id']);
							//Add card to hand
							$this->users_data[$player]['hand'][] = $card_data['id'];
							//Add card to hand added_cards array
							$this->step_status['added_cards'][$player]['hand'][] = $card;
							//Set the card is dropping from battle field
							$this->step_status['dropped_cards'][$player][$row][$card_iter] = $card['caption'];
							//Set regroup card for it animation
							$this->step_status['actions']['regroup_card'] = $card;
							//Set amination of regroup
							$this->step_status['actions']['appear'][$player][$row][$card_iter] = 'regroup';
							//Drop card from battle field
							unset($battle_field[$player][$row]['warrior'][$card_iter]);
							$battle_field[$player][$row]['warrior'] = array_values($battle_field[$player][$row]['warrior']);
							break 2;
						}
					}
				}
				//Recalculate battle field
				$battle_info = BattleFieldController::battleInfo($battle, $battle_field, $this->users_data, $this->magic_usage, $this->step_status);
				//Cards strength array
				$this->step_status['actions']['cards_strength'] = $battle_info['cards_strength'];
				//Get modified buffs by each row
				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				//Compare old buffs array with modified
				foreach($field_buffs as $field => $rows){
					//If new buff status disappears in field
					if(!isset($new_field_buffs[$field])){
						$this->step_status['actions']['disappear'][$field] = $field_buffs[$field];
					}else{
						//if new buff status disappears in row
						foreach($rows as $row => $row_data){
							if(isset($new_field_buffs[$field][$row])){
								$this->step_status['actions']['disappear'][$field][$row] = array_diff($field_buffs[$field][$row], $new_field_buffs[$field][$row]);
							}else{
								$this->step_status['actions']['disappear'][$field][$row] = $field_buffs[$field][$row];
							}
						}
					}
				}
				//Drop empty fields in disappear array
				if(isset($this->step_status['actions']['disappear'])){
					foreach($this->step_status['actions']['disappear'] as $action_player => $rows){
						//Drop for each row
						foreach($rows as $row => $data){
							if(empty($this->step_status['actions']['disappear'][$action_player][$row])){
								unset($this->step_status['actions']['disappear'][$action_player][$row]);
							}
						}
						//Drop for each player
						if(empty($this->step_status['actions']['disappear'][$action_player])){
							unset($this->step_status['actions']['disappear'][$action_player]);
						}
					}
				}

				//Remove additional data
				$this->users_data[$player]['addition_data'] = [];
				//Sorting decks
				$this->users_data = self::sortDecksByStrength($this->users_data);
				//Save user data
				\DB::table('tbl_battle_members')->where('id','=',$this->users_data[$msg->ident->userId]['battle_member_id'])
					->update([
						'user_hand'		=> serialize($this->users_data[$player]['hand']),
						'addition_data'	=> '',
						'card_to_play'	=> 'a:0:{}',
						'card_source'	=> 'hand'
					]);
				//Get next turn step player ID
				$user_type = (0 != $this->users_data['opponent']['round_passed'])? 'user': 'opponent';

				$this->step_status['timing'] = $turn_expire;
				$battle->battle_field	= serialize($battle_field);
				$battle->user_id_turn	= $this->users_data[$user_type]['id'];
				$battle->turn_expire	= $this->step_status['timing'] + time();
				$battle->save();

				//Switch regroup was activated by card or ME
				$card_image = ($msg->type == 'card')
					? \DB::table('tbl_cards')->select('img_url')->where('slug','=','peregruppirovka')->first()
					: \DB::table('tbl_magic_effect')->select('img_url')->where('slug','=','taktika')->first();
				//Set regroup animation image
				$this->step_status['actions']['regroup_img'] = '/img/card_images/'.$card_image->img_url;

				//Remove popup activation; set other values to defaults
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
// /Users actions handler

//Service functions
	/**
	 * Player send step-result data
	 * @param $users_data	users data array ($this->users_data)
	 * @param $step_status	step status array ($this->step_status)
	 * @param $msg			socket income message
	 * @param $SplBattleObj	current Battle object
	 * @param $from			user connection object
	 */
	protected static function sendUserMadeAction($users_data, $step_status, $msg, $SplBattleObj, $from){
		$step_status['counts'] = self::getDecksCounts($users_data);//Get decks cards quantity counters

		//Cards shirts
		$step_status['images'] = [
			$users_data['user']['login'] => $users_data['user']['card_images'],
			$users_data['opponent']['login'] => $users_data['opponent']['card_images'],
		];

		//Players energy counters
		$step_status['users_energy'] = [
			$users_data['user']['login']	=> $users_data['user']['energy'],
			$users_data['opponent']['login']=> $users_data['opponent']['energy']
		];

		$result = $step_status;
		$result['message'] = 'userMadeAction';
		$result['battleInfo'] = $msg->ident->battleId;//This battle ID
		$result['passed_user'] = '';//Passed user login by default
		$result['deck_slug'] = $users_data['user']['current_deck'];//Fraction caption

		//If there is only one passed user
		if(($users_data['opponent']['round_passed'] + $users_data['user']['round_passed']) == 1){
			$result['passed_user'] = ($users_data['opponent']['round_passed'] > 0)
				? $users_data['opponent']['login']
				: $users_data['user']['login'];
		}

		self::sendMessageToSelf($from, $result);//Send message to self

		//Destroy hand and deck arrays for opponent view (opponent should not see user's hand and deck)
		foreach($result['added_cards'] as $player => $decks){
			unset($result['added_cards'][$player]['hand']);
		}
		foreach($result['dropped_cards'] as $player => $decks){
			unset($result['dropped_cards'][$player]['deck']);
		}

		//If there was activated ability of "Cursed" - "chose first-step user" or peep_card popup
		if( ($users_data['opponent']['login'] != $result['round_status']['current_player']) || ($step_status['actions']['appear'] == 'peep_card') ){
			$result['round_status']['cards_to_play'] = [];
			$result['round_status']['activate_popup'] = '';
		}
		$result['deck_slug'] = $users_data['opponent']['current_deck'];//opponent fraction
		self::sendMessageToOthers($from, $result, $SplBattleObj[$msg->ident->battleId]);//Send messages to all players
	}

	/**
	 * Get decks card counters for both or special player
	 * @param $users_data
	 * @param string $player - player identificator (p1/p2/both)
	 * @return array [player=>[deck/hand/discard]=> int(card_count)]
	 */
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

	/**
	 * !!!WARNING!!!
	 * CRUNCH AVAILABLE
	 * This sorting function should work with variable pointer (&$users_data)
	 * But in the reason of PHP bug (array iteration with pointer) this function works as is
	 * That's why it allocated to three group of functions
	 * @param $users_data
	 * @return mixed
	 */
	protected static function sortDecksByStrength($users_data){
		//Get cards data for each deck-type
		$users_data['user']['deck'] = BattleFieldController::recontentDecks($users_data['user']['deck']);
		$users_data['user']['discard'] = BattleFieldController::recontentDecks($users_data['user']['discard']);
		$users_data['user']['hand'] = BattleFieldController::recontentDecks($users_data['user']['hand']);
		$users_data['opponent']['hand'] = BattleFieldController::recontentDecks($users_data['opponent']['hand']);
		$users_data['opponent']['discard'] = BattleFieldController::recontentDecks($users_data['opponent']['discard']);
		$users_data['opponent']['deck'] = BattleFieldController::recontentDecks($users_data['opponent']['deck']);

		//Sorting by strength then by title
		BattleFieldController::sortingDeck($users_data['user']['deck']);
		BattleFieldController::sortingDeck($users_data['user']['discard']);
		BattleFieldController::sortingDeck($users_data['user']['hand']);
		BattleFieldController::sortingDeck($users_data['opponent']['deck']);
		BattleFieldController::sortingDeck($users_data['opponent']['discard']);
		BattleFieldController::sortingDeck($users_data['opponent']['hand']);

		//Set card IDs to each deck-type
		$users_data['user']['deck'] = self::refillDeckWithIds($users_data['user']['deck']);
		$users_data['user']['discard'] = self::refillDeckWithIds($users_data['user']['discard']);
		$users_data['user']['hand'] = self::refillDeckWithIds($users_data['user']['hand']);
		$users_data['opponent']['hand'] = self::refillDeckWithIds($users_data['opponent']['hand']);
		$users_data['opponent']['discard'] = self::refillDeckWithIds($users_data['opponent']['discard']);
		$users_data['opponent']['deck'] = self::refillDeckWithIds($users_data['opponent']['deck']);
		return $users_data;
	}

	/**
	 * Puts in deck card IDs
	 * @param $deck
	 * @return array
	 */
	protected static function refillDeckWithIds($deck){
		$result = [];
		foreach($deck as $i => $card){
			$result[$i] = $card['id'];
		}
		return $result;
	}

	/**
	 * Return number of field by it caption
	 * @param $field
	 * @return int
	 */
	protected static function strRowToInt($field){
		switch($field){
			case 'meele':		$field_row = 0; break;//melee field
			case 'range':		$field_row = 1; break;//range field
			case 'superRange':	$field_row = 2; break;//super-range field
			case 'sortable-cards-field-more':$field_row = 3; break;//middle field
		}
		return $field_row;
	}

	/**
	 * Drop card from some deck by card ID
	 * @param $deck
	 * @param $card_id
	 * @return array
	 */
	protected static function dropCardFromDeck($deck, $card_id){
		//if card Id is crypted
		if(strlen($card_id) > 11){
			$card_id = Crypt::decrypt($card_id);
		}
		$deck = array_values($deck);
		//looking for card in deck
		foreach($deck as $card_iter => $card){
			//If card isset -> delete it
			if($card == $card_id){
				unset($deck[$card_iter]);
				break;
			}
		}
		$deck = array_values($deck);
		return $deck;
	}

	/**
	 * Proccess card/ME action
	 * @param $action			action data
	 * @param $battle_field		battle field data
	 * @param $users_data		users data ($this->users_data)
	 * @param $step_status		step status data ($this->step-status)
	 * @param $user_turn_id		current user ID
	 * @param $msg				socket message object
	 * @param $magic_usage		magic usage array
	 * @return array
	 * [
	 * 	user_turn_id	modified turn-step user ID
	 * 	magic_usage		modified magic usage array
	 * 	battle_field	modified battle_field data
	 * 	users_data		modified users data
	 * 	step_status		modified step status data
	 * ]
	 */
	protected static function actionProcessing($action, $battle_field, $users_data, $step_status, $user_turn_id, $msg, $magic_usage){
		switch($action['caption']){
			//Block magic abilities
			case 'block_magic':
				//Set the user used all magic abilities in each round
				$magic_usage[$users_data['opponent']['player']][0] = ['id' => Crypt::decrypt($msg->magic), 'allow'=>'0'];
				$magic_usage[$users_data['opponent']['player']][1] = ['id' => Crypt::decrypt($msg->magic), 'allow'=>'0'];
				$magic_usage[$users_data['opponent']['player']][2] = ['id' => Crypt::decrypt($msg->magic), 'allow'=>'0'];
				//Send action animation
				$step_status['actions']['appear'] = $action['caption'];
			break;

			//Card call (user choose one or more cards from self/opponent deck)
			case 'call':
				$temp = self::makeCallAction($action, $users_data, $step_status);
				$step_status = $temp['step_status'];
				$users_data = $temp['users_data'];
				$user_turn_id = $temp['user_turn_id'];
			break;

			//Cure - cancel actions of special-debuff cards/ME
			case 'cure'://ИСЦЕЛЕНИЕ
				//Get current field buffs
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				//Drop middle cards
				foreach($battle_field['mid'] as $card_data){
					//Switch the player-type discard
					$user_type = ($users_data['user']['login'] == $card_data['login'])? 'user': 'opponent';
					//Add card to discard
					$users_data[$user_type]['discard'][] = $card_data['id'];
					//Get card data
					$card = BattleFieldController::cardData($card_data['id']);
					//Animation: cards add to discard
					$step_status['added_cards'][$users_data[$user_type]['player']]['discard'][] = $card;
					//Animation: cards drops from middle field
					$step_status['dropped_cards'][$users_data[$user_type]['player']]['mid'][] = $card['caption'];
				}
				//Remove all cards from middle field
				$battle_field['mid'] = [];
				//Action animation
				$step_status['actions']['appear'] = $action['caption'];
				//Get modified buffs by each row 
				$new_field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				//Compare old buffs with modified
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
			//Drop card from opponent hand
			case 'drop_card'://CБРОС КАРТ ПРОТИВНИКА В ОТБОЙ
				//If not empty opponent's hand
				if(!empty($users_data['opponent']['hand'])){
					//enemyDropHand_cardCount - quantity cards to drop
					for($i=0; $i < $action['enemyDropHand_cardCount']; $i++){
						//Random card to drop
						$rand = mt_rand(0, count($users_data['opponent']['hand'])-1);
						//Move card to discard
						$users_data['opponent']['discard'][] = $users_data['opponent']['hand'][$rand];
						//get card data
						$card = BattleFieldController::getCardNaturalSetting($users_data['opponent']['hand'][$rand]);

						$step_status['dropped_cards'][$users_data['opponent']['player']]['hand'][$rand] = $card['caption'];
						$step_status['added_cards'][$users_data['opponent']['player']]['discard'][] = $card;
						//Destroy card from hand
						unset($users_data['opponent']['hand'][$rand]);
						$users_data['opponent']['hand'] = array_values($users_data['opponent']['hand']);
					}
					//Action animation
					$step_status['actions']['appear'] = $action['caption'];
				}
			break;

			//Card heal (user choose one or more cards from self/opponent discard)
			//Action is same as 'call' (look for line 1603)
			case 'heal'://ЛЕКАРЬ
				$temp = self::makeHealAction($action, $users_data, $step_status);
				$step_status = $temp['step_status'];
				$users_data = $temp['users_data'];
				$user_turn_id = $temp['user_turn_id'];
			break;

			//Card destroy other cards
			case 'killer'://УБИЙЦА
				$temp = self::makeKillerAction($action, $users_data, $step_status, $battle_field);
				$battle_field = $temp['battle_field'];
				$step_status = $temp['step_status'];
				$users_data = $temp['users_data'];
			break;

			//Action add to battle field other cards
			case 'master'://ПОВЕЛИТЕЛЬ
				$temp = self::makeMasterAction($action, $users_data, $step_status, $battle_field);
				$battle_field = $temp['battle_field'];
				$step_status = $temp['step_status'];
				$users_data = $temp['users_data'];
			break;

			//Action steals card(s) from opponent battle field
			case 'obscure'://ОДУРМАНИВАНИЕ
				//Current battle field buffs
				$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
				$cards_can_be_obscured = [];
				$min_strength = 999;
				$max_strength = 0;

				//obscure_ActionRow - row that available to steal
				foreach($action['obscure_ActionRow'] as $row_iter => $row){
					foreach($battle_field[$users_data['opponent']['player']][$row]['warrior'] as $card_data){
						$card = BattleFieldController::cardData($card_data['id']);
						//obscure_maxCardStrength - maximal strength of card that can be stolen
						if($card_data['strength'] <= $action['obscure_maxCardStrength']){
							//if card can be stolen with immunity ignore
							$allow_obscure = BattleFieldController::checkForSimpleImmune($action['obscure_ignoreImmunity'], $card['actions']);
							//allow to steal
							if($allow_obscure){
								//get max strength
								$max_strength = ($card_data['strength'] > $max_strength)
									? $card_data['strength']
									: $max_strength;
								//get min strength
								$min_strength = ($card_data['strength'] < $min_strength)
									? $card_data['strength']
									: $min_strength;

								//cards can be stolen array
								$cards_can_be_obscured[] = [
									'id'		=> $card['id'],
									'caption'	=> $card['caption'],
									'strength'	=> $card_data['strength'],
									'row'		=> $row
								];
							}
						}
					}
				}

				if($min_strength < 1) $min_strength = 1;

				//Process steal by cards strength modificator
				if(!empty($cards_can_be_obscured)){
					switch($action['obscure_strenghtOfCard']){
						case '0': $card_strength_to_obscure = $min_strength; break;//Weakest
						case '1': $card_strength_to_obscure = $max_strength; break;//Strongest
						case '2': //Random
							$random = mt_rand(0, count($cards_can_be_obscured)-1);
							$card_strength_to_obscure = $cards_can_be_obscured[$random]['strength'];
						break;
					}
				}

				$cards_to_obscure = [];
				//Get stolen cards
				//obscure_quantityOfCardToObscure - quantity of stolen cards
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

				//Apply card steal
				for($i=0; $i<count($cards_to_obscure); $i++){
					foreach($battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior'] as $j => $card_data){
						//steal card ID match
						if(Crypt::decrypt($cards_to_obscure[$i]['id']) == $card_data['id']){
							//Add stolen card to uses barrle field
							$battle_field[$users_data['user']['player']][$cards_to_obscure[$i]['row']]['warrior'][] = [
								'id'		=> $card_data['id'],
								'caption'	=> $card_data['caption'],
								'strength'	=> $card_data['strength'],
								'login'		=> $users_data['user']['login']
							];
							//Get card data
							$card_obscured = BattleFieldController::cardData($card_data['id']);
							foreach($card_obscured['actions'] as $obscured_card_action){
								switch($obscured_card_action['caption']){
									case 'call':
										$temp = self::makeCallAction($obscured_card_action, $users_data, $step_status);
										$step_status = $temp['step_status'];
										$users_data = $temp['users_data'];
										$user_turn_id = $temp['user_turn_id'];
									break;
									case 'heal':
										$temp = self::makeHealAction($obscured_card_action, $users_data, $step_status);
										$step_status = $temp['step_status'];
										$users_data = $temp['users_data'];
										$user_turn_id = $temp['user_turn_id'];
									break;
									case 'killer':
										$temp = self::makeKillerAction($obscured_card_action, $users_data, $step_status, $battle_field);
										$battle_field = $temp['battle_field'];
										$step_status = $temp['step_status'];
										$users_data = $temp['users_data'];
									break;
									case 'master':
										$temp = self::makeMasterAction($obscured_card_action, $users_data, $step_status, $battle_field);
										$battle_field = $temp['battle_field'];
										$step_status = $temp['step_status'];
										$users_data = $temp['users_data'];
									break;
									case 'spy':
										$temp = self::makeSpyAction($obscured_card_action, $users_data, $step_status);
										$step_status = $temp['step_status'];
										$users_data = $temp['users_data'];
									break;
								}
							}
							//Animations of add/drop card
							$step_status['added_cards'][$users_data['user']['player']][$cards_to_obscure[$i]['row']][] = $card_obscured;
							$step_status['dropped_cards'][$users_data['opponent']['player']][$cards_to_obscure[$i]['row']][$j] = $card_obscured['caption'];
							//Action animation
							$step_status['actions']['appear'][$users_data['opponent']['player']][$cards_to_obscure[$i]['row']][$j] = 'obscure';
							//Drop stolen card form opponent battle fields
							unset($battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior'][$j]);
							$battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior'] = array_values($battle_field[$users_data['opponent']['player']][$cards_to_obscure[$i]['row']]['warrior']);
							break;
						}
					}
				}

				//Compare old battle field buff with modified
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

			//Peep opponent hand cards
			case 'peep_card'://ПРОСМОТР КАРТ ПРОТИВНИКА
				$temp_hand = $users_data['opponent']['hand'];
				//Peep card quantity
				$n = (count($users_data['opponent']['hand']) < $action['overview_cardCount'])
					? count($users_data['opponent']['hand'])
					: $action['overview_cardCount'];
				//Fill peep cards array
				while(count($users_data['user']['cards_to_play']) < $n){
					//Get random card
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

			//Return card to hand from battle field
			case 'regroup'://ПЕРЕГРУППИРОВКА
				//Get battle field cards to regroup
				foreach($battle_field[$users_data['user']['player']] as $row => $row_data){
					foreach($row_data['warrior'] as $card_data){
						//Get card data
						$card = BattleFieldController::cardData($card_data['id']);
						$allow_to_regroup = true;
						//Check if regroup can take cards with full immunity
						if($action['regroup_ignoreImmunity'] == 0){
							$allow_to_regroup = BattleFieldController::checkForFullImmune($action['regroup_ignoreImmunity'], $card['actions']);
						}
						if($allow_to_regroup){
							//Regroup cards array fill
							$users_data['user']['cards_to_play'][] = $card_data['id'];
							$step_status['round_status']['cards_to_play'][] = $card;
						}
					}
				}
				//cards popup activates after user action
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
				if(!empty($step_status['played_card']['card']['id'])){
					$brotherhood_field = $step_status['played_card']['move_to']['player'];
					$brotherhood_row = $step_status['played_card']['move_to']['row'];
					foreach($battle_field[$brotherhood_field][$brotherhood_row]['warrior'] as $card_iter => $card_data){
						if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
							$step_status['actions']['appear'][$brotherhood_field][$brotherhood_row][$card_iter] = $action['caption'];
						}
					}
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
				$temp = self::makeSpyAction($action, $users_data, $step_status);
				$step_status = $temp['step_status'];
				$users_data = $temp['users_data'];
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

	protected static function makeKillerAction($action, $users_data, $step_status, $battle_field){
		//Get current field buffs
		$field_buffs = BattleFieldController::getBattleBuffs($battle_field);
		//if card attack friendly cards
		$players = (isset($action['killer_atackTeamate']) && ($action['killer_atackTeamate']== 1))
			? ['p1', 'p2']
			: [$users_data['opponent']['player']];
		//If card must attack only special group of cards
		$groups = $action['killer_group'];
		//Limit of card maximal strength
		$strength_limit_to_kill = ($action['killer_enemyStrenghtLimitToKill'] < 1) ? 999: $action['killer_enemyStrenghtLimitToKill'];

		$rows_strength = [];	//Summary of strength of cards in any row
		$max_strength = 0;		//Maximal card strength in field
		$min_strength = 999;	//Minimal card strength in field
		$card_strength_set = [];//Set of cards strength for random choise

		$cards_to_destroy = [];//cards that can be destroyed array
		foreach($players as $player){
			//killer_ActionRow -> rows where killer action should be used
			foreach($action['killer_ActionRow'] as $row){
				foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
					//Get summary row strength
					if(isset($rows_strength[$player][$row])){
						$rows_strength[$player][$row] += $card_data['strength'];
					}else{
						$rows_strength[$player][$row] = $card_data['strength'];
					}
					//if killing process applying to the group
					if(!empty($groups)){
						//Get card data
						$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
						foreach($card['group'] as $group_id){
							//if card belongs to killing group
							if(in_array($group_id, $groups)){
								//if card strength is less then limit of card maximal strength
								if($card_data['strength'] < $strength_limit_to_kill){
									if($max_strength < $card_data['strength']){
										$max_strength = $card_data['strength'];//maximal card strength
									}
									if($min_strength > $card_data['strength']){
										$min_strength = $card_data['strength'];//minimal card strength
									}
									//Fill set of cards strength for random choise
									$card_strength_set[] = $card_data['strength'];
									//cards-can-be-destroyed array fill
									$cards_to_destroy[$player][$row][$card_iter] = [
										'id'		=> $card_data['id'],
										'strength'	=> $card_data['strength']
									];
								}
							}
						}
					}else{
						//Get card data
						$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
						//Check if card has immune
						$allow_by_immune = BattleFieldController::checkForSimpleImmune($action['killer_ignoreKillImmunity'], $card['actions']);
						//if killer action has permission to kill card
						if($allow_by_immune){
							if($card_data['strength'] < $strength_limit_to_kill){
								if($max_strength < $card_data['strength']){
									$max_strength = $card_data['strength'];//maximal card strength
								}
								if($min_strength > $card_data['strength']){
									$min_strength = $card_data['strength'];//minimal card strength
								}
								//Fill set of cards strength for random choise
								$card_strength_set[] = $card_data['strength'];
								//cards-can-be-destroyed array fill
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
			case '0':	$card_strength_to_kill = $min_strength; break;//Kill most weak card
			case '1':	$card_strength_to_kill = $max_strength; break;//Kill card with maximal strength
			case '2':	//Kill random card
				$random = mt_rand(0, count($card_strength_set)-1);
				$card_strength_to_kill = $card_strength_set[$random];
				break;
		}

		$card_to_kill = [];//Cards that can be killed
		foreach($cards_to_destroy as $player => $rows){
			foreach($rows as $row => $cards){
				foreach($cards as $card_iter => $card_data){
					$allow_to_kill_by_force_amount = true;
					//killer_recomendedTeamateForceAmount_OnOff - kill by summary row strength value
					//0 - disallow; 1- allow
					if($action['killer_recomendedTeamateForceAmount_OnOff'] > 0){
						$row_summ = 0;
						//Calculate sum of row strength value
						foreach($action['killer_recomendedTeamateForceAmount_ActionRow'] as $row_to_calculate){
							if(isset($rows_strength[$player][$row_to_calculate])){
								$row_summ += $rows_strength[$player][$row_to_calculate];
							}
						}
						//killer_recomendedTeamateForceAmount_Selector - row strength sum quantificator
						switch($action['killer_recomendedTeamateForceAmount_Selector']){
							case '0':	//Row sum is greater than used value
								$allow_to_kill_by_force_amount = ($action['killer_recomendedTeamateForceAmount_OnOff'] <= $row_summ) ? true : false; break;
							case '1':	//Row sum is less than used value
								$allow_to_kill_by_force_amount = ($action['killer_recomendedTeamateForceAmount_OnOff'] >= $row_summ) ? true : false; break;
							case '2':	//Row sum is equal to used value
								$allow_to_kill_by_force_amount = ($action['killer_recomendedTeamateForceAmount_OnOff'] == $row_summ) ? true : false; break;
						}
					}
					//killer_killedQuality_Selector - case strength type of card to kill
					switch($action['killer_killedQuality_Selector']){
						case '0': //Most weak
							if(($card_data['strength'] <= $card_strength_to_kill) && ($allow_to_kill_by_force_amount) ){
								$card_to_kill[$player][$row][$card_iter] = $card_data;
							}
							break;
						case '1': //Most strongest
							if(($card_data['strength'] >= $card_strength_to_kill) && ($allow_to_kill_by_force_amount) ){
								$card_to_kill[$player][$row][$card_iter] = $card_data;
							}
							break;
						case '2': //Is equeal to strength value
							if(($card_data['strength'] == $card_strength_to_kill) && ($allow_to_kill_by_force_amount) ){
								$card_to_kill[$player][$row][$card_iter] = $card_data;
							}
							break;
					}
				}
			}
		}

		//Kill one or more cards: 0- one card; 1- all cards that fall under conditions
		if($action['killer_killAllOrSingle'] == 0){
			$temp = [];
			//Looking for first card that fall under conditions
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

		//Killing process
		foreach($card_to_kill as $player => $row_data){
			foreach($row_data as $row => $cards_to_kill){
				foreach($cards_to_kill as $card_iter => $card_to_kill){
					//Move card to discard
					$users_data[$player]['discard'][] = $card_to_kill['id'];
					//Get card data
					$card = BattleFieldController::cardData($card_to_kill['id']);
					//Animation of action
					$step_status['actions']['appear'][$player][$row][$card_iter] = 'killer';

					$step_status['added_cards'][$player]['discard'][] = $card;
					unset($battle_field[$player][$row]['warrior'][$card_iter]);
				}
				$battle_field[$player][$row]['warrior'] = array_values($battle_field[$player][$row]['warrior']);
			}
		}

		//Compare old battle field buffs with modified
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

		return [
			'battle_field' => $battle_field,
			'step_status' => $step_status,
			'users_data' => $users_data
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

	protected static function makeCallAction($action, $users_data, $step_status){
		$users_data['user']['card_source'] = 'deck';
		$action_data = [
			/*	Get cards from
				0 - user's deck; 1- opponent deck*/
			'deckChoise'	=> $action['summon_deckChoise'],
			/*	Get card of type
				0- concrete card(s);
				1- Card for concrete row;
				2- card of concrete type;(special/ordinary)
				3- card of concrete group;
				4- any card*/
			'typeOfCard'	=> $action['summon_typeOfCard'],
			/*	Getting card algorithm
				0- manual; 1- random*/
			'cardChoise'	=> $action['summon_cardChoise'],
			/*	Ignore card immunity
				0- do not ignore; 1- ignore;*/
			'ignoreImmunity'=> $action['summon_ignoreImmunity']
		];
		//if deckChoise == 0 -> receive array of card IDs
		if(isset($action['summon_type_singleCard']))$action_data['type_singleCard'] = $action['summon_type_singleCard'];
		//if deckChoise == 1 -> receive card action row (0- melee; 1- range; 2- super-range)
		if(isset($action['summon_type_actionRow']))	$action_data['type_actionRow'] = $action['summon_type_actionRow'];
		//if deckChoise == 2 -> receive card type (0- special card; 1- warrior card)
		if(isset($action['summon_type_cardType']))	$action_data['type_cardType'] = $action['summon_type_cardType'];
		//if deckChoise == 3 -> receive array of groups IDs
		if(isset($action['summon_type_group']))		$action_data['type_group'] = $action['summon_type_group'];
		//Make summon
		$summon_result = self::makeHealOrSummon($users_data, $step_status, $action_data);
		//card activates after user action
		$users_data		= $summon_result['users_data'];
		$user_turn_id	= $summon_result['user_turn_id'];
		$step_status	= $summon_result['step_status'];
		//Action animation
		$step_status['actions'][] = $action['caption'];
		return [
			'step_status' => $step_status,
			'users_data' => $users_data,
			'user_turn_id' => $user_turn_id
		];
	}

	protected static function makeHealAction($action, $users_data, $step_status){
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

		return [
			'step_status' => $step_status,
			'users_data' => $users_data,
			'user_turn_id' => $user_turn_id
		];
	}

	protected static function makeSpyAction($action, $users_data, $step_status){
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
		return [
			'step_status' => $step_status,
			'users_data' => $users_data
		];
	}

	protected static function makeMasterAction($action, $users_data, $step_status, $battle_field){
		$cards_can_be_added = [];
		//master_cardSource - destination deck (deck|hand|discard)
		foreach($action['master_cardSource'] as $destination){
			//looking for card groups
			foreach($users_data['user'][$destination] as $card_data){
				//Get card data
				$card = BattleFieldController::cardData($card_data);
				//If card has some group
				if(!empty($card['group'])){
					//if card group intersects to action group
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
		//master_summonByModificator - summon by strength modificator: 0- weakest; 1- strongest; 2 -random
		switch($action['master_summonByModificator']){
			case '0': usort($cards_can_be_added, function($a, $b){return ($a['strength'] - $b['strength']);}); break;
			case '1': usort($cards_can_be_added, function($a, $b){return ($b['strength'] - $a['strength']);});break;
			case '2':
				$cards_shuffle_keys = array_keys($cards_can_be_added);
				shuffle($cards_shuffle_keys);
				array_merge(array_flip($cards_shuffle_keys), $cards_can_be_added);
				break;
		}

		//array of cards sources
		$cards_to_add = ['hand'=> [], 'deck'=>[], 'discard'=>[]];
		$n = (count($cards_can_be_added) < $action['master_maxCardsSummon'])? count($cards_can_be_added): $action['master_maxCardsSummon'];
		for($i=0; $i<$n; $i++){
			$cards_to_add[$cards_can_be_added[$i]['source_deck']][] = $cards_can_be_added[$i]['id'];
		}

		if($n > 0){
			$cards_count = 0;
			//add summon cards to battle field
			foreach($cards_to_add as $destination => $cards){
				if(!empty($cards)){
					foreach($users_data['user'][$destination] as $card_to_summon_iter => $card_to_summon){
						$card = BattleFieldController::cardData($card_to_summon);
						//if card can be added to battle field
						if(in_array($card_to_summon, $cards)){
							//if there are more than one card action row
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
						//if card limit is settled
						if($cards_count >= $action['master_maxCardsSummon']){
							$users_data['user'][$destination] = array_values($users_data['user'][$destination]);
							break 2;
						}
					}
					$users_data['user'][$destination] = array_values($users_data['user'][$destination]);
				}
			}
		}

		return [
			'users_data'	=> $users_data,
			'step_status'	=> $step_status,
			'battle_field'	=> $battle_field
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