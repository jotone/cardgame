@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/spectrum.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/admin_products.js') }}"></script>
	<link rel="stylesheet" href="{{ URL::asset('css/spectrum.css') }}" />
@stop
@section('content')
	<div class="main-block" data-module="{{ $fields['id'] }}">
		<div class="aside-wrap">
			<div class="fixed-navigation-menu">
				<ul></ul>
			</div>
		</div>

		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $fields['title'] }}</div>
			<div class="work-place-wrap">
				<input name="id" type="hidden" @if(isset($content['id'])) value="{{ $content['id'] }}" @endif>

				<div>
					<fieldset>
						<legend>Основные данные</legend>
						<div class="row-wrap">
							<label class="fieldset-label-wrap">
								<input name="title" type="text" class="text-input col_1_2" placeholder="Название&hellip;" @if(isset($content['title'])) value="{{ $content['title'] }}" @endif>
								<span>Название</span>
							</label>
						</div>
						<div class="row-wrap">
							<label class="fieldset-label-wrap">
								<input name="slug" type="text" class="text-input col_1_2" placeholder="Ссылка меню&hellip;" @if(isset($content['slug'])) value="{{ $content['slug'] }}" @endif>
								<span>Ссылка меню</span>
							</label>
						</div>
						<div class="row-wrap">
							<label class="fieldset-label-wrap">
								<input name="enabled" type="checkbox" class="chbox-input" @if(isset($content['enabled'])) {{ $content['enabled'] }} @endif>
								<span>Опубликовать немедленно</span>
							</label>
						</div>
					</fieldset>
				</div>

				<div>
					<fieldset>
						<legend>Цвет</legend>
						<div class="row-wrap">
							<div class="chbox-selector-wrap" id="colors">
							@if(isset($content['color']))
								@foreach($content['color'] as $tem)
									<div class="color-item-wrap">
										<span class="drop-add-field">&times;</span>
										<div class="color-box" style="background-color: {{$tem->color}}"></div>
										<input name="colorTitle" type="text" class="text-input" placeholder="Название цвета&hellip;" value="{{$tem->title}}">
									</div>
								@endforeach
							@endif
							</div>
							<div style="margin: 16px 0 0 6px">
								<input name="colorPicker" type="text">
								<button name="chooseColor" class="control-button">Добавить цвет</button>
							</div>
						</div>
					</fieldset>
				</div>

				<div>
				@foreach($fields['disabled_fields'] as $disabled_field)
					@if($disabled_field->enabled)
						{!! \App\Http\Controllers\Supply\Functions::buildDefaultFields($disabled_field->type, $content) !!}
					@endif
				@endforeach
				</div>

				<div>
					<fieldset>
						<legend>Мета-данные</legend>
						<div class="row-wrap">
							<label class="fieldset-label-wrap">
								<input name="metaTitle" class="text-input col_1_2" type="text" placeholder="Meta Title&hellip;" @if(isset($content['meta_title'])) value="{{ $content['meta_title'] }}" @endif>
								<span>Meta Title</span>
							</label>
						</div>
						<div class="row-wrap">
							<label class="fieldset-label-wrap">
								<span>Meta Description</span>
								<textarea name="metaDescription" class="simple-text" placeholder="Meta Description&hellip;">@if(isset($content['meta_description'])){{$content['meta_description']}}@endif</textarea>
							</label>
						</div>
						<div class="row-wrap">
							<label class="fieldset-label-wrap">
								<span>Meta Keywords</span>
								<textarea name="metaKeywords" class="simple-text" placeholder="Meta Keywords&hellip;">@if(isset($content['meta_keywords'])){{$content['meta_keywords']}}@endif</textarea>
							</label>
						</div>
					</fieldset>
				</div>

				<div id="customFieldsWrap">
					@if(isset($content['custom_fields']))
						{!! \App\Http\Controllers\Supply\Functions::buildCustomFields($fields['custom_fields'], $content['custom_fields']) !!}
					@else
						{!! \App\Http\Controllers\Supply\Functions::buildCustomFields($fields['custom_fields']) !!}
					@endif
				</div>
			</div>
			<div class="button-wrap tac">
				<button name="saveProduct" class="control-button" type="button">Применить</button>
			</div>
		</div>
	</div>
@stop