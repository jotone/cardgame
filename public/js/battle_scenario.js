// ajax error message
function ajaxErrorMsg(jqXHR, exception) {
	var msg = '';
	if (jqXHR.status === 0) {
		msg = 'Not connect.\n Verify Network.';
	} else if (jqXHR.status == 404) {
		msg = 'Requested page not found. [404]';
	} else if (jqXHR.status == 500) {
		msg = 'Internal Server Error [500].';
	} else if (exception === 'parsererror') {
		msg = 'Requested JSON parse failed.';
	} else if (exception === 'timeout') {
		msg = 'Time out error.';
	} else if (exception === 'abort') {
		msg = 'Ajax request aborted.';
	} else {
		msg = 'Uncaught Error.\n' + jqXHR.responseText;
	}
	resultPopupShow(msg);
}
// /ajax error message

//Timer Functions
	function convertTimeToStr(seconds) {
		if(seconds > timeOut){
			seconds = timeOut;
		}
		if(seconds >= 0){
			var time = {'m':Math.floor(seconds / 60), 's':seconds % 60};
			for(var i in time){
				if(time[i] < 10) time[i] = '0'+time[i];
			}
			$('.troll-popup .timer-in-popup, .info-block-with-timer').find('span[data-time=minute]').text(time['m']);
			$('.troll-popup .timer-in-popup, .info-block-with-timer').find('span[data-time=seconds]').text(time['s']);
		}
	}

	function startTimer(login) {
		TimerInterval = setInterval(function () {
			var time = {'m':0, 's':0};
			time['m'] = parseInt($('.info-block-with-timer span[data-time=minute]').text());
			time['s'] = parseInt($('.info-block-with-timer span[data-time=seconds]').text());

			if(time['s'] == 0){
				time['m']--;
				time['s'] = 59;
			}else{
				time['s']--;
			}

			if( (time['m']<=0) && (time['s'] <= 0) ){
				clearInterval(TimerInterval);
				if($('#selecthandCardsPopup').hasClass('show')){
					userChangeCards();
				}else{
					if(login == $('.user-describer').attr('id')){
						conn.send(
							JSON.stringify({
								action: 'userPassed',
								ident: ident,
								timing: 0,
								user: $('.convert-battle-front>.user').attr('id')
							})
						);
					}
				}
			}
			for(var i in time){
				if(time[i] < 10) time[i] = '0'+time[i];
			}

			$('.troll-popup .timer-in-popup, .info-block-with-timer').find('span[data-time=minute]').text(time['m']);
			$('.troll-popup .timer-in-popup, .info-block-with-timer').find('span[data-time=seconds]').text(time['s']);
		}, 1000);
	}

	//PHP style time
	function phpTime(){return Math.floor(Date.now()/ 1000);}
	// /PHP style time

	//Смена идентификатора хода пользователя
	function changeTurnIndicator(login) {
		if(login == $('.user-describer').attr('id')){
			$('.user-turn-wrap .turn-indicator').addClass('active');
		}else{
			$('.user-turn-wrap .turn-indicator').removeClass('active');
		}
	}
	// /Смена идентификатора хода пользователя

// /Timer Functions

//Preloader on connect
function showPreloader() {
	$('.afterloader').css({'opacity':'1', 'z-index':'2222'});
}

function hidePreloader() {
	$('.afterloader').css({'opacity':'0', 'z-index':'-1'});
}
// /Preloader on connect

//Start card select popup
function radioPseudo() {
	$(document).on('click', '.popup-content-wrap .switch-user-turn-wrap label', function () {
		if($(this).find('input').prop('checked')){
			$('.popup-content-wrap .switch-user-turn-wrap .pseudo-radio').removeClass('active');
			$(this).find('.pseudo-radio').addClass('active');
		}
	});
	//If cursed choose first turn
	$(document).on('click', '#chooseUser label', function () {
		if($(this).find('input').prop('checked')){
			$('#chooseUser .pseudo-radio').removeClass('active');
			$(this).find('.pseudo-radio').addClass('active');
		}
	});
}
// /Start card select popup

//Popup
function openTrollPopup(popup) {
	popup.addClass('show');
	$('.new-popups-block').addClass('show');
}

//открыть попап (даже если уже открыт еще однин)
function openSecondTrollPopup(id, customClass) {
	id.addClass('show troll-popup-custom');
	if(customClass != null){
		id.addClass(customClass);
	}
	$('.new-popups-block').addClass('show-second');
}

function resultPopupShow(message) {
	$('#successEvent').find('.result').text(message);
	openTrollPopup($('#successEvent'));
}

function clickCloseCross() {
	$('.close-this').click(function(e){
		e.preventDefault();
		$(this).closest('div.troll-popup').removeClass('show');
		if($('div.troll-popup.show').length<=0){closeAllTrollPopup();}
	});
}

// закрыть попап по id
function closeSecondTrollPopup(id, customClass) {
	id.removeClass('show troll-popup-custom');
	if(customClass != null){
		id.removeClass(customClass);
	}
	$('.new-popups-block').removeClass('show-second');
}

function closeAllTrollPopup() {
	$('div.troll-popup').removeClass('show');
	$('.new-popups-block').removeClass('show');
}

function secondTrollPopupCustomImgAndTitle(text, imgSrc) {
	var holder = $('#card-start-step');
	holder.find('.content-card-info').empty().append('<div class="custom-img-and-title-wrap"><div class="custom-title"><span>'+text+'</span></div><div class="custom-img"><img src="'+imgSrc+'" alt=""></div></div>');

	openSecondTrollPopup(holder,'custom-img-and-title');
	setTimeout(function(){
		closeSecondTrollPopup(holder);
	},2000);
}
// /Popup

//Fix card margin-right
function calculateRightMarginCardHands() {
	calculate($('#sortableUserCards'));
	calculate($('#sortable-cards-field-more'));
	//calculate($('.cards-row-wrap'));
}
function calculate(obj){
	var count = obj.find('li').length + 1;
	var itemW = obj.find('li').width();
	var container = obj.width();
	var rightMargin = ((itemW * count) - container)/count;
	if(container < (itemW * count)){
		obj.find('li').css('margin-right','-'+rightMargin+'px');
	}
}
// /Fix card margin-right

//View player's deck
function viewPlayerDeck(){
	$('ul.deck-cards-list').jScrollPane();
	var api = $('ul.deck-cards-list').data('jsp');
	var throttleTimeout;
	$(window).bind('resize', function(){
		if (!throttleTimeout) {
			throttleTimeout = setTimeout(function(){
				api.reinitialise();
				throttleTimeout = null;
			}, 50);
		}
	});
	$(document).on('click', '#card-give-more-user li[data-field=deck] .card-my-init.cards-take-more', function(){
		openTrollPopup($('#allies-deck'));
	});
	$(document).on('click', '#card-give-more-user li[data-field=discard] .card-my-init.cards-take-more', function(){
		openTrollPopup($('#allies-discard'));
	});
	$(document).on('click', '#card-give-more-oponent li[data-field=discard] .card-init', function(){
		openTrollPopup($('#enemy-discard'));
	});
}
// /View player's deck

//Счетчики колод
function setDecksValues(counts, images){
	for(var player in counts){
		for(var deck in counts[player]){
			var user = $('.convert-right-info div[data-player='+player+']').attr('id');
			switch(deck){
				case 'deck':
				case 'discard':
					var initClass = ($('.user-describer').attr('data-player') == player)? 'card-my-init cards-take-more': 'card-init';
					if(parseInt(counts[player][deck]) > 0){
						if($('.convert-left-info div[data-type='+player+'] li[data-field='+deck+'] .counter').length > 0){
							$('.convert-left-info div[data-type='+player+'] li[data-field='+deck+'] .counter').text(counts[player][deck])
						}else{
							$('.convert-left-info div[data-type='+player+'] li[data-field='+deck+']').empty().append('' +
								'<div class="'+initClass+'" style="background-image: url(/img/fractions_images/'+images[user]['back']+') !important;">'+
									'<div class="card-otboy-counter deck">'+
									'<div class="counter">'+counts[player][deck]+'</div>'+
								'</div>'+
							'</div>');
						}
					}else{
						$('.convert-left-info div[data-type='+player+'] li[data-field='+deck+']').empty().append('<div class="nothinh-for-swap"></div>');
					}
					break;

				case 'hand':
					$('.convert-right-info div[data-player='+player+'] .pover-greencard .greencard-num').text(counts[player][deck])
				break;
			}
		}
	}
}
// /Счетчики колод

//Формирование стола по пользовательским данным
	function buildRoomPreview(userData) {
		$('#selecthandCardsPopup #handCards').empty();//очищение списков поп-апа выбора карт

		//Отображаем данные пользователей
		for(var key in userData){
			if(key != $('.user-describer').attr('id')){
				$('#selecthandCardsPopup .opponent-fraction span').text(userData[key]['deck_title']);
				$('#selecthandCardsPopup .opponent-description span').text(userData[key]['deck_descr']);
				window.userImgData['opponent'] = userData[key]['deck_img'];
			} else {
				window.userImgData['user'] = userData[key]['deck_img'];
			}

			if($('.convert-right-info #'+key).length < 1){
				$('.convert-right-info .oponent-describer').attr('id',key);//Установить никнейм оппонета в правом сайдбаре
				$('.rounds-counts.oponent .rounds-counts-title').text(key);

				$('.field-battle .cards-bet #card-give-more-oponent').attr('data-user', key);//Установить никнейм оппонента в отображение колоды

				$('.convert-battle-front .oponent').attr('data-user', key);//Установить логин оппонента в его поле битвы
			}

			createUserDescriber(key, userData[key]['img_url'], userData[key]['deck_title']);//Создать описание пользователей

			$('.convert-left-info .cards-bet ul[data-user='+key+'] .deck .counter').text(userData[key]['deck_count']);//Количество карт в колоде
			//Если у пользователя есть магические эффекты
			if(userData[key]['magic'].length > 0){
				//Вывод текущей магии пользователей
				$('.convert-right-info #' + key + ' .useless-card').children().children('.magic-effects-wrap').empty();
				createUserMagicFieldCards(key, userData[key]['magic']);
			}

			//Если пользователь не готов (не выбраны карты для игры)
			if( 0 == parseInt(userData[key]['ready'])){
				if (userData[key]['hand'].length > 0) {
					//Вывод карт руки и колоды
					$('#selecthandCardsPopup h5 span').text(userData[key]['can_change_cards']);
					for(var i=0; i<userData[key]['hand'].length; i++){
						$('#selecthandCardsPopup #handCards').append(createFieldCardView(userData[key]['hand'][i], userData[key]['hand'][i]['strength'], true));
					}
					//Изменение ширины карт при выборе Карт "Руки"
					hidePreloader();
					openTrollPopup($('#selecthandCardsPopup'));
					if(userData[key]['current_deck'] == 'cursed'){
						var logins = '';
						for(var login in userData){
							logins += '<label><input type="radio" name="userTurn" value="'+login+'"><div class="pseudo-radio"></div>'+login+'</label>';
						}
						if(!($('#selecthandCardsPopup .for_cursed .switch-user-turn-wrap').length >0)){
							$('#selecthandCardsPopup .for_cursed').append('<div class="switch-user-turn-wrap">Выберете, кому отдать первый ход: <div>'+logins+'</div></div>');
						}
					}
					//Пользователь поменял карты
					userChangeDeck(userData[key]['can_change_cards']);
				}
			}
		}
	}

	function createUserDescriber(userLogin, user_img, userRace) {
		if(user_img !== ''){
			$('.convert-right-info #'+userLogin+' .stash-about .image-oponent-ork').css({'background':'url(/img/user_images/'+user_img+') 50% 50% no-repeat'});
		}
		$('.convert-right-info #'+userLogin+' .stash-about .naming-oponent .name').text(userLogin);
		$('.convert-right-info #'+userLogin+' .stash-about .naming-oponent .rasa').text(userRace);
	}

	function createUserMagicFieldCards(userLogin, magicData) {
		for ( var i=0; i<magicData.length; i++ ) {
			$('.convert-right-info #' + userLogin ).find('.magic-effects-wrap').append(createMagicEffectView(magicData[i]));
		}
	}

	//Созднаие Отображения маг. еффекта
	function createMagicEffectView(magicData) {
		return '<li data-cardid="' + magicData['id'] + '">' +
			'<img src="/img/card_images/' + magicData['img_url']+'" alt="' + magicData['slug'] +'" title="' + magicData['title'] +'">'+
			'<div class="magic-description">'+ magicData['description']+'</div>'+
			'<div class="info-img"><img class="ignore" src="/images/info-icon.png" alt=""><span class="card-action-description">Инфо о магии</span></div>'+
		'</li>';
	}
	// /Созднаие Отображения маг. еффекта

	//Создание отображения карты в списке
	function createFieldCardView(cardData, strength) {
		var immune = '0';
		var full_immune = '0';
		cardData.actions.forEach(function(item) {
			if ( item.hasOwnProperty('immumity_type') ) {
				if( item.immunity_type == "1" ){
					full_immune = '1';
				}else{
					immune = '1';
				}
			}
		});
		//console.log('cardData when building card markup: ', cardData);
		return '<li class="content-card-item disable-select loading animation" data-cardid="'+cardData['id']+'" data-slug="'+cardData['caption']+'" data-immune="'+immune+'" data-full-immune="'+full_immune+'" data-relative="'+cardData['fraction']+'">'+
			createCardDescriptionView(cardData, strength)+
		'</li>';
	}
	// /Создание отображения карты в списке

	//Создание отображения колоды
	function createDeckCardPreview(count, is_user, deck) {
		var divClass = (is_user) ? 'card-my-init cards-take-more' : 'card-init';
		var deckBG = (is_user) ? 'user' : 'opponent';
		var deckBG = 'style="background-image: url(/img/fractions_images/'+window.userImgData[deckBG]+') !important"';
		var cardList = '';
		if(typeof deck != "undefined"){
			for(var i=0; i<deck.length; i++){
				cardList += createFieldCardView(deck[i], deck[i]['strength']);
			}
		}else{
			cardList += '<div class="'+divClass+'" '+deckBG+'><div class="card-otboy-counter deck">'+count+'</div></div>';
		}
		return cardList;
	}
	// /Создание отображения колоды

	//Создание отображения карты
	function createCardDescriptionView(cardData, strength) {
		var hasImmune = 0;
		var hasFullImmune = 0;
		for(var i in cardData['actions']){
			if(cardData['actions'][i]['caption'] == 'immune'){
				hasImmune = 1;
				hasFullImmune = cardData['actions'][i]['immumity_type'];
			}
		}

		var race_class = '';
		var special_class = '';

		switch(cardData['fraction']){
			case 'knight':		race_class = ' knight-race'; break;
			case 'highlander':	race_class = ' highlander-race'; break;
			case 'monsters':	race_class = ' monsters-race'; break;
			case 'undead':		race_class = ' undead-race'; break;
			case 'cursed':		race_class = ' cursed-race'; break;
			case 'forest':		race_class = ' forest-race'; break;
			case 'neutrall':	race_class = ' neutrall-race'; break;
			case 'special':		special_class = ' special-type'; break;
		}

		var allowed_row_images = '';
		if(cardData['fraction'] != 'special'){
			for(var i in cardData['allowed_row_images']){
				allowed_row_images += '<img src="'+cardData['allowed_row_images'][i]['image']+'" alt="">' +
				'<span class="card-action-description">'+cardData['allowed_row_images'][i]['title']+'</span>';
			}
		}

		var leader_class = (cardData['is_leader'] == 1 )? ' leader-type': '';
		var leader_tag = (cardData['is_leader'] == 1)? '<div class="leader-flag"><span class="card-action-description">Карта Лидера</span></div>': '';

		var action_images = '';
		if(cardData['action_images']){
			for(var i in cardData['action_images']){
				action_images += '<span class="card-action">'+
					'<img src="'+cardData['action_images'][i]['img']+'" alt="">'+
					'<span class="card-action-description">'+cardData['action_images'][i]['title']+'</span>'+
				'</span>';
			}
		}

		if(strength.length < 1){
			strength = cardData['strength'];
		}
		var cartStrengthTag = (race_class != '')
			? '<div class="label-power-card">'+
				'<span class="label-power-card-wrap">'+
					'<span class="buff-debuff-value"></span>'+
					'<span class="card-current-value">'+strength+'</span>'+
				'</span>'+
				'<span class="card-action-description">Сила карты</span>'+
			'</div>'
			: '';

		var result = '<div class="content-card-item-main'+special_class+leader_class+race_class+'" style="background-image: url(/img/card_images/'+cardData['img_url']+')" data-leader="'+cardData['is_leader']+'">'+
			'<div class="card-load-info card-popup">'+
				'<div class="info-img">'+
					'<img class="ignore" src="/images/info-icon.png" alt="">'+
					'<span class="card-action-description">Инфо о карте</span>'+
				'</div>'+
				leader_tag+
				cartStrengthTag+
				'<div class="hovered-items">'+
					'<div class="card-game-status">'+
						'<div class="card-game-status-role">'+allowed_row_images+'</div>'+
						'<div class="card-game-status-wrap">'+action_images+'</div>'+
					'</div>'+
					'<div class="card-name-property">'+
						'<p>'+cardData['title']+'</p>'+
					'</div>'+
				'</div>'+
			'</div>'+
		'</div>';
		return result;
	}
	//Создание отображения карты

	function userChangeDeck(can_change_cards) {
		//Смена карт при старте игры
		$(document).on('click', '#handCards li .content-card-item-main', function(event){
			if((!$(event.target).hasClass('ignore')) && event.which==1){
				if(parseInt($('#selecthandCardsPopup .popup-content-wrap h5 span').text()) > 0){
					var button =$(document.createElement('div'));
					button.addClass('change-card').html('<div class="change-card-start"><b>Сменить</b></div>');

					if($(this).hasClass('disactive')){
						$(this).removeClass('disactive');
						$(this).closest('li').find('.change-card').remove();

					}else{
						if($('#handCards li.disactive').length < can_change_cards){
							$(this).addClass('disactive').closest('li').append(button);
						}
					}
				}else{return;}
			}
		});

		if(parseInt($('#selecthandCardsPopup .popup-content-wrap h5 span').text()) > 0){
			userWantsChangeCard();
		}

		//Пользователь Выбрал карты и нажал "ОК"
		$('#selecthandCardsPopup .acceptHandDeck').click(function(e){
			e.preventDefault();
			userChangeCards();
			clearInterval(TimerInterval);
		});
	}

	function userWantsChangeCard() {
		$(document).on('click', '#selecthandCardsPopup #handCards .change-card', function(){
			showPreloader();
			var card = $(this).parent().attr('data-cardid');
			$(this).addClass('clicked');
			conn.send(
				JSON.stringify({
					action: 'changeCardInHand',
					ident: ident,
					card: card,
				})
			);
		});
	}

	function userChangeCards() {
		showPreloader();
		var token = $('.market-buy-popup input[name=_token]').val().trim();
		var turn = '';
		if($('#selecthandCardsPopup input[name=userTurn]').length > 0){
			turn = (typeof $('#selecthandCardsPopup input[name=userTurn]:checked').val() == "undefined")? $('.convert-right-info .user-describer').attr('id'): $('#selecthandCardsPopup input[name=userTurn]:checked').val();
		}
		var time = parseInt($('#selecthandCardsPopup .timer-in-popup span[data-time=minute]').text()) * 60 + parseInt($('#selecthandCardsPopup .timer-in-popup span[data-time=seconds]').text());
		$.ajax({
			url:	'/game_user_change_cards',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN':token},
			data:	{time:time},
			success:function(data){
				data = JSON.parse(data);

				var player = $('.user-describer').attr('id');

				$('#allies-deck .jspPane').empty().append(createDeckCardPreview(data[player]['deck'].length, true, data[player]['deck']));
				$('.user-card-stash #sortableUserCards').empty();
				for(var i=0; i< data[player]['hand'].length; i++){
					$('.user-card-stash #sortableUserCards').append(createFieldCardView(data[player]['hand'][i], data[player]['hand'][i]['strength']));
				}
				conn.send(
					JSON.stringify({
						action: 'userReady',
						ident: ident,
						turn: turn
					})
				);
				console.log('user send Ready');
				closeAllTrollPopup();
				hidePreloader();
				calculateRightMarginCardHands();
			},
			complete:function () {
				animateHandCard();
			}
		});
	}
// /Формирование стола по пользовательским данным

//Разрешить пользователю сделать ход
function cardCase(cardSource, allowToAction) {
	hidePreloader();
	$('#sortableUserCards li').unbind();

	var source = '';
	for(var player in cardSource) source = cardSource[player];

	if( (source == 'hand') && (allowToAction) ){
		$('#sortableUserCards li').click(function(event){
			if((!$(event.target).hasClass('ignore')) && event.which==1){
				$('.user-describer .magic-effects-wrap li').removeClass('active');
				if($(this).hasClass('active')){
					clearRowSelection();
				}else{
					$(this).parents('ul').children('li').removeClass('active');
					$(this).addClass('active');
				}
				if($(this).hasClass('active')){
					getCardActiveRow($(this).attr('data-cardid'), 'card', conn, ident);
				}
			}
		});

		$('.user-describer .magic-effects-wrap li').unbind();
		$('.user-describer .magic-effects-wrap li:not(.disactive)').click(function(event){
			if((!$(event.target).hasClass('ignore')) && event.which==1){
				$('#sortableUserCards li').removeClass('active');
				if($(this).hasClass('active')){
					clearRowSelection();
				}else{
					$(this).parents('ul').children('li').removeClass('active');
					$(this).addClass('active');
				}
				if($(this).hasClass('active')){
					getCardActiveRow($(this).attr('data-cardid'), 'magic', conn, ident);
				}
			}
		});
	}
	calculateRightMarginCardHands();
}

	//Отмена подсветки ряда действий карты
	function clearRowSelection(){
		$('.mezhdyblock .bor-beutifull-box #sortable-cards-field-more').removeClass('active');
		$('.convert-stuff .field-for-cards').each(function() {
			$(this).removeClass('active can-debuff');
			$(this).children('.fields-for-cards-wrap').children('.cards-row-wrap').children('li').removeClass('glow');
		});
	}
	// /Отмена подсветки ряда действий карты

	//Получить активные поля и действия карты
	function getCardActiveRow(card, type, conn, ident){
		conn.send(
			JSON.stringify({
				action: 'getActiveRow',
				ident: ident,
				type: type,
				card: card
			})
		);
	}
	// /Получить активные поля и действия карты

	//Отображение активных полей действия карты или магии
	function showCardActiveRow(result) {
		clearRowSelection();

		if(result.type == 'card'){
			if(result.fraction == 'special'){
				for(var i in result.actions){
					switch(result.actions[i]['caption']){
						case 'killer':	//Убийца
						case 'obscure':	//Одурманивание
						case 'sorrow':	//Печаль
							illuminateOpponent();
							break;

						case 'call':	//Призыв
						case 'cure':	//Исцеление
						case 'inspiration'://Воодушевление
						case 'heal':	//Лекарь
							illuminateCustom({parent: '.user', row: result.rows});
							break;

						case 'terrify':	//Страшный
							illuminateAside(); //Подсветить среднее поле
							var actionObj = result.actions[i];
							var fieldDebuff = actionObj.fear_ActionRow;
							var debuffTeameate = actionObj.fear_actionTeamate;
							var params = {
								debuff: true,
								debuffRow: fieldDebuff,
								debuffTeameate: debuffTeameate
							};
							illuminateCustom(params); // подсветить поля дебафа
						break;

						case 'regroup':	//Перегруппировка
							illuminateSelf();//Подсветить свое поле
						break;
					}
				}
			}else{
				//Если есть у карты особые действия
				if(result.actions.length > 0){
					for(var i in result.actions){
						var params = {};
						var parent = '.user';
						switch(result.actions[i]['caption']){
							case 'spy':		//Действие "Шпион"/"Разведчик"
								var parent = (result.actions[i]['spy_fieldChoise'] == '0' )? '.user': '.oponent';
							break;
							case 'terrify':	//Страшный
								params['debuff'] = true;
								params['debuffRow'] = result.actions[i].fear_ActionRow;
							break;
						}
					}
					params['parent'] = parent;
					params['row'] = result.rows;
					illuminateCustom(params);//Подсветить поля указанные в действии карты с учетом поля spy_fieldChoise
				}else{
					illuminateCustom({parent: '.user', row: result.rows});//Подсветить поля указанные в действии карты
				}
			}
		}else{
			//Magic Effect
			for(var i in result.actions){
				switch(result.actions[i]['caption']){
					case 'sorrow':
						illuminateOpponent();
					break;
					default:
						illuminateOpponent();
						illuminateSelf();
				}
			}
		}
	}
	// /Отображение активных полей действия карты или магии

	//Подсветка рядов действия карты
		//Средний блок
		function illuminateAside(){$('.mezhdyblock .bor-beutifull-box #sortable-cards-field-more').addClass('active');}
		//Поле оппонента
		function illuminateOpponent(){$('.oponent .convert-stuff .field-for-cards').addClass('active');}
		//Свое поле
		function illuminateSelf(){$('.user .convert-stuff .field-for-cards').addClass('active');}
		//Поле действия карты по-умолчанию
		function illuminateCustom(params) {
			var options = {};
			$.extend( options, params );
			if(options.hasOwnProperty('parent')){
				for ( var i = 0; i < options.row.length; i++ ) {
					var field = intRowToField(options.row[i]);
					$('.convert-battle-front ' + options.parent + ' .convert-one-field ' + field).addClass('active');
				}
			}
			if(options.hasOwnProperty('debuff')){
				options.debuffRow.forEach(function(item) {
					var field = intRowToField(item);
					$('.convert-battle-front .oponent .convert-one-field ' + field).addClass('can-debuff');
					if(options.debuffTeameate == 1){
						$('.convert-battle-front .user .convert-one-field ' + field).addClass('can-debuff');
					}
				});
			}
		}
	// /Подсветка рядов действия карты

	//Перевод значения названия поля в id ряда
	function intRowToField(row) {
		var field;
		switch(row.toString()) {
			case '0': field = '#meele'; break;
			case '1': field = '#range'; break;
			case '2': field = '#superRange'; break;
			case '3': field = '#sortable-cards-field-more'; break;
		}
		return field;
	}
	// /Перевод значения названия поля в id ряда
// /Разрешить пользователю сделать ход

//Функция проведения действия картой / МЭ / Пас
function userMakeAction(conn, cardSource, allowToAction) {
	$('.convert-battle-front .convert-stuff, .mezhdyblock .bor-beutifull-box').unbind();
	if(allowToAction){
		$('.convert-battle-front .convert-stuff, .mezhdyblock .bor-beutifull-box').on('click', '.active', function(){
			clearInterval(TimerInterval);
			var time = parseInt($('.info-block-with-timer span[data-time=minute]').text()) * 60 + parseInt($('.info-block-with-timer span[data-time=seconds]').text());
			if ( $('.summonCardPopup').hasClass('show') ) {
				var card = $('#summonWrap li').attr('data-cardid');
				$('.summonCardPopup').removeClass('show');
			} else {
				var card = $('#sortableUserCards li.active').attr('data-cardid');
			}

			var magic = $('.user-describer .magic-effects-wrap .active').attr('data-cardid');
			var BFData = {
				row: $(this).attr('id'),
				field: $(this).parents('.convert-cards').attr('id')
			};
			if(typeof magic != "undefined"){
				card = '';
			}else{
				magic = '';
			}

			if(allowToAction){
				conn.send(
					JSON.stringify({
						action: 'userMadeAction',
						ident: ident,
						card: card,
						magic: magic,
						BFData: BFData,
						source: cardSource,
						timing: time
					})
				);
				allowToAction = false;
			}

		});
		//Пользователь нажал "Пас"
		$('.buttons-block-play button[name=userPassed]').unbind();
		$('.buttons-block-play button[name=userPassed]').click(function(){
			if(allowToAction){
				clearInterval(TimerInterval);
				var time = parseInt($('.info-block-with-timer span[data-time=minute]').text()) * 60 + parseInt($('.info-block-with-timer span[data-time=seconds]').text());
				conn.send(
					JSON.stringify({
						action: 'userPassed',
						ident: ident,
						timing: time,
						user: $('.convert-battle-front>.user').attr('id')
					})
				);
				allowToAction = false;
			}
		});
	}
}
// /Функция проведения действия картой / МЭ / Пас

function fieldBuild(stepStatus, addingAnim){
	//убрать карту из руки
	if(stepStatus.played_card['move_to']['user'].length > 0){
		$('#sortableUserCards .active').remove();
		$('#sortableUserCards li').removeClass('active');
		var card = stepStatus.played_card;
		var rowId = intRowToField(card['move_to']['row']);
		if(card['move_to']['row'] != 3) {
			if(card['card']['fraction'] == 'special'){
				if(card['self_drop'] == 0){
					$('.convert-battle-front #'+card['move_to']['player']+'.convert-cards '+rowId+' .image-inside-line').empty().append(createFieldCardView(card['card'], card['strength']));
				}
			}else{
				$('.convert-battle-front #'+card['move_to']['player']+'.convert-cards '+rowId+' .cards-row-wrap').append(createFieldCardView(card['card'], card['strength']));
			}
		}else{
			$('.mezhdyblock '+rowId).append(createFieldCardView(card['card'], card['strength']));
		}
	}

	//Добавление карт
	if(!$.isEmptyObject(stepStatus.added_cards)){
		var player = $('.user-describer').attr('data-player');
		if(!$.isEmptyObject(stepStatus.added_cards[player])){

			for(var destination in stepStatus.added_cards[player]){
				switch(destination){
					case 'hand'://SPY action
						for(var i in stepStatus.added_cards[player]['hand']){
							$('.user-card-stash #sortableUserCards').append(createFieldCardView(stepStatus.added_cards[player]['hand'][i], stepStatus.added_cards[player]['hand'][i]['strength']));
							if(addingAnim){
								$('.user-card-stash #sortableUserCards li').last().addClass('added-by-effect waiting-for-animation');
							}
						}
						sortCards();
					break;

					case 'deck':
					case 'discard':
						for(var i in stepStatus.added_cards[player][destination]){
							var card = stepStatus.added_cards[player][destination][i];
							var type = ($('.convert-right-info .user-describer').attr('data-player') == player)? 'allies': 'enemy';
							//Add cards to deck popup window in game_header
							if($('#'+type+'-'+destination).length > 0){
								$('#'+type+'-'+destination+' .deck-cards-list .jspPane').append(createFieldCardView(card, card.strength));
							}
						}
					break;
					default:
						//Отыгрыш пришедшик карт в поле
				}
			}
		}
		/*var player = $('.convert-cards[data-user='+$('.user-describer').attr('id')+']').attr('id');
		if(typeof stepStatus.added_cards[player] != "undefined"){
			//Дополнительные карты
			for(var row in stepStatus.added_cards[player]){
				if(row != 'hand') {
					//убирание карт из руки
					var row_data = stepStatus.added_cards[player][row];
					for(var i in row_data){
						if(row_data[i]['destination'] == 'hand'){
							$('.user-card-stash #sortableUserCards li').each(function(){
								if($(this).attr('data-cardid') == row_data[i]['card']['id']){
									$(this).remove();
								}
							});
						}
					}
				}
			}
		}
		for(var player in stepStatus.added_cards){
			for(var row in stepStatus.added_cards[player]){
				if(row != 'hand'){
					for(var i in stepStatus.added_cards[player][row]){
						var rowId = intRowToField(row);
						var card = stepStatus.added_cards[player][row][i];
						$('.convert-battle-front #'+player+'.convert-cards '+rowId+' .cards-row-wrap').append(createFieldCardView(card['card'], card['strength']));
					}
				}
			}
		}*/
	}

	//удаление карт
	if(!$.isEmptyObject(stepStatus.dropped_cards)){
		for(var player in stepStatus.dropped_cards){
			for(var row in stepStatus.dropped_cards[player]){
				var type = ($('.convert-right-info .user-describer').attr('data-player') == player)? 'allies': 'enemy';
				switch(row){
					case 'deck':
					case 'discard':
						for(var i in stepStatus.dropped_cards[player][row]){
							var card = stepStatus.dropped_cards[player][row][i];
							console.log(card)
							//$('#'+type+'-'+row+' ul.deck-cards-list li[data-slug='+card+']:first').remove();
						}
					break;
					case 'hand':
						// удаление карты с руки противника
						if($('.convert-right-info .user-describer').attr('data-player') == player){
							for(var i in stepStatus.dropped_cards[player][row]){
								animationCardReturnToOutage(
									$('.user-card-stash #sortableUserCards li[data-slug="'+card+'"]:first'), 1500,
									function() {
										var timeout = (100 * ($('.user-card-stash #sortableUserCards li[data-slug="'+card+'"]:first').length - 1)) + 1500;
										setTimeout(function() {
											$('.user-card-stash #sortableUserCards li[data-slug="'+card+'"]:first').remove();
										}, timeout);
									}
								);
							}
						}
					break;
					case 'mid':
						$('.mezhdyblock #sortable-cards-field-more').children().fadeOut(500, function() {
							$('.mezhdyblock #sortable-cards-field-more').empty();
						});
					break;
					default:
						var rowId = intRowToField(row);
						for(var cardType in stepStatus.dropped_cards[player][row]){
							if(cardType == 'special'){
								console.log(stepStatus.dropped_cards[player][row][cardType]);
								animationDeleteSpecialCard(player,rowId);
							}else{
								for(var position in stepStatus.dropped_cards[player][row][cardType]){
									var card = stepStatus.dropped_cards[player][row][cardType][position];

									// Узнаю какие карты нужно удалить и даю им класс ready-to-die
									var currentCardDelete = $('.convert-battle-front #'+player+'.convert-cards '+rowId+' .cards-row-wrap li[data-slug="'+card+'"]:not(.ready-to-die)').first();
									currentCardDelete.addClass('ready-to-die');
									checkIfNeedRemoveBuffOnRow(player, row, stepStatus, 'support');
								}
							}
						}
				}
			}
		}
	}

	recalculateBattleStrength();
}

function sortCards(){
	var arrayToSort = {
		special: [],
		other: []
	};
	$('#sortableUserCards li').each(function(){
		if($(this).attr('data-relative') == 'special'){
			arrayToSort.special.push( $(this) );
		}else{
			var temp = {
				card: $(this),
				title: $(this).find('.card-name-property p').text(),
				strength: parseInt( $(this).find('.label-power-card-wrap .card-current-value').text() )
			};
			arrayToSort.other.push(temp);
		}
	});
	arrayToSort.other.sort(function(a, b){
		//return b.strength >= a.strength;
		var r = (b['strength'] - a['strength']);
		if(r !== 0) return r;
		return a['title'].localeCompare(b['title']);
	});

	$('#sortableUserCards').empty();

	for(var i in arrayToSort.other){
		$('#sortableUserCards').append(arrayToSort.other[i].card);
	}
	for(var i in arrayToSort.special){
		$('#sortableUserCards').append(arrayToSort.special[i]);
	}
	calculateRightMarginCardHands();

	if($('.added-by-effect').length){
		cardMovingFromTo( 'user', 'deck', $('.added-by-effect').length);
	}
}

function checkIfNeedRemoveBuffOnRow(player, row, stepStatus, buffName){
	console.log(stepStatus.actions);
	//Проверять массив пришедших и ушедших действий

	/*var buffMass = field_status[player][row]['buffs'];
	if ($.inArray(buffName, buffMass) == -1) {
		var currentRow = $('.convert-battle-front #'+player+'.convert-cards '+intRowToField(row)).closest('.convert-stuff');
		if (currentRow.hasClass(buffName+'-buff-wrap')){
			currentRow.removeClass(buffName+'-buff-wrap');
			currentRow.find('.'+buffName+'-buff').remove();

			//Если поля емиеет "Воодушевление" - то не удалять подсветку бафа для значения поля(большая цыфра)
			if (!currentRow.hasClass('inspiration-buff-wrap')) {
				currentRow.removeClass('buff');
			}
		}
	}*/
}

var processingRecalculateBattleStrength = true;
function recalculateBattleStrength(){
	if (processingRecalculateBattleStrength) {
		processingRecalculateBattleStrength = false;
		var fieldData = {
			p1:{
				meele: 0,
				range: 0,
				superRange: 0,
				total: 0
			},
			p2:{
				meele: 0,
				range: 0,
				superRange: 0,
				total: 0
			}
		};
		$('.convert-battle-front .convert-stuff .field-for-sum').text('0');
		$('.convert-right-info .power-text').text('0');
		$(document).find('.convert-battle-front .convert-cards').each(function(){
			var player = $(this).attr('id');
			var total = 0;
			$(this).find('.field-for-cards').each(function(){
				var row = $(this).attr('id');
				$(this).find('ul.cards-row-wrap li').each(function(){
					var strength = parseInt($(this).find('.card-current-value').text());
					fieldData[player][row] += strength;
					total += strength;
				});
			});
			fieldData[player]['total'] = total;
		});

		for(var player in fieldData){
			for(var field in fieldData[player]){
				if(field != 'total'){
					var pointsSum = $('#'+player+'.convert-cards #'+field).closest('.convert-stuff').find('.field-for-sum');

					// console.info("pointsSum.text()", pointsSum.text())
					// console.info("fieldData[player][field]", fieldData[player][field])
					// if (!pointsSum.hasClass('pulsed') && parseInt(pointsSum.text()) !== fieldData[player][field]) {
					// 	pulsingAdd(pointsSum)
					// }
					pointsSum.text(fieldData[player][field]);



				}else{
					$('.convert-right-info div[data-player='+player+'] .power-text').text(fieldData[player][field]);
				}
			}
		}
		processingRecalculateBattleStrength = true;

		function pulsingAdd(holder) {
			setTimeout(function() {
				holder.addClass('pulsed');
				setTimeout(function() {
					holder.removeClass('pulsed');
				}, 500);
			}, 0);
		}
	}
}

function circleRoundIndicator() {
	var opon = parseInt($('.rounds-counts.oponent .rounds-counts-count').text());
	var user = parseInt($('.rounds-counts.user .rounds-counts-count').text());
	if(user > 0){$('#svg #bar-oponent').css('stroke-dashoffset', '205px');}else{$('#svg #bar-oponent').css('stroke-dashoffset', '0');}
	if(opon > 0){$('#svg #bar-user').css('stroke-dashoffset', '205px');}else{$('#svg #bar-user').css('stroke-dashoffset', '0');}
}

function buildBattleField(added, dropped){
	for(var player in added){
		for(var deck in added[player]){
			for(var i in added[player][deck]){
				var card = added[player][deck][i];
				var type = ($('.convert-right-info .user-describer').attr('data-player') == player)? 'allies': 'enemy';
				//Add cards to deck popup window in game_header
				if($('#'+type+'-'+deck).length > 0){
					$('#'+type+'-'+deck+' .deck-cards-list .jspPane').append(createFieldCardView(card, card.strength));
				}
			}
		}
	}
	for(var player in dropped){
		var type = ($('.convert-right-info .user-describer').attr('data-player') == player)? 'allies': 'enemy';
		for(var row in dropped[player]){
			switch(row){
				case 'deck':
				case 'discard':
					for(var i in dropped[player][row]){
						var card = dropped[player][row][i];
						if($('#'+type+'-'+row).length > 0){
							$('#'+type+'-'+row+' .deck-cards-list').find('li[data-slug='+card+']:first').remove();
						}
					}
				break;
				default:
					var rowInField = intRowToField(row);
					for(var i in dropped[player][row]['warrior']){
						var card = dropped[player][row]['warrior'][i];
						var cardObj = $('#'+player+'.convert-cards '+rowInField+' .fields-for-cards-wrap li[data-slug='+card+']:not(.delete-round-ends)').first();
						cardObj.addClass('delete-round-ends');
					}
					var cardDeleted = $('.fields-for-cards-wrap li.delete-round-ends');
					cardDeleted.removeClass('show');
					setTimeout(function(){
						cardDeleted.remove();
					},500);

					if(typeof dropped[player][row]['special'] != 'undefined'){
						if(dropped[player][row]['special'].length > 0){
							$('#'+player+'.convert-cards '+rowInField+' .image-inside-line').fadeOut(500,function(){
								$('#'+player+'.convert-cards '+rowInField+' .image-inside-line').empty();
							});
						}
					}
			}
		}
	}
}

function popupActivation(result){
	switch(result.round_status.activate_popup){
		//Задействовать popup выбора хода игрока
		case 'activate_turn_choise':
			if(result.round_status.current_player == $('.user-describer').attr('id')){
				$('#selectCurrentTurn #chooseUser').empty();
				var users = [$('.convert-right-info .user-describer').attr('id'), $('.convert-right-info .oponent-describer').attr('id')];
				for(var i in users){
					$('#selectCurrentTurn #chooseUser').append('<label>' +
						'<input type="radio" name="usersTurn" value="'+users[i]+'">' +
						'<div class="pseudo-radio"></div> - '+users[i]+
						'</label>');
				}
				$('#selectCurrentTurn #chooseUser input[name=usersTurn]:first').prop('checked', true).next().addClass('active');
				openTrollPopup($('#selectCurrentTurn'));
				$('#selectCurrentTurn button').unbind();
				$('#selectCurrentTurn button').click(function(){
					clearInterval(TimerInterval);
					var time = parseInt($('.info-block-with-timer span[data-time=minute]').text()) * 60 + parseInt($('.info-block-with-timer span[data-time=seconds]').text());
					var userTurn = $('#selectCurrentTurn input[name=usersTurn]:checked').val();
					conn.send(
						JSON.stringify({
							action: 'cursedWantToChangeTurn',//Отправка сообщения о подключения пользователя к столу
							ident: ident,
							user: userTurn,
							time: time
						})
					);
					closeAllTrollPopup();
				});
			}
		break;
		case 'activate_choise':
			$('#selectNewCardsPopup .button-troll').hide(); //Скрыть все кнопки на в popup-окне
			$('#selectNewCardsPopup .button-troll.acceptNewCards').show(); //Показать кнопку "Готово" для выбора призваных карт

			$('#selectNewCardsPopup #handNewCards').empty();//Очистка списка карт popup-окна
			//если карт отыгрыша пришло больше 1й
			if(result.round_status.cards_to_play.length > 1){
				//Вывод карт в список в popup-окне

				var card_in_popup_count = 0;
				for(var i in result.round_status.cards_to_play){
					$('#selectNewCardsPopup #handNewCards').append(createFieldCardView(result.round_status.cards_to_play[i], result.round_status.cards_to_play[i]['strength']));
					card_in_popup_count++;
				}
				setMinWidthInPop(card_in_popup_count,$('#selectNewCardsPopup'));

				openTrollPopup($('#selectNewCardsPopup'));//Открытие popup-окна пользователю

				$('#selectNewCardsPopup #handNewCards li, #selectNewCardsPopup .button-troll.acceptNewCards').unbind();
				$('#selectNewCardsPopup #handNewCards li:first').addClass('glow');
				$('#selectNewCardsPopup #handNewCards li').click(function(event){
					if((!$(event.target).hasClass('ignore')) && event.which==1){
						$('#selectNewCardsPopup #handNewCards li').removeClass('glow');
						$(this).addClass('glow');
					}
				});

				incomeCardSelection(conn, ident, result.round_status.card_source); //Отслеживание нажатия кнопки "Готово"
			}else{//Если карта одна показываем её в боковом окне
				incomeOneCardSelection(result.round_status.cards_to_play[0]);
				getCardActiveRow(result.round_status.cards_to_play[0]['id'], 'card', conn, ident);//Подсветка ряда действия карты
			}
		break;
	}
}

function setMinWidthInPop(count,popup) {
	if (count>0){
		var holder = popup.find('.cards-select-wrap li');
		var card_in_poup_min_width = ( holder.width() * count ) + 300;//300 - magic count
		popup.css({
			'width':card_in_poup_min_width+'px'
		});
	}
}

//Функиции отправки выбраных карт для призыва на поле
function incomeOneCardSelection(card) {
	var content='<li class="content-card-item disable-select" data-cardid="'+card['id']+'" data-relative="'+card['fraction']+'" data-slug="'+card['caption']+'">'+
		createCardDescriptionView(card, card['strength'])+
		'</li>';
	$('.summonCardPopup').removeClass('show');
	$('#summonWrap').html(content);
	$('.summonCardPopup').addClass('show');
}

function incomeCardSelection(conn, ident, card_source) {
	$('#selectNewCardsPopup .button-troll.acceptNewCards').click(function(e) {
		e.preventDefault();
		if ( $('#selectNewCardsPopup #handNewCards .glow') ) {
			createPseudoCard( $('#selectNewCardsPopup #handNewCards .glow') );
		} else {
			return;
		}
	});

	function createPseudoCard(obj) {
		$('#summonWrap').empty();
		$('.summonCardPopup').removeClass('show');
		obj.clone().appendTo('#summonWrap');
		$('.summonCardPopup').addClass('show');
		closeAllTrollPopup();
		finalAction();
	}

	function finalAction() {
		cardCase(card_source,false);
		var card = $('#selectNewCardsPopup #handNewCards .glow').attr('data-cardid');
		for(var player in card_source){
			var source = card_source[player];
		}
		getCardActiveRow(card, 'card', conn, ident);
		conn.send(
			JSON.stringify({
				action: 'dropCard',
				ident: ident,
				card: card,
				player: player,
				deck: source
			})
		);

	}
}
// /Функиции отправки выбраных карт для призыва на поле

function processActions(result){

	if(!$.isEmptyObject(result.actions.appear)){

		if (typeof result.actions.appear === 'string'){
			switch(result.actions.appear){
				case 'cure':

					setTimeout(function(){
						setCardStrength(result.actions.cards_strength);
					},1000);

					break;
			}
		}

		for(var player in result.actions.appear){
			for(var row in result.actions.appear[player]){

				var actionRow = $('#'+player+'.convert-cards '+ intRowToField(row));

				for(var item in result.actions.appear[player][row]){

					var action = result.actions.appear[player][row][item];

					switch(action){
						case 'support'://Поддержка
							var obj = {};
								obj.field = actionRow
								obj.cardsMass = (!$.isEmptyObject(result.actions.cards)) ? result.actions.cards[player][row]: null;
								obj.effectName = 'support';
								obj.effectType = 'buff';

							animatePositiveNegativeEffects(obj);
						break;
						case 'brotherhood'://Боевое братство
							var obj = {};
								obj.field = actionRow;
								obj.cardsMass = (!$.isEmptyObject(result.actions.cards)) ? result.actions.cards[player][row]: null;
								obj.effectName = 'brotherhood';
								obj.effectType = 'buff';

							animatePositiveNegativeEffects(obj);
						break;
						case 'inspiration'://Воодушевление
							var obj = {};
								obj.field = actionRow;
								obj.cardsMass = (!$.isEmptyObject(result.actions.cards)) ? result.actions.cards[player][row] : null;
								obj.effectName = 'inspiration';
								obj.effectType = 'buff';

							animatePositiveNegativeEffects(obj);
						break;
						case 'fury'://Неистовство
							var obj = {};
								obj.field = actionRow;
								obj.cardsMass = (!$.isEmptyObject(result.actions.cards)) ? result.actions.cards[player][row] : null;
								obj.effectName = 'fury';
								obj.effectType = 'buff';

							animatePositiveNegativeEffects(obj);
						break;
						case 'terrify'://Страшный
							var obj = {};
								obj.field = actionRow;
								obj.cardsMass = (!$.isEmptyObject(result.actions.cards[player])) ? result.actions.cards[player][row] : null;
								obj.effectName = 'terrify';
								obj.effectType = 'debuff';

							animatePositiveNegativeEffects(obj);
						break;
						case 'killer'://Убийца

							var card = actionRow.find('.cards-row-wrap .content-card-item')[parseInt(item)];

							animationBurningCardEndDeleting(card);

							setTimeout(function(){
								setCardStrength(result.actions.cards_strength);
							},1000);

						break;
						case 'cure'://Исциление

						break;
						case 'sorrow'://печаль

							var obj = {};
								obj.field = actionRow;
								obj.cardIndex = 'undefined';
								obj.effectName = 'inspiration';
								obj.effectType = 'buff';
								obj.effectAnimation = 'fade';
							//Внимание - Удаление ефекта !
							animateDeletingPositiveNegativeEffects(obj);
						break;

					}
				}
			}
		}
	}

	if(!$.isEmptyObject(result.actions.disappear)){
		for(var player in result.actions.disappear){
			for(var row in result.actions.disappear[player]){

				var actionRow = $('#'+player+'.convert-cards '+ intRowToField(row));

				for(var item in result.actions.disappear[player][row]){

					var action = result.actions.disappear[player][row][item];

					switch(action){
						case 'support':
							var obj = {};
								obj.field = actionRow
								obj.cardIndex = parseInt(item);
								obj.effectName = 'support';
								obj.effectType = 'buff';

							animateDeletingPositiveNegativeEffects(obj);
						break;

						case 'brotherhood':
							var obj = {};
								obj.field = actionRow
								obj.cardIndex = parseInt(item);
								obj.effectName = 'brotherhood';
								obj.effectType = 'buff';

							animateDeletingPositiveNegativeEffects(obj);
						break;

						case 'terrify':
							var obj = {};
								obj.field = actionRow;
								obj.cardIndex = parseInt(item);
								obj.effectName = 'terrify';
								obj.effectType = 'debuff';

							animateDeletingPositiveNegativeEffects(obj);
						break;

						case 'fury':
							var obj = {};
								obj.field = actionRow;
								obj.cardIndex = parseInt(item);
								obj.effectName = 'fury';
								obj.effectType = 'buff';

							animateDeletingPositiveNegativeEffects(obj);
						break;

					}
				}
			}
		}
	}

	if ( $.isEmptyObject(result.actions.appear) && $.isEmptyObject(result.actions.disappear)) {
		recalculateBattleStrength();
	}

}

//battle start (Socket messages)
function startBattle() {
	conn = new WebSocket('ws://' + socketResult['dom'] + ':8080');//Создание сокет-соединения
	console.warn(conn);
	//Создание сокет-соединения
	conn.onopen = function(data){
		console.warn('Соединение установлено');
		conn.send(
			JSON.stringify({
				action: 'userJoinedToRoom',//Отправка сообщения о подключения пользователя к столу
				ident: ident
			})
		);
	};

	conn.onclose = function(e){}
	conn.onerror = function(e){
		alert('Socket error');
	};
	conn.onmessage = function(e){
		var result = JSON.parse(e.data);
		//console.group('Action');
		console.group(result.message);
		console.log(result);
		console.groupEnd();

		switch(result.message){
			case 'usersAreJoined':
				var token = $('.market-buy-popup input[name=_token]').val().trim();
				//Запрос на формирование изначальной колоды и руки пользователя
				$.ajax({
					url:		'/game_start',
					type:		'PUT',
					headers:	{'X-CSRF-TOKEN': token},
					data:		{battle_id: result.battleInfo, time: phpTime()},
					error:		function(jqXHR, exception){
						ajaxErrorMsg(jqXHR, exception);
					},
					success:	function(data){
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							//Формирование данных пользователей и окна выбора карт
							buildRoomPreview(data['userData']);
							hidePreloader();
						}

						var expireTime = result.turn_expire - phpTime();
						convertTimeToStr(expireTime);
						if(expireTime > 0){
							startTimer(result.round_status.current_player);
						}
					}
				});
				break;

			case 'changeCardInHand':
				hidePreloader();
				$('#selecthandCardsPopup #handCards .change-card.clicked').parents('li').addClass('animator-out');
				setTimeout(function(){
					$('#selecthandCardsPopup #handCards .animator-out').remove();
					$('#selecthandCardsPopup h5 span').text(result.can_change_cards);
					$('#selecthandCardsPopup #handCards').append(createFieldCardView(result.added_cards, result.added_cards.strength));
					$('#selecthandCardsPopup #handCards li:last-child').addClass('animator-in');
					$('#selecthandCardsPopup #handCards li:last-child').addClass('go');
					setTimeout(function () {
						$('#selecthandCardsPopup #handCards li:last-child').removeClass('animator-in go');
					},700);
				},700);

				if (result.can_change_cards == 0) {
					$('.content-card-item-main').removeClass('disactive');
					$('.content-card-item .change-card').remove();
				}
				break;

			case 'allUsersAreReady':
				changeTurnIndicator(result.round_status.current_player);//смена индикатора хода
				setDecksValues(result.counts, result.images);
				currentRound = result.round_status.round;
				break;

			case 'cardData':
				showCardActiveRow(result);
			break;

			case 'roundEnds':
				var win_status = [0, 0];
				for (var login in result.round_status.status.score) {
					if (login == $('.user-describer').attr('id')) {
						win_status[0] = result.round_status.status.score[login].length;
					} else {
						win_status[1] = result.round_status.status.score[login].length;
					}
				}
				$('.rounds-counts.user .rounds-counts-count').text(win_status[0]);
				$('.rounds-counts.oponent .rounds-counts-count').text(win_status[1]);

				$('.convert-stuff').removeAttr('class').addClass('convert-stuff');
				$('.debuff-or-buff-anim').remove();

				circleRoundIndicator();

				//Очищение полей
				$('.mezhdyblock #sortable-cards-field-more').children().fadeOut(500,function(){
					$('.mezhdyblock #sortable-cards-field-more').empty();
				});
				setTimeout(function() {
					buildBattleField(result.added_cards, result.dropped_cards);
					setDecksValues(result.counts, result.images);
					showCardOnDesc();
					recalculateBattleStrength();

					resultPopupShow(result.round_status.status.result + '! Подождите, идет подготовка нового раунда.');
					changeTurnIndicator(result.round_status.current_player);
					$('#sortableUserCards').empty();
					for(var i in result.user_hand){
						$('#sortableUserCards').append(createFieldCardView(result.user_hand[i], result.user_hand[i]['strength']));
					}
					animateHandCard();
					calculateRightMarginCardHands();

					popupActivation(result)

					allowToAction = (result.round_status.current_player == $('.user-describer').attr('id'))? true: false;
					cardCase(result.round_status.card_source, allowToAction);//Функция выбора карт
					userMakeAction(conn, result.round_status.card_source, allowToAction);//Функция разрешает пользователю действие
					setTimeout(function(){
						$('#successEvent').removeClass('show');
						if($('div.troll-popup.show').length <= 0){
							closeAllTrollPopup();
						}
						hidePreloader();
					}, 3000);
				}, 501);

			break;

			//Игра закончена
			case 'gameEnds':
				var res = {
					gold:0,
					silver:0,
					ranking:0,
					win:"Поздравляем! Вы победили!",
					lose:"К сожалению Вы проиграли!",
					draw:"Игра окончилась вничью!"
				};
				if(result.resources['gold'] != '0') res.gold = result.resources['gold'];
				if(result.resources['silver'] != '0') res.silver = Math.abs(result.resources['silver']);
				if(result.resources['user_rating'] != '0') res.ranking = Math.abs(result.resources['user_rating']);

				var resPop = $('#endGamePopup');
				var resMessage = 'По результатам боя Вы ';
				switch(result.resources.gameResult) {
					case 'loose':
						resPop.find('h5').text(res.lose);
						resMessage += 'получили <img class="resource" src="/images/header_logo_silver.png" alt="">'+res.silver+' серебра, но потеряли '+res.ranking+' очков рейтинга.';
						resPop.find('.result-game').html(resMessage);
						break;
					case 'win':
						resPop.find('h5').text(res.win);
						resMessage += 'получили <img class="resource" src="/images/header_logo_silver.png" alt="">'+res.silver+' серебра, и '+res.ranking+' очков рейтинга.';
						resPop.find('.result-game').html(resMessage);
						break;
					case 'draw':
						resPop.find('h5').text(res.draw);
						break;
				}

				closeAllTrollPopup();
				openTrollPopup(resPop);
				$('#successEvent').removeClass('show');
				allowToAction = false;
				turnDescript = {"cardSource" : "hand"};
				changeTurnIndicator(null);
				allowPopups = false;
				break;

			//Пользователь сделал действие
			case 'userMadeAction':
				if( (result.round_status.status.length > 0) || (!$.isEmptyObject(result.round_status.status)) ){
					resultPopupShow('Противник пасует');
				}
				if(!$.isEmptyObject(result.played_card.card)) {
					if(currentRound != result.round_status.round){
						//setTimeout(function () {
						$('.field-for-cards').removeClass('visible');
						$('.convert-cards .content-card-item').removeClass('transition');
						calculateRightMarginCardHands();
						fieldBuild(result, false);
						currentRound = result.round_status.round;
						//},1000)
					}else{
						fieldBuild(result, true);
						processActions(result);
					}

					changeTurnIndicator(result.round_status.current_player); //смена индикатора хода

					setDecksValues(result.counts, result.images);

					detailCardPopupOnStartStep(result.played_card['card'], result.played_card['strength']);
				}else{

				}
			break;
		}

		if( (result.message == 'allUsersAreReady') || (result.message == 'userMadeAction') ){
			calculateRightMarginCardHands();
			hidePreloader();

			if(typeof result.users_energy != "undefined"){
				for(var login in result.users_energy){
					$('.convert-right-info #'+login+' .stats-energy').text(result.users_energy[login]);
				}
			}

			var expireTime = result.timing - phpTime();
			convertTimeToStr(expireTime);
			clearInterval(TimerInterval);
			if(expireTime > 0){
				startTimer(result.round_status.current_player);
			}

			//!! Разбор активации попапов
			popupActivation(result);

			if(result.round_status.current_player == $('.user-describer').attr('id')){
				$('.info-block-with-timer .title-timer').find('span').text('Ваш ход').end().addClass('user-turn-green');
				allowToAction = true;
			}else{
				$('.info-block-with-timer .title-timer').find('span').text('Ход противника:').end().removeClass('user-turn-green');
				allowToAction = false;
			}

			cardCase(result.round_status.card_source, allowToAction);//Функция выбора карт
			userMakeAction(conn, result.round_status.card_source, allowToAction);//Функция разрешает пользователю действие
			clearRowSelection();//Очистка активированых рядов действий карт
		}

		//Пользователь сдается
		$('.convert-right-info button[name=userGiveUpRound]').unbind();
		$('.convert-right-info button[name=userGiveUpRound]').click(function(){
			var surrenderResult = confirm('Вы действительно хотите сдаться?');
			if(surrenderResult){
				conn.send(
					JSON.stringify({
						action: 'userGivesUp',//Отправка сообщения о подключения пользователя к столу
						ident: ident
					})
				);
			}
			else{
				return ;
			}
		});
	}
}

window.userImgData = {opponent:'', user: ''}; //User Images
var socketResult;
var ident;
var allowToAction = false;
var turnDescript = {cardSource: 'hand', additionalData: ''};
var timeOut;
var TimerInterval;
var conn;
var currentRound = 0;

$.get('/get_socket_settings', function (data) {
	socketResult = JSON.parse(data); //Получение данных настроек соккета
	//Формирование начального пакета идентификации битвы
	ident = {
		battleId: socketResult['battle'],
		userId: socketResult['user'],
		hash: socketResult['hash']
	};
	timeOut = socketResult['timeOut'];

	$(document).ready(function () {
		startBattle();
	});
});

$(document).ready(function(){
	radioPseudo();//Start card select popup
	showPreloader();//Show screen preloader
	clickCloseCross();//Close popup listener
	calculateRightMarginCardHands();//Create nice margin for hand carda
	viewPlayerDeck();//Deck click listener
	// При открытом попапе если мы нажимаем на любую область документа - попап закрываеться
	$(document).on('click', function() {
		if ( $('.troll-popup').hasClass('troll-popup-custom') ) {
			var id = $('.troll-popup.troll-popup-custom').attr('id');
			closeSecondTrollPopup( $('#'+id) );
		}
	});
	recalculateBattleStrength();
	circleRoundIndicator();
});

//*Анимации*//

//Анимация прихода карт на руку
function animateHandCard() {
	var delay = 500;
	$('#sortableUserCards li').addClass('transitiontime').removeClass('tramsitioned').css({
		'-webkit-animation-duration': delay+'ms',
		'animation-duration': delay+'ms',
		'left':'0px',
		'transform': 'none',
		'transition-delay': '0s'
	});
	var timeout3 = 0;
	$('#sortableUserCards li').each(function () {
		var k = $(this);
		setTimeout(function () {
			k.addClass('notransition');
			setTimeout(function () {
				k.removeClass('transitiontime notransition');
			},delay);
		},timeout3);
		timeout3+=100;
	});
}
// /Анимация прихода карт на руку

//Показ попапа с картой которой ходит игрок( открываеться при начале хода )
function detailCardPopupOnStartStep(card, strength, callback) {
	closeAllTrollPopup();
	var holder = $('#card-start-step');
	holder.find('.content-card-info').empty();
	var popContent = createCardDescriptionView(card, strength, 'without-description');

	holder.find('.content-card-info').append(popContent);
	openSecondTrollPopup(holder,null);

	setTimeout(function() {
		closeSecondTrollPopup(holder,null);//закрываю попап с детальной инфой карты
		setTimeout(function() {
			showCardOnDesc(null, callback);//показываю сыгранную карту на столе
		}, 500)
	}, 2000);
}

function recalculateCardsStrengthTimeout(params){
	setTimeout(function(){
		recalculateBattleStrength();
	}, params.timing);
}

//показать карты анимированно на столе
function showCardOnDesc(action, callback) {
	var card = $('.content-card-item.loading');

	switch(action){
		case 'mini-scale':
			card.addClass('show').removeClass('loading');
			setTimeout(function(){
				if (!card.parents('.field-for-cards').hasClass('overflow-visible') ) {
					card.parents('.field-for-cards').addClass('overflow-visible');
				}

				card.addClass('mini-scale');
				setTimeout(function(){
					card.removeClass('mini-scale');
					setTimeout(function(){
						card.parents('.field-for-cards').removeClass('overflow-visible');

						if (typeof callback !== 'undefined') {
							callback.callbackFunctionName(callback.callbackFunctionParams);
						}
					}, 300);
				}, 500);
			},1000);
		break;
		default:
			card.addClass('show').removeClass('loading');

			if (typeof callback !== 'undefined'){
				callback.callbackFunctionName(callback.callbackFunctionParams);
			}
	}
}

// pretty card moving
function cardMovingFromTo(side, from, count){
	var wrapper = null;
	var part = null;
	var cardsPosition = $('.convert-battle-front');

	switch(side){
		case 'opponent':wrapper = '#card-give-more-oponent'; break;
		case 'user':	wrapper = '#card-give-more-user'; break;
	}

	part = '[data-field='+from+']';

	var cardsStackObject = $(wrapper+' '+part);
	var cardsStackPosition = cardsStackObject.offset();
	var cardsStackParams = {
		width:	cardsStackObject.width(),
		height:	cardsStackObject.height(),
		background: cardsStackObject.find('.card-my-init').css('background-image')
	};

	var styles = {
		'width':	cardsStackParams.width,
		'height':	cardsStackParams.height,
		'background-image': cardsStackParams.background,
		'top':		cardsStackPosition.top,
		'left':		cardsStackPosition.left
	};

	var cardWhatGonaBeMoving = $('<div class="moving-card"></div>').css(styles);

	var cardsDestination = [];

	var cardWidth = 103; // card width by default css
	var paramToLeft = cardWidth/2;

	if($('#sortableUserCards li').length){
		cardWidth = $('#sortableUserCards li .content-card-item-main').width();
		paramToLeft = parseInt( $('.content-card-item:not(.added-by-effect)').width()/2);
	}

	$('.added-by-effect').each(function(){
		var addedParams = {
			width:	cardWidth,
			height:	$(this).height(),
			top:	$(this).offset().top - 10, // VERTICAL-ALIGN OF DECK - THEIR FAULT
			left:	$(this).offset().left - paramToLeft - 10 // NOT GOOD, BUT DON'T KNOW WHAT DO
		};
		cardsDestination.push(addedParams);
	});

	for(var i = 0; i < count; i++){
		var clonedCardMarkup = cardWhatGonaBeMoving.clone();
		cardsPosition.append(clonedCardMarkup);
	}

	var point = 0;

	var timer = setInterval(function(){
		var cardDistonationParam = cardsDestination[point];

		var style = {
			width:	cardDistonationParam.width,
			height:	cardDistonationParam.height,
			top:	cardDistonationParam.top,
			left:	cardDistonationParam.left
		};

		$('.moving-card').eq(point).css(style).addClass('move');
		$('.added-by-effect').eq(point).removeClass('waiting-for-animation');
		point++;

		if (point == count){
			clearInterval(timer);
			setTimeout(function(){
				$('.moving-card').remove();
				$('.added-by-effect').removeClass('added-by-effect');
			}, 1300);
		}
	}, 300);
};

function animationDeleteSpecialCard(player,rowId){

	var card = $('#'+player+'.convert-cards '+rowId+' .image-inside-line li'),
		otboy = $('.cards-bet[data-type="'+player+'"] [data-field="discard"]'),
		otboyOffset = otboy.offset(),
		cardOffset = card.offset(),
		zIndexHolder = 0;

	setTimeout(function(){
		card.css({
			'position':'fixed',
			'width':'auto',
			'z-index':'1000',
			'transition':'opacity ease .4s',
			'transform':'translateZ(0)',
			'left':cardOffset.left+'px',
			'top':cardOffset.top - $(window).scrollTop()+'px'
		}).animate({
			left: otboyOffset.left,
			top: otboyOffset.top - $(window).scrollTop()
		},{
			duration: 2500,
			progress: function (animation, number,remainingMs) {
				if(number >= 0.65 && number <= 0.67){
					card.css({'opacity':'0'});
				}
			},
			start: function(){
				card.parents('.convert-stuff').css({'z-index':'2'})
				zIndexHolder = card.parent().css('z-index');
				card.parent().css({'z-index':'100'})
			},
			complete: function() {
				card.parents('.convert-stuff').removeAttr('style');
				card.parent().css({'z-index':zIndexHolder});
				card.fadeOut(500,function(){
					card.remove();
				})
			}
		})
	}, 1000);
}

//Анимация возвтращения своих карт в в колоду - работает вместе с animateHandCard()
function animationCardReturnToOutage(cards, time, callback){
	var outageHolder = $('#card-give-more-user [data-field="discard"]');
	var outageHolderLeft = outageHolder.offset().left;
	var transitionDelay = 0;

	cards.addClass('tramsitioned');

	var zIndex = 100;;
	cards.each(function(index,item){
		var positionLeft = +($(item).offset().left).toFixed(0);
		var shiftLeft = positionLeft - outageHolderLeft + 15; // 15 - корректировка на сдвиг скейлом
		$(item).css({
			'left':'-'+shiftLeft+'px',
			'transform': 'scale3d(0.7,0.7,0.7)',
			'transition-duration': time+'ms',
			'transition-delay':transitionDelay+'s',
			'z-index': zIndex
		});

		zIndex++;
		transitionDelay+=0.1;

		if(index == (cards.length - 1) && typeof callback === 'function'){
			callback();
		}
	});

	var cardTransitionDuration = parseFloat( cards.css('transition-duration') );
	var timeout = (cardTransitionDuration + transitionDelay)*1000;

	return timeout;
}

//Показать попап при перегрупировке
function detailCardPopupOnOverloading(cardDetailOverloadingMarkup, card, strength, otherFunc) {
	var holder = $('#card-start-step');
	holder.find('.content-card-info').empty().append(cardDetailOverloadingMarkup);
	var popContent = createCardDescriptionView(card, strength, 'without-description');
	holder.find('.content-card-info').addClass('overloading-animation').append(popContent).end().addClass('overloading');
	openSecondTrollPopup(holder,null);

	setTimeout(function(){
		holder.find('.content-card-info').removeClass('overloading-animation');
		setTimeout(function(){
			closeSecondTrollPopup(holder,null);
			setTimeout(function(){
				holder.removeClass('overloading');
				if(otherFunc == 'show-and-delate-card'){
					showCardOnDesc('mini-scale');
					animationBurningCardEndDeleting('fade');
				}
			},1000)
		},2000)
	},2000);
}

function animatePositiveNegativeEffects(obj) {
	var field = obj.field,
		cardsMass = obj.cardsMass,
		effectName = obj.effectName,
		effectType = obj.effectType;

	var mainRow = field.closest('.convert-stuff');
	var pointsSum = mainRow.find('.field-for-sum');

	//Анимация на поле
	mainRow.addClass(effectName+'-'+effectType+'-wrap');
	var effectMarkup = null;
	if(field.children('.'+effectName+'-'+effectType+'.active').length > 0){
		//Проверить - есть ли уже разметка для такого бафа
		effectMarkup = field.children('.'+effectName+'-'+effectType+'.active');
	}else{
		field.append('<div class="debuff-or-buff-anim '+effectName+'-'+effectType+'" ></div>');
		effectMarkup = field.children('.'+effectName+'-'+effectType);
	}


	//мини-хук - показывать анимаци только когда закрытый попап показ карты хода
	var timer = setInterval(function() {
		if ( !$('.troll-popup.show').length ) {

			//запуск анимации на поле
			effectMarkup.addClass('active');

			//Выборка нужных карт
			//var cardNeedArray = null;

			if (typeof cardsMass !== 'undefined' || cardsMass !== null) {

				var $cards = field.find('.cards-row-wrap .content-card-item');
				for(var c in cardsMass){
					var $card = $($cards[c]);

					if (
						(effectType == 'debuff' && $card.is('[data-immune=0]') && $card.is('[data-full-immune=0]') ) || (effectType == 'buff' && $card.is('[data-full-immune=0]'))
					) {
						var strength = parseInt(cardsMass[c]['strength']);
						var strengthMod = parseInt(cardsMass[c]['strModif']);
						var operation = cardsMass[c]['operation'];
						if (strength !== NaN && strength !== strengthMod) {

							animateCardStrengthPulsing($card,effectName,effectType,strength,strengthMod,operation);

						}
					}

					switch(effectType){
						case 'buff':
							if ( effectName == 'brotherhood' && (Object.keys(cardsMass).length <= 1)) {
								break;
							}
							$card.addClass('buffed '+effectName+'-buffed');
							break;
						case 'debuff':
							$card.addClass('debuffed '+effectName+'-debuffed');
							break;
					}

				};
			}

			// setTimeout(function() {
			// 	pointsSum.addClass('pulsed');
			// 	setTimeout(function() {
			// 		pointsSum.removeClass('pulsed');
			// 	}, 500);
			// }, 0);

			mainRow.addClass(effectType);
			clearInterval(timer);
		}
	},600);
}

function animateCardStrengthPulsing(card,effectName,effectType,strength,strengthMod,operation) {

	setTimeout(function(){
		var currentValue = card.find('.card-current-value');
		currentValue.text(strength);//на всякий - вставляем обычное значение карты
		card.addClass('pulsed');//пульсация - начало

		var buffDebuffHolder = card.find('.buff-debuff-value');
		var newValue = null;
		var operationType = '';

		switch(operation.charAt(0)){
			case '+':
				operationType = '+';
				newValue = strengthMod - strength;

				break;
			case '-':
				operationType = '-';
				newValue = Math.abs(strengthMod - strength);//по модулю
				break;
			case 'x':
				operationType = 'x';
				newValue = operation.substr(1);
				break;
		}

		buffDebuffHolder.attr('data-math-simb', operationType );//вст + или - или х2 х3 ...
		buffDebuffHolder.text(newValue);
		currentValue.text(strengthMod);

		setTimeout(function(){
			card.removeClass('pulsed');//пульсация - конец
			recalculateBattleStrength();//пересчет сил на поле боя
		},2000);


	},500)

}

function animateDeletingPositiveNegativeEffects(obj) {
	var field = obj.field,
		cardIndex = obj.cardIndex,
		effectName = obj.effectName,
		effectType = obj.effectType;
		effectAnimation = obj.effectAnimation;

	var mainRow = field.closest('.convert-stuff');
	var pointsSum = mainRow.find('.field-for-sum');


	//мини-хук - показывать удаление аанимаци только когда закрытый попап показ карты хода
	var timer = setInterval(function() {
		if ( !$('.troll-popup.show').length ) {


			mainRow.removeClass(effectName+'-'+effectType+'-wrap '+effectType);

			if(field.children('.'+effectName+'-'+effectType+'.active').length > 0){// удаляем разметку подсвечивания полей
				var effectMarkup = field.children('.'+effectName+'-'+effectType);

				switch(effectAnimation){

					case 'fade':
						effectMarkup.fadeOut(500,function(){
							effectMarkup.remove();
						})
						break;

					default:
						effectMarkup.removeClass('active');
						setTimeout(function(){
							effectMarkup.fadeOut("slow",function(){
								effectMarkup.remove();
							})
						},2000);
				}
			}

			if (cardIndex != 'undefined') {//если мы знаем индекс карты
				var $card = field.find('.cards-row-wrap .content-card-item').eq(cardIndex);// выборка карты

console.info("cardIndex", cardIndex)
console.info("$card", $card)

console.info("effectType", effectType)

console.info("effectName", effectName)
				switch(effectType){//удаляем класы бафов-дебафов
					case 'buff':
						$card.removeClass('buffed '+effectName+'-buffed');
						break;
					case 'debuff':
						$card.removeClass('debuffed '+effectName+'-debuffed');
						break;
				}
			}

			clearInterval(timer);
		}
	},600);
}

function animationBurningCardEndDeleting(card,action) {
	var card = $(card);

	if (!card.parents('.field-for-cards').hasClass('overflow-visible') ) {

		card.parents('.field-for-cards').addClass('overflow-visible');
		card.parents('.convert-stuff').css({
			'z-index':'10'
		});

	}

	switch(action){
		case 'fade':
			card.removeClass('show');
			setTimeout(function() {
				card.remove();

			}, 500);
		break;
		default:
			//console.log('default');
			card.append('<span class="card-burning-item-main"><img src="/images/card-burning-item-main-2.gif" alt="" /></span>');
			setTimeout(function(){
				card.addClass('card-burning');
				setTimeout(function(){
					card.find('.content-card-item-main').fadeOut(900,function(){
						setTimeout(function(){
							card.removeClass('card-burning');
							setTimeout(function(){

								card.parents('.field-for-cards').removeClass('overflow-visible');
								card.parents('.convert-stuff').removeAttr('style');

								card.remove();

								recalculateBattleStrength();//пересчет сил на поле боя

							},1000)
						},500)
					});
				},2500)
			},300)
	}

}

var processingSetsCardStrength = true;
function setCardStrength(cards_strength){
	if(!$.isEmptyObject(cards_strength) && processingSetsCardStrength){
		processingSetsCardStrength = false;
		for(var player in cards_strength){
			for(var row in cards_strength[player]){

				var actionRow = $('#'+player+'.convert-cards '+ intRowToField(row));

				for(var item in cards_strength[player][row]){

					var value = cards_strength[player][row][item];

					var card = $(actionRow.find('.cards-row-wrap .content-card-item')[parseInt(item)]);
					var cardValue = card.find('.card-current-value');
					if (parseInt(cardValue) !== value){
						cardValue.text(value);
					}

				}
			}
		}
		processingSetsCardStrength = true;
	}
}