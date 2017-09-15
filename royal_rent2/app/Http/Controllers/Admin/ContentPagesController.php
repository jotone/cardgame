<?php
namespace App\Http\Controllers\Admin;

use App\ContentPages;
use App\EnabledModules;
use App\Http\Controllers\Supply\Functions;
use App\Modules;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Crypt;
use Validator;

class ContentPagesController extends BaseController
{
	public function getTemplateFields(Request $request){
		$data = $request->all();

		$page_data = ( (isset($data['page_id'])) && (!empty($data['page_id'])) )
			? ContentPages::select('img_url','description','text','custom_fields')->find($data['page_id'])
			: [];
		$disabled = '';
		$custom = '';
		if(0 == $data['id']){
			$module = Modules::select('id','options')->where('slug','=','pages')->first();
			$fields = unserialize($module->options);

			foreach($fields as $field){
				$disabled .=(!empty($page_data))
					? Functions::buildDefaultFields($field['name'], [$field['name'] => $page_data[$field['name']]])
					: Functions::buildDefaultFields($field['name']);
			}
		}else{
			$module = EnabledModules::select('disabled_fields','custom_fields')->find($data['id']);
			$fields = unserialize($module->disabled_fields);
			$custom_fields = unserialize($module->custom_fields);

			foreach($fields as $field){
				if($field->enabled == 1){
					$disabled .= (!empty($page_data))
						? Functions::buildDefaultFields($field->type, [$field->type => $page_data[$field->type]])
						: Functions::buildDefaultFields($field->type);
				}
			}

			$custom .= (!empty($page_data))
				? Functions::buildCustomFields($custom_fields, unserialize($page_data->custom_fields))
				: Functions::buildCustomFields($custom_fields);
		}

		$disabled = str_replace(["\t","\n","\r"],'',$disabled);
		$custom = str_replace(["\t","\n","\r"],'',$custom);
		return json_encode([
			'message'	=> 'success',
			'disabled'	=> $disabled,
			'custom'	=> $custom
		]);
	}

	public function addContentPage(Request $request){
		$user = Auth::user();
		$data = $request->all();
		if(!empty($data)) {
			$slug = Functions::str2url($data['slug']);
			$custom_fields = [];
			if (isset($data['custom_data'])) {
				$temp = json_decode($data['custom_data']);
				foreach ($temp as $field_iter => $field_data) {
					$custom_fields[] = Functions::customFieldDataFill($data, $field_data, 'pages');
				}
			}
			$regular_slider_data = (isset($data['regular_slider']))
				? Functions::getSliderData($data, 'regular_slider', 'pages')
				: [];

			$return_by_slug_isset = false;
		}else{
			return json_encode([
				'message'=>'error',
				'type' => ''
			]);
		}
		$published_at = date('Y-m-d H:i:s');
		if( (isset($data['id'])) && (!empty($data['id'])) ){
			$slug_isset = ContentPages::select('slug')->where('id','!=',$data['id'])->where('slug','=',$slug)->count();
			if(0 == $slug_isset){
				$result = ContentPages::find($data['id']);
				$result ->title = $data['title'];
				$result ->slug			= $slug;
				$result ->img_url		= serialize($regular_slider_data);
				$result ->description	= $data['description'];
				$result ->text			= $data['text'];
				$result ->custom_fields	= serialize($custom_fields);
				$result ->meta_title	= $data['meta_title'];
				$result ->meta_keywords	= $data['meta_keywords'];
				$result ->meta_description = $data['meta_description'];
				$result ->module_id		= $data['module_id'];
				$result ->author		= $user->login;
				$result ->enabled		= $data['enabled'];
				$result ->save();
			}else{
				$return_by_slug_isset = true;
			}
		}else{
			$slug_isset = ContentPages::select('slug')->where('slug','=',$slug)->count();
			if(0 == $slug_isset){
				$result = ContentPages::create([
					'title'			=> $data['title'],
					'slug'			=> $slug,
					'img_url'		=> serialize($regular_slider_data),
					'description'	=> $data['description'],
					'text'			=> $data['text'],
					'custom_fields'	=> serialize($custom_fields),
					'meta_title'	=> $data['meta_title'],
					'meta_keywords'	=> $data['meta_keywords'],
					'meta_description' => $data['meta_description'],
					'module_id'		=> $data['module_id'],
					'author'		=> $user->login,
					'enabled'		=> $data['enabled'],
					'published_at'	=> $published_at
				]);
			}else{
				$return_by_slug_isset = true;
			}
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
	}
}