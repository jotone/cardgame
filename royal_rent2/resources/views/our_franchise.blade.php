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
		<section class="franchising">
			<div class="mbox">
				<h1>{{ $content['string_0']['value'] }}</h1>
				{!! $content['fulltext_0']['value'] !!}
				<div class="franchising-pic" style="background: url('{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}') no-repeat center center">
					<!-- <img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt=""> -->
					<a class="mouse" href="#">
						<span class="dot"></span>
					</a>
				</div>
			</div>
		</section>

		<section class="why-franchising">
			<div class="mbox">
				<div class="profitable">
					<h2>{{ $content['string_1']['value'] }}</h2>
					<div class="profitable-row">
						@foreach($content['img_slider_1']['value'] as $item)
							<div class="profitable-item">
								<div class="profitable-pic">
									<img src="{{ URL::asset($item['img']) }}" alt="1">
								</div>
								<p>{{ $item['alt'] }}</p>
							</div>
						@endforeach
					</div>
					<a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
				</div>

				<div class="comfortable">
					<h2>{{ $content['string_2']['value'] }}</h2>
					<div class="comfortable-wrap">
						@foreach($content['img_slider_2']['value'] as $item)
							<div class="comfortable-item">
								<div class="comfortable-pic">
									<img src="{{ URL::asset($item['img']) }}" alt="">
								</div>
								<p>{{ $item['alt'] }}</p>
							</div>
						@endforeach
					</div>
					<a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
				</div>
			</div>
		</section>

		@if( (!empty($content['string_3']['value'])) && (!empty($content['fulltext_1']['value'])) )
			<section class="seo-block">
				<div class="mbox">
					<div class="seo-wrap">
						<div class="seo-pic">
							@foreach($content['img_slider_3']['value'] as $slide)
								<img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
							@endforeach
						</div>
						<div class="seo-info">
							<h2>{{ $content['string_3']['value'] }}</h2>
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
