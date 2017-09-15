@extends('layouts.default')
@section('content')
<div class="main">
	<!-- add partials here -->
	<section class="mbox first-step first-step-driver">
		<div class="headline" data-type="withDriver">
			<div class="title">Оформление заказа</div>
			<div class="steps">
				<ul>
					<li class="active">1. Информация</li>
					<li>2. Подтверждение</li>
				</ul>
			</div>
		</div>
		<div class="tab-switch active">
			<div class="tariffs" data-name="tarif-type">
				<div class="tarif-title">Выберите тариф</div>
				<div class="el-tariffs" data-price="{{ $content['price'] }}">
					@for($i=0; $i<count($content['tarifs']); $i++)
						<div class="tarif-item js-tariff" data-pos="{{ $content['tarifs'][$i]['id'] }}">
							<p>{{ $content['tarifs'][$i]['title'] }}</p>
							@if(!empty($content['tarifs'][$i]['data']))
							<div class="item @if($i == 0) chosen @endif">
								@if(!empty($content['tarifs'][$i]['data']['string_0']['value']))
									<?php
									switch($content['tarifs'][$i]['data']['string_0']['value']){
										case '[%price%]': $current_price = $content['price']; break;
										case '[%1-2%]': $current_price = $content['prices']['number_4']['value']; break;
										case '[%3-6%]': $current_price = $content['prices']['number_3']['value']; break;
										case '[%7-13%]': $current_price = $content['prices']['number_2']['value']; break;
										case '[%14-30%]': $current_price = $content['prices']['number_1']['value']; break;
										case '[%by_31%]': $current_price = $content['prices']['number_0']['value']; break;
										case '[%holiday%]': $current_price = $content['prices']['number_6']['value']; break;
									}
									?>
									<div class="price" data-type="per_hour">от {{ $current_price }} руб. в час</div>
								@else
									@if(strpos($content['tarifs'][$i]['data']['string_1']['value'], '[%week%]') === false)
										<p><i></i><b>{{ $content['tarifs'][$i]['data']['string_1']['value'] }}</b></p>
									@else
										<?php
										$temp = str_replace(['[%week%]'], $content['prices']['number_5']['value'], $content['tarifs'][$i]['data']['string_1']['value']);
										?>
										<div class="price" data-type="per_hour">{{ $temp }}</div>
									@endif
								@endif
								<div class="item-desc">{!! $content['tarifs'][$i]['data']['fulltext_0']['value'] !!}</div>
							</div>
							@endif
						</div>
					@endfor
				</div>
			</div>
			<form id="driverOrder">
				<input name="_token" type="hidden" value="{{ csrf_token() }}">
				<div class="order-info">
					<div class="info-title">Информация о заказе</div>
					<div class="info-items">
						<div class="item">
							<p>Город</p>
							<select name="city" class="oplata" data-placeholder="Выберите город" required="required">
								<option></option>
								@foreach($defaults['cities'] as $city)
									<option value="{{ $city['slug'] }}" @if($city['id'] == $defaults['current_city']['id']) selected="selected" @endif>{{ $city['title'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="item">
							<p>Место начала аренды</p>
							<input type="text" placeholder="Выберите место" name="start_place" required="required">
						</div>
						<div class="item">
							<p>Место окончания аренды</p>
							<input type="text" placeholder="Выберите место" name="finish_place" required="required">
						</div>
						<div class="item">
							<p>Оплата</p>
							<select name="oplata" class="oplata" data-placeholder="Выберите способ оплаты" required="required">
								<option></option>
								<option>Наличными</option>
								<option>Картой</option>
							</select>
						</div>
						<div class="page-line-item item" data-name="start">
							<div class="heading">
								<h4>Дата и время начала аренды</h4>
							</div>
							<div class="input-field start-rent">
								<div class="left-part">
									<img src="{{ URL::asset('img/calendar-ico.png') }}" alt="">
									<p>xx/xx/xx</p>
								</div>
								<span></span>
								<div class="right-part">
									<img src="{{ URL::asset('img/clock-ico.png') }}" alt="">
									<p><span>00</span>:<span>00</span></p>
								</div>
							</div>
							<div class="dropdown-datepicker">
								<div class="datepicker-heading">
									<div class="head-tab clicked">
										<img src="{{ URL::asset('img/calendar-ico.png') }}" alt="">
										<h4>Выбор даты</h4>
									</div>
									<div class="head-tab">
										<img src="{{ URL::asset('img/clock-ico.png') }}" alt="">
										<h4>Выбор времени</h4>
									</div>
								</div>

								<div class="main-date-content">
									<div class="main-date-tab">
										<div class="datepicker-init" id="nonedriver-datepicker-start-order"></div>
										<input type="text" name="datepicker-start" id="for-nonedriver-datepicker-start-order" required="required">
									</div>
									<div class="main-date-tab">
										<h4><label for="nonedriver-hourpicker-start">Часы</label></h4>
										<input type="text" name="hourpicker-start" class="timepicker-init timepicker-hours" placeholder="Выберите" id="nonedriver-hourpicker-start-order" required="required">

										<h4><label for="nonedriver-minutepicker-start">Минуты</label></h4>
										<input type="text" name="minutepicker-start" class="timepicker-init timepicker-minutes" placeholder="Выберите" id="nonedriver-minutepicker-start-order" required="required">
										<!-- <div class="timepicker-init"></div> -->
									</div>
								</div>
								<button class="datepicker-button">Принять</button>
							</div>
							<div class="error-message-hour">
								<p>Нельзя продолжить, так как вы не выбрали дату</p>
							</div>
							<div class="error-message-minutes">
								<p>Нельзя продолжить, так как вы не выбрали время</p>
							</div>
						</div>

						<div class="page-line-item item" data-name="finish">
							<div class="heading">
								<h4>Дата и время конца аренды</h4>
							</div>
							<div class="input-field end-rent">
								<div class="left-part">
									<img src="{{ URL::asset('img/calendar-ico.png') }}" alt="">
									<p>xx/xx/xx</p>
								</div>
								<span></span>
								<div class="right-part">
									<img src="{{ URL::asset('img/clock-ico.png') }}" alt="">
									<p><span>00</span>:<span>00</span></p>
								</div>
							</div>
							<div class="dropdown-datepicker">
								<div class="datepicker-heading">
									<div class="head-tab clicked">
										<img src="{{ URL::asset('img/calendar-ico.png') }}" alt="">
										<h4>Выбор даты</h4>
									</div>
									<div class="head-tab">
										<img src="{{ URL::asset('img/clock-ico.png') }}" alt="">
										<h4>Выбор времени</h4>
									</div>
								</div>
								<div class="main-date-content">
									<div class="main-date-tab">
										<div class="datepicker-init" id="nonedriver-datepicker-end-order"></div>
										<input type="text" name="datepicker-end" id="for-nonedriver-datepicker-end-order" required>
									</div>
									<div class="main-date-tab">
										<h4><label for="nonedriver-hourpicker-end">Часы</label></h4>
										<input type="text" name="hourpicker-end" class="timepicker-init timepicker-hours" placeholder="Выберите" id="nonedriver-hourpicker-end-order" required="required">

										<h4><label for="nonedriver-minutepicker-end">Минуты</label></h4>
										<input type="text" name="minutepicker-end" class="timepicker-init timepicker-minutes" placeholder="Выберите" id="nonedriver-minutepicker-end-order" required="required">

										<!-- <div class="timepicker-init"></div> -->
									</div>
								</div>
								<button class="datepicker-button">Принять</button>
							</div>
						</div>
					</div>
				</div>
				<div class="contacts">
					<div class="contacts-title">Контактные данные</div>
					<div class="main-info">
						<div class="form_input">
							<input type="text" placeholder="Ваше имя" name="name" required="required">
						</div>
						<div class="form_input">
							<input type="text" id="phone" placeholder="Телефон" name="phone" required="required">
						</div>
						<div class="form_input">
							<input type="email" placeholder="Email (не обязательно)" name="email">
						</div>
					</div>
				</div>
				@if(!empty($content['options']))
				<div class="tariffs" data-name="equipment">
					<div class="tarif-title">Выберите дополнительное оборудование</div>
					<div class="el-tariffs">
						@foreach($content['options'] as $option)
							<div class="tarif-item js-add-env">
								<p>{{ $option['title'] }}</p>
								<div class="item item-sec">
									@if(!empty($option['data']['string_0']['value']))
										<div class="price">{{ $option['data']['string_0']['value'] }} руб. в день</div>
									@else
										<div class="price">{{ $option['data']['string_1']['value'] }}</div>
									@endif

									<div class="item-pic">
									@if(!empty($option['img_url']))
										<img src="{{ URL::asset($option['img_url'][0]['img']) }}" alt="{{ $option['img_url'][0]['alt'] }}">
									@endif
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
				@endif
				<button type="submit" class="el-button" name="driverNextStep">Продолжить</button>
			</form>
		</div>

		<div class="tab-switch third-step active" data-type="driver">
			<div class="wrap-confirm">
				<div class="conf-title conf-title-big">Подтверждение заказа аренды с водителем</div>
				<div class="car-info">
					<div class="car-photo"><img src="{{ $content['images']['img'] }}" alt="{{ $content['images']['alt'] }}"></div>
					<div class="car-info-details">
						<div class="name">{{ $content['title'] }}</div>
						@if(!empty($content['color']))
						<div class="color">Цвет — {{ $content['color']->title }}&nbsp;<span class="red" style="background-color: {{$content['color']->color}}"></span></div>
						@endif
						<div class="pluses">
							@if( (!empty($content['seats'])) && ($content['seats'] > 0) )
							<div class="item">
								<img src="/img/people_cart.png" alt="">
								<p>до {{ $content['seats'] }}</p>
							</div>
							@endif
							@if(!empty($content['fuel_system']))
							<div class="item">
								<img src="/img/oil2_cart.png" alt="">
								<p>{{ $content['fuel_system'] }}</p>
							</div>
							@endif
							@if($content['fuel_consume'] > 0)
							<div class="item">
								<img src="/img/oil_cart.png" alt="">
								<p>{{ $content['fuel_consume'] }}/100км</p>
							</div>
							@endif
							@if($content['engine_power'] > 0)
							<div class="item">
								<img src="/img/engine_cart.png" alt="">
								<p>{{ $content['engine_power'] }} л.с.</p>
							</div>
							@endif
							@if(!empty($content['transmission']))
							<div class="item">
								<img src="/img/transmission.png" alt="">
								<p>{{ $content['transmission'] }}</p>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
			<div class="wrap-confirm" data-name="tarif-type">
				<div class="conf-title">Действующий тариф</div>
				<div class="cur-tarif">
					<div class="tar-name"></div>
					<div class="tar-desc">
						<div class="price"></div>
						<div class="item-desc">
							<p></p>	
						</div>
					</div>
				</div>
			</div>
			@if(!empty($content['options']))
			<div class="wrap-confirm" data-name="equipment">
				<div class="conf-title">Выберанное дополнительное оборудование</div>

			</div>
			@endif
			<div class="end-price">Итоговая стоимость — <b>7 000</b> руб.</div>
			<button type="submit" class="el-button" name="sendRequest">Продолжить</button>
		</div>
	</section>
</div>
@stop
