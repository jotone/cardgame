@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_mailing.js') }}"></script>
@stop
@section('content')
<div class="main-block">
	<div class="aside-wrap">
		<div class="template-select-wrap">
			<label class="fieldset-label-wrap">
				<strong>Выберите тип шаблона:</strong>
				<select name="dispatchTemplate" class="select-input">
					<option value="0">По умолчанию</option>
					@foreach($templates as $template)
						<option value="{{ $template['caption'] }}">{{ $template['title'] }}</option>
					@endforeach
				</select>
			</label>
		</div>
		<div class="template-select-wrap">
			<ul>
			@foreach($content as $email)
				<li style="margin: 10px 0;"><a style="color: #000;" href="mailto:{{ $email->email }}">{{ $email->email }}</a></li>
			@endforeach
			</ul>
		</div>
	</div>
	<div class="center-wrap col_1">
		<div class="page-caption row-wrap">{{ $page_title }}</div>
		<fieldset>
			<legend>Текст рассылки</legend>
			<div class="row-wrap">
				<input name="title" type="text" class="text-input col_1" placeholder="Название&hellip;">
			</div>
			<div class="row-wrap">
				<textarea class="needCKE col_1" name="dispatchText"></textarea>
			</div>
		</fieldset>
		<div class="button-wrap tac">
			<button name="saveTemplate" class="control-button" type="button">Сохранить как шаблон</button>
		</div>
		<div class="button-wrap tac">
			<button name="dispatch" class="control-button" type="button">Разослать</button>
		</div>
	</div>
</div>
@stop