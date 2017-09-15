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
		<section class="contacts">
			<div class="mbox">
				<div class="contacts-wrap">
					<div class="contacts-left">
						<div class="contacts-title">
							<h2>Мы всегда рядом</h2>
							<span>Есть много способов, чтобы связаться с нами</span>
						</div>
						@foreach($content as $city_data)
						<div class="contacts-address">
							<p class="circle">{{ $city_data['title'] }}</p>
							<a href="tel:{{ $city_data['data']['string_2']['value'] }}">т: {{ $city_data['data']['string_2']['value'] }}</a>
							<h3>Пункты выдачи:</h3>
							{!! $city_data['data']['fulltext_0']['value'] !!}
						</div>
						@endforeach
						@foreach($contact_data['email'] as $email)
							<div class="contacts-address">
								<a href="mailto:{{ $email }}">e: {{ $email }}</a>
							</div>
						@endforeach
						<div class="requisites">
							<h4>Реквизиты организации:</h4>
							{!! $contact_data['requisites'] !!}
						</div>
					</div>
					<div class="contacts-right">
						<h3>Пишите нам!</h3>
						<form class="contacts-form" action="{{ route('send-mail') }}" method="post">
							<input name="_token" type="hidden" value="{{ csrf_token() }}">
							<input name="type" type="hidden" value="pismo">
							<div class="form-field">
								<input type="text" name="name" placeholder="Ваше имя" required />
							</div>
							<div class="form-field">
								<input type="email" name="email" placeholder="Ваш e-mail" required />
							</div>
							<div class="form-field">
								<textarea name="text" placeholder="Сообщение..." required></textarea>
							</div>
							<button class="el-button" type="submit">Отправить</button>
						</form>
					</div>
				</div>
			</div>
			<div id="map"></div>
		</section>
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop