<?php

namespace App\Console\Commands;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Console\Command;

class adminInstallDB extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'admin:installDB';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(){
		$default_table_list = [
			'migrations',
			'password_resets',
			'users',
			'tbl_admin_menu',
			'tbl_articles',
			'tbl_categories',
			'tbl_enabled_modules',
			'tbl_menu_items',
			'tbl_modules',
			'tbl_page_content',
			'tbl_products',
			'tbl_promo',
			'tbl_reviews',
			'tbl_site_pages',
			'tbl_user_roles'
		];

		$tables = \DB::select('SHOW TABLES');
		$tables_list = [];
		foreach($tables as $table){
			$field = 'Tables_in_'.env('DB_DATABASE');
			$tables_list[] = $table->$field;
		}
		$missing_tables = array_diff($default_table_list, $tables_list);
		foreach($missing_tables as $table){
			switch($table){
				case 'migrations':		CreateTables::createMigrations(); break;
				case 'password_resets':	CreateTables::createPasswordResets(); break;
				case 'users':			CreateTables::createUsers(); break;
				case 'tbl_admin_menu':	CreateTables::createAdminMenu(); break;
				case 'tbl_articles':	CreateTables::createArticles(); break;
				case 'tbl_categories':	CreateTables::createCategories(); break;
				case 'tbl_enabled_modules': CreateTables::createEnabledModules(); break;
				case 'tbl_menu_items':	CreateTables::createSiteMenu(); break;
				case 'tbl_modules':		CreateTables::createModules(); break;
				case 'tbl_page_content':CreateTables::createPageContent(); break;
				case 'tbl_products':	CreateTables::createProducts(); break;
				case 'tbl_promo':		CreateTables::createPromo(); break;
				case 'tbl_reviews':		CreateTables::createReviews(); break;
				case 'tbl_site_pages':	CreateTables::createSitePages(); break;
				case 'tbl_user_roles':	CreateTables::createUserRoles(); break;
			}
		}
		$this->info('Database installed successfully');
	}
}

class CreateTables extends Migration
{
	public static function createAdminMenu(){
		\Schema::create('tbl_admin_menu', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->smallInteger('position')->unsigned();
			$table->integer('refer_to')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->boolean('enabled');
			$table->timestamps();
		});
		$data = [
			['title'=>'Главная','slug'=>'/admin','position'=>0,'refer_to'=>0,'enabled'=>1],
			['title'=>'Настройки','slug'=>'#','position'=>1,'refer_to'=>0,'enabled'=>1],
			['title'=>'Модули','slug'=>'/admin/modules','position'=>4,'refer_to'=>0,'enabled'=>1],
			['title'=>'Пользователи и права','slug'=>'#','position'=>2,'refer_to'=>0,'enabled'=>1],
			['title'=>'Основные настройки','slug'=>'/admin/settings','position'=>0,'refer_to'=>2,'enabled'=>1],
			['title'=>'Админ Меню','slug'=>'/admin/menu_settings','position'=>1,'refer_to'=>2,'enabled'=>1],
			['title'=>'Роли и права','slug'=>'/admin/user_roles','position'=>0,'refer_to'=>4,'enabled'=>1],
			['title'=>'Пользователи','slug'=>'/admin/users','position'=>1,'refer_to'=>4,'enabled'=>1],
			['title'=>'Почта и переписка','slug'=>'/admin/mailing','position'=>2,'refer_to'=>2,'enabled'=>1],
			['title'=>'Страницы','slug'=>'/admin/pages','position'=>3,'refer_to'=>0,'enabled'=>1],
		];
		foreach($data as $menu_item){
			\DB::table('tbl_admin_menu')->insert($menu_item);
		}
	}
	public static function createArticles(){
		\Schema::create('tbl_articles', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('img_url');
			$table->text('description');
			$table->string('text_caption');
			$table->text('text');
			$table->text('custom_fields');
			$table->string('meta_title');
			$table->string('meta_description');
			$table->string('meta_keywords');
			$table->integer('module_id')->unsigned();
			$table->string('author');
			$table->integer('views')->unsigned();
			$table->boolean('enabled');
			$table->timestamp('published_at');
			$table->timestamps();
		});
	}
	public static function createCategories(){
		\Schema::create('tbl_categories', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('img_url');
			$table->text('description');
			$table->text('text');
			$table->text('custom_fields');
			$table->smallInteger('position')->unsigned();
			$table->integer('refer_to')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->string('author');
			$table->boolean('enabled');
			$table->timestamps();
		});
	}
	public static function createEnabledModules(){
		\Schema::create('tbl_enabled_modules', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->boolean('unique_slug');
			$table->integer('type')->unsigned();
			$table->text('description');
			$table->text('disabled_fields');
			$table->text('custom_fields');
			$table->smallInteger('position')->unsigned();
			$table->boolean('enabled');
			$table->timestamps();
		});
	}
	public static function createMigrations(){
		\Schema::create('migrations', function (Blueprint $table) {
			$table->string('migration');
			$table->integer('batch');
		});
	}
	public static function createModules(){
		\Schema::create('tbl_modules', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('description');
			$table->text('options');
			$table->timestamps();
		});
		$data = [
			['title'=>'Меню Сайта','slug'=>'menu','description'=>'Данный модуль предназначен для добавления списков и меню на сайтa','options'=>'a:0:{}'],
			['title'=>'Категории','slug'=>'categories','description'=>'Данный модуль предназначен для добавления категорий на сайт','options'=>'a:3:{i:0;a:3:{s:4:"type";s:10:"img_slider";s:4:"name";s:7:"img_url";s:7:"caption";s:22:"Изображение";}i:1;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:11:"description";s:7:"caption";s:16:"Описание";}i:2;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:4:"text";s:7:"caption";s:10:"Текст";}}'],
			['title'=>'Статьи','slug'=>'articles','description'=>'Данный модуль предназначен для добавления статей (новости, заметки и т.д.)','options'=>'a:4:{i:0;a:3:{s:4:"type";s:10:"img_slider";s:4:"name";s:7:"img_url";s:7:"caption";s:22:"Изображение";}i:1;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:11:"description";s:7:"caption";s:16:"Описание";}i:2;a:3:{s:4:"type";s:6:"string";s:4:"name";s:12:"text_caption";s:7:"caption";s:29:"Заглавие текста";}i:3;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:4:"text";s:7:"caption";s:10:"Текст";}}'],
			['title'=>'Товары','slug'=>'products','description'=>'Данный модуль предназначен для добавления товаров на сайт','options'=>'a:4:{i:0;a:3:{s:4:"type";s:10:"img_slider";s:4:"name";s:7:"img_url";s:7:"caption";s:22:"Изображение";}i:1;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:11:"description";s:7:"caption";s:16:"Описание";}i:2;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:4:"text";s:7:"caption";s:10:"Текст";}i:3;a:3:{s:4:"type";s:6:"string";s:4:"name";s:5:"price";s:7:"caption";s:8:"Цена";}}'],
			['title'=>'Акции','slug'=>'promo','description'=>'Данный модуль предназначен для добавления акций и привязки товаров к ним','options'=>'a:5:{i:0;a:3:{s:4:"type";s:4:"date";s:4:"name";s:10:"date_begin";s:7:"caption";s:21:"Дата начала";}i:1;a:3:{s:4:"type";s:4:"date";s:4:"name";s:11:"date_finish";s:7:"caption";s:27:"Дата окончания";}i:2;a:3:{s:4:"type";s:10:"img_slider";s:4:"name";s:7:"img_url";s:7:"caption";s:22:"Изображение";}i:3;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:11:"description";s:7:"caption";s:16:"Описание";}i:4;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:4:"text";s:7:"caption";s:10:"Текст";}}'],
			['title'=>'Шаблоны страниц','slug'=>'pages','description'=>'Данный модуль предназначен для создания отдельных контентных страниц','options'=>'a:3:{i:0;a:3:{s:4:"type";s:10:"img_slider";s:4:"name";s:7:"img_url";s:7:"caption";s:22:"Изображение";}i:1;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:11:"description";s:7:"caption";s:16:"Описание";}i:2;a:3:{s:4:"type";s:8:"fulltext";s:4:"name";s:4:"text";s:7:"caption";s:10:"Текст";}}'],
		];
		foreach($data as $item){
			\DB::table('tbl_modules')->insert($item);
		}
	}
	public static function createPageContent(){
		\Schema::create('tbl_page_content', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('caption');
			$table->string('type');
			$table->text('content');
			$table->integer('refer_to')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->timestamps();
		});
	}
	public static function createPasswordResets(){
		\Schema::create('password_resets', function (Blueprint $table) {
			$table->string('email')->index();
			$table->string('token')->index();
			$table->timestamp('created_at');
		});
	}
	public static function createProducts(){
		\Schema::create('tbl_products', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('img_url');
			$table->text('description');
			$table->text('text');
			$table->float('price');
			$table->text('color');
			$table->text('custom_fields');
			$table->string('meta_title');
			$table->string('meta_description');
			$table->string('meta_keywords');
			$table->integer('module_id')->unsigned();
			$table->string('author');
			$table->integer('views')->unsigned();
			$table->boolean('enabled');
			$table->timestamp('published_at');
			$table->timestamps();
		});
	}
	public static function createPromo(){
		\Schema::create('tbl_promo', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('img_url');
			$table->text('description');
			$table->text('text');
			$table->string('discount');
			$table->timestamp('date_start');
			$table->timestamp('date_finish');
			$table->text('custom_fields');
			$table->string('meta_title');
			$table->string('meta_description');
			$table->string('meta_keywords');
			$table->integer('module_id')->unsigned();
			$table->string('author');
			$table->integer('views')->unsigned();
			$table->boolean('enabled');
			$table->timestamp('published_at');
			$table->timestamps();
		});
	}
	public static function createReviews(){
		\Schema::create('tbl_reviews', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->text('text');
			$table->integer('rating');
			$table->integer('refer_to')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->integer('associate_with')->unsigned();
			$table->timestamps();
		});
	}
	public static function createSiteMenu(){
		\Schema::create('tbl_menu_items', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('custom_fields');
			$table->integer('refer_to')->unsigned();
			$table->integer('module_id')->unsigned();
			$table->smallInteger('position')->unsigned();
			$table->boolean('enabled');
			$table->boolean('active');
			$table->timestamps();
		});
	}
	public static function createSitePages(){
		\Schema::create('tbl_site_pages', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('img_url');
			$table->text('description');
			$table->text('text');
			$table->text('custom_fields');
			$table->string('meta_title');
			$table->string('meta_description');
			$table->string('meta_keywords');
			$table->integer('module_id')->unsigned();
			$table->string('author');
			$table->integer('views')->unsigned();
			$table->boolean('enabled');
			$table->timestamp('published_at');
			$table->timestamps();
		});
	}
	public static function createUserRoles(){
		\Schema::create('tbl_user_roles', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('pseudonim');
			$table->boolean('editable');
			$table->text('access_pages');
			$table->timestamps();
		});
		$data = [
			['title'=>'Главный администратор','pseudonim'=>'ADM_ROLE','editable'=>0,'access_pages'=>'a:0:{}'],
			['title'=>'Гость','pseudonim'=>'user','editable'=>0,'access_pages'=>'deny_all']
		];
		foreach($data as $role){
			\DB::table('tbl_user_roles')->insert($role);
		}
	}
	public static function createUsers(){
		\Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->string('login')->unique();
			$table->string('password');
			$table->string('email');
			$table->string('name');
			$table->string('user_role');
			$table->text('img_url');
			$table->rememberToken();
			$table->timestamps();
		});
	}
}
