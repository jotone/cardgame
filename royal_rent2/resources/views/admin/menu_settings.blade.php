@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_menu_settings.js') }}"></script>
@stop
@section('content')
<div class="main-block" data-type="admin_menu">
	<div class="center-wrap col_1">
		<div class="categories-list-wrap">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			{!! $menu_list !!}
		</div>
	</div>
</div>
@stop