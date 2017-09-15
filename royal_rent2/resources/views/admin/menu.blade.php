@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_site_menu.js') }}"></script>
@stop
@section('content')
	<div class="main-block" data-module="{{ $module_id }}">
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="button-wrap">
				<a class="control-button" href="{{ URL::asset('/admin/'.$global_slug.'/add') }}">Добавить</a>
			</div>
			<div class="categories-list-wrap">
				{!! $content !!}
			</div>
		</div>
	</div>
@stop