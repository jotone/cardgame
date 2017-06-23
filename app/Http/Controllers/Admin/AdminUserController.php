<?php

namespace App\Http\Controllers\Admin;

use \App\User;
use App\Http\Controllers\Admin\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminUserController extends BaseController
{
    protected function changeBan($value, $id){
        $result = \DB::table('users')->where('id', '=', $id)->update(['is_banned' => $value]);
        if($result !== false){
            return 'success';
        }
    }

    public function banUser(Request $request){
        $data = $request->all();
        return $this->changeBan(1, $data['id']);
    }

    public function unbanUser(Request $request){
        $data = $request->all();
        return $this->changeBan(0, $data['id']);
    }

    public function userEdit(Request $request){
        $data = $request->all();

        $user = User::find($data['id']);
        if($user->login == getenv('DEBUG_APP_SMNSYS')){
            $role = 'true';
        }else{
            $role = $data['role'];
        }
        $role = ($role == true)? 1: 0;

        $user -> email = $data['email'];
        $user -> name = $data['name'];
        $user -> birth_date = $data['birthDate'];
        $user -> user_gold = $data['gold'];
        $user -> user_silver = $data['silver'];
        $user -> user_energy = $data['energy'];
        if($data['premActive'] == 'true'){
            $user -> premium_activated = 1;
        }else{
            $user -> premium_activated = 0;
        }
        $date = str_replace(['.','/','\\','_',':',' '], '-', $data['premExpire']);
        $user -> premium_expire_date = $date;
        $user -> user_role = $role;
        $user -> save();
        if($user !== false){
            return 'success';
        }
    }

    public function dropUser(Request $request){
        $data = $request->all();
        $result = User::find($data['user_id']);
        $result -> delete();
        return redirect(route('admin-users'));
    }
}