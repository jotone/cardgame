@extends('layouts.default')
@section('content')
<!-- MAIN -->
<div class="main">
	<!-- add partials here -->
	<div class="el-breadcrumbs">
		<div class="mbox">
			<ul>
				<li><a href="/">Главная</a></li>
				<li><a href="{{ URL::asset($meta['last_page']['slug']) }}">{{ $meta['last_page']['title'] }}</a></li>
				<li class="active"><a href="{{ URL::asset($meta['last_page']['slug'].$meta['slug']) }}">{{ $meta['title'] }}</a></li>
			</ul>
		</div>
	</div>
	<section class="mbox wrap-slider-card">
		<div class="slider-card">
			<div class="big-photos">
				@foreach($content['img_url'] as $src)
					<div class="photo"><img src="{{ URL::asset($src['img']) }}" alt="{{ $src['alt'] }}"></div>
				@endforeach
			</div>
			@if(count($content['img_url']) > 1)
			<div class="small-photos">
				@foreach($content['img_url'] as $src)
					<div class="sm-photo"><img src="{{ URL::asset($src['img']) }}" alt="{{ $src['alt'] }}"></div>
				@endforeach
			</div>
			@endif
		</div>
		<div class="right-card">
			<div class="card-title">{{ $meta['title'] }}</div>
			<div class="about">
				<span><img src="{{ URL::asset('img/money_cart.png') }}" alt=""></span>
				<span>от {{ number_format($content['data']['string_0']['value'],0,',',' ') }} руб.</span>
			</div>
			<div class="about">
				<span><img src="{{ URL::asset('img/time_cart.png') }}" alt=""></span>
				<span>от {{ $content['data']['string_1']['value'] }} часа</span>
			</div>
			<a class="el-button" href="{{ URL::asset('/order_romantic/'.$meta['slug']) }}">Заказать</a>
			<div class="ask">
				<a class="fancybox-form" href="#call-popup">
					<span><img src="{{ URL::asset('img/support_cart.png') }}" alt=""></span>
					<span>У меня есть вопрос. Заказать обратный звонок.</span>
				</a>
			</div>
		</div>
	</section>

	<section class="mbox wrapper-details">
		<div class="details">
			<p>{{ $content['text_caption'] }}</p>
			<a href="#"><img src="{{ URL::asset('img/plus.png') }}" alt=""></a>
		</div>
		<div class="equipment-hidden">
			{!! $content['text'] !!}
		</div>

		<div class="tariffs">
			@if(isset($content['data']['category_1']))
			<div class="tarif-title">Доступные тарифы при оформлении свидания:</div>
			<div class="available-tariffs">

				@foreach($content['data']['category_1'] as $tarif)
					<div class="tar-item">
						<div class="title">{{ $tarif['title'] }}</div>
						<div class="price">
							<?php
							$tarif_price = (!empty($tarif['data']['string_0']['value']))
								? explode('/',$tarif['data']['string_0']['value'])
								: [''];
							?>
							<p><b>{{ $tarif_price[0] }}</b> <i>&#8381;</i></p>
							@if(isset($tarif_price[1]))
								<span><strong>/</strong>{{ $tarif_price[1] }}</span>
							@endif
						</div>
						<?php
						$tarif['text'] = explode("\n\r",$tarif['text']);
						?>
						@foreach($tarif['text'] as $descr_field)
							<div class="desc">{!! str_replace(['<p>','</p>'],['',''],$descr_field) !!}</div>
						@endforeach
						<div class="prompt-hidden">
							<div class="prompt-title">{{ $tarif['data']['string_1']['value'] }}</div>
							{!! $tarif['data']['fulltext_0']['value'] !!}
						</div>
					</div>
				@endforeach
			</div>
			@endif

			<div class="tariffs-description">
				<div class="include">
					{!! $content['data']['fulltext_0']['value'] !!}
				</div>
			</div>
		</div>

	</section>

	@if(!empty($recomended))
		<section class="mbox wrap-recomend">
			<div class="recomend-title">Рекомендуем посмотреть эти романтические встречи:</div>
			<div class="filter-tab-item">
				@foreach($recomended as $article)
					<div class="excursion-item">
						<div class="exc-text">
							<div class="info">
								<span class="time"><img src="{{ URL::asset('img/time-one.png') }}" alt=""> {{ $article['data']['string_1']['value'] }} часа</span>
								<span class="time"><img src="{{ URL::asset('img/cost.png') }}" alt=""> от {{ number_format($article['data']['string_0']['value'],0,',',' ') }} руб.</span>
							</div>
							<div class="info">
								<div class="info-title">{{ $article['title'] }}</div>
							</div>
							<div class="info">
								<div class="info-desc">{!! $article['description'] !!}</div>
							</div>
							<div class="info-details">
								<a href="{{ URL::asset('excursion/'.$article['slug']) }}">Узнать подробнее</a>
							</div>
						</div>
						<div class="exc-photo">
							@if(!empty($article['img_url']))
								<img src="{{ URL::asset($article['img_url']) }}" alt="">
							@endif
						</div>
					</div>
				@endforeach
			</div>
		</section>
	@endif

	@if(!empty($visited))
		<section class="mbox wrap-look-before">
			<div class="recomend-title">Ранее просмотренные вами романтические встречи:</div>
			<div class="filter-tab-item">
				@foreach($visited as $article)
					<div class="excursion-item">
						<div class="exc-text">
							<div class="info">
								<span class="time"><img src="{{ URL::asset('img/time-one.png') }}" alt=""> {{ $article['data']['string_1']['value'] }} часа</span>
								<span class="time"><img src="{{ URL::asset('img/cost.png') }}" alt=""> от {{ number_format($article['data']['string_0']['value'],0,',',' ') }} руб.</span>
							</div>
							<div class="info">
								<div class="info-title">{{ $article['title'] }}</div>
							</div>
							<div class="info">
								<div class="info-desc">{!! $article['description'] !!}</div>
							</div>
							<div class="info-details">
								<a href="{{ URL::asset('excursion/'.$article['slug']) }}">Узнать подробнее</a>
							</div>
						</div>
						<div class="exc-photo">
							@if(!empty($article['img_url']))
								<img src="{{ URL::asset($article['img_url']) }}" alt="">
							@endif
						</div>
					</div>
				@endforeach
			</div>
		</section>
	@endif
	@include('layouts.attachment')
</div>
<!-- /MAIN -->
@stop