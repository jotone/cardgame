<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon" />
	<title>DragonHeart</title>

	<!-- build:css -->

	<link rel="stylesheet" href="{{ URL::asset('css/jquery.fancybox.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery.formstyler.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery.ui.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery.ui.datepicker.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/0_reset.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/slick-theme.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/slick.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery.jscrollpane.css') }}">

	<!-- add new file here -->

	<link rel="stylesheet" href="{{ URL::asset('css/zdev_0_basic.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_2.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_2_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_4.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_4_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_5.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_5_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_6.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_6_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_10.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_10_adapt.css') }}">
	<!-- endbuild -->

	<script src="{{ URL::asset('js/jquery-2.min.js') }}"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<!-- SCRIPTS -->

	<!-- build:js -->
	<script type="text/javascript" src="{{ URL::asset('js/device.js') }}" ></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.fancybox.pack.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.formstyler.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/maskInput.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/slick.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.mousewheel.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.jscrollpane.min.js') }}"></script>


	<!-- endbuild -->

<!-- <script src="@{{ URL::asset('js/validate_script.js') }}"></script>-->

	<script src='https://www.google.com/recaptcha/api.js'></script>

	<!--[if lt IE 10]>
	<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
	<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->
	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

</head>

<body>
<div class="hidden-block">
	<!-- Окно выбора карт -->
</div>

<div class="new-popups-block disable-select">
	<div class="summonCardPopup">
		<div id="summonWrap"></div>
	</div>
	<!-- Окно выбора "пришедших" карт -->
	<div class="troll-popup" id="selectNewCardsPopup">
		<div class="popup-content-wrap">
			<h5 class="cards-select-message-wrap">Карты</h5>
			<div class="pop-row" >
				<ul class="cards-select-wrap" id="handNewCards"></ul>
			</div>
			<div class="pop-row">
				<div class="timer-in-popup">
					<span data-time="minute">00</span>:<span data-time="seconds">00</span>
				</div>
			</div>
			<div class="pop-row button-wrap">
				<a class="button-troll acceptNewCards" href="#"><b>Готово</b></a>
				<a class="button-troll closeViewCards" href="#"><b>Закрыть</b></a>
				<a class="button-troll acceptRegroupCards" href="#"><b>Готово</b></a>
			</div>
		</div>
	</div>

	<!-- Окно конца игры -->
	<div class="troll-popup" id="endGamePopup">
		<div class="popup-content-wrap">
			<h5></h5>
			<div class="pop-row" >
				<span class="result-game"></span>
			</div>
			<div class="pop-row" >
			   <span>Нажмите “Ок” чтобы вернуться в меню игры.</span>
			</div>
			<div class="pop-row">
				<a class="button-troll acceptHandDeck" href="/"><b>OK</b></a>
			</div>
		</div>
	</div>

	<!-- Окно выбора "сдаться" -->
	<div class="troll-popup" id="userGiveUpPopup">
		<div class="close-this"></div>
		<div class="popup-content-wrap">
			<h5 style="text-align: center">Вы действительно хотите сдаться?</h5>
			<div class="pop-row">
				<button class="button-troll userGiveUp" data-action="true"><b>Да</b></button>
				<button class="button-troll userGiveUp" data-action="false"><b>Нет</b></button>
			</div>
		</div>
	</div>

	<!-- Окно выбора стартовых карт -->
	<div class="troll-popup hand-select-popup" id="selecthandCardsPopup">
		<div class="popup-content-wrap">
			<div class="pop-row opponent-fraction"><strong>Ваш противник: <span></span></strong></div>
			<div class="pop-row opponent-description">Особенности фракции: <span></span></div>

			<h5>Вы можете заменить <span></span> карты в руке</h5>

			<div class="pop-row for_cursed"></div>
			<div class="pop-row" >
				<ul class="cards-select-wrap cfix" id="handCards"></ul>
			</div>
			<div class="pop-row">
				<div class="timer-in-popup">
					<span data-time="minute">00</span>:<span data-time="seconds">00</span>
				</div>
			</div>
			<div class="pop-row">
				<a class="button-troll acceptHandDeck" href="#"><b>Готово</b></a>
			</div>
		</div>
	</div>

	<!-- Окно успешного действия -->
	<div class="troll-popup" id="successEvent">
		<div class="close-this"></div>
		<div class="popup-content-wrap">
			<h5>Результат:</h5>
			<div class="pop-row">
				<span class="result"></span>
			</div>
			<div class="pop-row">
				<div class="timer-in-popup">
					<span data-time="minute">00</span>:<span data-time="seconds">00</span>
				</div>
			</div>
		</div>
	</div>

	<div class="troll-popup" id="confirm-popup">
		<div class="close-this"></div>
		<div class="popup-content-wrap">
			<h5>Подтвердите действие:</h5>
			<div class="pop-row">
				<div class="confirm-txt"></div>
			</div>
			<div class="pop-row">
				<div class="timer-in-popup">
					<span data-time="minute">00</span>:<span data-time="seconds">00</span>
				</div>
			</div>
			<div class="pop-row">
				<a class="button-troll" data-value="true" href="#"><b>Подтвердить</b></a>
				<a class="button-troll" data-value="false" href="#"><b>Отменить</b></a>
			</div>
		</div>
	</div>

	<!-- Инфо карты -->
	<div class="troll-popup card-info" id="card-info">
		<div class="close-this"></div>
		<div class="content-card-info"></div>
	</div>

	<!-- Инфо ходящей карты -->
	<div class="troll-popup card-start-step" id="card-start-step">
		<div class="content-card-info"></div>
	</div>

	<!-- Окно ошибки -->
	<div class="market-buy-popup troll-popup" id="buyingCardOrmagic">
		<input name="_token" type="hidden" value="{{ csrf_token() }}">
	</div>
	<div class="afterloader">
		<img src="{{ URL::asset('images/379.gif') }}" alt="">
	</div>
	<div class="tyman"></div>

	<!-- Окно выбора хода-->
	<div class="troll-popup" id="selectCurrentTurn" style="width: 550px; height: auto;">
		<div class="popup-content-wrap">
			<div class="switch-user-turn-wrap">Выберите игрока для хода в текущем раунде
				<div id="chooseUser"></div>
			</div>
			<div class="pop-row">
				<div class="timer-in-popup">
					<span data-time="minute">00</span>:<span data-time="seconds">00</span>
				</div>
			</div>
			<div class="pop-row">
				<button class="button-troll" name="acceptUsersTurn" type="submit" value="Готово"><b>Готово</b></button>
			</div>
		</div>
	</div>

	<div class="troll-popup" id="allies-discard">
		<div class="close-this"></div>
		<div class="popup-content-wrap">
			<h5>Карты в вашем отбое:</h5>
			<div class="pop-row">
				<ul class="deck-cards-list">
					@foreach($ally['discard'] as $card_id)
						{!! \App\Http\Controllers\Site\BattleFieldController::cardSimpleView($card_id) !!}
					@endforeach
				</ul>
			</div>
		</div>
	</div>

	<div class="troll-popup" id="enemy-discard">
		<div class="close-this"></div>
		<div class="popup-content-wrap">
			<h5>Карты в отбое противника:</h5>
			<div class="pop-row">
				<ul class="deck-cards-list">
					@if(!empty($enemy['login']))
						@foreach($enemy['discard'] as $card_id)
							{!! \App\Http\Controllers\Site\BattleFieldController::cardSimpleView($card_id) !!}
						@endforeach
					@endif
				</ul>
			</div>
		</div>
	</div>

	<div class="troll-popup" id="allies-deck">
		<div class="close-this"></div>
		<div class="popup-content-wrap">
			<h5>Карты в колоде:</h5>
			<div class="pop-row">
				<ul class="deck-cards-list">
				@foreach($ally['deck'] as $card_id)
					{!! \App\Http\Controllers\Site\BattleFieldController::cardSimpleView($card_id) !!}
				@endforeach
				</ul>
			</div>
		</div>
	</div>
</div>