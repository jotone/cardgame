@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<div class="mbox">
			<ul class="el-breadcrumbs">
				<li><a href="/">Главная</a></li>
				<li class="active"><a href="{{ URL::asset($meta['slug']) }}">{{$meta['title']}}</a></li>
			</ul>
		</div>
		<section class="business-travel">
			<div class="mbox">
				<div class="business-travel-wrap">
					<h1>{{ $meta['title'] }}</h1>
					<p>{{ $content['string_0']['value'] }}</p>
					<a class="el-button corp-contract-link" href="#corp-contract">Стать корпоративным клиентом</a>
				</div>
				<div class="business-travel-pic" style="background: url({{ URL::asset($content['file_upload_0']['value']) }}) no-repeat center center;">
					<!-- <img src="{{ URL::asset($content['file_upload_0']['value']) }}" alt=""> -->
					<a class="mouse" href="#">
						<span class="dot"></span>
					</a>
				</div>
			</div>
		</section>


		<section class="types-service">
			<div class="mbox">
				<h2>{{ $content['string_1']['value'] }}</h2>
				<div class="types-service-row">
					@foreach($content['img_slider_0']['value'] as $item)
						<div class="types-service-item">
							<div class="types-service-pic">
								<img src="{{ URL::asset($item['img']) }}" alt="">
							</div>
							<p>{{ $item['alt'] }}</p>
						</div>
					@endforeach
				</div>
				<a class="el-button corp-contract-link" href="#corp-contract">Стать корпоративным клиентом</a>
			</div>
		</section>

		<section class="transport-services">
			<div class="mbox">
				<h2>Автотранспортное обслуживание</h2>
				<div class="transport-services-wrap">
					<div class="transport-services-left">
						<form action="/" method="post">
							<div class="form-field">
								<span>Вид транспорта</span>
								<select id="typeTransport" class="choose" name="typeTransport">
									@foreach($uniq_categories as $item)
										<option value="{{ $item['slug'] }}">{{ $item['title'] }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-field">
								<span>Марка</span>
								<select id="markTransport" class="choose" name="markTransport">
									@if(!empty($marks))
									@foreach($marks as $mark)
										<option value="{{ $mark['slug'] }}">{{ $mark['title'] }}</option>
									@endforeach
									@endif
								</select>
							</div>
							<div class="form-field">
								<span>Модель</span>
								<select id="modelTransport" class="choose" name="modelTransport">
									@foreach($models as $model)
										<option value="{{ $model['slug'] }}">{{ $model['title'] }}</option>
									@endforeach
								</select>
							</div>
						</form>
					</div>
					<div class="transport-services-center">
						@if(!empty($car))
							<div class="transport-services-pic">
								@if(!empty($car[0]['img_url']))
                                <img src="{{ $car[0]['img_url'][0]['img'] }}" alt="{{$car[0]['img_url'][0]['alt']}}">
                                @endif
							</div>
							<p>Минимальный заказ — {{ $car[0]['data']['string_1']['value'] }} часа</p>
							<p>Стоимость — от {{ $car[0]['price'] }} руб. в час</p>
							<div class="transport-services-color">
								@foreach($car[0]['color'] as $color)
									<a href="#" style="background-color: {{ $color->color }}" title="{{ $color->title }}"></a>
								@endforeach
							</div>
							<a class="transport-services-btn el-button corp-contract-link" href="#corp-contract">Стать корпоративным клиентом</a>
						@endif
					</div>
					<div class="transport-services-right">
						{!! $content['fulltext_0']['value'] !!}
					</div>
				</div>
			</div>
		</section>

		@if( (!empty($content['string_1']['value'])) && (!empty($content['fulltext_1']['value'])) )
			<section class="seo-block">
				<div class="mbox">
					<div class="seo-wrap">
						<div class="seo-pic">
							@foreach($content['img_slider_1']['value'] as $slide)
								<img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
							@endforeach
						</div>
						<div class="seo-info">
							<h2>{{ $content['string_1']['value'] }}</h2>
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