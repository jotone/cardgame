<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Site\SiteGameController;
use App\Http\Controllers\Site\SiteFunctionsController;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Crypt;

class BattleFieldController extends BaseController{

	public static function resetBattleFieldCardsStrength($battle_field){
		foreach($battle_field as $field => $rows){
			if($field != 'mid'){
				foreach($rows as $row => $cards){
					foreach($cards['warrior'] as $i => $card_data){
						$card = self::getCardNaturalSetting($card_data['id']);
						$battle_field[$field][$row]['warrior'][$i]['strength'] = $card['strength'];
					}
				}
			}
		}
		return $battle_field;
	}

	public static function battleInfo($battle, $battle_field, $users_data, $magic_usage, $step_status){
		$battle_field = self::resetBattleFieldCardsStrength($battle_field);

		$actions_array_support = [];//Массив действий "Поддержка"
		$actions_array_fury = [];//Массив действий "Неистовство"
		$actions_array_fear = [];//Массив действий "Страшный"
		$actions_array_brotherhood = [];//Массив действий "Боевое братство"
		$actions_array_inspiration = [];//Массив действий "Воодушевление"

		$field_status = [
			'p1' => [
				['buffs' => [],'debuffs'=>[],'special'=>[],'warrior'=>[]],
				['buffs' => [],'debuffs'=>[],'special'=>[],'warrior'=>[]],
				['buffs' => [],'debuffs'=>[],'special'=>[],'warrior'=>[]],
			],
			'p2' => [
				['buffs' => [],'debuffs'=>[],'special'=>[],'warrior'=>[]],
				['buffs' => [],'debuffs'=>[],'special'=>[],'warrior'=>[]],
				['buffs' => [],'debuffs'=>[],'special'=>[],'warrior'=>[]],
			],
			'mid' => []
		];

		foreach($battle_field as $field => $rows){
			if($field != 'mid'){
				foreach($rows as $row => $cards){
					foreach($cards['warrior'] as $card_iter => $card_data){
						$card = self::cardData($card_data['id']);
						$card['login'] = $card_data['login'];
						$field_status[$field][$row]['warrior'][$card_iter]['card'] = $card;
						$field_status[$field][$row]['warrior'][$card_iter]['buffs']= [];
						$field_status[$field][$row]['warrior'][$card_iter]['debuffs']= [];
						$field_status[$field][$row]['warrior'][$card_iter]['strengthModified'] = $card['strength'];
						foreach($card['actions'] as $action){
							switch($action['caption']){
								case 'brotherhood':		$actions_array_brotherhood[$field][$row][$card_data['id']] = $card; break;
								case 'inspiration':		$actions_array_inspiration[$field][$row] = $card; break;
								case 'fury':			$actions_array_fury[$field.'/'.$row.'/'.$card_iter] = $card; break;
								case 'support':			$actions_array_support[$field.'/'.$row.'/'.$card_iter] = $card; break;
								case 'fear':			$actions_array_fear[$field][uniqid()] = $card; break;
							}
						}
					}
					if(!empty($cards['special'])){
						$card = self::cardData($cards['special']['id']);
						$field_status[$field][$row]['special']['card'] = $card;
						foreach($card['actions'] as $action){
							switch($action['caption']){
								case 'inspiration':		$actions_array_inspiration[$field][$row] = $card; break;
							}
						}
					}
				}
			}else{
				foreach($rows as $card_iter => $card_data){
					$card =  self::cardData($card_data['id']);
					$field_status['mid'][$card_iter]['card'] = $card;
					foreach($card['actions'] as $action){
						if($action['caption'] == 'fear'){
							if(!isset($actions_array_fear['mid'][$card_data['id']])){
								$actions_array_fear['mid'][$card_data['id']] = $card;
							}
						}
					}
				}
			}
		}

		//Применение "Поддержка" к картам
		foreach($actions_array_support as $card_path => $action_card){
			$player = ($action_card['login'] == $users_data['user']['login'])? $users_data['user']['player'] : $users_data['opponent']['player'];

			foreach($action_card['actions'] as $action_iter => $action_data){
				if($action_data['caption'] == 'support'){
					$groups = ((isset($action_data['support_actionToGroupOrAll'])) && ($action_data['support_actionToGroupOrAll'] != 0))? $action_data['support_actionToGroupOrAll'] : [];

					foreach($action_data['support_ActionRow'] as $row){
						$field_status[$player][$row]['buffs'][] = 'support';
						foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
							$allow_support = true;
							$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
							if($action_data['support_ignoreImmunity'] == 0){
								foreach($card['actions'] as $action){
									if($action['caption'] == 'immune'){
										if($action['immumity_type'] == 1){
											$allow_support = false;
										}
									}
								}
							}

							if($allow_support){
								if(!empty($groups)){
									foreach ($card['group'] as $group_id){
										if(in_array($group_id, $groups)){
											$strength = ( ($action_data['support_selfCast'] == 1) || ($card_path != $player.'/'.$row.'/'.$card_iter) )
												? $card_data['strength'] + $action_data['support_strenghtValue']
												: $card_data['strength'];

											$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'support';

											$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $strength;
											$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $strength;

											if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
												if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
													$step_status['played_card']['strength'] = $strength;
												}
											}

											$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
										}
									}
								}else{
									$strength = ( ($action_data['support_selfCast'] == 1) || ($card_path != $player.'/'.$row.'/'.$card_iter) )
										? $card_data['strength'] + $action_data['support_strenghtValue']
										: $card_data['strength'];

									$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'support';

									$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $strength;
									$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $strength;

									if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
										if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
											$step_status['played_card']['strength'] = $strength;
										}
									}

									$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
								}

							}
						}
						$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
					}
				}
			}
		}

		//Применение МЭ "Поддержка" к картам
		/*foreach($magic_usage as $player => $magic_data){
			foreach($magic_data as $activated_in_round => $magic_id){
				if($activated_in_round == $battle->round_count){
					if($magic_id['allow'] != '0'){
						$magic = json_decode(SiteGameController::getMagicData($magic_id['id']));//Данные о МЭ
						foreach($magic->actions as $action_iter => $action_data){
							if($action_data->action == '13'){
								foreach($action_data->support_ActionRow as $row_iter => $row){//Ряды действия МЭ
									//Применение МЭ к картам
									foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card){
										//Если у карты есть полный иммунитет
										$allow_magic = true;
										if($action_data->support_ignoreImmunity == 0){
											foreach($card['card']['actions'] as $j => $action){
												if($action->action == '5'){
													if($action->immumity_type == 1){
														$allow_magic = false;
													}
												}
											}
										}

										if($allow_magic){
											$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $card['strength'] + $action_data->support_strenghtValue;

											$field_status[$player][$row]['warrior'][$card_iter]['buffs'][]= 'support';
											$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $strength;
											$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
										}
									}
								}
							}
						}
					}
				}
			}
		}*/

		//Применение "Неистовость" к картам
		foreach($actions_array_fury as $card_id => $card_data){
			$enemy_player = ($card_data['login'] == $users_data['user']['login'])? 'opponent': 'user';
			$enemy_field = ($card_data['login'] == $users_data['user']['login'])? $users_data['opponent']['player']: $users_data['user']['player'];

			foreach($card_data['actions'] as $action_iter => $action){
				if($action['caption'] == 'fury'){
					$allow_fury_by_race = $allow_fury_by_row = $allow_fury_by_group = $allow_fury_by_magic = false;
					//Колода противника вызывает у карты неистовство
					if( (in_array($users_data[$enemy_player]['current_deck'], $action['fury_enemyRace'])) ){
						$allow_fury_by_race = true;
					}

					//Количество воинов в ряду/рядах вызывает неистовство
					if((isset($action['fury_ActionRow'])) && (!empty($action['fury_ActionRow']))){
						$row_cards_count = 0;
						for($i=0; $i<count($action['fury_ActionRow']); $i++){
							$row_cards_count += count($battle_field[$enemy_field][$action['fury_ActionRow'][$i]]['warrior']);
						}
						$allow_fury_by_row = ($row_cards_count >= $action['fury_enemyWarriorsCount']) ? true : false;
					}

					//Карта определенной группы вызывает неистовство
					if((isset($action['fury_group'])) && (!empty($action['fury_group']))){
						foreach($battle_field[$enemy_field] as $enemy_row){
							foreach($enemy_row['warrior'] as $enemy_card_data){
								$enemy_card = BattleFieldController::getCardNaturalSetting($enemy_card_data['id']);
								if(!empty($enemy_card['group'])){
									foreach($enemy_card['group'] as $group){
										if(in_array($group, $action['fury_group'])){
											$allow_fury_by_group = true;
										}
									}
								}
							}
						}
					}

					//Магия вызывает неистовство
					if( (isset($action['fury_abilityCastEnemy'])) && ($action['fury_abilityCastEnemy'] == 1)){
						foreach($magic_usage[$enemy_field] as $activated_in_round => $magic_data){
							if($battle['round_count'] == $activated_in_round){
								$allow_fury_by_magic = true;
							}
						}
					}

					if(($allow_fury_by_row) || ($allow_fury_by_race) || ($allow_fury_by_magic) || ($allow_fury_by_group)){
						$card_destination = explode('/',$card_id);
						$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strength'] += $action['fury_strenghtVal'];
						$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strengthModified'] += $action['fury_strenghtVal'];
						if($action['fury_strenghtVal'] >= 0){
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['buffs'][]= 'fury';
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['buffs'] = array_values(array_unique($field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['buffs']));
							$step_status['actions']['appear'][$card_destination[0]][$card_destination[1]][] = 'fury-buff';
							$step_status['actions']['cards'][$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['caption']] = $battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['caption'];
							$step_status['actions']['modify_strength'] = $action['fury_strenghtVal'];
							$step_status['actions']['type'] = 'fury-buff';
						}else{
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['debuffs'][]= 'fury';
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['debuffs'] = array_values(array_unique($field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['debuffs']));
							$step_status['actions']['cards'][$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['caption']] = $battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['caption'];
							$step_status['actions']['appear'][$card_destination[0]][$card_destination[1]][] = 'fury-debuff';
							$step_status['actions']['modify_strength'] = $action['fury_strenghtVal'];
							$step_status['actions']['type'] = 'fury-debuff';
						}
					}
				}
			}
		}
		$step_status['actions']['cards'] = array_values(array_unique($step_status['actions']['cards']));

		//Применение действия "Страшный" к картам
		/*foreach($actions_array_fear as $source => $cards){
			foreach($cards as $card_id => $card_data){
				foreach($card_data['actions'] as $action){
					if($action['caption'] == 'terrify'){
						//Карта действует на всех или только на противника
						if($action['fear_actionTeamate'] == 1){
							$players = ['p1', 'p2'];
						}else{
							$players = ($card_data['login'] == $users_data['user']['login'])
								? [$users_data['opponent']['player']]
								: [$users_data['user']['player']];
						}

						//Карта действует на группу
						$groups = (isset($action['fear_actionToGroupOrAll']))? $action['fear_actionToGroupOrAll']: [];

						foreach($players as $player){
							if(!in_array($users_data[$player]['current_deck'], $action['fear_enemyRace'])){
								foreach($action['fear_ActionRow'] as $action_row){
									$field_status[$player][$action_row]['debuffs'][] = 'terrify';
									foreach($battle_field[$player][$action_row]['warrior'] as $card_iter => $card_data){
										$card = BattleFieldController::getCardNaturalSetting($card_data['id']);
										$allow_fear = self::checkForSimpleImmune($action['fear_ignoreImmunity'], $card['actions']);

										if(($card['strength'] > 0) && ($allow_fear)){
											if(!empty($groups)){
												foreach($card['group'] as $group_id){
													if(in_array($group_id, $groups)){
														$strength = $card_data['strength'] - $action['fear_strenghtValue'];
														if($strength < 1){
															$strength = 1;
														}
														$battle_field[$player][$action_row]['warrior'][$card_iter]['strength'] = $strength;

														$field_status[$player][$action_row]['warrior'][$card_iter]['debuffs'][] = 'terrify';
														$field_status[$player][$action_row]['warrior'][$card_iter]['strengthModified'] = $strength;

														$field_status[$player][$action_row]['warrior'][$card_iter]['debuffs'] = array_values(array_unique($field_status[$player][$action_row]['warrior'][$card_iter]['debuffs']));
													}
												}
											}else{
												$strength = $card_data['strength'] - $action['fear_strenghtValue'];
												if($strength < 1){
													$strength = 1;
												}
												$battle_field[$player][$action_row]['warrior'][$card_iter]['strength'] = $strength;
												$field_status[$player][$action_row]['warrior'][$card_iter]['strengthModified'] = $strength;

												$field_status[$player][$action_row]['warrior'][$card_iter]['debuffs'][] = 'terrify';
												$field_status[$player][$action_row]['warrior'][$card_iter]['debuffs'] = array_values(array_unique($field_status[$player][$action_row]['warrior'][$card_iter]['debuffs']));
											}
										}
									}
									$field_status[$player][$action_row]['debuffs'] = array_values(array_unique($field_status[$player][$action_row]['debuffs']));
								}
							}
						}
					}
				}
			}
		}*/

		//Применение МЭ "Страшный" к картам
		/*foreach($magic_usage as $player => $magic_data){
			$opponent_player = ($users_data['user']['player'] == $player)? $users_data['opponent']['player']: $users_data['user']['player'];

			foreach($magic_data as $activated_in_round => $magic_id){
				if($activated_in_round == $battle->round_count){
					if($magic_id['allow'] != '0'){
						$magic = json_decode(SiteGameController::getMagicData($magic_id['id']));//Данные о МЭ
						foreach($magic->actions as $action_iter => $action){
							if($action->action == '18'){
								if(!in_array($users_data[$opponent_player]['current_deck'], $action->fear_enemyRace)){
									foreach($action->fear_ActionRow as $action_row_iter => $action_row){
										$field_status[$player][$action_row]['debuffs'] = 'terrify';
										foreach($battle_field[$opponent_player][$action_row]['warrior'] as $card_iter => $card_data){
											$allow_fear = self::checkForSimpleImmune($action->fear_ignoreImmunity, $card_data['card']['actions']);

											if(($card_data['strength'] > 0) && ($allow_fear)){
												$strength = $card_data['strength'] - $action->fear_strenghtValue;
												if($strength < 1){
													$strength = 1;
												}
												$battle_field[$opponent_player][$action_row]['warrior'][$card_iter]['strength'] = $strength;

												$field_status[$player][$action_row]['warrior'][$card_iter]['debuffs'][] = 'terrify';
												$field_status[$player][$action_row]['warrior'][$card_iter]['debuffs'] = array_values(array_unique($field_status[$player][$action_row]['warrior'][$card_iter]['debuffs']));
												$field_status[$player][$action_row]['warrior'][$card_iter]['strengthModified'] = $strength;
											}
										}
										$field_status[$player][$action_row]['debuffs'] = array_values(array_unique($field_status[$player][$action_row]['debuffs']));
									}
								}
							}
						}
					}
				}
			}
		}*/

		//Применение "Боевое братство" к картам
		/*$cards_to_brotherhood = [];
		foreach($actions_array_brotherhood as $player => $cards_array){
			foreach($cards_array as $card_id => $card_data){
				foreach($card_data['actions'] as $action_iter => $action_data){
					if($action_data['caption'] == 'brotherhood'){
						if($action_data['brotherhood_actionToGroupOrSame'] == 0){
							$count_same = 0;
							$mult_same = 1;
							foreach($battle_field[$player] as $rows => $cards){
								foreach($cards['warrior'] as $card){
									if($card_data['id'] == $card['id']){
										$count_same++;
									}
								}
							}
							if($count_same > 0){
								$mult_same = $count_same;
								if($mult_same > $action_data['brotherhood_strenghtMult']){
									$mult_same = $action_data['brotherhood_strenghtMult'];
								}
							}
							foreach($battle_field[$player] as $rows => $cards){
								foreach($cards['warrior'] as $card_iter => $card){
									if($card_data['id'] == $card['id']){
										$battle_field[$player][$rows]['warrior'][$card_iter]['strength'] *= $mult_same;
									}
								}
							}
						}else{
							foreach($battle_field[$player] as $rows => $cards){
								foreach($cards['warrior'] as $card){
									for($i=0; $i<count($card['group']); $i++){
										if(in_array($card['group'][$i], $action_data->brotherhood_actionToGroupOrSame)){
											$cards_to_brotherhood[$player][$card['group'][$i].'_'.$action_data->brotherhood_strenghtMult][] = $card['id'];
										}
									}
								}
							}
						}
					}
				}
			}
		}*/

		/*if( (isset($cards_to_brotherhood)) && (!empty($cards_to_brotherhood)) ){
			foreach($cards_to_brotherhood as $player => $group_data){
				foreach($group_data as $group_ident => $cards_ids){
					$cards_to_brotherhood[$player][$group_ident] = array_unique($cards_to_brotherhood[$player][$group_ident]);
				}
			}

			foreach($cards_to_brotherhood as $player => $group_data){
				foreach($group_data as $group_ident => $cards_ids){
					$group_data = explode('_', $group_ident);
					$count_group = 0;
					$mult_group = 1;
					foreach($battle_field[$player] as $row => $cards){
						foreach($cards['warrior'] as $card){
							if(in_array($card['id'], $cards_ids)){
								$count_group++;
							}
						}
					}
					if($count_group > 0){
						$mult_group = $count_group;
						if($mult_group > $group_data[1]){
							$mult_group = $group_data[1];
						}
					}

					foreach($battle_field[$player] as $row => $cards){
						foreach($cards['warrior'] as $card_iter => $card){
							if(in_array($card['id'], $cards_ids)){
								$battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $mult_group;

								$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'brotherhood';
								$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
								$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
							}
						}
					}
				}
			}
		}*/
		// /Применение "Боевое братство" к картам

		//Применение Воодушевления
		/*foreach($actions_array_inspiration as $player => $row_data){
			foreach($row_data as $row => $card){
				$field_status[$player][$row]['buffs'][] = 'inspiration';
				foreach($card['actions'] as $action_data){
					if($action_data['caption'] == 'inspiration'){
						foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
							$allow_inspiration = true;
							if($action_data['inspiration_ignoreImmunity'] == 0){
								$card_data = BattleFieldController::getCardNaturalSetting($card_data['id']);
								foreach($card_data['actions'] as $i => $card_action){
									if($card_action['caption'] == 'immune'){
										if($card_action['immumity_type'] == 1){
											$allow_inspiration = false;
										}
									}
								}
							}
							if($allow_inspiration){
								$battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $action_data['inspiration_multValue'];

								$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'inspiration';
								$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
								$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
							}
						}
					}
				}
				$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
			}
		}*/

		//Применение МЭ "Воодушевление" к картам
		/*foreach($magic_usage as $player => $magic_data){
			foreach($magic_data as $activated_in_round => $magic_id){
				if($activated_in_round == $battle->round_count){
					if($magic_id['allow'] != '0'){
						$magic = json_decode(SiteGameController::getMagicData($magic_id['id']));//Данные о МЭ
						foreach($magic->actions as $action_iter => $action_data){
							if($action_data->action == '4'){
								foreach($action_data->inspiration_ActionRow as $row_iter => $row){
									$field_status[$player][$row]['buffs'][] = 'inspiration';
									if( (!isset($actions_array_inspiration[$player][$row])) || (empty($actions_array_inspiration[$player][$row])) ){
										foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
											$allow_inspiration = true;
											if($action_data->inspiration_ignoreImmunity == 0){
												foreach($card_data['card']['actions'] as $i => $card_action){
													if($card_action->action == '5'){
														if($card_action->immumity_type == 1){
															$allow_inspiration = false;
														}
													}
												}
											}
											if($battle_field[$player][$row]['special'] != ''){
												foreach($battle_field[$player][$row]['special']['card'] as $i => $card_action){
													if($card_action->action == '4'){
														$allow_inspiration = false;
													}
												}
											}
											if($allow_inspiration){
												$battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $action_data->inspiration_multValue;

												$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'inspiration';
												$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
												$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
											}
										}
									}
									$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
								}
							}
						}
					}
				}
			}
		}*/
		return [
			'battle_field' => $battle_field,
			'field_status' => $field_status,
			'step_status' => $step_status
		];
	}

	public static function getFractionFlag($fraction){
		switch($fraction){
			case 'cursed':		return 'cart-open-flag-cursed.png'; break;
			case 'forest':		return 'cart-open-flag-forest-masters.png'; break;
			case 'highlander':	return 'cart-open-flag-highlanders.png'; break;
			case 'knight':		return 'cart-open-flag-knights.png'; break;
			case 'monsters':	return 'cart-open-flag-monsters.png'; break;
			case 'neutrall':	return 'cart-open-flag-neutral.png'; break;
			case 'undead':		return 'cart-open-flag-undead.png'; break;
			default: return '';
		}
	}

	public static function cardData($id){
		if(strlen($id) > 11){
			$id = Crypt::decrypt($id);
		}

		$card = \DB::table('tbl_cards')
			->select('id','title','slug','card_type','card_race','is_leader','card_strong','card_groups','img_url','allowed_rows','card_actions')
			->find($id);
		if($card == false) return false;

		$allowed_row_images = SiteFunctionsController::createActionRowsArray($card->allowed_rows);
		$action_images = SiteFunctionsController::createActionsArray(unserialize($card->card_actions));

		$actions = self::processActions(unserialize($card->card_actions));

		$fraction = ($card->card_type == 'race')? $card->card_race: $card->card_type;

		$fraction_img = self::getFractionFlag($fraction);
		return [
			'id'			=> Crypt::encrypt($card->id),
			'title'			=> $card->title,
			'caption'		=> $card->slug.'_'.($card->id+13),
			'img_url'		=> $card->img_url,
			'fraction'		=> $fraction,
			'fraction_img'	=> $fraction_img,
			'is_leader'		=> $card->is_leader,
			'strength'		=> $card->card_strong,
			'group'			=> unserialize($card->card_groups),
			'allowed_rows'	=> unserialize($card->allowed_rows),
			'allowed_row_images'=> $allowed_row_images,
			'actions'		=> $actions,
			'action_images'	=> $action_images,
		];
	}

	public static function getCardNaturalSetting($card_id){
		if(strlen($card_id) > 11){
			$card_id = Crypt::decrypt($card_id);
		}
		$card_data = \DB::table('tbl_cards')
			->select('id','card_strong','title','slug','card_actions','card_type','card_race','allowed_rows','card_groups')
			->find($card_id);
		$actions = self::processActions(unserialize($card_data->card_actions));

		$fraction = ($card_data->card_type == 'race')? $card_data->card_race: $card_data->card_type;
		return [
			'id'			=> $card_data->id,
			'title'			=> $card_data->title,
			'caption'		=> $card_data->slug.'_'.($card_id+13),
			'strength'		=> $card_data->card_strong,
			'allowed_rows'	=> unserialize($card_data->allowed_rows),
			'actions'		=> $actions,
			'group'			=> unserialize($card_data->card_groups),
			'fraction'		=> $fraction
		];
	}

	public static function processActions($actions){
		$result = [];
		foreach($actions as $action){
			$action = get_object_vars($action);
			$action_type = \DB::table('tbl_actions')->select('type')->where('id','=',$action['action'])->first();
			$action['caption'] = $action_type->type;
			$result[] = $action;
		}
		return $result;
	}

	public static function cardView($card, $strength_override = -1, $quantity = 0){
		$has_immune = 0;
		$has_full_immune = 0;
		if(!isset($card['actions']))dd($card);
		foreach($card['actions'] as $action){
			if($action['caption'] == 'immune'){
				$has_immune = 1;
				$has_full_immune = $action['immumity_type'];
			}
		}

		$special_class = '';
		switch($card['fraction']){
			case 'knight':		$race_class = 'knight-race'; break;
			case 'highlander':	$race_class = 'highlander-race'; break;
			case 'monsters':	$race_class = 'monsters-race'; break;
			case 'undead':		$race_class = 'undead-race'; break;
			case 'cursed':		$race_class = 'cursed-race'; break;
			case 'forest':		$race_class = 'forest-race'; break;
			case 'neutrall':	$race_class = 'neutrall-race'; break;
			case 'special':		$special_class = 'special-type'; $race_class = ''; break;
			default: $race_class = '';
		}

		$allowed_row_images = '';
		if($card['fraction'] != 'special') {
			if (!empty($card['fraction'])) {
				foreach ($card['allowed_row_images'] as $i => $dist) {
					$allowed_row_images .= '
					<img src="' . \URL::asset($dist['image']) . '" alt="">
					<span class="card-action-description">' . $dist['title'] . '</span>';
				}
			}
		}
		$quantity_tag = ($quantity > 1)? '<div class="count">'.$quantity.'</div>': '';

		$leader_class = ($card['is_leader'] == 1 )? 'leader-type': '';
		$leader_tag = ($card['is_leader'] == 1)? '<div class="leader-flag"><span class="card-action-description">Карта Лидера</span></div>': '';

		$action_images = '';
		if(!empty($card['action_images'])){
			foreach($card['action_images'] as $i => $act){
				$action_images .= '
				<span class="card-action">
					 <img src="'.\URL::asset($act['img']).'" alt="">
					<span class="card-action-description">'.$act['title'].'</span>
				</span>';
			}
		}

		$strength = ($strength_override >= 0)? $strength_override: $card['strength'];

		$cart_strength_tag = ($race_class != '')
			? '<div class="label-power-card">
				<span class="label-power-card-wrap">
					<span class="buff-debuff-value"></span>
					<span class="card-current-value">'.$strength.'</span>
				</span>
				<span class="card-action-description">Сила карты</span>
			</div>'
			: '';
		return '
		<li class="content-card-item disable-select show" data-relative="'.$card['fraction'].'" data-immune="'.$has_immune.'" data-full-immune="'.$has_full_immune.'" data-cardid="'.$card['id'].'" data-slug="'.$card['caption'].'" data-relative="'.$card['fraction'].'">
			'.$quantity_tag.'
			<div class="content-card-item-main '.$race_class.' '.$leader_class.' '.$special_class.'" style="background-image: url('.\URL::asset('/img/card_images/'.$card['img_url']).')" data-leader="'.$card['is_leader'].'">
				<div class="card-load-info card-popup">
					<div class="info-img">
						<img class="ignore" src="'.\URL::asset('/images/info-icon.png').'" alt="">
						<span class="card-action-description">Инфо о карте</span>
					</div>
					'.$leader_tag.$cart_strength_tag.'
					<div class="hovered-items">
						<div class="card-game-status">
							<div class="card-game-status-role">'.$allowed_row_images.'</div>
							<div class="card-game-status-wrap">'.$action_images.'</div>
						</div>
						<div class="card-name-property">
							<p>'.$card['title'].'</p>
						</div>
					</div>
				</div>
			</div>
		</li>';
	}

	public static function cardSimpleView($id, $strength_override = -1, $quantity = 0){
		$card = self::cardData($id);
		return self::cardView($card, $strength_override, $quantity);
	}

	public static function sortingDeck(&$deck){
		usort($deck, function($a, $b){
			$r = ($b['strength'] - $a['strength']);
			if($r !== 0) return $r;
			return strnatcasecmp($a['title'], $b['title']);
		});
	}

	public static function recontentDecks($deck){
		foreach($deck as $i => $card){
			$deck[$i] = BattleFieldController::getCardNaturalSetting($card);
			$deck[$i]['id'] = $card;
		}
		return $deck;
	}

	public static function checkForSimpleImmune($ignoreImmunity, $card_actions){
		$allow_to_use = true;
		if($ignoreImmunity == 0){
			foreach($card_actions as $action_iter => $action){
				if($action['caption'] == 'immune'){
					$allow_to_use = false;
				}
			}
		}
		return $allow_to_use;
	}

	public static function checkForFullImmune($ignoreImmunity, $card_actions){
		$allow_to_use = true;
		if($ignoreImmunity == 0){
			foreach($card_actions as $i => $action){
				if($action['caption'] == 'immune'){
					if($action['immumity_type'] == 1){
						$allow_to_use = false;
					}
				}
			}
		}
		return $allow_to_use;
	}

	public static function getBuffClass($buff, $wrap = ''){
		switch($buff){
			case 'terrify':
				$class = 'terrify-debuff';
				if(!empty($wrap)){
					$class .= '-'.$wrap.' debuff';
				}
			break;
			case 'support':
				$class = 'support-buff';
				if(!empty($wrap)){
					$class .= '-'.$wrap.' buff';
				}
			break;
			case 'inspiration':
				$class = 'inspiration-buff';
				if(!empty($wrap)){
					$class .= '-'.$wrap.' buff';
				}
			break;
			default: $class = '';
		}
		return $class;
	}
}