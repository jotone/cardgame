<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
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
			if($user['user_role'] != 1){
				return redirect(route('admin-login'));
			}
		}
	
		return $next($request);
	}
}
