@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<section class="mbox event-order">
			<div class="book-title">Оформление заказа</div>
			<div class="tariffs">
				<div class="tarif-title">Выберите тариф:</div>
				<div class="el-tariffs">
					@foreach($content['data']['category_1'] as $tarif)
					<div class="tarif-item js-tariff" data-type="{{ $tarif['id'] }}">
						<p>{{ $tarif['title'] }}</p>
						<div class="item">
							<div class="price">{{ number_format($tarif['data']['string_0']['value'],0, '', ' ') }} руб.</div>
							<div class="item-desc">{!! $tarif['data']['fulltext_0']['value'] !!}</div>
						</div>
					</div>
					@endforeach
				</div>
				<div class="tariffs-description">
					<div class="include">
						{!! $content['text'] !!}
					</div>
					<div class="prompts">
						{!! $content['data']['fulltext_0']['value'] !!}
					</div>
				</div>
			</div>
			<div class="order-info">
				<div class="info-title">Информация о заказе</div>
				<div class="order">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
					<div class="order-item">
						<p>Как вас зовут</p>
						<input type="text" name="name" class="name" placeholder="Ваше имя">
					</div>
					<div class="order-item">
						<p>Контактный телефон</p>
						<input type="text" name="tel" id="phone" class="name" placeholder="Ваш телефон">
					</div>
					<div class="order-item">
						<p>Желаемые дата и время</p>
						<input type="text" name="date" class="name" id="exurtion-datepicker">
					</div>
				</div>
				<input name="order_excursion" type="submit" class="el-button submit" value="Подтвердить">
			</div>
		</section>
	</div>
	<!-- /MAIN -->
@stop