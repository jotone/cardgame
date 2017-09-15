@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_mailing.js') }}"></script>
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
				<input name="id" type="hidden" @if(isset($content['id'])) value="{{ $content['id'] }}" @endif>

				<div>
					<fieldset style="display: none">
						<legend>Динамические данные</legend>
						<div class="row-wrap" id="dynamicAssets">
							<table class="item-list">
								<thead>
									<tr>
										<th></th>
										<th>Название</th>
										<th>Шаблон для вставки</th>
										<th>Паттерн</th>
									</tr>
								</thead>
								<tbody>
								@foreach($mail_templates as $template)
									<tr>
										<td>
											<a class="block-button drop"href="#" data-id="{{$template->id}}" data-title="{{$template->title}}" title="Удалить">
												<img src="/img/drop.png" alt="Удалить">
											</a>
										</td>
										<td>{{$template->title}}</td>
										<td><span class="force-select-all">{{$template->caption}}</span></td>
										<td style="color: #73799c">{{$template->content}}</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						</div>
						<div class="row-wrap">
							<select name="dataType" class="select-input">
								<option value="-1">Выберите тип даных</option>
								@foreach($dynamic_data_list as $key => $value)
									<optgroup label="{{ $value['title'] }}">
										@foreach($value['data'] as $data)
											<option data-source="{{$key}}" data-module="{{ $data['module_slug'] }}" value="{{ $data['id'] }}">{{ $data['caption'] }}</option>
										@endforeach
									</optgroup>
								@endforeach
							</select>
							<select name="dataField" class="select-input" style="display: none"></select>
							<button name="addDynamicField" class="control-button" style="display: none">Добавить</button>
						</div>
					</fieldset>
				</div>

				<div>
					<fieldset>
						<legend>Название</legend>
						<div class="row-wrap">
							<input name="caption" type="text" class="text-input col_1_2" placeholder="Название&hellip;" @if(isset($content['title'])) value="{{$content['title']}}" @endif>
						</div>
					</fieldset>
				</div>

				<div>
					<fieldset>
						<legend>Основные данные</legend>
						<div class="row-wrap">
							<select name="mailSenderFake" class="select-input faker-changeable">
								<option>Выберите e-mail</option>
								@foreach($mail_list as $mail)
									<option>{{ $mail }}</option>
								@endforeach
							</select>
							<label class="fieldset-label-wrap">
								<input name="mailSender" type="email" class="text-input faker-input" @if(isset($content['sender'])) value="{{$content['sender']}}" @endif>
								<span>Отправитель</span>
							</label>
						</div>
						<div class="row-wrap">
							<select name="mailReceiverFake" class="select-input faker-changeable">
								<option>Выберите e-mail</option>
								@foreach($mail_list as $mail)
									<option>{{ $mail }}</option>
								@endforeach
							</select>
							<label class="fieldset-label-wrap">
								<input name="mailReceiver" type="email" class="text-input faker-input" @if(isset($content['receiver'])) value="{{$content['receiver']}}" @endif>
								<span>Получатель</span>
							</label>
						</div>
						<div class="row-wrap">
							<select name="mailReplyerFake" class="select-input faker-changeable">
								<option>Выберите e-mail</option>
								<option>Не отвечать на данное письмо</option>
								@foreach($mail_list as $mail)
									<option>{{ $mail }}</option>
								@endforeach
							</select>
							<label class="fieldset-label-wrap">
								<input name="mailReplyer" type="text" class="text-input faker-input" @if(isset($content['replyer'])) value="{{$content['replyer']}}" @endif>
								<span>Ответчик</span>
							</label>
						</div>
					</fieldset>

					<fieldset>
						<legend>Текст сообщения</legend>
						<div class="row-wrap">
							<strong>*</strong> - для использования внешних входящих параметров (GET, POST) используйте шаблон вставки [%параметр%]
						</div>
						<div class="row-wrap">
							<textarea name="mailText" class="needCKE">@if(isset($content['text'])){{ $content['text'] }}@endif</textarea>
						</div>
					</fieldset>
				</div>
				<div class="button-wrap tac">
					<button name="saveLetter" class="control-button">Применить</button>
				</div>
			</div>
		</div>
	</div>
@stop