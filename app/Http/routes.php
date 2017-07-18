<?php
Route::get('/', [
	'as'	=> 'user-home',
	'uses'	=> 'Site\SitePagesController@homePage'
]);
//Регистрация
Route::get('/registration', [
	'as'	=> 'user-registration',
	'uses'	=> 'Site\SitePagesController@registration'
]);
//Отправка данных регистрации
Route::post('/registration', [
	'as'	=> 'user-register-me',
	'uses'	=> 'Site\UserAuthController@userRegistration'
]);
//Получение подтверждения регистрации
Route::get('/confirm/{token}',[
	'as'	=> 'user-confirm-token',
	'uses'	=> 'Site\UserAuthController@confirmAccessToken'
]);

//Тех поддержка
Route::get('/support', [
	'as'	=> 'user-support',
	'uses'	=> 'Site\SitePagesController@supportPage'
]);

Route::post('/support', [
	'as'	=> 'user-sends-letter',
	'uses'	=> 'Site\UserAuthController@userSendsLetter'
]);

//Авторизация пользователя
Route::post('/login', [
	'as'	=> 'user-login',
	'uses'	=> 'Site\UserAuthController@login'
]);
//Выход пользователя
Route::get('/logout', [
	'as'	=> 'user-logout',
	'uses'	=> 'Site\UserAuthController@logout'
]);

//Страницы авторизированого пользователя
Route::group(['middleware' => 'notAuth'], function() {
	//Представления страниц
	//Столы
	Route::get('/games', [
		'as'	=> 'user-active-games',
		'uses'	=> 'Site\SitePagesController@gamesPage'
	]);
		//Валидация колоды
		Route::get('/validate_deck', [
			'uses'	=> 'Site\SiteFunctionsController@validateDeck'
		]);
		//Создание стола
		Route::post('/user_create_battle', [
			'as'	=> 'user-create-table',
			'uses'	=> 'Site\SiteGameController@createRoom'
		]);

	//Играть
	Route::get('/play/{id}', [
		'as'	=> 'user-in-game',
		'uses'	=> 'Site\SitePagesController@playPage'
	]);
		//Присоединение к столу
		Route::put('/user_connect_to_battle', [
			'uses'	=> 'Site\SiteGameController@userConnectToRoom'
		]);
		//Старт игры
		Route::put('/game_start', [
			'uses'	=> 'Site\SiteGameController@startGame'
		]);
		//Пользовательсменил карты
		Route::put('/game_user_change_cards', [
			'uses'	=> 'Site\SiteGameController@userReady'
		]);
		//Получить данные о карте
		Route::get('/game_get_card_data', [
			'uses'	=> 'Site\SiteGameController@getCardDataByRequest'
		]);
		//Получить данные о магии
		Route::get('/game_get_magic_data', [
			'uses'	=> 'Site\SiteGameController@getMagicDataByRequest'
		]);

	//Рейтинг
	Route::get('/rating', [
		'as'	=> 'rating-page',
		'uses'	=> 'Site\SitePagesController@ratingPage'
	]);
		Route::get('user_rating', [
			'uses'	=> 'Site\SiteFunctionsController@getUserRating'
		]);
		Route::get('/user_rating_scroll', [
			'uses'	=> 'Site\SiteFunctionsController@getUsersRatingByScroll'
		]);

	//Мои карты
	Route::get('/deck', [
		'as'	=> 'deck-page',
		'uses'	=> 'Site\SitePagesController@deckPage'
	]);
		//Получить колоду пользователя
		Route::get('/get_user_deck', [
			'uses'	=> 'Site\SiteFunctionsController@getUserDecks'
		]);
		//Изменение Колоды
		Route::put('/change_user_deck', [
			'uses'	=> 'Site\SiteFunctionsController@userPullOverCard'
		]);
		//Очистка колоды
		Route::put('/clear_deck', [
			'uses'	=> 'Site\SiteFunctionsController@userClearDeck'
		]);

	//Магазин
	Route::get('/market', [
		'as'	=> 'market-page',
		'uses'	=> 'Site\SitePagesController@marketPage'
	]);
		//Получить колоду по фракции
		Route::get('/get_cards_by_fraction', [
			'uses'	=> 'Site\SiteFunctionsController@cardsByFraction'
		]);
		//Подготовка покупки карты
		Route::get('/get_card_data', [
			'uses'	=> 'Site\SiteFunctionsController@getUserRequestToBuyCard'
		]);
		//Покупка карты
		Route::put('/card_buying', [
			'uses'	=> 'Site\SiteFunctionsController@userBuyCard'
		]);

	//Волшебство
	Route::get('/magic', [
		'as'	=> 'magic-page',
		'uses'	=> 'Site\SitePagesController@magicPage'
	]);
		Route::get('/get_magic_by_fraction', [
			'uses'	=> 'Site\SiteFunctionsController@getMagicByFraction'
		]);
		//Подготовка покупки волшебства
		Route::get('/get_magic_effect_data', [
			'uses'	=> 'Site\SiteFunctionsController@getUserRequestToBuyMagic'
		]);
		//Покупка волшебства
		Route::post('/magic_is_buyed', [
			'uses'	=> 'Site\SiteFunctionsController@userBuyingMagic'
		]);
		//Изменение активности магии
		Route::put('/magic_change_status', [
			'uses'	=> 'Site\SiteFunctionsController@userChangesMagicStatus'
		]);

	//Настройки
	Route::get('/settings', [
		'as'	=> 'user-settings-page',
		'uses'	=> 'Site\SitePagesController@settingsPage'
	]);
		Route::put('/settings', [
			'as'	=> 'user-settings-change',
			'uses'	=> 'Site\UserAuthController@userChangeSettings'
		]);

	//Обучение
	Route::get('/training', [
		'as'	=> 'training-page',
		'uses'	=> 'Site\SitePagesController@trainingPage'
	]);


	//Пользователь покупает премиум аккаунт
	Route::put('/user_buying_premium', [
		'as'	 => 'user-buying-prem-acc',
		'uses'	=> 'Site\SiteFunctionsController@userBuyingPremium'
	]);
	//Пользователь покупает серебро
	Route::put('/user_buying_silver', [
		'uses'	=> 'Site\SiteFunctionsController@userBuyingSilver'
	]);
	//Пользователь покупает энергию
	Route::put('/user_buying_energy', [
		'uses'	=> 'Site\SiteFunctionsController@userBuyingEnergy'
	]);
	//Получить статус занятости игрока
	Route::get('/check_user_playing_status', [
		'uses'	=> 'Site\SiteFunctionsController@getUserPlayingStatus'
	]);
});

//Получить количество пользователей онлайн
Route::get('/get_user_quantity', [
	'uses'	=> 'Site\SiteFunctionsController@getUsersQuantity'
]);
//Получить данные о пользователе
Route::get('/get_user_data', [
	'uses'	=> 'Site\SiteFunctionsController@getUserData'
]);
//Игра
Route::get('/get_socket_settings', [
	'uses'	=> 'Site\SiteGameController@socketSettings'
]);
//Получить описание рассы
Route::get('/get_fraction_description',[
	'uses'	=> 'Site\SiteFunctionsController@getFractionDescription'
]);


//Yadnex Money

//Страница отправки пополнения счета
Route::get('/transactions/{id}', [
	'uses'	=> 'Site\SitePagesController@payPage'
]);
//Страница успешно выполненного платежа
Route::post('/ya-mon/success', [
	'as'	=> 'ya-mo-succ',
	'uses'	=> 'Site\PaymentController@success'
]);
Route::post('/create_payment', [
	'uses'	=> 'Site\PaymentController@createPayStory'
]);

//Admin

//Authorisation
Route::get('/admin/login', [
	'as'	=> 'admin-login',
	'uses'	=> 'Admin\AdminAuthController@getLogin'
]);
Route::post('/admin/login', [
	'uses'	=> 'Admin\AdminAuthController@login'
]);
Route::get('/admin/logout', [
	'uses'	=> 'Admin\AdminAuthController@logout'
]);

Route::group(['middleware' => 'admin'], function() {
	//Главная (Фракции)
	Route::get('/admin', [
		'as' => 'admin-main',
		'uses' => 'Admin\AdminPagesController@index'
	]);
		//Добавление Фракции
		Route::get('/admin/fraction/add', [
			'as'	=> 'admin-fraction-add',
			'uses'	=> 'Admin\AdminPagesController@fractionAddPage'
		]);
		//Редактирование Фракции
		Route::get('/admin/fraction/edit/{id}', [
			'as'	=> 'admin-fraction-edit-it',
			'uses'	=> 'Admin\AdminPagesController@fractionEditPage'
		]);
		//Добавление Фракции [Кнопка "Добавить"]
		Route::post('/admin/fraction/add', [
			'as'	=> 'admin-fraction-add',
			'uses'	=> 'Admin\AdminFractionController@addFraction'
		]);
		//Изменение Фракции [Кнопка "Применить"]
		Route::put('/admin/fraction/edit', [
			'as'	=> 'admin-fraction-edit',
			'uses'	=> 'Admin\AdminFractionController@editFraction'
		]);
		//Удаление Фракции
		Route::delete('/admin/fraction/drop', [
			'as'	=> 'admin-fraction-drop',
			'uses'	=> 'Admin\AdminFractionController@dropFraction'
		]);


	//Настройки лиг
	Route::get('/admin/leagues', [
		'as'	=> 'admin-leagues',
		'uses'	=> 'Admin\AdminPagesController@leaguePage'
	]);
		//Сохранение лиг
		Route::post('/admin/league_apply', [
			'uses'	=> 'Admin\AdminLeagueController@leagueApply'
		]);
		//Удаление лиги
		Route::delete('/admin/league/drop', [
			'as'	=> 'admin-league-drop',
			'uses'	=> 'Admin\AdminLeagueController@leagueDrop'
		]);

	//Базовые карты
	Route::get('/admin/base_deck', [
		'as'	=> 'admin-base-deck',
		'uses'	=> 'Admin\AdminPagesController@baseDecksPage'
	]);
		Route::get('/admin/get_all_cards_selector', [
			 'uses'	 => 'Admin\AdminViews@getAllCardsSelector'
		]);
		Route::put('/admin/base_deck/save', [
			 'uses'	 => 'Admin\AdminFractionController@saveBaseDeck'
		]);

	//Соотношение обменов
	Route::get('/admin/exchanges', [
		'as'	=> 'admin-exchanges',
		'uses'	=> 'Admin\AdminPagesController@exchangesPage'
	]);
		//Сохранение обменов
		Route::put('/admin/save_exchanges', [
			'as'	=> 'admin-exchange-change',
			'uses'	=> 'Admin\AdminEtcDataController@editExchanges'
		]);

	//Настройка Покупки премиум аккаунта
	Route::get('/admin/premium', [
		'as'	=> 'admin-premium',
		'uses'	=> 'Admin\AdminPagesController@premiumPage'
	]);
		//Сохранение настроек премиума
		Route::put('/admin/save_premium', [
			'as'	=> 'admin-premium-options',
			'uses'	=> 'Admin\AdminEtcDataController@editPremium'
		]);

	//Настройки колоды
	Route::get('/admin/deck_options', [
		'as'	=> 'admin-deck-options',
		'uses'	=> 'Admin\AdminPagesController@deckOptionsPage'
	]);
		//Сохранение настроек колоды
		Route::put('/admin/save_deck_options', [
			'as'	=> 'admin-save-deck-options',
			'uses'	=> 'Admin\AdminEtcDataController@editDeckOptions'
		]);

	//Базовые поля пользователей
	Route::get('/admin/user/fields', [
		'as'	=> 'admin-user-fields',
		'uses'	=> 'Admin\AdminPagesController@userBasicFieldsPage'
	]);
		Route::put('/admin/user/fields/save', [
			'as'	=> 'admin-user-fields-save',
			'uses'	=> 'Admin\AdminEtcDataController@editBasicFieldsOptions'
		]);
	//Тайминг боя
	Route::get('/admin/timing', [
		'as'	=> 'admin-timing',
		'uses'	=> 'Admin\AdminPagesController@battleTiming'
	]);
		Route::put('/admin/timing/save', [
			'as'	=> 'admin-timing-save',
			'uses'	=> 'Admin\AdminEtcDataController@editBattleTiming'
		]);


	//Карты
	Route::get('/admin/cards', [
		'as'	=> 'admin-cards',
		'uses'	=> 'Admin\AdminPagesController@cardsPage'
	]);
		//Страница добавления Карты
		Route::get('/admin/card/add', [
			'as'	=> 'admin-card-add',
			'uses'	=> 'Admin\AdminPagesController@cardAddPage'
		]);
		//Страница редактирования Карты
		Route::get('/admin/card/edit/{id}', [
			'as'	=> 'admin-card-edit-page',
			'uses'	=> 'Admin\AdminPagesController@cardEditPage'
		]);
		//Добавление Карты [Кнопка "Добавить"]
		Route::post('/admin/cards/add', [
			'uses'	=> 'Admin\AdminCardsController@addCard'
		]);
		//Изменение Карты [Кнопка "Применить"]
		Route::put('/admin/card/edit', [
			'as'	=> 'admin-card-edit',
			'uses'	=> 'Admin\AdminCardsController@editCard'
		]);
		//Удалить Карту
		Route::delete('/admin/card/drop', [
			'as'	=> 'admin-cards-drop',
			'uses'	=> 'Admin\AdminCardsController@dropCard'
		]);
		//Получить текущие группы
		Route::get('/admin/cards/get_card_groups', [
			'uses'	=> 'Admin\AdminViews@cardsViewGroupsList'
		]);


	//Группы карт
	Route::get('/admin/card/groups', [
		'as'	=> 'admin-card-groups',
		'uses'	=> 'Admin\AdminPagesController@cardGroupsPage'
	]);
		//Страница добавления Групп карт
		Route::get('/admin/card/groups/add', [
			'as'	=> 'admin-card-groups-add',
			'uses'	=> 'Admin\AdminPagesController@cardGroupsAddPage'
		]);
		//Страница редактирования Группы карт
		Route::get('/admin/card/groups/edit/{id}', [
			'as'	=> 'admin-card-groups-edit-page',
			'uses'	=> 'Admin\AdminPagesController@cardGroupsEditPage'
		]);
		//Добавление Группы карт [Кнопка "Добавить"]
		Route::post('/admin/card/groups/add', [
			'uses'	=> 'Admin\AdminCardGroupController@addCardGroup'
		]);
		//Изменение Группы карт [Кнопка "Применить"]
		Route::put('/admin/card/groups/edit', [
			'as'	=> 'admin-card-groups-edit',
			'uses'	=> 'Admin\AdminCardGroupController@editCardGroup'
		]);
		//Удалить Группы карт
		Route::delete('/admin/card/groups/drop', [
			'as'	=> 'admin-card-groups-drop',
			'uses'	=> 'Admin\AdminCardGroupController@dropCardGroup'
		]);


	//Волшебство
	Route::get('/admin/magic', [
		'as'	=> 'admin-magic',
		'uses'	=> 'Admin\AdminPagesController@magicPage'
	]);
		//Страница добавления Волшебства
		Route::get('/admin/magic/add', [
			'as'	=> 'admin-magic-add',
			'uses'	=> 'Admin\AdminPagesController@magicAddPage'
		]);
		//Страница редактирования Волшебства
		Route::get('/admin/magic/edit/{id}', [
			'as'	=> 'admin-magic-edit-page',
			'uses'	=> 'Admin\AdminPagesController@magicEditPage'
		]);
		//Добавление Волшебства [Кнопка "Добавить"]
		Route::post('/admin/magic/add', [
			'uses'	=> 'Admin\AdminMagicController@addMagic'
		]);
		Route::put('/admin/magic/edit', [
			'as'	=> 'admin-magic-edit',
			'uses'	=> 'Admin\AdminMagicController@editMagic'
		]);
		//Удалить Волшебство
		Route::delete('/admin/magic/drop', [
			'as'	=> 'admin-magic-drop',
			'uses'	=> 'Admin\AdminMagicController@dropMagic'
		]);


	//Действия
	Route::get('/admin/actions', [
		'as'	=> 'admin-actions',
		'uses'	=> 'Admin\AdminPagesController@actionsPage'
	]);
		//Страница добавления Действия
		Route::get('/admin/action/add', [
			'as'	=> 'admin-action-add',
			'uses'	=> 'Admin\AdminPagesController@actionAddPage'
		]);
		//Страница редактирования Действия
		Route::get('/admin/action/edit/{id}', [
			'as'	=> 'admin-action-edit-page',
			'uses'	=> 'Admin\AdminPagesController@actionEditPage'
		]);
		//Добавление Действия [Кнопка "Добавить"]
		Route::post('/admin/action/add', [
			'uses'	=> 'Admin\AdminActionsController@addAction'
		]);
		//Изменение Действие [Кнопка "Применить"]
		Route::put('/admin/action/edit', [
			'uses'	=> 'Admin\AdminActionsController@editAction'
		]);
		//Удалить Действие
		Route::delete('/admin/action/drop', [
			'as'	=> 'admin-actions-drop',
			'uses'	=> 'Admin\AdminActionsController@dropAction'
		]);

	//Пользователи
	Route::get('/admin/users', [
		'as'	=> 'admin-users',
		'uses'	=> 'Admin\AdminPagesController@usersPage'
	]);
		Route::get('/admin/user/{id}', [
			'as'	=> 'admin-user-edit-page',
			'uses'	=> 'Admin\AdminPagesController@editUser'
		]);
		//Удалить
		Route::delete('/admin/user/drop', [
			'as'	=> 'admin-user-drop',
			'uses'	=> 'Admin\AdminUserController@dropUser'
		]);
		//Забанить
		Route::put('/admin/user/ban', [
			'as'	=> 'admin-ban-user',
			'uses'	=> 'Admin\AdminUserController@banUser'
		]);
		//Разабанить
		Route::put('/admin/user/unban', [
			'as'	=> 'admin-unban-user',
			'uses'	=> 'Admin\AdminUserController@unbanUser'
		]);
		//Редактирование пользователя
		Route::put('/admin/user/edit', [
			'as'	=> 'admin-user-edit',
			'uses'	=> 'Admin\AdminUserController@userEdit'
		]);
	//Оплаченые пополнения
    Route::get('/admin/payments', [
        'as'    => 'admin-payment',
        'uses'  => 'Admin\AdminPagesController@usersPayment'
    ]);

	//Страницы
	Route::get('/admin/pages', [
		'as'	=> 'admin-pages',
		'uses'	=> 'Admin\AdminPagesController@viewPages'
	]);
		Route::get('admin/pages/show_to_edit', [
			'uses'	=> 'Admin\AdminPagesController@viewEditablePage'
		]);
		Route::put('/admin/pages/edit', [
			'uses'	=> 'Admin\AdminPagesController@editPage'
		]);
	//Тех поддержка
	Route::get('/admin/support', [
		'as'	=> 'admin-support',
		'uses'	=> 'Admin\AdminPagesController@supportPage'
	]);
		Route::post('/admin/support/add', [
			'uses'	=> 'Admin\AdminPagesController@supportAddRubric'
		]);
		Route::put('/admin/support/edit', [
			'uses'	=> 'Admin\AdminPagesController@supportEditRubric'
		]);
		Route::put('/admin/support/change_positions', [
			'uses'	=> 'Admin\AdminPagesController@supportChangeRubricPosition'
		]);
		Route::delete('/admin/support/drop', [
			'uses'	=> 'Admin\AdminPagesController@supportDropRubric'
		]);
		Route::post('/admin/support/add_admin', [
			'uses'	=> 'Admin\AdminPagesController@supportAddEmail'
		]);
		Route::put('/admin/support/edit_admin', [
			'uses'	=> 'Admin\AdminPagesController@supportEditEmail'
		]);
		Route::delete('/admin/support/drop_admin', [
			'uses'	=> 'Admin\AdminPagesController@supportDropEmail'
		]);

	//Получить селектор всех карт в таблице
	Route::get('/admin/get_all_cards_selector', [
		'uses'	=> 'Admin\AdminViews@getAllCardsSelector'
	]);
	//Получить селектор всех карт
	Route::get('/admin/get_cards_selector', [
		'uses'	=> 'Admin\AdminViews@getAllCardsSelectorView'
	]);
});