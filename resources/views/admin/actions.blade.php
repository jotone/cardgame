@extends('admin.layouts.default')
@section('content')

<div class="main-central-wrap">
	<div class="button-wrap">
		<a class="add-one" href="{{ route('admin-action-add') }}">Добавить</a>
	</div>

	@if($actions)
		<table class="data-table">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th>Название</th>
				<th>Ссылка</th>
				<th>Описание</th>
				<th>HTML</th>
				<th>Создан</th>
				<th>Изменен</th>
			</tr>
		</thead>
		<tbody>
			@foreach($actions as $action)
			<tr>
				<td><a class="edit" href="{{ route('admin-action-edit-page', $action['id']) }}"></a></td>
				<td>
					{{ Form::open(['route' => 'admin-actions-drop', 'method' => 'POST']) }}
						{{ Form::hidden('_method', 'DELETE') }}
						<input name="adm_id" type="hidden" value="{{ $action['id'] }}">
						<input type="submit" class="drop" value="">
					{{ Form::close() }}
				</td>
				<td>{{ $action['title'] }}</td>
				<td>{{ $action['slug'] }}</td>
				<td>{{ Str::limit($action['description'], 50, '...') }}</td>
				<td class="tal">{!! $action['html_options'] !!}</td>
				<td>{{ $action['created'] }}</td>
				<td>{{ $action['updated'] }}</td>
			</tr>
			@endforeach 
		</tbody>
		</table>
	@endif
</div>

@stop