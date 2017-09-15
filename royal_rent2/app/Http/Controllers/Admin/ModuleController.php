<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Articles;
use App\Categories;
use App\ContentPages;
use App\EnabledModules;
use App\Http\Controllers\Supply\Functions;
use App\MenuItems;
use App\Modules;
use App\Products;
use App\Promo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
use Validator;

class ModuleController extends BaseController
{
	public function addModule(Request $request){
		$data = $request->all();
		$module_type = Modules::select('slug')->find($data['type']);
		if( (isset($data['id'])) && (!empty($data['id'])) ){
			$result = EnabledModules::find($data['id']);

			$admin_menu_item_isset = AdminMenu::where('slug','=',Functions::str2url($data['slug']))
				->where('module_id','=',0)
				->count();
			$admin_menu_module_isset = AdminMenu::where('slug','=',Functions::str2url($data['slug']))
				->where('module_id','!=',0)
				->count();

			$possible_slug_count = (Functions::str2url($data['slug']) != $result ->slug)? 1: 2;
			if( ($admin_menu_item_isset < 1) && ($admin_menu_module_isset < $possible_slug_count) ){
				$admin_menu_item_isset = AdminMenu::where('slug','=',$result->slug)->first();
				$result ->title			= $data['title'];
				$result ->slug			= Functions::str2url($data['slug']);
				$result ->unique_slug	= $data['unique_slug'];
				$result ->type			= $data['type'];
				$result ->description	= $data['description'];
				$result ->disabled_fields= serialize(json_decode($data['disabled_fields']));
				$result ->custom_fields	= serialize(json_decode($data['additional_data']));
				$result ->save();

				if($module_type->slug != 'pages'){
					$admin_menu_item_isset ->title = $result ->title;
					$admin_menu_item_isset ->slug = Functions::str2url($data['slug']);
					$admin_menu_item_isset ->save();
				}

				if($result != false){
					return json_encode([
						'message' => 'success'
					]);
				}
			}else{
				return json_encode([
					'message' => 'error',
					'type' => 'slug_isset'
				]);
			}
		}else{
			$module_isset = EnabledModules::select('slug')->where('slug','=',$data['slug'])->count();
			$last_element = EnabledModules::select('position')->orderBy('position','desc')->first();
			$position = (!empty($last_element))? $last_element->position+1: 0;
			if($module_isset == 0){
				$result = EnabledModules::create([
					'title'			=> $data['title'],
					'slug'			=> Functions::str2url($data['slug']),
					'unique_slug'	=> $data['unique_slug'],
					'type'			=> $data['type'],
					'description'	=> $data['description'],
					'disabled_fields'=> serialize(json_decode($data['disabled_fields'])),
					'custom_fields'	=> serialize(json_decode($data['additional_data'])),
					'position'		=> $position,
					'enabled'		=> 1
				]);
				if( ($result != false) && ($module_type->slug != 'pages') ){
					$admin_menu_item_isset = AdminMenu::where('slug','=',$result->slug)->count();
					if($admin_menu_item_isset < 1){
						$admin_menu_last = AdminMenu::where('refer_to','=',0)->orderBy('position','desc')->first();
						AdminMenu::create([
							'title'		=> $result->title,
							'slug'		=> $result->slug,
							'position'	=> $admin_menu_last->position + 1,
							'refer_to'	=> 0,
							'module_id'	=> $result->id,
							'enabled'	=> 1
						]);
					}else{
						return json_encode([
							'message' => 'error',
							'type' => 'slug_isset'
						]);
					}
				}
				if($result != false){
					return json_encode([
						'message' => 'success'
					]);
				}
			}else{
				return json_encode([
					'message' => 'error',
					'type' => 'slug_isset'
				]);
			}
		}
	}

	public function enableModule(Request $request){
		$data = $request->all();

		$module = EnabledModules::select('enabled')->find($data['id']);
		$enabled = ($module->enabled == 0)? 1: 0;
		$result = EnabledModules::where('id','=',$data['id'])->update([
			'enabled'=>$enabled
		]);
		if($result != false){
			return json_encode([
				'message' => 'success'
			]);
		}
	}

	public function changeModulePosition(Request $request){
		$data = $request->all();
		foreach($data['modules'] as $module_data){
			$result = EnabledModules::where('id','=',$module_data['id'])->update([
				'position'=>$module_data['pos']
			]);
		}
		if($result != false){
			return 'success';
		}
	}

	public function dropModule(Request $request){
		$data = $request->all();
		$result = EnabledModules::select('id','slug','type')->find($data['id']);
		$module = Modules::select('slug')->where('id','=',$result->type)->first();
		switch($module->slug){
			case 'menu':		MenuItems::where('module_id','=',$result->id)->delete(); break;
			case 'categories':	Categories::where('module_id','=',$result->id)->delete(); break;
			case 'articles':	Articles::where('module_id','=',$result->id)->delete(); break;
			case 'products':	Products::where('module_id','=',$result->id)->delete(); break;
			case 'promo':		Promo::where('module_id','=',$result->id)->delete(); break;
			case 'pages':		ContentPages::where('module_id','=',$result->id)->update(['module_id'=>0]); break;
		}
		AdminMenu::where('slug','=',$result->slug)->delete();
		$result = EnabledModules::where('id','=',$data['id'])->delete();
		if ($result != false) {
			return 'success';
		}
	}

	public function modulesContentAdd(Request $request){
		$user = Auth::user();
		$data = $request->all();
		if(!empty($data)){
			$current_module = EnabledModules::select('id','type','unique_slug','custom_fields')->find($data['module_id']);
			$parent_module = Modules::select('slug')->find($current_module->type);

			$slug = Functions::str2url($data['slug']);
			if($current_module->unique_slug > 0){
				$slug .= '_'.uniqid();
			}
			$custom_fields = [];
			if(isset($data['custom_data'])){
				$temp = json_decode($data['custom_data']);
				foreach($temp as $field_iter => $field_data){
					$custom_fields[] = Functions::customFieldDataFill($data, $field_data, $parent_module->slug);
				}
			}
			$regular_slider_data = (isset($data['regular_slider']))
				? Functions::getSliderData($data, 'regular_slider', $parent_module->slug)
				: [];

			$return_by_slug_isset = false;
			switch($parent_module->slug){
				case 'categories':
					if( (isset($data['id'])) && (!empty($data['id'])) ){
						$result = Categories::find($data['id']);
						$result ->title		= $data['title'];
						$result ->slug		= $slug;
						$result ->img_url	= serialize($regular_slider_data);
						$result ->text		= $data['text'];
						$result ->description	= $data['description'];
						$result ->custom_fields	= serialize($custom_fields);
						$result ->refer_to	= $data['refer_to'];
						$result ->module_id	= $current_module->id;
						$result ->author	= $user->login;
						$result ->save();
					}else{
						$last_pos = Categories::select('position')
							->where('module_id','=',$data['module_id'])
							->where('refer_to','=',$data['refer_to'])
							->orderBy('position','desc')
							->first();
						$position = ($last_pos != false)? ($last_pos->position +1): 0;
						$result = Categories::create([
							'title'		=> $data['title'],
							'slug'		=> $slug,
							'img_url'	=> serialize($regular_slider_data),
							'text'		=> $data['text'],
							'description'	=> $data['description'],
							'custom_fields'	=> serialize($custom_fields),
							'position'	=> $position,
							'refer_to'	=> $data['refer_to'],
							'module_id'	=> $current_module->id,
							'author'	=> $user->login,
							'enabled'	=> 0
						]);

					}
				break;

				case 'articles':
					$published_at = date('Y-m-d H:i:s');
					if( (isset($data['id'])) && (!empty($data['id'])) ){
						$slug_isset = Articles::select('slug')
							->where('module_id','=',$data['module_id'])
							->where('id','!=',$data['id'])
							->where('slug','=',$slug)
							->count();
						if(0 == $slug_isset){
							$result = Articles::find($data['id']);
							$result ->title			= $data['title'];
							$result ->slug			= $slug;
							$result ->img_url		= serialize($regular_slider_data);
							$result ->text_caption	= $data['text_caption'];
							$result ->text			= $data['text'];
							$result ->description	= $data['description'];
							$result ->meta_title	= $data['meta_title'];
							$result ->meta_keywords	= $data['meta_keywords'];
							$result ->meta_description = $data['meta_description'];
							$result ->custom_fields	= serialize($custom_fields);
							$result ->module_id		= $current_module->id;
							$result ->author		= $user->login;
							$result ->enabled		= $data['enabled'];
							$result ->save();
						}else{
							$return_by_slug_isset = true;
						}
					}else{
						$slug_isset = Articles::select('slug')
							->where('module_id','=',$data['module_id'])
							->where('slug','=',$slug)
							->count();
						if(0 == $slug_isset){
							$result = Articles::create([
								'title'			=> $data['title'],
								'slug'			=> $slug,
								'img_url'		=> serialize($regular_slider_data),
								'text_caption'	=> $data['text_caption'],
								'text'			=> $data['text'],
								'description'	=> $data['description'],
								'meta_title'	=> $data['meta_title'],
								'meta_keywords'	=> $data['meta_keywords'],
								'meta_description' => $data['meta_description'],
								'custom_fields'	=> serialize($custom_fields),
								'position'		=> 0,
								'module_id'		=> $current_module->id,
								'author'		=> $user->login,
								'enabled'		=> $data['enabled'],
								'published_at'	=> $published_at
							]);
						}else{
							$return_by_slug_isset = true;
						}
					}
				break;

				case 'menu':
					if( (isset($data['id'])) && (!empty($data['id'])) ){
						$result = MenuItems::find($data['id']);
						$result ->title			= $data['title'];
						$result ->slug			= $slug;
						$result ->custom_fields	= serialize($custom_fields);
						$result ->refer_to		= $data['refer_to'];
						$result ->module_id		= $current_module->id;
						$result ->position		= 0;
						$result ->enabled		= $data['enabled'];
						$result ->active		= $data['active'];
						$result ->save();
					}else{
						$result = MenuItems::create([
							'title'			=> $data['title'],
							'slug'			=> $slug,
							'custom_fields'	=> serialize($custom_fields),
							'refer_to'		=> $data['refer_to'],
							'module_id'		=> $current_module->id,
							'position'		=> 0,
							'enabled'		=> $data['enabled'],
							'active'		=> $data['active']
						]);
					}
				break;

				case 'products':
					$published_at = date('Y-m-d H:i:s');
					if( (isset($data['id'])) && (!empty($data['id'])) ){
						$result = Products::find($data['id']);
						$result->title			= $data['title'];
						$result->slug			= $slug;
						$result->img_url		= serialize($regular_slider_data);
						$result->description	= $data['description'];
						$result->text			= $data['text'];
						$result->price			= $data['price'];
						$result->color			= serialize(json_decode($data['colors']));
						$result->meta_title		= $data['meta_title'];
						$result->meta_keywords	= $data['meta_keywords'];
						$result->meta_description = $data['meta_description'];
						$result->custom_fields	= serialize($custom_fields);
						$result->module_id		= $current_module->id;
						$result->author			= $user->login;
						$result ->enabled		= $data['enabled'];
						$result->save();
					}else{
                        $result = Products::create([
							'title'			=> $data['title'],
							'slug'			=> $slug,
							'img_url'		=> serialize($regular_slider_data),
							'description'	=> $data['description'],
							'text'			=> $data['text'],
							'price'			=> $data['price'],
							'color'			=> serialize(json_decode($data['colors'])),
							'meta_title'	=> $data['meta_title'],
							'meta_keywords'	=> $data['meta_keywords'],
							'meta_description' => $data['meta_description'],
							'custom_fields'	=> serialize($custom_fields),
							'module_id'		=> $current_module->id,
							'author'		=> $user->login,
							'enabled'		=> $data['enabled'],
							'published_at'	=> $published_at
						]);
					}
				break;

				case 'promo':
					$published_at = date('Y-m-d H:i:s');
					if( (isset($data['id'])) && (!empty($data['id'])) ){
						$slug_isset = Products::select('slug')
							->where('module_id','=',$data['module_id'])
							->where('id','!=',$data['id'])
							->where('slug','=',$slug)
							->count();
						if(0 == $slug_isset){
							$result = Promo::find($data['id']);
							$result ->title			= $data['title'];
							$result ->slug			= $slug;
							$result ->img_url		= serialize($regular_slider_data);
							$result ->description	= $data['description'];
							$result ->text			= $data['text'];
							$result ->discount		= serialize(json_decode($data['discount']));
							$result ->date_start	= $data['date_begin'];
							$result ->date_finish	= $data['date_finish'];
							$result ->custom_fields	= serialize($custom_fields);
							$result ->meta_title	= $data['meta_title'];
							$result ->meta_keywords	= $data['meta_keywords'];
							$result ->meta_description = $data['meta_description'];
							$result ->module_id		= $current_module->id;
							$result ->author		= $user->login;
							$result ->enabled		= $data['enabled'];
							$result ->save();
						}else{
							$return_by_slug_isset = true;
						}
					}else{
						$slug_isset = Promo::select('slug')
							->where('module_id','=',$data['module_id'])
							->where('slug','=',$slug)
							->count();
						if(0 == $slug_isset){
							$result = Promo::create([
								'title'         => $data['title'],
								'slug'          => $slug,
								'img_url'		=> serialize($regular_slider_data),
								'description'	=> $data['description'],
								'text'			=> $data['text'],
								'discount'		=> serialize(json_decode($data['discount'])),
								'date_start'    => $data['date_begin'],
								'date_finish'   => $data['date_finish'],
								'custom_fields'	=> serialize($custom_fields),
								'meta_title'	=> $data['meta_title'],
								'meta_keywords'	=> $data['meta_keywords'],
								'meta_description' => $data['meta_description'],
								'module_id'		=> $current_module->id,
								'author'		=> $user->login,
								'enabled'		=> $data['enabled'],
								'published_at'	=> $published_at
							]);
						}else{
							$return_by_slug_isset = true;
						}
					}
				break;
			}
			if($return_by_slug_isset){
				return json_encode([
					'message'=>'error',
					'type' => 'slug_isset'
				]);
			}
			if($result != false){
				return json_encode(['message'=>'success']);
			}else{
				return json_encode([
					'message'=>'error',
					'type' => $result
				]);
			}
		}else{
			return json_encode([
				'message'=>'error',
				'type' => ''
			]);
		}
	}

	public function modulesChangeEnabled(Request $request){
		$data = $request->all();
		if($data['type'] == 'pages'){
			$result = ContentPages::find($data['id']);
		}else{
			$enabled_module = EnabledModules::select('type')->find($data['type']);
			$module = Modules::select('slug')->find($enabled_module->type);
			switch($module->slug){
				case 'categories':	$result = Categories::find($data['id']); break;
				case 'articles':	$result = Articles::find($data['id']); break;
				case 'menu':		$result = MenuItems::find($data['id']); break;
				case 'products':	$result = Products::find($data['id']); break;
				case 'promo':		$result = Promo::find($data['id']); break;
			}
		}
		$enabled = ($result->enabled == 1)? 0: 1;
		if( (isset($result->published_at)) && ($result->enabled == 0) ){
			$result->published_at = date('Y-m-d H:i:s');
		}
		$result-> enabled = $enabled;
		$result-> save();
		if($result != false){
			return json_encode([
				'message' => 'success',
				'published' => (isset($result->published_at))? Functions::convertDate($result->published_at): ''
			]);
		}
	}

	public function modulesDropElement(Request $request){
		$data = $request->all();
		if($data['type'] == 'pages'){
			$result = ContentPages::where('id','=',$data['id'])->delete();
		}else{
			$enabled_module = EnabledModules::select('type')->find($data['type']);
			$module = Modules::select('slug')->find($enabled_module->type);
			switch($module->slug) {
				case 'categories':
					$subcategories = Categories::select('id','refer_to')->where('refer_to','=',$data['id'])->get();
					foreach($subcategories as $subcategory){
						Categories::where('id','=',$subcategory->id)->update(['refer_to'=>0]);
					}
					$result = Categories::where('id','=',$data['id'])->delete();
				break;
				case 'articles':$result = Articles::where('id','=',$data['id'])->delete(); break;
				case 'menu':
					$submenu = MenuItems::select('id','refer_to')->where('refer_to','=',$data['id'])->get();
					foreach($submenu as $item){
						MenuItems::where('id','=',$item->id)->update(['refer_to'=>0]);
					}
					$result = MenuItems::where('id','=',$data['id'])->delete();
				break;
				case 'products':$result = Products::where('id','=',$data['id'])->delete(); break;
				case 'promo':	$result = Promo::where('id','=',$data['id'])->delete(); break;
			}
		}

		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}

	public function modulesChangePosition(Request $request){
		$data = $request->all();
		$items = json_decode($data['items']);
		$enabled_module = EnabledModules::select('type')->find($data['module']);
		$module = Modules::select('slug')->find($enabled_module->type);

		switch($module->slug){
			case 'categories':
				foreach($items as $item){
					$result = Categories::where('id','=',$item->id)->update([
						'position' => $item->position,
						'refer_to' => $item->refer_to
					]);
				}
			break;
			case 'menu':
				foreach($items as $item){
					$result = MenuItems::where('id','=',$item->id)->update([
						'position' => $item->position,
						'refer_to' => $item->refer_to
					]);
				}
			break;
		}
		if($result != false){
			return 'success';
		}
	}
}