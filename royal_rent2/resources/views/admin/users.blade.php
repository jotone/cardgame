@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_users.js') }}"></script>
@stop
<?php
$current_user = Auth::user();
?>
@section('content')
	<div class="main-block" data-type="admin_menu">
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<table class="item-list col_1">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>Логин</th>
						<th>e-mail</th>
						<th>Имя</th>
						<th>Роль</th>
						<th>Создан</th>
						<th>Изменен</th>
					</tr>
				</thead>
				<tbody>
				@foreach($users as $user)
					<tr>
						<td>
							<a class="button edit" href="{{ route('admin-users-edit-page', $user['id']) }}" title="Редактировать">
								<img src="{{ URL::asset('img/edit.png') }}" alt="Редактировать">
							</a>
						</td>
						<td>
							@if($current_user['login'] != $user['login'])
								<a class="button drop" href="#" title="Удалить" data-id="{{ $user['id'] }}" data-title="{{ $user['login'] }}">
									<img src="{{ URL::asset('img/drop.png') }}" alt="Удалить">
								</a>
							@endif
						</td>
						<td>{{ $user['login'] }}</td>
						<td>{{ $user['email'] }}</td>
						<td>{{ $user['name'] }}</td>
						<td>{{ $user['role'] }}</td>
						<td>{{ $user['created_at'] }}</td>
						<td>{{ $user['updated_at'] }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop