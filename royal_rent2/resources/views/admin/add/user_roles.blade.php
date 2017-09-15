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
			<div class="work-place-wrap">
				<input name="id" type="hidden" value="@if(isset($data)){{$data->id}}@endif">
				<fieldset>
					<legend>Основные данные</legend>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="title" type="text" class="text-input col_1_2" placeholder="Название роли&hellip;" value="@if(isset($data)){{$data->title}}@endif">
							<span>Название роли</span>
						</label>
					</div>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="pseudonim" type="text" class="text-input col_1_2" placeholder="Псевдоним роли&hellip;" value="@if(isset($data)){{$data->pseudonim}}@endif">
							<span>Псевдоним роли</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Запретить доступ к станицам</legend>
					<div class="row-wrap">
						<div class="chbox-selector-wrap">
							<?php
							$access_pages = (isset($data))? unserialize($data->access_pages): [];
							?>
							@foreach($pages as $page)
								@if($page['slug'] != '#')
								<div class="checkbox-item-wrap">
									<label class="fieldset-label-wrap">
									   <input name="page" type="checkbox" class="chbox-input" value="{{ $page['id'] }}" @if(in_array($page['id'], $access_pages)) checked="checked" @endif>
										<span>{{ $page['parent_title'] }}</span>
									</label>
								</div>
								@endif
							@endforeach
						</div>
					</div>
				</fieldset>
				<div class="button-wrap tac">
					<button name="saveRole" class="control-button" type="button">Применить</button>
				</div>
			</div>
		</div>
	</div>
@stop