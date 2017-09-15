@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_settings.js') }}"></script>
@stop

@section('content')
	<div class="main-block">
		<div class="aside-wrap">
			<div class="fixed-navigation-menu">
				<ul></ul>
			</div>
		</div>

		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="work-place-wrap">

				<div>
					<fieldset>
						<legend>Список e-mail'ов</legend>
						<div id="mailList">
							@if((isset($content['email'])) && (!empty($content['email'])))
								@foreach($content['email'] as $mail)
									<div class="row-wrap col_1_2" style="display: flex; align-items: center">
										<input name="mail" type="email" class="text-input col_4_5" placeholder="Введите e-mail&hellip;" value="{{$mail}}">
										<span class="drop-add-field">×</span>
									</div>
								@endforeach
							@endif
							<div class="row-wrap col_1_2" style="display: flex; align-items: center">
								<input name="mail" type="email" class="text-input col_4_5" placeholder="Введите e-mail&hellip;">
								<span class="drop-add-field">×</span>
							</div>
						</div>
						<div class="small-button-wrap">
							<button name="moreEmails" class="control-button">Еще&hellip;</button>
						</div>
					</fieldset>
				</div>

				<div style="display: none">
					<fieldset>
						<legend>Список телефонов</legend>
						<div id="phoneList">
							@if((isset($content['phone'])) && (!empty($content['phone'])))
								@foreach($content['phone'] as $phone)
									<div class="row-wrap col_1_2" style="display: flex; align-items: center">
										<input name="phone" type="text" class="text-input col_4_5 needPhoneMask" placeholder="Введите телефонный номер&hellip;" value="{{$phone}}">
										<span class="drop-add-field">×</span>
									</div>
								@endforeach
							@endif
							<div class="row-wrap col_1_2" style="display: flex; align-items: center">
								<input name="phone" type="text" class="text-input col_4_5 needPhoneMask" placeholder="Введите телефонный номер&hellip;">
								<span class="drop-add-field">×</span>
							</div>
						</div>
						<div class="small-button-wrap">
							<button name="morePhones" class="control-button">Еще&hellip;</button>
						</div>
					</fieldset>
				</div>

				<div>
					<fieldset>
						<legend>Социальные сети</legend>
						<div id="socList">
							@if((isset($content['social'])) && (!empty($content['social'])))
								@foreach($content['social'] as $social)
									<?php
									switch($social->type){
										case 'facebook':	$title = 'FaceBook'; break;
										case 'google_plus':	$title = 'Google+'; break;
										case 'instagram':	$title = 'Instagram'; break;
										case 'linkedin':	$title = 'LinkedIn'; break;
										case 'livejournal':	$title = 'LiveJournal'; break;
										case 'mailru':		$title = 'MailRu'; break;
										case 'pinterest':	$title = 'Pinterest'; break;
										case 'twitter':		$title = 'Twitter'; break;
										case 'viber':		$title = 'Viber'; break;
										case 'whatsapp':	$title = 'WhatsApp'; break;
										case 'vkontakte':	$title = 'Вконтакте'; break;
										case 'odnoklassniki':$title= 'Одноклассники'; break;
										default: $title = '';
									}
									?>
									<div class="row-wrap col_1_2" style="display: flex; align-items: center">
										<span style="width: 110px; padding-right: 10px;" class="tar">{{ $title }}:</span>
										<input name="socLink" type="text" class="text-input col_4_5" placeholder="Ссылка&hellip;" data-soc="{{$social->type}}" value="{{$social->link}}">
										<span class="drop-add-field">×</span>
									</div>
								@endforeach
							@endif
						</div>
						<div class="row-wrap">
							<ul class="pseudo-selector col_1_4">
								<li class="active" data-soc="facebook"><img src="{{ URL::asset('/img/social/fb-icon.png') }}"><span>FaceBook</span></li>
								<li data-soc="google_plus"><img src="{{ URL::asset('/img/social/gp-icon.png') }}"><span>Google+</span></li>
								<li data-soc="instagram"><img src="{{ URL::asset('/img/social/insta-icon.png') }}"><span>Instagram</span></li>
								<li data-soc="linkedin"><img src="{{ URL::asset('/img/social/li-icon.png') }}"><span>LinkedIn</span></li>
								<li data-soc="livejournal"><img src="{{ URL::asset('/img/social/lj-icon.png') }}"><span>LiveJournal</span></li>
								<li data-soc="mailru"><img src="{{ URL::asset('/img/social/mailru-icon.png') }}"><span>MailRu</span></li>
								<li data-soc="pinterest"><img src="{{ URL::asset('/img/social/pinterest-icon.png') }}"><span>Pinterest</span></li>
								<li data-soc="twitter"><img src="{{ URL::asset('/img/social/tw-icon.png') }}"><span>Twitter</span></li>
								<li data-soc="viber"><img src="{{ URL::asset('/img/social/viber-icon.png') }}"><span>Viber</span></li>
								<li data-soc="whatsapp"><img src="{{ URL::asset('/img/social/wa-icon.png') }}"><span>WhatsApp</span></li>
								<li data-soc="vkontakte"><img src="{{ URL::asset('/img/social/vk-icon.png') }}"><span>Вконтакте</span></li>
								<li data-soc="odnoklassniki"><img src="{{ URL::asset('/img/social/ok-icon.png') }}"><span>Одноклассники</span></li>
							</ul>
						</div>
						<div class="row-wrap">
							<button name="moreSocial" class="control-button">Добавить&hellip;</button>
						</div>
					</fieldset>
				</div>

				<div style="display: none">
					<fieldset>
						<legend>Адреса</legend>
						<div id="addresslist">
							@if((isset($content['address'])) && (!empty($content['address'])))
								@foreach($content['address'] as $address)
									<div class="row-wrap col_1_2" style="display: flex; align-items: center">
										<textarea name="address" class="simple-text" placeholder="Введите адресс&hellip;">{{$address}}</textarea>
										<span class="drop-add-field">×</span>
									</div>
								@endforeach
							@endif
							<div class="row-wrap col_1_2" style="display: flex; align-items: center">
								<textarea name="address" class="simple-text" placeholder="Введите адресс&hellip;"></textarea>
								<span class="drop-add-field">×</span>
							</div>
						</div>
						<div class="row-wrap">
							<button name="moreAddresses" class="control-button">Еще&hellip;</button>
						</div>
					</fieldset>
				</div>

				<div>
					<fieldset>
						<legend>Реквизиты</legend>
						<div class="row-wrap">
							<textarea class="needCKE" name="requisites">@if(isset($content['requisites'])){{$content['requisites']}}@endif</textarea>
						</div>
					</fieldset>
				</div>
			</div>
			<div class="button-wrap tac">
				<button name="saveSettings" class="control-button">Применить</button>
			</div>
		</div>
	</div>
@stop