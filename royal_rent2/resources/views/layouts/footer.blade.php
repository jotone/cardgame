<?php
$user = Auth::user();
?>
<div class="hidden-block">
	<div id="call_success">
		<div class="call-success">
			<div class="call_success">
				<div class="call-title">Спасибо за заявку!</div>
				<div class="call-subtitle">Мы свяжемся с Вами в ближайшее время</div>
			</div>
		</div>
	</div>

	<div id="call-popup">
		<form action="{{ route('send-mail') }}" name="contactForm" class="contact-form" method="POST">
			<input name="_token" type="hidden" value="{{ csrf_token() }}">
			<input name="type" type="hidden" value="oformlenie_zayavki">
			<input name="order_case" type="hidden" value="">
			<input name="etc" type="hidden">
			@if(isset($meta['title']))
			<input name="page_title" type="hidden" value="{{ $meta['title'] }} в {!! $defaults['current_city']['data']['string_1']['value'] !!}">
			@endif
			<div class="contact-form-title">
				<h5>В ближайшее время мы свяжемся с вами</h5>
			</div>
			<div class="contact-form-row cfix">
				<div class="contact-form-item">
					<div class="contact-form-item-input form_row">
						<div class="form_input">
							<input type="text" name="name" required="required" placeholder="Ваше Имя" @if($user) value="{{ $user['name'] }}" @endif>
						</div>
					</div>
				</div>
				<div class="contact-form-item">
					<div class="contact-form-item-input form_row">
						<div class="form_input">
							<input type="text" name="phone" required="required" placeholder="Ваш телефон" class="tel-mask" @if($user) value="{{ $user['phone'] }}" @endif>
						</div>
					</div>
				</div>
				<div class="contact-form-item">
					<div class="contact-form-item-input form_row">
						<div class="form_input">
							<textarea name="comment" placeholder="Комментарий"></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="contact-form-row cfix">
				<div class="contact-form-item">
					<button type="submit" class="el-button contact-submit">
						<span>Отправить</span>
					</button>
				</div>
			</div>
		</form>
	</div>

	<div id="corp-contract">
		<h5>Запрос корпоративного договора</h5>
		<form class="corp-contract-form">
			<input name="_token" type="hidden" value="{{ csrf_token() }}">
			<div class="form-field">
				<input type="text" name="company" placeholder="Название вашей компании" required />
			</div>
			<div class="form-field">
				<input type="text" name="contact" placeholder="Контактное лицо" required />
			</div>
			<div class="form-field">
				<input type="text" name="phone" placeholder="Контактный телефон" required />
			</div>
			<div class="form-field">
				<input type="email" name="contact_mail" placeholder="E-mail" required />
			</div>
			<b>Выберите виды обслуживания:</b>
			<div class="checkbox-wrap">
				<div class="checkbox-col">
					<div class="check-field">
						<input id="avia" type="checkbox" name="sevices" value="Авиаперевозки">
						<label for="avia"><span></span>Авиаперевозки</label>
					</div>
					<div class="check-field">
						<input id="vip" type="checkbox" name="sevices" value="VIP-обслуживание">
						<label for="vip"><span></span>VIP-обслуживание</label>
					</div>
					<div class="check-field">
						<input id="ground" type="checkbox" name="sevices" value="Наземное обслуживание">
						<label for="ground"><span></span>Наземное обслуживание</label>
					</div>
				</div>
				<div class="checkbox-col">
					<div class="check-field">
						<input id="train" type="checkbox" name="sevices" value="Железнодорожные перевозки">
						<label for="train"><span></span>Железнодорожные перевозки</label>
					</div>
					<div class="check-field">
						<input id="hotels" type="checkbox" name="sevices" value="Отели">
						<label for="hotels"><span></span>Отели</label>
					</div>
					<div class="check-field">
						<input id="visaSup" type="checkbox" name="sevices" value="Визовая поддержка">
						<label for="visaSup"><span></span>Визовая поддержка</label>
					</div>
				</div>
			</div>
			<div class="form-field">
				<input type="text" name="orders_volume" placeholder="Ориентировочный объем заказов в месяц (необязательно)">
			</div>
			<div class="form-field">
				<textarea name="etc_data" placeholder="Дополнительные комментарии (необязательно)"></textarea>
			</div>
			<div class="button-center">
				<input class="el-button" type="button" value="Отправить">
			</div>
		</form>
	</div>

	<div id="operational-request">
		<h5>В ближайшее время мы свяжемся с вами</h5>
		<form class="request-form" action="/" method="post">
			<div class="request-wrap">
				<div class="request-col">
					<div class="form-field">
						<span>Тариф</span>
						<select id="tariff-select" class="choose choose-ph" data-placeholder="Выберите тариф">
							<option></option>
							<option>Пункт 1</option>
							<option>Пункт 2</option>
						</select>
					</div>
					<div class="request-row">
						<div class="form-field">
							<span>Марка автомобиля</span>
							<select id="brand-select" class="choose choose-ph" data-placeholder="Выберите марку">
								<option></option>
								<option>Пункт 1</option>
								<option>Пункт 2</option>
								<option>Пункт 3</option>
							</select>
						</div>
						<div class="form-field">
							<span>Модель автомобиля</span>
							<select id="model-select" class="choose choose-ph" data-placeholder="Выберите модель">
								<option></option>
								<option>Пункт 1</option>
								<option>Пункт 2</option>
							</select>
						</div>
					</div>
					<div class="form-field">
						<span>Ваше ФИО</span>
						<input type="text" name="name" placeholder="Введите ФИО" required />
					</div>
					<div class="form-field">
						<span>Ваш номер телефона</span>
						<input id="phone" type="text" name="phone" placeholder="Введите номер телефона" required />
					</div>
					<div class="form-field">
					</div>
				</div>
				<div class="request-col">
					<div class="form-field">
						<span>Где получить автомобиль</span>
						<select id="get-select" class="choose choose-ph" data-placeholder="Выберите место">
							<option></option>
							<option>Пункт 1</option>
							<option>Пункт 2</option>
						</select>
					</div>
					<div class="form-field">
						<span>Дата и время начала аренды</span>
						<input type="text" name="beginDate">
					</div>
					<div class="form-field">
						<span>Где вернуть автомобиль</span>
						<select id="return-select" class="choose choose-ph" data-placeholder="Выберите место">
							<option></option>
							<option>Пункт 1</option>
							<option>Пункт 2</option>
						</select>
					</div>
					<div class="form-field">
						<span>Дата и время окончания аренды</span>
						<input type="text" name="endDate">
					</div>
				</div>
			</div>
			<button class="el-button" type="submit">Отправить</button>
		</form>
	</div>

	<div id="add-review">
		<div class="write-rev">Напишите свой отзыв о нас</div>
		<form action="{{ route('add-review') }}" class="add-review add-review-form" method="PUT" target="_self">
			<input name="_token" type="hidden" value="{{ csrf_token() }}">
			<input name="_method" type="hidden" value="PUT">
			<input name="name" type="text" class="name" placeholder="Имя" required />
			<input name="surname" type="text" class="name" placeholder="Фамилия" required />
			<input name="location" type="text" class="name" placeholder="Ваш город" required />
			<select name="auto_has_driver" class="choose" data-placeholder="С водителем или без">
				<option></option>
				@foreach($defaults['vehicle_type'] as $vehicle)
					<option value="{{ $vehicle['slug'] }}">{{ $vehicle['title'] }}</option>
				@endforeach
			</select>
			<select name="choose" class="choose" data-placeholder="Выберите вид транспорта, который вы арендовали">
				<option></option>

			</select>
			<select name="auto_mark" class="choose" data-placeholder="Выберите марку транспорта, который вы арендовали">
				<option></option>
			</select>
			<select name="auto_model" class="choose" data-placeholder="Выберите модель транспорта, который вы арендовали">
				<option></option>
			</select>
			<div class="wrap-evaluation">
				<span>Ваша оценка:</span>
				<input name="eval" type="radio" id="five-star" value="5">
				<label for="five-star" class="stars-label"></label>
				<input name="eval" type="radio" id="four-star" value="4">
				<label for="four-star" class="stars-label"></label>
				<input name="eval" type="radio" id="three-star" value="3">
				<label for="three-star" class="stars-label"></label>
				<input name="eval" type="radio" id="two-star" value="2">
				<label for="two-star" class="stars-label"></label>
				<input name="eval" type="radio" id="one-star" value="1">
				<label for="one-star" class="stars-label"></label>
			</div>
			<textarea name="coment" class="coment" placeholder="Ваш комментарий" rows="10" maxlength="150"></textarea>
			<div class="wrap-send">
				<div class="not-robot"><div class="g-recaptcha" data-sitekey="6LdgtRsUAAAAALs1vgbamxRVtH0O7QJS-uiZODMp"></div></div>
				<button type="submit" class="el-button">Отправить</button>
			</div>
		</form>
	</div>

	<div id="order-call">
		<div class="write-rev">В ближайшее время мы свяжемся с вами</div>
		<form action="#" class="add-review">
			<input type="text" class="name" placeholder="Ваше имя" @if($user) value="{{ $user['name'] }}" @endif>
			<input type="text" class="name" placeholder="Ваш телефон" @if($user) value="{{ $user['phone'] }}" @endif>
			<a class="el-button" href="#">Отправить</a>
		</form>
	</div>

	<div id="country_picker">
		<div class="main-heading">
			<h3>Choose you country or region.</h3>
		</div>
		<div class="countries-heading">
			<h4>Europe</h4>
		</div>
		<div class="countris">
			<div class="countri-item">
				<a href="#" data-language='#googtrans(en)'>
					<img src="{{ URL::asset('img/england.png') }}" alt="English">
					English
				</a>
			</div>
			<div class="countri-item">
				<a href="#" data-language='#googtrans(zh-CN)'>
					<img src="{{ URL::asset('img/turkey.png') }}" alt="中国">
					中国
				</a>
			</div>
			<div class="countri-item">
				<a href="#" data-language='#googtrans(ru)'>
					<img src="{{ URL::asset('/img/russia1.png') }}" alt="Русский">
					Русский
				</a>
			</div>
		</div>
	</div>

	<div id="city_picker">
		<div class="main-heading">
			<h3>Выберите свой город</h3>
		</div>
		<div class="countries-heading">
			<h4>Выберите из списка<!-- или воспользуйтесь поиском--></h4>
		</div>
		<form name="option-city" class="city-picker-form" action="{{ route('set-city') }}" method="POST" target="_self">
			<input name="_token" type="hidden" value="{{ csrf_token() }}">
			<select name="city" class="city-select choose">
				<option disabled>Выберите из списка</option>
				@foreach($defaults['cities'] as $city)
					<option value="{{ $city['slug'] }}">{{ $city['title'] }}</option>
				@endforeach
			</select>

			<!--<input type="search" name="search-city" placeholder="Введите название города" class="city-search">-->
			<button class="standartYellow">Сохранить</button>
		</form>
	</div>

	<div id="headerCalculator">
		<div class="calculator-tab-wrapper">
			<div class="tabs-line">
				<div class="tab chosen" data-type="0">Расчет без водителя</div>
				<div class="tab" data-type="1">Расчет с водителем</div>
			</div>
			<div class="pages-line" data-type="none-driver">
				<form class="driver-cont" action="/send_letter" method="POST" data-type="onlajn_zayavka">
					<div class="page-line-item">
						<div class="heading">
							<h4>Город</h4>
						</div>
						<select name="calculator-city" class="calculator-city-select choose" required="required">
							<option></option>
							@foreach($defaults['cities'] as $city)
								<option value="{{ $city['slug'] }}">{{ $city['title'] }}</option>
							@endforeach
						</select>
					</div>

					<div class="page-line-item">
						<div class="heading">
							<h4>Место подачи автомобиля</h4>
						</div>
						<select name="calculator-place" class="city-search choose" required="required">
							<option></option>
						</select>
					</div>

					<div class="page-line-item">
						<div class="heading">
							<h4>Место возврата автомобиля</h4>
						</div>
						<select name="calculator-place-back" class="calculator-place-back-select choose" required="required">
							<option></option>
						</select>
					</div>

					<div class="page-line-item">
						<div class="heading">
							<h4>Оплата</h4>
						</div>
						<select name="calculator-place-pay" class="calculator-place-back-select choose" required="required">
							<option value="Наличными">Наличными</option>
							<option value="Картой">Картой</option>
						</select>
					</div>

					<div class="page-line-item" data-name="start">
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
									<div class="datepicker-init" id="nonedriver-datepicker-start"></div>
									<input type="text" name="datepicker-start" id="for-nonedriver-datepicker-start" required="required">
								</div>
								<div class="main-date-tab">
									<h4><label for="nonedriver-hourpicker-start">Часы</label></h4>
									<input type="text" name="hourpicker-start" class="timepicker-init timepicker-hours" placeholder="Выберите" id="nonedriver-hourpicker-start" required="required">

									<h4><label for="nonedriver-minutepicker-start">Минуты</label></h4>
									<input type="text" name="minutepicker-start" class="timepicker-init timepicker-minutes" placeholder="Выберите" id="nonedriver-minutepicker-start" required="required">
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

					<div class="page-line-item" data-name="finish">
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
									<div class="datepicker-init" id="nonedriver-datepicker-end"></div>
									<input type="text" name="datepicker-end" id="for-nonedriver-datepicker-end" required>
								</div>
								<div class="main-date-tab">
									<h4><label for="nonedriver-hourpicker-end">Часы</label></h4>
									<input type="text" name="hourpicker-end" class="timepicker-init timepicker-hours" placeholder="Выберите" id="nonedriver-hourpicker-end" required="required">

									<h4><label for="nonedriver-minutepicker-end">Минуты</label></h4>
									<input type="text" name="minutepicker-end" class="timepicker-init timepicker-minutes" placeholder="Выберите" id="nonedriver-minutepicker-end" required="required">

									<!-- <div class="timepicker-init"></div> -->
								</div>
							</div>
							<button class="datepicker-button">Принять</button>
						</div>
					</div>

					<div class="page-line-item">
						<div class="heading">
							<h4>Вид транспорта</h4>
						</div>
						<select name="calculator-car-type" class="calculator-car-type-select choose" required="required">
							<option></option>
							<?php
							usort($defaults['vehicle_type'][1]['items'], function($a, $b){
								return strcasecmp($a['title'], $b['title']);
							});
							?>
							@foreach($defaults['vehicle_type'][1]['items'] as $item)
								<option value="{{ $item['slug'] }}">{{ $item['title'] }}</option>
							@endforeach
						</select>
					</div>
					<div class="page-line-item">
						<div class="heading">
							<h4>Марка</h4>
						</div>
						<select name="calculator-car-brand" class="calculator-car-brend choose" required="required">
							<option></option>
						</select>
					</div>

					<div class="page-line-item">
						<div class="heading">
							<h4>Модель</h4>
						</div>
						<select name="calculator-car-model" class="calculator-car-model choose" required="required">
							<option></option>
						</select>
					</div>

					<div class="pages-line-item bottom-total">
						<div class="image-wrap"></div>
						<div class="score" style="display: none">
							<div class="heading">
								<h3>Итого</h3>
							</div>
							<div class="border-wrap">
								<div class="total">
									<p></p>
								</div>
								<div class="description">
									<p>Бесплатная подача или возврат в аэропорту Пулково или на Московском вокзале с 9 до 21</p>
								</div>
							</div>
						</div>
					</div>
					<div class="button-wrap">
						<button class="standartYellow">Взять в аренду</button>
					</div>
				</form>
			</div>

			<div class="pages-line" data-type="with-driver">
				<form class="none-driver-cont" action="/send_letter" method="POST" data-type="onlajn_zayavka_s_voditelem">
					<div class="line-wrap">
						<div class="page-line-item">
							<div class="heading">
								<h4>Город</h4>
							</div>
							<select name="calculator-city" class="calculator-city-select choose" required="required">
								<option></option>
								<!-- <option disabled>Выберите из списка</option> -->
								@foreach($defaults['cities'] as $city)
									<option value="{{ $city['slug'] }}">{{ $city['title'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="page-line-item">
							<div class="heading">
								<h4>Место подачи автомобиля</h4>
							</div>
							<input name="calculator-place" class="calculator-place-select choose" required="required">
						</div>
					</div>

					<div class="line-wrap">
						<div class="page-line-item">
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
										<div class="datepicker-init" id="driver-datepicker-start"></div>
										<input type="text" name="datepicker-start" id="for-driver-datepicker-start" required="required">
									</div>
									<div class="main-date-tab">
										<h4><label for="driver-hourpicker-start">Часы</label></h4>
										<input type="text" name="hourpicker-start" class="timepicker-init timepicker-hours" placeholder="Выберите" id="driver-hourpicker-start" required="required">

										<h4><label for="driver-minutepicker-start">Минуты</label></h4>
										<input type="text" name="minutespicker-start" class="timepicker-init timepicker-minutes" placeholder="Выберите" id="driver-minutepicker-start" required="required">
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

						<div class="page-line-item">
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
										<div class="datepicker-init" id="driver-datepicker-end"></div>
										<input type="text" name="datepicker-end" id="for-driver-datepicker-end" required>
									</div>
									<div class="main-date-tab">
										<h4><label for="driver-hourpicker-end">Часы</label></h4>
										<input type="text" name="hourpicker-end" class="timepicker-init timepicker-hours" placeholder="Выберите" id="driver-hourpicker-end" required="required">

										<h4><label for="driver-minutepicker-end">Минуты</label></h4>
										<input type="text" name="minutespicker-end" class="timepicker-init timepicker-minutes" placeholder="Выберите" id="driver-minutepicker-end" required="required">

										<!-- <div class="timepicker-init"></div> -->
									</div>
								</div>
								<button class="datepicker-button">Принять</button>
							</div>
						</div>
					</div>

					<div class="line-wrap">
						<div class="page-line-item">
							<div class="heading">
								<h4>Вид транспорта</h4>
							</div>
							<select name="calculator-car-type" class="calculator-car-type-select choose" required="required">
								<option></option>
								<!--  <option disabled>Выберите вид транспорта</option> -->
								<?php
								usort($defaults['vehicle_type'][0]['items'], function($a, $b){
									return strcasecmp($a['title'], $b['title']);
								});
								?>
								@foreach($defaults['vehicle_type'][0]['items'] as $item)
									<option value="{{ $item['slug'] }}">{{ $item['title'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="page-line-item">
							<div class="heading">
								<h4>Марка</h4>
							</div>
							<select name="calculator-car-brand" class="calculator-car-brend choose" required="required">
								<option></option>
							</select>
						</div>
						<div class="page-line-item">
							<div class="heading">
								<h4>Модель</h4>
							</div>
							<select name="calculator-car-model" class="calculator-car-model choose" required="required">
								<option></option>
							</select>
						</div>
					</div>

					<div class="pages-line-item bottom-total">
						<div class="image-wrap"></div>
						<div class="score" style="display: none">
							<div class="heading">
								<h3>Итого</h3>
							</div>
							<div class="border-wrap">
								<div class="total">
									<p></p>
								</div>
								<div class="description">
									<p>Бесплатная подача или возврат в аэропорту Пулково или на Московском вокзале с 9 до 21</p>
								</div>
							</div>
						</div>
					</div>
					<div class="button-wrap">
						<button class="standartYellow">Взять в аренду</button>
					</div>
				</form>
			</div>

		</div>
	</div>

	<nav class="our-transport-nav">
		<div class="our-transport-nav-tabs">
			<?php
			usort($defaults['vehicle_type'], function($a, $b){
				return $a['position'] > $b['position'];
			});
			?>
			@for($i=0; $i<count($defaults['vehicle_type']); $i++)
				<div class="our-transport-nav-tab @if($i==0) tabbed @endif">
					{{$defaults['vehicle_type'][$i]['title']}}
				</div>
			@endfor
		</div>
		<div class="our-transport-nav-pages">
			@foreach($defaults['vehicle_type'] as $category)
				<div class="our-transport-nav-page">
					<?php
					usort($category['items'], function($a, $b){
						return $a['position'] > $b['position'];
					});
					?>
					@foreach($category['items'] as $item)
						<a href="{{ URL::asset('/transport/'.$item['slug'].'/'.$category['slug']) }}" class="ignor">
							@if(!empty($item['img_url']))
							<img src="{{ URL::asset($item['img_url'][0]['img']) }}" alt="">
							@endif
							<p>{{$item['title']}}</p>
						</a>
					@endforeach
				</div>
			@endforeach
		</div>
	</nav>

	<div class="wrapper-our-servicesjs">
		<nav class="our-services-nav">
			@foreach($defaults['menu_services'] as $headers)
				<div class="menu-list-item">
					<div class="heading our-services-heading">
						<h3>{{$headers['title']}}</h3>
					</div>
					<ul>
						@foreach($headers['items'] as $item)
							<li><a href="{{ URL::asset($item['slug']) }}">{{$item['title']}}</a></li>
						@endforeach
					</ul>
				</div>
			@endforeach
		</nav>
	</div>

	<div class="wrapper-about-companyjs">
		<nav class="about-company-nav">
			<ul>
			@foreach($defaults['menu_about_company'] as $item)
				<li><a href="{{ URL::asset($item['slug']) }}">{{$item['title']}}</a></li>
			@endforeach
			</ul>
		</nav>
	</div>

	<div class="wrapper-partners_investorsjs">
		<nav class="partners_investors-nav">
			<ul>
			@foreach($defaults['menu_partners'] as $item)
				<li><a href="{{ URL::asset($item['slug']) }}">{{$item['title']}}</a></li>
			@endforeach
			</ul>
		</nav>
	</div>

	<div id="login-menu">
		<form action="{{ route('login') }}" name="loginMenu" class="login" method="post">
			<input name="_token" type="hidden" value="{{ csrf_token() }}">
			<input name="_method" type="hidden" value="PUT">
			<input type="email" name="email" placeholder="example@mail.com" required="required">
			<input type="password" name="password" placeholder="пароль" required="required">
			<input type="text" name="name" placeholder="Имя">
			<input type="text" name="tel" placeholder="телефон" class="tel-mask">
			<button type="submit">ВОЙТИ</button>
		</form>
	</div>

	<div id="back-call">
		<div class="main-heading">
			<h3>Обратный звонок</h3>
		</div>
		<form action="{{ route('send-mail') }}" name="backCall" class="back-call" method="POST">
			<input name="_token" type="hidden" value="{{ csrf_token() }}">
			<input name="type" type="hidden" value="obratnyj_zvonok">
			<input type="text" name="name" placeholder="Ваше имя" required="required" @if($user) value="{{ $user['name'] }}" @endif>
			<input type="tel" class="tel-mask" name="tel" placeholder="Ваш телефон" required="required" @if($user) value="{{ $user['phone'] }}" @endif>
			<textarea name="comment" placeholder="Коментарий"></textarea>
			<div class="button-wrapper">
				<button class="standartYellow">Позвоните мне</button>
			</div>
		</form>
	</div>

	<div id="back-call1">
		<div class="main-heading">
			<h3>В ближайшее время мы свяжемся с вами</h3>
		</div>
		<form action="ajax.php" name="backCall1" class="back-call1">
			<input type="text" name="name" placeholder="Ваше имя" required="required" @if($user) value="{{ $user['name'] }}" @endif>
			<input type="tel" class="tel-mask" name="tel" placeholder="Ваш телефон" required="required" @if($user) value="{{ $user['phone'] }}" @endif>
			<div class="button-wrapper">
				<button class="standartYellow">Позвоните мне</button>
			</div>
		</form>
	</div>

	<div id="video-popup">
		@if( (isset($content)) && (isset($content['data']['textarea_0'])) )
			@if(!empty($content['data']['textarea_0']['value']))
				{!! $content['data']['textarea_0']['value'] !!}
			@endif
		@endif
	</div>

	<div id="photo-slider">
		<div class="popup-slider">
			<div class="big-popup"></div>
			<div class="small-popup"></div>
			<div class="prev"><img src="{{ URL::asset('img/prev.png') }}" alt=""></div>
			<div class="next"><img src="{{ URL::asset('img/next.png') }}" alt=""></div>
		</div>
	</div>
</div>

<!-- FOOTER -->
<div class="footer_placeholder">

</div>
<footer class="footer">
	<div class="mbox">
		@if(\Request::route()->getName() != 'home')
			<ul class="el-breadcrumbs foot-breadcrumbs">
				<li><a href="/">Главная</a></li>
				@if(isset($_COOKIE['prev_page']))
				<?php
				$prev_page = unserialize($_COOKIE['prev_page']);
				?>
				<li><a href="{{ URL::asset($prev_page[0]) }}">{{ $prev_page[1] }}</a></li>
				@endif
				@if(isset($meta['last_page']))
					<li><a href="{{ URL::asset($meta['last_page']['slug']) }}">{{ $meta['last_page']['title'] }}</a></li>
				@endif
				<li class="active"><a href="#">{{$meta['title']}}</a></li>
			</ul>
		@endif
		<div class="footer-list">
			@for($i=0; $i<count($defaults['footer_menu']); $i++)
				@if($i == 0)
					<div class="footer-list-col">
				@endif

				@if($i % 2 == 1)
					</div>
					<div class="footer-list-col">
				@endif
						<div class="footer-list-item">
							<h3>{{ $defaults['footer_menu'][$i]['title'] }}</h3>
							<ul>
								@foreach($defaults['footer_menu'][$i]['items'] as $item)
									<li>
										@if($i == 3)
											<a href="{{ URL::asset('/transport/'.$item['slug'])}}">{{ $item['title'] }}</a>
										@elseif($i == 4)
											@if($item['slug'] == '#headercalculator')
												<a href="#headerCalculator" class="fancyboxHeading">{{ $item['title'] }}</a>
											@elseif($item['slug'] == 'user_panel')
												<a href="" class="fancyboxHeading1">{{ $item['title'] }}</a>
											@else
												<a href="{{ URL::asset($item['slug'])}}">{{ $item['title'] }}</a>
											@endif
										@else
											<a href="{{ URL::asset($item['slug'])}}">{{ $item['title'] }}</a>
										@endif
									</li>
								@endforeach
							</ul>
						</div>
				@if($i == count($defaults['footer_menu'])-1)
						<a class="footer-logo" href="/">
							<div class="footer-logo-wrap">
								<img src="{{ URL::asset('img/footer-logo.png') }}" alt="Logo">
							</div>
							<p>ROYAL RENT</p>
						</a>
						<div class="footer-contacts">
							<span>Car rental. Around-the-clock.</span>
							<span>Автопрокат круглосуточно.</span>
							<a href="#tel:{{ str_replace(' ', '-',$defaults['current_city']['data']['string_2']['value']) }}">{{ str_replace(' ', '-',$defaults['current_city']['data']['string_2']['value']) }}</a>
							<a href="mailto:"></a>
						</div>
					</div>
				@endif
			@endfor
		</div>
	</div>
	<div class="footer-bottom">
		<div class="mbox">
			<div class="footer-bottom-wrap">
				<div class="footer-bottom-left">
					<div class="copyright">
						<span>Copyright © 2016 RoyalRent. Все права защищены.</span>
					</div>
					<div class="social">
						@foreach($defaults['site_settings']['social'] as $social)
							<a href="{{ $social->link }}">
								<img src="{{ URL::asset('img/'.$social->type.'.png') }}" alt="{{$social->link}}">
							</a>
						@endforeach
					</div>
				</div>
				<!-- <div id="google_translate_element"></div> -->
				<div class="design">
					<span>Макеты сделаны <a href="http://theparallel.ru">Parallel</a></span>
				</div>
			</div>
		</div>
	</div>
</footer>

<!-- FOOTER -->

<!-- SCRIPTS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('js/validate_script.js') }}" ></script>

<!-- build:js -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAD4EvtZ1QPqDgkPPt4o0hVDnkg45IvyUM"></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/device.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery.fancybox.pack.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery.formstyler.min.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery.validate.min.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/maskInput.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/slick.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery.timepicker.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery-ui.js') }}" ></script>

<script type="text/javascript" src="{{ URL::asset('js/plugins/datepicker-ru.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery.secretnav.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/modernizr.custom.25376.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/SmoothScroll.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/plugins/jquery.ui.touch-punch.min.js') }}" ></script>

<script type="text/javascript" src="{{ URL::asset('js/basic_scripts.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/develop/develop_1.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/develop/develop_2.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/develop/develop_4.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/develop/develop_5.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/develop/develop_6.js') }}" ></script>
<script type="text/javascript" src="{{ URL::asset('js/develop/develop_7.js') }}" ></script>
<script src='https://www.google.com/recaptcha/api.js'></script>

<!-- endbuild -->
<script type="text/javascript">
	function googleTranslateElementInit() {
		new google.translate.TranslateElement({pageLanguage: 'ru', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, multilanguagePage: true}, 'google_translate_element');
	}
</script>
<script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=Intl.~locale.en"></script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<!-- /SCRIPTS -->
</div>
</body>
</html>
