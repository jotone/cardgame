<?php
namespace App\Http\Controllers\Supply;
use App\AdminMenu;
use App\Articles;
use App\Categories;
use App\EnabledModules;
use App\MenuItems;
use App\Products;
use App\Promo;
use App\UserRoles;
use Auth;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Exception;

class Functions extends BaseController
{
	public static function getMicrotime(){
		$time = microtime();
		$time = explode(' ', $time);
		return $time[1] + $time[0];
	}

	public static function strip_data($text){
		$quotes = array("\x27", "\x22", "\x60", "\t", "\n", "\r", "*", "%", "<", ">", "?", "!" );
		$goodquotes = array("-", "+", "#" );
		$repquotes = array("\-", "\+", "\#" );
		$text = trim(strip_tags($text));
		$text = str_replace($quotes,'',$text);
		$text = str_replace($goodquotes,$repquotes,$text);
		$text = str_replace(" +"," ",$text);
		return $text;
	}

	public static function rgb2hex($rgb) {
		if(!is_array($rgb)){
			$rgb = substr($rgb,4,-1);
			$rgb = explode(', ',$rgb);
		}
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
		return $hex;
	}
	public static function rus2translit($string) {
		//Массив трансформации букв
		$converter = [
			'а'=>'a',	'б'=>'b',	'в'=>'v',	'г'=>'g',	'д'=>'d',	'е'=>'e',
			'ё'=>'e',	'ж'=>'zh',	'з'=>'z',	'и'=>'i',	'й'=>'j',	'к'=>'k',
			'л'=>'l',	'м'=>'m',	'н'=>'n',	'о'=>'o',	'п'=>'p',	'р'=>'r',
			'с'=>'s',	'т'=>'t',	'у'=>'u',	'ф'=>'f',	'х'=>'h',	'ц'=>'ts',
			'ч'=>'ch',	'ш'=>'sh',	'щ'=>'shch','ь'=>'',	'ы'=>'y',	'ъ'=>'',
			'э'=>'e',	'ю'=>'yu',	'я'=>'ya',	'і'=>'i',	'ї'=>'i',	'є'=>'ie',
			'А'=>'A',	'Б'=>'B',	'В'=>'V',	'Г'=>'G',	'Д'=>'D',	'Е'=>'E',
			'Ё'=>'E',	'Ж'=>'Zh',	'З'=>'Z',	'И'=>'I',	'Й'=>'J',	'К'=>'K',
			'Л'=>'L',	'М'=>'M',	'Н'=>'N',	'О'=>'O',	'П'=>'P',	'Р'=>'R',
			'С'=>'S',	'Т'=>'T',	'У'=>'U',	'Ф'=>'F',	'Х'=>'H',	'Ц'=>'Ts',
			'Ч'=>'Ch',	'Ш'=>'Sh',	'Щ'=>'Shch','Ь'=>'',	'Ы'=>'Y',	'Ъ'=>'',
			'Э'=>'E',	'Ю'=>'Yu',	'Я'=>'Ya',	'І'=>'I',	'Ї'=>'I',	'Є'=>'Ie'
		];
		//замена кирилицы входящей строки на латынь
		return strtr($string, $converter);
	}
	public static function str2url($str){
		$str = self::rus2translit($str);
		$str = strtolower($str);
		$str = preg_replace('~[^-a-z0-9_\.\#\/]+~u', '_', $str);
		$str = trim($str, "_");
		return $str;
	}
	public static function is_serialized($value, &$result = null){
		if (!is_string($value)){return false;}
		if ($value === 'b:0;'){
			$result = false;
			return true;
		}
		$length	= strlen($value);
		$end	= '';
		if(empty($value)) return false;
		switch ($value[0]){
			case 's': if ($value[$length - 2] !== '"'){return false;}
			case 'b':
			case 'i':
			case 'd': $end .= ';';
			case 'a':
			case 'O':
				$end .= '}';
				if($value[1] !== ':'){return false;}
				switch ($value[2]) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9: break;
					default: return false;
				}
			case 'N':
				$end .= ';';
				if($value[$length - 1] !== $end[0]){return false;}
				break;
			default: return false;
		}
		if (($result = @unserialize($value)) === false){
			$result = null;
			return false;
		}
		return true;
	}

	public static function getAlphabeticlist($list){
		$alphabet_list = [];
		foreach($list as $item){
			$first_letter = preg_split('//u',$item['title'],-1,PREG_SPLIT_NO_EMPTY);
			$first_letter = mb_strtolower($first_letter[0]);

			if(array_search(['sign' => mb_strtolower($first_letter),'items'=> []], $alphabet_list) === false){
				$alphabet_list[] = [
					'sign' => $first_letter,
					'items'=> []
				];
			}
		}
		asort($alphabet_list);
		$alphabet_list = array_values($alphabet_list);

		foreach($list as $item){
			$first_letter = preg_split('//u',$item['title'],-1,PREG_SPLIT_NO_EMPTY);
			$first_letter = mb_strtolower($first_letter[0]);
			foreach($alphabet_list as $key => $value){
				if($value['sign'] == $first_letter){
					$alphabet_list[$key]['items'][] = $item;
				}
			}
		}
		return $alphabet_list;
	}

	public static function buildElementListView($element, $value = []){
		$result = '<div class="chbox-selector-wrap">';
		foreach($element['items'] as $item){
			$checked = (in_array($item['id'], $value))? ' checked="checked"': '';
			$result .= '<div class="checkbox-item-wrap">';
			if(self::is_serialized($item['img_url'])){
				$img = unserialize($item['img_url']);
				$img = (!empty($img))? $img[0]: '';
			}else{
				$img = $item['img_url'];
			}
			if(!empty($img)){
				$result .='<div class="tac"><img src="'.$img['img'].'" alt="'.$img['alt'].'"></div>';
			}
			$result .= '
				<label class="fieldset-label-wrap">
					<input name="category" type="checkbox" class="chbox-input" value="'.$item['id'].'"'.$checked.'>
					<span>'.$item['title'].'</span>
				</label>
			</div>';
		}
		$result .= '</div>';
		return $result;
	}

	public static function assocListToFlat($list, $result = [], $parent_title=''){
		$parent_title = ($parent_title != '')? $parent_title.' &rarr; ': $parent_title;
		foreach($list as $item){
			$image = (isset($item['img_url']))? $item['img_url']: '';
			$result[] = [
				'id'		=> $item['id'],
				'title'		=> $item['title'],
				'slug'		=> $item['slug'],
				'img_url'	=> $image,
				'enabled'	=> $item['enabled'],
				'created_at'=> $item['created_at'],
				'updated_at'=> $item['updated_at'],
				'parent_title'=> $parent_title.$item['title']
			];

			if(!empty($item['inner'])){
				$result = self::assocListToFlat($item['inner'], $result, $item['title']);
			}
		}
		return $result;
	}

	public static function convertDate($date){
		if(!empty($date)){
			$year = substr($date,0,4);
			$month = substr($date,5,2);
			switch($month){
				case '1': $month_name = 'Янв'; break;
				case '2': $month_name = 'Фев'; break;
				case '3': $month_name = 'Мар'; break;
				case '4': $month_name = 'Апр'; break;
				case '5': $month_name = 'Май'; break;
				case '6': $month_name = 'Июн'; break;
				case '7': $month_name = 'Июл'; break;
				case '8': $month_name = 'Авг'; break;
				case '9': $month_name = 'Сен'; break;
				case '10':$month_name = 'Окт'; break;
				case '11':$month_name = 'Ноя'; break;
				case '12':$month_name = 'Дек'; break;
			}
			$day = substr($date,8,2);
			$time = substr($date,11,5);
			return $day.'/'.$month_name.'/'.$year.' '.$time;
		}else{
			return 'Не известно';
		}

	}

	public static function assoc_in_array($needle, $array, $strict = false, $bool = false){
		foreach($array as $key => $value){
			if(!is_array($value)){
				if($strict){
					if($needle === $value) $bool = true;
				}else{
					if($needle == $value) $bool = true;
				}
			}else{
				if(!empty($value)) $bool = self::assoc_in_array($needle, $value, $strict, $bool);
			}
		}
		return $bool;
	}

	public static function buildMenuList($path, $refer_to = 0){
		$path = (substr($path, 0,1) != '/')? '/'.$path: $path;

		$menu = AdminMenu::select('id','title','slug','position','module_id','refer_to','enabled')
			->where('refer_to','=',$refer_to)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();

		$result= '';
		if(isset($menu[0])){
			$result .= '<ul>';
			foreach($menu as $item){
				$count = AdminMenu::select('refer_to','enabled')
					->where('refer_to','=',$item->id)
					->where('enabled','=',1)
					->count();
				$cross = ($count > 0)? 'has_child': '';

				$item_slug = (0 != $item->module_id)? '/admin/'.$item->slug: $item->slug;

				$active = ($path == $item_slug)? ' active': '';
				$result.= '<li data-id="'.$item->id.'"><a href="'.$item_slug.'" class="'.$cross.$active.'">'.$item->title.'</a>';
				if($count > 0){
					$result.= self::buildMenuList($path, $item->id);
				}
				$result.= '</li>';
			}
			$result .= '</ul>';
		}

		return $result;
	}

	public static function getAssocList($type, $refer_to = 0, $module_id = 0, $order_by = []){
		$result = [];
		switch($type){
			case 'admin_menu':
				$elements = AdminMenu::select('id','title','slug','position','refer_to','enabled','created_at','updated_at')
					->where('refer_to','=',$refer_to)
					->orderBy('position','asc')
					->get();
			break;
			case 'categories':
				$elements = Categories::select('id','title','slug','img_url','position','refer_to','module_id','enabled','created_at','updated_at')
					->where('module_id','=',$module_id)
					->where('refer_to','=',$refer_to);
				if(!empty($order_by)){
					foreach($order_by as $order){
						$elements = $elements->orderBy($order[0],$order[1]);
					}
				}else{
					$elements = $elements->orderBy('position','asc');
				}
				$elements = $elements->get();
			break;
			case 'site_menu':
				$elements = MenuItems::select('id','title','slug','position','refer_to','module_id','enabled','created_at','updated_at')
					->where('module_id','=',$module_id)
					->where('refer_to','=',$refer_to)
					->orderBy('position','asc')
					->get();
			break;
		}
		if(isset($elements[0])){
			foreach($elements as $element){
				if(isset($element->img_url)) {
					$images = unserialize($element->img_url);
					$image = (!empty($images))? $images[0]: '';
				}else{
					$image = '';
				}

				switch($type){
					case 'admin_menu':
						$child_count = AdminMenu::select('refer_to')
							->where('refer_to','=',$refer_to)
							->count();
					break;
					case 'categories':
						$child_count = Categories::select('refer_to','module_id')
							->where('module_id','=',$module_id)
							->where('refer_to','=',$refer_to)
							->count();
					break;
					case 'site_menu':
						$child_count = MenuItems::select('refer_to','module_id')
							->where('module_id','=',$module_id)
							->where('refer_to','=',$refer_to)
							->count();
					break;
				}
				$inner = ($child_count > 0)? self::getAssocList($type, $element->id, $module_id,$order_by): [];

				$result[] = [
					'id'		=> $element->id,
					'title'		=> $element->title,
					'slug'		=> $element->slug,
					'enabled'	=> $element->enabled,
					'img_url'	=> $image,
					'created_at'=> self::convertDate($element->created_at),
					'updated_at'=> self::convertDate($element->updated_at),
					'inner'		=> $inner
				];
			}
		}

		return $result;
	}

	public static function buildCategoriesView($list, $build_controls = true, $slug='', $refer_to = 0){
		$result = '<ul data-refer="'.$refer_to.'">';
		foreach($list as $item){
			$disabled = ($item['enabled'] == 1)? ['',' trigger_on','on']: [' disabled',' trigger_off','off'];

			$result .= sprintf('
				<li data-id="%s">
					<div class="category-wrap%s">
						<div class="category-title">
							<div class="sort-controls">
								<p data-direction="up">&#9650;</p>
								<p data-direction="down">&#9660;</p></div>
							<div>%s</div>
						</div>
						<div class="category-slug">%s</div>
						<div class="timestamps">
							<p>Создан: %s</p>
							<p>Изменен: %s</p>
						</div>',
				$item['id'],$disabled[0],$item['title'],$item['slug'],$item['created_at'],$item['updated_at']);
			if($build_controls){
				if(empty($slug)){
					$link = '#';
				}else{
					$link = URL::asset('admin/'.$slug.'/edit/'.$item['id']);
				}
				$result .= sprintf('
					<div class="category-controls">
						<a class="button%s" href="#" title="Вкл/Выкл">%s</a>
						<a class="button edit" href="%s" title="Редактировать"><img src="%s" alt="Редактировать"></a>
						<a class="button drop" href="#" title="Удалить"><img src="%s" alt="Удалить"></a>
					</div>',$disabled[1],$disabled[2],$link,URL::asset('img/edit.png'),URL::asset('img/drop.png'));
			}
			$result .= '</div>';
			if(!empty($item['inner'])){
				$result .= self::buildCategoriesView($item['inner'], $build_controls, $slug, $item['id']);
			}
			$result .= '<ul class="empty" data-refer="'.$item['id'].'"></ul></li>';
		}
		$result .= '</ul>';

		return $result;
	}

	public static function buildCategorieSelector($list, $values = [], $parent_title = ''){
		$result = '';
		$allow_empty = true;
		foreach($list as $item){
			$link = (!empty(trim($parent_title)))? $parent_title.' &rarr; ': '';
			if(in_array($item['id'], $values)){
				$selected = ' selected="selected"';
				$allow_empty = false;
			}else{
				$selected = '';
			}
			if($allow_empty){
				$selected = ($item['title'] == '')? ' selected="selected"': '';
			}
			$result .= '<option value="'.$item['id'].'"'.$selected.'>'.$link.$item['title'].'</option>';
			if(!empty($item['inner'])){
				$result .= self::buildCategorieSelector($item['inner'], $values, $link.$item['title']);
			}
		}
		return $result;
	}

	public static function checkAccessToPage($path){
		$path = (substr($path, 0,1) != '/')? '/'.$path: $path;
		$admin = Auth::user();
		$admin_roles = UserRoles::select('pseudonim','access_pages')->where('pseudonim','=',$admin['user_role'])->first();
		$alowed_pages = unserialize($admin_roles->access_pages);

		$current_page = AdminMenu::select('id','slug')->where('slug','=',$path)->first();
		return (!in_array($current_page->id, $alowed_pages));
	}

	public static function moduleContent($custom_field){
		$result = '';
		switch($custom_field->type){
			case 'fieldset':
				$result .= '
					<fieldset class="tune-fieldset">
						<legend>
							<span data-type="caption">'.$custom_field->capt.'</span>
							<span class="drop-add-field">&times;</span>
						</legend>
						<div class="dropable-field">Перетащите сюда контент для вставки';
				if(!empty($custom_field->val)){
					foreach($custom_field->val as $item){
						$result .= self::moduleContent($item);
					}
				}
				$result .= '
						</div>
					</fieldset>';
			break;
			case 'string':
			case 'email':
			case 'textarea':
			case 'fulltext':
			case 'img_slider':
			case 'file_upload':
				$custom_type = 'Строка';
				if($custom_field->type == 'email') $custom_type = 'E-mail';
				if($custom_field->type == 'textarea') $custom_type = 'Текстовое поле';
				if($custom_field->type == 'fulltext') $custom_type = 'Полный текст';
				if($custom_field->type == 'img_slider') $custom_type = 'Слайдер изображений';
				if($custom_field->type == 'file_upload') $custom_type = 'Файл';
				$result .= '
					<div class="module-custom-elements-wrap">
						<div class="col_1_4">Название: <span data-type="caption">'.$custom_field->capt.'</span></div>
						<div class="col_1_2">Тип: '.$custom_type.'
						<input name="type" type="hidden" value="'.$custom_field->type.'">
						</div>
						<div class="col_1_4">
						<span class="drop-add-field">&times;</span>
						</div>
					</div>';
			break;
			case 'number':
			case 'range':
				$custom_type = 'Ввод чисел';
				if($custom_field->type == 'range') $custom_type = 'Ползунок';
				$result .= '
					<div class="module-custom-elements-wrap">
						<div class="col_1_4">Название: <span data-type="caption">'.$custom_field->capt.'</span></div>
						<div class="col_1_2">Тип: '.$custom_type.'
						<input name="type" type="hidden" value="'.$custom_field->type.'">
						<div>min:<span data-type="min">'.$custom_field->min.'</span>;</div>
						<div>max:<span data-type="max">'.$custom_field->max.'</span>;</div>
						<div>шаг:<span data-type="step">'.$custom_field->step.'</span>;</div>
						</div>
						<div class="col_1_4">
						<span class="drop-add-field">&times;</span>
						</div>
					</div>';
			break;
			case 'checkbox':
			case 'radio':
				$custom_type = 'Флажок';
				if($custom_field->type == 'radio') $custom_type = 'Переключатель';
				$result .= '
					<div class="module-custom-elements-wrap">
						<div class="col_1_4">Название: <span data-type="caption">'.$custom_field->capt.'</span></div>
						<div class="col_1_2">Тип: '.$custom_type.'
						<input name="type" type="hidden" value="'.$custom_field->type.'">;
						<div>Группа: <span data-type="group">'.$custom_field->group.'</span>;</div>
						<div>Значение: <span data-type="value">'.$custom_field->val.'</span>;</div>
						</div>
						<div class="col_1_4">
						<span class="drop-add-field">&times;</span>
						</div>
					</div>';
			break;
			case 'table':
				$result .= '
				<div class="module-custom-elements-wrap">
					<div class="col_1_4">Название: <span data-type="caption">'.$custom_field->capt.'</span></div>
					<div class="col_1_2">Тип: Таблица
					<input name="type" type="hidden" value="'.$custom_field->type.'">;
					Столбцов: <span data-type="value">'.$custom_field->val.'</span>;
					</div>
					<div class="col_1_4">
					<span class="drop-add-field">&times;</span>
					</div>
				</div>';
			break;
			case 'custom_slider':
				$sliderContent = '';
				foreach($custom_field->val as $custom_slider_item){
					switch($custom_slider_item->field){
						case 'image':	$name = 'Изображение'; break;
						case 'string':	$name = 'Строка'; break;
						case 'text':	$name = 'Текстовое поле'; break;
					}
					$sliderContent .= $name.':
					<span data-type="value" data-fieldtype="'.$custom_slider_item->field.'">'.$custom_slider_item->val.'</span>;<br>';
				}
				$result .= '
				<div class="module-custom-elements-wrap">
					<div class="col_1_4">Название: <span data-type="caption">'.$custom_field->capt.'</span></div>
					<div class="col_1_2">Тип: Настраиваемый слайдер;<br>
						<input name="type" type="hidden" value="'.$custom_field->type.'">'.$sliderContent.'
					</div>
					<div class="col_1_4">
						<span class="drop-add-field">&times;</span>
					</div>
				</div>';
			break;
			case 'articles':
			case 'category':
			case 'products':
			case 'promo':
				$content_type = 'Статьи';
				if($custom_field->type == 'category') $content_type = 'Категория';
				if($custom_field->type == 'products') $content_type = 'Товары';
				if($custom_field->type == 'promo') $content_type = 'Акции';
				$selected_category = EnabledModules::select('title')->find($custom_field->val);
				$mult = ($custom_field->mult == 1)? 'Да': 'Нет';
				if(isset($custom_field->pos_sel)){
					$pos_sel = ($custom_field->pos_sel == 1)? 'Да': 'Нет';
					$pos_val = $custom_field->pos_sel;
				}else{
					$pos_sel = 'Нет';
					$pos_val = 0;
				}

				$result .= '<div class="module-custom-elements-wrap">
					<div class="col_1_4">Название: <span data-type="caption">'.$custom_field->capt.'</span></div>
					<div class="col_1_2">Тип: '.$content_type.'
						<input name="type" type="hidden" value="'.$custom_field->type.'">;<br>
						'.$content_type.': <span data-type="name">'.$selected_category->title.'</span>;<br>
						Разрешить мультивыбор: '.$mult.'<input name="multSel" type="hidden" value="'.$custom_field->mult.'"><br>
						Разрешить позиционирование: '.$pos_sel.'<input name="posSel" type="hidden" value="'.$pos_val.'">
						<input name="value" type="hidden" value="'.$custom_field->val.'">
					</div>
					<div class="col_1_4">
						<span class="drop-add-field">&times;</span>
					</div>
				</div>';
			break;
		}
		return $result;
	}

	public static function buildDefaultFields($field_type, $value=[]){
		$result = '';
		switch($field_type){
			case 'date_begin':
				$date = (isset($value['date_start']))? date('Y-m-d',strtotime($value['date_start'])): '';
				$result .= '<fieldset>
					<legend>Дата начала</legend>
					<div class="row-wrap">
						<input type="text" class="text-input col_1_2 needDatePicker" name="date_begin" value="'.$date.'" placeholder="Дата начала&hellip;">
					</div>
				</fieldset>';
			break;
			case 'date_finish':
				$date = (isset($value['date_finish']))? date('Y-m-d',strtotime($value['date_finish'])): '';
				$result .= '<fieldset>
					<legend>Дата окончания</legend>
					<div class="row-wrap">
						<input type="text" class="text-input col_1_2 needDatePicker" name="date_finish" value="'.$date.'" placeholder="Дата окончания&hellip;">
					</div>
				</fieldset>';
			break;
			case 'description':
				$description = (isset($value['description']))? $value['description']: '';
				$result .= '<fieldset>
					<legend>Описание</legend>
					<div class="row-wrap">
						<textarea name="description" class="needCKE">'.$description.'</textarea>
					</div>
				</fieldset>';
			break;
			case 'text':
				$text = (isset($value['text']))? $value['text']: '';
				$result .= '<fieldset>
					<legend>Текст</legend>
					<div class="row-wrap">
						<textarea name="text" class="needCKE">'.$text.'</textarea>
					</div>
				</fieldset>';
			break;
			case 'text_caption':
				$text = (isset($value['text_caption']))? $value['text_caption']: '';
				$result .= '<fieldset>
					<legend>Заглавие текста</legend>
					<div class="row-wrap">
						<input name="text_caption" type="text" class="text-input col_1" value="'.$text.'" placeholder="Заглавие текста&hellip;">
					</div>
				</fieldset>';
			break;
			case 'price':
				$text = (isset($value['price']))? $value['price']: '';
				$result .= '<fieldset>
					<legend>Цена</legend>
					<div class="row-wrap">
						<input name="price" type="number" class="text-input col_1" value="'.$text.'" placeholder="Цена&hellip;">
					</div>
				</fieldset>';
			break;
			case 'img_url':
				if(isset($value['img_url'])){
					$images = (self::is_serialized($value['img_url']))? unserialize($value['img_url']): $value['img_url'];
				}else{
					$images = [];
				}
				$result .= self::buildSlider('regular_slider', 'Фото', $images);
			break;
		}
		return $result;
	}

	public static function buildCustomFields($fields, $values = [],  $fields_iterators = []){
		$result = '';
		foreach($fields as $field){
			$fields_iterators[$field->type] = 0;
		}
		$data = [];
		foreach($values as $i => $value){
			foreach($value as $field_name => $field_value){
				$data[$field_name] = $field_value;
			}
		}
		foreach($fields as $field){
			$name = $field->type.'_'.$fields_iterators[$field->type];
			switch($field->type){
				case 'fieldset':
					$temp = (isset($data[$name]['value']))
						? self::buildCustomFields($field->val, $data[$name]['value'], $fields_iterators)
						: self::buildCustomFields($field->val, [], $fields_iterators);
					$result .= sprintf('
					<fieldset data-name="%s" data-type="%s">
						<legend>%s</legend>
						%s
					</fieldset>', $name, $field->type, $field->capt, $temp);
				break;

				case 'radio':
				case 'checkbox':
					$checked = ((isset($data[$name]['value'])) && ($data[$name]['value'] == 1))? 'checked="checked"': '';
					$result .= sprintf('
					<div class="row-wrap" data-name="%1$s" data-type="%3$s">
						<label class="fieldset-label-wrap">
							<input name="%2$s" type="%3$s" class="chbox-input" value="%4$s" %5$s>
							<span>%6$s</span>
						 </label>
					</div>', $name, $field->group, $field->type, $field->val, $checked, $field->capt);
				break;

				case 'email':
				case 'string':
					$text = (isset($data[$name]['value']))? $data[$name]['value']: '';
					$result .= sprintf('
					<fieldset data-name="%1$s" data-type="%3$s">
						<legend>%2$s</legend>
						<div class="row-wrap">
							<input name="%1$s" type="%3$s" class="text-input col_1" value="%4$s">
						</div>
					</fieldset>', $name, $field->capt, $field->type, $text);
				break;

				case 'number':
				case 'range':
					$text = (isset($data[$name]['value']))? $data[$name]['value']: '';
					$result .= sprintf('
					<fieldset data-name="%1$s" data-type="%2$s">
						<legend>%3$s</legend>
						<div class="row-wrap">
							<input name="%1$s" type="%2$s" class="text-input col_1_2" min="%4$s" max="%5$s" step="%6$s" value="%7$s">
						</div>
					</fieldset>', $name, $field->type, $field->capt, trim($field->min), trim($field->max), trim($field->step), $text);
				break;

				case 'fulltext':
				case 'textarea':
					$text = (isset($data[$name]['value']))? $data[$name]['value']: '';
					$class = ($field->type != 'fulltext')? 'simple-text': 'needCKE';
					$result .= sprintf('
					<fieldset data-name="%1$s" data-type="%2$s">
						<legend>%3$s</legend>
						<div class="row-wrap">
							<textarea name="%1$s" class="%4$s">%5$s</textarea>
						</div>
					</fieldset>', $name, $field->type, $field->capt, $class, $text);
				break;

				case 'table':
					$temp_head = '';
					$temp_body = '';
					for($i=0; $i<=$field->val; $i++){
						if($i==0){
							$temp_head .= '<th></th>';
							$temp_body .= '<td>
							<a href="#" class="drop-row block-button" title="Удалить">
								<img src="/img/drop.png" alt="">
							</a>
						</td>';
						}else{
							$head_text = (isset($data[$name]['value']))? $data[$name]['value']['head'][$i-1]: '';
							$body_text = (isset($data[$name]['value']['body'][0]))? $data[$name]['value']['body'][0][$i-1]: '';
							$temp_head .= '<th><input name="tableHead" type="text" class="text-input" placeholder="Название столбца&hellip;" value="'.$head_text.'"></th>';
							$temp_body .= '<td><input name="tableBody" type="text" class="text-input" placeholder="Содержимое ячейки&hellip;" value="'.$body_text.'"></td>';
						}
					}
					if(isset($data[$name]['value'])){
						if(count($data[$name]['value']['body']) >1){
							for($i =1; $i<count($data[$name]['value']['body']); $i++){
								$temp_body .= '<tr>';
								for($j=0; $j<=$field->val; $j++){
									if($j==0){
										$temp_body .= '<td>
										<a href="#" class="drop-row block-button" title="Удалить">
											<img src="/img/drop.png" alt="">
										</a>';
									}else{
										$body_text = (isset($data[$name]['value']))? $data[$name]['value']['body'][$i][$j-1]: '';
										$temp_body .= '<td><input name="tableBody" type="text" class="text-input" placeholder="Содержимое ячейки&hellip;" value="'.$body_text.'"></td>';
									}
								}
								$temp_body .= '</tr>';
							}
						}
					}
					$result .= sprintf('
					<fieldset data-name="%s" data-type="%s">
						<legend>%s</legend>
						<div class="row-wrap">
							<table class="item-list col_1">
								<thead>
									<tr>%s</tr>
								</thead>
								<tbody>
									<tr>%s</tr>
								</tbody>
							</table>
						</div>
						<div class="row-wrap">
							<input name="addRowToTable" type="button" class="control-button" value="Добавить строку&hellip;">
						</div>
					</fieldset>', $name, $field->type, $field->capt, $temp_head, $temp_body);
				break;

				case 'img_slider':
					$images = (isset($data[$name]['value']))? $data[$name]['value']: [];
					$result .= self::buildSlider($name, $field->capt, $images);
				break;

				case 'custom_slider':
					$temp = '<div class="custom-slide-container active">
					<div class="row-wrap">
							<label class="fieldset-label-wrap">
							<input type="radio" name="previevBar" checked="checked">
							<span>Превью</span></label>
					</div>';
					$i = 0;
					foreach($field->val as $iter => $item){
						$value = (isset($data[$name]['value'][0][$iter]['value']))? $data[$name]['value'][0][$iter]['value']: '';
						switch($item->field){
							case 'string':	$temp .='<div class="row-wrap"><input name="string_'.$i.'" type="text" class="text-input col_1" placeholder="'.$item->val.'&hellip;" value="'.$value.'"></div>'; break;
							case 'text':	$temp .='<div class="row-wrap"><textarea name="text_'.$i.'" class="simple-text" placeholder="'.$item->val.'&hellip;">'.$value.'</textarea></div>'; break;
							case 'image':	$temp .= self::buildFileUpload(self::str2url($item->val), $item->val, $value); break;
						}
						$i++;
					}
					$temp .= '</div>';

					if(isset($data[$name]['value'])){
						for($j=1; $j<count($data[$name]['value']); $j++){
							$i = 0;
							$temp .= '<div class="custom-slide-container">
							<div class="row-wrap">
									<label class="fieldset-label-wrap">
									<input type="radio" name="previevBar" checked="checked">
									<span>Превью</span></label>
							</div>';
							foreach($field->val as $iter => $item){
								$value = (isset($data[$name]['value'][$j][$iter]['value']))? $data[$name]['value'][$j][$iter]['value']: '';
								switch($item->field){
									case 'string':	$temp .='<div class="row-wrap"><input name="string_'.$i.'" type="text" class="text-input col_1" placeholder="'.$item->val.'&hellip;" value="'.$value.'"></div>'; break;
									case 'text':	$temp .='<div class="row-wrap"><textarea name="text_'.$i.'" class="simple-text" placeholder="'.$item->val.'&hellip;">'.$value.'</textarea></div>'; break;
									case 'image':	$temp .= self::buildFileUpload(self::str2url($item->val), $item->val, $value); break;
								}
								$i++;
							}
							$temp .= '</div>';
						}
					}

					$result .= sprintf('
					<fieldset data-name="%s" data-type="%s">
						<legend>%s</legend>
						<div class="row-wrap">
							<div class="custom-slider-wrap">
								<div class="slider-controls-bg left">&#9664;</div>
								<div class="custom-slider-content-wrap">%s</div>
								<div class="slider-controls-bg right">&#9658;</div>
							</div>
						</div>
						<div class="slider-manage-buttons">
							<input name="customSliderAddSlide" type="button" class="control-button" value="Добавить слайд&hellip;">
							<input name="customSliderDropCurrentSlide" type="button" class="control-button" value="Удалить текущий слайд&hellip;">
						</div>
					</fieldset>', $name, $field->type, $field->capt, $temp);
				break;
				case 'file_upload':
					$image = (isset($data[$name]['value']))? $data[$name]['value']: '';
					$result .= self::buildFileUpload($name, $field->capt, $image);
				break;

				case 'articles':
				case 'products':
				case 'promo':
					if($field->type == 'articles'){
						$items = Articles::select('id','title','img_url')->where('module_id','=',$field->val)->orderBy('title','asc')->get();
					}
					if($field->type == 'products'){
						$items = Products::select('id','title','img_url')->where('module_id','=',$field->val)->orderBy('title','asc')->get();
					}
					if($field->type == 'promo'){
						$items = Promo::select('id','title','img_url')->where('module_id','=',$field->val)->orderBy('title','asc')->get();
					}

					$value = (isset($data[$name]['value']))? $data[$name]['value']: [];

					$temp = '';
					if($field->mult == 0){
						$temp .= '<select class="select-input">';
						foreach($items as $item){
							$selected = (in_array($item->id, $value))? ' selected="selected"': '';
							$temp .= '<option value="'.$item->id.'"'.$selected.'>'.$item->title.'</option>';
						}
						$temp .= '</select>';
					}else{
						$list = self::getAlphabeticlist($items);
						if(count($items) > 10){
							foreach($list as $element){
								$temp .= '
								<div class="alphabetic-wrap">
									<div class="sign-caption">'.mb_strtoupper($element['sign']).'</div>
									<div class="alphabetic-items-wrap">';
								$temp .= self::buildElementListView($element, $value);
								$temp .='
									</div>
								</div>';
							}
						}else{
							$temp .= '<div class="chbox-selector-wrap">';
							foreach($items as $item){
								$checked = (in_array($item->id, $value))? ' checked="checked"': '';
								$images = unserialize($item->img_url);
								$image = (!empty($images))? $images[0]: '';
								$temp .= '<div class="checkbox-item-wrap">';
								if(!empty($image)){
									$temp .= '<div class="tac"><img src="'.$image['img'].'" alt="'.$image['alt'].'"></div>';
								}
								$temp .= '<label class="fieldset-label-wrap">
									<input name="category" type="checkbox" class="chbox-input" value="'.$item->id.'"'.$checked.'>
									<span>'.$item->title.'</span>
								</label>
							</div>';
							}
							$temp .= '</div>';
						}
					}

					$result .= sprintf('
					<fieldset data-name="%s" data-type="%s">
						<legend>%s</legend>
						<div class="row-wrap">%s</div>
					</fieldset>', $name, $field->type, $field->capt, $temp);
				break;

				case 'category':
					if( (isset($field->pos_sel)) && ($field->pos_sel == 1)){
						$inside_categories = self::getAssocList('categories', $refer_to = 0, $field->val, [['position','asc']]);
						$value = (isset($data[$name]['value']))? $data[$name]['value']: [];
						if(!empty($value)){
							usort($value, function($a, $b){
								return $a->position > $b->position;
							});
							$temp = '<div class="optional-list-wrap"><ul data-refer="0">';
							foreach($value as $upper){
								if($upper->refer_to == 0){
									$element = Categories::select('id','title','slug','created_at','updated_at')->find($upper->id);
									$temp .= sprintf('
									<li data-id="%s">
										<div class="category-wrap">
											<div class="category-title">
												<div class="sort-controls">
													<p data-direction="up">&#9650;</p>
													<p data-direction="down">&#9660;</p></div>
												<div>%s</div>
											</div>
											<div class="category-slug">%s</div>
											<div class="timestamps">
												<p>Создан: %s</p>
												<p>Изменен: %s</p>
											</div>
										</div>',
									$element->id,$element->title,$element->slug,
									self::convertDate($element->created_at),self::convertDate($element->updated_at));

									$temp .= '<ul data-refer="'.$upper->id.'">';
									foreach($value as $inner){
										if($inner->refer_to == $upper->id){
											$element = Categories::select('id','title','slug','created_at','updated_at')->find($inner->id);
											$temp .= sprintf('
											<li data-id="%s">
												<div class="category-wrap">
													<div class="category-title">
														<div class="sort-controls">
															<p data-direction="up">&#9650;</p>
															<p data-direction="down">&#9660;</p></div>
														<div>%s</div>
													</div>
													<div class="category-slug">%s</div>
													<div class="timestamps">
														<p>Создан: %s</p>
														<p>Изменен: %s</p>
													</div>
												</div>
											</li>',
											$element->id, $element->title, $element->slug,
											self::convertDate($element->created_at),self::convertDate($element->updated_at));;
										}
									}
									$temp .= '</ul>
									</li>';
								}
							}
							$temp .= '</ul></div>';
						}else{
							$temp = '<div class="optional-list-wrap">';
							$temp .= self::buildCategoriesView($inside_categories, false);
							$temp .= '</div>';
						}
					}else{
						$inside_categories = self::getAssocList('categories', $refer_to = 0, $field->val, [['refer_to','asc'],['title','desc']]);
						$value = (isset($data[$name]['value']))? $data[$name]['value']: [];
						if($field->mult == 0){
							$temp = '<select class="select-input">'.self::buildCategorieSelector($inside_categories, $value).'</select>';
						}else{
							$temp = '';
							if(!empty($inside_categories[0]['inner'])){
								foreach($inside_categories as $parent){
									$temp .= '<div class="alphabetic-items-wrap"><div class="sign-caption tac">'.$parent['title'].'</div></div>';
									$temp .= '<div class="chbox-selector-wrap">';
									foreach($parent['inner'] as $element){
										$checked = (in_array($element['id'], $value))? ' checked="checked"': '';
										$temp .= '<div class="checkbox-item-wrap">';
										if(!empty($element['img_url'])){
											$temp .='<div class="tac"><img src="'.$element['img_url']['img'].'" alt="'.$element['img_url']['alt'].'"></div>';
										}
										$temp .= '
										<label class="fieldset-label-wrap">
											<input name="category" type="checkbox" class="chbox-input" value="'.$element['id'].'"'.$checked.'>
											<span>'.$element['title'].'</span>
										</label>
									</div>';
									}
									$temp .= '</div>';
								}
							}else{
								$inside_categories = self::assocListToFlat($inside_categories);
								$list = self::getAlphabeticlist($inside_categories);
								foreach($list as $element){
									$temp .= '<div class="alphabetic-wrap">
									<div class="sign-caption">'.mb_strtoupper($element['sign']).'</div>
									<div class="alphabetic-items-wrap">';
									$temp .= self::buildElementListView($element, $value);
									$temp .='
									</div>
								</div>';
								}
							}
						}
					}
					$result .= sprintf('
					<fieldset data-name="%s" data-type="%s">
						<legend>%s</legend>
						<div class="row-wrap">%s</div>
					</fieldset>', $name, $field->type, $field->capt, $temp);

				break;
				default: $result = '';
			}
			$fields_iterators[$field->type]++;
		}

		return $result;
	}

	public static function buildFileUpload($data_name, $legend, $image=''){
		$file = explode('.',$image);
		if(
			($file[count($file)-1] == 'png') ||
			($file[count($file)-1] == 'jpg') ||
			($file[count($file)-1] == 'jpeg')||
			($file[count($file)-1] == 'gif') ||
			($file[count($file)-1] == 'svg') ||
			($file[count($file)-1] == 'bmp')
		){
			$image = (!empty($image))? '<img src="'.$image.'" alt="'.$image.'">': '';
		}

		return sprintf('
		<fieldset data-name="%1$s" data-type="file">
			<legend>%2$s</legend>
			<div class="row-wrap">
				<div class="upload-image-preview">%3$s</div>
				<input class="file-upload-button" name="%1$s" type="file" placeholder="Обзор&hellip;">
			</div>
		</fieldset>', $data_name, $legend, $image);
	}

	public static function buildSlider($data_name, $legend, $images=[]){
		$images = array_values($images);
		$left_part = '';
		$right_part = '';
		for($i=0; $i<count($images); $i++){
			$is_active = ($i == 0)? 'active': '';
			$left_part .= '
			<div class="image-wrap '.$is_active.'" data-position="'.$i.'">
				<img src="'.$images[$i]['img'].'" alt="'.$images[$i]['alt'].'">
				<div class="attributes-wrap">
					<input name="altText" class="text-input" placeholder="Альтернативный текст…" style="width: 90%;" type="text" value="'.$images[$i]['alt'].'">
					<a href="#" class="drop-image button" title="Удалить">
						<img src="/img/drop.png" alt="">
					</a>
				</div>
			</div>';
			$image_src = explode('/', $images[$i]['img']);
			$right_part .= '
			<div class="slider-content-element" data-position="'.$i.'">
				<div class="element-title">'.$image_src[count($image_src)-1].'</div>
				<div class="element-size"></div>
				<div class="element-image">
					<img src="'.$images[$i]['img'].'" alt="'.$images[$i]['alt'].'">
				</div>
				<div class="element-alt">'.$images[$i]['alt'].'</div>
				<div class="element-drop">
					<img src="/img/drop.png" alt="Удалить" title="Удалить">
				</div>
			</div>';
		}
		return sprintf('
		<fieldset data-name="%s" data-type="slider">
			<legend>%s</legend>
			<div class="slider-wrap">
				<div class="slider-content">
					<div class="slider-preview">
						<div class="slider-controls left">&#9664;</div>
						<div class="slider-images-wrap">%s</div>
						<div class="slider-controls right">&#9658;</div>
					</div>
					<div class="slider-manage-buttons">
						<input name="imageFileToUpload" type="file" class="dn" multiple>
						<input class="control-button" name="loadFileToSlider" type="button" value="Загрузить файл&hellip;">
						<input class="control-button" name="getImgToSlider" type="button" value="Выбрать из загруженых&hellip;">
					</div>
				</div>
				<div class="slider-list-wrap">%s</div>
			</div>
		</fieldset>', $data_name, $legend, $left_part, $right_part);
	}

	public static function createImg($img_url, $folder, $use_img_check = true){
		if( ('undefined' != $img_url) && (!empty($img_url)) ){
			$destinationPath = base_path().'/public/images/'.$folder.'/';//Указываем папку хранения картинок
			$img_file = pathinfo(self::str2url($img_url->getClientOriginalName()));//Узнаем реальное имя файла
			$img_file['extension'] = strtolower($img_file['extension']);
			if($use_img_check){
				if(
					($img_file['extension'] != 'png') &&
					($img_file['extension'] != 'jpg') &&
					($img_file['extension'] != 'jpeg') &&
					($img_file['extension'] != 'gif') &&
					($img_file['extension'] != 'svg') &&
					($img_file['extension'] != 'bmp')
				){
					return '';
				}
			}
			$img_file = $img_file['filename'].'_'.substr(uniqid(),6).'.'.$img_file['extension'];//Создаем уникальное имя для файла

			$img_url -> move($destinationPath, $img_file);//Сохнаняем файл на сервере
		}else{
			$img_file = '';
		}
		return $img_file;
	}

	public static function sliderDataFill($data, $field_data, $folder){
		if( (isset($field_data->type)) && ($field_data->type == 'slider') ){
			$result_slider = [];
			foreach($field_data->items as $position => $item){
				if(!empty($item->uploaded)){
					foreach($data as $key => $value){
						if((strpos($key, $field_data->name) === 0) && ($key != $field_data->name) && (!empty($value)) && ($value != 'undefined')){
							if($value->getClientOriginalName() == $item->uploaded){
								$result_slider[$position] = [
									'alt'	=> $item->alt,
									'img'	=> '/images/'.$folder.'/'.self::createImg($value, $folder)
								];
							}
						}
					}
				}elseif(!empty($item->image)){
					$result_slider[$position] = [
						'alt'	=> $item->alt,
						'img'	=> $item->image
					];
				}
			}
			return $result_slider;
		}else{
			return [];
		}
	}

	public static function getSliderData($data, $name, $folder){
		$slider = json_decode($data[$name]);
		return self::sliderDataFill($data, $slider, $folder);
	}

	public static function customFieldDataFill($data, $field_data, $folder){
		$custom_fields = [];
		switch($field_data->type){
			case 'checkbox':
			case 'email':
			case 'fulltext':
			case 'number':
			case 'radio':
			case 'range':
			case 'string':
			case 'textarea':
				$custom_fields[$field_data->name] = [
					'value' => $field_data->items,
					'caption'=>$field_data->capt,
					'type'  => $field_data->type
				];
			break;

			case 'custom_slider':
				$slider_data = [];
				foreach($field_data->items as $iter => $item){
					$slider_data[$iter]['preview'] = $item->preview;
					foreach($item->items as $i => $field){
						if($field->type == 'file'){
							if(isset($data[$field_data->name.'-'.$field->name.'-slide'.$iter])){
								if(is_string($data[$field_data->name.'-'.$field->name.'-slide'.$iter])){
									$file = $data[$field_data->name.'-'.$field->name.'-slide'.$iter];
								}else{
									$file = (isset($data[$field_data->name.'-'.$field->name.'-slide'.$iter]))
										? '/images/'.$folder.'/'.self::createImg($data[$field_data->name.'-'.$field->name.'-slide'.$iter], $folder)
										: '';
								}
							}else{
								$file = '';
							}

							$slider_data[$iter]['items'][$i] = [
								'name' => $field->name,
								'value'=> $file,
								'type' => $field->type
							];
						}else{
							$slider_data[$iter]['items'][$i] = [
								'name' => $field->name,
								'capt' => $field->capt,
								'value'=> $field->value,
								'type' => $field->type
							];
						}
					}
				}
				$unused_keys = [];
				$temp = [];
				$allow_to_use = false;
				foreach($slider_data as $iter => $item){
					if($item['preview'] == 1) $allow_to_use = true;
					if($allow_to_use){
						$temp[] = $item['items'];
					}else{
						$unused_keys[] = $iter;
					}
				}
				foreach($unused_keys as $key) {
					$temp[] = $slider_data[$key]['items'];
				}

				$custom_fields[$field_data->name] = [
					'value'	=> $temp,
					'caption'=>$field_data->capt,
					'type'	=> $field_data->type
				];
			break;

			case 'fieldset':
				$items = [];
				foreach($field_data->items as $iter => $field){
					$items[] = self::customFieldDataFill($data, $field, $folder);
				}
				$custom_fields[$field_data->name] = [
					'value'	=> $items,
					'caption'=>$field_data->capt,
					'type'	=> $field_data->type
				];
			break;

			case 'file':
				if($data[$field_data->name]!= 'undefined'){
					if(is_string($data[$field_data->name])){
						$image = $data[$field_data->name];
					}else{
						$image = self::createImg($data[$field_data->name], $folder, false);
						$image = (!empty($image))? '/images/'.$folder.'/'.$image: '';
					}

					$custom_fields[$field_data->name] = [
						'value'	=> $image,
						'caption'=>$field_data->capt,
						'type'	=> $field_data->type
					];
				}
			break;

			case 'slider':
				$custom_fields[$field_data->name] = [
					'value'	=> self::sliderDataFill($data, $field_data, $folder),
					'caption'=>$field_data->capt,
					'type'	=> $field_data->type
				];
			break;

			case 'table':
				$custom_fields[$field_data->name] = [
					'value' => [
						'head' => $field_data->items->head,
						'body' => $field_data->items->body
					],
					'caption'=>$field_data->capt,
					'type'	=> $field_data->type
				];
			break;

			case 'articles':
			case 'category':
			case 'products':
			case 'promo':
				$custom_fields[$field_data->name] = [
					'value'	=> $field_data->items,
					'caption'=>$field_data->capt,
					'type'	=> $field_data->type
				];
			break;
			//Доделать /акции
		}
		return $custom_fields;
	}
}