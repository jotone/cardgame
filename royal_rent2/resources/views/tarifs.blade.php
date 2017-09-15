@extends('layouts.default')
@section('content')
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
	<section class="mbox wrap-tariffs">
		<div class="tarif-title">{{ $meta['title'] }}</div>
		@foreach($content as $vehicle_type)
			<div class="tariff-item">
				<div class="link">{{ $vehicle_type['title'] }} <span><img src="{{ URL::asset('/img/arrow.png') }}" alt=""></span></div>
				<div class="unvisible">
				@foreach($vehicle_type['items'] as $category)
					<div class="table-title">{{ $category['title'] }}</div>
					<div class="wrap-table">
						<?php
						$header = [];
						foreach($category['cars'] as $car){
							foreach($car['tarifs'] as $tarif){
								$header[\App\Http\Controllers\Supply\Functions::str2url($tarif['title'])] = $tarif['title'];
							}
						}
						?>
						<div class="table-header">
							<div class="column">Автомобиль</div>
							<div class="cell-description">
								@foreach($header as $item)
								<div class="column">{{ $item }}</div>
								@endforeach
							</div>

						</div>

						<div class="table-body">
						@foreach($category['cars'] as $car)
							<div class="table-row">
								<div class="table-col">
									<div class="car-info">
										<span class="fast-order"><a href="#call-popup" class="fancybox-form">Быстрый заказ</a></span>
										<em>{{ $car['title'] }} {{ $car['color'] }} {{ $car['year'] }} год</em>
									</div>
									<span class="more"><a href="{{ URL::asset('/car/'.$category['slug'].'/'.$car['slug'].'-'.\App\Http\Controllers\Supply\Functions::str2url($car['color'])) }}">Подробнее</a></span>
								</div>
								<div class="cell-description">
									@foreach($header as $header_item)
										<div class="table-col">
										@foreach($car['tarifs'] as $car_tarif)
											@if($car_tarif['title'] == $header_item)
												@if(!empty($car_tarif['value']))
													@if($car['discount'] > 0)
														<span class="sale">-<b>{{ $car['discount'] }}</b>%</span>
													@endif

													@if(!empty($car_tarif['value']))
														<?php
														switch($car_tarif['value']){
															case '[%price%]': $current_price = [number_format($car['price'],0, '', ' '), 'час']; break;
															case '[%1-2%]': $current_price = [$car['prices']['number_4']['value'], 'сутки']; break;
															case '[%3-6%]': $current_price = [$car['prices']['number_3']['value'], 'сутки']; break;
															case '[%7-13%]': $current_price = [$car['prices']['number_2']['value'], 'сутки']; break;
															case '[%14-30%]': $current_price = [$car['prices']['number_1']['value'], 'сутки']; break;
															case '[%by_31%]': $current_price = [$car['prices']['number_0']['value'], 'сутки']; break;
															case '[%holiday%]': $current_price = [$car['prices']['number_6']['value'], 'сутки']; break;
														}
														?>
														@if(!empty($current_price))
														<p><b>{{ $current_price[0] }}</b> <i>&#8381;</i>/ {{ $current_price[1] }}</p>
														@endif
													@else
														@if(strpos($car_tarif['value'], '[%week%]') === false)
															@if(!empty($car_tarif['value']))
															<p><i></i><b>{{ number_format($car_tarif['value'], 0, '', ' ') }}</b></p>
															@endif
														@else
															<?php
															$temp = str_replace(['[%week%]'], $content['prices']['number_5']['value'], $rent['data']['string_1']['value']);
															?>
															@if(!empty($temp))
															<p><i></i><b>{{ number_format($temp, 0, '', ' ') }}</b></p>
															@endif
														@endif
													@endif
												@endif
											@endif
										@endforeach
										</div>
									@endforeach
								</div>
							</div>
						@endforeach
						</div>
					</div>
				@endforeach
				</div>
			</div>
		@endforeach
	</section>
	@include('layouts.attachment')
</div>
@stop