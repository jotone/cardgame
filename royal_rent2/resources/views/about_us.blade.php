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
		<section class="mbox wrapper-about">
			<div class="about-desc normal">
				<div class="about-text">
					<h4 class="title">{{ $content['string_0']['value'] }}</h4>
					{!! $content['fulltext_0']['value'] !!}
				</div>
				<div class="picture"><img src="{{ URL::asset($content['file_upload_0']['value']) }}" alt=""></div>
			</div>
			<div class="about-desc reverse">
				<div class="about-text">
					<h4 class="title">{{ $content['string_1']['value'] }}</h4>
					{!! $content['fulltext_1']['value'] !!}
				</div>
				<div class="picture"><img src="{{ URL::asset($content['file_upload_1']['value']) }}" alt=""></div>
			</div>
		</section>
		<section class="mbox wrap-clients">
			<div class="clients-title">Наши клиенты</div>
			<div class="logos">
			@foreach($content['category_0'] as $item)
				<div class="logo-item"><img src="{{ URL::asset($item['img_url'][0]['img']) }}" alt="{{$item['title']}}"></div>
			@endforeach
			</div>
		</section>
		<section class="mbox big-slider">
			<div class="wrap-icons">
				<div class="transport-title">Выберите нужный вам транспорт</div>
				<div class="slider-nav">
					@foreach($content['custom_slider_0'] as $item)
					<div class="slide">
						<div class="icon"><img src="{{ URL::asset($item['piktogramma']['value']) }}" alt=""></div>
						<p>{{ $item['string_1']['value'] }}</p>
					</div>
					@endforeach
				</div>
			</div>
			<div class="wrap-navigation">
				<div class="slider-for">
					@foreach($content['custom_slider_0'] as $item)
					<div class="slide">
						<div class="transport-title">{{ $item['string_1']['value'] }}</div>
						<div class="slide-desc">{!! $item['text_2']['value'] !!}</div>
						<div class="buttons">
							<a href="{{ URL::asset($item['string_5']['value']) }}" class="el-button">{{ $item['string_3']['value'] }}</a>
							<a href="#call-popup" class="price fancybox-form about-order"><span><img src="{{ URL::asset('img/calculatorr.png') }}" alt=""></span>Рассчитать стоимость аренды</a>
						</div>
						<img src="{{ URL::asset($item['bolshoe_izobrazhenie']['value']) }}" alt="">
					</div>
					@endforeach
				</div>
			</div>
		</section>

		@if( (!empty($content['string_2']['value'])) && (!empty($content['fulltext_2']['value'])) )
		<section class="seo-block">
			<div class="mbox">
				<div class="seo-wrap">
					<div class="seo-pic">
						@foreach($content['img_slider_0']['value'] as $slide)
							<img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
						@endforeach
					</div>
					<div class="seo-info">
						<h2>{{ $content['string_2']['value'] }}</h2>
						{!! str_replace('<pre>','<p>', str_replace('</pre>','</p>',$content['fulltext_2']['value'])) !!}
					</div>
				</div>
			</div>
		</section>
		@endif
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop