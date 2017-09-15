@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_users.js') }}"></script>
@stop
@section('content')
	<div class="main-block" data-type="admin_menu">
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="work-place-wrap">
				<input name="id" type="hidden" value="{{ $user->id }}">
				<fieldset>
					<legend>Основные данные</legend>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="login" type="text" disabled class="text-input col_1_2" placeholder="Логин&hellip;" value="{{ $user['login'] }}">
							<span>Логин</span>
						</label>
					</div>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="email" type="text" class="text-input col_1_2" placeholder="E-mail&hellip;" value="{{ $user['email'] }}">
							<span>E-mail</span>
						</label>
					</div>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="name" type="text" class="text-input col_1_2" placeholder="E-mail&hellip;" value="{{ $user['name'] }}">
							<span>Имя</span>
						</label>
					</div>
					<div class="row-wrap">
						<label class="fieldset-label-wrap">
							<input name="phone" type="text" class="text-input col_1_2" placeholder="Телефон&hellip;" value="{{ $user['phone'] }}">
							<span>Телефон</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Роль</legend>
					<div class="row-wrap">
						<select name="userRole" class="select-input">
							@foreach($roles as $role)
								<option value="{{$role['pseudonim']}}" @if($role['pseudonim'] == $user['user_role']) selected="selected" @endif>{{$role['title']}}</option>
							@endforeach
						</select>
					</div>
				</fieldset>
				<div class="button-wrap tac">
					<button name="editUset" class="control-button" type="button">Применить</button>
				</div>
			</div>
		</div>
	</div>
@stop