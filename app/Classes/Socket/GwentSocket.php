<?php
namespace App\Classes\Socket;

use App\Battle;
use App\BattleMembers;
use App\League;
use App\User;
use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Site\SiteGameController;
use Ratchet\ConnectionInterface;

class GwentSocket extends BaseSocket
{
	protected $clients;  //Соединения клиентов
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
		/*$battle = Battle::find($this->battle_id);

		if($battle->fight_status < 3){
			if($battle->disconected_count <= 2){
				$battle->disconected_count++;
				$battle->save();
			}

			if($battle->disconected_count == 2){
				self::waitForRoundEnds($this, $conn);
			}
		}else{
			$this->clients->detach($conn);
			echo 'Connection '.$conn->resourceId.' has disconnected'."\n";
		}*/
	}

	protected static function sendMessageToOthers($from, $result, $battles){
		/*foreach ($battles as $client) {
			if ($client->resourceId != $from->resourceId) {
				$client->send(json_encode($result));
			}
		}*/
	}

	protected static function sendMessageToSelf($from, $message){
		//$from->send(json_encode($message));
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

		if(!$this->battles[$msg->ident->battleId]->contains($from)){
			$this->battles[$msg->ident->battleId]->attach($from);
		}
		$SplBattleObj = $this->battles;

		$timing_settings = SiteGameController::getTimingSettings();

		$battle = Battle::find($msg->ident->battleId); //Даные битвы
		$this->battle_id = $msg->ident->battleId;

		$battle_members = BattleMembers::where('battle_id', '=', $msg->ident->battleId)->get(); //Данные о участвующих в битве
		$users_data = [];

		\DB::table('users')->where('id', '=', $msg->ident->userId)->update([
			'updated_at'	=> date('Y-m-d H:i:s'),
			'user_online'	=> '1'
		]);

		//Создание массивов пользовательских данных
		foreach($battle_members as $key => $value){
			$user = User::find($value->user_id);
			$user_identificator = ($value->user_id == $battle->creator_id)? 'p1' : 'p2';
			if($value->user_id == $msg->ident->userId){
				$users_data['user'] = [
					'id'			=> $value->user_id,
					'login'			=> $user->login,
					'player'		=> $user_identificator,					//Идентификатор поля пользователя
					'user_magic'	=> unserialize($user->user_magic),
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
					'addition_data'	=> unserialize($value->addition_data),
					'battle_member_id'=> $value->id,						//ID текущей битвы
					'turn_expire'	=> $value->turn_expire,
					'time_shift'	=> $value->time_shift,
					'pseudonim'		=> 'user'
				];
				$users_data[$user_identificator] = &$users_data['user'];
				$users_data[$value->user_id] = &$users_data['user'];
			}else{
				$users_data['opponent'] = [
					'id'			=> $value->user_id,
					'login'			=> $user->login,
					'player'		=> $user_identificator,
					'user_magic'	=> unserialize($user->user_magic),
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
					'addition_data'	=> unserialize($value->addition_data),
					'battle_member_id'=> $value->id,
					'turn_expire'	=> $value->turn_expire,
					'time_shift'	=> $value->time_shift,
					'pseudonim'		=> 'opponent'
				];
				$users_data[$user_identificator] = &$users_data['opponent'];
				$users_data[$value->user_id] = &$users_data['opponent'];
			}
		}

		$this->step_status = [
			'added_cards'	=> [],
			'played_card'	=> [
				'card' => '',
				'move_to' => [
						'player'=> '',
						'row'	=> '',
						'user'	=> ''
					],
				'strength'	=> ''
			],
			'dropped_cards'	=> [],
			'played_magic'	=> '',
			'cards_strength'=> [],
			'actions'		=> [],
			'field_status'	=> []
		];
		if(isset($msg->timing)) $users_data['user']['turn_expire'] = $msg->timing - $users_data['user']['time_shift'];

		switch($msg->action){

		}
	}

}