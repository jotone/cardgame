$(window).load(function() {

	/*
	 *   Общие методы
	 */

	function recalcBaseDecks(){
		$('#baseCards .edition').each(function(){
			var total_strength = 0;
			var total_q = 0;
			var total_val = 0;
			$(this).find('tbody').find('tr').each(function(){
				var row = ($(this).children('td:eq(1)').find('select').find('option:selected').text().split(' '));
				var strength = parseInt(row[row.length-3].slice(0, -1));

				var value = parseInt(row[row.length-1].slice(0, -1));

				var value_row = value * parseInt($(this).children('td:eq(2)').find('input[name=currentQuantity]').val());
				$(this).find('td:eq(3)').text(value_row);

				total_strength += strength * parseInt($(this).children('td:eq(2)').find('input[name=currentQuantity]').val());
				total_q += parseInt($(this).children('td:eq(2)').find('input[name=currentQuantity]').val());
				total_val += parseInt($(this).children('td:eq(3)').text());
			});
			$(this).find('thead:eq(1)').find('th:eq(1)').text('Сила колоды ' + total_strength);
			$(this).find('thead:eq(1)').find('th:eq(2)').text('Количество карт ' + total_q);
			$(this).find('thead:eq(1)').find('th:eq(3)').text('Вес колоды ' + total_val);
		});
	}

	function getCardValue(_this){
		var row  = _this.parents('fieldset').find('.edition').find('tbody').find('tr:last-child').find('option:selected').text().split(' ');
		row = parseInt(row[row.length-1].slice(0, -1));
		_this.parents('fieldset').find('.edition').find('tbody').find('tr:last-child').find('td:eq(3)').text(row);
	}

	//Меню закладок
	$('.bookmark_menu li').click(function () {
		$(this).parent().children('li').removeClass('active');
		$(this).addClass('active');
		$('body .main-central-wrap').hide();
		$('body #' + $(this).attr('data-link')).show();
	});

	//Удаление елементов из таблиц материалов
	function dropRowInEditionTable() {
		$('.edition tr td').on('click', 'a.drop', function (e) {
			e.preventDefault();
			$(this).parent().parent().remove();

			if($('#baseCards').length){
				recalcBaseDecks();
			}
		});
	}

	dropRowInEditionTable();

	$('input.drop').click(function (e) {
		var result = confirm('Вы действительно хотите удалить данный элемент?');
		if (result == false) {
			return false;
		}
	});

	//Сортировка таблиц
	$('.data-table').on('click', 'a.table-direction', function (e) {
		e.preventDefault();
		if (!$(this).hasClass('active')) {
			$(this).parents('.data-table').find('a.table-direction').removeClass('active');
			$(this).addClass('active');
			var column = $(this).closest('th').index();
			var arrayToSort = [];

			$('.data-table tbody tr').each(function () {
				arrayToSort.push($(this));
			});
			if ($(this).attr('data-direct') == 'down') {
				arrayToSort.sort(function (a, b) {
					return a.children('td:eq(' + column + ')').text().localeCompare(b.children('td:eq(' + column + ')').text());
				});
			} else {
				arrayToSort.sort(function (a, b) {
					return b.children('td:eq(' + column + ')').text().localeCompare(a.children('td:eq(' + column + ')').text())
				});
			}

			$('.data-table tbody').empty();
			for (var i in arrayToSort) {
				$('.data-table tbody').append(arrayToSort[i]);
			}
		}
	});

	//обновление изображения пользователя
	function settingUpdateImg(){
		$('fieldset input[type=file]').change(function(e){
			var _this = $(this);
			var reader = new FileReader();
			reader.onload = function (e) {
				if(_this.next('.image-container').children('img').length < 1){
					_this.next('.image-container').append('<img src="" alt="">');
				}
				_this.next('.image-container').children('img').attr('src', e.target.result);
			}
			reader.readAsDataURL( $(this).prop('files')[0] );
		});
	}
	settingUpdateImg();

	/*
	*  Фракции
	*/
	//Добавление Фракции
	$('input[name=addFraction]').click(function(){
		var token = $('input[name=_token]').val();
		//Имитация отправки данных через форму
		var formData = new FormData();
		//Наполнение формы
		formData.append('title', $('input[name=fraction_title]').val().trim() );                   //Название расы
		formData.append('slug', $('input[name=fraction_slug]').val().trim() );                     //Обозначение расы
		formData.append('description', $('textarea[name=fraction_text]').val().trim() );           //Описание расы
		formData.append('short_description', $('textarea[name=fraction_short_decr]').val().trim()); //Короткое описание
		formData.append('type', $('input[name=fraction_type]').val().trim() );                     //Тип карт колоды (расовая/нейтральная/специальная)
		formData.append('img_url', $('input[name=fractionAddImg]').prop('files')[0] );             //Изображение
		formData.append('bg_img', $('input[name=fractionBGAddImg]').prop('files')[0] );            //Изображение фона фракции
		formData.append('card_img', $('input[name=fractionCardBG]').prop('files')[0] );            //Изображение рубашки фракции
		formData.append('descr_shop', tinymce.get('fraction_shop').getContent());
		formData.append('descr_magic', tinymce.get('fraction_magic').getContent());
		$.ajax({
			url:		'/admin/fraction/add',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'POST',
			processData:false,
			contentType:false,
			datatype:	'JSON',
			data:		formData,
			success:	function(data){
				var result = JSON.parse(data);
				if(result.message == 'success') location = '/admin'; else alert(result.message);
			}
		});
	});

	//редактирование Фракции
	$('input[name=editFraction]').click(function(){
		var token = $('input[name=_token]').val();
		//Имитация отправки данных через форму
		var formData = new FormData();
		//Наполнение формы
		formData.append('_method', 'PUT');                                                         //Указывам метод PUT
		formData.append('id', $('input[name=fraction_id]').val() );                                //ID расы
		formData.append('title', $('input[name=fraction_title]').val().trim() );                   //Название расы
		formData.append('slug', $('input[name=fraction_slug]').val().trim() );                     //Обозначение расы
		formData.append('description', $('textarea[name=fraction_text]').val().trim() );           //Описание расы
		formData.append('short_description', $('textarea[name=fraction_short_decr]').val().trim());//Короткое описание
		formData.append('type', $('input[name=fraction_type]').val().trim() );                     //Тип карт колоды (расовая/нейтральная/специальная)
		formData.append('img_url', $('input[name=fractionAddImg]').prop('files')[0] );             //Новый файл изображения
		formData.append('bg_img', $('input[name=fractionBGAddImg]').prop('files')[0] );            //Изображение фона фракции
		formData.append('card_img', $('input[name=fractionCardBG]').prop('files')[0] );            //Изображение рубашки фракции
		formData.append('descr_shop', tinymce.get('fraction_shop').getContent());
		formData.append('descr_magic', tinymce.get('fraction_magic').getContent());
		$.ajax({
			url:		'/admin/fraction/edit',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'POST',
			processData:false,
			contentType:false,
			data:		formData,
			success:	function(data){
				var result = JSON.parse(data);
				if(result.message == 'success') location = '/admin'; else alert(result.message);
			}
		});
	});
	/*
	 *  END OF Фракции
	 */

	/*
	*  Лиги
	*/
	//Добавить строку Лиги
	$('input[name=leagueAddRow]').click(function(){
		$(this).parents('.main-central-wrap').children('.edition').children('tbody').append('<tr><td><a class="drop" href="#"></a><input name="leagueId" value="" type="hidden"></td>'
			+'<td><input data-type="toAdd" name="title" type="text" value="" style="min-width: 65px; width: 65px;"></td>'
			+'<td><input data-type="toAdd" name="min_lvl" type="number" value="" style="width: 65px;"></td>'
			+'<td><input data-type="toAdd" name="max_lvl" type="number" value="" style="width: 65px;"></td>'
			+'<td><table><tr><td>Обычный:</td><td><input data-type="toAdd" name="gold_per_win" type="number" value="" style="width: 65px;"></td></tr><tr><td>Премиум:</td><td><input data-type="toAdd" name="prem_gold_per_win" type="number" value="" style="width: 65px;"></td></tr></table></td>'
			+'<td><table><tr><td>Обычный:</td><td><input data-type="toAdd" name="gold_per_loose" type="number" value="" style="width: 65px;"></td></tr><tr><td>Премиум:</td><td><input data-type="toAdd" name="prem_gold_per_loose" type="number" value="" style="width: 65px;"></td></tr></table></td>'
			+'<td><table><tr><td>Обычный:</td><td><input data-type="toAdd" name="silver_per_win" type="number" value="" style="width: 65px;"></td></tr><tr><td>Премиум:</td><td><input data-type="toAdd" name="prem_silver_per_win" type="number" value="" style="width: 65px;"></td></tr></table></td>'
			+'<td><table><tr><td>Обычный:</td><td><input data-type="toAdd" name="silver_per_loose" type="number" value="" style="width: 65px;"></td></tr><tr><td>Премиум:</td><td><input data-type="toAdd" name="prem_silver_per_loose" type="number" value="" style="width: 65px;"></td></tr></table></td>'
			+'<td><input data-type="toAdd" name="rating_per_win" type="number" value="" style="width: 65px;"></td>'
			+'<td><input data-type="toAdd" name="rating_per_loose" type="number" value="" style="width: 65px;"></td>'
			+'<td><input data-type="toAdd" name="min_amount" type="number" value="" style="width: 65px;"></td></tr>');
		dropRowInEditionTable();
	});

	//Сохранение данных Лиг
	$('input[name=leagueApply]').click(function(){
		var leagueData = [];
		$('#leagueOptions .edition>tbody>tr').each(function(){
			var rowData = [];
			$(this).find('input[data-type=toAdd]').each(function(){
				rowData.push(JSON.parse('{"'+$(this).attr('name')+'": "'+$(this).val()+'"}'));
			});
			leagueData.push(rowData);
		});

		var token = $('input[name=_token]').val();

		$.ajax({
			url:	'/admin/league_apply',
			headers:{'X-CSRF-TOKEN': token},
			type:	'POST',
			data:	{leagueData:JSON.stringify(leagueData)},
			success:function(data){
				if(data == 'success') alert('Данные успешно изменены'); else alert(data);
			}
		});
	});
	/*
	 *  END OF Лиги
	 */


	/*
	* Базовые карты колод
	*/

	//Добавить строку
	$('.main-central-wrap input[name=baseCardsAddRow]').click(function(){
		var _this = $(this);
		$.get(
			'/admin/get_all_cards_selector',
			function(data){
				_this.parents('fieldset').find('.edition').find('tbody').append(data);
				dropRowInEditionTable();
				getCardValue(_this);
				recalcBaseDecks();
			}
		);
	});
	//Сохранение Базовых колод рас
	$('#baseCards').on('click', 'input[name=baseCardsApply]', function(){
		var deckType = $(this).attr('id');
		var token = $('input[name=_token]').val();
		var deckArray = [];
		$('#baseCards fieldset[data-race='+deckType+'] .edition tbody tr').each(function(){
			deckArray.push($(this).children('td').children('select[name=currentCard]').val());
			deckArray.push($(this).children('td').children('input[name=currentQuantity]').val());
		});
		deckArray = JSON.stringify(deckArray);
		$.ajax({
			url:		'/admin/base_deck/save',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'PUT',
			data:		{deckType:deckType, deckArray:deckArray},
			success: function(data){
				if(data == 'success'){
					alert('Данные успешно изменены.');
					recalcBaseDecks();
				}else{
					alert(data);
				}
			}
		})
	});

	if($('#baseCards').length){
		$('#baseCards').on('change', 'select[name=currentCard], input[name=currentQuantity]', function () {
			recalcBaseDecks();
		});
		recalcBaseDecks();
	}
	/*
	 * END OF Базовые карты колод
	 */


	/*
	 * Карты
	 */
	if($('#addCard').length > 0){
		$('select[name=chooseRace]').change(function(){
			location = '/admin/cards?race=' + $(this).val();
		});
	}
	/*
	 * END OF Карты
	 */


	/*
	 * Группы Карт
	 */

	//Добавление карты в группу
	$('.edition input[name=addCardToGroup]').click(function(){
		$('#currentCardsInGroup').append('<tr><td><a class="drop" href="#' + $('select[name=currentCard]').val() + '"></a></td><td>' + $('select[name=currentCard] option:selected').text() + '</td><td style="display: none;">' + $('select[name=currentCard]').val() + '</td></tr>');
		dropRowInEditionTable();
	});

	//Создание группы
	$('input[name=cardGroupAdd]').click(function(){
		var token = $('input[name=_token]').val();

		var cards = [];
		$('#currentCardsInGroup tr').each(function(){
			cards.push( $(this).children('td:eq(2)').text() );
		});
		cards = JSON.stringify(cards);

		$.ajax({
			url:		'/admin/card/groups/add',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'POST',
			datatype:	'JSON',
			data:		{title:$('input[name=group_title]').val().trim(), cards:cards},
			success:	function(data){
				console.log(data);
				if(data == 'success'){
					location = '/admin/card/groups';
				}
			}
		});
	});

	//Редактирование группы
	$('input[name=cardGroupEdit]').click(function(){
		var token = $('input[name=_token]').val();

		var cards = [];
		$('#currentCardsInGroup tr').each(function(){
			cards.push( $(this).children('td:eq(2)').text() );
		});
		cards = JSON.stringify(cards);
		$.ajax({
			url:		'/admin/card/groups/edit',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'PUT',
			datatype:	'JSON',
			data:		{token:token, title:$('input[name=group_title]').val().trim(), cards:cards, id:$('input[name=group_id]').val()},
			success:	function(data){
				console.log(data);
				if(data == 'success'){
					location = '/admin/card/groups';
				}
			}
		});
	});

	/*
	 * END OF Группы Карт
	 */


	/*
	 *   Действия
	 */

	//Добавление характеристик в Действия->Добавить/Изменить
	$('input[name=action_add_characteristic]').click(function(){
		$('#card_action_characteristic_table').append('<tr><td style="width: 10%; vertical-align: top;"><input name="action_characteristic_label" type="text"></td><td><textarea name="action_characteristic_html"></textarea></td></tr>');
	});

	/*
	 * Отправка данных для сохранения из Действия->Добавить
	 * в обработчик /admin/actions/add методом POST
	 */
	$('input[name=actionAdd]').click(function(){
		var token = $('input[name=_token]').val();

		var characteristics = [];

		$('#card_action_characteristic_table tr').each(function(){
			//Собираем данные характеристик
			var label = $(this).children('td').children('input[name=action_characteristic_label]').val();
			var value = $(this).children('td').children('textarea[name=action_characteristic_html]').val();
			//Проверям на пустые значения
			if((label != '')&&(value != '')){
				characteristics.push(label.trim());
				characteristics.push(value.trim());
			}
		});

		/*
		 * Собственно, отправка в /admin/actions/add
		 * X-CSRF-TOKEN нужен для избежания кроссайтовой отсылки
		 */
		var formData = new FormData();
		formData.append( 'title', $('input[name=action_title]').val().trim() );                 //Название действия
		formData.append( 'description', $('textarea[name=action_descr]').val().trim() );        //Описание действия
		formData.append( 'characteristics', JSON.stringify(characteristics) );                  //Характеристики
		$.ajax({
			url:		'/admin/action/add',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'POST',
			datatype:	'JSON',
			data:		formData,
			processData: false,
			contentType: false,
			success:    function(data){
				if(data == 'success'){
					location = '/admin/actions';
				}
			}
		})
	});

	/*
	 * Отправка данных для сохранения из Действия->Изменить
	 * в обработчик /admin/actions/edit методом PUT
	 */
	$('input[name=actionEdit]').click(function(){
		var token = $('input[name=_token]').val();

		var characteristics = [];

		$('#card_action_characteristic_table tr').each(function(){
			var label = $(this).children('td').children('input[name=action_characteristic_label]').val();
			var value = $(this).children('td').children('textarea[name=action_characteristic_html]').val();
			if((label != '')&&(value != '')){
				characteristics.push(label.trim());
				characteristics.push(value.trim());
			}
		});
		var formData = new FormData();
		formData.append( 'token', token );
		formData.append( '_method', 'PUT' );
		formData.append( 'id', $('input[name=action_id]').val() );                              //ID действия
		formData.append( 'title', $('input[name=action_title]').val().trim() );                 //Название действия
		formData.append( 'description', $('textarea[name=action_descr]').val().trim() );        //Описание действия
		formData.append( 'characteristics', JSON.stringify(characteristics) );                                  //Характеристики

		$.ajax({
			url:		'/admin/action/edit',
			headers:	{'X-CSRF-TOKEN': token},
			type:		'POST',
			datatype:	'JSON',
			data:		formData,
			processData:false,
			contentType:false,
			success:	function(data){
				if(data == 'success'){
					location = '/admin/actions';
				}
			}
		});
	});

	/*
	 *   END OF Действия
	 */

	/*
	* Пользователи
	*/
	$('#userData').on('click', 'a[data-type=banUser]', function(e){
		e.preventDefault();
		var token = $('input[name=_token]').val();
		var id = $(this).attr('id');
		var login = $(this).parents('tr').find('td:eq(1)').text();
		var _this = $(this);
		$.ajax({
			url:	'/admin/user/ban',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id},
			success: function(data){
				if(data == 'success'){
					_this.attr('data-type', 'unbanUser').text('Снять бан');
				}else{
					alert(data);
				}
			}
		});
	});

	$('#userData').on('click', 'a[data-type=unbanUser]', function(e){
		e.preventDefault();
		var token = $('input[name=_token]').val();
		var id = $(this).attr('id');
		var login = $(this).parents('tr').find('td:eq(1)').text();
		var _this = $(this);
		$.ajax({
			url:	'/admin/user/unban',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id},
			success: function(data){
				if(data == 'success'){
					_this.attr('data-type', 'banUser').text('Забанить');
				}else{
					alert(data);
				}
			}
		});
	});

	$('#userDataTable input[name=user_premium_active]').change(function(){
		if($(this).prop('checked')){
			$(this).next('span').text('Дизактивировать');
		}else{
			$(this).next('span').text('Активировать');
		}
	});

	$('#userDataTable input[name=user_admin]').change(function(){
		if($(this).prop('checked')){
			$(this).next('span').text('Снять права');
		}else{
			$(this).next('span').text('Дать права');
		}
	});

	$('input[name=userApplyChanges]').click(function(){
		var token = $('input[name=_token]').val();
		var id = $('input[name=user_id]').val();
		var email = $('input[name=user_email]').val();
		var name = $('input[name=user_name]').val();
		var birthDate = $('input[name=user_birth]').val();
		var gold = $('input[name=user_gold]').val();
		var silver = $('input[name=user_silver]').val();
		var energy = $('input[name=user_energy]').val();
		var premActive = $('input[name=user_premium_active]').prop('checked');
		var premExpire = $('input[name=premium_expire_data]').val();
		var role = $('input[name=user_admin]').prop('checked');

		$.ajax({
			url:	'/admin/user/edit',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id, email:email, name:name, birthDate:birthDate, gold:gold, silver:silver, energy:energy, premActive:premActive, premExpire:premExpire, role:role},
			success:function(data){
				if(data == 'success'){
					alert('Данные успешно изменены');
				}else{
					alert(data);
				}
			}
		});
	});
	/*
	* END OF Пользователи
	*/

	function tinyMCE(selector){
		if($(selector).length>0){
			tinymce.init({ selector:selector });
		}
	}
	tinyMCE('textarea[name=card_short_descr]');
	tinyMCE('textarea[name=card_full_descr]');
	tinyMCE('textarea[name=magic_descr]');
	tinyMCE('textarea[name=pageText]');
	tinyMCE('textarea[name=fraction_shop]');
	tinyMCE('textarea[name=fraction_magic]');

	/*
	 * Страницы
	 */

	$('#sitePagesTexts').on('change', 'select[name=pageSelector]', function(){
		var slug = $(this).val();
		$.ajax({
			url:	'/admin/pages/show_to_edit',
			type:	'GET',
			data:	{slug:slug},
			success:function(data){
				data = JSON.parse(data);
				$('#sitePagesTexts input[name=pageTitle]').val(data['title']);
				$('#sitePagesTexts textarea[name=pageText]').val(data['text']);
				tinymce.get('pageText').setContent(data['text']);
				$('#sitePagesTexts input[name=applyPage]').attr('data-slug',data['slug']);
				tinyMCE('textarea[name=pageText]');
			}
		});
	});

	$('#sitePagesTexts').on('click', 'input[name=applyPage]', function(){
		var title = $('#sitePagesTexts input[name=pageTitle]').val();
		var slug = $(this).attr('data-slug');
		//var text = $('#sitePagesTexts textarea[name=pageText]').val();
		var text = tinymce.get('pageText').getContent();
		var token = $('input[name=_token]').val();
		$.ajax({
			url:	'/admin/pages/edit',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{slug:slug, title:title, text:text},
			success:function(data){
				if(data == 'success') alert('Данные успешно изменены');
			}
		});
	});

	$('#supportPage input[name=addRubric]').click(function(){
		var token = $('input[name=_token]').val();
		var title = $('#supportPage input[name=newRubricTitle]').val().trim();
		if(title.length >0){
			$.ajax({
				url:	'/admin/support/add',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				data:	{title:title},
				success:function(data){
					data =JSON.parse(data);
					if(data['message'] == 'success'){
						location.reload(true);
					}
				}
			});
		}
	});

	$('#supportPage').on('click', 'input[name=applyChange]', function(){
		var id = $(this).attr('data-id');
		var token = $('input[name=_token]').val();
		var title = $(this).parent().find('input[name=changeTitle]').val();
		if(title.length >0){
			$.ajax({
				url:	'/admin/support/edit',
				type:	'PUT',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id, title:title},
				success:function(data){
					if(data != 'success') alert(data);
				}
			})
		}
	});

	$('#supportPage').on('click', 'input[name=dropRubric]', function(){
		var id = $(this).attr('data-id');
		var token = $('input[name=_token]').val();
		var _that = $(this);
		$.ajax({
			url:	'/admin/support/drop',
			type:	'DELETE',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id},
			success:function(data){
				if(data == 'success'){
					_that.parents('tr').remove();
				}
			}
		})
	});

	$('#supportPage input[name=addEmail]').click(function(){
		var token = $('input[name=_token]').val();
		var email = $('#supportPage input[name=rubricAdminEmail]').val().trim();
		if(email.length >0){
			$.ajax({
				url:	'/admin/support/add_admin',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				data:	{email:email},
				success:function(data){
					if(data == 'success'){
						var lastIndex = parseInt($('#supportPage #adminsTable tr:last input.drop').attr('data-id')) +1;
						$('#supportPage #adminsTable').append('<tr>'+
							'<td><input name="dropEmail" class="drop" value="" type="button" data-id="'+lastIndex+'"></td>'+
							'<td><input name="changeEmail" type="text" value="'+email+'">'+
							'<input name="applyChange" type="button" value="Применить" data-id="'+lastIndex+'"></td></tr>');
						$('#supportPage input[name=rubricAdminEmail]').val('');
					}
				}
			});
		}
	});

	$('#supportPage').on('click', 'input[name=applyEmailChange]', function(){
		var iter = $(this).attr('data-id');
		var token = $('input[name=_token]').val();
		var email = $(this).parent().find('input[name=changeEmail]').val();
		if(email.length >0){
			$.ajax({
				url:	'/admin/support/edit_admin',
				type:	'PUT',
				headers:{'X-CSRF-TOKEN': token},
				data:	{iter:iter, email:email},
				success:function(data){
					if(data != 'success') alert(data);
				}
			})
		}
	});
	$('#supportPage').on('click', 'input[name=dropEmail]', function(){
		var iter = $(this).attr('data-id');
		var token = $('input[name=_token]').val();
		var _that = $(this);
		$.ajax({
			url:	'/admin/support/drop_admin',
			type:	'DELETE',
			headers:{'X-CSRF-TOKEN': token},
			data:	{iter:iter},
			success:function(data){
				if(data == 'success'){
					_that.parents('tr').remove();
					$('#supportPage #adminsTable tr').each(function(){
						$(this).find('input.drop').attr('data-id', $(this).index());
						$(this).find('input[name=applyEmailChange]').attr('data-id', $(this).index());
					});
				}
			}
		})
	});

	$('#supportPage #rubricsTable tbody').sortable({
		update: function(e, ui){
			var rubrics = [];
			var token = $('input[name=_token]').val();
			$('#supportPage #rubricsTable tr').each(function(){
				rubrics.push($(this).find('input[name=dropRubric]').attr('data-id'));
			});
			$.ajax({
				url:	'/admin/support/change_positions',
				type:	'PUT',
				headers:{'X-CSRF-TOKEN': token},
				data:	{rubrics:rubrics},
				success:function(data){
					if(data!='success') alert(data);
				}
			});
		}
	});
	/*
	 * END OF Страницы
	 */
});