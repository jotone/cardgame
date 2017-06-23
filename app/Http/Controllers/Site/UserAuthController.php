<?php
namespace App\Http\Controllers\Site;

use App\EtcData;
use App\League;
use App\Fraction;
use App\Rubric;
use App\User;
use Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Validator;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class UserAuthController extends BaseController
{
	public function login(Request $request){
		$data = $request->all();

		$login = htmlspecialchars(strip_tags(trim($data['login'])));
		$password = htmlspecialchars(strip_tags(trim($data['password'])));
		$password = md5($password.$login);
		$user_isset = User::where('login', '=', $login )->where('password', '=', $password)->count();

		if(1 == $user_isset){
			$user = User::where('login', '=', $login )->where('password', '=', $password)->get();

			if($user[0]->is_activated == 0){
				return redirect(route('user-home'))->withErrors(['Вы не подтвердили регистрацию по почте.']);
			}

			$auth = Auth::loginUsingId($user[0]->id);
			if(!$auth){
				return redirect(route('user-home'))->withErrors(['Ошибка авторизации']);
			}else{
				User::where('login', '=', $login )->where('password', '=', $password)->update(['user_online' => 1]);

				$user_rating = unserialize($auth->user_rating);
				$user_rating_keys = array_keys($user_rating);
				$leagues = League::get()->all();
				foreach($leagues as $league_id => $league_data){
					if( !in_array($league_data->slug, $user_rating_keys) ){
						$user_rating[$league_data['slug']] = [
							'user_rating'	=> '0',
							'win_count'		=> '0',
							'games_count'	=> '0'
						];
					}
				}

				$user_cards_in_deck = unserialize($auth->user_cards_in_deck);
				$user_available_deck = unserialize($auth->user_available_deck);
				foreach($user_cards_in_deck as $deck => $cards){
					foreach ($cards as $card_id => $q){
						$card_isset = \DB::table('tbl_cards')->where('id','=',$card_id)->count();
						if(!$card_isset){
							unset($user_cards_in_deck[$deck][$card_id]);
						}
					}
				}
				foreach($user_available_deck as $card_id => $q){
					$card_isset = \DB::table('tbl_cards')->where('id','=',$card_id)->count();
					if(!$card_isset){
						unset($user_cards_in_deck[$deck][$card_id]);
					}
				}

				$user_magic = unserialize($auth->user_magic);
				foreach($user_magic as $magic_id => $magic_data){
					$magic_isset = \DB::table('tbl_magic_effect')->where('id','=',$magic_id)->count();
					if(!$magic_isset){
						unset($user_magic[$magic_id]);
					}
				}

				User::where('id','=',$auth->id)->update([
					'user_rating'		=>serialize($user_rating),
					'user_cards_in_deck'=>serialize($user_cards_in_deck),
					'user_available_deck'=>serialize($user_available_deck),
					'user_magic'		=>serialize($user_magic),
					'user_online'		=> 1,
					'user_busy'	=> 0
				]);

				return redirect(route('user-home'));
			}
		}else{
			return redirect(route('user-home'))->withErrors(['Не правильный логин или пароль.']);
		}

	}

	public function logout(){
		$user = Auth::user();
		User::where('login', '=', $user['login'] )->where('password', '=', $user['password'])->update(['user_online' => 0]);
		Auth::logout();
		return redirect('/');
	}

	protected function userRegistration(Request $request){

		$data = $request -> all();

		//Валидация основных данных пользователя
		$validator = Validator::make($data, [
			'login'		=> 'required|max:255|min:3',
			'password'	=> 'required|min:6',
			'email'		=> 'required|email'
		]);
		/*
		 * Если данные не прошли валидацию, отправляем отчет
		 * о ошибках на /registration
		*/
		if($validator->fails()){
			return redirect(route('user-registration'))->withErrors($validator);
		}

		$login = htmlspecialchars(strip_tags(trim($data['login'])));
		$email = htmlspecialchars(strip_tags(trim($data['email'])));

		$password = htmlspecialchars(strip_tags(trim($data['password'])));
		$conf_pass = htmlspecialchars(strip_tags(trim($data['confirm_password'])));

		//если праоль не сходится с подтверждением
		if($password != $conf_pass){
			return redirect(route('user-registration'))->withErrors(['Подтвердите пароль']);
		}

		$password = md5($password.$login);

		$user_fraction = htmlspecialchars(strip_tags(trim($data['fraction_select'])));

		//Узнаем есть ли в БД пользователь с таким же логином
		$user = User::where('login', '=', $login )->count();

		if(!preg_match("/^[a-zA-Z][a-zA-Z0-9_\-]{2,255}$/", $login)){
			return redirect(route('user-registration'))->withErrors(['Логин пользователя содержит<br>запрещеные символы.<br>Разрешено использовать латинские буквы,<br>цифры, символы "_" и "-"']);
		}

		//Если такого пользователя не существует
		if(0 == $user){

			$base_fields = EtcData::where('label_data', '=', 'base_user_fields')->get();
			$user_begin_data = [];
			foreach($base_fields as $key){
				$user_begin_data[$key->meta_key] = $key->meta_value;
			}

			$fractions = \DB::table('tbl_fraction')->select('slug','type')->where('type', '=', 'race')->get();
			foreach($fractions as $fraction){
				$user_card_deck[$fraction->slug] = [];
			}

			$fractions = \DB::table('tbl_fraction')
				->select('slug','cards')
				->where('type','=','race')
				->where('slug','=',$user_fraction)
				->get();
			//Массив начальных доступных карт
			$race_deck = unserialize($fractions[0]->cards);

			foreach($race_deck as $key => $value){
				$user_card_deck[$user_fraction][$value['id']] = $value['q'];
			}

			$league_options = [];
			$leagues = League::orderBy('min_lvl', 'asc')->get();
			foreach($leagues as $i => $league){
				$league_options[$league['slug']] = [
					'user_rating'	=> '0',
					'win_count'		=> '0',
					'games_count'	=> '0'
				];
			}

			$activation_code = str_random(32);
			//Создаем пользователя и заносим его в таблицу с основными данными users
			$result = User::create([
				'login'				=> $login,
				'email'				=> $email,
				'password'			=> $password,
				'is_banned'			=> '0',
				'ban_time'			=> '0000-00-00 00:00:00',
				'user_role'			=> '0',
				'user_online'		=> '1',
				'user_gold'			=> $user_begin_data['baseGold'],
				'user_silver'		=> $user_begin_data['baseSilver'],
				'user_energy'		=> $user_begin_data['baseEnergy'],
				'user_current_deck'	=> $user_fraction,
				'last_user_deck'	=> $user_fraction,
				'user_base_fraction'=> $user_fraction,
				'user_available_deck'=>'a:0:{}',
				'user_cards_in_deck'=> serialize($user_card_deck),
				'user_magic'		=> 'a:0:{}',
				'user_rating'		=> serialize($league_options),
				'is_activated'		=> 1, //ПОМЕНЯТЬ НА 0
				'activation_code'	=> $activation_code,
			]);

			if($result !== false){
				$code = '<a href="'.route('user-confirm-token',$activation_code).'">'.$activation_code.'</a>';
				$mail_result = mail($result->email, 'Подтвердите регистрацию', $activation_code, 'From: dragonheart@xmail.com');

				return redirect(route('user-home'))->withErrors(['Регистрация почти завершена.<br>Вам необходимо подтвердить e-mail, указанный при регистрации, перейдя по ссылке в письме.']);
			}
		}else{
			return redirect(route('user-registration'))->withErrors(['Такой пользователь уже существует']);
		}
	}

	//пользователь меняет свои данные
	protected function userChangeSettings(Request $request){
		$data = $request->all();

		$user = Auth::user();
		if(($data['action'] == 'user_settings') && ($user)){
			$email = htmlspecialchars(strip_tags(trim($data['settings_email'])));
			$old_pass = htmlspecialchars(strip_tags(trim($data['current_password'])));
			$new_pass = htmlspecialchars(strip_tags(trim($data['settings_pass'])));
			$new_conf_pass = htmlspecialchars(strip_tags(trim($data['settings_pass_confirm'])));
			$name = htmlspecialchars(strip_tags(trim($data['user_name'])));
			$birth_date = htmlspecialchars(strip_tags(trim($data['birth_date'])));
			$gender = htmlspecialchars(strip_tags(trim($data['gender'])));

			if((!empty($email)) && ($email != $user->email)){
				$validator = Validator::make($data, [
					'email'	=> 'email'
				]);

				if($validator->fails()){
					return redirect(route('user-settings'))->withErrors($validator);
				}

				User::where('id','=',$user['id'])->update(['email' => $email]);
			}

			if(!empty($new_pass)){
				if(strlen($new_pass) < 6){
					return json_encode('Пароль слишком короткий');
				}

				if($user['password'] == md5($old_pass.$user['login'])){
					if($new_pass == $new_conf_pass){
						User::where('login','=',$user['login'])->update(['password' => md5($new_pass.$user['login'])]);
					}else{
						return json_encode('Пароль подтвержден неверно.');
					}
				}else{
					return json_encode('Неверный текущий пароль');
				}
			}

			if('undefined' != $data['image_user']){
				//Узнаем реальное имя файла
				$img_file = $data['image_user']->getClientOriginalName();

				$path_info = pathinfo($img_file);

				switch($path_info['extension']){
					case 'jpeg':$error = 0; break;
					case 'jpg': $error = 0; break;
					case 'gif': $error = 0; break;
					case 'png': $error = 0; break;
					default: $error = 1;
				}
				if(0 == $error){
					//Указываем папку хранения картинок
					$destinationPath = base_path().'/public/img/user_images/';
					$img_file = uniqid().'_'.htmlspecialchars(strip_tags(trim(AdminFunctions::str2url($img_file))));
					$result = $data['image_user']->move($destinationPath, $img_file);

					if($result != false){
						User::where('login','=',$user->login)->update(['img_url' => $img_file]);

						$img_to_crop = Image::make($destinationPath.$img_file);
						$natural_width = $img_to_crop->width();
						$natural_height =  $img_to_crop->height();

						if( (($natural_width / 2) > $natural_height) || (($natural_height / 2) > $natural_width) ){
							$x = ceil($natural_width /2);
							$y = ceil($natural_height /2);

							$crop_dimension = (($natural_width / 2) > $natural_height)? $natural_height: $natural_width;

							$img_to_crop->crop($crop_dimension, $crop_dimension, $x, $y);

							$natural_width = $img_to_crop->width();
							$natural_height =  $img_to_crop->height();
						}

						$divider = ($natural_width > $natural_height)? ceil($natural_width / 250): ceil($natural_height / 250);

						$width = $natural_width / $divider;
						$height = $natural_height / $divider;
						$img_to_crop->resize($width, $height, function ($constraint) {
							$constraint->upsize();
						});
						$img_to_crop->save();
					}
				}
			}

			if(!empty($name)){
				User::where('login','=',$user->login)->update(['name' => $name]);
			}

			if(!empty($birth_date)){
				User::where('login','=',$user->login)->update(['birth_date' => $birth_date]);
			}

			if(!empty($gender)){
				User::where('login','=',$user->login)->update(['user_gender' => $gender]);
			}

			return 'success';
		}
	}


	protected function confirmAccessToken($token){
		$token = strip_tags(htmlspecialchars(trim($token)));
		$user = \DB::table('users')
			->select('id','is_activated','activation_code')
			->where('is_activated', '=', 0)
			->where('activation_code', '=', $token)
			->get();

		if($user){

			if($user[0]->is_activated == 1){
				return redirect(route('user-home')->withErrors(['Вы уже активировали данный аккаунт.']));
			}

			$uset_to_activate = User::find($user[0] -> id);
			$uset_to_activate -> is_activated = 1;
			$uset_to_activate -> save();

			$auth = Auth::loginUsingId($user[0]->id);
			if(!$auth){
				return redirect(route('user-home'))->withErrors(['Ошибка авторизации']);
			}else{
				User::where('id', '=', $user[0]->id)->update(['user_online' => 1]);
				return redirect(route('user-home'));
			}

		}else{
			return redirect(route('user-home'))->withErrors(['Произошел сбой в подтверждении регистрации. Обратитесь в тех. поддержку.']);
		}
	}

	public function userSendsLetter(Request $request){
		$data = $request->all();
		if(!empty($data['g-recaptcha-response'])){
			$rubric = Rubric::select('title','slug')->where('slug','=',$data['rubric_select'])->get();
			$message = $rubric[0]->title."\n".strip_tags(htmlspecialchars(trim($data['qestionText'])));

			$email_from = strip_tags(htmlspecialchars(trim($data['email'])));

			$emails_to = EtcData::select('label_data', 'meta_key', 'meta_key_title')
				->where('label_data','=','support')
				->where('meta_key','=','emails')
				->get();

			$emails_to = unserialize($emails_to[0]->meta_key_title);
			$emails_to = implode(', ', $emails_to);

			$result = mail($emails_to, $rubric[0]->title, $message, 'From: '.$email_from);
			if($result){
				return redirect(route('user-home'));
			}
		}else{
			return redirect(route('user-support'))->withErrors('Подтвердите, что вы не робот.');
		}
	}
}