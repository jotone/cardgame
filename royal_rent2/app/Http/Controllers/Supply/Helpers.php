<?php
namespace App\Http\Controllers\Supply;

use App\Articles;
use App\Categories;
use App\EnabledModules;
use App\Reviews;
use Illuminate\Http\Request;
use App\MenuItems;
use App\PageContent;
use App\Products;
use App\Promo;
use App\Subscribers;
use Auth;

use Carbon\Carbon;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Exception;

class Helpers extends BaseController
{
	public static function monthDecToWord($month){
		switch($month){
			case '1': return 'января'; break;
			case '2': return 'февраля'; break;
			case '3': return 'марта'; break;
			case '4': return 'апреля'; break;
			case '5': return 'мая'; break;
			case '6': return 'июня'; break;
			case '7': return 'июля'; break;
			case '8': return 'августа'; break;
			case '9': return 'сентября'; break;
			case '10': return 'октября'; break;
			case '11': return 'ноября'; break;
			case '12': return 'декабря'; break;
			default: return 'Произошла ошибка';
		}
	}

	public function setCity(Request $request){
		$data = $request->all();
		if($data['city'] != $_COOKIE['current_city']){
			setcookie('current_city',$data['city'], time()+36000, '/');
			return 'city_changed';
		}
	}

	public static function buildMenuList($module_slug, $refer_to = 0){
		$result = [];
		$enabled_module = EnabledModules::select('id','slug')->where('slug','=',$module_slug)->first();

		$menu_items = MenuItems::select('id','title','slug','custom_fields')
			->where('module_id','=',$enabled_module->id)
			->where('refer_to','=',$refer_to)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();
		foreach($menu_items as $menu_item){
			$custom_fields = unserialize($menu_item->custom_fields);
			$custom_data = [];
			foreach($custom_fields as $field){
				foreach($field as $key => $value){
					$custom_data[$key] = $value;
				}
			}
			$children_items = MenuItems::where('module_id','=',$enabled_module->id)
				->where('refer_to','=',$menu_item->id)
				->where('enabled','=',1)
				->count();
			$items = ($children_items > 0)
				? self::buildMenuList($module_slug, $menu_item->id)
				: [];
			$has_hash = (strpos($menu_item['slug'],'#') === false)? 0: 1;
			$result[] = [
				'title'	=> $menu_item->title,
				'slug'	=> $menu_item->slug,
				'custom_fields' => $custom_data,
				'items'	=> $items,
				'has_hash' => $has_hash
			];
		}
		return $result;
	}

	public static function buildCategoriesList($module_slug, $refer_to = 0){
		$result = [];
		$enabled_module = EnabledModules::select('id','slug')->where('slug','=',$module_slug)->first();

		$categories = Categories::select('id','title','slug','img_url','description','text','custom_fields','position')
			->where('module_id','=',$enabled_module->id)
			->where('refer_to','=',$refer_to)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();
		foreach($categories as $category){
			$images = unserialize($category->img_url);
			$custom_fields = unserialize($category->custom_fields);
			$custom_data = [];
			foreach($custom_fields as $field){
				foreach($field as $key => $value){
					$custom_data[$key] = $value;
				}
			}
			$children_items = Categories::where('module_id','=',$enabled_module->id)
				->where('refer_to','=',$category->id)
				->where('enabled','=',1)
				->count();
			$items = ($children_items > 0)
				? self::buildCategoriesList($module_slug, $category->id)
				: [];
			$result[] = [
				'id'		=> $category->id,
				'title'		=> $category->title,
				'slug'		=> $category->slug,
				'img_url'	=> $images,
				'description'=>$category->description,
				'text'		=> $category->text,
				'position'	=> $category->position,
				'custom_fields'	=> $custom_data,
				'items'		=> $items
			];
		}
		return $result;
	}

	public static function getDefaultContent($current_city){
		$result['cities'] = self::buildCategoriesList('spisok_gorodov');
		$result['current_city'] = [];
		foreach($result['cities'] as $city){
			if($city['slug'] == $current_city){
				$result['current_city'] = [
					'id'	=> $city['id'],
					'title'	=> $city['title'],
					'data'	=> $city['custom_fields']
				];
			}
		}
		$result['header_menu'] = self::buildMenuList('menyu_v_shapke');
		$result['footer_menu'] = self::buildMenuList('menyu_v_futere');
		$result['vehicle_type'] = self::buildCategoriesList('tipy_transporta');
		$result['menu_services'] = self::buildMenuList('navigatsiya__nashi_uslugi');
		$result['menu_about_company'] = self::buildMenuList('navigatsiya__o_kompanii');
		$result['menu_partners'] = self::buildMenuList('navigatsiya__partneram_i_investoram');
		$settings = PageContent::select('title','content')->where('type','=','settings')->get();
		$result['site_settings'] =[];
		foreach($settings as $setting){
			$content = (Functions::is_serialized($setting->content))? unserialize($setting->content): $setting->content;
			$result['site_settings'][$setting->title] = $content;
		}
		return $result;
	}

	public static function convertCustomFields($custom_fields){
		$content = [];
		foreach($custom_fields as $field){
			foreach($field as $key => $value){
				switch($value['type']){
					case 'custom_slider':
						$temp = [];
						foreach($value['value'] as $slide_iter => $slides){
							foreach($slides as $slide){
								$temp[$slide_iter][$slide['name']] = [
									'value' => $slide['value'],
									'type'  => $slide['type']
								];
							}
						}
					break;
					case 'fieldset':
						$temp = self::convertCustomFields($value['value']);
					break;
					case 'category':
						$temp = [];
						foreach($value['value'] as $item){
							$category = Categories::select('id','title','slug','img_url','text','custom_fields','position','module_id')->find($item);
							if($category->module_id == 6){
								$temp[] = [
									'id'		=> $category->id,
									'title'		=> $category->title,
									'slug'		=> $category->slug,
									'text'		=> $category->text,
									'position'	=> $category->position,
									'img_url'	=> unserialize($category->img_url),
									'data'		=> unserialize($category->custom_fields)
								];
							}else{
								$temp[] = [
									'id'		=> $category->id,
									'title'		=> $category->title,
									'slug'		=> $category->slug,
									'text'		=> $category->text,
									'position'	=> $category->position,
									'img_url'	=> unserialize($category->img_url),
									'data'		=> self::convertCustomFields(unserialize($category->custom_fields))
								];
							}
						}
					break;
					default: $temp = $value;
				}
				$content[$key] = $temp;
			}
		}
		return $content;
	}

	public static function createExcursionViewData($ids, $current){
		if(!is_array($ids)){
			$ids = explode(',',$ids);
		}
		$result = [];
		foreach($ids as $id){
			if($id != $current){
				$article = Articles::find($id);
				$images = unserialize($article->img_url);
				$custom_fields = unserialize($article->custom_fields);
				$custom = Helpers::convertCustomFields($custom_fields);
				$allow_excursion = false;
				foreach($custom['category_2'] as $city){
					if($city['slug'] == $_COOKIE['current_city']){
						$allow_excursion = true;
					}
				}
				if($allow_excursion){
					$image = (!empty($images))? $images[0]['img']: '';
					$result[] = [
						'title' => $article->title,
						'slug' => $article->slug,
						'img_url' => $image,
						'description' => $article->description,
						'data'  => $custom
					];
				}
			}
		}
		return $result;
	}

	public function getSubcategory(Request $request){
		$data = $request->all();
		$vehicle_module = EnabledModules::select('id')->where('slug','=','tipy_transporta')->first();
		$category = Categories::select('id')
			->where('module_id','=',$vehicle_module->id)
			->where('slug','=',$data['slug'])
			->first();
		$categories = Categories::select('slug','title','img_url')
			->where('refer_to','=',$category->id)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();
		$result = [];
		foreach($categories as $category){
			$images = unserialize($category->img_url);
			$image = (!empty($images))? $images[0]['img']: '';
			$result[] = [
				'title'	=> $category->title,
				'slug'	=> $category->slug,
				'img_url'=>$image,
				'refer'	=> $data['slug']
			];
		}
		return json_encode($result);
	}

	public function getMarkByCategory(Request $request){
		$data = $request->all();
		if(isset($data['category'])){
			return json_encode(self::markBycategory($data['slug'],$data['category']));
		}else {
			return json_encode(self::markBycategory($data['slug']));
		}
	}

	public static function markBycategory($category, $parent = 'car_with_driver'){
		$vehicle_module = EnabledModules::select('id')->where('slug','=','tipy_transporta')->first();
		$upper_category = Categories::select('id')->where('slug','=',$parent)->where('module_id','=',$vehicle_module->id)->first();

		$category = Categories::select('id')->where('slug','=',$category)->where('refer_to','=',$upper_category->id)->first();
		$cars = Products::select('custom_fields')->where('enabled','=',1)->get();
		$models = [];
		foreach($cars as $car) {
			$content = self::convertCustomFields(unserialize($car->custom_fields));
			foreach($content['category_0'] as $item){
				if($category->id == $item['id']){
					foreach($content['category_1'] as $model){
						$models[] = $model['id'];
					}
				}
			}
		}
		$models = array_values(array_unique($models));
		$marks = [];
		foreach($models as $model_id){
			$model = Categories::select('refer_to')->find($model_id);
			$mark = Categories::select('title','slug')->find($model->refer_to);
			$marks[$mark->slug] = [
				'title' => $mark->title,
				'slug'  => $mark->slug
			];
		}
		asort($marks);
		$marks = array_values($marks);
		return $marks;
	}

	public function getModelsByMark(Request $request){
		$data = $request->all();
		if( (isset($data['category'])) && (isset($data['upper'])) ){
			return json_encode(self::modelByMark($data['mark'], $data['category'], $data['upper']));
		}else{
			return json_encode(self::modelByMark($data['mark']));
		}
	}

	public static function modelByMark($mark, $category = '', $upper = ''){
		$models_module = EnabledModules::select('id','slug')->where('slug','=','mark_and_model')->first();
		$current_mark = Categories::select('id','slug')
			->where('module_id','=',$models_module->id)
			->where('slug','=',$mark)
			->first();
		$models = Categories::select('id','title','slug')
			->where('module_id','=',$models_module->id)
			->where('refer_to','=',$current_mark->id)
			->orderBy('title','asc')
			->get();
		$result = [];
		foreach($models as $model){
			$cars = Products::select('img_url','custom_fields','price')
				->where('enabled','=',1)
				->where('custom_fields','LIKE','%'.$model->id.'%')
				->get();

			foreach($cars as $car){
				$custom = self::convertCustomFields(unserialize($car->custom_fields));
				$allow = false;

				if( (!empty($custom['category_1'])) && ($model->id == $custom['category_1'][0]['id']) ){
					$allow = true;
				}
				$allow_by_cat = true;
				if( (!empty($category)) && (!empty($upper)) ){
					$allow_by_cat = false;
					foreach($custom['category_0'] as $item){
						if($upper == 'car_nonedriver'){
							if( ($item['slug'] == $category) && ($car->price == 0) ){
								$allow_by_cat = true;
								break;
							}
						}else{
							if( ($item['slug'] == $category) && ($car->price > 0) ){
								$allow_by_cat = true;
								break;
							}
						}
					}
				}
				if($allow && $allow_by_cat){
					$images = unserialize($car->img_url);
					$image = (!empty($images))? $images[0]['img']: '';
					$result[] = [
						'title' => $model->title,
						'slug'  => $model->slug,
						'img_url' => $image
					];
					break;
				}
			}
		}
		return $result;
	}

	public function getCarByModel(Request $request){
		$data = $request->all();
		if(isset($data['driver'])){
			return json_encode(self::carByModel($data['mark'], $data['model'], $data['driver']));
		}else{
			return json_encode(self::carByModel($data['mark'], $data['model']));
		}
	}

	public static function carByModel($mark, $model, $only_with_driver = 0){
		$models_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();
		$cars = Products::select('id','title','img_url','price','color','custom_fields')->where('enabled','=',1)->get();
		$temp = [];
		foreach($cars as $car) {
			$content = self::convertCustomFields(unserialize($car->custom_fields));
			foreach($content['category_1'] as $item){
				if($item['slug'] == $model){
					$model_data = Categories::select('refer_to')
						->where('module_id','=',$models_module->id)
						->where('slug','=',$model)
						->first();
					$mark_data = Categories::select('slug')->find($model_data->refer_to);
					if($mark_data->slug == $mark){
						$custom_fields = self::convertCustomFields(unserialize($car->custom_fields));
						switch($only_with_driver){
							case '0':
								$temp[] = [
									'id'		=> $car->id,
									'title'		=> $car->title,
									'img_url'	=> unserialize($car->img_url),
									'price'		=> $car->price,
									'color'		=> unserialize($car->color),
									'transmission'=> $custom_fields['fieldset_1']['category_0'][0]['title'],
									'time'		=> $custom_fields['string_1']['value']
								];
							case '1':
								if($car->price > 0){
									$temp[] = [
										'id'		=> $car->id,
										'title'		=> $car->title,
										'img_url'	=> unserialize($car->img_url),
										'price'		=> $car->price,
										'color'		=> unserialize($car->color),
										'transmission'=> $custom_fields['fieldset_1']['category_0'][0]['title'],
										'time'		=> $custom_fields['string_1']['value']
									];
								}
							break;
							case '2':
								if($car->price == 0){
									$temp[] = [
										'id'		=> $car->id,
										'title'		=> $car->title,
										'img_url'	=> unserialize($car->img_url),
										'price'		=> $car->price,
										'color'		=> unserialize($car->color),
										'transmission'=> $custom_fields['fieldset_1']['category_0'][0]['title'],
										'time'		=> $custom_fields['string_1']['value']
									];
								}
							break;
						}
					}
				}
			}
		}
		if(!empty($temp)){
			return $temp;
		}else{
			return [];
		}
	}

	public function getCarById(Request $request){
		$data = $request->all();
		return json_encode(self::carById($data['id']));
	}

	public static function carById($id){
		$car = Products::find($id);
		if($car != false){
			$content = self::convertCustomFields(unserialize($car->custom_fields));
			$price = (!empty($content['string_0']['value']))? number_format($content['string_0']['value'], 0,'.',' '): 0;
			usort($content['category_2'], function($a, $b){
				return $a['position']>$b['position'];
			});
			$colors = [];
			$similar_cars = Products::select('color')->where('slug','=',$car->slug)->get();
			foreach ($similar_cars as $similar_car) {
				$color = unserialize($similar_car['color']);
				foreach($color as $item){
					$colors[Functions::str2url($item->title)] = $item;
				}
			}
			$cat_refer = Categories::select('refer_to')->find($content['category_0'][0]['id']);
			$upper = Categories::select('slug')->find($cat_refer->refer_to);

			$arr_keys = array_keys($colors);
			$temp_color = $colors[$arr_keys[0]];

			return [
				'id'			=> $car->id,
				'title'			=> $car->title,
				'slug'			=> $car->slug.'-'.Functions::str2url($temp_color->title),
				'text'			=> $car->text,
				'img_url'		=> unserialize($car->img_url),
				'price'			=> $car->price,
				'color'			=> $colors,
				'current_color'	=> unserialize($car->color),
				'cmp_full_price'=> (!empty($content['string_0']['value']))? $content['string_0']['value']: 0,
				'full_price'	=> $price,
				'transmission'	=> $content['fieldset_1']['category_0'][0]['title'],
				'seat_quant'	=> $content['fieldset_1']['string_0']['value'],
				'fuel_system'	=> $content['fieldset_1']['category_1'][0]['title'],
				'fuel_consume'	=> $content['fieldset_1']['number_0']['value'],
				'engine_power'	=> $content['fieldset_1']['number_1']['value'],
				'rents'			=> $content['category_2'],
				'options'		=> $content['category_3'],
				'video'			=> $content['textarea_0'],
				'promo'			=> $content['number_0'],
				'upper'			=> $upper['slug']
			];
		}else{
			return [];
		}
	}

	public function addReview(Request $request){
		$data = $request->all();

		$user = Auth::user();
		$user_id = ($user)? $user['id']: 0;
		$name = ($user)? $user['name']: $data['name'].' '.$data['surname'];
		$result = Reviews::create([
			'user_id'	=> $user_id,
			'name'		=> $name,
			'text'		=> $data['coment'],
			'rating'	=> $data['eval'],
			'custom_fields' => serialize([
				'loc'		=> $data['location'],
				'has_driver'=> $data['auto_has_driver'],
				'vehicle'	=> $data['choose'],
				'mark'		=> $data['auto_mark'],
				'model'		=> $data['auto_model']
			])
		]);
		if($result != false){
			return 'success';
		}
	}

	public function getMoreCars(Request $request){
		$data = $request->all();
		$url = explode('/', substr($data['path'], 1));

		$current_city = (!isset($_COOKIE['current_city']))? 'sankt-peterburg': $_COOKIE['current_city'];

		$promo_module = EnabledModules::select('id')->where('slug','promo')->first();
		$vehicle_module = EnabledModules::select('id','slug')->where('slug','tipy_transporta')->first();
		$city_module = EnabledModules::select('id')->where('slug','spisok_gorodov')->first();

		$current_city = Categories::select('title')->where('slug','=',$current_city)->where('module_id','=',$city_module->id)->first();
		//Parent category of vehicle type
		$default_vehicle_types = \DB::table('tbl_categories')->select('slug')->where('module_id','=',$vehicle_module->id)->where('refer_to','=',0)->where('enabled','=',1)->get();
		$vehicle_types = [];
		foreach($default_vehicle_types as $item){
			$vehicle_types[] = $item->slug;
		}
		if(in_array($url[1], $vehicle_types)){
			$driver_type = $url[1];
		}else{
			$driver_type = (isset($url[2]))? $url[2]: $vehicle_types[0];
		}
		$driver_type_id = \DB::table('tbl_categories')->select('id')->where('module_id','=',$vehicle_module->id)->where('enabled','=',1)->where('slug','=',$driver_type)->first();
		// /Parent category of vehicle type

		//Subcategory of vehicle type
		$vehicle_category = Categories::select('id','refer_to')->where('module_id','=',$vehicle_module->id)->where('enabled','=',1)->where('slug','=',$url[1]);
		if(!in_array($url[1], $vehicle_types)){
			$vehicle_category = $vehicle_category->where('refer_to','=',$driver_type_id->id);
		}
		$vehicle_category = $vehicle_category->first();
		// /Subcategory of vehicle type

		//ids of subcategories of vehicle type
		$vehicle_ids = [];
		if($vehicle_category->refer_to == 0){
			$temp = Categories::select('id')->where('refer_to','=',$vehicle_category->id)->orderBy('position','asc')->get();
			foreach($temp as $item){
				$vehicle_ids[] = $item->id;
			}
		}else{
			$vehicle_ids = [$vehicle_category->id];
		}
		// /ids of subcategories of vehicle type
		$car_array = [];
		//get all car ids by current mark, models, vehicle subcategories
		foreach($vehicle_ids as $car_vehicle_id){
			$car_vehicle_data = \DB::table('tbl_categories')->select('custom_fields')->find($car_vehicle_id);
			$car_vehicle_marks = unserialize($car_vehicle_data->custom_fields);

			$car_vehicle_marks = $car_vehicle_marks[0]['category_0']['value'];
			usort($car_vehicle_marks, function($a, $b){
				return $a->position > $b->position;
			});
			foreach($car_vehicle_marks as $mark){
				if($mark->refer_to == 0){
					foreach($car_vehicle_marks as $model){
						if($model->refer_to == $mark->id){
							$cars = \DB::table('tbl_products')->select('id','title','slug','img_url','price','color','custom_fields')
								->where('enabled', '=', 1)
								->where('custom_fields', 'LIKE', '%category_0";a:3:{s:5:"value";a:1:{i:0;s:'.strlen($car_vehicle_id).':"'.$car_vehicle_id.'"%')
								->where('custom_fields', 'LIKE', '%category_1";a:3:{s:5:"value";a:1:{i:0;s:'.strlen($model->id).':"'.$model->id.'"%')
								->orderBy('price','asc')
								->get();
							if(!empty($cars)){
								foreach($cars as $car){
									$car_data = self::convertCustomFields(unserialize($car->custom_fields));
									$add_by_city = false;
									if( (isset($car_data['category_4'])) && (!empty($car_data['category_4'])) ){
										foreach($car_data['category_4'] as $city){
											if($current_city->title == $city['title']){
												$add_by_city = true;
											}
										}
									}else{
										$add_by_city = true;
									}

									$allow_to_add = false;
									foreach($car_data['category_0'] as $current_vehicle_type){
										if(in_array($current_vehicle_type['id'], $vehicle_ids)){
											$allow_to_add = true;
											break;
										}
									}
									$allow_by_recomended = true;
									if( (isset($data['recomended'])) && ($data['recomended'] == 1) ){
										$allow_by_recomended = false;
										if(isset($car_data['fieldset_2'])){
											if($car_data['fieldset_2']['checkbox_0']['value'] == 1){
												$allow_by_recomended = true;
											}
										}
									}

									if( ($allow_to_add) && ($add_by_city) && ($allow_by_recomended)){
										$discount = [];
										$car_in_promo = Promo::select('discount','custom_fields')
											->where('module_id','=',$promo_module->id)
											->where('custom_fields','LIKE','%'.$car->id.'%')
											->whereDate('date_start','<=',date('Y-m-d H:i:s'))
											->whereDate('date_finish','>=',date('Y-m-d H:i:s'))
											->get();
										foreach($car_in_promo as $iter => $item){
											$promo_cars = self::convertCustomFields(unserialize($item->custom_fields));
											if(in_array($car->id, $promo_cars['products_0']['value'])){
												$discount = $item->discount;
											}
										}
										$color = unserialize($car->color);
										if(!empty($color)){
											$color = [
												'color' => $color[0]->color,
												'title' => $color[0]->title
											];
											$slug = $car->slug.'-'.Functions::str2url($color['title']);
										}else{
											$color = [];
											$slug = $car->slug;
										}
										$images = unserialize($car->img_url);
										$img = (!empty($images))? $images[0]: [];

										$price = '';
										if($car->price > 0){
											$price = $car->price;
										}else{
											foreach($car_data['fieldset_0'] as $field_name => $field_val){
												if($field_val['value'] != ''){
													$price = $field_val['value'];
													break;
												}
											}
										}
										if($price == ''){
											$price = 0;
										}

										$car_array[] = [
											'id'		=> $car->id,
											'title'		=> $car->title,
											'slug'		=> $slug,
											'img_url'	=> $img,
											'price'		=> $price,
											'color'		=> $color,
											'data'		=> $car_data,
											'upper_cat'	=> $driver_type,
											'promo'		=> $discount
										];
									}
								}
							}
						}
					}
				}
			}
		}

		switch($data['sort']){
			case 'cheap_to_costly':
				usort($car_array, function($a, $b){
					return $a['price'] > $b['price'];
				});
				break;
			case 'costly_to_cheap':
				usort($car_array, function($a, $b){
					return $a['price'] < $b['price'];
				});
				break;
		}
		$car_array = array_slice($car_array,$data['items'],$data['take']);

		return json_encode($car_array);
	}

	public function getCarsByFilter(Request $request){
		$data = $request->all();
		$data = json_decode($data['filter']);
		$current_city = (!isset($_COOKIE['current_city']))? 'sankt-peterburg': $_COOKIE['current_city'];

		$vehicle_module = EnabledModules::select('id')->where('slug','=','tipy_transporta')->first();
		$marks_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();
		$promo_module = EnabledModules::select('id')->where('slug','promo')->first();
		$upper_category = Categories::select('id','slug')
			->where('module_id','=',$vehicle_module->id)
			->where('refer_to','=',0)
			->where('slug','=',$data->parent)
			->where('enabled','=',1)
			->first();

		$vehicle_category = Categories::select('id','custom_fields')
			->where('module_id','=',$vehicle_module->id)
			->where('refer_to','=',$upper_category->id)
			->where('slug','=',$data->category)
			->where('enabled','=',1)
			->first();
		$custom_fields = unserialize($vehicle_category->custom_fields);

		$marks = $custom_fields[0]['category_0']['value'];
		usort($marks, function($a, $b){
			return $a->position > $b->position;
		});

		if(!empty($data->mark)){
			$current_mark = Categories::select('id')
				->where('module_id','=',$marks_module->id)
				->where('refer_to','=',0)
				->where('slug','=',$data->mark)
				->where('enabled','=',1)
				->first();
			if(empty($current_mark)){
				return [];
			}
		}else{
			$current_mark = [];
		}

		$car_array = [];
		foreach($marks as $mark){
			if($mark->refer_to == 0){
				if(!empty($current_mark)){
					$allow_by_mark = ($current_mark->id == $mark->id)? true: false;
				}else{
					$allow_by_mark = true;
				}
				if($allow_by_mark){
					foreach($marks as $model){
						if($model->refer_to == $mark->id){
							$cars = \DB::table('tbl_products')->select('id','title','price','slug','color','custom_fields','img_url')
								->where('enabled', '=', 1)
								->where('custom_fields', 'LIKE', '%category_0";a:3:{s:5:"value";a:1:{i:0;s:'.strlen($vehicle_category->id).':"'.$vehicle_category->id.'"%')
								->where('custom_fields', 'LIKE', '%category_1";a:3:{s:5:"value";a:1:{i:0;s:'.strlen($model->id).':"'.$model->id.'"%')
								/*->where('price','>=',$data->min)
								->where('price','<=',$data->max)*/
								->orderBy('price','asc')
								->get();
							if(!empty($cars)){
								foreach($cars as $car){
									$car_data = Helpers::convertCustomFields(unserialize($car->custom_fields));
									if($car_data['category_0'][0]['id'] == $vehicle_category->id){
										$colors = unserialize($car->color);
										$price = '';
										if($upper_category->slug == 'car_with_driver'){
											$price = $car->price;
										}else{
											foreach($car_data['fieldset_0'] as $field_name => $field_val){
												if($field_val['value'] != ''){
													$price = $field_val['value'];
													break;
												}
											}
										}
										if($price == ''){
											$price = 0;
										}
										if( ($price >= $data->min) && ($price <= $data->max) ){
											$add_by_city = false;
											if( (isset($car_data['category_4'])) && (!empty($car_data['category_4'])) ){
												foreach($car_data['category_4'] as $city){
													if($current_city == $city['slug']){
														$add_by_city = true;
													}
												}
											}else{
												$add_by_city = true;
											}
											if(!empty($data->car_event)){
												$add_by_event = false;
												if(isset($car_data['category_5'])){
													foreach($car_data['category_5'] as $events){
														if($events['slug'] == $data->car_event){
															$add_by_event = true;
															break;
														}
													}
												}
											}else{
												$add_by_event = true;
											}
											if(!empty($data->color)){
												$add_by_color = false;
												foreach($colors as $car_color){
													if(Functions::str2url($car_color->title) == $data->color){
														$add_by_color = true;
														break;
													}
												}
											}else{
												$add_by_color = true;
											}
											if( ($add_by_city) && ($add_by_event) && ($add_by_color) ){
												$discount = [];
												$car_in_promo = Promo::select('discount','custom_fields')
													->where('module_id','=',$promo_module->id)
													->where('custom_fields','LIKE','%'.$car->id.'%')
													->whereDate('date_start','<=',date('Y-m-d H:i:s'))
													->whereDate('date_finish','>=',date('Y-m-d H:i:s'))
													->get();
												foreach($car_in_promo as $iter => $item){
													$promo_cars = self::convertCustomFields(unserialize($item->custom_fields));
													if(in_array($car->id, $promo_cars['products_0']['value'])){
														$discount = $item->discount;
													}
												}
												$color = unserialize($car->color);
												if(!empty($color)){
													$color = [
														'color' => $color[0]->color,
														'title' => $color[0]->title
													];
													$slug = $car->slug.'-'.Functions::str2url($color['title']);
												}else{
													$color = [];
													$slug = $car->slug;
												}
												$images = unserialize($car->img_url);
												$img = (!empty($images))? $images[0]: [];
												$car_array[] = [
													'id'		=> $car->id,
													'title'		=> $car->title,
													'slug'		=> $slug,
													'img_url'	=> $img,
													'price'		=> $price,
													'color'		=> $color,
													'data'		=> $car_data,
													'upper_cat'	=> $upper_category->slug,
													'promo'		=> $discount
												];
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		switch($data->sort_by){
			case 'cheap_to_costly':
				usort($car_array, function($a, $b){
					return $a['price'] > $b['price'];
				});
			break;
			case 'costly_to_cheap':
				usort($car_array, function($a, $b){
					return $a['price'] < $b['price'];
				});
			break;
		}
		$car_array = array_slice($car_array,$data->items, 6);
		return json_encode($car_array);
	}

	public function getLocations(Request $request){
		$data = $request->all();
		$city_module = EnabledModules::select('id')->where('slug','=','spisok_gorodov')->first();
		$city = Categories::select('id')->where('module_id','=',$city_module->id)
			->where('enabled','=',1)
			->where('slug','=',$data['city'])
			->first();
		$locations = Categories::select('title','slug','custom_fields')->where('module_id','=',$city_module->id)
			->where('enabled','=',1)
			->where('refer_to','=',$city->id)
			->orderBy('position','asc')
			->get();
		$locations_list = [];
		foreach($locations as $location){
			$data = self::convertCustomFields(unserialize($location->custom_fields));
			$take = (isset($data['number_0']))? $data['number_0']['value']: 0;
			$return = (isset($data['number_1']))? $data['number_1']['value']: 0;
			$locations_list[] = [
				'title'	=> $location->title,
				'slug'	=> $location->slug,
				'take'	=> $take,
				'return'=> $return
			];
		}
		return json_encode($locations_list);
	}

	public function mailSender(Request $request){
		$data = $request->all();
		$user = Auth::user();

		$city = (!isset($_COOKIE['current_city']))? 'sankt-peterburg': $_COOKIE['current_city'];

		$city_module = EnabledModules::select('id')->where('slug','=','spisok_gorodov')->first();
		$excursion_module = EnabledModules::select('id')->where('slug','=','ekskursii')->first();
		$romantic_meet_module = EnabledModules::select('id')->where('slug','=','romanticheskie_vstrechi')->first();
		$vehicle_types = EnabledModules::select('id')->where('slug','=','tipy_transporta')->first();
		$marks_and_models = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();

		$city_caption = Categories::select('title')
			->where('module_id','=',$city_module->id)
			->where('slug','=',$city)
			->first();
		$city = (!empty($city_caption))? $city_caption->title: 'Неизвестно';

		$letter = PageContent::select('title','caption','type','content')
			->where('type','=','mail_template')
			->where('caption','=',$data['type'])
			->first();
		$content = unserialize($letter->content);

		switch($data['type']){
			case 'obratnyj_zvonok':
				$keys = ['[%city%]'];
				$values = [$city];
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type') ){
						$keys[] = '[%'.$key.'%]';
						$values[] = trim($value);
					}
				}

				$mail_body = str_replace($keys, $values, $content['text']);
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
				</html>';

				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$content['sender'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: ".$content['replyer']."\r\n";
				}
				$result = mail($content['receiver'] , $letter->title, $message, $headers);
				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'zakaz_romanticheskoj_vstrechi':
			case 'zakaz_ekskursii':
				$excursion = ($data['type'] == 'zakaz_ekskursii')
					? Articles::select('title')->where('module_id','=',$excursion_module->id)->where('slug','=',$data['path'])->first()
					: Articles::select('title')->where('module_id','=',$romantic_meet_module->id)->where('slug','=',$data['path'])->first();
				$excursion_tarif = Categories::select('title','custom_fields')->find($data['tarif']);
				$tarif_data = self::convertCustomFields(unserialize($excursion_tarif->custom_fields));

				$keys = ['[%city%]','[%url%]', '[%link%]', '[%name%]', '[%tel%]', '[%date%]'];
				$values = [$city, \URL::asset('/excursion/'.$data['path']), $excursion->title, $data['name'], $data['tel'], $data['date']];
				$mail_body = str_replace($keys, $values, $content['text']);
				$mail_body .= '<table><tr><td>Тип тарифа: '.$excursion_tarif->title.'</td></tr></table>';
				$mail_body .= '<table><tr><td>Цена: '.number_format($tarif_data['string_0']['value'],0,'',' ').'</td></tr></table>';
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
				</html>';
				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$content['sender'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: ".$content['replyer']."\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);
				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'oformlenie_zayavki':
				$keys = ['[%city%]'];
				$values = [$city];
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type') && ($key != 'order_case')){
						$keys[] = '[%'.$key.'%]';
						$values[] = trim($value);
					}
				}
				$mail_body = str_replace($keys, $values, $content['text']);

				if(isset($data['order_case'])){
					switch($data['order_case']){
						case '0': $letter_title = 'Рассчет стоимости аренды';break;
						case '1': $letter_title = 'Оформление заявки'; break;
						case '2':
							$letter_title = 'Заявка на внесение инвестиций';
							$mail_body .= '<p>Сумма инвестиций: '.$data['etc'].'</p>';
						break;
						case '3':
							$temp = json_decode($data['etc']);
							$mail_body .= '<p>Инвестиции:</p>';
							for($i=0; $i<count($temp); $i++){
								if($i == 4){
									$mail_body .= '<p>Доходы:</p>';
								}
								$mail_body .= '<p>'.$temp[$i]->title.' '.$temp[$i]->val.' руб.</p>';
							}
						break;
						default:
							$test_for_about = substr($data['order_case'], 0, 6);
							if($test_for_about == 'about_'){
								$letter_title = substr($data['order_case'], 6);
							}else{
								$letter_title = 'Быстрый заказ: '.$data['order_case'];
							}
					}
				}else{
					$letter_title = $letter->title;
				}
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter_title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
				</html>';
				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$content['sender'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: ".$content['replyer']."\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);
				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'zapros_korporativnogo_dogovora':
				$keys = [];
				$values = [];
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type') ){
						$keys[] = '[%'.$key.'%]';
						$values[] = trim($value);
					}
				}
				$mail_body = str_replace($keys, $values, $content['text']);
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
				</html>';
				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$content['sender'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: ".$content['replyer']."\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);
				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'pismo':
				$keys = [];
				$values = [];
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type') ){
						$keys[] = '[%'.$key.'%]';
						$values[] = trim($value);
					}
				}
				$mail_body = str_replace($keys, $values, $content['text']);
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><p>'.$letter->title.'</p>'.$mail_body.'</body>
				</html>';

				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$data['email'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: ".$content['replyer']."\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);
				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'zakaz_bez_voditelya':
				$keys = [];
				$values = [];
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type') && ($key != 'cat')){
						$keys[] = '[%'.$key.'%]';
						switch($key){
							case 'chosen_tarif':
								$values[] = str_replace(["\t","\n","\r"], '', trim($data['chosen_tarif']['title']).' '.trim($data['chosen_tarif']['price']));
							break;
							case 'recomended_tarif':
								$values[] = trim($data['recomended_tarif']['title']).' '.trim($data['recomended_tarif']['price']);
							break;
							case 'car_equip':
								$temp = '';
								foreach($data['car_equip'] as $car_equip){
									$temp .= str_replace(["\t","\n","\r"], '', trim($car_equip['title'])).' '.str_replace(["\t","\n","\r"], '', trim($car_equip['price'])).'; ';
								}
								$values[] = $temp;
							break;
							case 'ride_out':
								$temp = '';
								foreach($data['ride_out'] as $ride_out){
									$temp .= str_replace(["\t","\n","\r"], '', trim($ride_out)).'; ';
								}
								$values[] = $temp;
							break;
							default: $values[] = str_replace(["\t","\n","\r"], '', trim($value));
						}
					}
				}
				if( (!empty($data['info_surname'])) && (!empty($data['info_driver_id'])) ){
					$etc_data  = '<p>Пользователь пожелал заполнить все данные, чтобы все документы были готовы к его приезду</p>';
					$etc_data .= '<p>ФИО: '.trim($data['info_surname']).' '.trim($data['info_name']).' '.trim($data['info_fathername']).'</p>';
					$etc_data .= '<p>Серия, номер водительского удостоверения: '.trim($data['info_driver_id']).'; Дата выдачи: '.trim($data['info_drive_date']).'</p>';
					$etc_data .= '<p>Гражданство: '.trim($data['info_citizenship']).'</p>';
					$etc_data .= '<p>Серия, номер паспорта: '.trim($data['info_pasport_id']).'; Выдан '.trim($data['info_passport_issue']).'; Дата выдачи: '.trim($data['info_passport_date']).'</p>';
					$etc_data .= '<p>Регистрация по месту жительства: '.trim($data['info_registration']);
					$etc_data .= (!empty($data['info_address_phone']))? '; Телефон по адресу регистрации: '.trim($data['info_address_phone']).'</p>': ';</p>';
					$etc_data .= '<p>Адрес фактического проживания: '.trim($data['info_address']);
					$etc_data .= (!empty($data['info_fact_phone']))? '; Телефон по фактическому адресу: '.trim($data['info_fact_phone']).'</p>': ';</p>';
				}else{
					$etc_data = '';
				}

				$mail_body = str_replace($keys, $values, $content['text']);
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.$etc_data.'</body>
				</html>';

				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$content['sender'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: no-reply\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);

				//to user
				if(!empty($data['usermail'])){
					$letter = PageContent::select('title','caption','type','content')
						->where('type','=','mail_template')
						->where('caption','=','zakaz_bez_voditelya_pismo_polzovatelyu')
						->first();
					$content = unserialize($letter->content);
					$mail_body = str_replace($keys, $values, $content['text']);
					$message = '
					<html>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<head><title>'.$letter->title.'</title></head>
						<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
					</html>';
					$headers  = "Content-type: text/html; charset=utf-8 \r\n";
					$headers .= 'From: '.$letter->title.' <'.$content['receiver'].">\r\n";
					$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
					if($res){
						$headers .= "Reply-To: no-reply\r\n";
					}
					mail($data['usermail'] , $letter->title, $message, $headers);
				}

				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'zakaz_s_voditelem':
				$keys = [];
				$values = [];
				if( (!isset($data['car_equip'])) || (empty($data['car_equip'])) ){
					$data['car_equip'] = [[
						'title' => 'Отсутствует',
						'price' => ''
					]];
				}
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type') && ($key != 'cat')){
						$keys[] = '[%'.$key.'%]';
						switch($key){
							case 'chosen_tarif':
								$values[] = str_replace(["\t","\n","\r"], '', trim($data['chosen_tarif']['title']).' '.trim($data['chosen_tarif']['price']));
							break;
							case 'car_equip':
								$temp = '';
								foreach($data['car_equip'] as $car_equip){
									$temp .= str_replace(["\t","\n","\r"], '', trim($car_equip['title'])).' '.str_replace(["\t","\n","\r"], '', trim($car_equip['price'])).'; ';
								}
								$values[] = $temp;
							break;
							default: $values[] = str_replace(["\t","\n","\r"], '', trim($value));
						}
					}
				}
				$mail_body = str_replace($keys, $values, $content['text']);
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
				</html>';

				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$letter->title.' <'.$content['sender'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: no-reply\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);
				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
			case 'onlajn_zayavka_s_voditelem':
			case 'onlajn_zayavka':
				if(empty($user)){
					return 'not_logged_in';
				}else{
					$keys = ['[%username%]','[%usertel%]','[%type%]'];
					if( (isset($data['calculator-place-back'])) && (isset($data['calculator-place-pay'])) ){
						$values = [$user['name'], $user['phone'],'Без водителя'];
						foreach($data as $key => $value){
							if( ($key != 'type') && ($key != '_token') && ($key != '_url')){
								switch($key){
									case 'calculator-place-back':
									case 'calculator-place-pay':
									case 'calculator-place':
									case 'calculator-city':
										$city = Categories::select('title')->where('module_id','=',$city_module->id)
											->where('slug','=',$value)
											->where('enabled','=',1)
											->first();
										if(!empty($city)){
											$keys[] = '[%'.substr($key,11).'%]';
											$values[] = $city->title;
										}
										break;
									case 'datepicker-start':
										$keys[] = '[%date-start%]';
										if(empty($data['hourpicker-start'])) $data['hourpicker-start']= '00';
										if(empty($data['minutepicker-start'])) $data['minutepicker-start']= '00';
										$values[] = substr($value.' '.$data['hourpicker-start'].':'.$data['minutepicker-start'],0 ,15);
										break;
									case 'datepicker-end':
										$keys[] = '[%date-end%]';
										if(empty($data['hourpicker-end'])) $data['hourpicker-end']= '00';
										if(empty($data['minutepicker-end'])) $data['minutepicker-end']= '00';
										$values[] = substr($value.' '.$data['hourpicker-end'].':'.$data['minutepicker-end'],0 ,15);
										break;
									case 'calculator-car-type':
										$vehicle_type = Categories::select('title')->where('module_id','=',$vehicle_types->id)
											->where('slug','=',$value)
											->where('refer_to','!=',0)
											->where('enabled','=',1)
											->first();
										if(!empty($vehicle_type)) {
											$keys[] = '[%car-type%]';
											$values[] = $vehicle_type->title;
										}
										break;
									case 'calculator-car-brand':
										$mark = Categories::select('title')->where('module_id','=',$marks_and_models->id)
											->where('slug','=',$value)
											->where('refer_to','=',0)
											->where('enabled','=',1)
											->first();
										if(!empty($mark)){
											$keys[] = '[%mark%]';
											$values[] = $mark->title;
										}
										break;
									case 'calculator-car-model':
										$model = Categories::select('title')->where('module_id','=',$marks_and_models->id)
											->where('slug','=',$value)
											->where('refer_to','!=',0)
											->where('enabled','=',1)
											->first();
										if(!empty($model)){
											$keys[] = '[%model%]';
											$values[] = $model->title;
										}
										break;
									default:
										if( ($key != 'hourpicker-start') && ($key != 'minutepicker-start') && ($key != 'hourpicker-end') && ($key != 'minutepicker-end') ){
											$keys[] = '[%'.$key.'%]';
											$values[] = $value;
										}
								}
							}
						}
                                                $mail_body = str_replace($keys, $values, $content['text']);
						$message = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><head><title>'.$letter->title.'</title></head><body><p>'.$letter->title.'</p>'.$mail_body.'</body></html>';

						$headers  = "Content-type: text/html; charset=utf-8 \r\n";
						if(!empty($user['email'])){
							$headers .= 'From: '.$user['email'].">\r\n";
						}
						$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['receiver']);
						if( ($res) && (!empty($content['replyer'])) ){
							$headers .= "Reply-To: ".$content['replyer']."\r\n";
						}

						$result = mail($content['receiver'] , $letter->title, $message, $headers);
						if($result){
							return 'success';
						}else{
							$errorMessage = error_get_last()['message'];
							return $errorMessage;
						}
					}else{
						$values = [$user['name'], $user['phone'],'С водителем'];
						foreach($data as $key => $value){
							if( ($key != 'type') && ($key != '_token') && ($key != '_url')){
								switch($key){
									case 'calculator-city':
										$city = Categories::select('title')->where('module_id','=',$city_module->id)
											->where('slug','=',$value)
											->where('enabled','=',1)
											->first();
										if(!empty($city)){
											$keys[] = '[%'.substr($key,11).'%]';
											$values[] = $city->title;
										}
										break;
									case 'datepicker-start':
										$keys[] = '[%date-start%]';
										if(empty($data['hourpicker-start'])) $data['hourpicker-start']= '00';
										if(empty($data['minutepicker-start'])) $data['minutepicker-start']= '00';
										$values[] = substr($value.' '.$data['hourpicker-start'].':'.$data['minutepicker-start'],0, 15);
										break;
									case 'datepicker-end':
										$keys[] = '[%date-end%]';
										if(empty($data['hourpicker-end'])) $data['hourpicker-end']= '00';
										if(empty($data['minutepicker-end'])) $data['minutepicker-end']= '00';
										$values[] = substr($value.' '.$data['hourpicker-end'].':'.$data['minutepicker-end'],0 ,15);
										break;
									case 'calculator-car-type':
										$vehicle_type = Categories::select('title')->where('module_id','=',$vehicle_types->id)
											->where('slug','=',$value)
											->where('refer_to','!=',0)
											->where('enabled','=',1)
											->first();
										if(!empty($vehicle_type)) {
											$keys[] = '[%car-type%]';
											$values[] = $vehicle_type->title;
										}
										break;
									case 'calculator-car-brand':
										$mark = Categories::select('title')->where('module_id','=',$marks_and_models->id)
											->where('slug','=',$value)
											->where('refer_to','=',0)
											->where('enabled','=',1)
											->first();
										if(!empty($mark)){
											$keys[] = '[%mark%]';
											$values[] = $mark->title;
										}
										break;
									case 'calculator-car-model':
										$model = Categories::select('title')->where('module_id','=',$marks_and_models->id)
											->where('slug','=',$value)
											->where('refer_to','!=',0)
											->where('enabled','=',1)
											->first();
										if(!empty($model)){
											$keys[] = '[%model%]';
											$values[] = $model->title;
										}
										break;
									default:
										if( ($key != 'hourpicker-start') && ($key != 'minutespicker-start') && ($key != 'hourpicker-end') && ($key != 'minutespicker-end') ){
											$keys[] = '[%'.$key.'%]';
											$values[] = $value;
										}
								}
							}
						}
						$mail_body = str_replace($keys, $values, $content['text']);
						$message = '
						<html>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<head><title>'.$letter->title.'</title></head>
							<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
						</html>';

						$headers  = "Content-type: text/html; charset=utf-8 \r\n";
						$headers .= 'From: '.$user['email'].">\r\n";
						$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
						if($res){
							$headers .= "Reply-To: ".$content['replyer']."\r\n";
						}

						$result = mail($content['receiver'] , $letter->title, $message, $headers);
						if($result){
							return 'success';
						}else{
							$errorMessage = error_get_last()['message'];
							return $errorMessage;
						}
					}
				}
			break;
			case 'operativnyj_lizing':
			case 'arenda_avtomobilya_s_vykupom':
				$keys = ['[%city%]'];
				$values = [$city];
				foreach($data as $key => $value){
					if( ($key != '_token') && ($key != 'type')){
						$keys[] = '[%'.$key.'%]';
						$values[] = trim($value);
					}
				}
				$mail_body = str_replace($keys, $values, $content['text']);
				$message = '
				<html>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<head><title>'.$letter->title.'</title></head>
					<body><table><tr><td>'.$letter->title.'</td></tr></table>'.$mail_body.'</body>
				</html>';
				$headers  = "Content-type: text/html; charset=utf-8 \r\n";
				$headers .= 'From: '.$user['email'].">\r\n";
				$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $content['replyer']);
				if($res){
					$headers .= "Reply-To: ".$content['replyer']."\r\n";
				}

				$result = mail($content['receiver'] , $letter->title, $message, $headers);

				if($result){
					return 'success';
				}else{
					$errorMessage = error_get_last()['message'];
					return $errorMessage;
				}
			break;
		}
	}

	public function subscribeUs(Request $request){
		$data = $request->all();
		if(isset($data['email'])){
			$res = preg_match('/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/', $data['email']);
			if($res){
				$mail_isset = Subscribers::where('email','=',trim($data['email']))->count();
				if($mail_isset < 1){
					$result = Subscribers::create([
						'email' => $data['email']
					]);
					if($result != false){
						return 'success';
					}
				}else{
					return 'success';
				}
			}
		}
	}

	public function calculateRent(Request $request){
		$data = $request->all();
		$car = Products::select('price','custom_fields')->find($data['id']);
		$car_data = self::convertCustomFields(unserialize($car->custom_fields));
		usort($car_data['category_2'], function($a, $b){
			return $a['position'] > $b['position'];
		});
		$current_tarif = [];

		if($data['type'] == 'car_nonedriver'){
			switch(true){
				case (($data['start_day']==6) && ($data['finish_day']==0) && ($data['days'] == 2)):
					$current_tarif = $car_data['category_2'][4];
					break;
				case (($data['start_day']==1) && ($data['finish_day']==5) && ($data['days'] == 5)):
					$current_tarif = $car_data['category_2'][3];
					break;
				default:
					foreach($car_data['category_2'] as $tarif){
						$num = trim(preg_replace("/[^0-9]/", ' ', $tarif['title']));
						if(!empty($num)){
							$num = explode(' ', $num);
							if(count($num) < 2){
								if($data['days'] >= $num[0]){
									$current_tarif = $tarif;
									break;
								}
							}else{
								if( ($data['days'] >= $num[0]) && ($data['days'] <= $num[1]) ){
									$current_tarif = $tarif;
									break;
								}
							}
						}
					}
			}
			if(!empty($current_tarif)){
				if(empty($current_tarif['data']['string_0']['value'])){
					if(strpos('сутки',$current_tarif['data']['string_1']['value']) == false){
						$price = trim(preg_replace("/[^0-9]/", '', $current_tarif['data']['string_1']['value']));
						$full_price = $price*$data['days'];
					}else{
						$price = trim(preg_replace("/[^0-9]/", '', $current_tarif['data']['string_1']['value']));
						$full_price = $price*$data['hours'];
					}
				}else{
					$full_price = $data['hours'] * $current_tarif['data']['string_0']['value'];
				}
				$full_price += $data['start_price'] + $data['finish_price'] + $car_data['fieldset_0']['number_7']['value'];
				return json_encode([
					'message'	=> 'success',
					'price'		=> $full_price
				]);
			}else{
				return json_encode([
					'message'	=> 'error'
				]);
			}
		}else{
			$full_price = -1;
			foreach($car_data['category_2'] as $item){
				if($item['title'] == 'По часам'){
					if($item['data']['string_0']['value'] == '[%price%]'){
						$full_price = $data['hours']*$car->price;
					}else{
						$full_price = $data['hours']*$item['data']['string_0']['value'];
					}
				}
			}
			if($full_price > 0){
				$full_price += $data['start_price'] + $data['finish_price'];
				return json_encode([
					'message'	=> 'success',
					'price'		=> $full_price
				]);
			}else{
				return json_encode([
					'message'	=> 'error'
				]);
			}
		}
	}
}