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
			@foreach($modules as $module)
				<div class="module-wrap" title="{{ $module['description'] }}" data-id="{{ $module['id'] }}">
					<span>{{ $module['title'] }}</span>
					<div class="add-button"></div>
				</div>
			@endforeach
		</div>
	</div>
	<div class="center-wrap col_4_5">
		<div class="page-caption row-wrap">{{ $page_title }}</div>
		<div class="work-place-wrap" id="modulesSelectable">

		@foreach($enabled_modules as $module)
			<div class="enabled-module-wrap @if($module['enabled'] == 1) active @endif" data-id="{{ $module['id'] }}">
				<div class="enabled-module-row">
					<div class="enabled-module-title">
						{{ $module['title'] }}
						<span class="enabled-module-slug">({{ $module['slug'] }})</span>
					</div>
					<div class="enabled-module-title">Тип: <ins>{{ $module['type'] }}</ins></div>
				</div>
				<div class="show-button-wrap">
					<span>Развернуть</span>
				</div>

				<div class="show-wrap">
					<div class="enabled-module-row-inline">
						<div class="col_1_2 enabled-module-content">{{ $module['description'] }}</div>

						@if(!empty($module['disabled_fields']))
						<div class="col_1_4 enabled-module-content">
							<p>Отключенные поля:</p>
							<ul class="enabled-module-fileds-list">
								@foreach($module['disabled_fields'] as $field)
									<li>{{$field}};</li>
								@endforeach
							</ul>
						</div>
						@endif

						@if(!empty($module['custom_fields']))
						<div class="col_1_4 enabled-module-content">
							<p>Дополнительные поля:</p>
							<ul class="enabled-module-fileds-list">
								@foreach($module['custom_fields'] as $field)
									<li>{{$field}};</li>
								@endforeach
							</ul>
						</div>
						@endif
					</div>

					<div class="enabled-module-row">
						<div class="col_3_4">
							<div class="enabled-module-row-inline">
								<a class="button edit" href="{{ route('admin-modules-edit-page') }}/{{ $module['id'] }}" title="Редактировать" style="margin-right: 10px;">
									<img src="{{ URL::asset('img/edit.png') }}" alt="Редактировать">
								</a>
								<a class="button drop" href="#" title="Удалить" style="margin-right: 10px;"  data-title="{{ $module['title'] }}">
									<img src="{{ URL::asset('img/drop.png') }}" alt="Удалить">
								</a>
								<div class="enabled-module-on @if($module['enabled'] == 1) active @endif">
									@if($module['enabled'] == 1) Включен @else Выключен @endif
								</div>
							</div>
						</div>
						<div class="timestamps">
							<p>Создан: {{ $module['created_at'] }}</p>
							<p>Изменен: {{ $module['updated_at'] }}</p>
						</div>
					</div>
				</div>
			</div>
		@endforeach

		</div>
	</div>
</div>
@stop