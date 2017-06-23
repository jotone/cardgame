<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Site\SiteGameController;
use App\Http\Controllers\Site\SiteFunctionsController;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Crypt;

class BattleFieldController extends BaseController{
	public static function battleInfo($battle_field){
		$result = [];

		foreach($battle_field as $field => $field_data){
			if($field == 'mid'){
				if(!empty($field_data)){
					foreach($field_data as $card_data){
						if(isset($result[$field][$card_data['id']])){
							$result[$field][$card_data['id']]['counts']++;
						}else{
							$result[$field][$card_data['id']] = [
								'data'	=> self::cardData($card_data['id']),
								'counts'=> 1
							];
						}
					}
				}else{
					$result[$field] = [];
				}
			}else{
				foreach($field_data as $row => $row_data){
					$result[$field][$row]['buff'] = [];
					$result[$field][$row]['debuff'] = [];
					if(!empty($row_data['special'])){
						$result[$field][$row]['special'] = [
							'data'	=> self::cardData($row_data['special']['id']),
							'login'	=> $row_data['special']['login']
						];
					}else{
						$result[$field][$row]['special'] = [];
					}
					if(!empty($row_data['warrior'])){
						foreach($row_data['warrior'] as $card){
							$card_data = self::cardData($card['id']);
							$result[$field][$row]['warrior'][] = [
								'buff'		=> [],
								'debuff'	=> [],
								'data'		=> $card_data,
								'strength'	=> $card_data['strength'],
								'strengthModified'=> $card_data['strength'],
								'group'		=> implode(',',$card_data['group']),
								'login'		=> $card['login']
							];
						}
					}else{
						$result[$field][$row]['warrior'] = [];
					}
				}
			}
		}
		return $result;
	}

	public static function cardData($id){
		$card = \DB::table('tbl_cards')
			->select('id','title','slug','card_type','card_race','is_leader','card_strong','card_groups','img_url','allowed_rows','card_actions')
			->find($id);
		if($card == false) return false;

		$allowed_row_images = SiteFunctionsController::createActionRowsArray($card->allowed_rows);
		$action_images = SiteFunctionsController::createActionsArray(unserialize($card->card_actions));

		$actions = self::processActions(unserialize($card->card_actions));

		$fraction = ($card->card_type == 'race')? $card->card_race: $card->card_type;

		switch($fraction){
			case 'cursed':		$fraction_img = 'cart-open-flag-cursed.png'; break;
			case 'forest':		$fraction_img = 'cart-open-flag-forest-masters.png'; break;
			case 'highlander':	$fraction_img = 'cart-open-flag-highlanders.png'; break;
			case 'knight':		$fraction_img = 'cart-open-flag-knights.png'; break;
			case 'monsters':	$fraction_img = 'cart-open-flag-monsters.png'; break;
			case 'neutrall':	$fraction_img = 'cart-open-flag-neutral.png'; break;
			case 'undead':		$fraction_img = 'cart-open-flag-undead.png'; break;
			default: $fraction_img = '';
		}
		return [
			'id'			=> Crypt::encrypt($card->id),
			'title'			=> $card->title,
			'slug'			=> $card->slug.'_'.($card->id+13),
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

	public static function cardSimpleView($id, $quantity = 0){
		$card = self::cardData($id);

		$has_immune = 0;
		$has_full_immune = 0;
		foreach($card['actions'] as $action){
			if($action['caption'] == 'immune'){
				$has_immune = 1;
				$has_full_immune = $action['immumity_type'] == '1';
			}
		}

		switch($card['fraction']){
			case 'knight':		$race_class = 'knight-race'; break;
			case 'highlander':	$race_class = 'highlander-race'; break;
			case 'monsters':	$race_class = 'monsters-race'; break;
			case 'undead':		$race_class = 'undead-race'; break;
			case 'cursed':		$race_class = 'cursed-race'; break;
			case 'forest':		$race_class = 'forest-race'; break;
			case 'neutrall':	$race_class = 'neutrall-race'; break;
			default: $race_class = '';
		}

		$allowed_row_images = '';
		if(!empty($card['fraction'])){
			foreach($card['allowed_row_images'] as $i => $dist){
				$allowed_row_images .= '
				<img src="'.\URL::asset($dist['image']).'" alt="">
				<span class="card-action-description">'.$dist['title'].'</span>';
			}
		}

		$special_class = (empty($card['fraction']))? 'special-type': '';
		$leader_class = ($card['is_leader'] == 1 )? 'leader-type': '';

		$quantity_tag = ($quantity > 1)? '<div class="count">'.$quantity.'</div>': '';

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

		return '
		<li class="content-card-item disable-select show" data-cardid="'.$card['id'].'" data-relative="'.$card['fraction'].'" data-immune="'.$has_immune.'" data-full-immune="'.$has_full_immune.'">
			'.$quantity_tag.'
			<div class="content-card-item-main '.$race_class.' '.$leader_class.' '.$special_class.'" style="background-image: url('.\URL::asset('/img/card_images/'.$card['img_url']).')" data-leader="'.$card['is_leader'].'">
				<div class="card-load-info card-popup">
					<div class="info-img">
						<img class="ignore" src="'.\URL::asset('/images/info-icon.png').'" alt="">
						<span class="card-action-description">Инфо о карте</span>
					</div>
					'.$leader_tag.'
					<div class="label-power-card">
						<span class="label-power-card-wrap">
							<span class="buff-debuff-value"></span>
							<span class="card-current-value">'.$card['strength'].'</span>
						</span>
						<span class="card-action-description">Сила карты</span>
					</div>
					<div class="hovered-items">
						<div class="card-game-status">
							<div class="card-game-status-role">
							'.$allowed_row_images.'
							</div>
							<div class="card-game-status-wrap">
							'.$action_images.'
							</div>
						</div>
						<div class="card-name-property">
							<p>'.$card['title'].'</p>
						</div>
					</div>
				</div>
			</div>
		</li>';
	}
}