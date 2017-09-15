@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_user_roles.js') }}"></script>
@stop
@section('content')
	<div class="main-block" data-type="admin_menu">
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="button-wrap">
				<a class="control-button" href="{{ route('admin-user-roles-add-page') }}">Добавить</a>
			</div>
			<table class="item-list col_1">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>Роль</th>
						<th>Обозначение</th>
						<th>Запрещен доступ</th>
						<th>Создан</th>
						<th>Изменен</th>
					</tr>
				</thead>
				<tbody>
				@foreach($roles as $role)
					<tr>
						@if($role['editable'] > 0)
						<td>
							<a class="button edit" href="{{ route('admin-user-roles-edit-page', $role['id']) }}" title="Редактировать">
								<img src="{{ URL::asset('img/edit.png') }}" alt="Редактировать">
							</a>
						</td>
						<td>
							<a class="button drop" href="#" title="Удалить" data-id="{{ $role['id'] }}" data-title="{{ $role['title'] }}">
								<img src="{{ URL::asset('img/drop.png') }}" alt="Удалить">
							</a>
						</td>
						@else
						<td></td>
						<td></td>
						@endif
						<td>{{ $role['title'] }}</td>
						<td>{{ $role['pseudonim'] }}</td>
						<td>{!! $role['access_pages'] !!}</td>
						<td>{!! $role['created_at'] !!}</td>
						<td>{!! $role['updated_at'] !!}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop