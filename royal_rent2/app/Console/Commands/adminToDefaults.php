<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class adminToDefaults extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'admin:reset';

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
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(){
		$this->info("\t".'Attention. This command will destroy all of your database data.');
		if ($this->confirm("\t".'Do you wish to continue?')) {
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
			if(count($missing_tables) > 0){
				$error_txt = "\n\r\n\r\t".'ERROR. There is a missing table';
				if($missing_tables > 1){
					$error_txt .= 's';
				}
				$error_txt .= ":";
				foreach($missing_tables as $table){
					$error_txt .= "\n\r\t".$table;
				}
				$error_txt .= "\n\r\t".'Try to use a command admin:installDB'."\n\r";
				$this->error($error_txt);
			}else{
				foreach($tables as $table){
					$field = 'Tables_in_'.env('DB_DATABASE');
					\DB::table($table->$field)->truncate();
					switch($table->$field){
						case 'tbl_admin_menu':
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
						break;
						case 'tbl_modules':
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
						break;
						case 'tbl_user_roles':
							$data = [
								['title'=>'Главный администратор','pseudonim'=>'ADM_ROLE','editable'=>0,'access_pages'=>'a:0:{}'],
								['title'=>'Гость','pseudonim'=>'user','editable'=>0,'access_pages'=>'deny_all']
							];
							foreach($data as $role){
								\DB::table('tbl_user_roles')->insert($role);
							}
						break;
					}
				}
				$this->info("\t".'Tables truncates successfully'."\n\r\t".'tbl_admin_menu, tbl_user_roles, tbl_modules are refilled');
			}
		}
	}
}
