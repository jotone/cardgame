@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/redirects.js') }}"></script>
@stop
@section('content')
<div class="main-block" data-type="admin_menu">
	<div class="center-wrap col_1">
		<div class="page-caption row-wrap">{{ $page_title }}</div>
		<table class="item-list col_1">
			<thead>
			<tr>
				<th></th>
				<th>Перенаправить с ссылки</th>
				<th>На ссылку</th>
			</tr>
			</thead>
			<tbody>
			@foreach($content as $item)
				<tr data-id="{{ $item->id }}">
					<td>
						<a class="block-button drop" href="#" title="Удалить">
							<img src="{{ URL::asset('img/drop.png') }}" alt="Удалить">
						</a>
					</td>
					<td>
						<input name="link_from" type="text" class="text-input col_1" placeholder="Источник&hellip;" value="{{ $item->link_from }}">
					</td>
					<td>
						<input name="link_to" type="text" class="text-input col_1" placeholder="Назначение&hellip;" value="{{ $item->link_to }}">
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
		<div class="button-wrap tal">
			<button name="addItem" class="control-button" type="button">Добавить ссылку</button>
		</div>
		<div class="button-wrap tal">
			<button name="save" class="control-button" type="button">Сохранить</button>
		</div>
	</div>
</div>
@stop