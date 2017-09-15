@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<div class="el-breadcrumbs">
			<div class="mbox">
				<ul>
					<li><a href="/">Главная</a></li>
					<li class="active"><a href="{{ $meta['slug'] }}">{{ $meta['title'] }}</a></li>
				</ul>
			</div>
		</div>
		<section class="mbox cooperations">
			<div class="title-cooperations">{{ $content['string_0']['value'] }}</div>
			<div class="coop-advantages">{!! $content['fulltext_0']['value'] !!}</div>
			<div class="cooperation-people-pic" style="background: url({{ URL::asset($content['img_slider_0']['value'][0]['img']) }}) no-repeat center 0%; min-height: 260px;" >
				<a class="mouse" href="#">
					<span class="dot"></span>
				</a>
				<!-- <img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt=""> -->
			</div>
		</section>
		<section class="mbox wrap-work wrap-work-more" id="mbox">
			<div class="work-title">Условия работы с нами</div>
			<div class="wrap-pluses">
				@foreach($content['img_slider_1']['value'] as $item)
					<div class="item-plus">
						<img src="{{ URL::asset($item['img']) }}" alt="">
						<div class="plus-desc">{!! $item['alt'] !!}</div>
					</div>
				@endforeach
			</div>
			<a class="el-button fancybox-form form-order" href="#order-call">Оформить заявку</a>
		</section>
		<section class="mbox wrap-work">
			<div class="work-title">{{ $content['string_1']['value'] }}</div>
			<div class="wrap-pluses">
				@foreach($content['img_slider_2']['value'] as $item)
					<div class="item-plus">
						<img src="{{ URL::asset($item['img']) }}" alt="">
						<div class="plus-desc">{{ $item['alt'] }}</div>
					</div>
				@endforeach
			</div>
			<a class="el-button fancybox-form form-order" href="#order-call">Оформить заявку</a>
		</section>
		<section class="mbox wrap-work">
			<div class="work-title">Узнайте сколько зарабатывает ваш автомобиль</div>
			<div class="wrap-auto">
				<select name="auto" id="auto" data-placeholder="Модель автомобиля">
					<option></option>
					@foreach($car_marks as $car_mark)
						<option value="{{ $car_mark->slug }}">{{ $car_mark->title }}</option>
					@endforeach
				</select>
				<select name="auto" id="auto-type" data-placeholder="Модель автомобиля">
					<option label="Модель автомобиля"></option>
				</select>
			</div>
			<div class="call-us">Не нашли в списке свой автомобиль? <a href="#"> Позвоните нам!</a></div>
			<div class="wrap-car" style="display:none;">
				<img class="preview-photo" src="" alt="">
				<div class="about-car">
					<div class="wrap-text">
						<span class="text">Доход в сутки:</span>
						<span class="text">Доход в месяц:</span>
						<span class="text">Доход за год:</span>
					</div>
					<div class="wrap-prices">
						<div class="day">
							<span class="price">2 066</span>
							<span class="letter"> р.</span>
						</div>
						<div class="month">
							<span class="price">62 066</span>
							<span class="letter"> р.</span>
						</div>
						<div class="year">
							<span class="price">762 066</span>
							<span class="letter"> р.</span>
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
							@foreach($content['img_slider_3']['value'] as $slide)
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