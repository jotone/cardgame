@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<section class="news-inner-header" @if(!empty($content['big_img']))style="background: url({{ $content['big_img']['img'] }}) no-repeat center bottom; background-size: cover; background-attachment: fixed;"@endif>
			<h1>{{ $meta['title'] }}</h1>
			<a class="mouse" href="#">
				<span class="dot"></span>
			</a>
		</section>
		<div class="el-breadcrumbs">
			<div class="mbox">
				<ul>
					<li><a href="/">Главная</a></li>
					<li><a href="{{ URL::asset($meta['last_page']['slug']) }}">{{ $meta['last_page']['title'] }}</a></li>
					<li class="active"><a href="{{ URL::asset($meta['last_page']['slug'].'/'.$meta['slug']) }}">{{  $meta['title'] }}</a></li>
				</ul>
			</div>
		</div>
		<div class="news-inner">
			<div class="mbox">
				<div class="news-inner-text">
					<h2>{{ $content['caption'] }}</h2>
					{!! $content['text'] !!}
				</div>
				<div class="news-inner-pics">
					@foreach($content['images'] as $image)
						<div class="news-inner-pic" style="text-align: center">
							<img src="{{ URL::asset($image['img']) }}" alt="{{ $image['alt'] }}">
						</div>
					@endforeach
				</div>
			</div>
		</div>
		@if(!empty($content['cars']))
			<div class="mbox">
				<section class="mbox watched-cars">
					<div class="recom-title">Акционный транспорт:</div>
					<div class="all-cars all-cars-watched">
						@foreach($content['cars'] as $car)
							<div class="car-item">
								@if($car['promo']['value'] > 0)
									<div class="sale">
										<span>скидка</span>
										<span><b>{{ $car['promo']['value'] }}</b>%</span>
									</div>
								@endif
								<div class="photo">
									@if(!empty($car['img_url']))
										<img src="{{ $car['img_url'][0]['img'] }}" alt="{{ $car['img_url'][0]['alt'] }}">
									@endif
								</div>
								<a href="#photo-slider" class="view-photo">
									<span><img src="{{ URL::asset('img/pic-icon.png') }}" alt=""></span>
									<span>Посмотреть реальные фото</span>
								</a>
								<p>{{ $car['title'] }}&nbsp;</p>
								<p>от {{ number_format($car['price'],0,',',' ') }} &#8381;/ в час</p>
								<div class="colors">
									@foreach($car['color'] as $color)
										<div class="color" style="background-color: {{ $color->color }}" title="{{ $color->title }}"></div>
									@endforeach
								</div>
								<a href="{{ URL::asset('car/'.$car['upper'].'/'.$car['slug']) }}" class="el-button">Взять в аренду</a>
							</div>
						@endforeach
					</div>
					<div class="prev-watched"><img src="{{ URL::asset('img/prev.png') }}" alt=""></div>
					<div class="next-watched"><img src="{{ URL::asset('img/next.png') }}" alt=""></div>
				</section>
			</div>
		@endif
		<div class="news-inner">
			<div class="mbox">
				<div class="news-inner-link">
					<a href="{{ URL::asset($meta['last_page']['slug']) }}">Вернуться</a>
				</div>
			</div>
		</div>
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop