<?php
namespace App\Http\Controllers\Site;
use App\Http\Controllers\Supply\PHPMailer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\Supply\SMTP;
use Auth;
use Crypt;
use Validator;

class AuthController extends BaseController
{
	public function login(Request $request){
		$data = $request->all();
		$data['email'] = trim($data['email']);
		$data['password'] = trim($data['password']);

		$password = md5($data['email'].$data['password']);
		$user = User::select('id','login','password')
			->where('login','=',trim($data['email']))
			->where('password','=',$password)
			->first();

		if(!empty($user)){
			Auth::loginUsingId($user->id);
			return redirect()->back();
		}else{
			$status = [];
			$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $data['email']);
			if(!$res){
				$status['email'][] = ' The e-mail must be a valid email address';
			}
			if(!empty($status)){
				return redirect(route('home'))->with('status', $status);
			}

			$activation_code = base64_encode(serialize([$data['email'],date('Y-m-d H:i:s')])).'-'.uniqid();

			$result = User::create([
				'login'		=> $data['email'],
				'email'		=> $data['email'],
				'password'	=> $password,
				'name'		=> $data['name'],
				'phone'		=> $data['tel'],
				'resources'	=> 'a:0:{}',
				'user_role'	=> 'user',
				'account_activated' => 1,
				'activation_code' => $activation_code
			]);
			if($result != false){
				Auth::loginUsingId($result->id);

				$message = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><head><title>Royal Rent. Запрос на регистрацию в ЛК</title></head><body><p>Вы зарегистрировались как: '.$data['email'].'</p><p>Ваш пароль: '.$data['password'].'</p></body></html>';

				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= "From: Royal Rent <info@royalrent.ru>\r\n";
				$headers .= "Reply-To: info@royalrent.ru\r\n";

				$mail = mail($data['email'] , 'Royal Rent', $message, $headers);
				if($mail){
					return redirect()->back();
				}
			}
		}
	}

	public function logout(){
		Auth::logout();
		return redirect(route('home'));
	}
}