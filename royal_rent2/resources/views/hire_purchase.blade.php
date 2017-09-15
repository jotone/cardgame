@extends('layouts.default')
@section('content')
	<?php
	$user = Auth::user();
	?>
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<div class="el-breadcrumbs">
			<div class="mbox">
				<ul>
					<li><a href="/">Главная</a></li>
					<li class="active"><a href="{{ URL::asset($meta['slug']) }}">{{$meta['title']}}</a></li>
				</ul>
			</div>
		</div>
		<section class="mbox wrap-rent-header">
			<h1 class="rent-title">{!! $content['string_0']['value'] !!}<br>в {!! $defaults['current_city']['data']['string_1']['value']  !!}</h1>
			{!! $content['fulltext_0']['value'] !!}
			<a href="#" class="el-button goto-button">Выбрать автомобиль</a>
			<div class="rent-buy-car-pic" style="background: url('{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}') no-repeat center center; min-height: 169px;">
				<!-- <img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt="{{$content['img_slider_0']['value'][0]['alt']}}"> -->
				<a class="mouse" href="#">
					<span class="dot"></span>
				</a>
			</div>
		</section>
		<section class="mbox why-rent">
			<h2 class="rent-title">{{ $content['string_1']['value'] }}</h2>
			<div class="wrap-adventages">
				@foreach($content['img_slider_1']['value'] as  $slide)
					<div class="item">
						<img src="{{ URL::asset($slide['img']) }}" alt="">
						<p>{{ $slide['alt'] }}</p>
					</div>
				@endforeach
			</div>
			<a href="#" class="el-button goto-button">Выбрать автомобиль</a>
		</section>
		<section class="mbox el-calc-tabs">
			<div class="calc-title">Калькулятор</div>
			<div class="wrap-tabs">
				<ul class="tabs">
					<li class="active"><a href="#">1. Выбор марки</a></li>
					<li><a href="#">2. Выбор модели</a></li>
					<li><a href="#">3. Модификация</a></li>
					<li><a href="#">4. Выбор условий</a></li>
					<li><a href="#">5. Заявка</a></li>
				</ul>
				<div class="tab-content">
					<div class="content custom-info">
						<div class="left-tab-bar">
							<div class="marka">
								<p>Выбор марки</p>
								<select name="marka" class="mark choose choose-ph" data-placeholder="Выберите марку">
									<option></option>
									@foreach($car_marks as $car_mark)
										<option value="{{$car_mark->slug}}">{{ $car_mark->title }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="right-tab-bar">
							<div class="image-cars"><img src="{{ URL::asset('img/autos.png') }}" alt=""></div>
						</div>
					</div>

					<div class="content car-model select-model">
					</div>

					<div class="content car-model select-modification">
					</div>

					<div class="content custom-info view-car">
						<div class="left-tab-bar big">
							<div class="about-car">
								<!--<img src="../images/bmw316i.png" alt="">-->
								<div class="model">
									<!--<b>Bmw 316i</b>,
									<i>316</i>,
									<strong>АКПП</strong>-->
								</div>
							</div>
						</div>
						<div class="right-tab-bar smaller">
							<div class="functional">
								<div class="func-title">Стоимость — <span>1 300 000</span> руб.</div>
								<div class="wrap-range" id="date-ranges">
									<div class="desc">Срок аренды</div>
									<div class="range">
										<p>
											<input type="text" id="amount" readonly>
											<label for="amount">месяц</label>
										</p>
										<div id="slider"></div>
										<div class="min-max">
											<div class="min">3 месяца</div>
											<div class="max">3 года</div>
										</div>
									</div>
								</div>
								<div class="wrap-range" id="sum-range">
									<div class="desc">Сумма первого взноса</div>
									<div class="range">
										<p>
											<input type="text" id="amount-two" readonly>
											<label for="amount-two"></label>
										</p>
										<div id="slider-vznos"></div>
										<div class="min-max">
											<div class="min">0</div>
											<div class="max"></div>
										</div>
									</div>
								</div>
								<div class="preprice">Сумма залога — <span></span> руб.</div>
								<a href="#" class="el-button leave-btn">Оставить заявку</a>
							</div>
							<div class="information">
								<div class="main-price">
									<div class="title">Стоимость аренды в месяц</div>
									<div class="price"><span></span> руб.</div>
								</div>
								<div class="include">
									<div class="title">В стоимость входит</div>
									<p>КАСКО</p>
									<p>ОСАГО</p>
									<p>Замена резины</p>
									<p>Подменный авто</p>
									<p>Возможность поменять автомобиль</p>
								</div>
							</div>
						</div>
					</div>
					<div class="content custom-info">
						<div class="left-tab-bar big">
							<div class="about-car">
								<!--<img src="../images/bmw316i.png" alt="">-->
								<div class="model">
									<!--<b>Bmw 316i</b>,
									<i>316</i>,
									<strong>АКПП</strong>-->
								</div>
								<div class="model">
									Стоимость аренды в месяц — <span>86 988</span> руб.
								</div>
							</div>
						</div>
						<div class="right-tab-bar smaller">
							<div class="info">Наш менеджер перезвонит вам и ответит на все вопросы.</div>
							<form id="hirePurchaseForm" action="/send_letter" method="POST">
								<input name="type" type="hidden" value="arenda_avtomobilya_s_vykupom">
								<input name="car" type="hidden">
								<input name="price" type="hidden">
								<input name="rent_time" type="hidden">
								<input name="first_pay" type="hidden">
								<input name="deposite_pay" type="hidden">
								<input name="pay_per_month" type="hidden">
								<input name="leasing_name" type="text" placeholder="Ваше имя" class="input" @if(!empty($user))value="{{ $user['name'] }}"@endif>
								<input name="leasing_phone" type="text" placeholder="Ваш телефон" id="phone" class="input" @if(!empty($user))value="{{ $user['email'] }}"@endif>
								<input name="leasingSendRequest" type="submit" value="Отправить" class="el-button">
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>

	@if( (!empty($content['string_2']['value'])) && (!empty($content['fulltext_1']['value'])) )
			<section class="seo-block">
				<div class="mbox">
					<div class="seo-wrap">
						<div class="seo-pic">
							@foreach($content['img_slider_2']['value'] as $slide)
								<img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
							@endforeach
						</div>
						<div class="seo-info">
							<h2>{{ $content['string_2']['value'] }}</h2>
							{!! str_replace('<pre>','<p>', str_replace('</pre>','</p>',$content['fulltext_1']['value'])) !!}
						</div>
					</div>
				</div>
			</section>
		@endif
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop
