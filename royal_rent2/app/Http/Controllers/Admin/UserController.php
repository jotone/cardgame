<?php
namespace App\Http\Controllers\Admin;

use App\User;
use App\UserRoles;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
use Validator;

class UserController extends BaseController
{
	public function user_rolesAdd(Request $request){
		$data = $request->all();
		$pages = serialize(json_decode($data['pages']));
		if( (isset($data['id'])) && (!empty($data['id'])) ){
			$result = UserRoles::find($data['id']);
			$result ->title = $data['title'];
			$result ->pseudonim = $data['pseudonim'];
			$result ->access_pages = $pages;
			$result ->save();
		}else{
			$result = UserRoles::create([
				'title'		=> $data['title'],
				'pseudonim'	=> $data['pseudonim'],
				'editable'	=> 1,
				'access_pages'=> $pages
			]);
		}

		if($result != false){
			return 'success';
		}
	}

	public function user_rolesDelete(Request $request){
		$data = $request->all();
		$result = UserRoles::where('id',$data['id'])->delete();
		if($result != false){
			return 'success';
		}
	}

	public function userEdit(Request $request){
		$data = $request->all();
		if( (isset($data['id'])) && (!empty($data['id'])) ){
			$data = $request->all();
			$result = User::find($data['id']);
			$result ->email = $data['email'];
			$result ->name  = $data['name'];
			$result ->phone = $data['phone'];
			$result ->user_role = $data['role'];
			$result ->save();
		}
		if($result != false){
			return 'success';
		}
	}

	public function userDelete(Request $request){
		$data = $request->all();
		$result = User::where('id','=',$data['id'])->delete();
		if($result != false){
			return 'success';
		}
	}
}
