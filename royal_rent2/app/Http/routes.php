<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$redirects = \DB::table('tbl_requests')->select('link_from')->get();
foreach($redirects as $redirect){
	Route::get($redirect->link_from, function(Illuminate\Http\Request $request){
		$link = $request->getRequestUri();
		$redirect = \DB::table('tbl_requests')->select('link_to')->where('link_from','=',$link)->first();
		return Redirect::to($redirect->link_to, 301);
	});
}

Route::get('/', [
	'as'	=> 'home',
	'uses'	=> 'Site\PagesController@index'
]);
Route::get('/about_us', [
	'as'	=> 'about_us',
	'uses'	=> 'Site\PagesController@about_us'
]);
Route::get('/business_travel', [
	'as'	=> 'business_travel',
	'uses'	=> 'Site\PagesController@business_travel'
]);
Route::get('/contacts', [
	'as'	=> 'contacts',
	'uses'	=> 'Site\PagesController@contacts'
]);
Route::get('/credit_for_rent',[
	'as'	=> 'credit_for_rent',
	'uses'	=> 'Site\PagesController@creditForRent'
]);
Route::get('/hire_purchase', [
	'as'	=> 'hire_purchase',
	'uses'	=> 'Site\PagesController@hirePurchase'
]);
Route::get('/event_organization' ,[
	'as'	=> 'event_organization',
	'uses'	=> 'Site\PagesController@eventOrganization'
]);
Route::get('/cooperation', [
	'as'	=> 'cooperation',
	'uses'	=> 'Site\PagesController@cooperation'
]);
Route::get('/our_franchise',[
	'as'	=> 'our_franchise',
	'uses'	=> 'Site\PagesController@ourFranchise'
]);
Route::get('/delivery_car_to_taxi', [
	'as'	=> 'delivery_car_to_taxi',
	'uses'	=> 'Site\PagesController@deliveryCarToTaxi'
]);
Route::get('/operative_leasing', [
	'as'	=> 'operative_leasing',
	'uses'	=> 'Site\PagesController@operativeLeasing'
]);
Route::get('/rent_terms', [
	'as'	=> 'rent_terms',
	'uses'	=> 'Site\PagesController@rentTerms'
]);
Route::get('/tarifs', [
	'uses'	=> 'Site\PagesController@tarifs'
]);
Route::get('/excursion', [
	'as'	=> 'excursion',
	'uses'	=> 'Site\PagesController@excursion'
]);
Route::get('/excursion/{slug}', [
	'uses'	=> 'Site\PagesController@excursionView'
]);
Route::get('/order_excursion/{slug}', [
	'uses'	=> 'Site\PagesController@excursionOrder'
]);
Route::get('/romantic_date', [
	'as'	=> 'romantic_date',
	'uses'	=> 'Site\PagesController@romanticDate'
]);
Route::get('/romantic_date/{slug}', [
	'uses'	=> 'Site\PagesController@romanticDateView'
]);
Route::get('/order_romantic/{slug}',[
	'uses'	=> 'Site\PagesController@excursionOrder'
]);
Route::get('/for_investor', [
	'uses'  => 'Site\PagesController@forInvestor'
]);
Route::get('/articles', [
	'as'	=> 'articles',
	'uses'	=> 'Site\PagesController@news'
]);
Route::get('/articles/{slug}', [
	'uses'	=> 'Site\PagesController@viewNews'
]);
Route::get('/news', [
	'as'	=> 'news',
	'uses'	=> 'Site\PagesController@news'
]);
Route::get('/news/{slug}', [
	'uses'	=> 'Site\PagesController@viewNews'
]);
Route::get('/promo', [
	'as'	=> 'promo',
	'uses'	=> 'Site\PagesController@promo'
]);
Route::get('/promo/{slug}', [
	'uses'	=> 'Site\PagesController@viewNews'
]);

Route::get('/transport/{slug}/{category?}', [
	'uses'	=> 'Site\PagesController@simplePages'
]);
Route::get('/reviews', [
	'as'	=> 'reviews',
	'uses'	=> 'Site\PagesController@reviews'
]);

Route::get('/car/order/{category}/{id}', [
	'uses'	=> 'Site\PagesController@orderCar'
]);
Route::get('/car/{category}/{slug}', [
	'uses'	=> 'Site\PagesController@viewCar'
]);

Route::put('/login', [
	'as'	=> 'login',
	'uses'	=> 'Site\AuthController@login'
]);
Route::get('/logout', [
	'as'	=> 'logout',
	'uses'	=> 'Site\AuthController@logout'
]);
Route::get('/404', [
	'as'	=> 'page404',
	'uses'	=> 'Site\PagesController@page404'
]);

/*Route::get('/registration', [
	'as'	=> 'registration-page',
	'uses'	=> 'Site\PagesController@registrationPage'
]);
Route::post('/registration', [
	'as'	=> 'register-me',
	'uses'	=> 'Site\AuthController@registration'
]);*/

Route::get('/get_subcategory', [
	'uses'	=> 'Supply\Helpers@getSubcategory'
]);
Route::get('/get_mark_by_category', [
	'uses'  => 'Supply\Helpers@getMarkByCategory'
]);
Route::get('/get_models_by_mark', [
	'uses'	=> 'Supply\Helpers@getModelsByMark'
]);
Route::get('/get_car_by_model', [
	'uses'	=> 'Supply\Helpers@getCarByModel'
]);
Route::get('/get_car_by_id', [
	'uses'	=> 'Supply\Helpers@getCarById'
]);
Route::get('/show_more_cars', [
	'uses'	=> 'Supply\Helpers@getMoreCars'
]);
Route::get('/get_cars_by_filter', [
	'uses'	=> 'Supply\Helpers@getCarsByFilter'
]);
Route::get('/get_location', [
	'uses'	=> 'Supply\Helpers@getLocations'
]);
Route::post('/set_city', [
	'as'	=> 'set-city',
	'uses'	=> 'Supply\Helpers@setCity'
]);
Route::get('/calculate_rent', [
	'uses'	=> 'Supply\Helpers@calculateRent'
]);

Route::post('/send_letter', [
	'as'	=> 'send-mail',
	'uses'	=> 'Supply\Helpers@mailSender'
]);
Route::post('/subscribe_us', [
	'as'	=> 'subscribe',
	'uses'	=> 'Supply\Helpers@subscribeUs'
]);

Route::put('/add_review', [
	'as'	=> 'add-review',
	'uses'	=> 'Supply\Helpers@addReview'
]);

//Authorisation
Route::get('/admin/login', [
	'as'	=> 'admin-login',
	'uses'	=> 'Admin\PagesController@loginPage'
]);
Route::put('/admin/login', [
	'as'	=> 'login-as-admin',
	'uses'	=> 'Admin\AuthController@login'
]);
Route::get('/admin/logout', [
	'uses'	=> 'Admin\AuthController@logout'
]);

Route::group(['middleware' => 'admin'], function() {
	//REQUESTS
	Route::get('/admin/get_server_images', [
		'uses'	=> 'Admin\SimilarQueriesController@getServerImages'
	]);

	//ADMIN HOME PAGE
	Route::get('/admin', [
		'as'	=> 'admin-index',
		'uses'	=> 'Admin\PagesController@index'
	]);

	//SETTINGS
	Route::get('/admin/settings', [
		'as'	=> 'admin-settings',
		'uses'	=> 'Admin\PagesController@settings'
	]);
	Route::post('/admin/settings/save', [
		'uses'	=> 'Admin\SimilarQueriesController@saveSettings'
	]);
	Route::get('/admin/get_menu',[
		'uses'	=> 'Admin\SimilarQueriesController@getTopMenu'
	]);
	Route::get('/admin/redirects', [
		'uses'	=> 'Admin\PagesController@redirects'
	]);
		Route::post('/admin/save_redirects', [
			'uses'	=> 'Admin\SimilarQueriesController@saveRedirects'
		]);
		Route::delete('/admin/drop_redirect', [
			'uses'	=> 'Admin\SimilarQueriesController@dropRedirect'
		]);
	//Dispatching
	Route::get('/admin/dispatch', [
		'as'	=> 'admin-dispatch',
		'uses'	=> 'Admin\PagesController@dispatch'
	]);
	Route::post('/admin/dispatch/add', [
		'uses'	=> 'Admin\MailingController@dispatchAdd'
	]);
	Route::post('/admin/dispatch/make', [
		'uses'	=> 'Admin\MailingController@dispatchMake'
	]);
	Route::get('/admin/dispatch/get_template', [
		'uses'	=> 'Admin\MailingController@dispatchGetTemplate'
	]);
	//Mailing
	Route::get('/admin/mailing', [
		'as'	=> 'admin-mailing',
		'uses'	=> 'Admin\PagesController@mailing'
	]);
	Route::get('/admin/mailing/add', [
		'as'	=> 'admin-mailing',
		'uses'	=> 'Admin\PagesController@mailingAdd'
	]);
	Route::get('/admin/mailing/edit/{id}',[
		'uses'	=> 'Admin\PagesController@mailingEdit'
	]);
	Route::get('/admin/mailing/get_mailing_data_fields', [
		'uses'	=> 'Admin\MailingController@getMailingDatafields'
	]);
	Route::post('/admin/mailing/add_pattern', [
		'uses'	=> 'Admin\MailingController@mailingAddPattern'
	]);
	Route::post('/admin/mailing/add', [
		'uses'	=> 'Admin\MailingController@mailingAddTemplate'
	]);
	Route::delete('/admin/mailing/drop_pattern', [
		'uses'	=> 'Admin\MailingController@mailingDropPattern'
	]);
	Route::delete('/admin/mailing/drop',[
		'uses'	=> 'Admin\MailingController@mailingDrop'
	]);

	//MENU EDIT
	Route::get('/admin/menu_settings', [
		'as'	=> 'admin-menu-settings',
		'uses'	=> 'Admin\PagesController@menu_settings'
	]);
	//USER ROLES
	Route::get('/admin/user_roles', [
		'as'	=> 'admin-user-roles',
		'uses'	=> 'Admin\PagesController@user_roles'
	]);
	Route::get('/admin/user_roles/add', [
		'as'	=> 'admin-user-roles-add-page',
		'uses'	=> 'Admin\PagesController@user_rolesAddPage'
	]);
	Route::get('/admin/user_roles/edit/{id}', [
		'as'	=> 'admin-user-roles-edit-page',
		'uses'	=> 'Admin\PagesController@user_rolesEditPage'
	]);
	Route::post('/admin/user_roles/add', [
		'uses'	=> 'Admin\UserController@user_rolesAdd'
	]);
	Route::delete('/admin/user_roles/delete', [
		'uses'	=> 'Admin\UserController@user_rolesDelete'
	]);
	//USERS
	Route::get('/admin/users', [
		'as'	=> 'admin-users',
		'uses'	=> 'Admin\PagesController@usersPage'
	]);
	Route::get('/admin/users/edit/{id}', [
		'as'	=> 'admin-users-edit-page',
		'uses'	=> 'Admin\PagesController@usersEditPage'
	]);
	Route::post('/admin/users/edit', [
		'uses'	=> 'Admin\UserController@userEdit'
	]);
	Route::delete('/admin/users/delete', [
		'uses'	=> 'Admin\UserController@userDelete'
	]);

	Route::put('/admin/change_position', [
		'uses'	=> 'Admin\SimilarQueriesController@changePosition'
	]);

	//Content Pages
	Route::get('/admin/pages', [
		'as'	=> 'admin-pages',
		'uses'	=> 'Admin\PagesController@contentPages'
	]);
	Route::get('/admin/pages/add',[
		'as'	=> 'admin-pages-add-page',
		'uses'	=> 'Admin\PagesController@contentPagesAddPage'
	]);
	Route::get('/admin/pages/edit/{id?}', [
		'uses'	=> 'Admin\PagesController@contentPagesEditPage'
	]);
	Route::post('/admin/pages/add', [
		'uses'	=> 'Admin\ContentPagesController@addContentPage'
	]);
	Route::get('/admin/get_template_fields', [
		'uses'	=> 'Admin\ContentPagesController@getTemplateFields'
	]);
	Route::put('/admin/pages/enable', [
		'uses'	=> 'Admin\ModuleController@modulesChangeEnabled'
	]);
	Route::delete('/admin/pages/drop', [
		'uses'	=> 'Admin\ModuleController@modulesDropElement'
	]);

	//MODULES
	Route::get('/admin/modules', [
		'as'	=> 'admin-modules',
		'uses'	=> 'Admin\PagesController@modules'
	]);
	Route::get('/admin/modules/add/{id?}', [
		'as'	=> 'admin-modules-add-page',
		'uses'	=> 'Admin\PagesController@moduleAdd'
	]);
	Route::get('/admin/modules/edit/{id?}', [
		'as'	=> 'admin-modules-edit-page',
		'uses'	=> 'Admin\PagesController@moduleView'
	]);
	Route::post('/admin/modules/add', [
		'as'	=> 'admin-modules-add',
		'uses'	=> 'Admin\ModuleController@addModule'
	]);
	Route::put('/admin/modules/enable', [
		'uses'	=> 'Admin\ModuleController@enableModule'
	]);
	Route::put('/admin/modules/change_postion', [
		'uses'	=> 'Admin\ModuleController@changeModulePosition'
	]);
	Route::delete('/admin/modules/drop', [
		'uses'	=> 'Admin\ModuleController@dropModule'
	]);

	Route::get('/admin/modules/get_module_default_settings', [
		'uses'	=> 'Admin\SimilarQueriesController@getModuleSettings'
	]);
	Route::get('/admin/get_modules_list', [
		'uses'	=> 'Admin\SimilarQueriesController@getModulesList'
	]);

	$tables = \DB::select('SHOW TABLES');
	$admin_menu_isset = false;
	foreach($tables as $table){
		$field = 'Tables_in_'.env('DB_DATABASE');
		if($table->$field == 'tbl_admin_menu'){
			$admin_menu_isset = true;
			break;
		}
	}
	if($admin_menu_isset){
		$modules = \DB::table('tbl_admin_menu')->select('slug','module_id')->where('module_id','!=',0)->get();
		foreach($modules as $module){
			Route::get('/admin/'.$module->slug, [
				'uses' => 'Admin\PagesController@modulesContentView'
			]);
			Route::get('/admin/'.$module->slug.'/add', [
				'uses' => 'Admin\PagesController@modulesContentAdd'
			]);
			Route::get('/admin/'.$module->slug.'/edit/{id}', [
				'uses' => 'Admin\PagesController@modulesContentEdit'
			]);
			Route::post('/admin/'.$module->slug.'/add', [
				'uses' => 'Admin\ModuleController@modulesContentAdd'
			]);

			Route::put('/admin/'.$module->slug.'/enable', [
				'uses' => 'Admin\ModuleController@modulesChangeEnabled'
			]);
			Route::put('/admin/'.$module->slug.'/change_position', [
				'uses' => 'Admin\ModuleController@modulesChangePosition'
			]);
			Route::delete('/admin/'.$module->slug.'/drop', [
				'uses' => 'Admin\ModuleController@modulesDropElement'
			]);
		}
	}
});

Route::get('/{slug}',[
	'uses'  => 'Site\PagesController@simplePages'
]);