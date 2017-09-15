<div class="sort-bar">
	<div class="options">
		<a href="javascript:void(0)" class="filter" data-slider="wrap-slider">
			<span><img src="{{ URL::asset('/img/filter.png') }}" alt=""></span>
			<span>Фильтр</span>
		</a>
		<a href="javascript:void(0)" class="reset">
			<span><img src="{{ URL::asset('/img/close-icon.png') }}" alt=""></span>
			<span>Сбросить</span>
		</a>
	</div>
	<div class="sort">
		<ul class="dropdown-sorting" data-order="normal">
			<li>
				<a href="javascript:void(0)" class="main-link">
					<p>Упорядочить по: <span>По умолчанию</span></p>
					<span class="arrow"></span>
				</a>
				<ul>
					<li><a href="#" data-type="normal"><p>По умолчанию</p></a></li>
					<li><a href="#" data-type="cheap_to_costly"><p>Цене: сначала дешевые</p></a></li>
					<li><a href="#" data-type="costly_to_cheap"><p>Цене: сначала дорогие</p></a></li>
				</ul>
			</li>
		</ul>
		<a href="#" class="el-button" id="showRecomended">Показать спецпредложения</a>
	</div>
</div>

<div class="content-wrap">
	<section class="left-bar">
		<div class="wrap-sort">
			<div class="sort-item">
				<p>Вид аренды</p>
				<select name="driver" class="driver">
					@foreach($defaults['vehicle_type'] as $top_category)
						<option value="{{ $top_category['slug'] }}">{{ $top_category['title'] }}</option>
					@endforeach
				</select>
			</div>

			<div class="sort-item" id="transportCategories">
				<p>Вид транспорта: <span>{{ $defaults['vehicle_type'][0]['items'][0]['title'] }}</span></p>
				<ul class="dropdown-transport">
					<li>
						<a href="#" class="main-link" data-refer="{{ $defaults['vehicle_type'][0]['slug'] }}" data-type="{{ $defaults['vehicle_type'][0]['items'][0]['slug'] }}">
							<img src="{{ URL::asset($defaults['vehicle_type'][0]['items'][0]['img_url'][0]['img']) }}" alt="">
						</a>
						<ul>
							@foreach($defaults['vehicle_type'][0]['items'] as $item)
								<li data-refer="{{ $defaults['vehicle_type'][0]['slug'] }}">
									<a class="select-transport-category" href="#" data-type="{{ $item['slug'] }}">
										<img src="{{ URL::asset($item['img_url'][0]['img']) }}" alt="">
										<p>{{ $item['title'] }}</p>
									</a>
								</li>
							@endforeach
						</ul>
					</li>
				</ul>
				<div class="arrows"><img src="{{ URL::asset('img/arrows.png') }}" alt=""></div>
			</div>
			<div class="sort-item">
				<p>Марка</p>
				<?php
				$marks = (!empty($defaults['vehicle_type'][0]['items']))? \App\Http\Controllers\Supply\Helpers::markBycategory($defaults['vehicle_type'][0]['items'][0]['slug'], $defaults['vehicle_type'][0]['slug']): [];
				?>
				<select name="carMark" class="driver" data-placeholder="Любая марка">
					<option></option>
					@if(!empty($marks)))
					@foreach($marks as $mark)
						<option value="{{ $mark['slug'] }}">{{ $mark['title'] }}</option>
					@endforeach
					@endif
				</select>
			</div>
			<div class="sort-item">
				<p>Событие</p>
				<select name="carEvent" class="driver" data-placeholder="Любое событие">
					<option></option>
					@foreach($event_list as $event)
						<option value="{{ $event->slug }}">{{ $event->title }}</option>
					@endforeach
				</select>
			</div>
			<div class="sort-item">
				<p>Стоимость</p>
				<input name="minAmount" type="text" id="left-bar-min-amount" pattern="^[0-9]+$" readonly style="border:0; color:#f6931f; font-weight:bold; width: 48%; text-align: left;" value="{{ $price_limits['min'] }}">
				<input name="maxAmonut" type="text" id="left-bar-max-amount" pattern="^[0-9]+$" readonly style="border:0; color:#f6931f; font-weight:bold; width: 50%; text-align: right;" value="{{ $price_limits['max'] }}">
				<div id="left-bar-slider-range"></div>
			</div>
			<div class="sort-item color-picker">
				<p>Цвет</p>
				<select name="carColor" class="driver" data-placeholder="Любой цвет">
					<option></option>
					<option value="any">Любой цвет</option>
					@foreach($colors_list as $color_slug => $color)
						<option value="{{ $color_slug }}">{{ $color['title'] }}</option>
					@endforeach
				</select>
			</div>
			<div class="sort-item" style="text-align: center">
				<a class="el-button filter-button" href="#">Ok</a>
			</div>
		</div>
		<div class="wrap-slider">
			@foreach($comments_list as $comment)
				<div class="slide-item">
					@if(!empty($comment['img_url']))
					<div class="photo"><img src="{{ URL::asset($comment['img_url']) }}" alt=""></div>
					@endif
					<div class="name">{{ $comment['name'] }}</div>
					<div class="city">{{ $comment['city'] }}</div>
					<div class="text">
						{{ $comment['text'] }}
					</div>
				</div>
			@endforeach
		</div>
		<a href="{{ URL::asset(route('reviews')) }}" class="read-all">
			<span class="icon"><img src="{{ URL::asset('/img/eye-one.png') }}" alt=""></span>
			<span class="text">Читать все отзывы</span>
		</a>
	</section>

	<div style="width: 100%">
		<div class="all-cars" data-page="1" data-type="{{ $type }}" style="display: flex !important;">
		@if(empty($cars))
			<p style="font-size:20px; font-weight: bold; width: 100%; text-align: center; margin: 30px auto; ">Здесь пока ничего нет</p>
		@endif
		@foreach($cars as $car_array)
			<div class="car-item" data-pos="{{ $car_array['id'] }}">
				<?php
				if((!empty($car_array['data']['number_0']['value'])) && ($car_array['data']['number_0']['value'] > 0)){
					$promo['val'] = $car_array['data']['number_0']['value'];
					$promo['type'] = 'percent';
				}else{
					if(!empty($car_array['promo'])){
						if(\App\Http\Controllers\Supply\Functions::is_serialized($car_array['promo'])){
							$car_array['promo'] = unserialize($car_array['promo']);
						}
						$promo['val'] = $car_array['promo']->value;
						$promo['type'] = $car_array['promo']->type;
					}else{
						$promo['val'] = 0;
					}
				}
				?>
				@if($promo['val'] > 0)
					<div class="sale">
						<span>скидка</span>
						@if($promo['type'] == 'percent')
							<span><b>{{ $promo['val'] }}</b>%</span>
						@else
							<span>&minus;<b>{{ $promo['val'] }}</b> <i>₽</i></span>
						@endif
					</div>
				@endif
				<div class="photo">
					@if(!empty($car_array['img_url']))
						<img src="{{ URL::asset($car_array['img_url']['img']) }}" alt="{{ $car_array['img_url']['alt'] }}">
					@endif
				</div>
				<a href="#photo-slider" class="view-photo">
					<span><img src="{{ URL::asset('img/pic-icon.png') }}" alt=""></span>
					<span>Посмотреть реальные фото</span>
				</a>
				<p>{{ $car_array['title'] }}</p>
				@if($car_array['price'] > 0)
					<p>от {{ number_format($car_array['price'],0, '',' ') }} &#8381;/ в час</p>
				@else
					<p>от
						<?php
						if(empty($car_array['data']['fieldset_0']['number_1']['value'])){
							$car_array['data']['fieldset_0']['number_1']['value'] = 0;
						}
						?>
						{{ number_format($car_array['data']['fieldset_0']['number_1']['value'],0, '',' ') }}
						&#8381;/ в сутки
					</p>
				@endif
				<div class="colors">
					@if(!empty($car_array['color']))
					<div class="color" style="background-color: {{ $car_array['color']['color'] }}" title="{{ $car_array['color']['title'] }}"></div>
					@endif
				</div>
				<a href="{{ URL::asset('/car/'.$car_array['upper_cat'].'/'.$car_array['slug']) }}" class="el-button">Взять в аренду</a>
			</div>
		@endforeach
		</div>
		@if(count($cars) >= 6)
			<div class="mbox wrap-numbers" id="carFilterPagination">
				<div class="show-more-wrap" id="showMore">
					<a href="#"><img src="{{ URL::asset('/img/show-more.png') }}" alt="">
						<p>Показать еще</p>
					</a>
				</div>
				<div class="show-more-wrap" id="preloader" style="display: none">
					<img src="{{ URL::asset('img/fancybox_loading@2x.gif') }}" alt="loading...">
				</div>
			</div>
		@endif
	</div>
</div>
