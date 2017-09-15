@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
?>
<!-- MAIN -->
<div class="main">
	<!-- add partials here -->
	<section class="mbox first-step">
		<div class="headline" data-type="noneDriver">
			<div class="title">Оформление заказа</div>
			<div class="steps">
				<ul>
					<li class="active">1. Информация</li>
					<li>2. Доп. опции</li>
					<li>3. Подтверждение</li>
				</ul>
			</div>
		</div>
		<div class="tab-switch step-null active">
			<div class="tariffs">
				<div class="tarif-title">Вы можете воспользоваться специальным тарифом, либо пропустить этот пункт</div>
				<div class="el-tariffs">
					@foreach($content['tarifs'] as $tarif)
						<div class="tarif-item js-tariff" data-pos="{{ $tarif['id'] }}">
							<p>{{ $tarif['title'] }}</p>
							@if(!empty($tarif['data']))
								<div class="item">
									@if(!empty($tarif['data']['string_0']['value']))
										<?php
										switch($tarif['data']['string_0']['value']){
											case '[%price%]': $current_price = $content['price']; break;
											case '[%1-2%]': $current_price = $content['prices']['number_4']['value']; break;
											case '[%3-6%]': $current_price = $content['prices']['number_3']['value']; break;
											case '[%7-13%]': $current_price = $content['prices']['number_2']['value']; break;
											case '[%14-30%]': $current_price = $content['prices']['number_1']['value']; break;
											case '[%by_31%]': $current_price = $content['prices']['number_0']['value']; break;
											case '[%holiday%]': $current_price = $content['prices']['number_6']['value']; break;
										}
										?>
										<div class="price" data-type="per_hour">от {{ $current_price }} руб. в сутки</div>
									@else
										@if(strpos($tarif['data']['string_1']['value'], '[%week%]') === false)
											<p><i></i><b>{{ $tarif['data']['string_1']['value'] }}</b></p>
										@else
											<?php
											$temp = str_replace(['[%week%]'], $content['prices']['number_5']['value'], $tarif['data']['string_1']['value']);
											?>
											<div class="price" data-type="per_hour">{{ $temp }}</div>
										@endif
									@endif
									<div class="item-desc">{!! $tarif['data']['fulltext_0']['value'] !!}</div>
								</div>
							@endif
						</div>
					@endforeach
				</div>
			</div>
			<form id="noneDriverOrderStepOne">
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
							<p>Где забрать автомобиль</p>
							<input type="text" placeholder="Укажите место" name="startPlace">
						</div>
						<div class="item">
							<p>Где вернуть автомобиль</p>
							<input type="text" placeholder="Укажите место" name="finishPlace">
						</div>
						<div class="item parent-item">
							<div class="subitem">
								<p>Ваш возраст</p>
								<input type="number" name="age" class="age" data-placeholder="Выберите возраст" min="18" step="1" value="18">
							</div>
							<div class="subitem">
								<p>Стаж вождения</p>
								<input type="number" name="staz"  class="staz" data-placeholder="Выберите стаж" min='1' step="1" value="1">
							</div>
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

						<div class="item">
							<p>Оплата</p>
							<select name="oplata" class="oplata" data-placeholder="Выберите  способ оплаты">
								<option></option>
								<option selected value="cash">Наличными</option>
								<option value="card">Картой</option>
							</select>
						</div>
					</div>
				</div>
				<div class="contacts-title">Контактные данные</div>
				<div class="main-info">
					<div class="form_input">
						<input type="text" placeholder="Ваше имя" name="name" required="required" @if($user) value="{{ $user['name'] }}" @endif>
					</div>
					<div class="form_input">
						<input type="text" id="phone" placeholder="Телефон" name="phone" required="required">
					</div>
					<div class="form_input">
						<input type="email" placeholder="Email (не обязательно)" name="email" @if($user) value="{{ $user['email'] }}" @endif>
					</div>
				</div>

				<div class="contacts">
					<div class="check-field" id="allfilds">
						<input id="all-filds" type="checkbox" name="all_fields">
						<label for="all-filds"><span></span>Я хочу заполнить все данные, чтобы все документы были готовы к моему приезду</label>
					</div>
					<div class="full-info">
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Фамилия" name="info_surname">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Серия, номер водительского удостоверения" name="info_driver_id">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Серия, номер паспорта" name="info_pasport_id">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Регистрация по месту жительства" name="info_registration">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Адрес фактического проживания" name="info_address">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Имя" name="info_name" required="required">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Дата выдачи водительского удостоверения" name="info_drive_date">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Дата выдачи паспорта" name="info_passport_date">
						</div>
						<div class="form_input">
							<input type="text" id="phone" placeholder="Телефон по адресу регистрации (если есть)" name="info_address_phone">
						</div>
						<div class="form_input">
							<input type="text" id="phone" placeholder="Телефон по фактическому адресу (если есть)" name="info_fact_phone">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Отчество" name="info_fathername">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Гражданство" name="info_citizenship">
						</div>
						<div class="form_input">
							<input class="need-req" type="text" placeholder="Кем выдан паспорт" name="info_passport_issue">
						</div>
					</div>
					<div class="check-field">
						<input id="agree" type="checkbox" name="agree" required="required">
						<label for="agree"><span></span>Я согласен(на) с условиями, перечисленными ниже</label>
					</div>
				</div>
				<div class="desc">Отметьте здесь в подтверждение своего безусловного согласия на обработку (на осуществление любых действий, операций) любым способом информации, относящейся к вашим персональным данным, указанной выше (далее персональные данные), в том числе на передачу указанных персональных данных и осуществление аналогичных действий, компании «Шкода-Сервис», а также любым иным компаниям, с которыми компания «Шкода-Сервис» по собственному усмотрению заключила/заключит соответствующие договоры, для следующих целей:<br>Предоставление заказанных/согласованных услуг<br>Я предупрежден(на), что сообщение ложных сведений или представление поддельных документов влечет ответственность, установленную законодательством. С действующими тарифами ознакомлен(на), возражений не имею.</div>
				<button type="submit" class="el-button" name="nonedriverStepOne">Продолжить</button>
			</form>
		</div>

		<div class="tab-switch step-one">
			@if(!empty($content['options']))
				<div class="tariffs">
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
			<div class="order-info">
				<div class="info-title">Ограничьте вашу ответственность</div>
				<div class="info-items info-items-small">
					<div class="item">
						<p>Выберите из списка</p>
						<select name="responsibility" class="oplata" data-placeholder="Выберите из списка">
							@foreach($settings['responsibility'] as $item)
								<option value="{{ $item['slug'] }}">
									{{ $item['title'] }}
									@if(!empty($item['data']['number_0']['value']))
										 — {{$item['data']['number_0']['value']}} руб. в день
									@endif
								</option>
							@endforeach
						</select>
					</div>
					<div class="item">
						<a href="#" class="more">Узнать подробнее о предложенных вариантах</a>
					</div>
				</div>
			</div>
			<div class="order-info">
				<div class="info-title">Покрытие повреждений стекол, фар и автошин</div>
				<div class="info-items info-items-small">
					<div class="item">
						<p>Выберите из списка</p>
						<select name="damage_coverage" class="oplata" data-placeholder="Выберите из списка">
							@foreach($settings['damage_coverage'] as $item)
								<option value="{{ $item['slug'] }}">
									{{ $item['title'] }}
									@if(!empty($item['data']['number_0']['value']))
										— {{$item['data']['number_0']['value']}} руб. в день
									@endif
								</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
			<div class="wrap-zones">
				<div class="zones-title">Выезды за область</div>
				<div class="zones">
					<p>Выберите зоны, в которые вы хотите выезжать обслуживания:</p>
					@foreach($settings['ride_out'] as $item)
						<div class="check-field">
							<input id="zone{{ $item['slug'] }}" type="checkbox" name="{{ $item['slug'] }}" value="{{$item['data']['number_0']['value']}}">
							<label for="zone{{ $item['slug'] }}">
								<span></span>{{ $item['title'] }}
								@if(!empty($item['data']['number_0']['value']))
								— {{$item['data']['number_0']['value']}} руб. в день
								@endif
							</label>
						</div>
					@endforeach
				</div>
				<a href="#" class="more">Узнать подробнее о предложенных вариантах</a>
			</div>
			<div class="order-info">
				<div class="info-title">Залог &mdash; <span data-type="pledge">{{ $content['prices']['number_7']['value'] }}</span> руб.</div>
			</div>
			<button type="submit" class="el-button" name="nonedriverStepTwo">Продолжить</button>
		</div>

		<div class="tab-switch third-step" data-type="nonedriver">
			<div class="wrap-confirm">
				<div class="conf-title conf-title-big">Подтверждение заказа аренды без водителя</div>
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
			<div class="wrap-confirm" data-name="current-tarif">
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
			<div class="wrap-confirm" data-name="car-equip">
				<div class="conf-title">Выберанное дополнительное оборудование</div>
				<div class="cur-tarif">
					<div class="tar-name"></div>
					<div class="tar-desc">
						<div class="price"></div>
						<div class="pic"></div>
					</div>
				</div>
			</div>
			<div class="wrap-confirm" data-name="responsibility">
				<div class="conf-title">Ограничение вашей ответственности</div>
				<div class="desc"></div>
			</div>
			<div class="wrap-confirm" data-name="damage_coverage">
				<div class="conf-title">Покрытие повреждений стекол, фар и автошин</div>
				<div class="desc"></div>
			</div>
			<div class="wrap-confirm special" data-name="ride_out">
				<div class="conf-title">Выезды за область</div>
				<div class="desc">Выбранные зоны, в которые вы хотите выезжать обслуживания:</div>

			</div>
			<div class="end-price">Итоговая стоимость — <b></b> руб.</div>
			<button type="submit" class="el-button" name="sendRequest">Продолжить</button>
		</div>
	</section>
</div>
@stop
