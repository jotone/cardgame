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
		<section class="car-taxi">
			<div class="mbox">
				<div class="car-taxi-text">
					<h1>{{ $meta['title'] }}</h1>
					{!! $meta['text'] !!}
					<a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
				</div>
				<div class="car-taxi-pic" style="background: url({{ URL::asset($meta['img_url'][0]['img']) }}) no-repeat center 0%; ">
					<!-- <img src="{{ URL::asset($meta['img_url'][0]['img']) }}" alt="Taxi"> -->
					<a class="mouse" href="#">
						<span class="dot"></span>
					</a>
				</div>
			</div>
		</section>

		<section class="car-taxi-content">
			<div class="mbox">
				<div class="advantages">
					<h2>{{ $content['string_0']['value'] }}</h2>
					<div class="advantages-row">
						@foreach($content['img_slider_0']['value'] as $item)
							<div class="advantages-item">
								<div class="advantages-pic">
									<img src="{{ URL::asset($item['img']) }}" alt="">
								</div>
								<p>{{ $item['alt'] }}</p>
							</div>
						@endforeach
					</div>
					<a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
				</div>
				<div class="terms-work">
					<h2>{{ $content['img_slider_1']['caption'] }}</h2>
					<div class="terms-work-row">
						@foreach($content['img_slider_1']['value'] as $item)
							<div class="terms-work-item">
								<div class="terms-work-pic">
									<img src="{{ URL::asset($item['img']) }}" alt="">
								</div>
								<p>{!! $item['alt'] !!}</p>
							</div>
						@endforeach
					</div>
					<a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
				</div>
				<div class="car-park">
					<h2>Автопарк для аренды под такси</h2>
					<div class="car-park-wrap">
						@if(!empty($content['table_0']['value']['head']))
						<div class="car-park-top">
							<div class="car-park-table">
								<div class="table-header">
									@foreach($content['table_0']['value']['head'] as $item)
										<div class="column">{{ $item }}</div>
									@endforeach
								</div>
								<div class="table-body">
									@foreach($content['table_0']['value']['body'] as $row)
										<div class="table-row">
											@foreach($row as $col)
												<div class="table-col">{{ $col }}</div>
											@endforeach
										</div>
									@endforeach
								</div>
							</div>
						</div>
						@endif
						<div class="car-park-bottom">
							<div class="car-park-text">
								{!! $content['fulltext_0']['value'] !!}
							</div>
							<a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
						</div>
					</div>
				</div>
			</div>
		</section>

		@if( (!empty($content['string_1']['value'])) && (!empty($content['fulltext_1']['value'])) )
			<section class="seo-block">
				<div class="mbox">
					<div class="seo-wrap">
						<div class="seo-pic">
							@foreach($content['img_slider_2']['value'] as $slide)
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