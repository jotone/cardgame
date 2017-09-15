<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Articles;
use App\Categories;
use App\ContentPages;
use App\EnabledModules;
use App\Http\Controllers\Supply\Functions;
use App\Http\Controllers\Supply\Helpers;
use App\MenuItems;
use App\Modules;
use App\PageContent;
use App\Products;
use App\Promo;
use App\Requests301;
use App\Subscribers;
use App\User;
use App\UserRoles;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
use Illuminate\Support\Facades\Redirect;
use Validator;

class PagesController extends BaseController
{
	public function loginPage(){
		return view('admin.login');
	}

	public function index(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			return view('admin.home', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title
			]);
		}
	}

	public function modules(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			$modules = Modules::select('id','title','slug','description','options')->orderBy('title','asc')->get();
			$modules_list = [];
			foreach($modules as $module){
				$modules_list[] = [
					'id'	=> $module->id,
					'title'	=> $module->title,
					'slug'	=> $module->slug,
					'description' => $module->description
				];
			}

			$enabled_modules = EnabledModules::orderBy('position','asc')->get();
			$enabled_modules_list = [];

			foreach($enabled_modules as $module){
				$module_type = Modules::select('id', 'title')->find($module->type);

				$disabled_fields = unserialize($module->disabled_fields);
				$disabled_fields_list = [];
				foreach($disabled_fields as $field){
					switch($field){
						case 'date_begin': $disabled_fields_list[] = 'Дата начала'; break;
						case 'date_finish': $disabled_fields_list[] = 'Дата окончания'; break;
						case 'description': $disabled_fields_list[] = 'Описание'; break;
						case 'img_url': $disabled_fields_list[] = 'Слайдер изображений'; break;
						case 'text_caption': $disabled_fields_list[] = 'Заглавие текста'; break;
						case 'text': $disabled_fields_list[] = 'Текст'; break;
					}
				}

				$custom_fields = unserialize($module->custom_fields);
				$custom_fields_list = [];
				foreach($custom_fields as $field){
					$custom_fields_list[] = $field->capt;
				}

				$enabled_modules_list[] = [
					'id'			=> $module->id,
					'title'			=> $module->title,
					'slug'			=> $module->slug,
					'type'			=> $module_type->title,
					'description'	=> $module->description,
					'disabled_fields'=>$disabled_fields_list,
					'custom_fields'	=> $custom_fields_list,
					'enabled'		=> $module->enabled,
					'created_at'	=> Functions::convertDate($module->created_at),
					'updated_at'	=> Functions::convertDate($module->updated_at)
				];
			}

			return view('admin.modules', [
				'menu'		=> $menu,
				'start'		=> $start,
				'page_title'=> $page_caption->title,
				'modules'	=> $modules_list,
				'enabled_modules' => $enabled_modules_list
			]);
		}
	}

	public function moduleAdd($id, Request $request){
		$path = explode('/',$request->path());
		array_pop($path);
		array_pop($path);
		$path = implode('/',$path);
		$allow_access = Functions::checkAccessToPage($path);

		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$path.'%')->first();

			$modules = Modules::select('id','title','slug','description','options')->orderBy('title','asc')->get();
			$modules_list = [];
			foreach($modules as $module){
				$modules_list[] = [
					'id'	=> $module->id,
					'title'	=> $module->title,
					'slug'	=> $module->slug,
					'description' => $module->description
				];
			}

			return view('admin.add.modules', [
				'menu'		=> $menu,
				'start'		=> $start,
				'page_title'=> $page_caption->title.' &rarr; Добавление',
				'modules'	=> $modules_list,
				'id'		=> $id
			]);
		}
	}

	public function moduleView($id, Request $request){
		$path = explode('/',$request->path());
		array_pop($path);
		array_pop($path);
		$path = implode('/',$path);
		$allow_access = Functions::checkAccessToPage($path);

		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$path.'%')->first();

			$modules = Modules::select('id','title','slug','description','options')->orderBy('title','asc')->get();
			$modules_list = [];
			foreach($modules as $module){
				$modules_list[] = [
					'id'	=> $module->id,
					'title'	=> $module->title,
					'slug'	=> $module->slug,
					'description' => $module->description
				];
			}

			$enabled_module = EnabledModules::find($id);
			$module_data = [
				'id'			=> $enabled_module->id,
				'title'			=> $enabled_module->title,
				'slug'			=> $enabled_module->slug,
				'unique_slug'	=> ($enabled_module->unique_slug == 1)? 'checked="checked"': '',
				'description'	=> $enabled_module->description,
				'disabled_fields'=>unserialize($enabled_module->disabled_fields),
				'custom_fields'	=> unserialize($enabled_module->custom_fields),
			];

			return view('admin.add.modules', [
				'menu'		=> $menu,
				'start'		=> $start,
				'page_title'=> $page_caption->title.' &rarr; Добавление',
				'modules'	=> $modules_list,
				'id'		=> $enabled_module->type,
				'data'		=> $module_data
			]);
		}
	}

	public function settings(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			$content = PageContent::select('title','content')->where('type','=','settings')->get();
			$data_list = [];
			foreach ($content as $item){
				$data_list[$item->title] = (Functions::is_serialized($item->content))? unserialize($item->content): $item->content;
			}
			return view('admin.settings', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title'=> $page_caption->title,
				'content'	=> $data_list
			]);
		}
	}

	public function dispatch(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			$mails = Subscribers::orderBy('email','asc')->get();

			$templates = PageContent::select('title','caption')->where('type','=','dispatch')->orderBy('title','asc')->get();
			return view('admin.dispatch', [
				'menu'		=> $menu,
				'start'		=> $start,
				'page_title'=> $page_caption->title,
				'content'	=> $mails,
				'templates' => $templates
			]);
		}
	}

	public function mailing(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();
			$mail_templates = PageContent::select('id','title','type','content','created_at','updated_at')
				->where('type','=','mail_template')
				->get();
			$templates = [];
			foreach($mail_templates as $mail_template){
				$content = unserialize($mail_template->content);
				$templates[] = [
					'id'		=> $mail_template->id,
					'title'		=> $mail_template->title,
					'sender'	=> $content['sender'],
					'receiver'	=>$content['receiver'],
					'created_at'=>Functions::convertDate($mail_template->created_at),
					'updated_at'=>Functions::convertDate($mail_template->updated_at)
				];
			}

			return view('admin.mailing', [
				'menu'		=> $menu,
				'start'		=> $start,
				'page_title'=> $page_caption->title,
				'content'	=> $templates
			]);
		}
	}

	public function mailingAdd(Request $request){
		$path = explode('/',$request->path());
		unset($path[count($path)-1]);
		$path = implode('/',$path);
		$allow_access = Functions::checkAccessToPage($path);
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$path.'%')->first();

			$forbidden_modules = Modules::select('id')->where('slug','=','menu')->orWhere('slug','=','pages')->get();

			$enabled_modules = EnabledModules::select('id','title','slug','type');
			foreach($forbidden_modules as $forbidden_module){
				$enabled_modules = $enabled_modules->where('type','!=',$forbidden_module->id);
			}
			$enabled_modules = $enabled_modules->orderBy('type','asc')->orderBy('title','asc')->get();

			$dynamic_data_list = [
				'users' => [
					'title'	=> 'Пользователи',
					'data'	=> [['caption'	=> 'Пользователи', 'id'=>'0', 'module_slug'=>'users']]
				]
			];
			foreach($enabled_modules as $module){
				$module_type = Modules::select('slug','title')->where('id','=',$module->type)->first();
				$dynamic_data_list[$module_type->slug] = [
					'title'	=> $module_type->title,
					'data'	=> []
				];
			}

			foreach($enabled_modules as $module){
				$module_type = Modules::select('slug')->where('id','=',$module->type)->first();
				$dynamic_data_list[$module_type->slug]['data'][] = [
					'caption'	=> $module->title,
					'id'		=> $module->id,
					'module_slug' => $module->slug
				];
			}

			$mail_templates = PageContent::select('id','title','caption','content')->where('type','=','mail_pattern')->get();

			$mail_list = PageContent::select('content')->where('type','=','settings')->where('title','=','email')->first();
			return view('admin.add.mailing', [
				'menu'			=> $menu,
				'start'			=> $start,
				'page_title'	=> $page_caption->title,
				'dynamic_data_list' => $dynamic_data_list,
				'mail_templates'=> $mail_templates,
				'mail_list'		=> unserialize($mail_list->content)
			]);
		}
	}

	public function mailingEdit($id, Request $request){
		$path = explode('/',$request->path());
		unset($path[count($path)-1]);
		unset($path[count($path)-1]);
		$path = implode('/',$path);
		$allow_access = Functions::checkAccessToPage($path);
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$path.'%')->first();

			$forbidden_modules = Modules::select('id')->where('slug','=','menu')->orWhere('slug','=','pages')->get();

			$enabled_modules = EnabledModules::select('id','title','slug','type');
			foreach($forbidden_modules as $forbidden_module){
				$enabled_modules = $enabled_modules->where('type','!=',$forbidden_module->id);
			}
			$enabled_modules = $enabled_modules->orderBy('type','asc')->orderBy('title','asc')->get();

			$dynamic_data_list = [
				'users' => [
					'title'	=> 'Пользователи',
					'data'	=> [['caption'	=> 'Пользователи', 'id'=>'0', 'module_slug'=>'users']]
				]
			];
			foreach($enabled_modules as $module){
				$module_type = Modules::select('slug','title')->where('id','=',$module->type)->first();
				$dynamic_data_list[$module_type->slug] = [
					'title'	=> $module_type->title,
					'data'	=> []
				];
			}

			foreach($enabled_modules as $module){
				$module_type = Modules::select('slug')->where('id','=',$module->type)->first();
				$dynamic_data_list[$module_type->slug]['data'][] = [
					'caption'	=> $module->title,
					'id'		=> $module->id,
					'module_slug' => $module->slug
				];
			}

			$mail_templates = PageContent::select('id','title','caption','content')->where('type','=','mail_pattern')->get();

			$mail_list = PageContent::select('content')->where('type','=','settings')->where('title','=','email')->first();

			$letter_data = PageContent::select('id','title','content')->find($id);
			$content = unserialize($letter_data->content);

			$content_data = [
				'id'		=> $letter_data->id,
				'title'		=> $letter_data->title,
				'sender'	=> $content['sender'],
				'receiver'	=> $content['receiver'],
				'replyer'	=> $content['replyer'],
				'text'		=> $content['text']
			];
			return view('admin.add.mailing', [
				'menu'			=> $menu,
				'start'			=> $start,
				'page_title'	=> $page_caption->title,
				'dynamic_data_list' => $dynamic_data_list,
				'mail_templates'=> $mail_templates,
				'mail_list'		=> unserialize($mail_list->content),
				'content'		=> $content_data
			]);
		}
	}

	public function menu_settings(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			$admin_menu = Functions::getAssocList('admin_menu', 0);
			$menu_list = Functions::buildCategoriesView($admin_menu, false);
			return view('admin.menu_settings', [
				'menu'		=> $menu,
				'start'		=> $start,
				'page_title'=> $page_caption->title,
				'menu_list'	=> $menu_list,
			]);
		}
	}

	public function user_roles(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access) {
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			$roles = UserRoles::orderBy('title','asc')->get();
			$roles_list = [];
			foreach ($roles as $role) {
				$pages = 'Полный доступ';
				if($role->access_pages != 'deny_all'){
					$access_pages = unserialize($role->access_pages);
					if(!empty($access_pages)){
						$pages = '';
					}
					foreach($access_pages as $page_id){
						$page_data = AdminMenu::select('id','title')->find($page_id);
						$pages .= '<p>'.$page_data->title.'</p>';
					}
				}else{
					$pages = 'Полный запрет';
				}

				$roles_list[] = [
					'id'		=> $role->id,
					'title'		=> $role->title,
					'pseudonim'	=> $role->pseudonim,
					'editable'	=> $role->editable,
					'access_pages' => $pages,
					'created_at'=> Functions::convertDate($role->created_at),
					'updated_at'=> Functions::convertDate($role->updated_at)
				];
			}

			return view('admin.user_roles', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title,
				'roles'	=> $roles_list
			]);
		}
	}

	public function user_rolesAddPage(Request $request){
		$path = explode('/',$request->path());
		array_pop($path);
		$path = implode('/',$path);

		$allow_access = Functions::checkAccessToPage($path);
		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);

			$page_caption = AdminMenu::select('title', 'slug')->where('slug', 'LIKE', '%' . $path . '%')->first();

			$pages = Functions::assocListToFlat(Functions::getAssocList('admin_menu'));

			return view('admin.add.user_roles', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title . ' &rarr; Добавление',
				'pages'	=> $pages
			]);
		}
	}

	public function user_rolesEditPage($id, Request $request){
		$path = explode('/',$request->path());
		array_pop($path);
		array_pop($path);
		$path = implode('/',$path);

		$allow_access = Functions::checkAccessToPage($path);
		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);

			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$path.'%')->first();

			$pages = Functions::assocListToFlat(Functions::getAssocList('admin_menu'));

			$page_data = UserRoles::find($id);
			return view('admin.add.user_roles', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title.' &rarr; Редактирование',
				'pages'	=> $pages,
				'data'	=> $page_data
			]);
		}
	}

	public function usersPage(Request $request){
		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($request->path());
		$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access){
			$users = User::orderBy('login','asc')->get();
			$users_list = [];
			foreach($users as $user){
				$role = UserRoles::select('title','pseudonim')->where('pseudonim','=',$user->user_role)->first();
				$users_list[] = [
					'id'	=> $user->id,
					'login'	=> $user->login,
					'email'	=> $user->email,
					'name'	=> $user->name,
					'role'	=> $role->title,
					'created_at'=> Functions::convertDate($user->created_at),
					'updated_at'=> Functions::convertDate($user->updated_at)
				];
			}

			return view('admin.users', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title,
				'users'	=> $users_list
			]);
		}
	}

	public function usersEditPage($id, Request $request){
		$path = explode('/',$request->path());
		array_pop($path);
		array_pop($path);
		$path = implode('/',$path);

		$allow_access = Functions::checkAccessToPage($path);
		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($path);
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$path.'%')->first();

			$roles = UserRoles::orderBy('title','asc')->get();
			$user = User::find($id);
			return view('admin.add.users', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title.' &rarr; Редактирование',
				'user'	=> $user,
				'roles'	=> $roles
			]);
		}
	}

	//Content pages list
	public function contentPages(Request $request){
		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($request->path());
		$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();
		$request_data = $request->all();

		$active_direction = ['sort'=>'title', 'dir'=>'asc'];
		$pages = ContentPages::select('id','title','slug','img_url','author','views','module_id','enabled','published_at','created_at','updated_at');

		if(isset($request_data['sort_by'])){
			$direction = ((isset($request_data['dir'])) && ($request_data['dir'] == 'asc'))? 'asc': 'desc';
			$active_direction = ['sort'=>$request_data['sort_by'], 'dir'=>$request_data['dir']];
			switch($request_data['sort_by']){
				case 'title':	$pages = $pages->orderBy('title',$direction); break;
				case 'slug':	$pages = $pages->orderBy('slug',$direction); break;
				case 'author':	$pages = $pages->orderBy('author',$direction); break;
				case 'views':	$pages = $pages->orderBy('views',$direction); break;
				case 'template':$pages = $pages->orderBy('module_id',$direction); break;
				case 'published':$pages =$pages->orderBy('enabled',$direction)->orderBy('published_at',$direction); break;
				case 'created':	$pages = $pages->orderBy('created_at',$direction); break;
				case 'updated':	$pages = $pages->orderBy('updated_at',$direction); break;
			}
		}else{
			$pages = $pages->orderBy('title','asc');
		}
		$pages = $pages->paginate(25);

		$paginate_options = [
			'next_page'		=> $pages->nextPageUrl().'&sort_by='.$active_direction['sort'].'&dir='.$active_direction['dir'],
			'current_page'	=> $pages->currentPage(),
			'last_page'		=> $pages->lastPage(),
			'sort_by'		=> $active_direction['sort'],
			'dir'			=> $active_direction['dir']
		];

		$data_list = [];
		foreach($pages as $page){
			$images = unserialize($page->img_url);
			$image = (!empty($images))? $images[0]['img']: '';

			$enabled = ($page->enabled == 1)? 'checked="checked"': '';

			if($page->module_id != 0){
				$used_template = EnabledModules::select('title')->find($page->module_id);
				$template = $used_template->title;
			}else{
				$template = 'По умолчанию';
			}
			$data_list[] = [
				'id'		=> $page->id,
				'title'		=> $page->title,
				'slug'		=> $page->slug,
				'img_url'	=> $image,
				'author'	=> $page->author,
				'views'		=> $page->views,
				'module_id'	=> $template,
				'enabled'	=> $enabled,
				'published_at' => Functions::convertDate($page->published_at),
				'created_at'=> Functions::convertDate($page->created_at),
				'updated_at'=> Functions::convertDate($page->updated_at)
			];
		}

		return view('admin.pages', [
			'menu'		=> $menu,
			'start'		=> $start,
			'page_title'=> $page_caption->title,
			'pages'		=> $data_list,
			'active_direction' => $active_direction,
			'pagination'=> $paginate_options,
		]);
	}

	//Content Pages add page
	public function contentPagesAddPage(Request $request){
		$path = explode('/',$request->path());
		unset($path[count($path)-1]);
		$path = implode('/',$path);

		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($path);

		$template_module_type = Modules::select('id','options')->where('slug','=','pages')->first();
		$enabled_templates = EnabledModules::select('id','title')->where('type','=',$template_module_type->id)->get();
		$default = unserialize($template_module_type->options);
		$default_disabled = [];
		foreach($default as $item) {
			$default_disabled[] = [
				'enabled'	=> 1,
				'type'		=> $item['name']
			];
		}
		$templates = [];
		$templates[] = [
			'id'	=> 0,
			'title'	=> 'По умолчанию'
		];
		foreach($enabled_templates as $template){
			$templates[] = [
				'id'	=> $template->id,
				'title'	=> $template->title,
			];
		}

		$links = MenuItems::select('slug','title')->where('slug','NOT LIKE','%#%')->distinct('slug')->orderBy('title','asc')->get();
		return view('admin.add.pages', [
			'menu'		=> $menu,
			'start'		=> $start,
			'page_title'=> 'Добавление страницы',
			'templates'	=> $templates,
			'content'	=> [],
			'links'		=> $links
		]);
	}

	//Content Page edit page
	public function contentPagesEditPage($id, Request $request){
		$path = explode('/',$request->path());
		$slug = $path[1];
		unset($path[count($path)-1]);
		unset($path[count($path)-1]);
		$path = implode('/',$path);
		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($path);

		$template_module_type = Modules::select('id','options')->where('slug','=','pages')->first();
		$enabled_templates = EnabledModules::select('id','title')->where('type','=',$template_module_type->id)->get();
		$default = unserialize($template_module_type->options);
		$default_disabled = [];
		foreach($default as $item) {
			$default_disabled[] = [
				'enabled'	=> 1,
				'type'		=> $item['name']
			];
		}
		$templates = [];
		$templates[] = [
			'id'	=> 0,
			'title'	=> 'По умолчанию'
		];
		foreach($enabled_templates as $template){
			$templates[] = [
				'id'	=> $template->id,
				'title'	=> $template->title,
			];
		}

		$page = ContentPages::select('id','title','slug','meta_title','meta_description','meta_keywords','enabled','module_id')->find($id);
		$links = MenuItems::select('slug','title')->where('slug','NOT LIKE','%#%')->distinct('slug')->orderBy('title','asc')->get();
		$content = [
			'id'			=> $page->id,
			'title'			=> $page->title,
			'slug'			=> $page->slug,
			'meta_title'	=> $page->meta_title,
			'meta_description' => $page->meta_description,
			'meta_keywords'	=> $page->meta_keywords,
			'enabled'		=> ($page->enabled == 1)? 'checked="checked"': '',
			'module_id'		=> $page->module_id
		];

		return view('admin.add.pages', [
			'menu'		=> $menu,
			'start'		=> $start,
			'page_title'=> 'Добавление страницы',
			'templates'	=> $templates,
			'content'	=> $content,
			'links'		=> $links
		]);
	}

	//View module content list
	public function modulesContentView(Request $request){
		$path = explode('/',$request->path());
		$slug = $path[1];
		$path = implode('/',$path);
		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($path);
		$request_data = $request->all();

		$module = EnabledModules::select('id','title','slug','type')->where('slug','=',$slug)->first();

		$module_type = Modules::select('slug')->find($module->type);
		switch($module_type->slug){
			case 'categories':
				$active_direction = [];
				$data_content = Functions::getAssocList('categories', 0, $module->id);
				$data_list = Functions::buildCategoriesView($data_content, true, $module->slug);
				$paginate_options = [];
			break;
			case 'articles':
				$active_direction = ['sort'=>'created', 'dir'=>'desc'];
				$data_content = Articles::select('id','title','slug','img_url','author','views','enabled','published_at','created_at','updated_at')
					->where('module_id','=',$module->id);
				if(isset($request_data['sort_by'])){
					$direction = ((isset($request_data['dir'])) && ($request_data['dir'] == 'asc'))? 'asc': 'desc';
					$active_direction = ['sort'=>$request_data['sort_by'], 'dir'=>$request_data['dir']];
					switch($request_data['sort_by']){
						case 'title':	$data_content = $data_content->orderBy('title',$direction); break;
						case 'slug':	$data_content = $data_content->orderBy('slug',$direction); break;
						case 'author':	$data_content = $data_content->orderBy('author',$direction); break;
						case 'views':	$data_content = $data_content->orderBy('views',$direction); break;
						case 'published':$data_content = $data_content->orderBy('enabled',$direction)->orderBy('published_at',$direction); break;
						case 'created':	$data_content = $data_content->orderBy('created_at',$direction); break;
						case 'updated':	$data_content = $data_content->orderBy('updated_at',$direction); break;
					}
				}else{
					$data_content = $data_content->orderBy('created_at','desc');
				}
				$data_content = $data_content->paginate(25);

				$paginate_options = [
					'next_page'		=> $data_content->nextPageUrl().'&sort_by='.$active_direction['sort'].'&dir='.$active_direction['dir'],
					'current_page'	=> $data_content->currentPage(),
					'last_page'		=> $data_content->lastPage(),
					'sort_by'		=> $active_direction['sort'],
					'dir'			=> $active_direction['dir']
				];

				$data_list = [];
				foreach($data_content as $item){
					$images = unserialize($item->img_url);
					$image = (!empty($images))? $images[0]['img']: '';
					$enabled = ($item->enabled == 1)? 'checked="checked"': '';
					$data_list[] = [
						'id'		=> $item->id,
						'title'		=> $item->title,
						'slug'		=> $item->slug,
						'img_url'	=> $image,
						'author'	=> $item->author,
						'views'		=> $item->views,
						'enabled'	=> $enabled,
						'published_at' => Functions::convertDate($item->published_at),
						'created_at'=> Functions::convertDate($item->created_at),
						'updated_at'=> Functions::convertDate($item->updated_at)
					];
				}
			break;

			case 'menu':
				$active_direction = [];
				$data_content = Functions::getAssocList('site_menu', 0, $module->id);
				$data_list = Functions::buildCategoriesView($data_content, true, $module->slug);
				$paginate_options = [];
			break;

			case 'products':
				$active_direction = ['sort'=>'created', 'dir'=>'desc'];
				$data_content = Products::select('id','title','slug','img_url','price','author','views','custom_fields','enabled','published_at','created_at','updated_at')
					->where('module_id','=',$module->id);
				if(isset($request_data['sort_by'])){
					$direction = ((isset($request_data['dir'])) && ($request_data['dir'] == 'asc'))? 'asc': 'desc';
					$active_direction = ['sort'=>$request_data['sort_by'], 'dir'=>$request_data['dir']];
					switch($request_data['sort_by']){
						case 'title':	$data_content = $data_content->orderBy('title',$direction); break;
						case 'slug':	$data_content = $data_content->orderBy('slug',$direction); break;
						case 'author':	$data_content = $data_content->orderBy('author',$direction); break;
						case 'price':	$data_content = $data_content->orderBy('price',$direction); break;
						case 'published':$data_content = $data_content->orderBy('enabled',$direction)->orderBy('published_at',$direction); break;
						case 'created':	$data_content = $data_content->orderBy('created_at',$direction); break;
						case 'updated':	$data_content = $data_content->orderBy('updated_at',$direction); break;
					}
				}else{
					$data_content = $data_content->orderBy('updated_at','desc');
				}
				$data_content = $data_content->paginate(25);

				$paginate_options = [
					'next_page'		=> $data_content->nextPageUrl().'&sort_by='.$active_direction['sort'].'&dir='.$active_direction['dir'],
					'current_page'	=> $data_content->currentPage(),
					'last_page'		=> $data_content->lastPage(),
					'sort_by'		=> $active_direction['sort'],
					'dir'			=> $active_direction['dir']
				];

				$data_list = [];
				foreach($data_content as $item){
				    $custom_field = Helpers::convertCustomFields(unserialize($item->custom_fields));
					$images = unserialize($item->img_url);
					$image = (!empty($images))? $images[0]['img']: '';
					$enabled = ($item->enabled == 1)? 'checked="checked"': '';
					$data_list[] = [
						'id'		=> $item->id,
						'title'		=> $item->title,
						'slug'		=> $item->slug,
						'price'		=> $item->price,
						'img_url'	=> $image,
						'author'	=> $item->author,
						'views'		=> $item->views,
						'enabled'	=> $enabled,
						'published_at' => Functions::convertDate($item->published_at),
						'created_at'=> Functions::convertDate($item->created_at),
						'updated_at'=> Functions::convertDate($item->updated_at)
					];
				}
			break;
			case 'promo':
				$active_direction = ['sort'=>'created', 'dir'=>'desc'];
				$data_content = Promo::select('id','title','slug','img_url','discount','date_start','date_finish','author','views','enabled','published_at','created_at','updated_at')
					->where('module_id','=',$module->id);
				if(isset($request_data['sort_by'])){
					$direction = ((isset($request_data['dir'])) && ($request_data['dir'] == 'asc'))? 'asc': 'desc';
					$active_direction = ['sort'=>$request_data['sort_by'], 'dir'=>$request_data['dir']];
					switch($request_data['sort_by']){
						case 'title':	$data_content = $data_content->orderBy('title',$direction); break;
						case 'slug':	$data_content = $data_content->orderBy('slug',$direction); break;
						case 'author':	$data_content = $data_content->orderBy('author',$direction); break;
						case 'discount':$data_content = $data_content->orderBy('discount',$direction); break;
						case 'dates':	$data_content = $data_content->orderBy('date_start',$direction); break;
						case 'views':	$data_content = $data_content->orderBy('views',$direction); break;
						case 'published':$data_content = $data_content->orderBy('enabled',$direction)->orderBy('published_at',$direction); break;
						case 'created':	$data_content = $data_content->orderBy('created_at',$direction); break;
						case 'updated':	$data_content = $data_content->orderBy('updated_at',$direction); break;
					}
				}else{
					$data_content = $data_content->orderBy('created_at','desc');
				}
				$data_content = $data_content->paginate(25);

				$paginate_options = [
					'next_page'		=> $data_content->nextPageUrl().'&sort_by='.$active_direction['sort'].'&dir='.$active_direction['dir'],
					'current_page'	=> $data_content->currentPage(),
					'last_page'		=> $data_content->lastPage(),
					'sort_by'		=> $active_direction['sort'],
					'dir'			=> $active_direction['dir']
				];

				$data_list = [];
				foreach($data_content as $item){
					$images = unserialize($item->img_url);
					$image = (!empty($images))? $images[0]['img']: '';
					$enabled = ($item->enabled == 1)? 'checked="checked"': '';
					$discount = unserialize($item->discount);
					$discount_value = ($discount->type == 'percent')? $discount->value.'%': '&minus;'.$discount->value;
					$data_list[] = [
						'id'		=> $item->id,
						'title'		=> $item->title,
						'slug'		=> $item->slug,
						'img_url'	=> $image,
						'discount'	=> $discount_value,
						'author'	=> $item->author,
						'views'		=> $item->views,
						'enabled'	=> $enabled,
						'date_start'	=> substr(Functions::convertDate($item->date_start),0,-5),
						'date_finish'	=> substr(Functions::convertDate($item->date_finish),0,-5),
						'published_at'	=> Functions::convertDate($item->published_at),
						'created_at'=> Functions::convertDate($item->created_at),
						'updated_at'=> Functions::convertDate($item->updated_at)
					];
				}
			break;
		}
		if(isset($data_list)) {
			return view('admin.'.$module_type->slug, [
				'menu'			=> $menu,
				'start'			=> $start,
				'page_title'	=> $module->title,
				'global_slug'	=> $slug,
				'module_id'		=> $module->id,
				'content'		=> $data_list,
				'active_direction' => $active_direction,
				'pagination'	=> $paginate_options
			]);
		}
	}

	//Adding Page content module
	public function modulesContentAdd(Request $request){
		$path = explode('/',$request->path());
		$slug = $path[1];
		unset($path[count($path)-1]);
		$path = implode('/',$path);

		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($path);

		$module_data = EnabledModules::select('id','title','type','disabled_fields','custom_fields')
			->where('slug','=',$slug)
			->first();

		$module_type = Modules::select('slug')->find($module_data->type);
		switch($module_type->slug){
			case 'categories':
				$categories = Categories::select('id','title')
					->where('module_id', '=', $module_data->id)
					->orderBy('refer_to')
					->orderBy('title','asc')
					->get();

				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Добавление категории в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
					'categories'=> $categories
				];
			break;

			case 'articles':
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Добавление статьи в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
				];
			break;

			case 'menu':
				$menu_items = MenuItems::select('id','title')->where('module_id', '=', $module_data->id)->get();
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Добавление  меню в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
					'menu_items'=> $menu_items
				];
			break;

			case 'products':
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Добавление в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
				];
			break;
			case 'promo':
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Добавление в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
				];
			break;
		}
		if(isset($fields)){
			return view('admin.add.'.$module_type->slug, [
				'menu'	=> $menu,
				'start'	=> $start,
				'fields'=> $fields,
				'content'  => []
			]);
		}
	}

	//Editing Page content module
	public function modulesContentEdit($id, Request $request){
		$path = explode('/',$request->path());
		$slug = $path[1];
		unset($path[count($path)-1]);
		unset($path[count($path)-1]);
		$path = implode('/',$path);
		$start = Functions::getMicrotime();
		$menu = Functions::buildMenuList($path);

		$marks_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();

		$module_data = EnabledModules::select('id','title','type','disabled_fields','custom_fields')
			->where('slug','=',$slug)
			->first();

		$module_type = Modules::select('slug')->find($module_data->type);
		switch($module_type->slug){
			case 'categories':
				$data = Categories::select('id','title','slug','refer_to','img_url','description','text','custom_fields')->find($id);
				$custom_fields = unserialize($data->custom_fields);
				foreach($custom_fields as $iter => $custom_data){
					foreach($custom_data as $field_title => $field){
						if($field['caption'] == 'Позиции Марок и моделей'){
							$marks = Categories::select('id','position','refer_to')
								->where('module_id','=',$marks_module->id)
								->where('enabled','=',1)
								->get();
							foreach($marks as $mark){
								$allow_to_add = true;
								foreach($field['value'] as $item){
									if($item->id == $mark->id){
										$allow_to_add = false;
										break;
									}
								}

								if($allow_to_add){
									$custom_fields[$iter][$field_title]['value'][] = json_decode(json_encode($mark->getAttributes()));
								}
							}
						}
					}
				}
				$page_data = [
					'id'		=> $data->id,
					'title'		=> $data->title,
					'slug'		=> $data->slug,
					'refer_to'	=> $data->refer_to,
					'img_url'	=> unserialize($data->img_url),
					'description'=> $data->description,
					'text'		=> $data->text,
					'custom_fields'=> $custom_fields
				];

				$categories = Categories::select('id','title')
					->where('module_id', '=', $module_data->id)
					->where('id', '!=', $id)
					->get();

				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Редактирование категории в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
					'categories'=> $categories
				];
			break;

			case 'articles':
				$data = Articles::select('id','title','slug','img_url','description','text_caption','text','custom_fields','meta_title','meta_description','meta_keywords','enabled')->find($id);
				$page_data = [
					'id'			=> $data->id,
					'title'			=> $data->title,
					'slug'			=> $data->slug,
					'img_url'		=> unserialize($data->img_url),
					'description'	=> $data->description,
					'text_caption'	=> $data->text_caption,
					'text'			=> $data->text,
					'meta_title'	=> $data->meta_title,
					'meta_keywords'	=> $data->meta_keywords,
					'meta_description' => $data->meta_description,
					'custom_fields'	=> unserialize($data->custom_fields),
					'enabled'		=> ($data->enabled == 1)? 'checked="checked"': ''
				];
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Редактирование "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
				];
			break;

			case 'menu':
				$data = MenuItems::select('id','title','slug','custom_fields','refer_to','enabled','active')->find($id);
				$page_data = [
					'id'			=> $data->id,
					'title'			=> $data->title,
					'slug'			=> $data->slug,
					'custom_fields'	=> unserialize($data->custom_fields),
					'refer_to'		=> $data->refer_to,
					'enabled'		=> ($data->enabled == 1)? 'checked="checked"': '',
					'active'		=> $data->active
				];
				$menu_items = MenuItems::select('id','title')
					->where('module_id', '=', $module_data->id)
					->where('id','!=',$data->id)
					->get();
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Редактирование меню в "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
					'menu_items'	=> $menu_items
				];
			break;

			case 'products':
				$data = Products::select('id','title','slug','img_url','description','text','price','color','custom_fields','meta_title','meta_description','meta_keywords','enabled')->find($id);
				$page_data = [
					'id'			=> $data->id,
					'title'			=> $data->title,
					'slug'			=> $data->slug,
					'img_url'		=> unserialize($data->img_url),
					'description'	=> $data->description,
					'price'			=> $data->price,
					'color'			=> unserialize($data->color),
					'text'			=> $data->text,
					'meta_title'	=> $data->meta_title,
					'meta_keywords'	=> $data->meta_keywords,
					'meta_description' => $data->meta_description,
					'custom_fields'	=> unserialize($data->custom_fields),
					'enabled'		=> ($data->enabled == 1)? 'checked="checked"': ''
				];
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Редактирование "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
				];
			break;

			case 'promo':
				$data = Promo::select('id','title','slug','img_url','description','text','discount','date_start','date_finish','custom_fields','meta_title','meta_description','meta_keywords','enabled')->find($id);
				$discount = unserialize($data->discount);
				$discout_value = [
					'value'		=> $discount->value,
					'percent'	=> ($discount->type == 'percent')? 'checked="checked"': '',
					'minus'		=> ($discount->type == 'minus')? 'checked="checked"': '',
				];
				$page_data = [
					'id'			=> $data->id,
					'title'			=> $data->title,
					'slug'			=> $data->slug,
					'img_url'		=> unserialize($data->img_url),
					'description'	=> $data->description,
					'text'			=> $data->text,
					'discount'		=> $discout_value,
					'date_start'	=> $data->date_start,
					'date_finish'	=> $data->date_finish,
					'meta_title'	=> $data->meta_title,
					'meta_keywords'	=> $data->meta_keywords,
					'meta_description' => $data->meta_description,
					'custom_fields'	=> unserialize($data->custom_fields),
					'enabled'		=> ($data->enabled == 1)? 'checked="checked"': ''
				];
				$fields = [
					'id'		=> $module_data->id,
					'title'		=> 'Редактирование "'.$module_data->title.'"',
					'disabled_fields'=> unserialize($module_data->disabled_fields),
					'custom_fields'	=> unserialize($module_data->custom_fields),
				];
			break;
		}
		if(isset($page_data)){
			return view('admin.add.'.$module_type->slug, [
				'menu'	=> $menu,
				'start'	=> $start,
				'fields'=> $fields,
				'content' => $page_data
			]);
		}
	}

	public function redirects(Request $request){
		$allow_access = Functions::checkAccessToPage($request->path());
		if($allow_access){
			$start = Functions::getMicrotime();
			$menu = Functions::buildMenuList($request->path());
			$page_caption = AdminMenu::select('title','slug')->where('slug','LIKE','%'.$request->path().'%')->first();

			$redirects = Requests301::orderBy('link_from','asc')->get();

			return view('admin.redirects', [
				'menu'	=> $menu,
				'start'	=> $start,
				'page_title' => $page_caption->title,
				'content' => $redirects
			]);
		}
	}
}