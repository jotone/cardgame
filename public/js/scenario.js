/** /main page*/
var curentRaceURL;

function fancyboxForm(){
	$('.fancybox-form').fancybox({
		openEffect  : 'fade',
		closeEffect : 'fade',
		autoResize:true,
		wrapCSS:'fancybox-form',
		'closeBtn' : true,
		fitToView:true,
		padding:'0'
	})
}

function radioPseudo2() {
	$(document).on('click', '#buyingPremium label', function () {
		if($(this).find('input').prop('checked')){
			$('#buyingPremium .pseudo-radio').removeClass('active');
			$(this).find('.pseudo-radio').addClass('active');
		}
	});
}
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
	showErrorMessage(msg);
}
//Форма логинизации на главной
function showFormOnMain(){
	//При нажатии на кнопку "вход"
	$('.forget-pass-form button').click(function(event){
		if ($(this).hasClass('show-form-please') ){
			event.preventDefault();
			$('.form-wrap-for-rows').slideDown(500);
			$(this).removeClass('show-form-please');
		}
	});
	//Не скрывать форму входа при возврате ошибки
	if(!$('.forget-pass-form button').hasClass('show-form-please') ){
		$('.form-wrap-for-rows').slideDown(10);
	}
}
function showWindowAboutOnMain() { // скрыть / показать  инфо о игре
	$('.drop-menu-open').click(function(e){
		$('.convert-about').slideToggle(500);
		$(this).toggleClass('back-text');
		e.preventDefault();
	});
}
function clickOnRace() { //показать инфо расы
	$('.rase-ric').click(function (e) {
		e.preventDefault();
		if(!$(this).closest('.item-rise').hasClass('active')){$('.item-rise').removeClass('active');}
		$(this).closest('.item-rise').toggleClass('active');
	});
}function clickOnLicenseAgree() { //показать инфо расы
	$('.agree-field a').click(function (e) {
		e.preventDefault();
		$('.license-agreement').addClass('show');
	});
	$('.license-agreement .button-troll').click(function (e) {
		e.preventDefault();
		$('.license-agreement').removeClass('show');
	});
}
function clickCloseCross() { //закрыть попап
	$('.close-this').click(function (e) {
		e.preventDefault();
		$('.item-rise').removeClass('active');
		$('.license-agreement').removeClass('show');
		closeAllTrollPopup();
	});
}
// end of /main
function logoutUser() {
	$('a.log_out_menu').click(function (e) {
		e.preventDefault();
		closeAllTrollPopup();
		var href = $(this).data('href');
		var conf = $('#confirm-popup');
		var butts = conf.find('.button-troll');
		conf.find('.confirm-txt').text('Вы уверены что хотите выйти?');
		openTrollPopup(conf);
		butts.unbind();
		butts.click(function (e) {
			e.preventDefault();
			result = $(this).data('value');
			closeAllTrollPopup();
			if(result === true){
				location = href;
			}
		});
	});
}
/** /settings*/

//Получить данные пользователя
//Если user_login не указан, возвращает данные текущей сессии
function getUserData(user_login){
	$.ajax({
		url:    '/get_user_data',
		type:   'GET',
		data:   {login: user_login},
		success:function(data){
			if(user_login != ''){
				var res = JSON.parse(data);
				if(res['avatar'] != ''){
					$('.user .user-image').append('<img src="/img/user_images/' + res['avatar'] + '" alt="">');
				}
				$('.rating .resurses .gold').text(res['gold']);
				$('.rating .resurses .silver').text(res['silver']);
				$('.rating .resurses .lighting').text(res['energy']);
				$('.preload .preloader, .convert-resurses .preload-resurses').hide();
				$('.preload .user-name, .rating .convert-resurses .resurses').css('opacity', '1');
				$('.convert-header .rating>p').text('РЕЙТИНГ '+res['rating']['position']+'-е место : '+res['rating']['rating'])
				window.maxCardQuantity		= res['maxCardQuantity'];
				window.minWarriorQuantity	= res['minWarriorQuantity'];
				window.specailQuantity		= res['specialQuantity'];
				window.leaderQuantity		= res['leaderQuantity'];
				window.leagues				= res['leagues'];
				window.exchange_gold		= res['exchanges']['usd_to_gold'];
				window.exchange_rub			= res['exchanges']['rub_to_usd'];
				window.gold_to_silver		= res['exchanges']['gold_to_silver'];
				window.gold_to_100_energy	= res['exchanges']['gold_to_100_energy'];
				window.gold_to_200_energy	= res['exchanges']['gold_to_200_energy'];
				window.silver_to_100_energy = res['exchanges']['silver_to_100_energy'];
				window.silver_to_200_energy = res['exchanges']['silver_to_200_energy'];
				window.user_gold			= res['gold'];

			}
		},
		error: function (jqXHR, exception) {
			ajaxErrorMsg(jqXHR, exception);
		}
	});
}
function settingsInputFile(){
	$('.form-description-settings-inp-wrap input').styler({
		fileBrowse:" ",
		filePlaceholder:"Сменить аватар"
	});
}
//Изменение пользовательских настроек
function applySettings(){
	$('.form-wrap-input button[name=settingsChange]').click(function(e){
		e.preventDefault();
		var token = $('input[name=_token]').val();
		var formData = new FormData();
		formData.append( 'token', token );
		formData.append( '_method', 'PUT');
		formData.append( 'settings_email', $('.form-wrap-value input[name=settings_email]').val().trim());
		formData.append( 'current_password', $('.form-wrap-value input[name=current_password]').val().trim());
		formData.append( 'settings_pass', $('.form-wrap-value input[name=settings_pass]').val().trim());
		formData.append( 'settings_pass_confirm', $('.form-wrap-value input[name=settings_pass_confirm]').val().trim());
		formData.append( 'image_user', $('.form-description-settings-inp input[name=image_user]').prop('files')[0] );
		formData.append( 'user_name', $('.form-wrap-item input[name=settings_name]').val().trim() );
		formData.append( 'birth_date', $('.form-wrap-item input[name=settings_birth_date]').val().trim() );
		formData.append( 'gender', $('.form-wrap-item select[name=settings_gender]').val().trim() );
		formData.append( 'action', 'user_settings' );
		var img_KB = (typeof $('.form-description-settings-inp input[name=image_user]').prop('files')[0] != 'undefined')
			? $('.form-description-settings-inp input[name=image_user]').prop('files')[0].size / 1024
			: 0;

		if(img_KB > 4096){
			alert('Размер изображения не должен превышать 4 МБ')
		}else{
			$.ajax({
				url:		'/settings',
				headers:	{'X-CSRF-TOKEN': token},
				type:		'POST',
				processData:false,
				contentType:false,
				data:		formData,
				error: function (jqXHR){
					$('.forms-footer-blocks.right').html('<div style="width: 100%; height: 400px; z-index: 1000; background-color: #fff;">'+jqXHR.responseText+'</div>');
				},
				success:	function(data){
					if(data == 'success') {
						location = '/settings';
					}else{
						$('.form-wrap-for-rows .error-text').text(JSON.parse(data)).show();
					}
				}
			});
		}
	});
}
//обновление изображения пользователя
function settingUpdateImg(){
	$('.form-description-settings-inp input[name=image_user]').change(function(e){
		var reader = new FileReader();
		reader.onload = function (e){
			if( $('.form-description-settings-img .form-description-settings-img-wrap #avatarImg').length > 0 ){
				$('.form-description-settings-img .form-description-settings-img-wrap #avatarImg').attr('src', e.target.result);
			}else{
				$('.form-description-settings-img .form-description-settings-img-wrap').append('<img id="avatarImg" src="" alt="">');
				$('.form-description-settings-img .form-description-settings-img-wrap #avatarImg').attr('src', e.target.result);
			}
		};
		reader.readAsDataURL( $(this).prop('files')[0] );
	});
}
//end of /settings
/** /deck*/

//Построение Отображения карты в колоде
//data - данные карты
//wraper - обертка для карты
function buildCardDeckView(cardData, wraper){
	var result = '' +
		'<div class="content-card-item-main';
	if(cardData['type'] == 'special'){result += ' special-type';}
	if(cardData['is_leader'] == 1){result += ' leader-type';}
	switch (cardData['race']) {
		case 'highlander':	result += ' highlander-race'; break;
		case 'monsters':	result += ' monsters-race'; break;
		case 'undead':		result += ' undead-race'; break;
		case 'cursed':		result += ' cursed-race'; break;
		case 'knight':		result += ' knight-race'; break;
		case 'forest':		result += ' forest-race'; break;
		default: if(cardData['type'] == 'neutrall'){result += ' neutrall-race';}
	}
	result +=' " style="background-image: url(/img/card_images/'+cardData['img_url']+')" data-leader="'+cardData['is_leader']+'" data-type="'+cardData['type']+'" data-weight="'+cardData['weight']+'">' +
			'<div class="card-load-info card-popup"><div class="info-img"><img class="ignore" src="/images/info-icon.png" alt=""><span class="card-action-description">Инфо о карте</span></div>';
	if(cardData['is_leader'] == 1){
		result += '<div class="leader-flag"><span class="card-action-description">Карта Лидера</span></div>';
	}
	result +='<div class="label-power-card"><span class="label-power-card-wrap">'+cardData['strength']+'</span><span class="card-action-description">';
	if(cardData['type'] == 'special'){
		result += 'Специальная карта';
	}else{
		result += 'Сила карты';
	}
	result +='</span></div><div class="hovered-items"><div class="card-game-status"><div class="card-game-status-role">' ;
	if(cardData['type'] != 'special'){
		for (var j = 0; j < cardData['allowed_rows'].length; j++) {
			result +='<img src="'+cardData['allowed_rows'][j].image+'" alt=""><span class="card-action-description">'+cardData['allowed_rows'][j].title+'</span>';
		}
	}

	result += '</div><div class="card-game-status-wrap">';
	if(cardData['actions'].length>0){
		for (var i = 0; i < cardData['actions'].length; i++) {
			result = result + '<span class="card-action"><img src="' + cardData['actions'][i].img+'" alt=""><span class="card-action-description">'+cardData['actions'][i].title+'</span></span>';
		}
	}
	result = result + '</div>' +
					'</div>' +
					'<div class="card-name-property"><p>'+cardData['title']+'</p></div>' +
					'<div class="card-description-hidden"><div class="jsp-cont-descr">';
	if(curentRaceURL.length>0){result +='<img src="img/fractions_images/'+curentRaceURL+'" alt="">';}

	result += cardData['descr'] +'</div></div>'+
				'</div>' +
			'</div>' +
		'</div>';
	if(wraper == 'ul'){
		result = '<li class="content-card-item disable-select" data-cardId="'+cardData['id']+'" data-cardleft="'+cardData['quantity']+'">'+result+
					'<div class="maxCountInDeck-wrap">' +
						'<span class="current-card-type-count">'+cardData['quantity']+'</span>/<span class="current-max-card-count">'+cardData['max_quant']+'</span>' +
					'</div>' +
				'</li>';
	}
	if(wraper == 'div'){
		result = '' +
			'<div class="market-cards-item disable-select" data-card="'+cardData['id']+'">'+result+
				'<div class="market-card-item-price"><div class="card-quantity"><span class="available">'+cardData['quantity']+'</span>/'+cardData['max_quant']+'</div> ';

		if(cardData['gold'] != 0){
			result += '<div class="marker-price-gold">'+cardData['gold']+'</div>';
		}
		if(cardData['silver'] != 0){
			result += '<div class="marker-price-silver">'+cardData['silver']+'</div>';
		}
		if((cardData['silver'] != 0) || (cardData['gold'] != 0)) {
			result += '</div>' +
				'<div class="market-card-item-buy"><a href="#" class="button-buy" id="simpleBuy">КУПИТЬ</a></div>';
		}
		result += '</div>';
	}
	return result;
}

function infoCardStart() { // dubl from battle_scenario.js
	var popup = $('#card-info');
	$(document).on('click', '.info-img',function () {
		closeAllTrollPopup();
		var content =  $(this).closest('.content-card-item-main').parent().html();
		popup.find('.content-card-info').html(content);

		infoCardChangeInfoProps();

		openTrollPopup(popup);
		setTimeout(function () {
			var jsp = popup.find('.jsp-cont-descr');
			jsp.jScrollPane();

		}, 100);
	});

	function infoCardChangeInfoProps(){
		var contentCard = $('#card-info .content-card-item-main');
		var contentCardImg = contentCard.css('background-image').replace('url(','').replace(')','').replace(/\"/gi, "");

		contentCard.removeAttr('style');
		contentCard.find('.card-load-info').prepend('<div class="card-info-image"><img src="'+contentCardImg+'" alt=""></div>');

		var maxImgWidth = contentCard.find('.card-load-info .card-info-image img').width();

		if (maxImgWidth <= 0){
			maxImgWidth = '50vw'
		}else {
			maxImgWidth = maxImgWidth*2
		}

		contentCard.css('min-width',maxImgWidth).addClass('new-card-form');
		var description = contentCard.find('.card-description-hidden').detach();
		contentCard.append(description);

	}
}

//Формирование колод пользователя и свободных карт
function getUserDeck(deck, user_login){
	$.ajax({
		url:    '/get_user_deck',
		type:   'GET',
		data:   {deck:deck, login: user_login},
		success:function(data){
			var res = JSON.parse(data);
			curentRaceURL = res.race_img;
			$('.content-card-field ul#sortableTwo, .content-card-field ul#sortableOne').empty();
			//Формирование доступных карт
			for(var i in res['available']){
				$('.content-card-field ul#sortableTwo').append(buildCardDeckView(res['available'][i], 'ul'));
			}
			//Формирование Карт Колоды
			for(var i in res['in_deck']){
				$('.content-card-field ul#sortableOne').append(buildCardDeckView(res['in_deck'][i], 'ul'));
			}
			$('.content-card-center-img-wrap img').attr('src',$('.content-card-center-img-wrap img').data('src')+'/'+res['race_img']);
			$('.select-rase-img img').attr('src',window.location.origin+'/img/fractions_images/'+curentRaceURL)
			//Пересчет данных колоды
			recalculateDeck();
		},
		error: function (jqXHR, exception) {
			ajaxErrorMsg(jqXHR, exception);
		}
	});
}
//скролл
function initScrollpane() {
	$('.scroll-pane, .market-cards, .market-cards-wrap').jScrollPane({
		contentWidth: '0px',
		autoReinitialise: true
	});
}
//Фикс перетягивания колоды
function underDragCardFix() {
	if ($('.content-card-field')) {
		$('.content-card-field').mouseleave(function (event) {
			$(document).mouseup();
		});
	}
}
//пересчет коллоды
function recalculateDeck(){
	var cardsCount = 0;         //количество карт
	var warriorsQuantity = 0;   //Количество воинов
	var specialQuantity = 0;    //Количество спец карт
	var deckWeight = 0;         //Вес колоды
	var league = '';            //Лига колоды (уровень)
	var leaderQuantity = 0;     //Количество карт лидеров
	var cardsDeck = {};
	$('#sortableOne .content-card-item').each(function(){
		//пересчет карт
		var tmpX = parseInt($(this).attr('data-cardleft'));
		cardsCount += tmpX;

		//Перечет карт воинов и спец карт
		if($(this).children('.content-card-item-main').attr('data-type') != 'special'){
			warriorsQuantity += tmpX;
		}else{
			specialQuantity += tmpX;
		}
		//пересчет карт-лидеров
		if($(this).children('.content-card-item-main').attr('data-leader') == '1'){
			leaderQuantity += tmpX;
		}
		//Вес колоды
		deckWeight += parseInt($(this).children('.content-card-item-main').attr('data-weight')) * tmpX;
	});

	//Подсчет лиги
	for(var i=0; i<window.leagues.length; i++){
		if(deckWeight >= window.leagues[i]['min_lvl']){
			league = window.leagues[i]['title'];
		}
	}
	$('.content-card-center-block .content-card-center-description-block .deck-card-sum').text(cardsCount);
	$('.content-card-center-block .deck-warriors .current-value').text(warriorsQuantity);
	$('.content-card-center-block .deck-special .current-value').text(specialQuantity);
	$('.content-card-center-block .deck-cards-power').text(deckWeight);
	$('.content-card-center-block .deck-league').text(league);
	$('.content-card-center-block .deck-liders .current-value').text(leaderQuantity);

	var arrays = sortSomeDeck('#sortableOne');
	for(var i in arrays.tempArrSpecial){
		$('#sortableOne').append(arrays.tempArrSpecial[i]);
	}
	for(var i in arrays.tempArrWarior){
		$('#sortableOne').append(arrays.tempArrWarior[i]);
	}

	arrays = sortSomeDeck('#sortableTwo');
	for(var i in arrays.tempArrWarior){
		$('#sortableTwo').append(arrays.tempArrWarior[i]);
	}
	for(var i in arrays.tempArrSpecial){
		$('#sortableTwo').append(arrays.tempArrSpecial[i]);
	}
}

function sortSomeDeck(side){
	var tempArrSpecial = [];
	var tempArrWarior = [];

	$(side+' li').each(function(){
		if($(this).find('.content-card-item-main').attr('data-type') == 'special'){
			tempArrSpecial.push($(this));
		}else{
			tempArrWarior.push($(this));
		}
	});

	tempArrWarior.sort(function(a, b){
		if(side == '#sortableOne'){
			return parseInt(a.find('.label-power-card-wrap').text()) - parseInt(b.find('.label-power-card-wrap').text());
		}else{
			return parseInt(b.find('.label-power-card-wrap').text()) - parseInt(a.find('.label-power-card-wrap').text());
		}
	});

	$(side).empty();
	return {
		'tempArrSpecial':tempArrSpecial,
		'tempArrWarior': tempArrWarior
	}
}
//отправка данных о колодах
//deck   - название колоды
//cardId - id карты
//source - панель колоды(левая правая)
function sendUserDeck(deck, cardId, source){
	var token = $('input[name=_token]').val();
	$.ajax({
		url:	'/change_user_deck',
		headers:{'X-CSRF-TOKEN': token},
		type:	'PUT',
		data:	{deck:deck, id:cardId, source:source},
		success:function(){
			//пересчет колоды
			recalculateDeck();
		},
		error: function (jqXHR, exception) {
			ajaxErrorMsg(jqXHR, exception);
		}
	});
}
//перетягивание
function clearDeck() {
	showPreloader();
	var race = $('.content-card-center .selection-rase select').val();
	var token = $('input[name=_token]').val();
	$.ajax({
		url:	'/clear_deck',
		headers:{'X-CSRF-TOKEN': token},
		type:	'PUT',
		data:	{deck:race},
		success:function(data){
			var res = JSON.parse(data);
			curentRaceURL = res.race_img;
			$('.content-card-field ul#sortableTwo, .content-card-field ul#sortableOne').empty();
			//Формирование доступных карт
			for(var i in res['available']){
				$('.content-card-field ul#sortableTwo').append(buildCardDeckView(res['available'][i], 'ul'));
			}
			//Пересчет данных колоды
			hidePreloader();
			recalculateDeck();
		},
		error: function (jqXHR, exception) {
			ajaxErrorMsg(jqXHR, exception);
		}
	});
}

function dblDraggCards() {
	$(document).on('dblclick', 'li.content-card-item', function (event) {
		if ((!$(event.target).hasClass('ignore')) && event.which == 1) {
			var curentCard = $(this); //текущая карта
			var curentCardID = parseInt(curentCard.attr('data-cardid'));
			var cardLeft = parseInt(curentCard.attr('data-cardleft')); //oсталось карт
			var counter = curentCard.find('.current-card-type-count');
			var targetdeck;
			if(curentCard.closest('ul').attr('id')=='sortableOne'){
				targetdeck = $('#sortableTwo');
			}else{
				targetdeck = $('#sortableOne');
			}

			moveCard(targetdeck);
			function moveCard(deck){
				if (deck.attr('id') == 'sortableOne') {var source = 'available'; }
				if (deck.attr('id') == 'sortableTwo') {var source = 'user_deck'; }
				var sameCard = deck.find('li[data-cardid ="'+curentCardID+'"]');

				var sameNowLeft = parseInt(sameCard.attr('data-cardleft'));
				var maxSameCard = parseInt(sameCard.find('.current-max-card-count').text());

				var specLeft = parseInt($('.content-card-center-block .deck-special .current-value').text());
				var specMax = parseInt($('.content-card-center-block .deck-special .min-value').text());

				var leadLeft = parseInt($('.content-card-center-block .deck-liders .current-value').text());
				var leadMax = parseInt($('.content-card-center-block .deck-liders .min-value').text());

				if((deck.attr('id') == 'sortableOne') ){ //если тянут карту в  игровую деку
					if(curentCard.children('.content-card-item-main').attr('data-type') == 'special'){
						if(specLeft >= specMax){
							resultPopupShow('Достигнут лимит количества специальных карт в колоде');
							return;
						}
					}
					if(curentCard.children('.content-card-item-main').attr('data-leader') == '1'){
						if(leadLeft >= leadMax){
							resultPopupShow('Достигнут лимит количества карт лидеров в колоде');
							return;
						}
					}
				}
				if(sameCard.length>0){
					if((deck.attr('id') == 'sortableOne') ){ //если тянут карту в  игровую деку
						if(sameNowLeft >= maxSameCard){
							resultPopupShow('Достигнут лимит количества данных карт в колоде');
							return;
						}
					}
					if(cardLeft <= 1 ){
						curentCard.remove();
					}else{
						var i = (parseInt(curentCard.attr('data-cardleft'))) - 1;
						curentCard.attr('data-cardleft',i);
						counter.text(i);
					}
					sameCard.attr('data-cardleft',(sameNowLeft + 1));
					sameCard.find('.current-card-type-count').text(sameNowLeft + 1);
				}else{
					var clone = curentCard.clone();
					clone.css('display','inline-block');
					clone.attr('data-cardleft',1);
					clone.find('.current-card-type-count').text(1);
					if(cardLeft <= 1 ){
						curentCard.remove();
					}else{
						curentCard.attr('data-cardleft',cardLeft-1);
						counter.text( curentCard.attr('data-cardleft'));
					}
					deck.append(clone);
				}
				var race = $('.content-card-center .selection-rase select').val();
				sendUserDeck(race, curentCardID, source);
			}
		}else{return;}
	});
}

function draggableCards() {
	$(document).on('mousedown', 'li.content-card-item', function (event) {
		var startTime = (new Date()).getTime();
		if((!$(event.target).hasClass('ignore')) && event.which==1) {
			var eventX = event.pageX;
			var eventY = event.pageY;
			var that =$(this);
			$(this).on('mouseup', function (e) {
				var elapsed = ((new Date()).getTime() - startTime);
				if(elapsed<300){
					clearTimeout(timeout);
				}
			});
			var timeout = setTimeout(function () {
				var offsetX = eventX - that.offset().left;
				var offsetY = eventY - that.offset().top;
				reset();

				var curentCard = that; //текущая карта
				var curentCardID = parseInt(curentCard.attr('data-cardid'));
				var cardLeft = parseInt(curentCard.attr('data-cardleft')); //oсталось карт
				var curentCardHTML = curentCard.html(); //разметка текущей карты
				var fakeWrap = document.createElement('div'); // псевдополе
				var fakeCard = document.createElement('div'); // псевдокарта
				var container = $('.content-card-field'); //контейнер дек
				var currentDeck, endDeck, succesDeck;
				var counter = curentCard.find('.current-card-type-count');

				var leftContainer = container.find('.content-card-left').outerWidth(); // ширина левой деки
				var rightContainer = container.find('.content-card-right').outerWidth(); // ширина правой деки
				var centerContainer = container.find('.content-card-field-center').outerWidth(); // ширина инфополя
				if((eventX - container.offset().left)<=leftContainer){ // вычисление стартовой деки
					currentDeck = 0;
					succesDeck = $('.content-card-right ul');
				}else if((eventX - container.offset().left)>=leftContainer+centerContainer ){
					currentDeck = 1;
					succesDeck = $('.content-card-left ul');
				}

				fakeCard = $(fakeCard);
				fakeCard.addClass('fake-card').html(curentCardHTML);
				fakeCard.find('.maxCountInDeck-wrap').remove();
				fakeWrap = $(fakeWrap);
				fakeWrap.addClass('fake-wrap').append(fakeCard).appendTo('.content-card-field');
				var mx = eventX - container.offset().left-offsetX;
				var my = eventY - container.offset().top-offsetY;
				fakeCard.css({
					left: mx,
					top: my,
					opacity: 1
				});
				if(cardLeft <= 1 ){
					curentCard.css('display','none')
				}else{
					var i = (parseInt(curentCard.attr('data-cardleft'))) - 1;
					curentCard.attr('data-cardleft',i);
					counter.text(i);
				}

				container.on("mousemove", function (e) {
					var x = e.offsetX == undefined ? e.layerX : e.offsetX;
					var y = e.offsetY == undefined ? e.layerY : e.offsetY;
					fakeCard.css({
						left: (x - offsetX),
						top: (y - offsetY),
						opacity: 1
					});
				});
				fakeWrap.on('mouseup', function (e) {
					if((e.pageX - container.offset().left)<=leftContainer){ // вычисление конечной деки
						endDeck = 0;
					}else if((e.pageX - container.offset().left)>=leftContainer+centerContainer ){
						endDeck = 1;
					}
					if(endDeck == currentDeck){
						cancelDrag();
					}else if(Math.abs(endDeck - currentDeck) == 1){
						successDrag(succesDeck);
					}else{
						cancelDrag();
					}
				});

				function successDrag(deck) {
					if (deck.attr('id') == 'sortableOne') {     var source = 'available';  }
					if (deck.attr('id') == 'sortableTwo') {var source = 'user_deck'; }
					var sameCard = deck.find('li[data-cardid ="'+curentCardID+'"]');

					var sameNowLeft = parseInt(sameCard.attr('data-cardleft'));
					var maxSameCard = parseInt(sameCard.find('.current-max-card-count').text());

					var specLeft = parseInt($('.content-card-center-block .deck-special .current-value').text());
					var specMax = parseInt($('.content-card-center-block .deck-special .min-value').text());

					var leadLeft = parseInt($('.content-card-center-block .deck-liders .current-value').text());
					var leadMax = parseInt($('.content-card-center-block .deck-liders .min-value').text());

					if((deck.attr('id') == 'sortableOne') ){ //если тянут карту в  игровую деку
						if(curentCard.children('.content-card-item-main').attr('data-type') == 'special'){
							if(specLeft >= specMax){
								resultPopupShow('Достигнут лимит количества специальных карт в колоде');
								cancelDrag();
								return;
							}
						}
						if(curentCard.children('.content-card-item-main').attr('data-leader') == '1'){
							if(leadLeft >= leadMax){
								resultPopupShow('Достигнут лимит количества карт лидеров в колоде');
								cancelDrag();
								return;
							}
						}
					}
					if(sameCard.length>0){
						if((deck.attr('id') == 'sortableOne') ){ //если тянут карту в  игровую деку
							if(sameNowLeft >= maxSameCard){
								resultPopupShow('Достигнут лимит количества данных карт в колоде');
								cancelDrag();
								return;
							}
						}
						if(cardLeft <= 1 ){ curentCard.remove();   }
						sameCard.attr('data-cardleft',(sameNowLeft + 1));
						sameCard.find('.current-card-type-count').text(sameNowLeft + 1);
					}else{
						var clone = curentCard.clone();
						clone.css('display','inline-block');
						clone.attr('data-cardleft',1);
						clone.find('.current-card-type-count').text(1);
						if(cardLeft <= 1 ){
							curentCard.remove();
						}else{
							curentCard.attr('data-cardleft',cardLeft-1);
							counter.text( curentCard.attr('data-cardleft'));
						}
						deck.append(clone);
					}
					var race = $('.content-card-center .selection-rase select').val();
					sendUserDeck(race, curentCardID, source);
					reset();
				}
				function cancelDrag() {
					curentCard.attr('data-cardleft',cardLeft);
					counter.text(cardLeft);
					curentCard.css('display','inline-block');
					reset();
				}
				function reset() {
					$('.fake-card').remove();
					$('.fake-wrap').remove();
				}
			},300);
		}else{return;}
	});
}
//end of /deck
/** /market*/

//Пользователь хочет купить карту
function userByingCard(){
	$('.content-card-wrap-main .market-card-item-buy').on('click', '.button-buy', function(e){
		e.preventDefault();
		closeAllTrollPopup();
		var id = $(this).parents('.market-cards-item').attr('data-card');
		var buyType = $(this).attr('id');
		var available = $(this).parents('.market-cards-item').find('.available');
		$.ajax({
			url:	'/get_card_data',
			type:	'GET',
			data:	{card_id: id, buy_type:buyType},
			success:function(data){
				var res = JSON.parse(data);
				if(res['message'] == 'success'){
					var conf = $('#confirm-popup-with-number-input');
					var butts = conf.find('.button-troll');
					conf.find('.confirm-txt').text('Сколько Вы хотите купить карт " '+res['title']+'"?');
					$('#confirm-popup-with-number-input input[name=quant]').val(1);
					openTrollPopup(conf);
					butts.unbind();
					butts.click(function (e) {
						e.preventDefault();
						var result = $(this).data('value');
						closeAllTrollPopup();
						if(result == true){
							var token = $('#buyingCardOrmagic input[name=_token]').val();
							var quant = parseInt($('#confirm-popup-with-number-input input[name=quant]').val());
							if (quant >= 1){
								$.ajax({
									url:	'/card_buying',
									type:	'PUT',
									headers:{'X-CSRF-TOKEN': token},
									data:	{card_id: id, buy_type:buyType, quant:quant},
									success:function(data){
										var res = JSON.parse(data);
										if(res['message'] == 'success'){
											refreshRosources(res);
											var x = parseInt(available.text());
											x+=quant;
											available.text(x);
											resultPopupShow('Успешная покупка карты: "'+res['title']+'". Количество: '+quant);
										}else{
											resultPopupShow(res['message']);
										}
									}
								});
							}else{
								closeAllTrollPopup();
								resultPopupShow('Карты не куплены.');
							}
							//end ajax card_is_buyed
						}
					});
				}else{
					resultPopupShow(res['message']);
				}
			}
		});
		//end ajax get_card_data
	});
}
//Украшение селекта рас
function marketSelection(){
	if($('.selection-rase select').length > 0){
		$('.selection-rase select').styler({
			selectSmartPositioning:'-1'
		});
	}
}
//end of /market
/**	Magic*/
//Создание отображения таблицы "Волшебства" :3
function buildMagicEffectsView(data){
	return '<tr>' +
	'<td class="no-border"><a href="#" class="button-plus" data-type="' + data['id'] + '"></a></td>' +
	'<td class="effect-img"><img src="img/card_images/' + data['img_url'] + '" alt="" /></td>' +
	'<td class="effect-title">' + data['title'] + '</td>' +
	'<td class="effect-descript">' + data['descr'] + '</td>' +
	'<td class="energy-effect">' + data['energy'] + '</td>' +
	'<td class="gold-tableCell">' + data['gold'] + '</td>' +
	'<td class="silver-tableCell">' + data['silver'] + '</td>' +
	'<td class="market-status-wrap done"><div class="market-status ' + data['status'] + '"><span></span></div></td>' +
	'<td class="effect-date">' + data['used_times'] + '</td>' +
	'</tr>';
}
//пользователь покупает волшебство
function userByingMagic(){
	$('.main-table tr td .button-plus').click(function(e){
		e.preventDefault();
		var id = $(this).attr('data-type');
		$.ajax({
			url:	'/get_magic_effect_data',
			type:	'GET',
			data:	{magic_id:id},
			success:function(data){
				var res = JSON.parse(data);
				var conf = $('#confirm-popup');
				var butts = conf.find('.button-troll');
				var result;
				conf.find('.confirm-txt').text('Вы действительно хотите купить карту '+res['title']+'?');
				openTrollPopup(conf);
				butts.unbind();
				butts.click(function (e) {
					e.preventDefault();
					result = $(this).data('value');
					closeAllTrollPopup();
					if(result == true){
						var token = $('#buyingCardOrmagic input[name=_token]').val();
						res['user_gold'] = parseInt(res['user_gold']);
						res['user_silver'] = parseInt(res['user_silver']);
						res['price_gold'] = parseInt(res['price_gold']);
						res['price_silver'] = parseInt(res['price_silver']);
						if( (res['user_gold'] < res['price_gold']) || (res['user_silver'] < res['price_silver']) ){
							openTrollPopup('Недостаточно средств');
						}else{
							$.ajax({
								url:    '/magic_is_buyed',
								type:   'POST',
								headers:{'X-CSRF-TOKEN': token},
								data:   {magic_id: id},
								success:function(data){
									var res = JSON.parse(data);
									if(res['message'] == 'success'){
										$('.main-table tr a[data-type="'+id+'"]').parent().parent().children('.market-status-wrap').children('.market-status').removeClass('disabled');
										$('.main-table tr a[data-type="'+id+'"]').parent().parent().children('.effect-date').html(res['date']);
										refreshRosources(res);
										resultPopupShow('Волшебство '+res['title']+' стала доступным.');
									}
								}
							});
							//end ajax magic_is_buyed
						}
					}
				});
			},
			error: function (jqXHR, exception) {
				ajaxErrorMsg(jqXHR, exception);
			}
		});
		//end ajax get_magic_effect_data
	});
}
//Пользователь меняет статус активности волшебства
function userChangesMagicEffectStatus(){
	$('.main-table .market-status-wrap .market-status').click(function() {
		if (!$(this).hasClass('disabled')) {
			var status_id = $(this).parents('tr').children('.no-border').children('.button-plus').attr('data-type');
			var token = $('#buyingCardOrmagic input[name=_token]').val();
			var is_active = $(this).hasClass('active');

			showPreloader();
			$.ajax({
				url: '/magic_change_status',
				type: 'PUT',
				headers: {'X-CSRF-TOKEN': token},
				data: {status_id: status_id, is_active: is_active},
				success: function (data) {
					hidePreloader();
					var res = JSON.parse(data);
					if (res[0] == 'success') {
						if (res[1] == 0) {
							$('.main-table tr .no-border a[data-type="' + status_id + '"]').parent().parent().children('.market-status-wrap').children('.market-status').removeClass('active');
						} else {
							$('.main-table tr .no-border a[data-type="' + status_id + '"]').parent().parent().children('.market-status-wrap').children('.market-status').addClass('active');
						}
					}
					if (res[0] == 'too_much') {
						showErrorMessage('<p>Разрешается использовать только ТРИ активных волшебства.</p>');
					}
					if (res[0] == 'deny_by_league') {
						showErrorMessage('<p>Данное волшебство не доступно для текущей лиги</p>');
					}
				},
				error: function (jqXHR, exception) {
					ajaxErrorMsg(jqXHR, exception);
				}
			});
			//end ajax magic_change_status
		}
	});
}
//end of /magic

/** Общие методы*/

//Возвращает карты/волшебство в зависимости от расы
function getCardsByRace(fraction){
	switch($('.market-page').attr('id')){
		case 'market':	var url = '/get_cards_by_fraction'; break;
		case 'magic':	var url = '/get_magic_by_fraction'; break;
	}
	$.ajax({
		url:	url,
		type:	'GET',
		data:	{fraction:fraction},
		success:function(data){
			var res = JSON.parse(data);
			curentRaceURL = res['race_img'];
			switch($('.market-page').attr('id')){
				case 'market':
					$('.market-selection .select-rase-img, .content-card-field-wrap .market-cards-items-wrap').empty();
					for(var i=0; i<res['cards'].length; i++){
						$('.content-card-field-wrap .market-cards-items-wrap').append(buildCardDeckView(res['cards'][i], 'div'));
					}
					userByingCard();
				break;
				case 'magic':
					$('.content-card-field-wrap .main-table>tbody>tr').remove();
					for(var i=0; i<res['effects'].length; i++){
						$('.content-card-field-wrap .main-table>tbody').append(buildMagicEffectsView(res['effects'][i]));
					}
					userByingMagic();
					userChangesMagicEffectStatus();
				break;
			}
			if(res['race_img'] != ''){
				$('.market-selection .select-rase-img').append('<img src="img/fractions_images/' + res['race_img'] + '" alt="">');
			}
		},
		error: function (jqXHR, exception) {
			ajaxErrorMsg(jqXHR, exception);
		}
	})
}
//Функция обновления значений цены usd в золото
function eventsToRefreshGoldPrices(input){
	input.change(function(){refreshGoldPrices();});
	input.keyup(function(){refreshGoldPrices();});
	input.keydown(function(){refreshGoldPrices();});
}
//Функция обновления значений цены золото в usd
function eventsToRefreshUsdPrices(input){
	input.change(function(){refreshUsdPrices();});
	input.keyup(function(){refreshUsdPrices();});
	input.keydown(function(){refreshUsdPrices();});
}
//Функция обновления значений цены золото в серебро
function eventsToRefreshSilverPrices(input){
	input.change(function(){refreshSilverPrices();});
	input.keyup(function(){refreshSilverPrices();});
	input.keydown(function(){refreshSilverPrices();});
}

function refreshGoldPrices(){
	var goldValue = parseInt($('#buySomeGold input[name=goldToBuy]').val());
	var usdValue = (goldValue / window.exchange_gold).toFixed(2);
	$('#buySomeGold input[name=goldToUsd]').val(usdValue);
	$('#buySomeGold .error').removeClass('show');
}

function refreshUsdPrices(){
	var usdValue = parseInt($('#buySomeGold input[name=goldToUsd]').val());
	var goldValue = (usdValue * window.exchange_gold).toFixed(2);
	$('#buySomeGold input[name=goldToBuy]').val(goldValue);
	$('#buySomeGold .error').removeClass('show');
}

//Функция обновления значений ресурсов пользователя
function refreshRosources(resources){
	if(resources['gold'] != 'undefined') $('.rating .resurses .gold').text(resources['gold']);
	if(resources['silver'] != 'undefined') $('.rating .resurses .silver').text(resources['silver']);
	if(resources['energy'] != 'undefined') $('.rating .resurses .lighting').text(resources['energy']);
}
function refreshSilverPrices(){
	var goldValue = parseInt($('.market-buy-popup input[name=goldToSell]').val());
	if( Number.isInteger(goldValue) ){
		var silverToBuy = parseInt(goldValue * window.gold_to_silver);
		$('#buySomeSilver #silverToBuy').text(silverToBuy);
		$('#buySomeSilver .error').removeClass('show');
		if(goldValue != 0){
			$('#buySomeSilver .button-troll').removeClass('unactive');
		}else{
			$('#buySomeSilver .button-troll').addClass('unactive');
		}
	}else{
		$('#buySomeSilver #silverToBuy').text('0');
		$('#buySomeSilver .error').addClass('show');
		$('#buySomeSilver .button-troll').addClass('unactive');
	}
}
function closeAllTrollPopup(){
	$('div.troll-popup').removeClass('show');
	$('.new-popups-block').removeClass('show');
}
//Покупка Серебра
function showSilverBuyingPopup(){
	$(document).on('click', '.buy-more-silver', function(e){
		e.preventDefault();
		closeAllTrollPopup();
		openTrollPopup($('#buySomeSilver'));
		$('#buySomeSilver .button-troll').addClass('unactive');
		$('#buySomeSilver .button-troll').click(function(e){
			e.preventDefault();
			var goldToSell = parseInt($('#buySomeSilver input[name=goldToSell]').val());
			$.ajax({
				url:	'/user_buying_silver',
				type:	'PUT',
				headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
				data:	{gold:goldToSell},
				success:function(data){
					var res = JSON.parse(data);
					if(res['message'] == 'success'){
						$('#buySomeSilver input[name=goldToSell]').val(0);
						$('#buySomeSilver #silverToBuy').text('0');
						resultPopupShow('Успешный обмен');
						refreshRosources(res);
					}else{
						resultPopupShow(res['message']);
					}
				},
				error: function (jqXHR, exception) {
					ajaxErrorMsg(jqXHR, exception);
				}
			});
		});
		$('#buySomeSilver .clckAnim').click(function () {
			refreshSilverPrices();
		});
		refreshSilverPrices();
	});
}
//Покупка энергии
function showEnergyBuyingPopup(){
	$(document).on('click', '.buy-more-energy', function(e) {
		e.preventDefault();
		closeAllTrollPopup();
		$.ajax({
			url:	'/check_user_playing_status',
			type:	'GET',
			success:function (data) {
				if(data != 0){
					var res = JSON.parse(data);
					showErrorMessage(res['message']);
				}else{
					openTrollPopup($('#buySomeEnergy'));
					$('#buySomeEnergy .button-troll').unbind();
					$('#buySomeEnergy .button-troll').click(function(){
						var payType = $(this).data('name');
						$.ajax({
							url:    '/user_buying_energy',
							type:   'PUT',
							headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
							data:   {pay_type:payType},
							success:function(data){
								var res = JSON.parse(data);
								if(res['message'] == 'success'){
									refreshRosources(res);
									resultPopupShow('Успешный обмен');
								}else{
									resultPopupShow(res['message']);
								}
							},
							error: function (jqXHR, exception) {
								ajaxErrorMsg(jqXHR, exception);
							}
						})
					});
				}
			},
			error: function (jqXHR, exception) {
				ajaxErrorMsg(jqXHR, exception);
			}
		});
	});
}
//Покупка золота
function showGoldBuyingPopup(){
	$(document).on('click', '.buy-more-gold', function(event){
		event.preventDefault();
		closeAllTrollPopup();
		$.ajax({
			url:	'/check_user_playing_status',
			type:	'GET',
			success:function(data){
				if(data != 0){
					var res = JSON.parse(data);
					showErrorMessage(res['message']);
				}else{
					openTrollPopup($('#buySomeGold'));
					$('#buySomeGold .button-troll').click(function(e){
						e.preventDefault();
						$.ajax({
							url:	'/create_payment',
							type:	'POST',
							headers:{'X-CSRF-TOKEN': $('#buyingCardOrmagic input[name=_token]').val()},
							data:	{
								money: $('#buySomeGold input[name=goldToUsd]').val(),
								type: $('#buySomeGold input[name=paymentType]:checked').val()
							},
							success:function(data){
								try{
									data = JSON.parse(data);
									if(data['message'] == 'success'){
										location = '/transactions/'+data['transaction'];
									}
								}catch(e){}
							}
						});
					});
					$('#buySomeGold .clckAnim').click(function () {
						if($(this).parent('.input-type-number').find('input[name=goldToUsd]').length > 0){
							refreshUsdPrices();
						}else{
							refreshGoldPrices();
						}
					});
					refreshGoldPrices();
					refreshUsdPrices();
				}
			},
			error: function (jqXHR, exception) {
				console.log(jqXHR.responseText())
				ajaxErrorMsg(jqXHR, exception);
			}
		});
	});
}
// клик по кнопке анимация
function animationButtonClick() {
	var protect = false;
	if(protect == false) {
		$('.clckAnim').mousedown(function () {
			protect = true;
			$(this).addClass('clicked');
			$(this).mouseup(function () {
				$(this).removeClass('clicked');
				protect = false;
			});
		});
	}
}

function incrementDecrementInputNumber() {
	$('.input-type-number').each(function () {
		var input = $(this).find('input');
		var step = (typeof input.attr('step') != 'undefined')? parseInt(input.attr('step')): 1;
		var min = (typeof input.attr('min') != 'undefined')? parseInt(input.attr('min')): 1;
		$(this).find('.increment').click(function (){
			var x = parseInt(input.val());
			x = x + step;
			input.val(x);
		});
		$(this).find('.decrement').click(function (){
			var x = parseInt(input.val());
			if((x - step) >= min){
				x = x - step;
				input.val(x);
			}else{
				input.val(min);
			}
		});
		input.keydown(function(event) { // Разрешаем: backspace, delete, tab и escape Разрешаем: Ctrl+A Разрешаем: home, end, влево, вправо
			if( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || (event.keyCode == 65 && event.ctrlKey === true) || (event.keyCode >= 35 && event.keyCode <= 39)){
				return;
			}else{
				if((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )){event.preventDefault();}
			}
		});
	});
}
function openTrollPopup(popup){
	popup.addClass('show');
	$('.new-popups-block').addClass('show');
}
//попап результатов
function resultPopupShow(message){
	$('#successEvent').find('.result').html(message);
	openTrollPopup($('#successEvent'));
	setTimeout(function () {
		$('#successEvent').removeClass('show');
		if(!$('div.troll-popup').hasClass('show')){
			$('.new-popups-block').removeClass('show');
		}
	}, 3000);
}
function showErrorMessage(message){
	hidePreloader();
	$('#buyingCardOrmagic .popup-content-wrap').html('<p>' + message + '</p>');
	openTrollPopup($('#buyingCardOrmagic'));
	setTimeout(function () {
		$('#buyingCardOrmagic').removeClass('show');
		if(!$('div.troll-popup').hasClass('show')){
			$('.new-popups-block').removeClass('show');
		}
	}, 3000);
}
function showPreloader() {
	$('.afterloader').css({'opacity':'1', 'z-index':'2222'});

}
function hidePreloader() {
	$('.afterloader').css({'opacity':'0', 'z-index':'-1'});
}
//покупка премиум аккаунта
$(document).on('click','#buyingPremium a.button-troll',function(e){
	e.preventDefault();
	var premiumType = $('#buyingPremium input[name=premiumType]:checked').val();
	if(premiumType != undefined){
		$.ajax({
			url:	'/user_buying_premium',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
			beforeSend: function(){
				showPreloader()
			},
			data:	{premiumType:premiumType},
			success:function(data){
				var res = JSON.parse(data);
				hidePreloader();
				if(res['message'] == 'success'){
					location.reload(true);
				}else{
					resultPopupShow(res['message']);
				}
			}
		});
	}
});
//Вывод Колод для игры
function showUserDecks(){
	hidePreloader();
	$('#choose-rase-block .button-troll').click(function(e){
		e.preventDefault();
		var fraction = $(this).data('name');
		$('#choose-rase-block #gameForm input[name=currentRace]').val(fraction);
		$.ajax({
			url:	'/validate_deck',
			type:	'GET',
			beforeSend: function(){
				showPreloader()
			},
			data:	{fraction:fraction},
			success:function(data){
				var res = JSON.parse(data);
				switch(res['message']){
					case 'success':		$('#choose-rase-block #gameForm').submit(); break;
					case 'in_battle':	location = '/play/'+res['room']; break;
					default:
						resultPopupShow(res['message']);
						hidePreloader();
				}
			},
			error: function (jqXHR, exception) {
				ajaxErrorMsg(jqXHR, exception);
				hidePreloader();
			}
		});
	});
}
//Присоединение к игре
function userConnectToGame(){
	$('.tables-list').on('click', 'a.play-game', function(e){
		e.preventDefault();
		showPreloader();
		var id = $(this).attr('id');
		$.ajax({
			url:	'/user_connect_to_battle',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
			data:	{id:id},
			success:function(data){
				var res = JSON.parse(data);
				if(res['message'] == 'success'){
					location = '/play/'+id;
				}else{
					showErrorMessage(res['message']);
				}
			},
			error: function (jqXHR, exception) {
				ajaxErrorMsg(jqXHR, exception);
			}
		});
	});
}
function array_unique( inputArr ) {
	var result = [];
	$.each(inputArr, function(i, el){
		if($.inArray(el, result) === -1) result.push(el);
	});
	return result;
}
function sidebarPlay() {
	$('#start-game').click(function (event) {
		event.preventDefault();
		closeAllTrollPopup();
		openTrollPopup($('#choose-rase-block'));
	});
}
//Начальная загрузка расы колоды/волшебства
function initSelectDec() {
	var slct =$('.content-card-center .selection-rase select');
	if(slct.length > 0){
		marketSelection();
		getUserDeck(slct.val());
		slct.change(function(){getUserDeck(slct.val()); });
	}
}
function changeChekInputInFilterDeck(){
	$('.filter-decks input').on('change', function() {
		var  race = $('.selection-rase-wrap select').val();
		$('.filter-decks input').each(function () {
			if($(this).attr('data-card-type') == 'special'){
				if($(this).prop('checked')){
					$('.content-card-item .special-type').parent().css('display','inline-block');
				}else{
					$('.content-card-item .special-type').parent().css('display','none');
				}
			}
			if($(this).attr('data-card-type') == 'neutral'){
				if($(this).prop('checked')){
					$('.content-card-item .neutrall-race').parent().css('display','inline-block');
				}else{
					$('.content-card-item .neutrall-race').parent().css('display','none');
				}
			}
			if($(this).attr('data-card-type') == 'fraction'){
				if($(this).prop('checked')){
					$('.content-card-item .'+race+'-race').parent().css('display','inline-block');
				}else{
					$('.content-card-item .'+race+'-race').parent().css('display','none');
				}
			}
		});
	});
}
//ranking page------------------------START----------------------------------------
	//document ready starter for rankin page
	function rankingPageStarter() {
		initJSP();
		rankTabs();
		wheel();
	}
	//ranking tabs
	function rankTabs(){
		$('.ranking-page-buttons-league li').click(function () {
			showPreloader();
			$('.ranking-page-table-content li.pseudo').html('').removeClass('stand-on-bottom stand-on-top');
			autoWidth();
			var that =$(this);
			var league = $(this).attr('data-league');
			var ind = $(this).index()+1;
			if(!$(this).hasClass('loaded')){
				$.ajax({
					url:	'/user_rating',
					type:	'GET',
					data:	{league:league},
					success:function(data){
						var res = JSON.parse(data);
						if(res['message'] == 'success'){
							$('.ranking-page-buttons-league li').removeClass('active');
							$('.ranking-page-buttons-league li').eq(ind-1).addClass('active');
							constructRating(res['users'], ind);
							that.addClass('loaded');
						}else{
							showErrorMessage(res['message']);
							hidePreloader();
							return;
						}
					},
					error: function (jqXHR, exception) {
						ajaxErrorMsg(jqXHR, exception);
						hidePreloader();
						return;
					}
				});
			}else{
				$('.ranking-page-buttons-league li').removeClass('active');
				$('.ranking-page-table-content li').removeClass('active');
				$('.ranking-page-table-head-container').removeClass('active');
				$('.ranking-page-table-head-container').eq(ind-1).addClass('active');
				$(this).addClass('active');
				$('.ranking-page-table-content li').eq(ind).addClass('active');
				hidePreloader();
			}
			checkPseudo();
		});
	}
	//END ranking tabs
	// construct rating
	function constructRating(data, ind) { //создание таблицы рейтинга
		var topContent="";
		var botContent="";
		for (var i = 0; i < data.length; i++) {
			if(i<3){
				topContent += constructRows(data[i]);
			}else{
				botContent += constructRows(data[i]);
			}
			$('.ranking-page-table-head-container').removeClass('active');
			$('.ranking-page-table-head-container').eq(ind-1).addClass('active').html(topContent);
			$('.ranking-page-table-content li').removeClass('active');
			$('.ranking-page-table-content li').eq(ind).addClass('active').html(botContent);
			setTimeout(function () {
				initJSP();
			},300);
		}
	}
	function constructRows(data) {//создание ряда рейтинга
		var content ='<div class="ranking-page-table-row cfix';
		if(data['is_active'] != undefined){
			content +=' active'
		}
		content +='">' +
			'<div class="cell place">';
		switch(data['position']) {
			case 1:
				content+='<img src="/images/1st-place.png" alt="">';
				break;
			case 2:
				content+='<img src="/images/2nd-place.png" alt="">';
				break;
			case 3:
				content+='<img src="/images/3rd-place.png" alt="">';
				break;
			default:
				content += data['position'];
				break;
		}
		content +='</div>' +
			'<div class="cell name">'+data['login']+'</div>' +
			'<div class="cell battles">'+data['games']+'</div>' +
			'<div class="cell percent">'+data['wins_percent']+'&nbsp;%</div>' +
			'<div class="cell ranking">'+data['rating']+'</div>' +
			'</div>';
		return content;
	}
	// END construct rating
var atBot, atTop, api;
var ajaxloaderFlagTop = false;
var ajaxloaderFlagBot = false;
var start = false;
	//jscrollPane init

	function wheel() {
		var box = document.querySelector('.ranking-page-table-content');
		if (box.addEventListener) {
			// IE9, Chrome, Safari, Opera
			box.addEventListener("mousewheel", MouseWheelHandler, false);
			// Firefox
			box.addEventListener("DOMMouseScroll", MouseWheelHandler, false);
		}
		// IE 6/7/8
		else box.attachEvent("onmousewheel", MouseWheelHandler);
		function MouseWheelHandler(e) { // подгрузка по скролу
			var league = $('.ranking-page-table-content li.active').attr('data-league');
			var direction, position;
			if (!ajaxloaderFlagBot && atBot) {
				ajaxloaderFlagBot = true;
				direction = 1;
				position = parseInt($('.ranking-page-table-content li.active .ranking-page-table-row:last-child .place').text());
				$.ajax({
					url: '/user_rating_scroll',
					type: 'GET',
					data: {
						league: league,
						direction: direction,
						position: position
					},
					success: function (data) {
						var res = JSON.parse(data);
						if (res['message'] == 'success') {
							var content = '';
							for (var i = 0; i < res['users'].length; i++) {
								content += constructRows(res['users'][i]);
							}
							$('.ranking-page-table-content li.active .jspPane').append(content);
							api.reinitialise();
							ajaxloaderFlagBot = false;
						} else {
							showErrorMessage(res['message']);
						}
					},
					error: function (jqXHR, exception) {
						ajaxErrorMsg(jqXHR, exception);
					}
				});
			}
			if(!ajaxloaderFlagTop && atTop){
				ajaxloaderFlagTop = true;
				direction = 0;
				position = parseInt($('.ranking-page-table-content li.active .ranking-page-table-row:first-child .place').text());
				$.ajax({
					url:	'/user_rating_scroll',
					type:	'GET',
					data:	{league:league, direction:direction, position:position},
					success:function(data){
						var res = JSON.parse(data);

						if(res['message'] == 'success'){
							var j = 0;
							var content = '';
							for (var i = 0; i < res['users'].length; i++) {
								content += constructRows(res['users'][i]);
								j++;
							}
							$('.ranking-page-table-content li.active .jspPane').prepend(content);
							api.reinitialise();
							api.scrollToY(j*50);
							ajaxloaderFlagTop = false;
						}else{
							showErrorMessage(res['message']);
						}
					},
					error: function (jqXHR, exception) {
						ajaxErrorMsg(jqXHR, exception);
					}
				});
			}
		}
	}

	function initJSP() {
		checkPseudo();
		$('.ranking-page-table-content li.active').each(function(){
			$(this).jScrollPane({
				showArrows: $(this).is('.arrow')
			});
			api = $(this).data('jsp');
			var throttleTimeout;
			$(this).bind('jsp-user-scroll-y',function(event, destTop, isAtTop, isAtBottom) {
				atBot = isAtBottom;
				atTop = isAtTop;
				checkPseudo();
			});
			autoWidth();
			$(window).bind('resize', function(){
				if (!throttleTimeout) {
					throttleTimeout = setTimeout(function(){
						api.reinitialise();
						autoWidth();
						throttleTimeout = null;
					}, 50);
				}
			});
		});
		hidePreloader();
	}
	//END jscrollPane init

	function checkPseudo() {
		$('.ranking-page-table-content li.pseudo').html('').removeClass('stand-on-bottom stand-on-top');
		if($('.ranking-page-table-content li.active .ranking-page-table-row.active').length>0){
			var container = $('.ranking-page-table-content li.active');
			var elem = $('.ranking-page-table-content li.active .ranking-page-table-row.active');
			var act = $('.ranking-page-table-content li.pseudo');
			act.html(" ").removeClass('stand-on-bottom stand-on-top');
			act.append(elem.clone().removeClass('active'));
			var containerTop = container.offset().top;
			var containerBot = containerTop + container.outerHeight();
			var elemTop = elem.offset().top;
			var elemBot = elemTop + elem.outerHeight();
			if(elemTop<=containerTop){
				act.addClass('stand-on-top');
			}else{
				act.removeClass('stand-on-top');
			}
			if(elemBot >= containerBot){
				act.addClass('stand-on-bottom');
			}
		}
	}
	function autoWidth() {
		var wid = $('.ranking-page-table-content li.active .ranking-page-table-row:first-child').width();
		$('.ranking-page-table-head').width(wid);
		$('.ranking-page-table-content li.pseudo').width(wid);
	}
//ranking page------------------------END----------------------------------------

function birdthDatePicker() {
	if($( "#datepicker" ).length>0){$( "#datepicker" ).datepicker();}
}

// Попап при клике на иконку расы (в магазине и в волшебстве)
function showPopRaseInfo() {
	var ajaxVariable = null;
	$(document).on('click','.selection-rase-img',function (e) {
		e.preventDefault();

		if ( ajaxVariable !== null ) {
			ajaxVariable.abort();
		}

		ajaxVariable = $.ajax({
			url:	'/get_fraction_description',
			type:	'GET',
			data:	{fraction:$('select.selection-rase-select').val()},
			success:function(data){
				data = JSON.parse(data);
				$('#card-info .content-card-info').empty();
				var pageId = $('.market-page').attr('id');

				var popupText = (pageId == 'market') ? data.shop_text : data.magic_text;

				var popup = $('#card-info .content-card-info');

				var popMarkup = '<div class="chose-rase-popup">'+
									'<div class="top-img"><img src="'+data.img_url+'" alt="" ></div>'+
									'<div class="description">'+
										'<div class="title-rase">'+data.title+'</div>'+
										'<div class="des-text">'+popupText+'</div>'+
									'</div>'+
								'</div>';

				popup.append(popMarkup);

				$('.chose-rase-popup .description').jScrollPane();

				openTrollPopup($('#card-info'));
			}
		});

	});
}


$(document).ready(function(){
	if( (!$('.login-page').length>0) && (!$('.registration-main-page').length > 0) ) getUserData();  //Получить данные пользователя (по идее должна не работать только после логинизации)
	showFormOnMain();                       //Украшение формы логина на главной
	showWindowAboutOnMain();                //Кнопка "ОБ ИГРЕ" на главной
	fancyboxForm();
	settingsInputFile();                    //Страница "Настройки". Украшение файл приемника
	initScrollpane();                       //Инициализация скролла на страницах "Мои карты", "Магазин", ("Волшебство не проверялось")
	draggableCards();                       //Инициализация перетягивания карт
	underDragCardFix();                     //Фикс перетягивания
	incrementDecrementInputNumber();
	if($('.ranking-page').length>0){rankingPageStarter();} //инициализация скриптов страницы рейтинга
	showGoldBuyingPopup();
	sidebarPlay();
	showSilverBuyingPopup();
	showEnergyBuyingPopup();
	clickOnRace();
	clickCloseCross();
	clickOnLicenseAgree();
	animationButtonClick();
	eventsToRefreshSilverPrices($('.market-buy-popup input[name=goldToSell]'));
	eventsToRefreshGoldPrices($('.market-buy-popup input[name=goldToBuy]'));
	eventsToRefreshUsdPrices($('.market-buy-popup input[name=goldToUsd]'));
	showUserDecks();
	userConnectToGame();
	initSelectDec();
	infoCardStart();
	radioPseudo2();
	birdthDatePicker();
	dblDraggCards();
	changeChekInputInFilterDeck();
	showPopRaseInfo();
	if($('a.log_out_menu').length > 0){logoutUser();}
	$('.male-select').styler({
		selectPlaceholder: 'Выбор фракции'
	});
	$('.rubric-select').styler({
		selectPlaceholder: 'Тип вопроса'
	});
	//вычисление количества активных пользователей на сайте
	setInterval(function(){
		$.get('/get_user_quantity', function(data){
			$('.people-box .preload-peoples img').hide();
			$('.people-box .people').css('opacity', '1').text(data);
		});
	},15000);
	// отключение перетаскивания картинок
	$("img").mousedown(function(){return false;});
	//Украшение селекторов

	if($('.settings-page-wrap .settings-page select').length > 0){
		$('.settings-page select').styler();
	}
	if($('.content-card-top .market-selection select').length > 0){
		marketSelection();
		getCardsByRace($('.content-card-top .market-selection select').val());
		$('.content-card-top .market-selection select').change(function(){
			getCardsByRace($(this).val());
		});
	}
	//Изменение настроек пользоателя
	applySettings();
	//Изменение картинки
	settingUpdateImg();
	//Выбор расы колоды/волшебства

	//PREMIUM
	$('.button-PRO-convert a.button-push').click(function(e){
		e.preventDefault();
		openTrollPopup($('#buyingPremium'));
	});

	//Закрытие popup-окна
	$(document).on('click', '.close-popup', function(){
		$(this).parent().hide();
	});
	//пользователь создает стол
	$(document).on('click', 'input[name=createTable]', function(){
		$('#createTable').show(300);
	});
	if($('.conteiner-pop').length>0){
		$('.conteiner-pop').jScrollPane();
	}
	if($('.login-page  .description').length>0){
		$('.login-page  .description').jScrollPane();
	}
	$(document).click(function (event) {//миссклики для закрытия попапов
		var div = $('.active .hovered-block, .active .rase-ric');
		if(!div.is(event.target) && div.has(event.target).length === 0){
			$('.item-rise').removeClass('active');
		}
	});
	$('#buySomeGold .pay-buttons-wrap label').click(function(){
		$(this).closest('.pay-buttons-wrap').find('label').removeClass('active');
		$(this).addClass('active');
	})
});
