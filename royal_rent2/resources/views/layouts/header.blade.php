<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="{{$meta['meta_description']}}" />
	<meta name="keywords" content="{{$meta['meta_keywords']}}" />
	<title>{{$meta['meta_title']}}</title>

	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

	<!-- build:css -->

	<link rel="stylesheet" href="{{ URL::asset('css/fancybox.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/formstyler.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/normalize.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/slick-theme.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/slick.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery-ui.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery.timepicker.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/jquery.secretnav.css') }}">

	<!-- add new file here -->
	<!-- add new file here -->

	<link rel="stylesheet" href="{{ URL::asset('css/zdev_0_basic.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_1.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_1_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_2.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_2_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_4.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_4_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_5.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_5_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_6.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_6_adapt.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_7.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/zdev_7_adapt.css') }}">
	<!-- endbuild -->

	<!--[if lt IE 10]>
	<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
	<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->
	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
<div class="global-wrapper">
	<?php
	$user = Auth::user();
	$user_panel_link = (!$user)? '#login-menu': '';
	?>
	<div class="nav-mobile-menu">
		<div class="top-block">
			<div class="language-picker">
				<a href="#country_picker" class="fancyboxHeading">
					<img src="{{ URL::asset('img/russia.png') }}" alt="">
					<p>Русский</p>
				</a>
			</div>
			<div class="city-picker">
				<a href="#city_picker" class="fancyboxHeading">
					<img src="{{ URL::asset('img/city.png') }}" alt="">
					<p>{{ $defaults['current_city']['title'] }}</p>
				</a>
			</div>
			<div class="online-calculator">
				<a href="#headerCalculator" class="fancyboxHeading">
					<img src="{{ URL::asset('img/calculator.png') }}" alt="">
					<p>Онлайн заявка</p>
				</a>
			</div>
			<div class="log-in">
				<a href="#login-menu" class="fancyboxHeading1">

					<img src="{{ URL::asset('img/login.png') }}" alt="">
					<p>Личный кабинет</p>
				</a>
			</div>
			<div class="telephon">
				<a href="tel:{{ str_replace(' ', '-',$defaults['current_city']['data']['string_2']['value']) }}">{{ str_replace(' ', '-',$defaults['current_city']['data']['string_2']['value']) }}</a>
			</div>
		</div>

		<div class="bottom-part ">
			<div class="main-wrap">
				<ul class="nav-list">
				@foreach($defaults['header_menu'] as $menu_item)
					@if($menu_item['has_hash'] == 1)
						<li id="{{ $menu_item['slug'] }}">
							<a class="open_{{substr($menu_item['slug'],1) }}">
								@if($menu_item['custom_fields']['file_upload_0']['value']!='')
									<img src="{{$menu_item['custom_fields']['file_upload_0']['value']}}" alt="">
									<p>{{$menu_item['title']}}</p>
								@else
									{{$menu_item['title']}}
								@endif
							</a>
						</li>
					@else
						<li>
							<a href="{{ URL::asset($menu_item['slug']) }}">
								@if(!empty($menu_item['custom_fields']['file_upload_0']['value']!=''))
									<img src="{{$menu_item['custom_fields']['file_upload_0']['value']}}" alt="">
									<p>{{$menu_item['title']}}</p>
								@else
									{{$menu_item['title']}}
								@endif
							</a>
						</li>
					@endif
				@endforeach
				</ul>
			</div>
		</div>
		<div class="nav-mobile-close-button"></div>
	</div>
	<!-- HEADER -->
	<header class="header">

		<div class="top-part">
			<div class="main-wrap">
				<div class="company-logo">
					<a href="/">
						<img src="{{ URL::asset('img/white_logo.png') }}" alt="">
					</a>
				</div>
				<div class="nav-menu-adaptiv-wrap">
					<div class="language-picker">
						<a href="#country_picker" class="fancyboxHeading">
							<img src="{{ URL::asset('img/russia.png') }}" alt="">
							<p>Русский</p>
						</a>
					</div>
					<div class="city-picker">
						<a href="#city_picker" class="fancyboxHeading">
							<img src="{{ URL::asset('img/city.png') }}" alt="">
							<p>{{ $defaults['current_city']['title'] }}</p>
						</a>
					</div>
					<div class="online-calculator">
						<a href="#headerCalculator" class="fancyboxHeading">
							<img src="{{ URL::asset('img/calculator.png') }}" alt="">
							<p>Онлайн заявка</p>
						</a>
					</div>
					<div class="log-in">
						<a href="{{ $user_panel_link }}" class="fancyboxHeading1">
							<img src="{{ URL::asset('img/login.png') }}" alt="">
							<p>Личный кабинет</p>
						</a>
					</div>
					<div class="telephon">
						<a href="tel:{{ str_replace(' ', '-',$defaults['current_city']['data']['string_2']['value']) }}">{{ str_replace(' ', '-',$defaults['current_city']['data']['string_2']['value']) }}</a>
					</div>
				</div>
				<div class="mobile-nav-menu-button">
					<span></span>
					<span></span>
					<span></span>
				</div>
				<div class="button-wrap">
					<a href="#back-call" class="fancyboxHeading" >Обратный звонок</a>
				</div>
			</div>
		</div>

		<div class="bottom-part ">
			<div class="main-wrap">
				<ul class="nav-list">
				@foreach($defaults['header_menu'] as $menu_item)
					@if($menu_item['has_hash'] == 1)
						<li id="{{ $menu_item['slug'] }}">
							<a class="open_{{ substr($menu_item['slug'],1) }}">
								@if($menu_item['custom_fields']['file_upload_0']['value']!='')
									<img src="{{$menu_item['custom_fields']['file_upload_0']['value']}}" alt="">
									<p>{{$menu_item['title']}}</p>
								@else
									{{$menu_item['title']}}
								@endif
							</a>
						</li>
					@else
						<li>
							<a href="{{ URL::asset($menu_item['slug']) }}">
								@if($menu_item['custom_fields']['file_upload_0']['value']!='')
									<img src="{{$menu_item['custom_fields']['file_upload_0']['value']}}" alt="">
									<p>{{$menu_item['title']}}</p>
								@else
									{{$menu_item['title']}}
								@endif
							</a>
						</li>
					@endif
				@endforeach
				</ul>
			</div>
		</div>
	</header>
	<!-- /HEADER -->