@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_categories.js') }}"></script>
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
							<select name="refer_to" class="select-input">
								<option value="0" @if((isset($content['refer_to'])) && ($content['refer_to'] == 0)) selected="selected" @endif>Не относится</option>
								@foreach($fields['categories'] as $category)
									<option value="{{ $category->id }}" @if((isset($content['refer_to'])) && ($content['refer_to'] == $category->id)) selected="selected" @endif>{{ $category->title }}</option>
								@endforeach
							</select>
							<span>Отнести к категории</span>
							</label>
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
				<div id="customFieldsWrap">
					@if(isset($content['custom_fields']))
						{!! \App\Http\Controllers\Supply\Functions::buildCustomFields($fields['custom_fields'], $content['custom_fields']) !!}
					@else
						{!! \App\Http\Controllers\Supply\Functions::buildCustomFields($fields['custom_fields']) !!}
					@endif
				</div>
			</div>
			<div class="button-wrap tac">
				<button name="saveCategory" class="control-button" type="button">Применить</button>
			</div>
		</div>
	</div>
@stop