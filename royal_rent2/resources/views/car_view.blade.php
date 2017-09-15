@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<div class="el-breadcrumbs">
			<div class="mbox">
				<ul>
					<li><a href="/">Главная</a></li>
					@if(isset($_COOKIE['prev_page']))
					<?php
					$prev_page = unserialize($_COOKIE['prev_page']);
					?>
					<li><a href="{{ URL::asset($prev_page[0]) }}">{{ $prev_page[1] }}</a></li>
					@endif
					<li class="active"><a href="{{ URL::asset($meta['slug'].'-'.\App\Http\Controllers\Supply\Functions::str2url($content['colors'][0]['title'])) }}">{{ $meta['title'] }}</a></li>
				</ul>
			</div>
		</div>
		<section class="mbox wrap-slider-card">
			<div class="slider-card">
				<div class="big-photos">
					@for($i=1; $i<count($content['img_url']); $i++)
						<div class="photo"><img src="{{ URL::asset($content['img_url'][$i]['img']) }}" alt="{{ $content['img_url'][$i]['alt'] }}"></div>
					@endfor
				</div>
				<div class="small-photos">
					@for($i=1; $i<count($content['img_url']); $i++)
						<div class="sm-photo"><img src="{{ URL::asset($content['img_url'][$i]['img']) }}" alt="{{ $content['img_url'][$i]['alt'] }}"></div>
					@endfor
				</div>
			</div>
			<div class="right-card">
				<div class="card-title">{{ $meta['title'] }}</div>

				<div @if( (empty($content['price']) || ($content['price'] < 1))) style="display: none" @endif>
					<p>от <span data-name="price">{{  number_format($content['price'],0,',',' ') }}</span> &#8381;/ в час</p>
				</div>

				<div class="wrap-color">
					<div class="color-content">
						@if(!empty($content['colors']))
							<div class="content">Цвет &mdash;
								@foreach($content['colors'] as $color)
									@if($color['id'] == $content['id']) {{ $color['title'] }} @endif
								@endforeach
							</div>
						@endif
					</div>
					<div class="colors">
						@for($i=0; $i<count($content['colors']); $i++)
							<div class="choose-color @if($content['colors'][$i]['id'] == $content['id']) active @endif" data-refer="{{ $content['colors'][$i]['id'] }}">
								<span style="background-color: {{ $content['colors'][$i]['color'] }}"></span>
							</div>
						@endfor
					</div>
				</div>

				<a class="el-button orenda" href="#">Взять в аренду</a>
				<div class="ask">
					<a class="fancybox-form" href="#call-popup">
						<span><img src="{{ URL::asset('/img/support_cart.png') }}" alt=""></span>
						<span>Помощь при аренде. Заказать обратный звонок.</span>
					</a>
				</div>
				<div class="advantages">
					@if( (!empty($content['data']['fieldset_1']['string_0']['value'])) && ($content['data']['fieldset_1']['string_0']['value'] > 0))
					<div class="adv-item" title="Количество мест">
						<img src="{{ URL::asset('img/people_cart.png') }}" alt="">
						<p data-name="seat">до {{ $content['data']['fieldset_1']['string_0']['value'] }}</p>
					</div>
					@endif
					<div class="adv-item" title="Топливная система">
						<img src="{{ URL::asset('img/oil2_cart.png') }}" alt="">
						<p data-name="fuel-system">{{ $content['data']['fieldset_1']['category_1'][0]['title'] }}</p>
					</div>
					@if($content['data']['fieldset_1']['number_0']['value'] > 0)
					<div class="adv-item" title="Потребление топлива">
						<img src="{{ URL::asset('img/oil_cart.png') }}" alt="">
						<p data-name="fuel-consume">{{ $content['data']['fieldset_1']['number_0']['value'] }}л/100км</p>
					</div>
					@endif
					@if($content['data']['fieldset_1']['number_1']['value'] > 0)
					<div class="adv-item" title="Мощность двигателя">
						<img src="{{ URL::asset('img/engine_cart.png') }}" alt="">
						<p data-name="engine-power">{{ $content['data']['fieldset_1']['number_1']['value'] }} л.с.</p>
					</div>
					@endif
					<div class="adv-item" title="Тип трансмиссии">
						<img src="{{ URL::asset('img/transmission.png') }}" alt="">
						<p data-name="transmission">{{ $content['data']['fieldset_1']['category_0'][0]['title'] }}</p>
					</div>
				</div>

				<a href="#video-popup" class="video fancybox-form" @if(empty($content['video'])) style="display: none;" @endif>
					<span><img src="{{ URL::asset('img/video_cart.png') }}" alt=""></span>
					<span>Посмотреть видео-обзор этого транспорта</span>
				</a>
				<div class="functions" @if(empty($content['data']['category_3'])) style="display: none;" @endif>
					<a href="#" class="options">
						<span><img src="{{ URL::asset('img/change_cart.png') }}" alt=""></span>
						<span>Дополнительные опции при оформлении аренды</span>
					</a>
					<div class="options-hidden">
						@foreach($content['data']['category_3'] as $option)
							<div class="item">
								<div class="name">{{ $option['title'] }}</div>
								<div class="price">
									@if(!empty($option['data']['string_0']['value']))
										{{ $option['data']['string_0']['value'] }} р в сутки
									@else
										{{ $option['data']['string_1']['value'] }}
									@endif
								</div>
								<div class="pic"><img src="{{ $option['img_url'][0]['img'] }}" alt="{{ $option['img_url'][0]['alt'] }}"></div>
							</div>
						@endforeach
					</div>
				</div>

			</div>
		</section>

		<section class="mbox wrapper-details nondrive-details">
			<div class="details">
				<p>Оборудование установленное на этот транспорт</p>
				<a href="#"><img src="{{ URL::asset('img/plus.png') }}" alt=""></a>
			</div>
			<div class="equipment-hidden">
				@if(!empty($content['text']))
				{!! $content['text'] !!}
				@else
				{!! $content['description'] !!}
				@endif
			</div>
			<div class="available-tariffs">
				@foreach($content['data']['category_2'] as $rent)
					<div class="tar-item">
						<div class="title">{{ $rent['title'] }}</div>
						<div class="price">
						@if(!empty($rent['data']['string_0']['value']))
							<?php
							switch($rent['data']['string_0']['value']){
								case '[%price%]': $current_price = [number_format($content['price'],0, '', ' '), 'в час']; break;
								case '[%1-2%]': $current_price = [$content['data']['fieldset_0']['number_4']['value'], 'в сутки']; break;
								case '[%3-6%]': $current_price = [$content['data']['fieldset_0']['number_3']['value'], 'в сутки']; break;
								case '[%7-13%]': $current_price = [$content['data']['fieldset_0']['number_2']['value'], 'в сутки']; break;
								case '[%14-30%]': $current_price = [$content['data']['fieldset_0']['number_1']['value'], 'в сутки']; break;
								case '[%by_31%]': $current_price = [$content['data']['fieldset_0']['number_0']['value'], 'в сутки']; break;
								case '[%holiday%]': $current_price = [$content['data']['fieldset_0']['number_6']['value'], 'в сутки']; break;
							}
							?>
							<p>от <b>{{ $current_price[0] }}</b> <i>₽</i> </p>
							<span><strong>/</strong>{{ $current_price[1] }}</span>
						@else
							@if(strpos($rent['data']['string_1']['value'], '[%week%]') === false)
								<p><i></i><b>{{ $rent['data']['string_1']['value'] }}</b></p>
							@else
								<?php
								$temp = str_replace(['[%week%]'], $content['data']['fieldset_0']['number_5']['value'], $rent['data']['string_1']['value']);
								?>
								<p><i></i><b>{{ $temp }}</b></p>
							@endif
						@endif
						</div>
						<?php
						$descript = array_diff(explode("\n",$rent['text']), ['',"\r","\n"]);
						?>
						@foreach($descript as $item)
							<div class="desc">{{ strip_tags($item) }}</div>
						@endforeach
						<div class="prompt-hidden">
							{!! $rent['data']['fulltext_0']['value'] !!}
						</div>
					</div>
				@endforeach
			</div>
		</section>
		<div class="mbox">
			@if(!empty($recomended))
			<section class="mbox recomended-cars">
				<div class="recom-title">Рекомендуем посмотреть эти автомобили:</div>

				<div class="all-cars all-cars-slider">
				@foreach($recomended as $car)
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
						<p>{{ $car['title'] }}</p>
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
				<div class="prev"><img src="{{ URL::asset('img/prev.png') }}" alt=""></div>
				<div class="next"><img src="{{ URL::asset('img/next.png') }}" alt=""></div>
			</section>
			@endif

			@if(!empty($visited))
			<section class="mbox watched-cars">
				<div class="recom-title">Ранее просмотренный транспорт:</div>
				<div class="all-cars all-cars-watched">
				@foreach($visited as $car)
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
						<p>{{ $car['title'] }}</p>
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
			@endif
		</div>
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop