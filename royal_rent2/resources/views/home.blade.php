@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<section class="wrapper-acordeon">
			<div class="main-slider">
				@foreach($content['custom_slider_0'] as $slide)
					<div class="slide">
						<img src="{{ URL::asset($slide['vertikalnyj_slajd']['value']) }}" alt="">
						<div class="headline">{{$slide['string_3']['value']}}</div>
					</div>
				@endforeach
			</div>

			<div class="huge-hidden-blocks">
				@foreach($content['custom_slider_0'] as $i => $slide)
					<div class="hidden-item" data-attr="{{$i}}">
						<img src="{{ URL::asset($slide['bolshoe_izobrazhenie']['value']) }}" alt="">
						<div class="text">{{$slide['string_3']['value']}}</div>
					</div>
				@endforeach
			</div>

			<div class="shadow-hidden-blocks">
				@foreach($content['custom_slider_0'] as $i => $slide)
					<div class="shadow-item" data-attr="{{$i}}">
						<a href="javascript: void(0);" class="shadow-item_link"><div class="name">{{$slide['string_1']['value']}}</div></a>
					</div>
				@endforeach
			</div>
		</section>

		<div class="mbox index-about">
			<div class="title">{{$content['string_0']['value']}}</div>
			<div class="wrap-items">
				@foreach($content['custom_slider_1'] as $i => $about)
					<div class="item">
						<img src="{{ URL::asset($about['kartinka']['value']) }}" alt="">
						<div class="item-name">{{ $about['string_1']['value'] }}</div>
						<div class="desc">{{ $about['text_2']['value'] }}</div>
					</div>
				@endforeach
			</div>
		</div>

		<section class="mbox wrap-transport-slider">
			<div class="left-bar-slider">
				<div class="arrows">
					<div class="prev-wide"></div>
					<div class="next-wide"></div>
				</div>
				<div class="white-block"></div>
				<div class="wide-slider">
					@foreach($content['custom_slider_2'] as $i => $slide)
						<div class="slide"><img src="{{ URL::asset($slide['kartinka']['value']) }}" alt=""></div>
					@endforeach
				</div>
				<div class="iphone"><img src="{{ URL::asset('img/iphone.png') }}" alt=""></div>
			</div>

			<div class="title-slider">
				@foreach($content['custom_slider_2'] as $i => $slide)
					<div class="slide">{{ $slide['string_1']['value'] }}</div>
				@endforeach
			</div>

			<div class="text-slider">
				@foreach($content['custom_slider_2'] as $i => $slide)
					<div class="slide">
						<div class="desc">{{ $slide['text_2']['value'] }}</div>
						<div class="goto">
							<a href="{{ URL::asset($slide['string_3']['value']) }}" class="link">
								<p>Перейти к услуге</p>
								<span class="arrow"><span></span></span>
							</a>
						</div>
						<div class="goto">
							<a href="javascript: void(0);" class="link open_our-services">
								<p>Посмотреть все наши услуги</p>
								<span class="arrow"><span></span></span>
							</a>
						</div>
					</div>
				@endforeach
			</div>
		</section>
		<section class="mbox index-video">
			<video autoplay loop>
				<source src="{{ URL::asset('video/'.$content['string_1']['value']) }}" type="video/mp4">
			</video>
		</section>
		<section class="mbox about-rent">
			<div class="text">
				<div class="take-car">{{ $content['string_2']['value'] }}</div>
				{!! $content['fulltext_0']['value'] !!}
			</div>
			<div class="photo">
				@foreach($content['img_slider_0']['value'] as $i => $slide)
					<div class="photo-item"><img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}"></div>
				@endforeach
			</div>
		</section>

		<section class="seo-block">
			<div class="mbox">
				<div class="seo-wrap">
					<div class="seo-pic">
						@foreach($content['img_slider_1']['value'] as $slide)
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
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop
