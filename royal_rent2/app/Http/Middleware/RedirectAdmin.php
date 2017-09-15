<?php
namespace App\Http\Middleware;
use App\UserRoles;
use Closure;
use Illuminate\Support\Facades\Auth;
class RedirectAdmin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'admin')
	{
		if (!Auth::check()) {
			return redirect(route('admin-login'));
		}else{
			$user = Auth::user();
			$roles = UserRoles::select('pseudonim','access_pages')->where('access_pages','!=','deny_all')->get();
			$role_arr = [];
			foreach($roles as $role){
				$role_arr[] = $role->pseudonim;
			}
			if(!in_array($user['user_role'],$role_arr)){
				return redirect(route('admin-login'));
			}
		}
		return $next($request);
	}
}