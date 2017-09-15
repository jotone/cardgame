@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_pages.js') }}"></script>
@stop
@section('content')
	<div class="main-block">
		<div class="aside-wrap">
			<div class="template-select-wrap">
				<label class="fieldset-label-wrap">
					<strong>Выберите тип шаблона:</strong>
					<select name="templateType" class="select-input">
						@foreach($templates as $template)
							<option value="{{ $template['id'] }}" @if((isset($content['module_id'])) && ($content['module_id'] == $template['id'])) selected="selected" @endif>{{ $template['title'] }}</option>
						@endforeach
					</select>
				</label>
			</div>
			<div class="fixed-navigation-menu">
				<ul></ul>
			</div>
		</div>
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
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
							<select name="slugFake" class="select-input faker-changeable">
								@foreach($links as $link)
									<option value="{{ $link->slug }}">{{ $link->title }}</option>
								@endforeach
							</select>
							<label class="fieldset-label-wrap">
								<input name="slug" type="text" class="text-input faker-input" placeholder="Ссылка меню&hellip;" @if(isset($content['slug'])) value="{{ $content['slug'] }}" @endif>
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

				<div id="default_fields"></div>

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

				<div id="customFieldsWrap"></div>
			</div>
			<div class="button-wrap tac">
				<button name="savePage" class="control-button" type="button">Применить</button>
			</div>
		</div>
	</div>
@stop