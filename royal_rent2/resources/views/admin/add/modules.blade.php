@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_modules.js') }}"></script>
@stop
@section('content')
	<div class="main-block">
		<div class="aside-wrap">
			<div class="module-list-wrap">
				@foreach($modules as $module_item)
					<div class="module-wrap" title="{{ $module_item['description'] }}" data-id="{{ $module_item['id'] }}">
						<span>{{ $module_item['title'] }}</span>
						<div class="add-button"></div>
					</div>
				@endforeach
			</div>
		</div>
		<div class="center-wrap col_4_5">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="work-place-wrap">
				<input name="id" type="hidden" @if(isset($data)) value="{{ $data['id'] }}" @endif>
				<fieldset>
					<legend>Тип модуля</legend>
					<div class="row-wrap">
						<select name="moduleType" class="select-input">
						@foreach($modules as $module_item)
							<option value="{{$module_item['id']}}" @if((!empty($id)) && ($id == $module_item['id'])) selected="selected" @endif>{{$module_item['title']}}</option>
						@endforeach
						</select>
					</div>
				</fieldset>

				<fieldset>
					<legend>Короткое описание модуля</legend>
					<div class="row-wrap">
						<textarea class="simple-text" name="description">@if(isset($module['description'])){{ $module['description'] }}@endif</textarea>
					</div>
				</fieldset>

				<fieldset>
					<legend>Основные данные</legend>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="title" type="text" class="text-input col_1_2" placeholder="Название модуля&hellip;" @if(isset($data['title'])) value="{{ $data['title'] }}" @endif>
							<span>Название модуля</span>
						</label>
					</div>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="slug" type="text" class="text-input col_1_2" placeholder="Ссылка меню&hellip;" @if(isset($data['slug'])) value="{{ $data['slug'] }}" @endif>
							<span>Ссылка меню</span>
						</label>
					</div>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="unique_slug" type="checkbox" class="chbox-input" @if(isset($data['unique_slug'])) {{ $data['unique_slug'] }} @endif>
							<span>Уникальная подпись к ссылке</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Контент по умолчанию</legend>
					<div id="moduleNormalSettings" @if(isset($data['disabled_fields'])) data-deny="deny" @endif>
					@if(isset($data['disabled_fields']))
						@foreach($data['disabled_fields'] as $disabled_field)
							<?php
							switch($disabled_field->type){
								case 'date_begin': $name = 'Дата начала'; $type = 'Дата'; break;
								case 'date_finish': $name = 'Дата окончания'; $type = 'Дата'; break;
								case 'description': $name = 'Описание'; $type = 'Полный текст'; break;
								case 'text': $name = 'Текст'; $type = 'Полный текст'; break;
								case 'img_url': $name = 'Изображение'; $type = 'Слайдер изображений'; break;
								case 'text_caption': $name = 'Строка'; $type = 'Строка'; break;
								case 'price': $name = 'Цена'; $type = 'Строка'; break;
							}
							?>
							<div class="module-elements-wrap ui-sortable-handle" data-type="{{ $disabled_field->type }}">
								<div class="col_1_4">Название: {{ $name }}</div>
								<div class="col_1_4">Тип: {{ $type }}</div>
								<div class="col_1_4">Псевдоним: {{ $disabled_field->type }}</div>
								<div class="col_1_4">
									<label class="">
										<input class="chbox-input" type="checkbox" @if($disabled_field->enabled) checked="checked" @endif>
										<span>Включен</span>
									</label>
								</div>
							</div>
						@endforeach
					@endif
					</div>
				</fieldset>

				<fieldset>
					<legend>Добавить дополнительный контент в модуль</legend>
					<div id="moduleAdditionalSettings">
					@if(isset($data['custom_fields']))
						@foreach($data['custom_fields'] as $custom_field)
							{!! \App\Http\Controllers\Supply\Functions::moduleContent($custom_field) !!}
						@endforeach
					@endif
					</div>
					<div class="row-wrap">
						<select name="contentType" class="select-input">
							<option value="group">Контейнер группы</option>
							<optgroup label="Контейнеры формы">
								<option value="string">Строка</option>
								<option value="email">E-mail</option>
								<option value="number">Ввод чисел</option>
								<option value="range">Ползунок</option>
								<option value="checkbox">Флажок</option>
								<option value="radio">Переключатель</option>
								<option value="textarea">Текстовое поле</option>
								<option value="fulltext">Полный текст</option>
							</optgroup>
							<optgroup label="Контент других модулей">
								<option value="promo">Акции</option>
								<option value="category">Категории</option>
								<option value="articles">Статьи</option>
								<option value="products">Товары</option>
							</optgroup>
							<optgroup label="Другое">
								<option value="table">Таблица</option>
								<option value="img_slider">Слайдер изображений</option>
								<option value="custom_slider">Настраиваемый слайдер</option>
								<option value="file_upload">Файл</option>
							</optgroup>
						</select>
					</div>
					<div class="row-wrap">
						<fieldset>
							<legend>Заполните данные дополнительного контента</legend>
							<div id="contentTune"></div>
						</fieldset>
					</div>
					<div class="row-wrap">
						<button name="addContent" type="button" class="control-button">Добавить</button>
					</div>
				</fieldset>
			</div>
			<div class="button-wrap tac">
				<button name="saveModule" class="control-button" type="button">Применить</button>
			</div>
		</div>
	</div>
@stop