<?php
namespace App\Http\Controllers\Site;

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
		$cards_strength = [];
		$fury_cards = [];
		$played_card_actions = [];
		if( isset($step_status['played_magic']) && !empty($step_status['played_magic']) ){
			foreach($step_status['played_magic'] as $player => $magic_data){
				foreach($step_status['played_magic'][$player]['actions'] as $action){
					$played_card_actions[] = $action['caption'];
				}
			}
		}
		if( isset($step_status['played_card']['card']) && !empty($step_status['played_card']['card']) ){
			foreach($step_status['played_card']['card']['actions'] as $action){
				$played_card_actions[] = $action['caption'];
			}
			$step_status['played_card']['card']['buffs'] = [];
			$step_status['played_card']['card']['debuffs'] = [];
			if( (!empty($step_status['added_cards'])) && (in_array('master', $played_card_actions) || in_array('obscure', $played_card_actions)) ){
				foreach($step_status['added_cards'] as $field => $rows){
					foreach($rows as $row => $row_data){
						foreach($row_data as $card_iter => $card_data){
							foreach($card_data['actions'] as $action){
								switch($action['caption']){
									case 'support'://Поддержка
										if(!empty($step_status['played_card']['card'])){
											foreach($action['support_ActionRow'] as $inner_row){
												$step_status['actions']['appear'][$field][$inner_row][] = $action['caption'];
											}
										}
									break;

									case 'terrify':
										if($action['fear_actionTeamate'] == 1){
											$players = ['p1','p2'];
										}else{
											$players = ($step_status['played_card']['move_to']['player'] == 'p1')? ['p2']: ['p1'];
										}
										foreach($players as $player){
											foreach($action['fear_ActionRow'] as $inner_row){
												$step_status['actions']['appear'][$player][$inner_row][] = $action['caption'];
											}
										}
									break;
								}
							}
						}
					}
				}
			}
		}
		$played_card_actions = array_values(array_unique($played_card_actions));

		foreach($battle_field as $field => $rows){
			if($field != 'mid'){
				foreach($rows as $row => $cards){
					foreach($cards['warrior'] as $card_iter => $card_data){
						$card = self::cardData($card_data['id']);
						$cards_strength[$field][$row][$card_iter] = $card['strength'];
						$card['login'] = $card_data['login'];
						$field_status[$field][$row]['warrior'][$card_iter]['card'] = $card;
						$field_status[$field][$row]['warrior'][$card_iter]['buffs']= [];
						$field_status[$field][$row]['warrior'][$card_iter]['debuffs']= [];
						$field_status[$field][$row]['warrior'][$card_iter]['strengthModified'] = $card['strength'];
						foreach($card['actions'] as $action){
							switch($action['caption']){
								case 'brotherhood':		$actions_array_brotherhood[$field][$card_data['id']] = $card; break;
								case 'inspiration':		$actions_array_inspiration[$field][$row] = $card; break;
								case 'fury':			$actions_array_fury[$field.'/'.$row.'/'.$card_iter] = $card; break;
								case 'support':			$actions_array_support[$field.'/'.$row.'/'.$card_iter] = $card; break;
								case 'terrify':			$actions_array_fear[$field][uniqid()] = $card; break;
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
						if($action['caption'] == 'terrify'){
							if(!isset($actions_array_fear['mid'][$card_data['id']])){
								$actions_array_fear['mid'][$card_data['id']] = $card;
							}
						}
					}
				}
			}
		}

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
								$enemy_card = self::getCardNaturalSetting($enemy_card_data['id']);
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
					$card_destination = explode('/',$card_id);
					if(($allow_fury_by_row) || ($allow_fury_by_race) || ($allow_fury_by_magic) || ($allow_fury_by_group)){
						$strength = $battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strength'] + $action['fury_strenghtVal'];

						if($action['fury_strenghtVal'] >= 0){
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['buffs'][]= 'fury';
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['buffs'] = array_values(array_unique($field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['buffs']));
							if(
								(!isset($battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['fury_modified'])) ||
								($battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['fury_modified'] != 1)
							){
								if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
									$fury_cards[$card_destination[0]][$card_destination[1]][$card_destination[2]] = [
										'card'		=> $card_data['caption'],
										'strength'	=> $battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strength'],
										'strModif'	=> $strength,
										'operation'	=> '+',
									];
								}
							}
						}

						if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
							if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
								$step_status['played_card']['card']['buffs'][] = 'fury';
							}
						}

						$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strength'] = $strength;
						$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strengthModified'] = $strength;
						$cards_strength[$card_destination[0]][$card_destination[1]][$card_destination[2]] = $strength;
						$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['fury_modified'] = 1;
						if( (isset($step_status['added_cards'])) && (!empty($step_status['added_cards'])) && (!in_array('spy', $played_card_actions))){
							if(isset($step_status['added_cards'][$card_destination[0]][$card_destination[1]])){
								foreach($step_status['added_cards'][$card_destination[0]][$card_destination[1]] as $i => $added_card){
									if(Crypt::decrypt($added_card['id']) == $battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['id']){
										$step_status['added_cards'][$card_destination[0]][$card_destination[1]][$i]['strength'] = $battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strength'];
									}
								}
							}
						}
					}else{
						if((!$allow_fury_by_row) || (!$allow_fury_by_race) || (!$allow_fury_by_magic) || (!$allow_fury_by_group)){
							$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strength'] = $card_data['strength'];
							$field_status[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['strengthModified'] = $card_data['strength'];
							$cards_strength[$card_destination[0]][$card_destination[1]][$card_destination[2]] = $card_data['strength'];
							$battle_field[$card_destination[0]][$card_destination[1]]['warrior'][$card_destination[2]]['fury_modified'] = 0;
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
							$card = self::getCardNaturalSetting($card_data['id']);
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

											if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
												if( (in_array('support', $played_card_actions)) || (in_array('master', $played_card_actions)) || (in_array('obscure', $played_card_actions))){
													$step_status['actions']['cards'][$player][$row][$card_iter] = [
														'card'		=> $card['caption'],
														'strength'	=> $battle_field[$player][$row]['warrior'][$card_iter]['strength'],
														'strModif'	=> $strength,
														'operation'	=> '+'
													];
												}

												if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
													$step_status['played_card']['strength'] = $strength;
													$step_status['played_card']['card']['buffs'][] = 'support';
												}
											}
											if(isset($fury_cards[$player][$row][$card_iter])){
												$fury_cards[$player][$row][$card_iter]['strength'] = $fury_cards[$player][$row][$card_iter]['strModif'];
												$fury_cards[$player][$row][$card_iter]['strModif'] = $strength;
											}

											$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $strength;
											$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $strength;

											$cards_strength[$player][$row][$card_iter] = $strength;

											if(isset($step_status['added_cards'][$player][$row]) && (!in_array('spy', $played_card_actions))){
												foreach($step_status['added_cards'][$player][$row] as $i => $added_card){
													if(Crypt::decrypt($added_card['id']) == $battle_field[$player][$row]['warrior'][$card_iter]['id']){
														$step_status['added_cards'][$player][$row][$i]['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
													}
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

									if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
										if( (in_array('support', $played_card_actions)) || (in_array('master', $played_card_actions)) || (in_array('obscure', $played_card_actions))){
											$step_status['actions']['cards'][$player][$row][$card_iter] = [
												'card'		=> $card['caption'],
												'strength'	=> $battle_field[$player][$row]['warrior'][$card_iter]['strength'],
												'strModif'	=> $strength,
												'operation'	=> '+'
											];
										}

										if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
											$step_status['played_card']['strength'] = $strength;
											$step_status['played_card']['card']['buffs'][] = 'support';
										}
									}
									if(isset($fury_cards[$player][$row][$card_iter])){
										$fury_cards[$player][$row][$card_iter]['strength'] = $fury_cards[$player][$row][$card_iter]['strModif'];
										$fury_cards[$player][$row][$card_iter]['strModif'] = $strength;
									}

									$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $strength;
									$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $strength;

									$cards_strength[$player][$row][$card_iter] = $strength;

									if(isset($step_status['added_cards'][$player][$row]) && (!in_array('spy', $played_card_actions))){
										foreach($step_status['added_cards'][$player][$row] as $i => $added_card){
											if(Crypt::decrypt($added_card['id']) == $battle_field[$player][$row]['warrior'][$card_iter]['id']){
												$step_status['added_cards'][$player][$row][$i]['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
											}
										}
									}
								}
								$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
							}
						}
						$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
					}
				}
			}
		}

		//Применение МЭ "Поддержка" к картам
		if(!empty($magic_usage['p1']) || !empty($magic_usage['p2'])){
			foreach($magic_usage as $player => $magic_data){
				foreach($magic_data as $activated_in_round => $magic_id){
					if($activated_in_round == $battle->round_count){
						if($magic_id['allow'] != '0'){
							$magic = self::magicData($magic_id['id']);//Данные о МЭ
							foreach($magic['actions'] as $action){
								if($action['caption'] == 'support'){
									foreach($action['support_ActionRow'] as $row_iter => $row){//Ряды действия МЭ
										$field_status[$player][$row]['buffs'][] = 'support';
										//Применение МЭ к картам
										foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
											$card = self::cardData($card_data['id']);
											//Если у карты есть полный иммунитет
											$allow_magic = self::checkForFullImmune($action['support_ignoreImmunity'], $card['actions']);
											if($allow_magic){
												$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'support';

												$strength = $card_data['strength'] + $action['support_strenghtValue'];

												if(
													(isset($step_status['played_magic'])) && (!empty($step_status['played_magic'])) ||
													(isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card']))
												){
													$step_status['actions']['cards'][$player][$row][$card_iter] = [
														'card'		=> $card['caption'],
														'strength'	=> $card_data['strength'],
														'strModif'	=> $strength,
														'operation'	=> '+'
													];
												}

												if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
													if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
														$step_status['played_card']['strength'] = $strength;
														$step_status['played_card']['card']['buffs'][] = 'support';
													}
												}

												if(isset($fury_cards[$player][$row][$card_iter])){
													$fury_cards[$player][$row][$card_iter]['strength'] = $fury_cards[$player][$row][$card_iter]['strModif'];
													$fury_cards[$player][$row][$card_iter]['strModif'] = $strength;
												}

												$battle_field[$player][$row]['warrior'][$card_iter]['strength'] = $strength;
												$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $strength;

												$cards_strength[$player][$row][$card_iter] = $strength;

												if(isset($step_status['added_cards'][$player][$row]) && (!in_array('spy', $played_card_actions))){
													foreach($step_status['added_cards'][$player][$row] as $i => $added_card){
														if(Crypt::decrypt($added_card['id']) == $battle_field[$player][$row]['warrior'][$card_iter]['id']){
															$step_status['added_cards'][$player][$row][$i]['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
														}
													}
												}
												$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
											}
										}
										$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
									}
								}
							}
						}
					}
				}
			}
		}
		// /Применение МЭ "Поддержка" к картам

		//Применение действия "Страшный" к картам
		foreach($actions_array_fear as $source => $cards){
			foreach($cards as $card_id => $card_data){
				foreach($card_data['actions'] as $action){
					if($action['caption'] == 'terrify'){
						//Карта действует на всех или только на противника
						if($action['fear_actionTeamate'] == 1){
							$players = ['opponent', 'user'];
						}else{
							$players = ($card_data['login'] == $users_data['user']['login'])
								? ['opponent']
								: ['user'];
						}

						//Карта действует на группу
						$groups = (isset($action['fear_actionToGroupOrAll']))? $action['fear_actionToGroupOrAll']: [];
						foreach($players as $player){
							if(!in_array($users_data[$player]['current_deck'], $action['fear_enemyRace'])){
								foreach($action['fear_ActionRow'] as $action_row){
									$field = $users_data[$player]['player'];
									$field_status[$field][$action_row]['debuffs'][] = 'terrify';
									foreach($battle_field[$field][$action_row]['warrior'] as $card_iter => $card_data){
										$card = self::getCardNaturalSetting($card_data['id']);
										$allow_fear = self::checkForSimpleImmune($action['fear_ignoreImmunity'], $card['actions']);

										if($allow_fear){
											if(!empty($groups)){
												foreach($card['group'] as $group_id){
													if(in_array($group_id, $groups)){
														$strength = $card_data['strength'] - $action['fear_strenghtValue'];
														if($strength < 1){
															$strength = 1;
															if($card['strength'] == 0){
																$strength = 0;
															}
														}

														if(
															(isset($step_status['played_card']['card']) && !empty($step_status['played_card']['card'])) ||
															(!empty($step_status['played_magic']))
														){
															if( in_array('terrify', $played_card_actions) || in_array('support', $played_card_actions) || in_array('master', $played_card_actions) ){
																$step_status['actions']['cards'][$field][$action_row][$card_iter] = [
																	'card'		=> $card['caption'],
																	'strength'	=> $battle_field[$field][$action_row]['warrior'][$card_iter]['strength'],
																	'strModif'	=> $strength,
																	'operation'	=> '-'
																];
															}

															if(empty($step_status['played_magic'])){
																if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
																	$step_status['played_card']['strength'] = $strength;
																	$step_status['played_card']['card']['debuffs'][] = 'terrify';
																}
															}
														}
														if(isset($fury_cards[$field][$action_row][$card_iter])){
															$fury_cards[$field][$action_row][$card_iter]['strength'] = $fury_cards[$field][$action_row][$card_iter]['strModif'];
															$fury_cards[$field][$action_row][$card_iter]['strModif'] = $strength;
														}

														$battle_field[$field][$action_row]['warrior'][$card_iter]['strength'] = $strength;
														$field_status[$field][$action_row]['warrior'][$card_iter]['strengthModified'] = $strength;

														$cards_strength[$field][$action_row][$card_iter] = $strength;

														if(isset($step_status['added_cards'][$field][$action_row]) && (!in_array('spy', $played_card_actions))){
															foreach($step_status['added_cards'][$field][$action_row] as $i => $added_card){
																if(Crypt::decrypt($added_card['id']) == $battle_field[$field][$action_row]['warrior'][$card_iter]['id']){
																	$step_status['added_cards'][$field][$action_row][$i]['strength'] = $battle_field[$field][$action_row]['warrior'][$card_iter]['strength'];
																}
															}
														}

														$field_status[$field][$action_row]['warrior'][$card_iter]['debuffs'][] = 'terrify';
														$field_status[$field][$action_row]['warrior'][$card_iter]['debuffs'] = array_values(array_unique($field_status[$field][$action_row]['warrior'][$card_iter]['debuffs']));
													}
												}
											}else{
												$strength = $card_data['strength'] - $action['fear_strenghtValue'];
												if($strength < 1){
													$strength = 1;
													if($card['strength'] == 0){
														$strength = 0;
													}
												}

												if(
													(isset($step_status['played_card']['card']) && !empty($step_status['played_card']['card'])) ||
													(!empty($step_status['played_magic']))
												){
													if( in_array('terrify', $played_card_actions) || in_array('support', $played_card_actions) || in_array('master', $played_card_actions) ){
														$step_status['actions']['cards'][$field][$action_row][$card_iter] = [
															'card'		=> $card['caption'],
															'strength'	=> $battle_field[$field][$action_row]['warrior'][$card_iter]['strength'],
															'strModif'	=> $strength,
															'operation'	=> '-'
														];
													}

													if(empty($step_status['played_magic'])){
														if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
															$step_status['played_card']['strength'] = $strength;
															$step_status['played_card']['card']['debuffs'][] = 'terrify';
														}
													}
												}

												if(isset($fury_cards[$field][$action_row][$card_iter])){
													$fury_cards[$field][$action_row][$card_iter]['strength'] = $fury_cards[$field][$action_row][$card_iter]['strModif'];
													$fury_cards[$field][$action_row][$card_iter]['strModif'] = $strength;
												}

												$battle_field[$field][$action_row]['warrior'][$card_iter]['strength'] = $strength;
												$field_status[$field][$action_row]['warrior'][$card_iter]['strengthModified'] = $strength;

												$cards_strength[$field][$action_row][$card_iter] = $strength;

												if(isset($step_status['added_cards'][$field][$action_row]) && (!in_array('spy', $played_card_actions))){
													foreach($step_status['added_cards'][$field][$action_row] as $i => $added_card){
														if(Crypt::decrypt($added_card['id']) == $battle_field[$field][$action_row]['warrior'][$card_iter]['id']){
															$step_status['added_cards'][$field][$action_row][$i]['strength'] = $battle_field[$field][$action_row]['warrior'][$card_iter]['strength'];
														}
													}
												}

												$field_status[$field][$action_row]['warrior'][$card_iter]['debuffs'][] = 'terrify';
												$field_status[$field][$action_row]['warrior'][$card_iter]['debuffs'] = array_values(array_unique($field_status[$field][$action_row]['warrior'][$card_iter]['debuffs']));
											}
										}
									}
									$field_status[$field][$action_row]['debuffs'] = array_values(array_unique($field_status[$field][$action_row]['debuffs']));
								}
							}
						}
					}
				}
			}
		}

		//Применение МЭ "Страшный" к картам
		if(!empty($magic_usage['p1']) || !empty($magic_usage['p2'])){
			foreach($magic_usage as $player => $magic_data){
				foreach($magic_data as $activated_in_round => $magic_id){
					if($activated_in_round == $battle->round_count){
						if($magic_id['allow'] != '0'){
							$magic = self::magicData($magic_id['id']);//Данные о МЭ

							foreach($magic['actions'] as $action_iter => $action){
								if($action['caption'] == 'terrify'){
									$opponent_player =  ($player == $users_data['user']['player'])? 'opponent': 'user';
									if($action['fear_actionTeamate'] == 0){
										$fields = ($player == $users_data['user']['player'])? [$users_data['opponent']['player']]: [$users_data['user']['player']];
									}else{
										$fields = ['p1','p2'];
									}

									foreach($fields as $field){
										if(!in_array($users_data[$opponent_player]['current_deck'], $action['fear_enemyRace'])){
											foreach($action['fear_ActionRow'] as $action_row){
												$field_status[$field][$action_row]['debuffs'][] = 'terrify';
												foreach($battle_field[$field][$action_row]['warrior'] as $card_iter => $card_data){
													$card = self::cardData($card_data['id']);
													$allow_fear = self::checkForSimpleImmune($action['fear_ignoreImmunity'], $card['actions']);

													if($allow_fear){
														$strength = $card_data['strength'] - $action['fear_strenghtValue'];
														if($strength < 1){
															$strength = 1;
															if($card['strength'] == 0){
																$strength = 0;
															}
														}

														if( (isset($step_status['played_magic'])) && (!empty($step_status['played_magic'])) ){
															$step_status['actions']['cards'][$field][$action_row][$card_iter] = [
																'card'		=> $card['caption'],
																'strength'	=> $card_data['strength'],
																'strModif'	=> $strength,
																'operation'	=> '-'
															];
														}

														if(
															(isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ||
															(!empty($step_status['played_magic']))
														){
															if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
																$step_status['played_card']['strength'] = $strength;
																$step_status['played_card']['card']['debuffs'][] = 'terrify';
															}
														}

														if(isset($fury_cards[$field][$action_row][$card_iter])){
															$fury_cards[$field][$action_row][$card_iter]['strength'] = $fury_cards[$field][$action_row][$card_iter]['strModif'];
															$fury_cards[$field][$action_row][$card_iter]['strModif'] = $strength;
														}

														$battle_field[$field][$action_row]['warrior'][$card_iter]['strength'] = $strength;
														$field_status[$field][$action_row]['warrior'][$card_iter]['strengthModified'] = $strength;

														$cards_strength[$field][$action_row][$card_iter] = $strength;

														if(isset($step_status['added_cards'][$field][$action_row]) && (!in_array('spy', $played_card_actions))){
															foreach($step_status['added_cards'][$field][$action_row] as $i => $added_card){
																if(Crypt::decrypt($added_card['id']) == $battle_field[$field][$action_row]['warrior'][$card_iter]['id']){
																	$step_status['added_cards'][$field][$action_row][$i]['strength'] = $battle_field[$field][$action_row]['warrior'][$card_iter]['strength'];
																}
															}
														}

														$field_status[$field][$action_row]['warrior'][$card_iter]['debuffs'][] = 'terrify';
														$field_status[$field][$action_row]['warrior'][$card_iter]['debuffs'] = array_values(array_unique($field_status[$field][$action_row]['warrior'][$card_iter]['debuffs']));
													}
												}
												$field_status[$field][$action_row]['debuffs'] = array_values(array_unique($field_status[$field][$action_row]['debuffs']));
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		//Применение "Боевое братство" к картам
		$cards_to_brotherhood = [];
		foreach($actions_array_brotherhood as $player => $cards_array){
			foreach($cards_array as $card_id => $card_data){
				foreach($card_data['actions'] as $action_data){
					if($action_data['caption'] == 'brotherhood'){
						if($action_data['brotherhood_actionToGroupOrSame'] == 0){
							$count_same = 0;
							$mult_same = 1;
							foreach($battle_field[$player] as $rows => $cards){
								foreach($cards['warrior'] as $card){
									if($card['id'] == Crypt::decrypt($card_data['id'])){
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
									if(Crypt::decrypt($card_data['id']) == $card['id']){
										$support_action = false;

										if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ){
											if(in_array('brotherhood', $played_card_actions)){
												$support_action = true;
											}
											if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
												$step_status['played_card']['card']['buffs'][] = 'brotherhood';
											}
										}

										if($support_action){
											if(Crypt::decrypt($step_status['played_card']['card']['id']) == $card['id']){
												if($count_same > 1){
													$step_status['actions']['cards'][$player][$rows][$card_iter] = [
														'card'		=> $card['caption'],
														'strength'	=> $battle_field[$player][$rows]['warrior'][$card_iter]['strength'],
														'strModif'	=> $battle_field[$player][$rows]['warrior'][$card_iter]['strength'] * $mult_same,
														'operation'	=> 'x'.$mult_same
													];
												}
											}
										}else{
											if( ($count_same > 1) && (!in_array('fury', $played_card_actions)) ){
												$step_status['actions']['cards'][$player][$rows][$card_iter] = [
													'card' => $card['caption'],
													'strength' => $battle_field[$player][$rows]['warrior'][$card_iter]['strength'],
													'strModif' => $battle_field[$player][$rows]['warrior'][$card_iter]['strength'] * $mult_same,
													'operation' => 'x'.$mult_same
												];
											}
										}

										$field_status[$player][$rows]['warrior'][$card_iter]['buffs'][] = 'brotherhood';
										$battle_field[$player][$rows]['warrior'][$card_iter]['strength'] *= $mult_same;
										$field_status[$player][$rows]['warrior'][$card_iter]['strengthModified'] *= $mult_same;

										if(isset($step_status['added_cards'][$player][$rows]) && (!in_array('spy', $played_card_actions))){
											foreach($step_status['added_cards'][$player][$rows] as $i => $added_card){
												if(Crypt::decrypt($added_card['id']) == $battle_field[$player][$rows]['warrior'][$card_iter]['id']){
													$step_status['added_cards'][$player][$rows][$i]['strength'] = $battle_field[$player][$rows]['warrior'][$card_iter]['strength'];
												}
											}
										}

										$cards_strength[$player][$rows][$card_iter] = $battle_field[$player][$rows]['warrior'][$card_iter]['strength'];
									}
								}
							}
						}else{
							foreach($battle_field[$player] as $rows => $cards){
								foreach($cards['warrior'] as $card){
									$card = self::getCardNaturalSetting($card['id']);
									for($i=0; $i<count($card['group']); $i++){
										if(in_array($card['group'][$i], $action_data['brotherhood_actionToGroupOrSame'])){
											$cards_to_brotherhood[$player][$card['group'][$i].'_'.$action_data['brotherhood_strenghtMult']][] = $card['id'];
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if( (isset($cards_to_brotherhood)) && (!empty($cards_to_brotherhood)) ){
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
								$card = self::getCardNaturalSetting($card['id']);

								if( (isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) && (in_array($group_data[0], $card['group'])) ){
									if(in_array($group_data[0], $step_status['played_card']['card']['group'])) {
										if($count_group > 1) {
											if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
												$step_status['played_card']['card']['buffs'][] = 'brotherhood';
											}
											$step_status['actions']['cards'][$player][$row][$card_iter] = [
												'card' => $card['caption'],
												'strength' => $battle_field[$player][$row]['warrior'][$card_iter]['strength'],
												'strModif' => $battle_field[$player][$row]['warrior'][$card_iter]['strength'] * $mult_group,
												'operation' => 'x' . $mult_group
											];
										}
									}
								}

								$battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $mult_group;

								$cards_strength[$player][$row][$card_iter] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];

								$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'brotherhood';
								$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
								$field_status[$player][$row]['warrior'][$card_iter]['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
							}
						}
					}
				}
			}
		}
		// /Применение "Боевое братство" к картам

		//Применение Воодушевления
		foreach($actions_array_inspiration as $player => $row_data){
			foreach($row_data as $row => $card){
				$field_status[$player][$row]['buffs'][] = 'inspiration';
				foreach($card['actions'] as $action_data){
					if($action_data['caption'] == 'inspiration'){
						foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
							$card_data = self::getCardNaturalSetting($card_data['id']);
							$allow_inspiration = self::checkForFullImmune($action_data['inspiration_ignoreImmunity'], $card_data['actions']);

							if($allow_inspiration){
								if(
									(isset($step_status['played_card']['card']) && !empty($step_status['played_card']['card'])) ||
									(!empty($step_status['played_magic']))
								){
									if(!in_array('killer', $played_card_actions)){
										$step_status['actions']['cards'][$player][$row][$card_iter] = [
											'card'		=> $card_data['caption'],
											'strength'	=> $battle_field[$player][$row]['warrior'][$card_iter]['strength'],
											'strModif'	=> $battle_field[$player][$row]['warrior'][$card_iter]['strength'] * $action_data['inspiration_multValue'],
											'operation'	=> 'x'.$action_data['inspiration_multValue']
										];
									}

									if(empty($step_status['played_magic'])){
										if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
											$step_status['played_card']['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'] * $action_data['inspiration_multValue'];
											$step_status['played_card']['card']['buffs'][] = 'inspiration';
										}
									}
									
								}
								if(isset($fury_cards[$player][$row][$card_iter])){
									$fury_cards[$player][$row][$card_iter]['strength'] = $fury_cards[$player][$row][$card_iter]['strModif'];
									$fury_cards[$player][$row][$card_iter]['strModif'] = $strength;
								}

								$battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $action_data['inspiration_multValue'];
								$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'inspiration';
								$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
								$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
								$cards_strength[$player][$row][$card_iter] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];

								if(isset($step_status['added_cards'][$player][$row]) && (!in_array('spy', $played_card_actions))){
									foreach($step_status['added_cards'][$player][$row] as $i => $added_card){
										if(Crypt::decrypt($added_card['id']) == $battle_field[$player][$row]['warrior'][$card_iter]['id']){
											$step_status['added_cards'][$player][$row][$i]['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
										}
									}
								}
							}
						}
					}
				}
				$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
			}
		}

		//Применение МЭ "Воодушевление" к картам
		if(!empty($magic_usage['p1']) || !empty($magic_usage['p2'])){
			foreach($magic_usage as $player => $magic_data){
				foreach($magic_data as $activated_in_round => $magic_id){
					if($activated_in_round == $battle->round_count){
						if($magic_id['allow'] != '0'){
							$magic = self::magicData($magic_id['id']);//Данные о МЭ
							foreach($magic['actions'] as $action_iter => $action){
								if($action['caption'] == 'inspiration'){
									foreach($action['inspiration_ActionRow'] as $row_iter => $row){
										$field_status[$player][$row]['buffs'][] = 'inspiration';
										if( (!isset($actions_array_inspiration[$player][$row])) || (empty($actions_array_inspiration[$player][$row])) ){
											foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
												$card = self::cardData($card_data['id']);

												$allow_inspiration = self::checkForFullImmune($action['inspiration_ignoreImmunity'], $card['actions']);

												if($allow_inspiration){
													if( (isset($step_status['played_magic'])) && (!empty($step_status['played_magic'])) ){
														$step_status['actions']['cards'][$player][$row][$card_iter] = [
															'card'		=> $card_data['caption'],
															'strength'	=> $card_data['strength'],
															'strModif'	=> $card_data['strength'] * $action['inspiration_multValue'],
															'operation'	=> 'x'.$action['inspiration_multValue']
														];
													}

													if(
														(isset($step_status['played_card']['card'])) && (!empty($step_status['played_card']['card'])) ||
														(!empty($step_status['played_magic']))
													){
														if($card_data['id'] == Crypt::decrypt($step_status['played_card']['card']['id'])){
															$step_status['played_card']['strength'] = $card_data['strength'] * $action['inspiration_multValue'];
															$step_status['played_card']['card']['buffs'][] = 'inspiration';
														}
													}

													if(isset($fury_cards[$player][$row][$card_iter])){
														$fury_cards[$player][$row][$card_iter]['strength'] = $fury_cards[$player][$row][$card_iter]['strModif'];
														$fury_cards[$player][$row][$card_iter]['strModif'] = $strength;
													}

													$battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $action['inspiration_multValue'];
													$field_status[$player][$row]['warrior'][$card_iter]['buffs'][] = 'inspiration';
													$field_status[$player][$row]['warrior'][$card_iter]['buffs'] = array_values(array_unique($field_status[$player][$row]['warrior'][$card_iter]['buffs']));
													$field_status[$player][$row]['warrior'][$card_iter]['strengthModified'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
													$cards_strength[$player][$row][$card_iter] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];


													if(isset($step_status['added_cards'][$player][$row]) && (!in_array('spy', $played_card_actions))){
														foreach($step_status['added_cards'][$player][$row] as $i => $added_card){
															if(Crypt::decrypt($added_card['id']) == $battle_field[$player][$row]['warrior'][$card_iter]['id']){
																$step_status['added_cards'][$player][$row][$i]['strength'] = $battle_field[$player][$row]['warrior'][$card_iter]['strength'];
															}
														}
													}
												}
											}
										}
										$field_status[$player][$row]['buffs'] = array_values(array_unique($field_status[$player][$row]['buffs']));
									}
								}
							}
						}else{
							if(isset($step_status['actions'])){
								$magic = self::magicData($magic_id['id']);//Данные о МЭ
								foreach($magic['actions'] as $action_iter => $action){
									if($action['caption'] == 'inspiration'){
										foreach($action['inspiration_ActionRow'] as $row){
											if(empty($battle_field[$player][$row]['special'])){
												foreach($battle_field[$player][$row]['warrior'] as $card_data){
													$card = self::getCardNaturalSetting($card_data['id']);
													foreach($card['actions'] as $action){
														if($action['caption'] != 'inspiration'){
															$step_status['actions']['disappear'][$player][$row][] = 'inspiration';
														}
														break 2;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if(
			(isset($step_status['played_card']['card']) || isset($step_status['played_magic'])) &&
			(!empty($step_status['played_card']['card']) || !empty($step_status['played_magic']))
		){
			if(!empty($step_status['played_card']['card'])){
				$step_status['played_card']['card']['buffs'] = array_values(array_unique($step_status['played_card']['card']['buffs']));
				$step_status['played_card']['card']['debuffs'] = array_values(array_unique($step_status['played_card']['card']['debuffs']));
			}

			if(!empty($played_card_actions)){
				if(!empty($step_status['played_card']['card'])){
					foreach($step_status['played_card']['card']['actions'] as $action){
						$step_status = self::battleInfoFinishHelper($action, $step_status, 'card');
					}
				}else{
					foreach($step_status['played_magic'] as $player => $magic_data){
						foreach($magic_data['actions'] as $action){
							$step_status = self::battleInfoFinishHelper($action, $step_status, 'magic',$users_data);
						}
					}
				}
			}else{
				if (empty($step_status['played_magic'])) {
					$step_status['actions']['appear'] = [];
					$step_status['actions']['disappear'] = [];
					$step_status['actions']['cards'] = [];
				}
			}

			foreach($fury_cards as $player => $rows){
				foreach($rows as $row => $data){
					foreach($data as $card_iter => $card_data){
						$step_status['actions']['appear'][$player][$row][$card_iter] = 'fury';
						$step_status['actions']['cards'][$player][$row][$card_iter] = $card_data;
					}
				}
			}
		}

		return [
			'battle_field'	=> $battle_field,
			'field_status'	=> $field_status,
			'step_status'	=> $step_status,
			'cards_strength'=> $cards_strength
		];
	}

	protected static function battleInfoFinishHelper($action, $step_status, $type, $users_data = []){
		switch($action['caption']){
			case 'brotherhood':
				$player = ($step_status['played_card']['move_to']['player'] == 'p1')? 'p2': 'p1';

				if(!empty($player)){
					if(isset($step_status['actions']['cards'][$player])){
						unset($step_status['actions']['cards'][$player]);
					}
					if(isset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']])){
						foreach($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']] as $row => $data){
							if($row != $step_status['played_card']['move_to']['row']){
								unset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']][$row]);
							}
						}
					}
				}
			break;
			case 'inspiration':
				if($type == 'card'){
					$player = ($step_status['played_card']['move_to']['player'] == 'p1')? 'p2': 'p1';
				}else{
					$type = ($step_status['round_status']['current_player'] == $users_data['user']['login'])? 'opponent': 'user';

					$player_id = $users_data[$type]['id'];
					$player_status = \DB::table('tbl_battle_members')->select('round_passed')->where('user_id','=',$player_id)->first();
					if($player_status->round_passed == 0){
						$type = ($type == 'user')? 'opponent': 'user';
					}
					$player = $users_data[$type]['player'];
				}

				if(isset($step_status['actions']['cards'][$player])){
					unset($step_status['actions']['cards'][$player]);
				}
				foreach($step_status['actions']['cards'] as $player => $rows){
					foreach($rows as $row => $data){
						if($step_status['played_card']['move_to']['row'] != $row){
							if(isset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']][$row])){
								unset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']][$row]);
							}
						}
					}
				}
				break;
			case 'support':
				if($type == 'card'){
					$player = ($step_status['played_card']['move_to']['player'] == 'p1')? 'p2': 'p1';
				}else{
					$type = ($step_status['round_status']['current_player'] == $users_data['user']['login'])? 'user': 'opponent';
					var_dump($type);

					$player_id = $users_data[$type]['id'];
					$player_status = \DB::table('tbl_battle_members')->select('round_passed')->where('user_id','=',$player_id)->first();
					if($player_status->round_passed == 1){
						$type = ($type == 'user')? 'opponent': 'user';
					}
					var_dump($type);
					$player = $users_data[$type]['player'];
					var_dump($player);
				}
				if(isset($step_status['actions']['cards'][$player])){
					unset($step_status['actions']['cards'][$player]);
				}
				if(isset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']])){
					foreach($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']] as $row => $data){
						if(!in_array($row, $action['support_ActionRow'])){
							unset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']][$row]);
						}
					}
				}
			break;
			case 'terrify':
				if($step_status['played_card']['move_to']['player'] != 'mid'){
					if($action['fear_actionTeamate'] == 0){
						if(isset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']])) {
							unset($step_status['actions']['cards'][$step_status['played_card']['move_to']['player']]);
						}
					}
				}
				foreach($step_status['actions']['cards'] as $player => $rows){
					foreach($rows as $row => $data){
						if(!in_array($row, $action['fear_ActionRow'])){
							unset($step_status['actions']['cards'][$player][$row]);
						}
					}
				}
			break;
		}
		return $step_status;
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

	public static function magicData($id){
		if(strlen($id) > 11){
			$id = Crypt::decrypt($id);
		}

		$magic = \DB::table('tbl_magic_effect')
			->select('id','title','img_url','energy_cost','effect_actions')
			->find($id);
		if(!$magic) return false;

		$actions = self::processActions(unserialize($magic->effect_actions));
		return [
			'id'		=> Crypt::encrypt($magic->id),
			'title'		=> $magic->title,
			'img_url'	=> $magic->img_url,
			'energy_cost'=>$magic->energy_cost,
			'actions'	=> $actions
		];
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
		$buffs_classes = '';
		if(isset($card['card'])){
			if(!empty($card['buffs'])){
				$buffs_classes .= ' buffed';
				foreach($card['buffs'] as $buff){
					$buffs_classes .= ' '.$buff.'-buffed';
				}
			}
			if(!empty($card['debuffs'])){
				$buffs_classes .= ' debuffed';
				foreach($card['debuffs'] as $buff){
					$buffs_classes .= ' '.$buff.'-debuffed';
				}
			}
			$card = $card['card'];
		}
		$has_immune = 0;
		$has_full_immune = 0;
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
		<li class="content-card-item disable-select show'.$buffs_classes.'" data-relative="'.$card['fraction'].'" data-immune="'.$has_immune.'" data-full-immune="'.$has_full_immune.'" data-cardid="'.$card['id'].'" data-slug="'.$card['caption'].'" data-relative="'.$card['fraction'].'">
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
			$deck[$i] = self::getCardNaturalSetting($card);
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

	public static function getBattleBuffs($battle_field){
		$field_status = [];
		foreach($battle_field as $field => $rows){
			if($field == 'mid'){
				foreach($rows as $card_iter => $card_data){
					$card = self::getCardNaturalSetting($card_data['id']);
					foreach($card['actions'] as $action){
						if($action['caption'] == 'terrify'){
							if($action['fear_actionTeamate'] == 0){
								$fields = ($field == 'p1')? ['p2']: ['p1'];
							}else{
								$fields = ['p1','p2'];
							}
							foreach($fields as $action_field){
								foreach($action['fear_ActionRow'] as $action_row){
									$field_status[$action_field][$action_row][] = $action['caption'];
								}
							}
						}
					}
				}
			}else{
				foreach($rows as $row => $row_data){
					foreach($row_data as $type => $cards){
						if($type == 'special'){
							if(!empty($cards)){
								$card = self::getCardNaturalSetting($cards['id']);
								foreach($card['actions'] as $action){
									if($action['caption'] == 'inspiration'){
										$field_status[$field][$row][] = $action['caption'];
									}
								}
							}
						}else{
							foreach($cards as $card){
								$card = self::getCardNaturalSetting($card['id']);
								foreach($card['actions'] as $action){
									switch($action['caption']){
										case 'brotherhood':
										case 'inspiration':
											$field_status[$field][$row][] = $action['caption'];
										break;
										case 'support':
											foreach($action['support_ActionRow'] as $action_row){
												$field_status[$field][$action_row][] = $action['caption'];
											}
										break;
										case 'terrify':
											if($action['fear_actionTeamate'] == 0){
												$fields = ($field == 'p1')? ['p2']: ['p1'];
											}else{
												$fields = ['p1','p2'];
											}
											foreach($fields as $action_field){
												foreach($action['fear_ActionRow'] as $action_row){
													$field_status[$action_field][$action_row][] = $action['caption'];
												}
											}
										break;
									}
								}
							}
						}
					}
				}
			}
		}
		foreach($field_status as $field => $rows){
			foreach($rows as $row =>$data){
				$field_status[$field][$row] = array_values(array_unique($field_status[$field][$row]));
			}
		}
		return $field_status;
	}

	public static function getCardDescription($id){
		$card = \DB::table('tbl_cards')
			->select('id','title','full_description','card_type','card_race','is_leader','card_strong','img_url','allowed_rows','card_actions')
			->find($id);
		if($card == false) return false;

		$allowed_row_images = SiteFunctionsController::createActionRowsArray($card->allowed_rows);
		$action_images = SiteFunctionsController::createActionsArray(unserialize($card->card_actions));

		$fraction = ($card->card_type == 'race')? $card->card_race: $card->card_type;

		$fraction_img = self::getFractionFlag($fraction);
		return [
			'id'			=> Crypt::encrypt($card->id),
			'title'			=> $card->title,
			'text'			=> $card->full_description,
			'img_url'		=> $card->img_url,
			'fraction'		=> $fraction,
			'fraction_img'	=> $fraction_img,
			'is_leader'		=> $card->is_leader,
			'strength'		=> $card->card_strong,
			'allowed_row_images'=> $allowed_row_images,
			'action_images'	=> $action_images,
		];
	}

	public static function getMagicDescription($id){
		$magic = \DB::table('tbl_magic_effect')
			->select('id','title','img_url','description')
			->find($id);

		return [
			'id'		=> Crypt::encrypt($magic->id),
			'title'		=> $magic->title,
			'text'		=> $magic->description,
			'img_url'	=> $magic->img_url,
		];
	}
}