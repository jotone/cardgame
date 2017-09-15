<?php
namespace App\Http\Controllers\Admin;
use App\User;

use App\Http\Controllers\Supply\Functions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Auth;
use Crypt;
use Validator;
class AuthController extends BaseController
{
	public function login(Request $request){
		$data = $request->all();
		$login = Functions::strip_data(trim($data['login']));
		$password = Functions::strip_data(trim(substr($data['password'],0,255)));
		$password = md5($login.$password);
		$user = User::select('id','login','password','user_role')
			->where('login','=',$login)
			->where('password','=',$password)
			->where('user_role','!=','user')
			->get();
		if(!empty($user->all())){
			Auth::loginUsingId($user[0]->id);
			return redirect(route('admin-index'));
		}else{
			return redirect(route('home'));
		}
	}

	public function logout(){
		Auth::logout();
		return redirect(route('home'));
	}
}