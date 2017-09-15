<?php
namespace App\Http\Controllers\Site;

use App\Articles;
use App\Categories;
use App\EnabledModules;
use App\Http\Controllers\Supply\Functions;
use App\Reviews;
use App\User;
use Illuminate\Http\Request;
use App\PageContent;
use App\Products;
use App\Promo;
use App\ContentPages;
use App\Http\Controllers\Supply\Helpers;
use Illuminate\Routing\Controller as BaseController;
use PDO;
use Auth;
use Crypt;
use Symfony\Component\Console\Tests\Helper\HelperSetTest;
use Validator;

class PagesController extends BaseController
{
	public function index(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}

		$defaults = Helpers::getDefaultContent($current_city);

		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','main-page')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		return view('home', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content
		]);
	}

	public function about_us(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','about_us')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/about_us',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		return view('about_us', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content
		]);
	}

	public function contacts(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','contacts')
			->first();

		$page_meta_data = [
			'slug' => '/contacts',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		$content = [];

		$cities_module = EnabledModules::select('id')->where('slug','=','spisok_gorodov')->first();
		$cities = Categories::select('title','custom_fields')
			->where('module_id','=',$cities_module->id)
			->where('refer_to','=',0)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();
		foreach($cities as $city){
			$content[] = [
				'title'=> $city->title,
				'data' => Helpers::convertCustomFields(unserialize($city->custom_fields))
			];
		}
		$other_content = PageContent::select('title','content')->where('type','=','settings')
			->where('title','=','email')->orWhere('title','=','requisites')->get();

		$contact_data = [];
		foreach($other_content as $item){
			$contact_data[$item->title] = (Functions::is_serialized($item->content))? unserialize($item->content): $item->content;
		}
		return view('contacts', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content,
			'contact_data'=> $contact_data
		]);
	}

	public function business_travel(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','business_travel')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/about_us',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		$uniq_categories = [];
		foreach($defaults['vehicle_type'] as $up_category){
			foreach($up_category['items'] as $item){
				$uniq_categories[$item['slug']] = [
					'title' => $item['title'],
					'slug' => $item['slug']
				];
			}
		}
		$uniq_categories = array_values($uniq_categories);
		$marks = (!empty($uniq_categories))? Helpers::markBycategory($uniq_categories[0]['slug']): [];
		$models = (!empty($marks))? Helpers::modelByMark($marks[0]['slug']): [];
		$car = (!empty($models))? Helpers::carByModel($marks[0]['slug'], $models[0]['slug']): [];

		return view('business_travel', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content,
			'uniq_categories' => $uniq_categories,
			'marks' => $marks,
			'models' => $models,
			'car' => $car
		]);
	}

	public function forInvestor(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','for_investor')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/about_us',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		return view('for_investor', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content
		]);
	}

	public function tarifs(){
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);

		$tarif_module = EnabledModules::select('id')->where('slug','=','tarify-sortirovka_kategorij')->first();
		$vehicles_list = [];
		$cars = \DB::table('tbl_products')->select('id','title','slug','price','color','custom_fields')
			->where('enabled','=',1)
			->get();
		foreach($cars as $car){
			$car_data = Helpers::convertCustomFields(unserialize($car->custom_fields));
			$color = unserialize($car->color);
			if(!empty($car_data['category_0'])){
				foreach($car_data['category_0'] as $category){
					$vehicle = \DB::table('tbl_categories')->select('refer_to')->find($category['id']);
					$upper_category = \DB::table('tbl_categories')->select('title','slug')->find($vehicle->refer_to);

					$category_position = \DB::table('tbl_categories')->select('position')
						->where('module_id','=',$tarif_module->id)
						->where('enabled','=',1)
						->where('slug','=',$category['slug'])
						->first();

                                        $tarif_title = \DB::table('tbl_categories')->select('title')
						->where('module_id','=',$tarif_module->id)
						->where('slug','=',$category['slug'])
						->first();

					if(!isset($vehicles_list[$category['slug']])){
						$vehicles_list[$category['slug']] = [
							'title'	=> $tarif_title->title,
							'slug'	=> $category['slug'],
							'position' => $category_position->position,
							'items'	=> []
						];
					}

					if(!isset($vehicles_list[$category['slug']]['items'][$upper_category->slug])){
						$vehicles_list[$category['slug']]['items'][$upper_category->slug] = [
							'title'	=> $upper_category->title,
							'slug'	=> $upper_category->slug,
							'cars'	=> []
						];
					}

					$promos = Promo::select('discount','custom_fields')
						->where('custom_fields','LIKE','%'.$car->id.'%')
						->whereDate('date_start','<=',date('Y-m-d H:i:s'))
						->whereDate('date_finish','>=',date('Y-m-d H:i:s'))
						->get();
					$discount = $car_data['number_0']['value'];
					foreach($promos as $promo){
						$promo_data = Helpers::convertCustomFields(unserialize($promo->custom_fields));
						if(in_array($car->id, $promo_data['products_0']['value'])){
							$discount = unserialize($promo->discount);
							$discount = $discount->value;
						}
					}

					$items = $vehicles_list[$category['slug']]['items'][$upper_category->slug]['cars'];
					$tarifs = [];
					foreach($car_data['category_2'] as $tarif){
						$tarifs[] = [
							'title' => $tarif['title'],
							'value' => (!empty($tarif['data']['string_0']['value']))? $tarif['data']['string_0']['value']: $tarif['data']['string_1']['value']
						];
					}
					$items[] = [
						'title'	=> $car->title,
						'slug'	=> $car->slug,
						'price'	=> $car->price,
						'prices'=> $car_data['fieldset_0'],
						'color'	=> (!empty($color))? $color[0]->title: '',
						'tarifs'=> $tarifs,
						'year'	=> $car_data['fieldset_1']['string_1']['value'],
						'discount' => (!empty($discount))? $discount: 0
					];
					$vehicles_list[$category['slug']]['items'][$upper_category->slug]['cars'] = $items;
				}
			}
		}
		usort($vehicles_list, function($a, $b){
			return $a['position'] > $b['position'];
		});

		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','tarifs')
			->first();
		$page_meta_data = [
			'slug' => '/tarifs',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		return view('tarifs', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $vehicles_list
		]);
	}

	public function creditForRent(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','credit_for_rent')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/about_us',
			'img_url' => unserialize($page_content_model->img_url),
			'text' => $page_content_model->text,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		return view('credit_for_rent', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content
		]);
	}

	public function cooperation(Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$slug = $request->path();
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=',$slug)
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/'.$slug,
			'img_url' => unserialize($page_content_model->img_url),
			'text' => $page_content_model->text,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		$marks_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();
		$car_mark = Categories::select('title','slug')
			->where('module_id','=',$marks_module->id)
			->where('enabled','=',1)
			->where('refer_to','=',0)
			->orderBy('position','asc')
			->get();
		return view('cooperation', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content,
			'car_marks'	=> $car_mark
		]);
	}

	public function deliveryCarToTaxi(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','delivery_car_to_taxi')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/delivery_car_to_taxi',
			'img_url' => unserialize($page_content_model->img_url),
			'text' => $page_content_model->text,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		return view('delivery_car_to_taxi', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content,
		]);
	}

	public function eventOrganization(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','event_organization')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);
		$allow_page = false;
		foreach($content['category_0'] as $city){
			if($city['slug'] == $_COOKIE['current_city']){
				$allow_page = true;
			}
		}

		if($allow_page){
			$page_meta_data = [
				'slug' => '/event_organization',
				'img_url' => unserialize($page_content_model->img_url),
				'text' => $page_content_model->text,
				'title' => $page_content_model->title,
				'meta_title' => $page_content_model->meta_title,
				'meta_keywords' => $page_content_model->meta_keywords,
				'meta_description' => $page_content_model->meta_description
			];
			$event_module = EnabledModules::select('id')->where('slug','=','organizatsiya_meropriyatij')->first();
			$events = Categories::select('title','text','custom_fields')
				->where('module_id','=',$event_module->id)
				->orderBy('position','asc')
				->get();
			$event_list = [];
			foreach($events as $event){
				$event_list[] = [
					'title' => $event->title,
					'text'  => $event->text,
					'data'  => Helpers::convertCustomFields(unserialize($event->custom_fields))
				];
			}

			return view('event_organization', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
				'events'	=> $event_list
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function operativeLeasing(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','operative_leasing')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/operative_leasing',
			'img_url' => unserialize($page_content_model->img_url),
			'text' => $page_content_model->text,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		$marks_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();
		$car_mark = Categories::select('title','slug')
			->where('module_id','=',$marks_module->id)
			->where('enabled','=',1)
			->where('refer_to','=',0)
			->orderBy('position','asc')
			->get();

		return view('operative_leasing', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content,
			'car_marks' => $car_mark
		]);
	}

	public function ourFranchise(Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$slug = $request->path();
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=',$slug)
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/'.$slug,
			'img_url' => unserialize($page_content_model->img_url),
			'text' => $page_content_model->text,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		return view('our_franchise', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content
		]);
	}

	public function rentTerms(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','rent_terms')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$page_meta_data = [
			'slug' => '/rent_terms',
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		return view('rent_terms', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $content
		]);
	}

	public function hirePurchase(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','hire_purchase')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);
		$allow_page = false;
		foreach($content['category_0'] as $city){
			if($city['slug'] == $_COOKIE['current_city']){
				$allow_page = true;
			}
		}

		if($allow_page){
			$page_meta_data = [
				'slug' => '/hire_purchase',
				'img_url' => unserialize($page_content_model->img_url),
				'text' => $page_content_model->text,
				'title' => $page_content_model->title,
				'meta_title' => $page_content_model->meta_title,
				'meta_keywords' => $page_content_model->meta_keywords,
				'meta_description' => $page_content_model->meta_description
			];
			$marks_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();
			$car_mark = Categories::select('title','slug')
				->where('module_id','=',$marks_module->id)
				->where('enabled','=',1)
				->where('refer_to','=',0)
				->orderBy('position','asc')
				->get();
			return view('hire_purchase', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
				'car_marks' => $car_mark
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function reviews(Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','text','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','reviews')
			->first();
		$page_meta_data = [
			'slug' => '/reviews',
			'img_url' => unserialize($page_content_model->img_url),
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];
		$comments = Reviews::select('user_id','name','text','custom_fields','rating','created_at')
			->orderBy('created_at','desc')
			->paginate(10);
		$comments_list = [];
		$vehicle_module = EnabledModules::select('id','slug')->where('slug','tipy_transporta')->first();
		$mark_module = EnabledModules::select('id')->where('slug','=','mark_and_model')->first();
		foreach($comments as $comment){
			if($comment->user_id != 0){
				$user_data = User::select('login','name')->find($comment->user_id);
				$name = (!empty($user_data->name))? $user_data->name: $user_data->login;
			}else{
				$name = $comment->name;
			}
			$custom = unserialize($comment->custom_fields);

			$mark = Categories::select('title')
				->where('module_id','=',$mark_module->id)
				->where('slug','=',$custom['mark'])
				->first();
			$model = Categories::select('title')
				->where('module_id','=',$mark_module->id)
				->where('slug','=',$custom['model'])
				->first();
			$has_driver = Categories::select('title')
				->where('module_id','=',$vehicle_module->id)
				->where('slug','=',$custom['has_driver'])
				->first();
			$car = (!empty($mark->title))? $mark->title: '';
			$car .= ''.(!empty($model->title))? $model->title: '';
			$comments_list[] = [
				'user_name'	=> $name,
				'text'		=> $comment->text,
				'rating'	=> $comment->rating,
				'date'		=> date('d/m/Y', strtotime($comment->created_at)),
				'city'		=> $custom['loc'],
				'car'		=> trim($car),
				'has_driver'=> mb_strtolower($has_driver->title)
			];
		}
		return view('reviews', [
			'defaults'		=> $defaults,
			'meta'			=> $page_meta_data,
			'comments_list'	=> $comments_list,
			'paginate'		=> ceil($comments->total()/ 10),
			'current_page'	=> $comments->currentPage(),
		]);
	}
	
	public function news(Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		$data = $request->all();
		$paginate = ( (isset($data['page'])) && ($data['page'] > 1) )? 8: 9;
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$slug = $request->path();

		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=',$slug)
			->first();
		$page_meta_data = [
			'slug' => '/'.$slug,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		$news_module = EnabledModules::select('id','slug')->where('slug','=',$slug)->first();
		$news = Articles::select('title','slug','img_url','description','published_at')
			->where('module_id','=',$news_module->id)
			->where('enabled','=',1)
			->orderBy('published_at','desc')
			->paginate($paginate);

		if(!empty($news->all())){
			$last_one = Articles::select('title','slug','img_url','description','published_at')
				->where('module_id','=',$news_module->id)
				->where('enabled','=',1)
				->orderBy('published_at','desc')
				->first();
			$date = '<b>'.date('j', strtotime($last_one->published_at)).'</b> '.Helpers::monthDecToWord(date('n', strtotime($last_one->published_at)));
			if(date('Y', strtotime($last_one->published_at)) < date('Y')){
				$date .= date('Y', strtotime($last_one->published_at));
			}
			$first = [
				'title'		=> $last_one->title,
				'slug'		=> $last_one->slug,
				'description'=>$last_one->description,
				'img_url'	=> unserialize($last_one->img_url),
				'date'		=> $date
			];
		}else{
			$first = [];
		}

		$news_list = [];
		for($i=((isset($data['page'])) && ($data['page'] > 1))? 0:1; $i<count($news); $i++){
			$date = '<b>'.date('j', strtotime($news[$i]->published_at)).'</b> '.Helpers::monthDecToWord(date('n', strtotime($news[$i]->published_at)));
			if(date('Y', strtotime($news[$i]->published_at)) < date('Y')){
				$date .= date('Y', strtotime($news[$i]->published_at));
			}
			$news_list[] = [
				'title'		=> $news[$i]->title,
				'slug'		=> $news[$i]->slug,
				'description'=>$news[$i]->description,
				'img_url'	=> unserialize($news[$i]->img_url),
				'date'		=> $date,
			];
		}

		return view('articles', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $news_list,
			'first'		=> $first,
			'link_to'	=> '/'.$slug,
			'paginate'	=> $news->lastPage(),
			'current_page' => $news->currentPage()
		]);
	}

	public function promo(Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		$data = $request->all();
		$paginate = ( (isset($data['page'])) && ($data['page'] > 1) )? 8: 9;
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$slug = $request->path();

		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=',$slug)
			->first();
		$page_meta_data = [
			'slug' => '/'.$slug,
			'title' => $page_content_model->title,
			'meta_title' => $page_content_model->meta_title,
			'meta_keywords' => $page_content_model->meta_keywords,
			'meta_description' => $page_content_model->meta_description
		];

		$promo_module = EnabledModules::select('id','slug')->where('slug','=',$slug)->first();
		$promos = Promo::select('title','slug','img_url','description','published_at')
			->where('module_id','=',$promo_module->id)
			->where('enabled','=',1)
			->orderBy('published_at','desc')
			->paginate($paginate);

		if(!empty($promos->all())){
			$last_one = Promo::select('title','slug','img_url','description','published_at')
				->where('module_id','=',$promo_module->id)
				->where('enabled','=',1)
				->orderBy('published_at','desc')
				->first();
			$date = '<b>'.date('j', strtotime($last_one->published_at)).'</b> '.Helpers::monthDecToWord(date('n', strtotime($last_one->published_at)));
			if(date('Y', strtotime($last_one->published_at)) < date('Y')){
				$date .= date('Y', strtotime($last_one->published_at));
			}
			$first = [
				'title'		=> $last_one->title,
				'slug'		=> $last_one->slug,
				'description'=>$last_one->description,
				'img_url'	=> unserialize($last_one->img_url),
				'date'		=> $date
			];
		}else{
			$first = [];
		}

		$promos_list = [];
		for($i=((isset($data['page'])) && ($data['page'] > 1))? 0:1; $i<count($promos); $i++){
			$date = '<b>'.date('j', strtotime($promos[$i]->published_at)).'</b> '.Helpers::monthDecToWord(date('n', strtotime($promos[$i]->published_at)));
			if(date('Y', strtotime($promos[$i]->published_at)) < date('Y')){
				$date .= date('Y', strtotime($promos[$i]->published_at));
			}
			$promos_list[] = [
				'title'		=> $promos[$i]->title,
				'slug'		=> $promos[$i]->slug,
				'description'=>$promos[$i]->description,
				'img_url'	=> unserialize($promos[$i]->img_url),
				'date'		=> $date,
			];
		}

		return view('articles', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
			'content'	=> $promos_list,
			'first'		=> $first,
			'link_to'	=> '/'.$slug,
			'paginate'	=> $promos->lastPage(),
			'current_page' => $promos->currentPage()
		]);
	}

	public function viewNews($slug, Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}

		$defaults = Helpers::getDefaultContent($current_city);
		$url = explode('/', $request->path());
		$module = EnabledModules::select('id')->where('slug','=',$url[0])->first();

		$last_page = ContentPages::select('title','slug')->where('slug','=',$url[0])->first();

		$article = Articles::where('module_id','=',$module->id)->where('slug','=',$slug)->first();
		if(empty($article)){
			$article = Promo::where('module_id','=',$module->id)->where('slug','=',$slug)->first();
		}

		if(!empty($article)){
			$custom_fields = Helpers::convertCustomFields(unserialize($article->custom_fields));
			$cars = [];
			if(isset($custom_fields['products_0'])){
				foreach($custom_fields['products_0']['value'] as $car_id){
					$cars[] = Helpers::carById($car_id);
				}
			}

			$img_url = unserialize($article->img_url);
			$images = [];
			if(count($img_url) > 1){
				for($i=1; $i<count($img_url); $i++){
					$images[] = $img_url[$i];
				}
			}
			$big_img = (!empty($img_url))? $img_url[0]: [];

			$content = [
				'big_img'	=> $big_img,
				'images'	=> $images,
				'text'		=> $article->text,
				'caption'	=> $article->text_caption,
				'cars'		=> $cars
			];
			$page_meta_data = [
				'last_page' => [
					'slug' => $last_page->slug,
					'title' => $last_page->title
				],
				'slug' => '/'.$slug,
				'title' => $article->title,
				'meta_title' => $article->meta_title,
				'meta_keywords' => $article->meta_keywords,
				'meta_description' => $article->meta_description
			];

			return view('view_article', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function romanticDate(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','romantic_date')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$allow_page = false;
		foreach($content['category_0'] as $city){
			if($city['slug'] == $_COOKIE['current_city']){
				$allow_page = true;
			}
		}
		if($allow_page){
			$page_meta_data = [
				'slug' => '/romantic_date',
				'title' => $page_content_model->title,
				'images' => unserialize($page_content_model->img_url),
				'meta_title' => $page_content_model->meta_title,
				'meta_keywords' => $page_content_model->meta_keywords,
				'meta_description' => $page_content_model->meta_description
			];
			$excursion_types_module = EnabledModules::select('id','slug')->where('slug','=','vidy_romanticheskih_vstrech')->first();
			$excursion_types = Categories::select('title','slug')
				->where('module_id','=',$excursion_types_module->id)
				->orderBy('position','asc')
				->get();
			$excursions_module = EnabledModules::select('id','slug')->where('slug','=','romanticheskie_vstrechi')->first();
			$excursions = Articles::select('title','slug','img_url','description','custom_fields')
				->where('module_id','=',$excursions_module->id)
				->where('enabled','=',1)
				->get();
			$excursion_arr = [];
			foreach($excursions as $excursion){
				$images = unserialize($excursion->img_url);
				$custom_fields = unserialize($excursion->custom_fields);
				$custom = Helpers::convertCustomFields($custom_fields);
				$allow_excursion = false;
				foreach($custom['category_2'] as $city){
					if($city['slug'] == $_COOKIE['current_city']){
						$allow_excursion = true;
					}
				}
				if($allow_excursion){
					$image = (!empty($images))? $images[0]['img']: '';
					$excursion_arr[] = [
						'title' => $excursion->title,
						'slug' => $excursion->slug,
						'img_url' => $image,
						'description' => $excursion->description,
						'data'  => $custom
					];
				}
			}
			return view('excursion', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
				'excursion_types' => $excursion_types,
				'excursions'=> $excursion_arr,
				'build_exursion' => false,
				'parent_slug' => 'romantic_date'
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function romanticDateView($slug){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);

		$article = Articles::where('slug','=',$slug)->first();
		if($article != false){
			if(isset($_COOKIE['dates'])) {
				$visited = $_COOKIE['dates'];
				$visited .= ',' . $article->id;
				$visited = explode(',', $visited);
				$visited = array_values(array_unique($visited));
				$visited = implode(',', $visited);
			}else{
				$visited = $article->id;
			}
			setcookie('dates', $visited, time() + 3600, '/');
			$visited_excursions = Helpers::createExcursionViewData($visited, $article->id);

			$recomended = [];
			$excursions_module = EnabledModules::select('id','slug')->where('slug','=','romanticheskie_vstrechi')->first();
			$recomended_excursions = Articles::select('id','custom_fields')->where('module_id','=',$excursions_module->id)
				->where('enabled','=',1)
				->get();
			foreach($recomended_excursions as $recomended_excursion){
				$custom = Helpers::convertCustomFields(unserialize($recomended_excursion->custom_fields));
				if($custom['fieldset_0']['checkbox_0']['value'] == 1){
					$recomended[] = $recomended_excursion->id;
				}
			}
			$recomended_excursions = Helpers::createExcursionViewData($recomended, $article->id);

			$last_page = ContentPages::select('title','slug')->where('slug','=','romantic_date')->first();
			$page_meta_data = [
				'last_page' => [
					'slug' => '/romantic_date',
					'title' => $last_page->title
				],
				'slug' => $slug,
				'title' => $article->title,
				'meta_title' => $article->meta_title,
				'meta_keywords' => $article->meta_keywords,
				'meta_description' => $article->meta_description
			];
			$content = [
				'img_url'		=> unserialize($article->img_url),
				'description'	=> $article->description,
				'text_caption'	=> $article->text_caption,
				'text'			=> $article->text,
				'data'			=> Helpers::convertCustomFields(unserialize($article->custom_fields))
			];

			return view('romantic_date_view', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
				'recomended'=> $recomended_excursions,
				'visited'   => $visited_excursions
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function excursion(){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_content_model = ContentPages::select('title','slug','img_url','custom_fields','meta_title','meta_keywords','meta_description')
			->where('slug','=','excursion')
			->first();
		$custom_fields = unserialize($page_content_model->custom_fields);
		$content = Helpers::convertCustomFields($custom_fields);

		$allow_page = false;
		foreach($content['category_0'] as $city){
			if($city['slug'] == $_COOKIE['current_city']){
				$allow_page = true;
			}
		}
		if($allow_page){
			$page_meta_data = [
				'slug' => '/romantic_date',
				'title' => $page_content_model->title,
				'images' => unserialize($page_content_model->img_url),
				'meta_title' => $page_content_model->meta_title,
				'meta_keywords' => $page_content_model->meta_keywords,
				'meta_description' => $page_content_model->meta_description
			];

			$excursion_types_module = EnabledModules::select('id','slug')->where('slug','=','vidy_ekskursij')->first();
			$excursion_types = Categories::select('title','slug')
				->where('module_id','=',$excursion_types_module->id)
				->orderBy('position','asc')
				->get();
			$excursions_module = EnabledModules::select('id','slug')->where('slug','=','ekskursii')->first();
			$excursions = Articles::select('title','slug','img_url','description','custom_fields')
				->where('module_id','=',$excursions_module->id)
				->where('enabled','=',1)
				->get();

			$excursion_arr = [];
			foreach($excursions as $excursion){
				$images = unserialize($excursion->img_url);
				$custom_fields = unserialize($excursion->custom_fields);
				$custom = Helpers::convertCustomFields($custom_fields);
				$allow_excursion = false;
				foreach($custom['category_2'] as $city){
					if($city['slug'] == $_COOKIE['current_city']){
						$allow_excursion = true;
					}
				}
				if($allow_excursion){
					$image = (!empty($images))? $images[0]['img']: '';
					$excursion_arr[] = [
						'title' => $excursion->title,
						'slug' => $excursion->slug,
						'img_url' => $image,
						'description' => $excursion->description,
						'data'  => $custom
					];
				}
			}
			return view('excursion', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
				'excursion_types' => $excursion_types,
				'excursions'=> $excursion_arr,
				'build_exursion' => true,
				'parent_slug' => 'excursion'
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function excursionView($slug){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);

		$article = Articles::where('slug','=',$slug)->first();
		if($article != false){
			if(isset($_COOKIE['excursions'])){
				$visited = $_COOKIE['excursions'];
				$visited .= ','.$article->id;
				$visited = explode(',', $visited);
				$visited = array_values(array_unique($visited));
				$visited = implode(',',$visited);
			}else{
				$visited = $article->id;
			}
			setcookie('excursions',$visited, time()+3600, '/');

			$visited_excursions = Helpers::createExcursionViewData($visited, $article->id);

			$recomended = [];
			$excursions_module = EnabledModules::select('id','slug')->where('slug','=','ekskursii')->first();
			$recomended_excursions = Articles::select('id','custom_fields')->where('module_id','=',$excursions_module->id)
				->where('enabled','=',1)
				->get();
			foreach($recomended_excursions as $recomended_excursion){
				$custom = Helpers::convertCustomFields(unserialize($recomended_excursion->custom_fields));
				if($custom['fieldset_0']['checkbox_0']['value'] == 1){
					$recomended[] = $recomended_excursion->id;
				}
			}
			$recomended_excursions = Helpers::createExcursionViewData($recomended, $article->id);

			$last_page = ContentPages::select('title','slug')->where('slug','=','excursion')->first();
			$page_meta_data = [
				'last_page' => [
					'slug' => '/excursion',
					'title' => $last_page->title
				],
				'slug' => $slug,
				'title' => $article->title,
				'meta_title' => $article->meta_title,
				'meta_keywords' => $article->meta_keywords,
				'meta_description' => $article->meta_description
			];
			$content = [
				'img_url'		=> unserialize($article->img_url),
				'description'	=> $article->description,
				'text_caption'	=> $article->text_caption,
				'text'			=> $article->text,
				'data'			=> Helpers::convertCustomFields(unserialize($article->custom_fields))
			];

			return view('excursion_view', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content,
				'recomended'=> $recomended_excursions,
				'visited'   => $visited_excursions
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function excursionOrder($slug){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);

		$article = Articles::where('slug','=',$slug)->first();
		if($article != false){
			$last_page = ContentPages::select('title','slug')->where('slug','=','excursion')->first();
			$page_meta_data = [
				'last_page' => [
					'slug' => '/excursion',
					'title' => $last_page->title
				],
				'slug' => $slug,
				'title' => $article->title,
				'meta_title' => $article->meta_title,
				'meta_keywords' => $article->meta_keywords,
				'meta_description' => $article->meta_description
			];
			$content = [
				'img_url'		=> unserialize($article->img_url),
				'description'	=> $article->description,
				'text_caption'	=> $article->text_caption,
				'text'			=> $article->text,
				'data'			=> Helpers::convertCustomFields(unserialize($article->custom_fields))
			];

			return view('excursion_order', [
				'defaults'	=> $defaults,
				'meta'		=> $page_meta_data,
				'content'	=> $content
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function simplePages($slug, Request $request){
		unset($_COOKIE['prev_page']);
		setcookie('prev_page', null, -1, '/');
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);

		$templates_simple = EnabledModules::select('id')->where('slug','=','shablon_vid_transporta')->first();
		$templates_table = EnabledModules::select('id')->where('slug','=','shablon_stranitsy_s_tablitsej')->first();
		$page_content_model = ContentPages::select('title','slug','img_url','custom_fields','meta_title','meta_keywords','meta_description','module_id')
			->where('slug','=',$slug)
			->first();

		if( ($page_content_model->module_id != $templates_simple->id) && ($page_content_model->module_id != $templates_table->id) ){
			$page_content_model = [];
		}
		if(!empty($page_content_model)){
			$url = explode('/', $request->path());
			$event_module = EnabledModules::select('id')->where('slug','sobytiya')->first();
			$promo_module = EnabledModules::select('id')->where('slug','promo')->first();
			$vehicle_module = EnabledModules::select('id','slug')->where('slug','tipy_transporta')->first();
			$max = Products::select('price')->orderBy('price','desc')->first();
			$min = Products::select('price')->orderBy('price','asc')->first();
			$events = Categories::select('title','slug')->where('module_id','=',$event_module->id)->orderBy('position','asc')->get();
			$comments = Reviews::select('user_id','name','text','rating','custom_fields')->orderBy('created_at','desc')->get();
			$comments_list = [];
			foreach($comments as $comment){
				$data = unserialize($comment->custom_fields);
				if($comment->user_id != 0){
					$user = User::select('login','name')->find($comment->user_id);
					$user->name;
					$name = (!empty($user->name))? $user->name: $user->login;
					$img_url = $user->img_url;
				}else{
					$name = $comment->name;
					$img_url = '';
				}
				$comments_list[] = [
					'name'	=> $name,
					'img_url'=>$img_url,
					'text'	=> $comment->text,
					'city'	=> $data['loc']
				];
			}

			$colors_list = [];
			$colors_by_cars = Products::select('color')->where('color','!=','a:0:{}')->where('enabled','=',1)->get();
			foreach($colors_by_cars as $car_color){
				$color = unserialize($car_color->color);
				foreach($color as $item){
					if(!empty($item->title)) {
						$colors_list[Functions::str2url($item->title)] = [
							'color' => $item->color,
							'title' => $item->title
						];
					}
				}
			}

			$vehicle_types = [];
			foreach($defaults['vehicle_type'] as $item){
				$vehicle_types[] = $item['slug'];
			}

			if(in_array($slug, $vehicle_types)){
				$driver_type = $slug;
			}else{
				$driver_type = (isset($url[2]))? $url[2]: $vehicle_types[0];
			}
			$driver_type_id = Categories::select('id')
				->where('module_id','=',$vehicle_module->id)
				->where('enabled','=',1)
				->where('slug','=',$driver_type)
				->first();

			$custom_fields = unserialize($page_content_model->custom_fields);
			$content = Helpers::convertCustomFields($custom_fields);
			$category_slug = ((isset($content['category_1'])) && (!empty($content['category_1'])))
				? $content['category_1'][0]['slug']
				: $vehicle_types[0];
			$vehicle_category = Categories::select('id','refer_to')
				->where('module_id','=',$vehicle_module->id)
				->where('enabled','=',1)
				->where('slug','=',$category_slug);

			if(!in_array($category_slug, $vehicle_types)){
				$vehicle_category = $vehicle_category->where('refer_to','=',$driver_type_id->id);
			}
			$vehicle_category = $vehicle_category->first();

			$vehicle_ids = [];
			if($vehicle_category->refer_to == 0){
				$temp = Categories::select('id')
					->where('refer_to','=',$vehicle_category->id)
					->orderBy('position','asc')
					->get();
				foreach($temp as $item){
					$vehicle_ids[] = $item->id;
				}
			}else{
				$vehicle_ids = [$vehicle_category->id];
			}

			$car_array = [];

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
										if(count($car_array) < 6){
											$car_data = Helpers::convertCustomFields(unserialize($car->custom_fields));
											$add_by_city = false;
											if( (isset($car_data['category_4'])) && (!empty($car_data['category_4'])) ){
												foreach($car_data['category_4'] as $city){
													if($defaults['current_city']['title'] == $city['title']){
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
											if( ($allow_to_add) && ($add_by_city) ){
												$discount = [];
												$car_in_promo = Promo::select('discount','custom_fields')
													->where('module_id','=',$promo_module->id)
													->where('custom_fields','LIKE','%'.$car->id.'%')
													->whereDate('date_start','<=',date('Y-m-d H:i:s'))
													->whereDate('date_finish','>=',date('Y-m-d H:i:s'))
													->get();
												foreach($car_in_promo as $iter => $item){
													$promo_cars = Helpers::convertCustomFields(unserialize($item->custom_fields));
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
													'price'		=> $car->price,
													'color'		=> $color,
													'data'		=> $car_data,
													'upper_cat'	=> $driver_type,
													'promo'		=> $discount
												];
											}
										}else{
											break 4;
										}
									}
								}
							}
						}
					}
				}
			}
			setcookie('prev_page', serialize([implode('/',$url), $page_content_model->title]), time()+36000, '/');

			$page_meta_data = [
				'slug' => implode('/',$url),
				'title' => $page_content_model->title,
				'images' => unserialize($page_content_model->img_url),
				'meta_title' => $page_content_model->meta_title,
				'meta_keywords' => $page_content_model->meta_keywords,
				'meta_description' => $page_content_model->meta_description
			];
			return view('content_page', [
				'defaults'		=> $defaults,
				'meta'			=> $page_meta_data,
				'content'		=> $content,
				'event_list'	=> $events,
				'price_limits'	=> [
					'min'		=> $min->price,
					'max'		=> $max->price
				],
				'colors_list'	=> $colors_list,
				'comments_list'	=> $comments_list,
				'cars'			=> $car_array,
				'type'			=> $category_slug
			]);
		}else{
			return redirect(route('page404'));
		}
	}

	public function viewCar($category, $slug, Request $request){
		$temp = explode('-',$slug);
		if(count($temp > 1)){
			$need_color = $temp[count($temp)-1];
			unset($temp[count($temp)-1]);
			$slug = implode('-', $temp);
		}else{
			$need_color = [];
		}

		$vehicle_module = EnabledModules::select('id')->where('slug','=','tipy_transporta')->first();
		$upper_category = Categories::select('id')
			->where('module_id','=',$vehicle_module->id)
			->where('slug','=',$category)
			->first();
		if($upper_category != false){
			if(!isset($_COOKIE['current_city'])){
				setcookie('current_city','sankt-peterburg', time()+36000, '/');
				$current_city = 'sankt-peterburg';
			}else{
				$current_city = $_COOKIE['current_city'];
			}
			$defaults = Helpers::getDefaultContent($current_city);
			$cars = Products::where('slug','=',$slug)->where('enabled','=',1)->get();
			if(count($cars) > 0){
				$cars_array = [];
				foreach($cars as $car){
					$content = Helpers::convertCustomFields(unserialize($car->custom_fields));
					foreach($content['category_0'] as $vehicle_type){
						$current_vehicle_type = Categories::select('refer_to')->find($vehicle_type['id']);
						if($current_vehicle_type->refer_to == $upper_category->id){
							$cars_array[] = $car->id;
						}
					}
					$current_color = unserialize($car->color);
					if(!empty($need_color)){
						if(Functions::str2url($current_color[0]->title) == $need_color){
							$car_data = $car;
						}
					}
				}
				$colors = [];
				foreach($cars_array as $car_id){
					$car_color = Products::select('color')->find($car_id);
					$color = unserialize($car_color->color);
					if(!empty($color)) {
						$colors[] = [
							'id'	=> $car_id,
							'color'	=> $color[0]->color,
							'title'	=> $color[0]->title
						];
					}
				}

				if(empty($need_color)){
					$car_data = $cars[0];
				}
				if(isset($_COOKIE['cars'])) {
					$visited = $_COOKIE['cars'];
					$visited .= ',' . $car_data->id;
					$visited = explode(',', $visited);
					$visited = array_values(array_unique($visited));
					$visited = implode(',', $visited);
				}else{
					$visited = $car_data->id;
				}
				setcookie('cars', $visited, time() + 3600, '/');

				$data = Helpers::convertCustomFields(unserialize($car_data->custom_fields));
				usort($data['category_2'], function($a, $b){
					return $a['position']>$b['position'];
				});

				$content = [
					'id'		=> $car_data->id,
					'title'		=> $car_data->title,
					'img_url'	=> unserialize($car_data->img_url),
					'description' => $car_data->description,
					'text'		=> $car_data->text,
					'price'		=> $car_data->price,
					'colors'	=> $colors,
					'data'		=> $data
				];
				$page_meta_data = [
					'slug' => 'car/'.$category.'/'.$slug,
					'title' => $car_data->title,
					'meta_title' => $car_data->meta_title,
					'meta_keywords' => $car_data->meta_keywords,
					'meta_description' => $car_data->meta_description
				];
				$recomended = [];
				$visited_cars = [];
				$visited= explode(',',$visited);
				$cars = Products::select('id','slug','custom_fields')->get();
				$car_vehicle_categories_list = [];
				foreach($data['category_0'] as $vehicle_category){
					$car_vehicle_categories_list[] = $vehicle_category['id'];
				}
				foreach($cars as $car){
					$content_data = Helpers::convertCustomFields(unserialize($car->custom_fields));
					$current_vehicle_categories_list = [];

					foreach($content_data['category_0'] as $vehicle_category){
						$current_vehicle_categories_list[] = $vehicle_category['id'];
					}
					$vehicle_intersect = array_intersect($car_vehicle_categories_list, $current_vehicle_categories_list);

					if(isset($content_data['fieldset_2'])){
						if( ($content_data['fieldset_2']['checkbox_0']['value'] == 1) && ($car->slug != $slug) && (!empty($vehicle_intersect))){
							$recomended[$car->slug] = Helpers::carById($car->id);
						}
					}
					if( (in_array($car->id, $visited)) && ($car->slug != $slug) ){
						$visited_cars[$car->slug] = Helpers::carById($car->id);
					}
				}
				$recomended = array_values($recomended);
				$visited_cars = array_values($visited_cars);

				return view('car_view', [
					'defaults'	=> $defaults,
					'meta'		=> $page_meta_data,
					'content'	=> $content,
					'recomended'=> $recomended,
					'visited'	=> $visited_cars
				]);
			}else{
				return redirect(route('page404'));
			}
		}else{
			return redirect(route('page404'));
		}
	}

	public function orderCar($category, $id){
		$vehicle_module = EnabledModules::select('id')->where('slug','=','tipy_transporta')->first();
		$upper_category = Categories::select('id')
			->where('module_id','=',$vehicle_module->id)
			->where('slug','=',$category)
			->first();
		if($upper_category != false) {
			if (!isset($_COOKIE['current_city'])) {
				setcookie('current_city', 'sankt-peterburg', time() + 36000, '/');
				$current_city = 'sankt-peterburg';
			} else {
				$current_city = $_COOKIE['current_city'];
			}
			$defaults = Helpers::getDefaultContent($current_city);

			$car_data = Products::find($id);
			$custom_data = Helpers::convertCustomFields(unserialize($car_data->custom_fields));

			$images = unserialize($car_data->img_url);
			$color = unserialize($car_data->color);
			usort($custom_data['category_2'], function($a, $b){
				return $a['position'] > $b['position'];
			});

			$content = [
				'title'		=> $car_data->title,
				'tarifs'	=> $custom_data['category_2'],
				'options'	=> $custom_data['category_3'],
				'images'	=> (!empty($images))? $images[0]: [],
				'color'		=> (!empty($color))? $color[0]: [],
				'price'		=> $car_data->price,
				'prices'	=> $custom_data['fieldset_0'],
				'min_time'	=> $custom_data['string_1']['value'],
				'seats'		=> $custom_data['fieldset_1']['string_0']['value'],
				'year'		=> $custom_data['fieldset_1']['string_1']['value'],
				'transmission'	=> $custom_data['fieldset_1']['category_0'][0]['title'],
				'fuel_system'	=> $custom_data['fieldset_1']['category_1'][0]['title'],
				'fuel_consume'	=> $custom_data['fieldset_1']['number_0']['value'],
				'engine_power'	=> $custom_data['fieldset_1']['number_1']['value'],
			];

			$page_meta_data = [
				'slug' => 'car/order/'.$category.'/'.$id,
				'title' => $car_data->title,
				'meta_title' => $car_data->meta_title,
				'meta_keywords' => $car_data->meta_keywords,
				'meta_description' => $car_data->meta_description
			];

			if($category == 'car_with_driver'){
				return view('car_driver_order', [
					'defaults'	=> $defaults,
					'meta'		=> $page_meta_data,
					'content'	=> $content,
				]);
			}else{
				$settings = [
					'responsibility' => [],
					'damage_coverage' => [],
					'ride_out' => []
				];
				$responsibility_module = EnabledModules::select('id')->where('slug','=','spisok_ogranichte_vashu_otvetstvennost')->first();
				$responsibility_list = Categories::select('title','slug','custom_fields')
					->where('module_id','=',$responsibility_module->id)
					->where('enabled','=',1)
					->orderBy('position','asc')
					->get();
				foreach($responsibility_list as $item){
					$custom_data = Helpers::convertCustomFields(unserialize($item->custom_fields));
					$settings['responsibility'][] = [
						'slug'	=> $item->slug,
						'title'	=> $item->title,
						'data'	=> $custom_data
					];
				}
				$damage_coverage_module = EnabledModules::select('id')->where('slug','=','spisok_pokrytie_povrezhdenij')->first();
				$damage_coverage_list = Categories::select('title','slug','custom_fields')
					->where('module_id','=',$damage_coverage_module->id)
					->where('enabled','=',1)
					->orderBy('position','asc')
					->get();
				foreach($damage_coverage_list as $item){
					$custom_data = Helpers::convertCustomFields(unserialize($item->custom_fields));
					$settings['damage_coverage'][] = [
						'slug'	=> $item->slug,
						'title'	=> $item->title,
						'data'	=> $custom_data
					];
				}
				$ride_out_module = EnabledModules::select('id')->where('slug','=','spisok_vyezdy_za_oblast')->first();
				$ride_out_module_list = Categories::select('title','slug','custom_fields')
					->where('module_id','=',$ride_out_module->id)
					->where('enabled','=',1)
					->orderBy('position','asc')
					->get();
				foreach($ride_out_module_list as $item){
					$custom_data = Helpers::convertCustomFields(unserialize($item->custom_fields));
					$settings['ride_out'][] = [
						'slug'	=> $item->slug,
						'title'	=> $item->title,
						'data'	=> $custom_data
					];
				}

				return view('car_nodriver_order', [
					'defaults'	=> $defaults,
					'meta'		=> $page_meta_data,
					'content'	=> $content,
					'settings'	=> $settings
				]);
			}
		}else{
			return redirect(route('page404'));
		}
	}

	public function page404(){
		if(!isset($_COOKIE['current_city'])){
			setcookie('current_city','sankt-peterburg', time()+36000, '/');
			$current_city = 'sankt-peterburg';
		}else{
			$current_city = $_COOKIE['current_city'];
		}
		$defaults = Helpers::getDefaultContent($current_city);
		$page_meta_data = [
			'slug' => '/about_us',
			'title' => '404',
			'meta_title' => '404',
			'meta_keywords' => '',
			'meta_description' => ''
		];
		return view('404', [
			'defaults'	=> $defaults,
			'meta'		=> $page_meta_data,
		]);
	}
}