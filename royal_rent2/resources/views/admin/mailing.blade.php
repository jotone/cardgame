@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_mailing.js') }}"></script>
@stop

@section('content')
	<div class="main-block">
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="button-wrap" style="display:none;">
				<a class="control-button" href="{{ URL::asset('/admin/mailing/add') }}">Добавить</a>
			</div>
			<div class="categories-list-wrap">
				@if(!empty($content))
					<table class="item-list col_1" id="mailingTable">
						<thead>
						<tr>
							<th></th>
							<th></th>
							<th>Название</th>
							<th>Отправитель</th>
							<th>Получатель</th>
							<th>Создан</th>
							<th>Изменен</th>
						</tr>
						</thead>
						<tbody>
						@foreach($content as $item)
							<tr>
								<td>
									<a class="block-button edit" href="{{ URL::asset('/admin/mailing/edit/'.$item['id']) }}" title="Редактировать">
										<img src="{{ URL::asset('img/edit.png') }}" alt="Редактировать">
									</a>
								</td>
								<td>
									<a class="block-button drop" data-id="{{ $item['id'] }}" href="#" data-title="{{ $item['title'] }}" title="Удалить">
										<img src="{{ URL::asset('img/drop.png') }}" alt="Удалить">
									</a>
								</td>
								<td>{{ $item['title'] }}</td>
								<td>{{ $item['sender'] }}</td>
								<td>{{ $item['receiver'] }}</td>
								<td>{{ $item['created_at'] }}</td>
								<td>{{ $item['updated_at'] }}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				@endif
			</div>
		</div>
	</div>
@stop