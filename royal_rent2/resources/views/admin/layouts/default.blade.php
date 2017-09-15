<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<title>SomeAdmin</title>

	<link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
	<link rel="stylesheet" href="{{ URL::asset('css/reset.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/admin.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery-ui.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery-ui.structure.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery-ui.theme.min.css') }}">
	<script type="text/javascript" src="{{ URL::asset('js/jquery.3.1.1.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/ckeditor.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/build-config.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/config.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/styles.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery-ui.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.mask.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/admin_scenario.js') }}"></script>
	@yield('scripts')
</head>
<body>
<header class="col_1" data-token="{{ csrf_token() }}">
	<nav class="top-menu">{!! $menu !!}</nav>
</header>
@yield('content')
<?php
$time = microtime();
$time = explode(' ', $time);
$total_time = round(($time[1] + $time[0] - $start),4);
?>
<footer>
	<div class="col_1_4">Страница построена за {{$total_time}} секунд</div>
	<div class="error-log col_1_2">Ошибка &#9888;</div>
	<div class="col_1_4 tac"><a href="/logout">Выйти</a></div>
</footer>

<div class="error-popup">
	<div class="close-popup"></div>
	<div class="popup-caption"><span></span></div>
	<div class="error-wrap"></div>
</div>

<div class="overview-popup">
	<div class="close-popup"></div>
	<div class="popup-images"></div>
	<div class="button-wrap tac">
		<button name="addImageFromSaved" class="control-button" type="button">Готово</button>
	</div>
</div>
</body>
</html>