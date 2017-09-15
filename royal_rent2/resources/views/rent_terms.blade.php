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
		<section class="mbox wrap-conditions">
			<div class="cond-title">{{ $meta['title'] }}</div>
			<?php
            usort($content['category_0'], function($a, $b){
                return $a['position']>$b['position'];
            });
			?>
			<div class="wrap-tabs">
				@for($i = 0; $i < count($content['category_0']); $i++)
					<div id="{{ $content['category_0'][$i]['slug'] }}" class="btn {{ $content['category_0'][$i]['slug'] }} @if($i==0) active @endif">{{ $content['category_0'][$i]['title'] }}</div>
				@endfor
			</div>
			<div class="wrap-content">
				@foreach($content['category_0'] as $category)
					<div class="content">
						{!! $category['text'] !!}
					</div>
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

		@if( (!empty($content['string_0']['value'])) && (!empty($content['fulltext_0']['value'])) )
		<section class="seo-block">
			<div class="mbox">
				<div class="seo-wrap">
					<div class="seo-pic">
						@foreach($content['img_slider_0']['value'] as $slide)
							<img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
						@endforeach
					</div>
					<div class="seo-info">
						<h2>{{ $content['string_0']['value'] }}</h2>
						{!! str_replace('<pre>','<p>', str_replace('</pre>','</p>',$content['fulltext_0']['value'])) !!}
					</div>
				</div>
			</div>
		</section>
		@endif
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop