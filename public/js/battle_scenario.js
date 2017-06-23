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

//PHP style time
function phpTime() {
	return Math.floor(Date.now()/ 1000);
}
// /PHP style time

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
function closeAllTrollPopup() {
	$('div.troll-popup').removeClass('show');
	$('.new-popups-block').removeClass('show');
}
// /Popup

//Fix card margin-right
function calculateRightMarginCardHands() {
	calculate($('#sortableUserCards'));
	calculate($('#sortable-cards-field-more'));
	calculate($('.cards-row-wrap'));
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
	$(document).on('click', '#card-give-more-user li[data-field=deck] .card-my-init.cards-take-more', function () {
		openTrollPopup($('#allies-deck'));
	});
	$(document).on('click', '#card-give-more-user li[data-field=discard] .card-my-init.cards-take-more', function () {
		openTrollPopup($('#allies-discard'));
	});
	$(document).on('click', '#card-give-more-oponent li[data-field=discard] .card-init', function () {
		openTrollPopup($('#enemy-discard'));
	});
}
// /View player's deck

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
	}
}
//

window.userImgData = {opponent:'', user: ''}; //User Images
var socketResult;
var ident;
var allowToAction = false;
var turnDescript = {cardSource: 'hand', additionalData: ''};
var timeOut;
var conn;

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
});