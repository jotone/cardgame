@extends('layouts.default')
@section('content')
<?php
$errors = $errors->all();
?>
<div class="not-main registration-main-page one-screen-login">
	<div class="main form-block">

		<div class="form-wrap">
			<div class="form-wrap-main">
			@if(isset($user) && ($user))
				<div class="form-title">Вы уже зарегистрированы</div>
			@else

				<div class="form-title">РЕГИСТРАЦИЯ</div>

				<div class="form-wrap-item placeholder-form">
					{{ Form::open(['route' => 'user-register-me', 'class' => 'register-form', 'autocomplete' => 'off', 'method' => 'POST']) }}

						<input type="hidden" name="action" value= "registration" class="typesubmit" />

						<div class="form-wrap-for-rows">
							<div class="form-wrap-row form_row">
								<div class="form-wrap-value">
									<div class="form-wrap-input form_input">
										<input type="text" name="login" placeholder="Логин" required="required" />
									</div>
								</div>
							</div>
							<div class="form-wrap-row form_row">
								<div class="form-wrap-value">
									<div class="form-wrap-input form_input">
										<input type="password" name="password" placeholder="Пароль" required="required" />
									</div>
								</div>
							</div>
							<div class="form-wrap-row form_row">
								<div class="form-wrap-value">
									<div class="form-wrap-input form_input">
										<input type="password"  name="confirm_password" placeholder="Повторите пароль" required="required" />
									</div>
								</div>
							</div>
							<div class="form-wrap-row form_row">
								<div class="form-wrap-value">
									<div class="form-wrap-input form_input">
										<input type="email" name="email" placeholder="Email" required="required" />
									</div>
								</div>
							</div>
							<div class="form-wrap-row form_row">
								<div class="form-wrap-value">
									<div class="form-wrap-input form_input">
										<select name="fraction_select" required="required" class="male-select yellow-font">
											<option></option>
											@foreach($fractions as $key => $fraction)
												<?php
												$selected = ( (isset($_GET['fraction'])) && ($_GET['fraction'] == $fraction->slug) )? 'selected': '';
												?>
												<option value="{{ $fraction->slug }}" {!! $selected !!}>{{ $fraction->title }}</option>
											@endforeach
										</select>
									</div>
								</div>
							</div>
							<!-- <div class="reCaptcha-wrap">
								<div class="g-recaptcha" data-sitekey="6LfWZyQTAAAAAP3EiGHuaUaTb1t3si4fOBv8E4YK"></div>
							</div> -->

							<div class="agree-field">
								<input type="checkbox" name="check-agree" id="linka-check" value="false">
								<label for="linka-check" class="swicher-maker">
									<span class="kvadratic kv-true "></span>
									<span>я принимаю условия <a href="#">лицензионного соглашения</a> </span>
								</label>
							</div>

							<div class="form-wrap-row error-text" @if(!empty($errors)) style="display: block;" @endif>
								@if(!empty($errors))
									@foreach($errors as $error)
										<p>{!! $error !!}</p>
									@endforeach
								@endif
							</div>
							<div class="form-wrap-row submit">
								<div class="form-wrap-value">
									<div class="form-wrap-input">
										<button class="form-button" type="submit">
											<span class="form-button-hover"></span>
											<span class="form-button-text">ЗАРЕГИСТРИРОВАТЬСЯ</span>
										</button>
										<a href="{{ route('user-home') }}" class="form-button back-button">
											<span class="form-button-hover"></span>
											<span class="form-button-text">На главную</span>
										</a>
									</div>
								</div>
							</div>
						</div>
					{{ Form::close() }}
				</div>

			@endif

			</div>
		</div>

		<div id="popup-agree" class="license-agreement">
			<div class="close-this"></div>
			<div class="conteiner-pop">
				<div class="title">{!! $page_content->title !!}</div>
				<div class="texter">
					{!! $page_content->text !!}
				</div>
			</div>
			<div class="button-troll disable-select">
				<b>ЗАКРЫТЬ</b>
			</div>
		</div>

	</div>
</div>
@stop
