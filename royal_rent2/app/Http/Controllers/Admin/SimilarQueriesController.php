<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\EnabledModules;
use App\Modules;
use App\PageContent;
use App\Http\Controllers\Supply\Functions;
use App\Requests301;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
use Validator;

class SimilarQueriesController extends BaseController
{
	public function changePosition(Request $request){
		$data = $request->all();
		$items = json_decode($data['items']);
		switch($data['type']){
			case 'admin_menu':
				foreach($items as $item){
					$elem = AdminMenu::find($item->id);
					$elem ->position = $item->position;
					$elem ->refer_to = $item->refer_to;
					$elem ->save();
				}
			break;
		}
		if($elem != false){
			return 'success';
		}
	}

	protected static function getFolders($folder = 'images', &$all_files){
		$fp=opendir($folder);
		while($cv_file=readdir($fp)) {
			if(is_file($folder."/".$cv_file)) {
				$all_files[] = $folder."/".$cv_file;
			}elseif( ($cv_file != '.') && ($cv_file != '..') && ($cv_file != 'users') && (is_dir($folder.'/'.$cv_file)) ){
				self::getFolders($folder."/".$cv_file, $all_files);
			}
		}
		closedir($fp);
		return $all_files;
	}

	public function getServerImages(){
		$folders = [];
		$folders = self::getFolders('images', $folders);

		return json_encode([
			'message' => 'success',
			'folders' => $folders
		]);
	}

	public function getModulesList(Request $request){
		$data = $request->all();
		$module = Modules::select('id')->where('slug','=',$data['destination'])->first();
		$enabled_modules = EnabledModules::select('id','title')->where('type','=',$module->id)->orderBy('title','asc')->get();
		$result = [];
		foreach($enabled_modules as $item){
			$result[] = [
				'id' => $item->id,
				'title' => $item->title
			];
		}
		return json_encode([
			'message'	=> 'success',
			'result'	=> $result
		]);
	}

	public function getModuleSettings(Request $request){
		$data = $request->all();

		$module = Modules::select('id','options')->find($data['type']);
		return json_encode([
			'message' => 'success',
			'options' => unserialize($module->options)
		]);
	}

	public function saveSettings(Request $request){
		$data = $request->all();
		if(isset($data['type']))
		switch($data['type']){
			case 'settings':
				foreach($data as $key => $value){
					if($key != 'type'){
						$content_isset = PageContent::where('title','=',$key)->count();
						$content = (is_array(json_decode($value)))? serialize(json_decode($value)): $value;

						if(0 == $content_isset){
							$result = PageContent::create([
								'title' => $key,
								'type'  => $data['type'],
								'content' => $content
							]);
						}else{
							$result = PageContent::where('title','=',$key)->update([
								'content' => $content
							]);
						}
					}
				}
			break;
		}
		if($result != false){
			return 'success';
		}
	}

	public function getMailingDatafields(Request $request){
		$data = $request->all();
		$result = [];
		switch($data['type']){
			case '0':
				$result = [
					['login','Логин'],
					['email','e-mail'],
					['name','Имя']
				];
			break;

			default:
				$enabled_module = EnabledModules::select('slug','custom_fields','type')->find($data['type']);
				$module_type = Modules::select('slug')->find($enabled_module->type);

				$custom_fields = unserialize($enabled_module->custom_fields);
				switch($module_type->slug){
					case 'articles':
					case 'categories':
					case 'promo':
						$result = [
							['title','Название'],
							['slug','Ссылка']
						];
					break;
					case 'products':
						$result = [
							['title','Название'],
							['slug','Ссылка'],
							['price','Цена'],
							['color','Цвет']
						];
					break;
				}

				foreach($custom_fields as $custom_field){
					switch($custom_field->type){
						case 'articles':
							$accepted_module = EnabledModules::select('slug')->find($custom_field->val);
							$result[] = [
								'custom~'.$custom_field->type.'~'.$accepted_module->slug,
								'ID Статьи: '.$custom_field->capt
							];
						break;
						case 'category':
							$accepted_module = EnabledModules::select('slug')->find($custom_field->val);
							$result[] = [
								'custom~'.$custom_field->type.'~'.$accepted_module->slug,
								'ID Категории: '.$custom_field->capt
							];
						break;
						case 'checkbox':
							$result[] = [
								'custom~'.$custom_field->type.'~'.$custom_field->pos,
								'Флажок: '.$custom_field->capt
							];
						break;
						case 'number':
							$result[] = [
								'custom~'.$custom_field->type.'~'.$custom_field->pos,
								'Значение: '.$custom_field->capt
							];
						break;
						case 'range':
							$result[] = [
								'custom~'.$custom_field->type.'~'.$custom_field->pos,
								'Ползунок: '.$custom_field->capt
							];
						break;
						case 'products':
							$accepted_module = EnabledModules::select('slug')->find($custom_field->val);
							$result[] = [
								'custom~'.$custom_field->type.'~'.$accepted_module->slug,
								'ID Товара: '.$custom_field->capt
							];
						break;
						case 'promo':
							$accepted_module = EnabledModules::select('slug')->find($custom_field->val);
							$result[] = [
								'custom~'.$custom_field->type.'~'.$accepted_module->slug,
								'ID Акции: '.$custom_field->capt
							];
						break;
					}
				}
		}
		return json_encode([
			'message'	=> 'success',
			'response'	=> $result
		]);
	}

	public function getTopMenu(){
		$menu = Functions::buildMenuList('/admin/menu_settings');
		return $menu;
	}

	public function saveRedirects(Request $request){
		$data = $request->all();

		$links = json_decode($data['links']);
		foreach($links as $link){
			if($link->id == 0){
				$result = Requests301::create([
					'link_from' => trim($link->from),
					'link_to' => trim($link->to),
				]);
			}else{
				$result = Requests301::find($link->id);
				$result->link_from = trim($link->from);
				$result->link_to = trim($link->to);
				$result->save();
			}
		}
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}

	public function dropRedirect(Request $request){
		$data = $request->all();
		$result = Requests301::where('id','=',$data['id'])->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}
}