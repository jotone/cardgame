@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
?>
<div class="not-main registration-main-page one-screen-login">
	<div class="main form-block">

		<div class="form-wrap">
			<div class="form-wrap-main">
				<div class="form-title">Тех. Поддержка</div>
				<div class="form-wrap-item placeholder-form">
					{{ Form::open(['route' => 'user-sends-letter', 'class' => 'register-form', 'autocomplete' => 'off', 'method' => 'POST']) }}

					<div class="form-wrap-for-rows">
						<div class="form-wrap-row form_row">
							<div class="form-wrap-value">
								<div class="form-wrap-input form_input">
									<select name="rubric_select" required="required" class="rubric-select yellow-font">
										<option></option>
										@foreach($rubrics as $rubric)
											<option value="{{ $rubric->slug }}">{{ $rubric->title }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>

						<div class="form-wrap-row form_row">
							<div class="form-wrap-value">
								<div class="form-wrap-input form_input">
									<input type="email" name="email" placeholder="Email" required @if($user) value="{{$user['email']}}" @endif>
								</div>
							</div>
						</div>

						<div class="form-wrap-row form_row">
							<div class="form-wrap-value">
								<div class="form-wrap-input form_input">
									<textarea name="qestionText" style="min-height: 200px;" required placeholder="Сообщение"></textarea>
								</div>
							</div>
						</div>

						<div class="reCaptcha-wrap">
							<div class="g-recaptcha" data-sitekey="6LfWZyQTAAAAAP3EiGHuaUaTb1t3si4fOBv8E4YK"></div>
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
										<span class="form-button-text">Отправить</span>
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
			</div>
		</div>
	</div>
</div>
@stop
